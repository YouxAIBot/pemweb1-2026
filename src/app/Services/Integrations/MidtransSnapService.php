<?php

namespace App\Services\Integrations;

use App\Models\PremiumPayment;
use App\Services\PremiumActivationService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class MidtransSnapService
{
    public function __construct(
        private readonly PremiumActivationService $activationService,
    ) {}

    public function isConfigured(): bool
    {
        return filled($this->serverKey());
    }

    public function createTransaction(PremiumPayment $payment): array
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('Midtrans server key belum dikonfigurasi.');
        }

        $payment->loadMissing(['user', 'package']);

        $payload = [
            'transaction_details' => [
                'order_id' => $payment->gateway_order_id ?: $payment->payment_code,
                'gross_amount' => (int) $payment->amount,
            ],
            'customer_details' => [
                'first_name' => $payment->user?->name ?? 'YoLearning User',
                'email' => $payment->user?->email,
            ],
            'item_details' => [[
                'id' => (string) ($payment->package?->id ?? 'premium'),
                'price' => (int) $payment->amount,
                'quantity' => 1,
                'name' => $payment->package?->name ?? 'Premium YoLearning',
            ]],
            'callbacks' => [
                'finish' => route('learning.premium'),
            ],
        ];

        $response = Http::withBasicAuth($this->serverKey(), '')
            ->acceptJson()
            ->asJson()
            ->post($this->snapEndpoint(), $payload);

        if ($response->failed()) {
            $message = Arr::first((array) $response->json('error_messages')) ?: 'Gagal membuat transaksi Midtrans.';

            throw new RuntimeException($message);
        }

        return $response->json();
    }

    public function handleNotification(array $payload): ?PremiumPayment
    {
        if (! $this->isValidSignature($payload)) {
            throw new RuntimeException('Signature Midtrans tidak valid.');
        }

        $orderId = (string) Arr::get($payload, 'order_id');

        if ($orderId === '') {
            return null;
        }

        $payment = PremiumPayment::query()
            ->where('gateway_order_id', $orderId)
            ->orWhere('payment_code', $orderId)
            ->first();

        if (! $payment) {
            return null;
        }

        $transactionStatus = (string) Arr::get($payload, 'transaction_status');
        $fraudStatus = (string) Arr::get($payload, 'fraud_status');

        $payment->update([
            'gateway_transaction_id' => Arr::get($payload, 'transaction_id'),
            'gateway_status' => $transactionStatus,
            'gateway_response' => $payload,
        ]);

        if ($transactionStatus === 'settlement' || ($transactionStatus === 'capture' && $fraudStatus === 'accept')) {
            $payment->update([
                'payment_status' => PremiumPayment::STATUS_PAID,
                'paid_at' => $payment->paid_at ?? now(),
            ]);

            $this->activationService->approve($payment->refresh(), null, 'Pembayaran otomatis aktif via Midtrans.');

            return $payment->refresh();
        }

        if (in_array($transactionStatus, ['cancel', 'deny'], true)) {
            $payment->update([
                'payment_status' => PremiumPayment::STATUS_REJECTED,
                'rejected_at' => now(),
                'note' => 'Pembayaran Midtrans dibatalkan atau ditolak.',
            ]);
        }

        if ($transactionStatus === 'expire') {
            $payment->update([
                'payment_status' => PremiumPayment::STATUS_EXPIRED,
                'note' => 'Pembayaran Midtrans kedaluwarsa.',
            ]);
        }

        return $payment->refresh();
    }

    private function isValidSignature(array $payload): bool
    {
        $signature = (string) Arr::get($payload, 'signature_key');

        if ($signature === '' || ! $this->isConfigured()) {
            return false;
        }

        $expected = hash('sha512', implode('', [
            Arr::get($payload, 'order_id'),
            Arr::get($payload, 'status_code'),
            Arr::get($payload, 'gross_amount'),
            $this->serverKey(),
        ]));

        return hash_equals($expected, $signature);
    }

    private function snapEndpoint(): string
    {
        return config('services.midtrans.is_production')
            ? 'https://app.midtrans.com/snap/v1/transactions'
            : 'https://app.sandbox.midtrans.com/snap/v1/transactions';
    }

    private function serverKey(): ?string
    {
        return config('services.midtrans.server_key');
    }
}
