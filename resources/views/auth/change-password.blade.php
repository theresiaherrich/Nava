@extends('layouts.app')
@section('title', 'Ganti Kata Sandi')
@section('content')
    <div class="max-w-lg p-7">
        <x-ui.page-header title="Ganti Kata Sandi" :subtitle="auth()->user()->must_change_password ? 'Ganti kata sandi awal sebelum membuka dashboard.' : 'Perbarui kata sandi akun Anda.'" />
        <x-ui.card>
            <form class="space-y-4" method="POST" action="/api/auth/password/change" data-auth="password/change">
                @csrf
                @foreach (['password_lama' => 'Kata sandi lama', 'password_baru' => 'Kata sandi baru', 'password_baru_confirmation' => 'Konfirmasi kata sandi baru'] as $field => $label)
                    <label class="block text-sm font-semibold">{{ $label }}
                        <input type="password" name="{{ $field }}" required autocomplete="{{ $field === 'password_lama' ? 'current-password' : 'new-password' }}" class="mt-1 block w-full rounded-control border border-line px-3 py-2">
                    </label>
                @endforeach
                <p data-error role="alert" class="text-sm text-danger"></p>
                <x-ui.button type="submit">Simpan kata sandi</x-ui.button>
            </form>
        </x-ui.card>
    </div>
@endsection
