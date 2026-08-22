@extends('layouts.app')
@section('title', 'Materials')
@section('page-title', 'Materials & Inventory')

@section('content')
<style>
.material-thumb {
    width: 44px;
    height: 44px;
    border-radius: 8px;
    object-fit: cover;
    border: 1px solid var(--border);
    cursor: pointer;
    transition: transform 0.2s, box-shadow 0.2s;
}
.material-thumb:hover {
    transform: scale(1.1);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
.material-thumb-placeholder {
    width: 44px;
    height: 44px;
    border-radius: 8px;
    background: #f1f5f9;
    color: #94a3b8;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    border: 1px dashed var(--border);
}
.image-preview-container {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-top: 8px;
    padding: 10px;
    border: 1.5px dashed var(--border);
    border-radius: var(--radius-sm);
    background: #fafafa;
}
.preview-img-box {
    width: 56px;
    height: 56px;
    border-radius: 8px;
    object-fit: cover;
    border: 1px solid var(--border);
    display: none;
}
.photo-modal-img {
    max-width: 100%;
    max-height: 70vh;
    border-radius: 12px;
    box-shadow: var(--shadow-lg);
    display: block;
    margin: 0 auto;
    object-fit: contain;
}
</style>

{{-- Live Plastic Market Rates Strip --}}
<div class="card" style="margin-bottom:20px;background:linear-gradient(135deg,#0f172a,#1e293b);border:1px solid rgba(59,130,246,0.3);color:#fff;border-radius:12px;padding:14px 18px;overflow:hidden;max-width:100%;box-sizing:border-box;">
    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;margin-bottom:12px;">
        <div style="display:flex;align-items:center;gap:10px;">
            <div style="width:32px;height:32px;border-radius:8px;background:linear-gradient(135deg,#3b82f6,#2563eb);display:flex;align-items:center;justify-content:center;">
                <i class="fa-solid fa-chart-line" style="color:#fff;font-size:14px;"></i>
            </div>
            <div>
                <div style="display:flex;align-items:center;gap:8px;">
                    <span style="font-size:14px;font-weight:700;letter-spacing:0.2px;">Online Polymer Live Market Rates</span>
                    <span style="display:inline-flex;align-items:center;gap:4px;font-size:10px;font-weight:700;padding:2px 7px;border-radius:20px;background:rgba(16,185,129,0.15);color:#34d399;border:1px solid rgba(52,211,153,0.3);">
                        <span style="width:5px;height:5px;border-radius:50%;background:#34d399;"></span> LIVE FEED
                    </span>
                </div>
                <div style="font-size:11px;color:rgba(255,255,255,0.6);" id="mat-market-sync">Live 3MinAPI feed for inventory costing reference</div>
            </div>
        </div>
        <button type="button" onclick="fetchMaterialMarketRates(true)" class="btn btn-primary btn-sm" style="background:#2563eb;border:none;padding:4px 12px;font-size:11px;">
            <i class="fa-solid fa-arrows-rotate" id="mat-spinner"></i> Refresh Rates
        </button>
    </div>
    <div id="mat-market-rates" style="display:flex;gap:10px;overflow-x:auto;padding-bottom:4px;scrollbar-width:thin;">
        @if(isset($polymerRates['items']) && count($polymerRates['items']) > 0)
            @foreach($polymerRates['items'] as $item)
                @php
                    $isUp = ($item['trend'] ?? '') === 'up' || (isset($item['change']) && str_starts_with($item['change'], '+') && $item['change'] !== '+0.00');
                    $isDown = ($item['trend'] ?? '') === 'down' || (isset($item['change']) && str_starts_with($item['change'], '-'));
                    $trendColor = $isUp ? '#34d399' : ($isDown ? '#f87171' : '#94a3b8');
                    $trendIcon = $isUp ? 'fa-arrow-up' : ($isDown ? 'fa-arrow-down' : 'fa-minus');
                @endphp
                <div style="background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.1);border-radius:8px;padding:8px 12px;min-width:170px;flex-shrink:0;">
                    <div style="font-size:10px;color:#93c5fd;font-weight:600;text-transform:uppercase;">{{ $item['category'] ?? 'Polymer' }}</div>
                    <div style="font-size:12px;font-weight:700;color:#fff;margin:2px 0 4px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $item['material_name'] }}</div>
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <span style="font-size:14px;font-weight:800;color:#f8fafc;">₹{{ number_format((float)($item['current_price'] ?? 0), 2) }}<small style="font-size:10px;color:rgba(255,255,255,0.5);font-weight:500;">/{{ $item['unit'] ?? 'Kg' }}</small></span>
                        <span style="font-size:10px;color:{{ $trendColor }};font-weight:700;"><i class="fa-solid {{ $trendIcon }}"></i> {{ $item['change'] ?? '' }}</span>
                    </div>
                </div>
            @endforeach
        @endif
    </div>
</div>

<script>
async function fetchMaterialMarketRates(force = false) {
    const container = document.getElementById('mat-market-rates');
    const sync = document.getElementById('mat-market-sync');
    const spinner = document.getElementById('mat-spinner');
    if (spinner) spinner.classList.add('fa-spin');

    try {
        const url = force ? '/plastic-prices?refresh=1' : '/plastic-prices';
        const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
        const data = await res.json();

        if (data && data.items && data.items.length > 0) {
            let html = '';
            data.items.forEach(item => {
                const isUp = item.trend === 'up' || (item.change && item.change.startsWith('+') && item.change !== '+0.00');
                const isDown = item.trend === 'down' || (item.change && item.change.startsWith('-'));
                const trendColor = isUp ? '#34d399' : (isDown ? '#f87171' : '#94a3b8');
                const trendIcon = isUp ? 'fa-arrow-up' : (isDown ? 'fa-arrow-down' : 'fa-minus');

                html += `
                <div style="background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.1);border-radius:8px;padding:8px 12px;min-width:170px;flex-shrink:0;">
                    <div style="font-size:10px;color:#93c5fd;font-weight:600;text-transform:uppercase;">${item.category || 'Polymer'}</div>
                    <div style="font-size:12px;font-weight:700;color:#fff;margin:2px 0 4px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${item.material_name}</div>
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <span style="font-size:14px;font-weight:800;color:#f8fafc;">₹${Number(item.current_price).toFixed(2)}<small style="font-size:10px;color:rgba(255,255,255,0.5);font-weight:500;">/${item.unit || 'Kg'}</small></span>
                        <span style="font-size:10px;color:${trendColor};font-weight:700;"><i class="fa-solid ${trendIcon}"></i> ${item.change || ''}</span>
                    </div>
                </div>`;
            });
            container.innerHTML = html;
            if (sync && data.last_updated) {
                const time = new Date(data.last_updated).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                sync.innerHTML = `Live reference rates updated at ${time}`;
            }
        }
    } catch(e) {
        console.error(e);
    } finally {
        if (spinner) spinner.classList.remove('fa-spin');
    }
}
</script>

<div class="card" style="box-shadow:var(--shadow-sm); border:1px solid var(--border);">
    <div class="card-header" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; padding:14px 20px;">
        <h3 style="margin:0; font-size:15px; font-weight:700;"><i class="fa fa-boxes-stacked"></i> Materials List</h3>
        <div style="display:flex; gap:8px; flex-wrap:wrap;">
            <button type="button" class="btn btn-outline btn-sm" id="btn-sync-api" onclick="syncMaterialsFromApi()" style="font-weight:600;">
                <i class="fa-solid fa-cloud-arrow-down" id="sync-icon"></i> Sync All from Live API
            </button>
            <button class="btn btn-primary btn-sm" onclick="openAddMaterialModal()" style="font-weight:600;">
                <i class="fa fa-plus"></i> Add Material
            </button>
        </div>
    </div>
    <div class="card-body" style="padding:0">
        @if($materials->isEmpty())
        <div class="empty-state" style="padding:40px 20px; text-align:center;"><i class="fa fa-boxes-stacked" style="font-size:32px; color:var(--text-muted); margin-bottom:10px;"></i><p>No materials added yet.</p></div>
        @else
        <div class="table-wrap" style="overflow-x:auto; width:100%;">
            <table style="width:100%; border-collapse:collapse; min-width:700px;">
                <thead>
                    <tr style="border-bottom:1px solid var(--border);">
                        <th style="width:50px; padding:12px 16px;">#</th>
                        <th style="width:60px; padding:12px;">Photo</th>
                        <th style="width:130px; padding:12px;">Type</th>
                        <th style="padding:12px;">Material Name</th>
                        <th style="width:70px; padding:12px;">Unit</th>
                        <th style="width:180px; padding:12px;">Grade / Details</th>
                        <th style="width:120px; padding:12px; text-align:right;">Stock</th>
                        <th style="width:100px; padding:12px 16px; text-align:center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($materials as $m)
                <tr style="border-bottom:1px solid var(--border);">
                    <td style="padding:12px 16px; color:var(--text-muted); font-size:12px;">{{ $m->id }}</td>
                    <td style="padding:12px;">
                        @if($m->image)
                            <img src="{{ asset($m->image) }}" alt="{{ $m->name }}" class="material-thumb" onclick="viewPhoto('{{ asset($m->image) }}', '{{ e($m->name) }}')">
                        @else
                            <div class="material-thumb-placeholder">
                                <i class="fa fa-box-open"></i>
                            </div>
                        @endif
                    </td>
                    <td style="padding:12px;">
                        <span class="badge {{ $m->type === 'Raw Material' ? 'badge-orange' : ($m->type === 'Additive' ? 'badge-blue' : 'badge-green') }}">
                            {{ $m->type }}
                        </span>
                    </td>
                    <td style="padding:12px; font-weight:700; color:var(--text);">{{ $m->name }}</td>
                    <td style="padding:12px; color:var(--text-muted);">{{ $m->unit ?? 'Kg' }}</td>
                    <td style="padding:12px; font-size:12.5px;">
                        <div style="font-weight:600; color:var(--text);">{{ $m->grade_variation ?? '—' }}</div>
                        @if($m->temp || $m->size)
                            <div style="font-size:11px; color:var(--text-muted); margin-top:2px;">
                                @if($m->temp)<span>Temp: {{ $m->temp }}</span>@endif
                                @if($m->temp && $m->size) • @endif
                                @if($m->size)<span>Size: {{ $m->size }}</span>@endif
                            </div>
                        @endif
                    </td>
                    <td style="padding:12px; text-align:right; font-weight:800; color:var(--text);">
                        {{ number_format((float)$m->stock_quantity, 2) }}
                        <span style="font-size:11px; font-weight:500; color:var(--text-muted);">{{ $m->unit ?? 'Kg' }}</span>
                    </td>
                    <td style="padding:12px 16px; text-align:center;">
                        <div style="display:inline-flex; gap:6px;">
                            <button class="btn btn-outline btn-sm btn-icon"
                                title="Edit"
                                onclick="editMaterial({{ $m->id }}, {{ json_encode($m->type) }}, {{ json_encode($m->name) }}, {{ json_encode($m->unit) }}, {{ json_encode($m->grade_variation) }}, {{ json_encode($m->temp) }}, {{ json_encode($m->size) }}, {{ $m->stock_quantity }}, {{ json_encode($m->image ? asset($m->image) : null) }})">
                                <i class="fa fa-pen"></i>
                            </button>
                            <button class="btn btn-danger btn-sm btn-icon"
                                title="Delete"
                                onclick="deleteRecord('{{ route('materials.destroy', $m) }}', 'material')">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

<!-- Add Modal -->
<div class="modal-overlay" id="addMaterialModal">
    <div class="modal">
        <div class="modal-header">
            <h3>Add Material</h3>
            <button class="modal-close" onclick="closeModal('addMaterialModal')">✕</button>
        </div>
        <form id="addMaterialForm" enctype="multipart/form-data">
            <div class="modal-body">
                <div class="form-row cols-2">
                    <div class="form-group">
                        <label>Type *</label>
                        <select name="type" required>
                            <option value="">Select type</option>
                            <option value="Raw Material">Raw Material</option>
                            <option value="Additive">Additive</option>
                            <option value="Final Product">Final Product</option>
                        </select>
                    </div>
                    <div class="form-group"><label>Name *</label><input type="text" name="name" required></div>
                </div>
                <div class="form-row cols-2">
                    <div class="form-group"><label>Unit</label><input type="text" name="unit" placeholder="Kg, Pcs..."></div>
                    <div class="form-group"><label>Stock Quantity</label><input type="number" name="stock_quantity" step="0.01" value="0"></div>
                </div>
                <div class="form-row cols-3">
                    <div class="form-group"><label>Grade/Variation</label><input type="text" name="grade_variation"></div>
                    <div class="form-group"><label>Temp</label><input type="text" name="temp"></div>
                    <div class="form-group"><label>Size</label><input type="text" name="size"></div>
                </div>
                <div class="form-group">
                    <label><i class="fa fa-camera"></i> Material Photo / Image</label>
                    <input type="file" name="image" id="add_mat_img_input" accept="image/*" onchange="previewMatImage(this, 'add_mat_preview')">
                    <div class="image-preview-container" id="add_mat_preview_container" style="display:none;">
                        <img id="add_mat_preview" class="preview-img-box" alt="Preview">
                        <span style="font-size:12px;color:var(--text-muted);">Selected material image</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('addMaterialModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Material</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal-overlay" id="editMaterialModal">
    <div class="modal">
        <div class="modal-header">
            <h3>Edit Material</h3>
            <button class="modal-close" onclick="closeModal('editMaterialModal')">✕</button>
        </div>
        <form id="editMaterialForm" enctype="multipart/form-data">
            <input type="hidden" name="_method" value="PUT">
            <div class="modal-body">
                <div class="form-row cols-2">
                    <div class="form-group">
                        <label>Type *</label>
                        <select id="em_type" name="type" required>
                            <option value="Raw Material">Raw Material</option>
                            <option value="Additive">Additive</option>
                            <option value="Final Product">Final Product</option>
                        </select>
                    </div>
                    <div class="form-group"><label>Name *</label><input type="text" id="em_name" name="name" required></div>
                </div>
                <div class="form-row cols-2">
                    <div class="form-group"><label>Unit</label><input type="text" id="em_unit" name="unit"></div>
                    <div class="form-group"><label>Stock Quantity</label><input type="number" id="em_stock" name="stock_quantity" step="0.01"></div>
                </div>
                <div class="form-row cols-3">
                    <div class="form-group"><label>Grade/Variation</label><input type="text" id="em_grade" name="grade_variation"></div>
                    <div class="form-group"><label>Temp</label><input type="text" id="em_temp" name="temp"></div>
                    <div class="form-group"><label>Size</label><input type="text" id="em_size" name="size"></div>
                </div>
                <div class="form-group">
                    <label><i class="fa fa-camera"></i> Material Photo / Image</label>
                    <input type="file" name="image" id="edit_mat_img_input" accept="image/*" onchange="previewMatImage(this, 'edit_mat_preview')">
                    <div class="image-preview-container" id="edit_mat_preview_container" style="display:none;">
                        <img id="edit_mat_preview" class="preview-img-box" alt="Preview">
                        <label style="font-size:12px;color:#dc2626;cursor:pointer;">
                            <input type="checkbox" name="remove_image" value="1" id="edit_mat_remove_chk"> Remove current photo
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('editMaterialModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Material</button>
            </div>
        </form>
    </div>
</div>

{{-- Photo Viewer Modal --}}
<div class="modal-overlay" id="photoModal" onclick="closePhotoModal(event)">
    <div class="modal" style="max-width:550px; background:rgba(255,255,255,0.98); backdrop-filter:blur(10px);">
        <div class="modal-header" style="border:none; padding-bottom:0;">
            <h3 id="photoModalTitle"><i class="fa fa-image"></i> Photo View</h3>
            <button class="modal-close" onclick="closeModal('photoModal')">✕</button>
        </div>
        <div class="modal-body" style="text-align:center; padding:20px;">
            <img id="photoModalImg" src="" alt="Photo" class="photo-modal-img">
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let editUrl = '';

function openAddMaterialModal() {
    document.getElementById('addMaterialForm').reset();
    document.getElementById('add_mat_preview_container').style.display = 'none';
    openModal('addMaterialModal');
}

function previewMatImage(input, previewId) {
    const preview = document.getElementById(previewId);
    const container = preview.parentElement;
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
            container.style.display = 'flex';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function viewPhoto(url, title) {
    document.getElementById('photoModalImg').src = url;
    document.getElementById('photoModalTitle').innerHTML = '<i class="fa fa-image"></i> ' + (title || 'Material Photo');
    openModal('photoModal');
}

function closePhotoModal(e) {
    if (e.target.id === 'photoModal') {
        closeModal('photoModal');
    }
}

document.getElementById('addMaterialForm').addEventListener('submit', function(e) {
    e.preventDefault();
    submitForm(this, '{{ route('materials.store') }}', 'POST');
});

function editMaterial(id, type, name, unit, grade, temp, size, stock, imageUrl) {
    editUrl = `/materials/${id}`;
    document.getElementById('em_type').value = type || 'Raw Material';
    document.getElementById('em_name').value = name || '';
    document.getElementById('em_unit').value = unit || '';
    document.getElementById('em_stock').value = stock || 0;
    document.getElementById('em_grade').value = grade || '';
    document.getElementById('em_temp').value = temp || '';
    document.getElementById('em_size').value = size || '';

    const preview = document.getElementById('edit_mat_preview');
    const container = document.getElementById('edit_mat_preview_container');
    const removeChk = document.getElementById('edit_mat_remove_chk');
    if (removeChk) removeChk.checked = false;

    if (imageUrl) {
        preview.src = imageUrl;
        preview.style.display = 'block';
        container.style.display = 'flex';
    } else {
        preview.src = '';
        preview.style.display = 'none';
        container.style.display = 'none';
    }

    openModal('editMaterialModal');
}

document.getElementById('editMaterialForm').addEventListener('submit', function(e) {
    e.preventDefault();
    submitForm(this, editUrl, 'PUT');
});

async function syncMaterialsFromApi() {
    const btn = document.getElementById('btn-sync-api');
    const icon = document.getElementById('sync-icon');
    if (btn) btn.disabled = true;
    if (icon) {
        icon.className = 'fa-solid fa-arrows-rotate fa-spin';
    }

    try {
        const res = await fetch('{{ route('materials.sync-api') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        });
        const data = await res.json();
        if (data.success) {
            alert(data.message || 'Materials synced successfully!');
            window.location.reload();
        } else {
            alert(data.message || 'Unable to sync materials.');
        }
    } catch (e) {
        console.error(e);
        alert('Network error while syncing materials from API.');
    } finally {
        if (btn) btn.disabled = false;
        if (icon) icon.className = 'fa-solid fa-cloud-arrow-down';
    }
}
</script>
@endsection
