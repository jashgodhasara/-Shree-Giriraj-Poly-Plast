{{-- Shared form fields for create/edit user modals --}}
@php $isEdit = $isEdit ?? false; @endphp

<div style="margin-bottom:16px;">
    <label style="display:block;font-size:13px;font-weight:600;color:var(--text);margin-bottom:7px;">Full Name <span style="color:var(--danger);">*</span></label>
    <input type="text" name="name" id="{{ $isEdit ? 'edit_name' : 'create_name' }}"
        value="{{ old('name', $user->name ?? '') }}"
        placeholder="e.g. Jash Godhasara"
        style="width:100%;padding:10px 14px;border:1.5px solid var(--border);border-radius:9px;font-size:14px;font-family:inherit;outline:none;color:var(--text);"
        onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='var(--border)'"
        required>
    @error('name')<p style="font-size:12px;color:var(--danger);margin-top:4px;">{{ $message }}</p>@enderror
</div>

<div style="margin-bottom:16px;">
    <label style="display:block;font-size:13px;font-weight:600;color:var(--text);margin-bottom:7px;">Email Address <span style="color:var(--danger);">*</span></label>
    <input type="email" name="email" id="{{ $isEdit ? 'edit_email' : 'create_email' }}"
        value="{{ old('email', $user->email ?? '') }}"
        placeholder="user@company.com"
        style="width:100%;padding:10px 14px;border:1.5px solid var(--border);border-radius:9px;font-size:14px;font-family:inherit;outline:none;color:var(--text);"
        onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='var(--border)'"
        required>
    @error('email')<p style="font-size:12px;color:var(--danger);margin-top:4px;">{{ $message }}</p>@enderror
</div>

<div style="margin-bottom:16px;">
    <label style="display:block;font-size:13px;font-weight:600;color:var(--text);margin-bottom:7px;">Role <span style="color:var(--danger);">*</span></label>
    <select name="role" id="{{ $isEdit ? 'edit_role' : 'create_role' }}"
        style="width:100%;padding:10px 14px;border:1.5px solid var(--border);border-radius:9px;font-size:14px;font-family:inherit;outline:none;color:var(--text);background:#fff;cursor:pointer;"
        onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='var(--border)'"
        required>
        <option value="staff" {{ old('role', $user->role ?? 'staff') === 'staff' ? 'selected' : '' }}>Staff / Employee</option>
        <option value="admin" {{ old('role', $user->role ?? '') === 'admin' ? 'selected' : '' }}>Admin</option>
    </select>
    @error('role')<p style="font-size:12px;color:var(--danger);margin-top:4px;">{{ $message }}</p>@enderror
</div>

<div style="margin-bottom:24px;">
    <label style="display:block;font-size:13px;font-weight:600;color:var(--text);margin-bottom:7px;">
        Password {{ $isEdit ? '<span style="font-size:11px;color:var(--text-muted);font-weight:400;">(leave blank to keep current)</span>' : '<span style="color:var(--danger);">*</span>' }}
    </label>
    <input type="password" name="password" id="{{ $isEdit ? 'edit_password' : 'create_password' }}"
        placeholder="{{ $isEdit ? 'Leave blank to keep current password' : 'Min. 8 characters' }}"
        style="width:100%;padding:10px 14px;border:1.5px solid var(--border);border-radius:9px;font-size:14px;font-family:inherit;outline:none;color:var(--text);"
        onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='var(--border)'"
        {{ $isEdit ? '' : 'required minlength=8' }}>
    @error('password')<p style="font-size:12px;color:var(--danger);margin-top:4px;">{{ $message }}</p>@enderror
</div>

<div style="display:flex;gap:10px;justify-content:flex-end;">
    <button type="button"
        onclick="closeModal('{{ $isEdit ? 'editModal' : 'createModal' }}')"
        style="padding:10px 20px;background:#fff;border:1px solid var(--border);border-radius:9px;font-size:14px;font-weight:500;cursor:pointer;font-family:inherit;color:var(--text-muted);transition:all .15s;"
        onmouseover="this.style.background='var(--bg)'" onmouseout="this.style.background='#fff'">
        Cancel
    </button>
    <button type="submit"
        style="padding:10px 22px;background:linear-gradient(135deg,var(--primary),var(--primary-dark));color:#fff;border:none;border-radius:9px;font-size:14px;font-weight:600;cursor:pointer;font-family:inherit;box-shadow:0 4px 12px rgba(99,102,241,.3);">
        <i class="fa-solid {{ $isEdit ? 'fa-floppy-disk' : 'fa-user-plus' }}" style="margin-right:6px;"></i>
        {{ $buttonText }}
    </button>
</div>
