<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\DashboardSetting;
use App\Models\PremiumPackage;
use App\Models\PremiumPayment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

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

    private function makePaymentCode(int $userId): string
    {
        do {
            $code = 'PRM-' . now()->format('Ymd') . '-' . $userId . '-' . Str::upper(Str::random(5));
        } while (PremiumPayment::where('payment_code', $code)->exists());

        return $code;
    }
}
