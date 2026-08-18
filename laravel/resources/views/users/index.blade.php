@extends('layouts.app')

@section('title', 'User Management')

@section('content')
<div style="padding:0;">

    <!-- Page Header -->
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:28px;flex-wrap:wrap;gap:12px;">
        <div>
            <h1 style="font-size:22px;font-weight:700;color:var(--text);margin-bottom:4px;">
                <i class="fa-solid fa-users-gear" style="color:var(--primary);margin-right:8px;"></i>
                User Management
            </h1>
            <p style="font-size:13.5px;color:var(--text-muted);">Manage ERP users, roles, and access control.</p>
        </div>
        <button id="openCreateBtn" type="button"
            style="display:inline-flex;align-items:center;gap:8px;padding:11px 20px;background:linear-gradient(135deg,var(--primary),var(--primary-dark));color:#fff;border:none;border-radius:9px;font-size:14px;font-weight:600;cursor:pointer;font-family:inherit;box-shadow:0 4px 14px rgba(99,102,241,.3);">
            <i class="fa-solid fa-plus"></i> Add User
        </button>
    </div>

    <!-- Flash Messages -->
    @if(session('success'))
    <div style="display:flex;align-items:center;gap:10px;background:rgba(16,185,129,.08);border:1px solid rgba(16,185,129,.25);border-radius:10px;padding:13px 16px;margin-bottom:20px;">
        <i class="fa-solid fa-circle-check" style="color:#10b981;font-size:15px;"></i>
        <span style="font-size:13.5px;color:var(--text);font-weight:500;">{{ session('success') }}</span>
    </div>
    @endif
    @if(session('error'))
    <div style="display:flex;align-items:center;gap:10px;background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.25);border-radius:10px;padding:13px 16px;margin-bottom:20px;">
        <i class="fa-solid fa-circle-exclamation" style="color:#ef4444;font-size:15px;"></i>
        <span style="font-size:13.5px;color:var(--text);font-weight:500;">{{ session('error') }}</span>
    </div>
    @endif

    <!-- Stats Row -->
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:16px;margin-bottom:24px;">
        @php
            $totalUsers = $users->count();
            $adminCount = $users->where('role','admin')->count();
            $staffCount = $users->where('role','staff')->count();
            $activeCount = $users->where('is_active',true)->count();
        @endphp
        @foreach([
            ['Total Users','fa-users',$totalUsers,'var(--primary)','rgba(99,102,241,.08)'],
            ['Admins','fa-user-shield',$adminCount,'#8b5cf6','rgba(139,92,246,.08)'],
            ['Staff','fa-user-tie',$staffCount,'var(--accent2)','rgba(16,185,129,.08)'],
            ['Active','fa-circle-check',$activeCount,'var(--info)','rgba(59,130,246,.08)'],
        ] as [$label,$icon,$val,$color,$bg])
        <div style="background:var(--card-bg);border:1px solid var(--border);border-radius:var(--radius-sm);padding:18px 20px;display:flex;align-items:center;gap:14px;box-shadow:var(--shadow-sm);">
            <div style="width:42px;height:42px;border-radius:10px;background:{{ $bg }};display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i class="fa-solid {{ $icon }}" style="color:{{ $color }};font-size:16px;"></i>
            </div>
            <div>
                <div style="font-size:22px;font-weight:700;color:var(--text);line-height:1;">{{ $val }}</div>
                <div style="font-size:12px;color:var(--text-muted);margin-top:2px;">{{ $label }}</div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Users Table -->
    <div style="background:var(--card-bg);border:1px solid var(--border);border-radius:var(--radius);box-shadow:var(--shadow);overflow:hidden;">
        <div style="padding:18px 22px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;">
            <h3 style="font-size:15px;font-weight:600;color:var(--text);">All Users</h3>
            <span style="font-size:12px;color:var(--text-muted);background:var(--bg);padding:4px 10px;border-radius:20px;">{{ $users->count() }} users</span>
        </div>

        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:13.5px;">
                <thead>
                    <tr style="background:rgba(99,102,241,.04);">
                        <th style="padding:12px 22px;text-align:left;font-weight:600;color:var(--text-muted);font-size:12px;text-transform:uppercase;letter-spacing:.5px;border-bottom:1px solid var(--border);">#</th>
                        <th style="padding:12px 16px;text-align:left;font-weight:600;color:var(--text-muted);font-size:12px;text-transform:uppercase;letter-spacing:.5px;border-bottom:1px solid var(--border);">User</th>
                        <th style="padding:12px 16px;text-align:left;font-weight:600;color:var(--text-muted);font-size:12px;text-transform:uppercase;letter-spacing:.5px;border-bottom:1px solid var(--border);">Role</th>
                        <th style="padding:12px 16px;text-align:left;font-weight:600;color:var(--text-muted);font-size:12px;text-transform:uppercase;letter-spacing:.5px;border-bottom:1px solid var(--border);">Status</th>
                        <th style="padding:12px 16px;text-align:left;font-weight:600;color:var(--text-muted);font-size:12px;text-transform:uppercase;letter-spacing:.5px;border-bottom:1px solid var(--border);">Created</th>
                        <th style="padding:12px 22px;text-align:right;font-weight:600;color:var(--text-muted);font-size:12px;text-transform:uppercase;letter-spacing:.5px;border-bottom:1px solid var(--border);">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $index => $user)
                    <tr class="user-row" style="border-bottom:1px solid var(--border);transition:background .15s;">
                        <td style="padding:14px 22px;color:var(--text-muted);">{{ $index + 1 }}</td>
                        <td style="padding:14px 16px;">
                            <div style="display:flex;align-items:center;gap:12px;">
                                <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,var(--primary),#8b5cf6);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                    <span style="font-size:14px;font-weight:700;color:#fff;">{{ strtoupper(substr($user->name ?? '?', 0, 1)) }}</span>
                                </div>
                                <div>
                                    <div style="font-weight:600;color:var(--text);">
                                        {{ $user->name ?: '(No Name)' }}
                                        @if($user->id === auth()->id())
                                        <span style="font-size:10px;background:rgba(99,102,241,.12);color:var(--primary);padding:2px 7px;border-radius:20px;margin-left:6px;font-weight:600;">You</span>
                                        @endif
                                    </div>
                                    <div style="font-size:12px;color:var(--text-muted);">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td style="padding:14px 16px;">
                            @if($user->role === 'admin')
                            <span style="display:inline-flex;align-items:center;gap:5px;padding:4px 10px;background:rgba(139,92,246,.1);color:#7c3aed;border-radius:20px;font-size:12px;font-weight:600;">
                                <i class="fa-solid fa-user-shield" style="font-size:10px;"></i> Admin
                            </span>
                            @else
                            <span style="display:inline-flex;align-items:center;gap:5px;padding:4px 10px;background:rgba(16,185,129,.1);color:#059669;border-radius:20px;font-size:12px;font-weight:600;">
                                <i class="fa-solid fa-user-tie" style="font-size:10px;"></i> {{ ucfirst($user->role ?? 'Staff') }}
                            </span>
                            @endif
                        </td>
                        <td style="padding:14px 16px;">
                            @if($user->is_active)
                            <span style="display:inline-flex;align-items:center;gap:5px;padding:4px 10px;background:rgba(16,185,129,.1);color:#059669;border-radius:20px;font-size:12px;font-weight:600;">
                                <span style="width:6px;height:6px;border-radius:50%;background:#10b981;display:inline-block;"></span> Active
                            </span>
                            @else
                            <span style="display:inline-flex;align-items:center;gap:5px;padding:4px 10px;background:rgba(239,68,68,.1);color:#dc2626;border-radius:20px;font-size:12px;font-weight:600;">
                                <span style="width:6px;height:6px;border-radius:50%;background:#ef4444;display:inline-block;"></span> Disabled
                            </span>
                            @endif
                        </td>
                        <td style="padding:14px 16px;color:var(--text-muted);">
                            {{ $user->created_at ? \Carbon\Carbon::parse($user->created_at)->format('d M Y') : '—' }}
                        </td>
                        <td style="padding:14px 22px;text-align:right;">
                            <div style="display:inline-flex;gap:6px;align-items:center;">

                                <!-- Edit Button -->
                                <button type="button"
                                    class="btn-edit"
                                    data-id="{{ $user->id }}"
                                    data-name="{{ $user->name }}"
                                    data-email="{{ $user->email }}"
                                    data-role="{{ $user->role }}"
                                    title="Edit User"
                                    style="width:32px;height:32px;border-radius:7px;border:1.5px solid var(--border);background:#fff;color:var(--primary);cursor:pointer;font-size:13px;display:inline-flex;align-items:center;justify-content:center;transition:all .2s;">
                                    <i class="fa-solid fa-pen"></i>
                                </button>

                                @if($user->id !== auth()->id())
                                <!-- Toggle Active -->
                                <form method="POST" action="{{ route('users.toggle-active', $user) }}" style="display:inline;margin:0;">
                                    @csrf
                                    <button type="submit"
                                        title="{{ $user->is_active ? 'Disable User' : 'Enable User' }}"
                                        style="width:32px;height:32px;border-radius:7px;border:1.5px solid var(--border);background:#fff;color:{{ $user->is_active ? '#f59e0b' : '#10b981' }};cursor:pointer;font-size:13px;display:inline-flex;align-items:center;justify-content:center;transition:all .2s;">
                                        <i class="fa-solid {{ $user->is_active ? 'fa-ban' : 'fa-circle-check' }}"></i>
                                    </button>
                                </form>

                                <!-- Delete -->
                                <form method="POST" action="{{ route('users.destroy', $user) }}" style="display:inline;margin:0;" class="delete-form">
                                    @csrf @method('DELETE')
                                    <input type="hidden" name="_user_name" value="{{ $user->name }}">
                                    <button type="submit"
                                        title="Delete User"
                                        style="width:32px;height:32px;border-radius:7px;border:1.5px solid var(--border);background:#fff;color:#ef4444;cursor:pointer;font-size:13px;display:inline-flex;align-items:center;justify-content:center;transition:all .2s;">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="padding:48px;text-align:center;color:var(--text-muted);">
                            <i class="fa-solid fa-users-slash" style="font-size:32px;opacity:.3;display:block;margin-bottom:10px;"></i>
                            No users found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ── Create User Modal ── -->
