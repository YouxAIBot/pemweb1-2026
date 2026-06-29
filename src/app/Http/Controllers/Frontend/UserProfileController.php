<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\DashboardSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $tab = in_array($request->query('tab'), ['account', 'edit-profile'], true)
            ? $request->query('tab')
            : 'account';

        return view('frontend.learning.profile', [
            'setting' => DashboardSetting::query()->first() ?? new DashboardSetting(['brand_text' => 'YoLearning', 'brand_initial' => 'Y']),
            'user' => $request->user(),
            'profile' => $request->user()->learningProfile()->with('language')->first(),
            'tab' => $tab,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:180', Rule::unique('users', 'email')->ignore($user->id)],
            'bio' => ['nullable', 'string', 'max:220'],
            'avatar' => ['nullable', 'image', 'max:2048'],
            'current_password' => ['nullable', 'string'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ], [
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak sama.',
            'avatar.image' => 'Foto profil harus berupa gambar.',
        ]);

        if (filled($data['password'] ?? null)) {
            if (! filled($data['current_password'] ?? null) || ! Hash::check($data['current_password'], $user->password)) {
                return back()->withErrors(['current_password' => 'Password lama tidak sesuai.'])->withInput();
            }
        }

        $payload = [
            'name' => $data['name'],
            'email' => $data['email'],
            'bio' => $data['bio'] ?? null,
        ];

        if ($request->hasFile('avatar')) {
            $payload['avatar_url'] = $request->file('avatar')->store('profile-photos', 'public');
        }

        if (filled($data['password'] ?? null)) {
            $payload['password'] = $data['password'];
        }

        $user->update($payload);

        return back()->with('learning_success', 'Profil berhasil diperbarui.');
    }
}
