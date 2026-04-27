(function() {
    'use strict';

    const tpState = {
        addOrganization: null,
        addSbu: null,
        editOrganization: null,
        editSbu: null
    };

    $(document).ready(function() {
        initializeMultiSelects();
        initializeEventHandlers();
    });

    function getCsrfToken() {
        return $('meta[name="csrf-token"]').attr('content');
    }

    function showSwal(type, title, message) {
        Swal.fire({ icon: type, title: title, text: message });
    }

    function escapeHtml(value) {
        if (value === null || value === undefined) return '';
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function formatPakCnicInput(input) {
        if (!input) return;
        var val = String(input.value).replace(/\D/g, '');
        if (val.length > 15) val = val.substring(0, 15);
        var formatted = '';
        var core = val.substring(0, 13);
        if (core.length > 0) {
            formatted = core.substring(0, 5);
            if (core.length > 5) formatted += '-' + core.substring(5, 12);
            if (core.length > 12) formatted += '-' + core.substring(12, 13);
        }
        if (val.length > 13) formatted += val.substring(13);
        input.value = formatted;
    }

    function formatNtnDigitsInput(input) {
        if (!input) return;
        var v = String(input.value).replace(/\D/g, '').substring(0, 13);
        input.value = v;
    }

    function digitsToCnicDisplay(digits) {
        var d = String(digits || '').replace(/\D/g, '').substring(0, 15);
        if (!d.length) return '';
        var core = d.substring(0, 13);
        var out = core.substring(0, 5);
        if (core.length > 5) out += '-' + core.substring(5, 12);
        if (core.length > 12) out += '-' + core.substring(12, 13);
        if (d.length > 13) out += d.substring(13);
        return out;
    }

    function syncVendorTaxUi(scope) {
        var isInd = scope === 'add'
            ? $('#is_individual_contractor').val() === '1'
            : $('#edit_is_individual_contractor').val() === '1';
        if (scope === 'add') {
            $('#ntnWrap').toggleClass('d-none', isInd);
            $('#contractorCnicWrap').toggleClass('d-none', !isInd);
            $('#ntn').prop('required', !isInd).prop('disabled', isInd);
            $('#contractor_cnic').prop('required', isInd).prop('disabled', !isInd);
        } else {
            $('#edit_ntnWrap').toggleClass('d-none', isInd);
            $('#edit_contractorCnicWrap').toggleClass('d-none', !isInd);
            $('#edit_ntn').prop('required', !isInd).prop('disabled', isInd);
            $('#edit_contractor_cnic').prop('required', isInd).prop('disabled', !isInd);
        }
    }

    function applyTpFilters() {
        const status = $('input[name="filterTpStatus"]:checked').val();
        $('#thirdPartiesGrid .col-md-6').each(function() {
            const card = $(this).find('.third-party-card');
            const cardStatus = String(card.data('tp-status'));
            $(this).toggle(status === 'all' || cardStatus === status);
        });
    }

    function clearTpFilters() {
        $('input#filterTpStatusAll').prop('checked', true);
        $('#thirdPartiesGrid .col-md-6').show();
    }

    function populateTpDetailCanvas(button) {
        const get = function(attr, fallback) {
            const value = button.getAttribute(attr);
            return (value !== null && value !== '') ? value : (fallback || '—');
        };

        const name = get('data-tp-name');
        $('#detailTpLogoPlaceholder').text((name.substring(0, 2) || '?').toUpperCase());
        $('#detailTpName').text(name);
        $('#detailTpThirdPartyName').text(get('data-tp-third-party-name'));
        $('#detailTpCity').text(get('data-tp-city'));
        $('#detailTpOrganization').text(get('data-organization-name'));
        $('#detailTpSbus').text(get('data-tp-sbu-names'));
        $('#detailTpVendorId').text(get('data-tp-vendor-id'));
        const serviceTypeVal = get('data-tp-service-type');
        const specifyServiceTypeVal = (button.getAttribute('data-tp-specify-service-type') || '').trim();
        $('#detailTpServiceType').text(serviceTypeVal);
        if (serviceTypeVal === 'Other' && specifyServiceTypeVal) {
            $('#detailTpSpecifyServiceTypeRow').removeClass('d-none');
            $('#detailTpSpecifyServiceType').text(specifyServiceTypeVal);
        } else {
            $('#detailTpSpecifyServiceTypeRow').addClass('d-none');
            $('#detailTpSpecifyServiceType').text('—');
        }
        var isInd = get('data-tp-is-individual') === '1';
        $('#detailTpVendorType').text(isInd ? 'Individual contractor (CNIC)' : 'Registered company (NTN)');
        $('#detailTpNtn').text(isInd ? '—' : (get('data-tp-ntn') || '—'));
        var conRaw = button.getAttribute('data-tp-contractor-cnic');
        var conDigits = (conRaw !== null && conRaw !== '') ? String(conRaw).replace(/\D/g, '') : '';
        $('#detailTpContractorCnic').text(!isInd ? '—' : (conDigits.length ? digitsToCnicDisplay(conRaw) : '—'));
        $('#detailTpContactPersonName').text(get('data-tp-contact-person-name'));
        $('#detailTpMobileNumber').text(get('data-tp-mobile-number'));
        $('#detailTpEmail').text(get('data-tp-email'));
        $('#detailTpSupervisorName').text(get('data-tp-supervisor-name'));
        var supCnicRaw = button.getAttribute('data-tp-supervisor-cnic');
        var supCnicDigits = (supCnicRaw !== null && supCnicRaw !== '') ? String(supCnicRaw).replace(/\D/g, '') : '';
        $('#detailTpSupervisorCnic').text(supCnicDigits.length ? digitsToCnicDisplay(supCnicRaw) : '—');
        $('#detailTpSupervisorMobile').text(get('data-tp-supervisor-mobile-number'));
        $('#detailTpContractStartDate').text(get('data-tp-contract-start-date'));
        $('#detailTpContractEndDate').text(get('data-tp-contract-end-date'));
        $('#detailTpScopeOfWork').text(get('data-tp-scope-of-work'));
        $('#detailTpEstimatedStaffCount').text(get('data-tp-estimated-staff-count'));
        $('#detailTpRemarks').text(get('data-tp-remarks'));

        const companyDocUrl = button.getAttribute('data-tp-company-doc-url');
        const contractDocUrl = button.getAttribute('data-tp-contract-doc-url');
        $('#detailTpCompanyDoc').html(companyDocUrl ? '<a href="' + escapeHtml(companyDocUrl) + '" target="_blank" class="text-white text-decoration-underline">View document</a>' : '—');
        $('#detailTpContractDoc').html(contractDocUrl ? '<a href="' + escapeHtml(contractDocUrl) + '" target="_blank" class="text-white text-decoration-underline">View document</a>' : '—');
        $('#detailTpStatus').text(get('data-tp-active') === '1' ? 'Active' : 'Inactive');
    }

    function createMultiSelect(config) {
        const box = document.getElementById(config.boxId);
        const chips = document.getElementById(config.chipsId);
        const placeholder = document.getElementById(config.placeholderId);
        const dropdown = document.getElementById(config.dropdownId);
        const search = document.getElementById(config.searchId);
        const list = document.getElementById(config.listId);
        const hiddenInputs = document.getElementById(config.hiddenInputsId);

        let options = [];
        let selected = [];
        let isOpen = false;
        let isDisabled = false;
        let placeholderText = config.defaultPlaceholder || 'Select options...';
        let disabledMessage = config.disabledMessage || 'No options found';

        function sync() {
            renderHiddenInputs();
            renderChips();
            renderList();
        }

        function renderHiddenInputs() {
            if (!hiddenInputs) return;
            hiddenInputs.innerHTML = selected.map(function(id) {
                return '<input type="hidden" name="' + config.fieldName + '[]" value="' + id + '">';
            }).join('');
        }

        function renderChips() {
            if (!chips || !placeholder) return;
            if (!selected.length) {
                chips.innerHTML = '';
                placeholder.textContent = placeholderText;
                placeholder.style.display = '';
                return;
            }
            placeholder.style.display = 'none';
            chips.innerHTML = selected.map(function(id) {
                const row = options.find(function(item) { return String(item.id) === id; });
                const label = row ? row.name : id;
                return '<span class="tp-ms-chip">' + escapeHtml(label) + '<span class="tp-ms-chip-x" data-remove-id="' + escapeHtml(id) + '">×</span></span>';
            }).join('');
        }

        function renderList() {
            if (!list) return;
            if (isDisabled) {
                list.innerHTML = '<div class="tp-ms-no-result">' + disabledMessage + '</div>';
                return;
            }

            const query = ((search && search.value) || '').toLowerCase().trim();
            const filtered = options.filter(function(item) {
                return !query || String(item.name || '').toLowerCase().includes(query);
            });
            if (!filtered.length) {
                list.innerHTML = '<div class="tp-ms-no-result">No options found</div>';
                return;
            }

            list.innerHTML = filtered.map(function(item) {
                const id = String(item.id);
                const picked = selected.includes(id);
                return '<div class="tp-ms-opt ' + (picked ? 'picked' : '') + '" data-option-id="' + id + '">' +
                    '<span class="tp-ms-opt-cb"><svg class="tp-ms-opt-ck" viewBox="0 0 16 16" width="12" height="12" fill="none"><path d="M3.5 8.2l3 3L12.5 5" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></span>' +
                    '<span>' + escapeHtml(item.name) + '</span>' +
                '</div>';
            }).join('');
        }

        function open() {
            if (!dropdown || !box || isDisabled) return;
            isOpen = true;
            dropdown.style.display = 'block';
            box.classList.add('open');
            if (search) setTimeout(function() { search.focus(); }, 0);
        }

        function close() {
            if (!dropdown || !box) return;
            isOpen = false;
            dropdown.style.display = 'none';
            box.classList.remove('open');
        }

        function emitChange() {
            if (typeof config.onChange === 'function') {
                config.onChange(selected.slice());
            }
        }

        function setOptions(nextOptions) {
            options = Array.isArray(nextOptions) ? nextOptions.map(function(item) {
                return { id: String(item.id), name: item.name };
            }) : [];
            const valid = new Set(options.map(function(item) { return String(item.id); }));
            selected = selected.filter(function(id) { return valid.has(id); });
            sync();
        }

        function setSelected(ids, triggerChange) {
            const normalized = Array.isArray(ids) ? ids.map(function(id) { return String(id); }) : [];
            const valid = new Set(options.map(function(item) { return String(item.id); }));
            selected = normalized.filter(function(id) { return valid.has(id); });
            sync();
            if (triggerChange !== false) emitChange();
        }

        function clear(triggerChange) {
            setSelected([], triggerChange);
        }

        function toggleOption(id) {
            const normalized = String(id);
            if (selected.includes(normalized)) {
                selected = selected.filter(function(item) { return item !== normalized; });
            } else {
                selected.push(normalized);
            }
            sync();
            emitChange();
        }

        function setDisabled(flag, message, nextPlaceholder) {
            isDisabled = Boolean(flag);
            if (typeof message === 'string') disabledMessage = message;
            if (typeof nextPlaceholder === 'string') {
                placeholderText = nextPlaceholder;
            } else if (isDisabled) {
                placeholderText = disabledMessage;
            } else {
                placeholderText = config.defaultPlaceholder || placeholderText;
            }
            if (box) box.classList.toggle('disabled', isDisabled);
            if (search) {
                search.disabled = isDisabled;
                if (isDisabled) search.value = '';
            }
            if (isDisabled) close();
            sync();
        }

        function clearInvalid() {
            if (box) box.classList.remove('is-invalid');
            const fb = box && box.parentNode.querySelector('.invalid-feedback[data-field="' + config.fieldName + '"]');
            if (fb) fb.remove();
        }

        function markInvalid(message) {
            if (!box) return;
            box.classList.add('is-invalid');
            const existing = box.parentNode.querySelector('.invalid-feedback[data-field="' + config.fieldName + '"]');
            if (existing) {
                existing.textContent = message;
                return;
            }
            const feedback = document.createElement('div');
            feedback.className = 'invalid-feedback d-block';
            feedback.dataset.field = config.fieldName;
            feedback.textContent = message;
            box.insertAdjacentElement('afterend', feedback);
        }

        if (box) {
            box.addEventListener('click', function(event) {
                event.preventDefault();
                event.stopPropagation();
                if (isDisabled) return;
                if (isOpen) close(); else open();
            });
        }
        if (search) {
            search.addEventListener('input', function() { if (!isDisabled) renderList(); });
            search.addEventListener('click', function(event) { event.stopPropagation(); });
            search.addEventListener('keydown', function(event) { event.stopPropagation(); });
        }
        if (list) {
            list.addEventListener('click', function(event) {
                event.stopPropagation();
                const item = event.target.closest('[data-option-id]');
                if (item) toggleOption(item.dataset.optionId);
            });
        }
        if (chips) {
            chips.addEventListener('click', function(event) {
                const removeNode = event.target.closest('[data-remove-id]');
                if (!removeNode) return;
                event.stopPropagation();
                toggleOption(removeNode.dataset.removeId);
            });
        }
        document.addEventListener('click', function(event) {
            const inBox = box && box.contains(event.target);
            const inDrop = dropdown && dropdown.contains(event.target);
            if (!inBox && !inDrop) close();
        });

        sync();

        return {
            setOptions: setOptions,
            setSelected: setSelected,
            getSelected: function() { return selected.slice(); },
            clear: clear,
            setDisabled: setDisabled,
            clearInvalid: clearInvalid,
            markInvalid: markInvalid
        };
    }

    function getOrganizations() {
        return Array.isArray(window.thirdPartyOrganizations) ? window.thirdPartyOrganizations : [];
    }

    function getSbus() {
        return Array.isArray(window.thirdPartySbus) ? window.thirdPartySbus : [];
    }

    function filterSbusByOrganizations(organizationIds) {
        const set = new Set((organizationIds || []).map(function(id) { return String(id); }));
        return getSbus().filter(function(sbu) {
            return set.has(String(sbu.organization_id));
        }).map(function(sbu) {
            return { id: String(sbu.id), name: sbu.name };
        });
    }

    function updateAddSbuOptions(orgIds) {
        if (!tpState.addSbu) return;
        const selectedOrgs = Array.isArray(orgIds) ? orgIds : [];
        if (!selectedOrgs.length) {
            tpState.addSbu.setDisabled(true, 'First select the organization', 'First select the organization');
            tpState.addSbu.setOptions([]);
            tpState.addSbu.setSelected([], false);
            return;
        }
        const previous = tpState.addSbu.getSelected();
        tpState.addSbu.setDisabled(false, undefined, 'Select SBUs...');
        tpState.addSbu.setOptions(filterSbusByOrganizations(selectedOrgs));
        tpState.addSbu.setSelected(previous, false);
    }

    function updateEditSbuOptions(orgIds) {
        if (!tpState.editSbu) return;
        const selectedOrgs = Array.isArray(orgIds) ? orgIds : [];
        if (!selectedOrgs.length) {
            tpState.editSbu.setDisabled(true, 'First select the organization', 'First select the organization');
            tpState.editSbu.setOptions([]);
            tpState.editSbu.setSelected([], false);
            return;
        }
        const previous = tpState.editSbu.getSelected();
        tpState.editSbu.setDisabled(false, undefined, 'Select SBUs...');
        tpState.editSbu.setOptions(filterSbusByOrganizations(selectedOrgs));
        tpState.editSbu.setSelected(previous, false);
    }

    function initializeMultiSelects() {
        const organizations = getOrganizations().map(function(item) {
            return { id: String(item.id), name: item.name };
        });

        tpState.addOrganization = createMultiSelect({
            fieldName: 'organization_ids',
            boxId: 'addOrganizationBox',
            chipsId: 'addOrganizationChips',
            placeholderId: 'addOrganizationPh',
            dropdownId: 'addOrganizationDropdown',
            searchId: 'addOrganizationSearch',
            listId: 'addOrganizationList',
            hiddenInputsId: 'addOrganizationHiddenInputs',
            onChange: updateAddSbuOptions
        });
        tpState.addSbu = createMultiSelect({
            fieldName: 'sbu_ids',
            boxId: 'addSbuBox',
            chipsId: 'addSbuChips',
            placeholderId: 'addSbuPh',
            dropdownId: 'addSbuDropdown',
            searchId: 'addSbuSearch',
            listId: 'addSbuList',
            hiddenInputsId: 'addSbuHiddenInputs'
        });
        tpState.editOrganization = createMultiSelect({
            fieldName: 'organization_ids',
            boxId: 'editOrganizationBox',
            chipsId: 'editOrganizationChips',
            placeholderId: 'editOrganizationPh',
            dropdownId: 'editOrganizationDropdown',
            searchId: 'editOrganizationSearch',
            listId: 'editOrganizationList',
            hiddenInputsId: 'editOrganizationHiddenInputs',
            onChange: updateEditSbuOptions
        });
        tpState.editSbu = createMultiSelect({
            fieldName: 'sbu_ids',
            boxId: 'editSbuBox',
            chipsId: 'editSbuChips',
            placeholderId: 'editSbuPh',
            dropdownId: 'editSbuDropdown',
            searchId: 'editSbuSearch',
            listId: 'editSbuList',
            hiddenInputsId: 'editSbuHiddenInputs'
        });

        tpState.addOrganization.setOptions(organizations);
        tpState.editOrganization.setOptions(organizations);
        tpState.addSbu.setOptions([]);
        tpState.editSbu.setOptions([]);
        tpState.addSbu.setDisabled(true, 'First select the organization', 'First select the organization');
        tpState.editSbu.setDisabled(true, 'First select the organization', 'First select the organization');
    }

    function clearFormMessages(formSelector) {
        $(formSelector + ' .is-invalid').removeClass('is-invalid');
        $(formSelector + ' .invalid-feedback').remove();
        if (formSelector === '#addThirdPartyForm') {
            tpState.addOrganization && tpState.addOrganization.clearInvalid();
            tpState.addSbu && tpState.addSbu.clearInvalid();
        }
        if (formSelector === '#editThirdPartyForm') {
            tpState.editOrganization && tpState.editOrganization.clearInvalid();
            tpState.editSbu && tpState.editSbu.clearInvalid();
        }
    }

    function validateThirdPartyClient(formSelector) {
        var errs = {};
        function add(field, msg) {
            if (!errs[field]) errs[field] = [];
            errs[field].push(msg);
        }
        var $f = $(formSelector);
        var addOrg = formSelector === '#addThirdPartyForm' ? tpState.addOrganization : tpState.editOrganization;
        var addSbu = formSelector === '#addThirdPartyForm' ? tpState.addSbu : tpState.editSbu;
        if (!addOrg || !addOrg.getSelected().length) {
            add('organization_ids', 'Select at least one organization.');
        }
        if (!addSbu || !addSbu.getSelected().length) {
            add('sbu_ids', 'Select at least one SBU.');
        }
        var tpName = String($f.find('[name="third_party_name"]').val() || '').trim();
        if (tpName.length < 2) {
            add('third_party_name', 'Company name must be at least 2 characters.');
        } else if (!/[A-Za-z]/.test(tpName)) {
            add('third_party_name', 'Company name must contain letters.');
        }
        if (!String($f.find('[name="service_type"]').val() || '').trim()) {
            add('service_type', 'Service type is required.');
        }
        if ($f.find('[name="service_type"]').val() === 'Other') {
            var st = String($f.find('[name="specify_service_type"]').val() || '').trim();
            if (st.length < 3) {
                add('specify_service_type', 'Please specify the service type (at least 3 characters).');
            } else if (!/[A-Za-z]/.test(st)) {
                add('specify_service_type', 'Specified service type must contain letters.');
            }
        }
        var isInd = $f.find('[name="is_individual_contractor"]').val() === '1';
        var ntnDigits = String($f.find('[name="ntn"]').val() || '').replace(/\D/g, '');
        var contractorDigits = String($f.find('[name="contractor_cnic"]').val() || '').replace(/\D/g, '');
        if (!isInd) {
            if (!/^[0-9]{5,13}$/.test(ntnDigits)) {
                add('ntn', 'NTN must be 5 to 13 digits only.');
            }
        } else if (!/^[0-9]{13,15}$/.test(contractorDigits)) {
            add('contractor_cnic', 'Contractor CNIC must be 13 to 15 digits.');
        }
        var contactName = String($f.find('[name="contact_person_name"]').val() || '').trim();
        if (contactName.length < 3) {
            add('contact_person_name', 'Contact person name must be at least 3 characters.');
        } else if (!/[A-Za-z]/.test(contactName)) {
            add('contact_person_name', 'Contact person name must contain letters.');
        }
        var mobile = String($f.find('[name="mobile_number"]').val() || '').replace(/\D/g, '');
        if (!/^[0-9]{11,15}$/.test(mobile)) {
            add('mobile_number', 'Mobile number must be 11 to 15 digits.');
        }
        var supName = String($f.find('[name="supervisor_name"]').val() || '').trim();
        if (supName.length < 3) {
            add('supervisor_name', 'Supervisor name must be at least 3 characters.');
        } else if (!/[A-Za-z]/.test(supName)) {
            add('supervisor_name', 'Supervisor name must contain letters.');
        }
        var supCnic = String($f.find('[name="supervisor_cnic"]').val() || '').replace(/\D/g, '');
        if (!/^[0-9]{13,15}$/.test(supCnic)) {
            add('supervisor_cnic', 'Supervisor CNIC must be 13 to 15 digits.');
        }
        var supMobile = String($f.find('[name="supervisor_mobile_number"]').val() || '').replace(/\D/g, '');
        if (!/^[0-9]{11,15}$/.test(supMobile)) {
            add('supervisor_mobile_number', 'Supervisor mobile number must be 11 to 15 digits.');
        }
        if (!String($f.find('[name="contract_start_date"]').val() || '').trim()) {
            add('contract_start_date', 'Contract start date is required.');
        }
        if (!String($f.find('[name="contract_end_date"]').val() || '').trim()) {
            add('contract_end_date', 'Contract end date is required.');
        }
        var scope = String($f.find('[name="scope_of_work"]').val() || '').trim();
        if (scope.length < 5) {
            add('scope_of_work', 'Scope of work must be at least 5 characters.');
        }
        var esc = parseInt(String($f.find('[name="estimated_staff_count"]').val() || ''), 10);
        if (!esc || esc < 1) {
            add('estimated_staff_count', 'Estimated staff count must be at least 1.');
        }
        if (typeof validate !== 'undefined') {
            var emailVal = String($f.find('[name="email"]').val() || '').trim();
            var emailErr = validate({ email: emailVal }, { email: { presence: { allowEmpty: false }, email: true } });
            if (emailErr && emailErr.email) {
                add('email', emailErr.email[0]);
            }
        } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(String($f.find('[name="email"]').val() || '').trim())) {
            add('email', 'Email address format is invalid.');
        }
        return Object.keys(errs).length ? errs : null;
    }

    function showValidationErrors(formSelector, errors) {
        clearFormMessages(formSelector);

        $.each(errors, function(field, messages) {
            const root = field.includes('.') ? field.split('.')[0] : field;
            if (root === 'organization_ids') {
                if (formSelector === '#addThirdPartyForm' && tpState.addOrganization) tpState.addOrganization.markInvalid(messages[0]);
                if (formSelector === '#editThirdPartyForm' && tpState.editOrganization) tpState.editOrganization.markInvalid(messages[0]);
                return;
            }
            if (root === 'sbu_ids') {
                if (formSelector === '#addThirdPartyForm' && tpState.addSbu) tpState.addSbu.markInvalid(messages[0]);
                if (formSelector === '#editThirdPartyForm' && tpState.editSbu) tpState.editSbu.markInvalid(messages[0]);
                return;
            }

            let input = $(formSelector + ' [name="' + field + '"]');
            if (!input.length) input = $(formSelector + ' [name="' + root + '"]');
            if (!input.length) input = $(formSelector + ' [name="' + root + '[]"]');
            if (input.length) {
                input.addClass('is-invalid');
                input.after('<div class="invalid-feedback d-block">' + messages[0] + '</div>');
            }
        });
    }

    function syncSpecifyServiceTypeUi(scope) {
        if (scope === 'add') {
            if ($('#service_type').val() === 'Other') {
                $('#specifyServiceTypeWrap').removeClass('d-none');
                $('#specify_service_type').prop('disabled', false).prop('required', true);
            } else {
                $('#specifyServiceTypeWrap').addClass('d-none');
                $('#specify_service_type').val('').prop('disabled', true).prop('required', false);
            }
        } else {
            if ($('#edit_service_type').val() === 'Other') {
                $('#editSpecifyServiceTypeWrap').removeClass('d-none');
                $('#edit_specify_service_type').prop('disabled', false).prop('required', true);
            } else {
                $('#editSpecifyServiceTypeWrap').addClass('d-none');
                $('#edit_specify_service_type').val('').prop('disabled', true).prop('required', false);
            }
        }
    }

    function resetAddTpForm() {
        const form = document.getElementById('addThirdPartyForm');
        if (form) form.reset();
        clearFormMessages('#addThirdPartyForm');
        $('#is_active').val('1');
        $('#vendor_id_display').val('Auto-generated after save');
        tpState.addOrganization.clear(false);
        updateAddSbuOptions([]);
        tpState.addSbu.clear(false);
        syncSpecifyServiceTypeUi('add');
        $('#is_individual_contractor').val('0');
        $('#ntn').val('');
        $('#contractor_cnic').val('');
        syncVendorTaxUi('add');
    }

    function resetEditTpForm() {
        const form = document.getElementById('editThirdPartyForm');
        if (form) form.reset();
        clearFormMessages('#editThirdPartyForm');
        $('#edit_is_active').val('1');
        $('#edit_vendor_id').val('');
        $('#edit_company_registration_document_link').html('');
        $('#edit_contract_copy_link').html('');
        $('#editThirdPartyForm').attr('data-update-url', '');
        tpState.editOrganization.clear(false);
        updateEditSbuOptions([]);
        tpState.editSbu.clear(false);
        syncSpecifyServiceTypeUi('edit');
        $('#edit_is_individual_contractor').val('0');
        $('#edit_ntn').val('');
        $('#edit_contractor_cnic').val('');
        syncVendorTaxUi('edit');
    }

    function storeThirdParty() {
        const form = $('#addThirdPartyForm');
        const url = form.data('store-url');
        clearFormMessages('#addThirdPartyForm');

        if (!url) {
            showSwal('error', 'Error', 'Store URL not found.');
            return;
        }

        var clientErr = validateThirdPartyClient('#addThirdPartyForm');
        if (clientErr) {
            showValidationErrors('#addThirdPartyForm', clientErr);
            showSwal('error', 'Validation Error', 'Please fix the highlighted fields and try again.');
            return;
        }

        $.ajax({
            url: url,
            type: 'POST',
            data: new FormData(form[0]),
            processData: false,
            contentType: false,
            headers: { 'X-CSRF-TOKEN': getCsrfToken(), 'Accept': 'application/json' },
            beforeSend: function() { $('#saveThirdPartyBtn').prop('disabled', true).html('Saving...'); },
            success: function(response) {
                if (response.success) {
                    const canvasEl = document.getElementById('addThirdPartyCanvas');
                    const offcanvas = bootstrap.Offcanvas.getInstance(canvasEl);
                    if (offcanvas) offcanvas.hide();
                    Swal.fire({ icon: 'success', title: 'Success', text: response.message || 'Third party created successfully.', timer: 1500, showConfirmButton: false })
                        .then(function() { location.reload(); });
                } else {
                    showSwal('error', 'Error', response.message || 'Failed to create third party.');
                }
            },
            error: function(xhr) {
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    showValidationErrors('#addThirdPartyForm', xhr.responseJSON.errors);
                    showSwal('error', 'Validation Error', 'Please fix the highlighted fields and try again.');
                } else {
                    showSwal('error', 'Error', (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Failed to create third party.');
                }
            },
            complete: function() { $('#saveThirdPartyBtn').prop('disabled', false).html('<i class="bi bi-check-lg me-1"></i>Create Third Party'); }
        });
    }

    function loadEditTpData(button) {
        const editUrl = $(button).data('edit-url');
        clearFormMessages('#editThirdPartyForm');
        if (!editUrl) {
            showSwal('error', 'Error', 'Edit URL not found.');
            return;
        }
        resetEditTpForm();
        $.ajax({
            url: editUrl,
            type: 'GET',
            headers: { 'Accept': 'application/json' },
            success: function(response) {
                if (response.success && response.data) {
                    const data = response.data;
                    $('#edit_id').val(data.id || '');
                    tpState.editOrganization.setSelected(data.organization_ids || []);
                    updateEditSbuOptions(tpState.editOrganization.getSelected());
                    tpState.editSbu.setSelected(data.sbu_ids || []);
                    $('#edit_third_party_name').val(data.third_party_name || '');
                    $('#edit_vendor_id').val(data.vendor_id || '');
                    $('#edit_service_type').val(data.service_type || '');
                    $('#edit_specify_service_type').val(data.specify_service_type || '');
                    syncSpecifyServiceTypeUi('edit');
                    $('#edit_is_individual_contractor').val(data.is_individual_contractor === 1 || data.is_individual_contractor === '1' || data.is_individual_contractor === true ? '1' : '0');
                    syncVendorTaxUi('edit');
                    $('#edit_ntn').val(data.ntn || '');
                    $('#edit_contractor_cnic').val(digitsToCnicDisplay(data.contractor_cnic || ''));
                    $('#edit_contact_person_name').val(data.contact_person_name || '');
                    $('#edit_mobile_number').val(data.mobile_number || '');
                    $('#edit_email').val(data.email || '');
                    $('#edit_supervisor_name').val(data.supervisor_name || '');
                    $('#edit_supervisor_cnic').val(digitsToCnicDisplay(data.supervisor_cnic || ''));
                    $('#edit_supervisor_mobile_number').val(data.supervisor_mobile_number || '');
                    $('#edit_contract_start_date').val(data.contract_start_date || '');
                    $('#edit_contract_end_date').val(data.contract_end_date || '');
                    $('#edit_scope_of_work').val(data.scope_of_work || '');
                    $('#edit_estimated_staff_count').val(data.estimated_staff_count || '');
                    $('#edit_remarks').val(data.remarks || '');

                    const companyDocLink = data.company_registration_document_url
                        ? '<a href="' + escapeHtml(data.company_registration_document_url) + '" target="_blank" class="text-white text-decoration-underline">View document</a>'
                        : 'No document uploaded';
                    const contractDocLink = data.contract_copy_url
                        ? '<a href="' + escapeHtml(data.contract_copy_url) + '" target="_blank" class="text-white text-decoration-underline">View document</a>'
                        : 'No document uploaded';
                    $('#edit_company_registration_document_link').html(companyDocLink);
                    $('#edit_contract_copy_link').html(contractDocLink);

                    $('#edit_is_active').val(data.is_active === 1 || data.is_active === '1' || data.is_active === true ? '1' : '0');
                    $('#editThirdPartyForm').attr('data-update-url', $(button).data('update-url'));
                    formatPakCnicInput(document.getElementById('edit_contractor_cnic'));
                    formatPakCnicInput(document.getElementById('edit_supervisor_cnic'));
                } else {
                    showSwal('error', 'Error', response.message || 'Failed to load data.');
                }
            },
            error: function(xhr) {
                showSwal('error', 'Error', (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Failed to fetch third party.');
            }
        });
    }

    function updateThirdParty() {
        const form = $('#editThirdPartyForm');
        const url = form.attr('data-update-url');
        clearFormMessages('#editThirdPartyForm');
        if (!url) {
            showSwal('error', 'Error', 'Update URL not found.');
            return;
        }

        var clientErrEdit = validateThirdPartyClient('#editThirdPartyForm');
        if (clientErrEdit) {
            showValidationErrors('#editThirdPartyForm', clientErrEdit);
            showSwal('error', 'Validation Error', 'Please fix the highlighted fields and try again.');
            return;
        }

        $.ajax({
            url: url,
            type: 'POST',
            data: new FormData(form[0]),
            processData: false,
            contentType: false,
            headers: { 'X-CSRF-TOKEN': getCsrfToken(), 'Accept': 'application/json' },
            beforeSend: function() { $('#updateThirdPartyBtn').prop('disabled', true).html('Updating...'); },
            success: function(response) {
                if (response.success) {
                    const canvasEl = document.getElementById('editThirdPartyCanvas');
                    const offcanvas = bootstrap.Offcanvas.getInstance(canvasEl);
                    if (offcanvas) offcanvas.hide();
                    Swal.fire({ icon: 'success', title: 'Success', text: response.message || 'Third party updated successfully.', timer: 1500, showConfirmButton: false })
                        .then(function() { location.reload(); });
                } else {
                    showSwal('error', 'Error', response.message || 'Failed to update third party.');
                }
            },
            error: function(xhr) {
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    showValidationErrors('#editThirdPartyForm', xhr.responseJSON.errors);
                    showSwal('error', 'Validation Error', 'Please fix the highlighted fields and try again.');
                } else {
                    showSwal('error', 'Error', (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Failed to update third party.');
                }
            },
            complete: function() { $('#updateThirdPartyBtn').prop('disabled', false).html('<i class="bi bi-check-lg me-1"></i>Update Third Party'); }
        });
    }

    function deleteThirdParty(button) {
        const deleteUrl = $(button).data('delete-url');
        if (!deleteUrl) {
            showSwal('error', 'Error', 'Delete URL not found.');
            return;
        }
        Swal.fire({
            title: 'Are you sure?',
            text: 'This third party will be permanently deleted.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!'
        }).then(function(result) {
            if (!result.isConfirmed) return;
            $.ajax({
                url: deleteUrl,
                type: 'POST',
                data: { _method: 'DELETE', _token: getCsrfToken() },
                headers: { 'X-CSRF-TOKEN': getCsrfToken(), 'Accept': 'application/json' },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({ icon: 'success', title: 'Deleted', text: response.message || 'Deleted successfully.', timer: 1500, showConfirmButton: false })
                            .then(function() { location.reload(); });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error', text: response.message || 'Failed.' });
                    }
                },
                error: function(xhr) {
                    Swal.fire({ icon: 'error', title: 'Error', text: (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Failed to delete.' });
                }
            });
        });
    }

    function initializeEventHandlers() {
        const detailCanvas = document.getElementById('thirdPartyDetailCanvas');
        if (detailCanvas) {
            detailCanvas.addEventListener('show.bs.offcanvas', function(event) {
                const button = event.relatedTarget;
                if (button && button.classList.contains('view-tp-btn')) populateTpDetailCanvas(button);
            });
        }

        const addCanvas = document.getElementById('addThirdPartyCanvas');
        if (addCanvas) addCanvas.addEventListener('show.bs.offcanvas', resetAddTpForm);

        const editCanvas = document.getElementById('editThirdPartyCanvas');
        if (editCanvas) {
            editCanvas.addEventListener('show.bs.offcanvas', function(event) {
                const button = event.relatedTarget;
                if (button && button.classList.contains('edit-tp-btn')) loadEditTpData(button);
            });
            editCanvas.addEventListener('hidden.bs.offcanvas', resetEditTpForm);
        }

        $('.filter-tp-status').on('change', applyTpFilters);
        $('#clearTpFiltersBtn').on('click', clearTpFilters);
        $(document).on('change', '#service_type', function() { syncSpecifyServiceTypeUi('add'); });
        $(document).on('change', '#edit_service_type', function() { syncSpecifyServiceTypeUi('edit'); });
        $(document).on('change', '#is_individual_contractor', function() {
            if ($(this).val() === '1') $('#ntn').val(''); else $('#contractor_cnic').val('');
            syncVendorTaxUi('add');
        });
        $(document).on('change', '#edit_is_individual_contractor', function() {
            if ($(this).val() === '1') $('#edit_ntn').val(''); else $('#edit_contractor_cnic').val('');
            syncVendorTaxUi('edit');
        });
        $(document).on('input', '.tp-cnic-field', function() { formatPakCnicInput(this); });
        $(document).on('input', '.tp-ntn-field', function() { formatNtnDigitsInput(this); });
        $(document).on('click', '#saveThirdPartyBtn', function(event) { event.preventDefault(); storeThirdParty(); });
        $(document).on('click', '#updateThirdPartyBtn', function(event) { event.preventDefault(); updateThirdParty(); });
        $(document).on('click', '.delete-tp-btn', function(event) { event.preventDefault(); deleteThirdParty(this); });
    }
})();