<div id="createModal" style="display:none;position:fixed;inset:0;z-index:1000;background:rgba(0,0,0,.55);backdrop-filter:blur(4px);align-items:center;justify-content:center;padding:20px;">
    <div style="background:#fff;border-radius:16px;width:100%;max-width:460px;box-shadow:0 24px 60px rgba(0,0,0,.2);">
        <div style="padding:22px 24px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;">
            <h3 style="font-size:16px;font-weight:700;color:var(--text);"><i class="fa-solid fa-user-plus" style="color:var(--primary);margin-right:8px;"></i>Add New User</h3>
            <button type="button" id="closeCreateBtn" style="background:none;border:none;font-size:20px;color:var(--text-muted);cursor:pointer;line-height:1;padding:4px 8px;">&times;</button>
        </div>
        <form method="POST" action="{{ route('users.store') }}" style="padding:24px;" id="createForm">
            @csrf
            <div style="margin-bottom:16px;">
                <label style="display:block;font-size:13px;font-weight:600;color:var(--text);margin-bottom:7px;">Full Name <span style="color:var(--danger);">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" placeholder="e.g. Jash Godhasara"
                    style="width:100%;padding:10px 14px;border:1.5px solid var(--border);border-radius:9px;font-size:14px;font-family:inherit;outline:none;color:var(--text);" required>
                @error('name')<p style="font-size:12px;color:var(--danger);margin-top:4px;">{{ $message }}</p>@enderror
            </div>
            <div style="margin-bottom:16px;">
                <label style="display:block;font-size:13px;font-weight:600;color:var(--text);margin-bottom:7px;">Email Address <span style="color:var(--danger);">*</span></label>
                <input type="email" name="email" value="{{ old('email') }}" placeholder="user@company.com"
                    style="width:100%;padding:10px 14px;border:1.5px solid var(--border);border-radius:9px;font-size:14px;font-family:inherit;outline:none;color:var(--text);" required>
                @error('email')<p style="font-size:12px;color:var(--danger);margin-top:4px;">{{ $message }}</p>@enderror
            </div>
            <div style="margin-bottom:16px;">
                <label style="display:block;font-size:13px;font-weight:600;color:var(--text);margin-bottom:7px;">Role <span style="color:var(--danger);">*</span></label>
                <select name="role" style="width:100%;padding:10px 14px;border:1.5px solid var(--border);border-radius:9px;font-size:14px;font-family:inherit;outline:none;color:var(--text);background:#fff;cursor:pointer;" required>
                    <option value="staff" {{ old('role') === 'staff' ? 'selected' : '' }}>Staff / Employee</option>
                    <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                </select>
                @error('role')<p style="font-size:12px;color:var(--danger);margin-top:4px;">{{ $message }}</p>@enderror
            </div>
            <div style="margin-bottom:24px;">
                <label style="display:block;font-size:13px;font-weight:600;color:var(--text);margin-bottom:7px;">Password <span style="color:var(--danger);">*</span></label>
                <input type="password" name="password" placeholder="Min. 8 characters"
                    style="width:100%;padding:10px 14px;border:1.5px solid var(--border);border-radius:9px;font-size:14px;font-family:inherit;outline:none;color:var(--text);" required minlength="8">
                @error('password')<p style="font-size:12px;color:var(--danger);margin-top:4px;">{{ $message }}</p>@enderror
            </div>
            <div style="display:flex;gap:10px;justify-content:flex-end;">
                <button type="button" id="closeCreateBtn2" style="padding:10px 20px;background:#fff;border:1px solid var(--border);border-radius:9px;font-size:14px;font-weight:500;cursor:pointer;font-family:inherit;color:var(--text-muted);">Cancel</button>
                <button type="submit" style="padding:10px 22px;background:linear-gradient(135deg,var(--primary),var(--primary-dark));color:#fff;border:none;border-radius:9px;font-size:14px;font-weight:600;cursor:pointer;font-family:inherit;">
                    <i class="fa-solid fa-user-plus" style="margin-right:6px;"></i> Create User
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ── Edit User Modal ── -->
<div id="editModal" style="display:none;position:fixed;inset:0;z-index:1000;background:rgba(0,0,0,.55);backdrop-filter:blur(4px);align-items:center;justify-content:center;padding:20px;">
    <div style="background:#fff;border-radius:16px;width:100%;max-width:460px;box-shadow:0 24px 60px rgba(0,0,0,.2);">
        <div style="padding:22px 24px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;">
            <h3 style="font-size:16px;font-weight:700;color:var(--text);"><i class="fa-solid fa-user-pen" style="color:var(--primary);margin-right:8px;"></i>Edit User</h3>
            <button type="button" id="closeEditBtn" style="background:none;border:none;font-size:20px;color:var(--text-muted);cursor:pointer;line-height:1;padding:4px 8px;">&times;</button>
        </div>
        <form method="POST" id="editForm" style="padding:24px;">
            @csrf @method('PUT')
            <div style="margin-bottom:16px;">
                <label style="display:block;font-size:13px;font-weight:600;color:var(--text);margin-bottom:7px;">Full Name <span style="color:var(--danger);">*</span></label>
                <input type="text" name="name" id="edit_name" placeholder="e.g. Jash Godhasara"
                    style="width:100%;padding:10px 14px;border:1.5px solid var(--border);border-radius:9px;font-size:14px;font-family:inherit;outline:none;color:var(--text);" required>
            </div>
            <div style="margin-bottom:16px;">
                <label style="display:block;font-size:13px;font-weight:600;color:var(--text);margin-bottom:7px;">Email Address <span style="color:var(--danger);">*</span></label>
                <input type="email" name="email" id="edit_email" placeholder="user@company.com"
                    style="width:100%;padding:10px 14px;border:1.5px solid var(--border);border-radius:9px;font-size:14px;font-family:inherit;outline:none;color:var(--text);" required>
            </div>
            <div style="margin-bottom:16px;">
                <label style="display:block;font-size:13px;font-weight:600;color:var(--text);margin-bottom:7px;">Role <span style="color:var(--danger);">*</span></label>
                <select name="role" id="edit_role" style="width:100%;padding:10px 14px;border:1.5px solid var(--border);border-radius:9px;font-size:14px;font-family:inherit;outline:none;color:var(--text);background:#fff;cursor:pointer;" required>
                    <option value="staff">Staff / Employee</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <div style="margin-bottom:24px;">
                <label style="display:block;font-size:13px;font-weight:600;color:var(--text);margin-bottom:7px;">
                    Password <span style="font-size:11px;color:var(--text-muted);font-weight:400;">(leave blank to keep current)</span>
                </label>
                <input type="password" name="password" id="edit_password" placeholder="Leave blank to keep current"
                    style="width:100%;padding:10px 14px;border:1.5px solid var(--border);border-radius:9px;font-size:14px;font-family:inherit;outline:none;color:var(--text);">
            </div>
            <div style="display:flex;gap:10px;justify-content:flex-end;">
                <button type="button" id="closeEditBtn2" style="padding:10px 20px;background:#fff;border:1px solid var(--border);border-radius:9px;font-size:14px;font-weight:500;cursor:pointer;font-family:inherit;color:var(--text-muted);">Cancel</button>
                <button type="submit" style="padding:10px 22px;background:linear-gradient(135deg,var(--primary),var(--primary-dark));color:#fff;border:none;border-radius:9px;font-size:14px;font-weight:600;cursor:pointer;font-family:inherit;">
                    <i class="fa-solid fa-floppy-disk" style="margin-right:6px;"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.user-row:hover { background: rgba(99,102,241,.03); }
