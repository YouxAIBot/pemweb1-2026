<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\DashboardSetting;
use App\Models\PremiumPackage;
use App\Models\PremiumPayment;
use App\Services\Integrations\MidtransSnapService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class PremiumController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user()->load(['activePremium.package']);

        return view('frontend.learning.premium', [
            'setting' => DashboardSetting::query()->first() ?? new DashboardSetting(['brand_text' => 'YoLearning', 'brand_initial' => 'Y']),
            'packages' => PremiumPackage::query()->active()->orderBy('sort_order')->get(),
            'activePremium' => $user->activePremium,
            'payments' => PremiumPayment::query()
                ->with('package')
                ->where('user_id', $user->id)
                ->latest()
                ->take(12)
                ->get(),
            'midtransEnabled' => app(MidtransSnapService::class)->isConfigured(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'premium_package_id' => ['required', 'exists:premium_packages,id'],
            'payment_proof' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:4096'],
            'note' => ['nullable', 'string', 'max:500'],
        ], [
            'payment_proof.required' => 'Upload bukti pembayaran terlebih dahulu.',
            'payment_proof.mimes' => 'Bukti pembayaran harus berupa gambar atau PDF.',
            'payment_proof.max' => 'Ukuran bukti pembayaran maksimal 4 MB.',
        ]);

        $package = PremiumPackage::query()
            ->active()
            ->findOrFail($data['premium_package_id']);

        $existingPending = PremiumPayment::query()
            ->where('user_id', $request->user()->id)
            ->where('premium_package_id', $package->id)
            ->where('payment_status', PremiumPayment::STATUS_PENDING)
            ->latest()
            ->first();

        if ($existingPending) {
            return redirect()
                ->route('learning.premium')
                ->with('learning_error', 'Kamu masih punya pembayaran yang menunggu verifikasi. Tunggu admin memproses atau hubungi admin.');
        }

        $proofPath = $request->file('payment_proof')->store('premium/payment-proofs', 'public');

        PremiumPayment::create([
            'user_id' => $request->user()->id,
            'premium_package_id' => $package->id,
            'payment_code' => $this->makePaymentCode($request->user()->id),
            'payment_method' => 'manual_bank_transfer',
            'amount' => $package->price,
            'payment_proof' => $proofPath,
            'payment_status' => PremiumPayment::STATUS_PENDING,
            'note' => $data['note'] ?? null,
        ]);

        return redirect()
            ->route('learning.premium')
            ->with('learning_success', 'Bukti pembayaran berhasil dikirim. Status premium aktif setelah admin menyetujui pembayaran.');
    }

    public function midtrans(Request $request, MidtransSnapService $midtrans): RedirectResponse
    {
        if (! $midtrans->isConfigured()) {
            return redirect()
                ->route('learning.premium')
                ->with('learning_error', 'Midtrans belum dikonfigurasi. Gunakan pembayaran manual terlebih dahulu.');
        }

        $data = $request->validate([
            'premium_package_id' => ['required', 'exists:premium_packages,id'],
        ]);

        $package = PremiumPackage::query()
            ->active()
            ->findOrFail($data['premium_package_id']);

        $existingPending = PremiumPayment::query()
            ->where('user_id', $request->user()->id)
            ->where('premium_package_id', $package->id)
            ->where('payment_method', 'midtrans_snap')
            ->where('payment_status', PremiumPayment::STATUS_PENDING)
            ->latest()
            ->first();

        if ($existingPending && filled($existingPending->snap_redirect_url)) {
            return redirect()->away($existingPending->snap_redirect_url);
        }

        $paymentCode = $this->makePaymentCode($request->user()->id);
        $payment = PremiumPayment::create([
            'user_id' => $request->user()->id,
            'premium_package_id' => $package->id,
            'payment_code' => $paymentCode,
            'payment_method' => 'midtrans_snap',
            'amount' => $package->price,
            'payment_status' => PremiumPayment::STATUS_PENDING,
            'gateway' => 'midtrans',
            'gateway_order_id' => $paymentCode,
        ]);

        try {
            $snap = $midtrans->createTransaction($payment);

            $payment->update([
                'snap_token' => $snap['token'] ?? null,
                'snap_redirect_url' => $snap['redirect_url'] ?? null,
                'gateway_response' => $snap,
            ]);

            if (! filled($payment->snap_redirect_url)) {
                throw new RuntimeException('Midtrans tidak mengembalikan URL pembayaran.');
            }

            return redirect()->away($payment->snap_redirect_url);
        } catch (Throwable $exception) {
            $payment->update([
                'payment_status' => PremiumPayment::STATUS_REJECTED,
                'rejected_at' => now(),
                'note' => 'Gagal membuat transaksi Midtrans: ' . $exception->getMessage(),
            ]);

            return redirect()
                ->route('learning.premium')
                ->with('learning_error', 'Transaksi Midtrans gagal dibuat. Coba lagi atau gunakan pembayaran manual.');
        }
    }

    public function midtransNotification(Request $request, MidtransSnapService $midtrans): JsonResponse
    {
        try {
            $payment = $midtrans->handleNotification($request->all());
        } catch (RuntimeException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 403);
        }

        return response()->json([
            'received' => true,
            'payment_code' => $payment?->payment_code,
            'status' => $payment?->payment_status,
        ]);
    }

    private function makePaymentCode(int $userId): string
    {
        do {
            $code = 'PRM-' . now()->format('Ymd') . '-' . $userId . '-' . Str::upper(Str::random(5));
        } while (PremiumPayment::where('payment_code', $code)->exists());

        return $code;
    }
}
