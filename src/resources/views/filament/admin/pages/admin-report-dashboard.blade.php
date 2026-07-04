<x-filament-panels::page>
    <div class="space-y-6">
        <div class="grid gap-4 md:grid-cols-4">
            <x-filament::section>
                <p class="text-sm font-semibold text-gray-500">Pembayaran Pending</p>
                <p class="mt-2 text-3xl font-black">{{ $this->paymentStats['pending'] }}</p>
            </x-filament::section>

            <x-filament::section>
                <p class="text-sm font-semibold text-gray-500">Pembayaran Berhasil</p>
                <p class="mt-2 text-3xl font-black">{{ $this->paymentStats['approved'] }}</p>
            </x-filament::section>

            <x-filament::section>
                <p class="text-sm font-semibold text-gray-500">Revenue Premium</p>
                <p class="mt-2 text-3xl font-black">Rp{{ number_format($this->paymentStats['revenue'], 0, ',', '.') }}</p>
            </x-filament::section>

            <x-filament::section>
                <p class="text-sm font-semibold text-gray-500">Premium Aktif</p>
                <p class="mt-2 text-3xl font-black">{{ $this->premiumStats['active'] }}</p>
            </x-filament::section>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <x-filament::section>
                <x-slot name="heading">Premium</x-slot>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between gap-4"><span>Aktif</span><strong>{{ $this->premiumStats['active'] }}</strong></div>
                    <div class="flex justify-between gap-4"><span>Expired</span><strong>{{ $this->premiumStats['expired'] }}</strong></div>
                    <div class="flex justify-between gap-4"><span>Expired 7 Hari</span><strong>{{ $this->premiumStats['expiring_soon'] }}</strong></div>
                </div>
            </x-filament::section>

            <x-filament::section>
                <x-slot name="heading">Aktivitas Belajar</x-slot>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between gap-4"><span>Level selesai hari ini</span><strong>{{ $this->activityStats['levels_today'] }}</strong></div>
                    <div class="flex justify-between gap-4"><span>Level selesai 7 hari</span><strong>{{ $this->activityStats['levels_week'] }}</strong></div>
                    <div class="flex justify-between gap-4"><span>Turnamen 7 hari</span><strong>{{ $this->activityStats['tournament_attempts'] }}</strong></div>
                    <div class="flex justify-between gap-4"><span>Quiz Room 7 hari</span><strong>{{ $this->activityStats['quiz_histories'] }}</strong></div>
                </div>
            </x-filament::section>

            <x-filament::section>
                <x-slot name="heading">Performa Ads</x-slot>
                <div class="space-y-3 text-sm">
                    @forelse ($this->adStats as $row)
                        <div class="flex justify-between gap-4">
                            <span>{{ str($row->placement)->headline() }}</span>
                            <strong>{{ $row->total }}</strong>
                        </div>
                    @empty
                        <p class="text-gray-500">Belum ada impression iklan.</p>
                    @endforelse
                </div>
            </x-filament::section>
        </div>

        <x-filament::section>
            <x-slot name="heading">Pembayaran Terbaru</x-slot>
            <x-slot name="description">Pantau request manual dan transaksi gateway terbaru.</x-slot>

            <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-800">
                <table class="w-full divide-y divide-gray-200 text-sm dark:divide-gray-800">
                    <thead class="bg-gray-50 text-left text-xs font-bold uppercase tracking-wide text-gray-500 dark:bg-gray-900">
                        <tr>
                            <th class="px-4 py-3">Kode</th>
                            <th class="px-4 py-3">User</th>
                            <th class="px-4 py-3">Paket</th>
                            <th class="px-4 py-3">Jumlah</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Tanggal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($this->recentPayments as $payment)
                            <tr>
                                <td class="px-4 py-3 font-semibold">{{ $payment->payment_code }}</td>
                                <td class="px-4 py-3">{{ $payment->user?->name ?? 'User' }}</td>
                                <td class="px-4 py-3">{{ $payment->package?->name ?? '-' }}</td>
                                <td class="px-4 py-3">Rp{{ number_format($payment->amount, 0, ',', '.') }}</td>
                                <td class="px-4 py-3">{{ \App\Models\PremiumPayment::STATUSES[$payment->payment_status] ?? $payment->payment_status }}</td>
                                <td class="px-4 py-3">{{ $payment->created_at?->format('d M Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-6 text-center text-gray-500">Belum ada pembayaran.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
