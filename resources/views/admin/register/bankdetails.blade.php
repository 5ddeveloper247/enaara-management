{{-- STEP 5: Bank — draft first, saved accounts as cards below (family-style) --}}
@push('styles')
<style>
    #bank-draft-form .check-input[type="radio"] {
        appearance: radio;
        -webkit-appearance: radio;
        -moz-appearance: radio;
        width: 1.125rem;
        height: 1.125rem;
        margin-top: 0.2rem;
        flex-shrink: 0;
        background-color: #fff !important;
        border: 2px solid #012445 !important;
        box-shadow: none !important;
        accent-color: #012445;
    }
    #bank-draft-form .check-input[type="checkbox"] {
        width: 1.125rem;
        height: 1.125rem;
        margin-top: 0.2rem;
        flex-shrink: 0;
        background-color: #fff !important;
        border: 2px solid #012445 !important;
        box-shadow: none !important;
        accent-color: #012445;
    }
    #bank-draft-form .form-check,
    #bankListing .form-check {
        display: flex;
        align-items: flex-start;
        gap: 0.45rem;
    }
    .bank-card--editing > .card {
        border-color: #ffc107 !important;
        box-shadow: 0 0 0 2px rgba(255, 193, 7, 0.35);
    }
</style>
@endpush
<div class="step" id="step-5">
    <div class="section-title">
        <span>Section E — Bank Details</span>
    </div>

    <div id="bank-hidden-inputs" class="visually-hidden" aria-hidden="true"></div>

    <div class="card border-1 rounded-3 mb-4" id="bank-draft-form">
        <div class="card-body p-4">
            <div class="fw-semibold mb-3" id="bank-draft-title">New bank account</div>
            <div class="row g-2">
                <div class="col-12">
                    <label class="form-label">Account category <span class="text-danger">*</span></label>
                    <div class="d-flex flex-wrap gap-3 mt-1">
                        <div class="form-check d-flex align-items-center gap-2">
                            <input class="check-input" type="radio" id="draft_account_cat_personal" name="draft_account_category" value="personal">
                            <label class="form-check-label mb-0" for="draft_account_cat_personal">Personal Account</label>
                        </div>
                        <div class="form-check d-flex align-items-center gap-2">
                            <input class="check-input" type="radio" id="draft_account_cat_company" name="draft_account_category" value="company_operated">
                            <label class="form-check-label mb-0" for="draft_account_cat_company">Company Operated Account</label>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="draft_account_title">Account Title <span class="text-danger">*</span></label>
                    <input type="text" id="draft_account_title" class="form-control" maxlength="255" autocomplete="off">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="draft_account_no">Account No <span class="text-danger">*</span></label>
                    <input type="text" id="draft_account_no" class="form-control number-only" maxlength="24" inputmode="numeric" autocomplete="off">
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="draft_bank_name">Bank Name <span class="text-danger">*</span></label>
                    <input type="text" id="draft_bank_name" class="form-control" autocomplete="off">
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="draft_branch_address">Branch Address <span class="text-danger">*</span></label>
                    <input type="text" id="draft_branch_address" class="form-control" maxlength="500" autocomplete="off">
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="draft_iban">IBAN Number</label>
                    <input type="text" id="draft_iban" class="form-control text-uppercase" maxlength="34" placeholder="Optional" autocomplete="off">
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="draft_branch_code">Branch Code <span class="text-danger">*</span></label>
                    <input type="text" id="draft_branch_code" class="form-control" maxlength="50" autocomplete="off" placeholder="e.g. GL-102">
                </div>
                <div class="col-md-9 d-flex align-items-end">
                    <div class="w-100">
                        <div class="form-check d-flex align-items-center gap-2 h-100 mb-0">
                            <input class="check-input" type="checkbox" id="draft_is_salary_account" value="1">
                            <label class="form-check-label mb-0" for="draft_is_salary_account">Use for salary (payroll)</label>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">A/C Type <span class="text-danger">*</span></label>
                    <div class="d-flex flex-wrap gap-3 mt-1">
                        <div class="form-check d-flex align-items-center gap-2">
                            <input class="check-input" type="radio" id="draft_ac_saving" name="draft_account_type" value="Saving">
                            <label class="form-check-label mb-0" for="draft_ac_saving">Saving</label>
                        </div>
                        <div class="form-check d-flex align-items-center gap-2">
                            <input class="check-input" type="radio" id="draft_ac_current" name="draft_account_type" value="Current">
                            <label class="form-check-label mb-0" for="draft_ac_current">Current</label>
                        </div>
                    </div>
                </div>
                <div class="col-12 mt-2 d-flex flex-wrap gap-2">
                    <button type="button" class="btn btn-primary" id="btnSaveBankDraft">Save account</button>
                    <button type="button" class="btn btn-outline-secondary" id="btnClearBankDraft">Clear form</button>
                </div>
            </div>
        </div>
    </div>

    <div class="fw-semibold small text-muted mb-2">Saved accounts</div>
    <div id="bankListing" class="row g-3">
        <div id="bank-listing-empty" class="col-12 text-center text-muted small py-4 border rounded-3 bg-light bg-opacity-25">
            No accounts saved yet. Complete the form above and click <strong>Save account</strong>.
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    var hiddenWrap = document.getElementById('bank-hidden-inputs');
    var cardListing = document.getElementById('bankListing');
    var listingEmpty = document.getElementById('bank-listing-empty');
    var btnSave = document.getElementById('btnSaveBankDraft');
    var btnClear = document.getElementById('btnClearBankDraft');
    var draftTitleEl = document.getElementById('bank-draft-title');
    var draftEditingIndex = null;
    var saveBankSubsectionUrl = '{{ route('admin.employee.save_subsection') }}';
    var deleteBankDetailUrl = '{{ route('admin.employee.delete_bank_detail') }}';

    function bankCsrfToken() {
        var m = document.querySelector('meta[name="csrf-token"]');
        return m ? m.getAttribute('content') : '';
    }

    function clearBankDraftApiErrors() {
        var draft = document.getElementById('bank-draft-form');
        if (!draft) return;
        draft.querySelectorAll('.field-error-msg, .step-val-error').forEach(function (e) {
            e.remove();
        });
        draft.querySelectorAll('input, select, textarea').forEach(function (el) {
            el.classList.remove('is-invalid', 'is-invalid-step');
            el.style.borderColor = '';
            el.style.paddingRight = '';
            el.style.backgroundImage = '';
            el.style.backgroundRepeat = '';
            el.style.backgroundPosition = '';
            el.style.backgroundSize = '';
        });
        draft.querySelectorAll('div[class*="col-"]').forEach(function (col) {
            if (col.closest('#bank-draft-form') !== draft) return;
            col.classList.remove('is-invalid-step');
        });
    }

    function bankDraftMarkInvalid(targetEl, msg) {
        if (!targetEl || !msg) return;
        var tag = targetEl.tagName;
        var isControl = tag === 'INPUT' || tag === 'SELECT' || tag === 'TEXTAREA';
        var err = document.createElement('div');
        err.className = 'field-error-msg text-danger small mt-1 fw-bold';
        err.setAttribute('role', 'alert');
        err.textContent = msg;
        if (isControl) {
            targetEl.classList.add('is-invalid', 'is-invalid-step');
            targetEl.style.borderColor = '#dc3545';
            targetEl.style.paddingRight = 'calc(1.5em + 0.75rem)';
            targetEl.style.backgroundImage =
                'url("data:image/svg+xml,%3csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 12 12\' width=\'12\' height=\'12\' fill=\'none\' stroke=\'%23dc3545\'%3e%3ccircle cx=\'6\' cy=\'6\' r=\'4.5\'/%3e%3cpath stroke-linejoin=\'round\' d=\'M5.8 3.6h.4L6 6.5z\'/%3e%3ccircle cx=\'6\' cy=\'8.2\' r=\'.6\' fill=\'%23dc3545\' stroke=\'none\'/%3e%3c/svg%3e")';
            targetEl.style.backgroundRepeat = 'no-repeat';
            targetEl.style.backgroundPosition = 'right calc(0.375em + 0.1875rem) center';
            targetEl.style.backgroundSize = 'calc(0.75em + 0.375rem) calc(0.75em + 0.375rem)';
            var col = targetEl.closest('div[class*="col-"]');
            if (col) col.appendChild(err);
            else targetEl.insertAdjacentElement('afterend', err);
        } else {
            targetEl.classList.add('is-invalid-step');
            targetEl.appendChild(err);
        }
    }

    function showBankSubsectionFieldErrors(errors) {
        clearBankDraftApiErrors();
        if (!errors || typeof errors !== 'object') return 0;
        var draft = document.getElementById('bank-draft-form');
        if (!draft) return 0;

        var applied = 0;
        var map = {
            account_category: function () {
                var inp = draft.querySelector('input[name="draft_account_category"]');
                return inp ? inp.closest('.col-12') : null;
            },
            account_title: function () {
                return document.getElementById('draft_account_title');
            },
            account_no: function () {
                return document.getElementById('draft_account_no');
            },
            bank_name: function () {
                return document.getElementById('draft_bank_name');
            },
            branch_code: function () {
                return document.getElementById('draft_branch_code');
            },
            branch_address: function () {
                return document.getElementById('draft_branch_address');
            },
            iban: function () {
                return document.getElementById('draft_iban');
            },
            account_type: function () {
                var inp = draft.querySelector('input[name="draft_account_type"]');
                return inp ? inp.closest('.col-md-6') : null;
            },
            is_salary_account: function () {
                var inp = document.getElementById('draft_is_salary_account');
                return inp ? inp.closest('.col-12') : null;
            },
            bank_detail_id: function () {
                return document.getElementById('bank-draft-title');
            },
        };

        var firstScroll = null;
        Object.keys(errors).forEach(function (key) {
            var msgs = errors[key];
            if (!msgs || !msgs.length) return;
            var msg = String(msgs[0]);
            var baseKey = key;
            if (key.indexOf('.') !== -1) {
                var parts = key.split('.');
                baseKey = parts[parts.length - 1];
            }
            var getter = map[baseKey] || map[key];
            if (!getter) return;
            var el = getter();
            if (!el) return;
            bankDraftMarkInvalid(el, msg);
            applied++;
            if (!firstScroll) firstScroll = el.closest('div[class*="col-"]') || el;
        });

        if (firstScroll && firstScroll.scrollIntoView) {
            firstScroll.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        return applied;
    }

    function bankPayloadFromServer(b) {
        if (!b) return {};
        return {
            account_category: b.account_category || '',
            account_title: b.account_title || '',
            account_no: String(b.account_no || ''),
            bank_name: b.bank_name || '',
            branch_code: b.branch_code || '',
            branch_address: b.branch_address || '',
            iban: b.iban || '',
            account_type: b.account_type || '',
            is_salary_account: !!b.is_salary_account,
        };
    }

    function refreshBankSnapshotsSalaryFromHidden() {
        if (!hiddenWrap || !cardListing) return;
        cardListing.querySelectorAll('[data-bank-card]').forEach(function (col) {
            var idx = col.getAttribute('data-index');
            if (idx === null || idx === '') return;
            var wrap = hiddenWrap.querySelector('[data-bank-saved-entry][data-index="' + idx + '"]');
            var h = wrap ? wrap.querySelector('input[name*="[is_salary_account]"]') : null;
            var raw = col.getAttribute('data-bank-snapshot');
            if (!raw) return;
            try {
                var snap = JSON.parse(raw);
                snap.is_salary_account = !!(h && h.value === '1');
                col.setAttribute('data-bank-snapshot', JSON.stringify(snap));
            } catch (e) {}
        });
    }

    function applySalaryBankIdToAllRows(salaryBankId) {
        if (!hiddenWrap) return;
        hiddenWrap.querySelectorAll('[data-bank-saved-entry]').forEach(function (w) {
            var bid = w.getAttribute('data-bank-detail-id');
            var sal = w.querySelector('input[name*="[is_salary_account]"]');
            if (!sal) return;
            var isSal = salaryBankId && bid && String(salaryBankId) === String(bid);
            sal.value = isSal ? '1' : '0';
        });
        syncSalaryBadges();
        refreshBankSnapshotsSalaryFromHidden();
    }

    function getDraftData() {
        var cat = document.querySelector('#bank-draft-form input[name="draft_account_category"]:checked');
        var acType = document.querySelector('#bank-draft-form input[name="draft_account_type"]:checked');
        var salEl = document.getElementById('draft_is_salary_account');
        return {
            account_category: cat ? cat.value : '',
            account_title: (document.getElementById('draft_account_title') || {}).value || '',
            account_no: (document.getElementById('draft_account_no') || {}).value || '',
            bank_name: (document.getElementById('draft_bank_name') || {}).value || '',
            branch_code: (document.getElementById('draft_branch_code') || {}).value || '',
            branch_address: (document.getElementById('draft_branch_address') || {}).value || '',
            iban: (document.getElementById('draft_iban') || {}).value || '',
            account_type: acType ? acType.value : '',
            is_salary_account: !!(salEl && salEl.checked),
        };
    }

    function clearDraftEditingState() {
        draftEditingIndex = null;
        if (cardListing) {
            cardListing.querySelectorAll('[data-bank-card]').forEach(function (c) {
                c.classList.remove('bank-card--editing');
            });
        }
        if (draftTitleEl) draftTitleEl.textContent = 'New bank account';
    }

    function clearDraftForm() {
        document.querySelectorAll('#bank-draft-form input[name="draft_account_category"]').forEach(function (r) { r.checked = false; });
        document.querySelectorAll('#bank-draft-form input[name="draft_account_type"]').forEach(function (r) { r.checked = false; });
        ['draft_account_title', 'draft_account_no', 'draft_bank_name', 'draft_branch_code', 'draft_branch_address', 'draft_iban'].forEach(function (id) {
            var el = document.getElementById(id);
            if (el) el.value = '';
        });
        var salChk = document.getElementById('draft_is_salary_account');
        if (salChk) salChk.checked = false;
        clearDraftEditingState();
        clearBankDraftApiErrors();
    }

    function readHiddenWrapAsData(wrap) {
        var data = {};
        if (!wrap) return data;
        wrap.querySelectorAll('input[type="hidden"]').forEach(function (inp) {
            var m = inp.name.match(/banks\[\d+\]\[(.+)\]/);
            if (m) data[m[1]] = inp.value;
        });
        return data;
    }

    function mergeBankDraftFromCardSnapshot(data, col) {
        if (!col) return data;
        var raw = col.getAttribute('data-bank-snapshot');
        if (!raw) return data;
        try {
            var snap = JSON.parse(raw);
            var keys = ['account_category', 'account_title', 'account_no', 'bank_name', 'branch_code', 'branch_address', 'iban', 'account_type'];
            keys.forEach(function (k) {
                if (Object.prototype.hasOwnProperty.call(snap, k) && snap[k] != null && String(snap[k]).length) {
                    data[k] = snap[k];
                }
            });
            if (Object.prototype.hasOwnProperty.call(snap, 'is_salary_account')) {
                data.is_salary_account = snap.is_salary_account ? '1' : '0';
            }
        } catch (e) {}
        return data;
    }

    function normalizeDraftAccountCategory(val) {
        var s = String(val || '').trim().toLowerCase();
        if (s === 'personal') return 'personal';
        if (s === 'company_operated' || s === 'company operated' || s === 'company') return 'company_operated';
        return String(val || '').trim();
    }

    function normalizeDraftAccountType(val) {
        var s = String(val || '').trim().toLowerCase();
        if (s === 'saving' || s === 'savings') return 'Saving';
        if (s === 'current') return 'Current';
        return String(val || '').trim();
    }

    function fillDraftFromData(data) {
        var cat = normalizeDraftAccountCategory(data.account_category);
        document.querySelectorAll('#bank-draft-form input[name="draft_account_category"]').forEach(function (r) {
            r.checked = r.value === cat;
        });
        var acType = normalizeDraftAccountType(data.account_type);
        document.querySelectorAll('#bank-draft-form input[name="draft_account_type"]').forEach(function (r) {
            r.checked = r.value === acType;
        });
        var map = {
            draft_account_title: 'account_title',
            draft_account_no: 'account_no',
            draft_bank_name: 'bank_name',
            draft_branch_code: 'branch_code',
            draft_branch_address: 'branch_address',
            draft_iban: 'iban',
        };
        Object.keys(map).forEach(function (id) {
            var el = document.getElementById(id);
            if (el) el.value = data[map[id]] != null ? String(data[map[id]]) : '';
        });
        var salEl = document.getElementById('draft_is_salary_account');
        if (salEl) {
            salEl.checked = data.is_salary_account === '1' || data.is_salary_account === 1 || data.is_salary_account === true;
        }
    }

    function startEditSavedBank(idx) {
        if (!hiddenWrap || !cardListing) return;
        var wrap = hiddenWrap.querySelector('[data-bank-saved-entry][data-index="' + idx + '"]');
        if (!wrap) return;
        var data = readHiddenWrapAsData(wrap);
        var col = cardListing.querySelector('[data-bank-card][data-index="' + idx + '"]');
        data = mergeBankDraftFromCardSnapshot(data, col);
        fillDraftFromData(data);
        draftEditingIndex = idx;
        if (draftTitleEl) draftTitleEl.textContent = 'Edit bank account';
        cardListing.querySelectorAll('[data-bank-card]').forEach(function (c) {
            c.classList.toggle('bank-card--editing', parseInt(c.getAttribute('data-index'), 10) === idx);
        });
        var formEl = document.getElementById('bank-draft-form');
        if (formEl && formEl.scrollIntoView) formEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function updateHiddenRowFromDraft(wrap, idx, data, isSalary) {
        function setField(field, val) {
            var inp = wrap.querySelector('input[name="banks[' + idx + '][' + field + ']"]');
            if (inp) inp.value = val != null ? String(val) : '';
        }
        setField('account_category', data.account_category);
        setField('account_title', data.account_title.trim());
        setField('account_no', data.account_no.replace(/\s+/g, ''));
        setField('bank_name', data.bank_name.trim());
        setField('branch_code', data.branch_code.trim());
        setField('branch_address', data.branch_address.trim());
        setField('iban', (data.iban || '').replace(/\s+/g, '').toUpperCase());
        setField('account_type', data.account_type);
        var sal = wrap.querySelector('input[name="banks[' + idx + '][is_salary_account]"]');
        if (sal) sal.value = isSalary ? '1' : '0';
    }

    function cardInnerHtml(idx, data, isSalary) {
        var bank = data.bank_name.trim();
        var initials = bank.length >= 2 ? bank.substring(0, 2).toUpperCase() : (bank.charAt(0) || '—').toUpperCase();
        var catLabel = data.account_category === 'company_operated' ? 'Company operated' : 'Personal';
        var ibanLine = (data.iban || '').trim() ? escapeHtml((data.iban || '').trim()) : '—';
        return (
            '<div class="card border-1 rounded-3 h-100">' +
            '<div class="card-body p-4">' +
            '<div class="d-flex justify-content-between align-items-start mb-3">' +
            '<div class="d-flex align-items-center min-w-0">' +
            '<div class="me-3 bg-main text-white rounded-2 d-flex align-items-center justify-content-center fw-bold flex-shrink-0" style="width:45px;height:45px;font-size:1.1rem;">' +
            escapeHtml(initials) +
            '</div>' +
            '<div class="min-w-0">' +
            '<h6 class="mb-0 fw-semibold small text-truncate">' +
            escapeHtml(bank) +
            '</h6>' +
            '<small class="text-muted small d-block text-truncate">' +
            escapeHtml(data.account_title.trim()) +
            '</small>' +
            '</div></div>' +
            '<div class="d-flex flex-column align-items-end gap-1 flex-shrink-0">' +
            '<span class="badge bg-primary align-self-start" style="font-size:10px;padding:4px 6px;">' +
            escapeHtml(catLabel) +
            '</span>' +
            '<span data-bank-salary-badge class="badge ' +
            (isSalary ? 'bg-success' : 'bg-secondary') +
            ' align-self-start" style="font-size:10px;padding:4px 6px;">' +
            (isSalary ? 'Salary account' : '—') +
            '</span></div></div>' +
            '<div class="mb-2"><small class="text-muted small"><strong>Account no.:</strong> ' +
            escapeHtml(data.account_no.replace(/\s+/g, '')) +
            ' · <strong>Type:</strong> ' +
            escapeHtml(data.account_type) +
            '</small></div>' +
            '<div class="mb-2"><small class="text-muted small"><strong>Branch:</strong> ' +
            escapeHtml(data.branch_code.trim()) +
            ' — ' +
            escapeHtml(data.branch_address.trim()) +
            '</small></div>' +
            '<div class="mb-2"><small class="text-muted small"><strong>IBAN:</strong> ' +
            ibanLine +
            '</small></div>' +
            '<div class="mt-3 pt-3 border-top d-flex justify-content-end gap-2 flex-wrap">' +
            '<button type="button" class="btn btn-sm btn-outline-primary btn-edit-saved-bank"><i class="bi bi-pencil me-1"></i>Edit</button>' +
            '<button type="button" class="btn btn-sm btn-outline-danger btn-remove-saved-bank"><i class="bi bi-trash me-1"></i>Remove</button>' +
            '</div></div></div>'
        );
    }

    function wireCardCol(col) {
        var rm = col.querySelector('.btn-remove-saved-bank');
        if (rm) {
            rm.addEventListener('click', function () {
                removeSavedAtIndex(parseInt(col.getAttribute('data-index'), 10));
            });
        }
        var ed = col.querySelector('.btn-edit-saved-bank');
        if (ed) {
            ed.addEventListener('click', function () {
                startEditSavedBank(parseInt(col.getAttribute('data-index'), 10));
            });
        }
    }

    function savedCount() {
        return hiddenWrap ? hiddenWrap.querySelectorAll('[data-bank-saved-entry]').length : 0;
    }

    function toggleListingEmpty() {
        if (listingEmpty) listingEmpty.classList.toggle('d-none', savedCount() > 0);
    }

    function reindexSavedBanks() {
        if (!hiddenWrap || !cardListing) return;
        var entries = hiddenWrap.querySelectorAll('[data-bank-saved-entry]');
        entries.forEach(function (wrap, idx) {
            wrap.setAttribute('data-index', String(idx));
            wrap.querySelectorAll('input[name^="banks["]').forEach(function (inp) {
                inp.name = inp.name.replace(/banks\[\d+\]/, 'banks[' + idx + ']');
            });
        });
        var cards = cardListing.querySelectorAll('[data-bank-card]');
        cards.forEach(function (col, idx) {
            col.setAttribute('data-index', String(idx));
        });
        var salaryIdx = -1;
        entries.forEach(function (wrap, idx) {
            var h = wrap.querySelector('input[name*="[is_salary_account]"]');
            if (h && h.value === '1') salaryIdx = idx;
        });
        if (salaryIdx >= 0) {
            setSalaryIndex(salaryIdx);
        }
        syncSalaryBadges();
        refreshBankSnapshotsSalaryFromHidden();
    }

    function setSalaryIndex(idx) {
        if (!hiddenWrap || !cardListing) return;
        var entries = hiddenWrap.querySelectorAll('[data-bank-saved-entry]');
        entries.forEach(function (wrap, i) {
            var h = wrap.querySelector('input[name*="[is_salary_account]"]');
            if (h) h.value = i === idx ? '1' : '0';
        });
        syncSalaryBadges();
        refreshBankSnapshotsSalaryFromHidden();
    }

    function syncSalaryBadges() {
        if (!cardListing || !hiddenWrap) return;
        cardListing.querySelectorAll('[data-bank-card]').forEach(function (col) {
            var idx = parseInt(col.getAttribute('data-index'), 10);
            var wrap = hiddenWrap.querySelector('[data-bank-saved-entry][data-index="' + idx + '"]');
            var h = wrap ? wrap.querySelector('input[name*="[is_salary_account]"]') : null;
            var isSal = h && h.value === '1';
            var badge = col.querySelector('[data-bank-salary-badge]');
            if (badge) {
                badge.textContent = isSal ? 'Salary account' : '—';
                badge.className = 'badge ' + (isSal ? 'bg-success' : 'bg-secondary') + ' align-self-start';
                badge.style.fontSize = '10px';
                badge.style.padding = '4px 6px';
            }
        });
    }

    function appendHiddenRow(idx, data, isSalary, bankDetailId) {
        var wrap = document.createElement('div');
        wrap.setAttribute('data-bank-saved-entry', '');
        wrap.setAttribute('data-index', String(idx));
        if (bankDetailId) wrap.setAttribute('data-bank-detail-id', String(bankDetailId));
        function hid(name, val) {
            var i = document.createElement('input');
            i.type = 'hidden';
            i.name = 'banks[' + idx + '][' + name + ']';
            i.value = val != null ? String(val) : '';
            wrap.appendChild(i);
        }
        hid('account_category', data.account_category);
        hid('account_title', data.account_title.trim());
        hid('account_no', data.account_no.replace(/\s+/g, ''));
        hid('bank_name', data.bank_name.trim());
        hid('branch_code', data.branch_code.trim());
        hid('branch_address', data.branch_address.trim());
        hid('iban', (data.iban || '').replace(/\s+/g, '').toUpperCase());
        hid('account_type', data.account_type);
        var sal = document.createElement('input');
        sal.type = 'hidden';
        sal.name = 'banks[' + idx + '][is_salary_account]';
        sal.value = isSalary ? '1' : '0';
        wrap.appendChild(sal);
        hiddenWrap.appendChild(wrap);
    }

    function escapeHtml(s) {
        return String(s || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function applyBankCardSnapshot(col, data, isSalary) {
        if (!col || !data) return;
        var snap = {
            account_category: data.account_category || '',
            account_title: (data.account_title || '').trim(),
            account_no: String(data.account_no || '').replace(/\s+/g, ''),
            bank_name: (data.bank_name || '').trim(),
            branch_code: (data.branch_code || '').trim(),
            branch_address: (data.branch_address || '').trim(),
            iban: (data.iban || '').replace(/\s+/g, '').toUpperCase(),
            account_type: data.account_type || '',
            is_salary_account: !!isSalary,
        };
        col.setAttribute('data-bank-snapshot', JSON.stringify(snap));
    }

    window.syncBankHiddenRowsFromCardSnapshots = function () {
        if (!hiddenWrap || !cardListing) return;
        reindexSavedBanks();
        var cards = cardListing.querySelectorAll('[data-bank-card]');
        var wraps = hiddenWrap.querySelectorAll('[data-bank-saved-entry]');
        var n = Math.min(cards.length, wraps.length);
        for (var i = 0; i < n; i++) {
            var col = cards[i];
            var wrap = wraps[i];
            var raw = col.getAttribute('data-bank-snapshot');
            if (!raw) {
                var backfill = readHiddenWrapAsData(wrap);
                if (backfill.account_title || backfill.account_no || backfill.bank_name) {
                    var h0 = wrap.querySelector('input[name*="[is_salary_account]"]');
                    applyBankCardSnapshot(
                        col,
                        {
                            account_category: backfill.account_category || '',
                            account_title: backfill.account_title || '',
                            account_no: backfill.account_no || '',
                            bank_name: backfill.bank_name || '',
                            branch_code: backfill.branch_code || '',
                            branch_address: backfill.branch_address || '',
                            iban: backfill.iban || '',
                            account_type: backfill.account_type || '',
                        },
                        !!(h0 && h0.value === '1')
                    );
                    raw = col.getAttribute('data-bank-snapshot');
                }
            }
            if (!raw) continue;
            var snap;
            try {
                snap = JSON.parse(raw);
            } catch (e) {
                continue;
            }
            var row = {
                account_category: snap.account_category || '',
                account_title: snap.account_title || '',
                account_no: String(snap.account_no || ''),
                bank_name: snap.bank_name || '',
                branch_code: snap.branch_code || '',
                branch_address: snap.branch_address || '',
                iban: snap.iban || '',
                account_type: snap.account_type || '',
            };
            updateHiddenRowFromDraft(wrap, i, row, !!snap.is_salary_account);
        }
        reindexSavedBanks();
    };

    function appendBankCard(idx, data, isSalary, bankDetailId) {
        var col = document.createElement('div');
        col.className = 'col-md-6 col-lg-4';
        col.setAttribute('data-bank-card', '');
        col.setAttribute('data-index', String(idx));
        if (bankDetailId) col.setAttribute('data-bank-detail-id', String(bankDetailId));
        col.innerHTML = cardInnerHtml(idx, data, isSalary);
        applyBankCardSnapshot(col, data, isSalary);
        wireCardCol(col);
        cardListing.appendChild(col);
        if (listingEmpty && listingEmpty.parentNode === cardListing) {
            cardListing.appendChild(listingEmpty);
        }
    }

    function removeSavedAtIndex(idx) {
        if (!hiddenWrap || !cardListing) return;
        if (draftEditingIndex === idx) {
            clearDraftForm();
        }
        var entry = hiddenWrap.querySelector('[data-bank-saved-entry][data-index="' + idx + '"]');
        var col = cardListing.querySelector('[data-bank-card][data-index="' + idx + '"]');
        var dbId = entry ? entry.getAttribute('data-bank-detail-id') : '';
        var empEl = document.getElementById('saved_employee_id');
        var empId = empEl ? empEl.value : '';

        function removeDomOnly() {
            if (entry) entry.remove();
            if (col) col.remove();
            reindexSavedBanks();
            toggleListingEmpty();
        }

        var confirmOpts = {
            title: 'Remove bank account?',
            text: dbId && empId ? 'This will permanently delete this account from the employee record.' : 'Remove this account from the list?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, remove',
            cancelButtonText: 'Cancel',
        };

        var afterConfirm = function () {
            if (dbId && empId) {
                var fd = new FormData();
                fd.append('employee_id', empId);
                fd.append('id', dbId);
                fetch(deleteBankDetailUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': bankCsrfToken(),
                        Accept: 'application/json',
                    },
                    body: fd,
                })
                    .then(function (resp) {
                        return resp.json().then(function (data) {
                            return { ok: resp.ok, status: resp.status, data: data };
                        });
                    })
                    .then(function (out) {
                        if (out.ok && out.data && out.data.success) {
                            if (entry) entry.remove();
                            if (col) col.remove();
                            reindexSavedBanks();
                            applySalaryBankIdToAllRows(out.data.salary_bank_id);
                            toggleListingEmpty();
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Deleted',
                                    text: out.data.message || 'Bank account removed.',
                                    timer: 2000,
                                    showConfirmButton: false,
                                });
                            }
                        } else if (out.data && out.data.errors && typeof Swal !== 'undefined') {
                            var errHtml = '<ul class="text-start mt-2 small">';
                            Object.keys(out.data.errors).forEach(function (k) {
                                out.data.errors[k].forEach(function (msg) {
                                    errHtml += '<li>' + msg + '</li>';
                                });
                            });
                            errHtml += '</ul>';
                            Swal.fire({ icon: 'error', title: 'Validation', html: errHtml });
                        } else if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: (out.data && out.data.message) || 'Could not delete bank account.',
                            });
                        }
                    })
                    .catch(function () {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({ icon: 'error', title: 'Error', text: 'Network error.' });
                        }
                    });
                return;
            }
            removeDomOnly();
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'Removed',
                    text: 'Account removed from the list.',
                    timer: 2000,
                    showConfirmButton: false,
                });
            }
        };

        if (typeof Swal !== 'undefined') {
            Swal.fire(confirmOpts).then(function (r) {
                if (r.isConfirmed) afterConfirm();
            });
        } else if (window.confirm(confirmOpts.text)) {
            afterConfirm();
        }
    }

    function validateDraft(data) {
        var ok = true;
        var first = null;
        function mark(el, msg) {
            if (typeof markFieldInvalid === 'function') markFieldInvalid(el, msg);
            ok = false;
            if (!first && el) first = el;
        }
        if (typeof clearStepErrors === 'function') clearStepErrors();
        if (!data.account_category) {
            var r = document.querySelector('#bank-draft-form input[name="draft_account_category"]');
            if (r) mark(r.closest('.col-12') || r, 'Select account category.');
        }
        if (!data.account_title || data.account_title.trim().length < 3) mark(document.getElementById('draft_account_title'), 'Account title is required (min 3 characters).');
        if (!data.account_no || !/^[0-9]{8,24}$/.test(data.account_no.replace(/\s+/g, ''))) {
            mark(document.getElementById('draft_account_no'), 'Account number must be 8–24 digits.');
        }
        if (!data.bank_name || data.bank_name.trim().length < 2) {
            mark(document.getElementById('draft_bank_name'), 'Bank name is required.');
        } else {
            var bn = data.bank_name.trim();
            var hasLetter = false;
            try {
                hasLetter = /\p{L}/u.test(bn);
            } catch (e) {
                hasLetter = /[A-Za-z]/.test(bn);
            }
            if (!hasLetter) {
                mark(document.getElementById('draft_bank_name'), 'Bank name must include letters (not numbers only).');
            }
        }
        if (!data.branch_code || data.branch_code.trim().length < 1) mark(document.getElementById('draft_branch_code'), 'Branch code is required.');
        if (!data.branch_address || data.branch_address.trim().length < 2) {
            mark(document.getElementById('draft_branch_address'), 'Branch address is required.');
        }
        if (!data.account_type) {
            var rt = document.querySelector('#bank-draft-form input[name="draft_account_type"]');
            if (rt) mark(rt.closest('.col-md-6') || rt, 'Select A/C type.');
        }
        if (!ok && first && first.scrollIntoView) first.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return ok;
    }

    function saveDraftToList() {
        var data = getDraftData();
        if (!validateDraft(data)) return;
        var empEl = document.getElementById('saved_employee_id');
        var empId = empEl ? empEl.value : '';
        if (!empId) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Employee not created yet',
                    text: 'Save General Information (step 1) first so the employee record exists. Then you can save bank accounts to the database.',
                    confirmButtonColor: '#012445',
                });
            } else {
                alert('Save General Information first.');
            }
            return;
        }

        var fd = new FormData();
        fd.append('employee_id', empId);
        fd.append('subsection', 'bank_row');
        fd.append('step', '5');
        fd.append('account_category', data.account_category);
        fd.append('account_title', data.account_title.trim());
        fd.append('account_no', data.account_no.replace(/\s+/g, ''));
        fd.append('bank_name', data.bank_name.trim());
        fd.append('branch_code', data.branch_code.trim());
        fd.append('branch_address', data.branch_address.trim());
        fd.append('iban', (data.iban || '').replace(/\s+/g, '').toUpperCase());
        fd.append('account_type', data.account_type);
        fd.append('is_salary_account', data.is_salary_account ? '1' : '0');

        var eIdx = draftEditingIndex;
        if (eIdx !== null) {
            var wrapEd = hiddenWrap.querySelector('[data-bank-saved-entry][data-index="' + eIdx + '"]');
            var bid = wrapEd ? wrapEd.getAttribute('data-bank-detail-id') : '';
            if (bid) fd.append('bank_detail_id', bid);
        }

        var saveBtn = btnSave;
        var prevText = saveBtn ? saveBtn.textContent : '';
        if (saveBtn) {
            saveBtn.disabled = true;
            saveBtn.textContent = 'Saving...';
        }

        fetch(saveBankSubsectionUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': bankCsrfToken(),
                Accept: 'application/json',
            },
            body: fd,
        })
            .then(function (resp) {
                return resp.json().then(function (body) {
                    return { ok: resp.ok, status: resp.status, body: body };
                });
            })
            .then(function (out) {
                if (saveBtn) {
                    saveBtn.disabled = false;
                    saveBtn.textContent = prevText;
                }
                clearBankDraftApiErrors();

                if (out.ok && out.body && out.body.success) {
                    var b = out.body.bank;
                    var salId = out.body.salary_bank_id;
                    var payload = bankPayloadFromServer(b);
                    if (eIdx !== null) {
                        var wrap = hiddenWrap.querySelector('[data-bank-saved-entry][data-index="' + eIdx + '"]');
                        var col = cardListing.querySelector('[data-bank-card][data-index="' + eIdx + '"]');
                        if (wrap && col && b) {
                            wrap.setAttribute('data-bank-detail-id', String(b.id));
                            col.setAttribute('data-bank-detail-id', String(b.id));
                            updateHiddenRowFromDraft(wrap, eIdx, payload, !!b.is_salary_account);
                            applySalaryBankIdToAllRows(salId);
                            reindexSavedBanks();
                            var salH = wrap.querySelector('input[name*="[is_salary_account]"]');
                            var cardIsSal = salH && salH.value === '1';
                            col.innerHTML = cardInnerHtml(eIdx, payload, cardIsSal);
                            applyBankCardSnapshot(col, payload, cardIsSal);
                            wireCardCol(col);
                            col.classList.remove('bank-card--editing');
                        }
                    } else {
                        var idx = savedCount();
                        appendHiddenRow(idx, payload, !!b.is_salary_account, b.id);
                        appendBankCard(idx, payload, !!b.is_salary_account, b.id);
                        applySalaryBankIdToAllRows(salId);
                        reindexSavedBanks();
                    }
                    clearDraftForm();
                    syncSalaryBadges();
                    toggleListingEmpty();
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Saved',
                            text: out.body.message || 'Bank account saved.',
                            timer: 2200,
                            showConfirmButton: false,
                        });
                    }
                    return;
                }
                if (out.body && out.body.errors) {
                    var shown = showBankSubsectionFieldErrors(out.body.errors);
                    if (!shown && typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Validation',
                            text: 'Please review the highlighted fields or messages above.',
                            confirmButtonColor: '#012445',
                        });
                    }
                    return;
                }
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: (out.body && out.body.message) || 'Could not save bank account.',
                    });
                }
            })
            .catch(function () {
                if (saveBtn) {
                    saveBtn.disabled = false;
                    saveBtn.textContent = prevText;
                }
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Network error.' });
                }
            });
    }

    window.addSavedBankFromServer = function (b) {
        if (!b || !hiddenWrap || !cardListing) return;
        var data = {
            account_category: b.account_category || '',
            account_title: b.account_title || '',
            account_no: b.account_no || '',
            bank_name: b.bank_name || '',
            branch_code: b.branch_code || '',
            branch_address: b.branch_address || '',
            iban: b.iban || '',
            account_type: b.account_type || '',
        };
        var idx = savedCount();
        var already = hiddenWrap.querySelector('[data-bank-saved-entry] input[name*="[is_salary_account]"][value="1"]');
        var isSalary = !!b.is_salary_account && !already;
        appendHiddenRow(idx, data, isSalary, b.id || null);
        appendBankCard(idx, data, isSalary, b.id || null);
        reindexSavedBanks();
        toggleListingEmpty();
    };

    window.syncBankSalaryRadiosAfterLoad = function () {
        if (!hiddenWrap) return;
        var entries = hiddenWrap.querySelectorAll('[data-bank-saved-entry]');
        var salaryIdx = -1;
        entries.forEach(function (wrap, i) {
            var h = wrap.querySelector('input[name*="[is_salary_account]"]');
            if (h && h.value === '1') salaryIdx = i;
        });
        if (salaryIdx >= 0) setSalaryIndex(salaryIdx);
        var salChk = document.getElementById('draft_is_salary_account');
        if (salChk) salChk.checked = false;
    };

    window.ensureAtLeastOneBankRow = function () {
        toggleListingEmpty();
        var salChk = document.getElementById('draft_is_salary_account');
        if (salChk) salChk.checked = false;
    };

    document.addEventListener('DOMContentLoaded', function () {
        toggleListingEmpty();
        if (btnSave) btnSave.addEventListener('click', saveDraftToList);
        if (btnClear) btnClear.addEventListener('click', clearDraftForm);
    });
})();
</script>
@endpush
