@extends('layouts.app')

@section('title', 'Change Password')

@section('content')
<div style="max-width:520px; margin:0 auto; padding:24px 0;">

    <!-- Page Header -->
    <div style="margin-bottom:28px;">
        <h1 style="font-size:22px; font-weight:700; color:var(--text); margin-bottom:4px;">
            <i class="fa-solid fa-lock" style="color:var(--primary); margin-right:8px;"></i>
            Change Password
        </h1>
        <p style="font-size:13.5px; color:var(--text-muted);">Update your account password. Use a strong password with at least 8 characters.</p>
    </div>

    <!-- Success Alert -->
    @if(session('success'))
    <div style="display:flex;align-items:center;gap:10px;background:rgba(16,185,129,.08);border:1px solid rgba(16,185,129,.25);border-radius:10px;padding:14px 16px;margin-bottom:20px;">
        <i class="fa-solid fa-circle-check" style="color:#10b981;font-size:15px;"></i>
        <span style="font-size:13.5px;color:var(--text);font-weight:500;">{{ session('success') }}</span>
    </div>
    @endif

    <!-- Form Card -->
    <div style="background:var(--card-bg);border-radius:var(--radius);border:1px solid var(--border);padding:32px;box-shadow:var(--shadow);">
        <form method="POST" action="{{ route('change-password.update') }}" id="changePwForm">
            @csrf

            <!-- Current Password -->
            <div style="margin-bottom:20px;">
                <label style="display:block;font-size:13px;font-weight:600;color:var(--text);margin-bottom:8px;">
                    Current Password <span style="color:var(--danger);">*</span>
                </label>
                <div style="position:relative;">
                    <i class="fa-solid fa-lock" style="position:absolute;left:13px;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:13px;pointer-events:none;"></i>
                    <input
                        type="password"
                        name="current_password"
                        id="current_password"
                        placeholder="Enter current password"
                        style="width:100%;padding:11px 40px 11px 38px;border:1.5px solid {{ $errors->has('current_password') ? 'var(--danger)' : 'var(--border)' }};border-radius:9px;font-size:14px;font-family:inherit;outline:none;background:#fff;color:var(--text);transition:border .2s;"
                        onfocus="this.style.borderColor='var(--primary)'"
                        onblur="this.style.borderColor='var(--border)'"
                        required
                    >
                </div>
                @error('current_password')
                <p style="font-size:12px;color:var(--danger);margin-top:5px;"><i class="fa-solid fa-circle-exclamation" style="margin-right:4px;"></i>{{ $message }}</p>
                @enderror
            </div>

            <!-- New Password -->
            <div style="margin-bottom:20px;">
                <label style="display:block;font-size:13px;font-weight:600;color:var(--text);margin-bottom:8px;">
                    New Password <span style="color:var(--danger);">*</span>
                </label>
                <div style="position:relative;">
                    <i class="fa-solid fa-key" style="position:absolute;left:13px;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:13px;pointer-events:none;"></i>
                    <input
                        type="password"
                        name="password"
                        id="new_password"
                        placeholder="Min. 8 characters"
                        style="width:100%;padding:11px 40px 11px 38px;border:1.5px solid {{ $errors->has('password') ? 'var(--danger)' : 'var(--border)' }};border-radius:9px;font-size:14px;font-family:inherit;outline:none;background:#fff;color:var(--text);transition:border .2s;"
                        onfocus="this.style.borderColor='var(--primary)'"
                        onblur="this.style.borderColor='var(--border)'"
                        required minlength="8"
                    >
                </div>
                @error('password')
                <p style="font-size:12px;color:var(--danger);margin-top:5px;"><i class="fa-solid fa-circle-exclamation" style="margin-right:4px;"></i>{{ $message }}</p>
                @enderror
            </div>

            <!-- Confirm New Password -->
            <div style="margin-bottom:28px;">
                <label style="display:block;font-size:13px;font-weight:600;color:var(--text);margin-bottom:8px;">
                    Confirm New Password <span style="color:var(--danger);">*</span>
                </label>
                <div style="position:relative;">
                    <i class="fa-solid fa-shield-halved" style="position:absolute;left:13px;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:13px;pointer-events:none;"></i>
                    <input
                        type="password"
                        name="password_confirmation"
                        id="password_confirmation"
                        placeholder="Re-enter new password"
                        style="width:100%;padding:11px 40px 11px 38px;border:1.5px solid var(--border);border-radius:9px;font-size:14px;font-family:inherit;outline:none;background:#fff;color:var(--text);transition:border .2s;"
                        onfocus="this.style.borderColor='var(--primary)'"
                        onblur="this.style.borderColor='var(--border)'"
                        required
                    >
                </div>
            </div>

            <button type="submit" id="changePwBtn"
                style="display:inline-flex;align-items:center;gap:8px;padding:12px 24px;background:linear-gradient(135deg,var(--primary),var(--primary-dark));color:#fff;border:none;border-radius:9px;font-size:14px;font-weight:600;cursor:pointer;font-family:inherit;box-shadow:0 4px 14px rgba(99,102,241,.3);transition:all .2s;">
                <i class="fa-solid fa-floppy-disk"></i>
                Update Password
            </button>
        </form>
    </div>
</div>
@endsection