.btn-edit:hover { background: var(--primary) !important; color: #fff !important; border-color: var(--primary) !important; }
</style>

<script>
(function() {
    // ── Modal helpers ──────────────────────────────────────────
    function showModal(id) {
        var el = document.getElementById(id);
        el.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
    function hideModal(id) {
        var el = document.getElementById(id);
        el.style.display = 'none';
        document.body.style.overflow = '';
    }

    // ── Create Modal ──────────────────────────────────────────
    document.getElementById('openCreateBtn').addEventListener('click', function() {
        showModal('createModal');
    });
    document.getElementById('closeCreateBtn').addEventListener('click', function() {
        hideModal('createModal');
    });
    document.getElementById('closeCreateBtn2').addEventListener('click', function() {
        hideModal('createModal');
    });
    document.getElementById('createModal').addEventListener('click', function(e) {
        if (e.target === this) hideModal('createModal');
    });

    // ── Edit Modal ──────────────────────────────────────────
    document.querySelectorAll('.btn-edit').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var id    = this.dataset.id;
            var name  = this.dataset.name;
            var email = this.dataset.email;
            var role  = this.dataset.role;

            document.getElementById('edit_name').value    = name;
            document.getElementById('edit_email').value   = email;
            document.getElementById('edit_role').value    = role;
            document.getElementById('edit_password').value = '';
            document.getElementById('editForm').action    = '/users/' + id;

            showModal('editModal');
        });
    });
    document.getElementById('closeEditBtn').addEventListener('click', function() {
        hideModal('editModal');
    });
    document.getElementById('closeEditBtn2').addEventListener('click', function() {
        hideModal('editModal');
    });
    document.getElementById('editModal').addEventListener('click', function(e) {
        if (e.target === this) hideModal('editModal');
    });

    // ── Delete confirm ──────────────────────────────────────
    document.querySelectorAll('.delete-form').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            var name = this.querySelector('[name="_user_name"]').value;
            if (!confirm('Delete user "' + name + '"?\n\nThis cannot be undone.')) {
                e.preventDefault();
            }
        });
    });

    // ── Keyboard ESC closes modals ──────────────────────────
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            hideModal('createModal');
            hideModal('editModal');
        }
    });

    // ── Auto-open on validation error ──────────────────────
    @if($errors->any() && old('_method') === null)
        showModal('createModal');
    @elseif($errors->any() && old('_method') === 'PUT')
        showModal('editModal');
    @endif
})();
</script>
@endsection
