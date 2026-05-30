@php
use Illuminate\Support\Str;

$selectedIds = array_map('intval', $selectedModuleIds ?? []);

$actions = [
    'view' => 'View',
    'add' => 'Add',
    'edit' => 'Edit',
    'update' => 'Update',
    'delete' => 'Delete',
];

$actionFromModuleName = function (?string $name): string {
    $n = trim((string) $name);
    if ($n === '') {
        return 'view';
    }
    if (preg_match('/^delete\s+/i', $n)) {
        return 'delete';
    }
    if (preg_match('/^update\s+/i', $n)) {
        return 'update';
    }
    if (preg_match('/^edit\s+/i', $n)) {
        return 'edit';
    }
    if (preg_match('/^add\s+/i', $n)) {
        return 'add';
    }
    return 'view';
};

$baseKeyFrom = function (?string $name): string {
    $n = trim((string) $name);
    if ($n === '') {
        return 'module';
    }
    $x = preg_replace('/^(add|edit|update|delete)\s+/i', '', $n);
    $x = preg_replace('/\s+(add|edit|update|delete)$/i', '', (string) $x);
    $x = trim((string) $x);
    return Str::lower($x !== '' ? $x : $n);
};

$displayNameFrom = function (?string $name, string $fallbackKey): string {
    $n = trim((string) $name);
    if ($n === '') {
        return Str::title(str_replace(['-', '_'], ' ', $fallbackKey));
    }
    $x = preg_replace('/^(add|edit|update|delete)\s+/i', '', $n);
    $x = preg_replace('/\s+(add|edit|update|delete)$/i', '', (string) $x);
    $x = trim((string) $x);
    return $x !== '' ? $x : $n;
};

$rowsByCategory = [];
foreach (($moduleCategories ?? []) as $category) {
    $catKey = (string) $category->getKey();
    $rowsByCategory[$catKey] = [
        'category' => $category,
        'rows' => [],
    ];
    foreach (($category->modules ?? []) as $module) {
        $name = $module->module_name ?? '';
        $action = $actionFromModuleName($name);
        $baseKey = $baseKeyFrom($name);
        if (!isset($rowsByCategory[$catKey]['rows'][$baseKey])) {
            $rowsByCategory[$catKey]['rows'][$baseKey] = [
                'display' => $displayNameFrom($name, $baseKey),
                'modules' => [],
            ];
        }
        if ($action === 'view' || !isset($rowsByCategory[$catKey]['rows'][$baseKey]['modules']['view'])) {
            if ($action === 'view') {
                $rowsByCategory[$catKey]['rows'][$baseKey]['display'] = $displayNameFrom($name, $baseKey);
            }
        }
        $rowsByCategory[$catKey]['rows'][$baseKey]['modules'][$action] = $module;
    }
}

$totalModules = 0;
$totalPermissions = 0;
foreach ($rowsByCategory as $bucket) {
    foreach ($bucket['rows'] as $row) {
        $totalModules++;
        foreach (array_keys($actions) as $actionKey) {
            if (!empty($row['modules'][$actionKey])) {
                $totalPermissions++;
            }
        }
    }
}
@endphp

@push('styles')
<style>
    .mp-toolbar { display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; }
    .mp-meta { font-size: 12px; color: #6c757d; }
    .mp-meta strong { color:#212529; }
    .mp-accordion .accordion-button { padding: .75rem 1rem; }
    .mp-accordion .accordion-button:not(.collapsed) { background: #f8f9fa; color: #212529; box-shadow: none; }
    .mp-category-title { font-weight: 600; }
    .mp-table { width:100%; margin:0; }
    .mp-table th {
        font-size: 12px;
        color: #212529;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        padding-top: 0.75rem;
        padding-bottom: 0.75rem;
        border-bottom: 2px solid #dee2e6;
    }
    .mp-table th.module-col-head {
        color: #212529;
        font-size: 13px;
        text-transform: none;
        letter-spacing: 0;
    }
    .mp-action-label {
        display: block;
        color: #0d3b66;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        line-height: 1.2;
    }
    .mp-table th.action-col { text-align: center; }
    .mp-table td, .mp-table th { vertical-align: middle; }
    .mp-table td.module-col { width: 38%; min-width: 200px; }
    .mp-table td.action-col, .mp-table th.action-col { width: 12.4%; text-align: center; }
    .mp-row-title { font-weight: 600; color: #212529; font-size: 14px; margin: 0; }
    .mp-action-empty { color: #ced4da; font-size: 15px; user-select: none; font-weight: 600; }
    .mp-sticky-head { position: sticky; top: 0; z-index: 2; box-shadow: 0 2px 0 #dee2e6; }
    .mp-sticky-head th { background: #e9ecef !important; color: #212529 !important; }
    .mp-table .form-check-input {
        width: 1.25rem;
        height: 1.25rem;
        margin: 0;
        border: 2px solid #495057;
        cursor: pointer;
        background-color: #fff;
        flex-shrink: 0;
    }
    .mp-table .form-check-input:checked {
        background-color: #0d6efd;
        border-color: #0d6efd;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20'%3e%3cpath fill='none' stroke='%23fff' stroke-linecap='round' stroke-linejoin='round' stroke-width='3' d='M6 10l3 3l6-6'/%3e%3c/svg%3e");
    }
    .mp-table .form-check-input:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }
    .mp-action-cell { padding: 0.65rem 0.25rem !important; }
    .mp-scroll { max-height: 520px; overflow:auto; border: 1px solid rgba(0,0,0,.06); border-radius: 12px; }
    .mp-scroll::-webkit-scrollbar { height: 10px; width: 10px; }
    .mp-scroll::-webkit-scrollbar-thumb { background: rgba(0,0,0,.15); border-radius: 10px; }
</style>
@endpush

<div class="mp-toolbar mb-3">
    <div class="d-flex align-items-center gap-3 flex-wrap">
        <h6 class="mb-0 fw-semibold">
            <i class="bi bi-grid me-2"></i>Module Permissions
        </h6>
        <div class="mp-meta">
            <span class="me-3">Total Modules: <strong id="mpTotalModules">{{ $totalModules }}</strong></span>
            <span>Total Permissions: <strong id="mpTotalPermissions">{{ $totalPermissions }}</strong></span>
        </div>
    </div>
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <button type="button" class="btn btn-sm btn-outline-secondary" id="mpExpandAll">Expand All</button>
        <div class="form-check form-switch m-0">
            <input class="form-check-input" type="checkbox" id="selectAllModules">
            <label class="form-check-label" for="selectAllModules">Select All</label>
        </div>
    </div>
</div>

<div class="accordion mp-accordion" id="modulePermissionsAccordion">
    @foreach($rowsByCategory as $catKey => $bucket)
        @php
            $category = $bucket['category'];
            $rows = $bucket['rows'];
            $moduleCount = count($rows);
            $catName = $category->category_name ?? 'Uncategorized';
            $catId = 'mpCat_' . preg_replace('/[^a-zA-Z0-9_]/', '_', (string) $catKey);
        @endphp

        <div class="accordion-item border-0 mb-2 shadow-sm rounded-4 overflow-hidden">
            <h2 class="accordion-header" id="{{ $catId }}_head">
                <button class="accordion-button fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $catId }}_body" aria-expanded="true" aria-controls="{{ $catId }}_body">
                    <span class="mp-category-title">{{ $catName }}</span>
                    <span class="ms-2 text-muted small fw-normal">({{ $moduleCount }} {{ $moduleCount === 1 ? 'module' : 'modules' }})</span>
                </button>
            </h2>
            <div id="{{ $catId }}_body" class="accordion-collapse collapse show" aria-labelledby="{{ $catId }}_head">
                <div class="accordion-body pt-2">
                    <div class="mp-scroll">
                        <table class="table table-sm table-hover mp-table mb-0">
                            <thead class="mp-sticky-head">
                                <tr>
                                    <th class="ps-3 module-col-head">Module / Sub Module</th>
                                    @foreach($actions as $aKey => $aLabel)
                                        <th class="action-col">
                                            <span class="mp-action-label">{{ $aLabel }}</span>
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rows as $baseKey => $row)
                                    @php
                                        $rowId = $catId . '__' . preg_replace('/[^a-zA-Z0-9_]/', '_', (string) $baseKey);
                                    @endphp
                                    <tr data-mp-row="{{ $rowId }}">
                                        <td class="module-col ps-3">
                                            <span class="mp-row-title">{{ $row['display'] }}</span>
                                        </td>
                                        @foreach($actions as $aKey => $aLabel)
                                            @php
                                                $m = $row['modules'][$aKey] ?? null;
                                                $id = $m?->id ? (int) $m->id : null;
                                                $checked = $id ? in_array($id, $selectedIds, true) : false;
                                            @endphp
                                            <td class="action-col mp-action-cell">
                                                @if($id)
                                                    <input
                                                        type="checkbox"
                                                        class="form-check-input module-privilege-cb mp-cb"
                                                        name="module_ids[]"
                                                        value="{{ $id }}"
                                                        data-action="{{ $aKey }}"
                                                        data-row="{{ $rowId }}"
                                                        data-cat="{{ $catId }}"
                                                        {{ $checked ? 'checked' : '' }}
                                                    >
                                                @else
                                                    <span class="mp-action-empty" title="No {{ $aLabel }} permission defined in modules">—</span>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                                @if($moduleCount === 0)
                                    <tr>
                                        <td colspan="{{ 1 + count($actions) }}" class="text-muted small px-3 py-3">No modules found in this category.</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    @if(empty($moduleCategories) || (is_countable($moduleCategories) && count($moduleCategories) === 0))
        <p class="text-muted small">No modules available. Add modules first.</p>
    @endif
</div>

@push('scripts')
<script>
    (function () {
        function qs(sel, root) { return (root || document).querySelector(sel); }
        function qsa(sel, root) { return Array.from((root || document).querySelectorAll(sel)); }

        function setChecked(list, value) {
            list.forEach(function (el) { if (!el.disabled) el.checked = value; });
        }

        function getAllPermissionCheckboxes() {
            return qsa('input.mp-cb[type="checkbox"][name="module_ids[]"]');
        }

        function updateMainSelectAll() {
            var all = getAllPermissionCheckboxes();
            var selectAll = qs('#selectAllModules');
            if (!selectAll) return;
            if (!all.length) { selectAll.checked = false; selectAll.indeterminate = false; return; }
            var checked = all.filter(function (x) { return x.checked; }).length;
            selectAll.indeterminate = checked > 0 && checked < all.length;
            selectAll.checked = checked === all.length;
        }

        document.addEventListener('change', function (e) {
            var t = e.target;
            if (t && t.id === 'selectAllModules') {
                setChecked(getAllPermissionCheckboxes(), t.checked);
                updateMainSelectAll();
                return;
            }
            if (t && t.classList.contains('mp-cb')) {
                updateMainSelectAll();
            }
        });

        var expandBtn = qs('#mpExpandAll');
        if (expandBtn) {
            expandBtn.dataset.state = 'expanded';
            expandBtn.textContent = 'Collapse All';
            expandBtn.addEventListener('click', function () {
                var items = qsa('#modulePermissionsAccordion .accordion-collapse');
                var shouldExpand = expandBtn.dataset.state !== 'expanded';
                items.forEach(function (el) {
                    if (shouldExpand) el.classList.add('show');
                    else el.classList.remove('show');
                });
                qsa('#modulePermissionsAccordion .accordion-button').forEach(function (btn) {
                    if (shouldExpand) btn.classList.remove('collapsed');
                    else btn.classList.add('collapsed');
                    btn.setAttribute('aria-expanded', shouldExpand ? 'true' : 'false');
                });
                expandBtn.textContent = shouldExpand ? 'Collapse All' : 'Expand All';
                expandBtn.dataset.state = shouldExpand ? 'expanded' : 'collapsed';
            });
        }

        updateMainSelectAll();
    })();
</script>
@endpush
