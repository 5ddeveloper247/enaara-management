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
        $('#detailTpAddress').text(get('data-tp-address'));

        const lat = button.getAttribute('data-tp-latitude');
        const lng = button.getAttribute('data-tp-longitude');
        $('#detailTpCoordinates').text((lat && lng) ? lat + ', ' + lng : '—');
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
                return '<span class="tp-ms-chip">' + label + '<span class="tp-ms-chip-x" data-remove-id="' + id + '">×</span></span>';
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
                    '<span>' + item.name + '</span>' +
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

    function resetAddTpForm() {
        const form = document.getElementById('addThirdPartyForm');
        if (form) form.reset();
        clearFormMessages('#addThirdPartyForm');
        $('#is_active').val('1');
        tpState.addOrganization.clear(false);
        updateAddSbuOptions([]);
        tpState.addSbu.clear(false);
    }

    function resetEditTpForm() {
        const form = document.getElementById('editThirdPartyForm');
        if (form) form.reset();
        clearFormMessages('#editThirdPartyForm');
        $('#edit_is_active').val('1');
        $('#editThirdPartyForm').attr('data-update-url', '');
        tpState.editOrganization.clear(false);
        updateEditSbuOptions([]);
        tpState.editSbu.clear(false);
    }

    function storeThirdParty() {
        const form = $('#addThirdPartyForm');
        const url = form.data('store-url');
        clearFormMessages('#addThirdPartyForm');

        if (!url) {
            showSwal('error', 'Error', 'Store URL not found.');
            return;
        }

        $.ajax({
            url: url,
            type: 'POST',
            data: form.serialize(),
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
                    $('#edit_city').val(data.city || '');
                    $('#edit_address').val(data.address || '');
                    $('#edit_latitude').val(data.latitude || '');
                    $('#edit_longitude').val(data.longitude || '');
                    $('#edit_is_active').val(data.is_active === 1 || data.is_active === '1' || data.is_active === true ? '1' : '0');
                    $('#editThirdPartyForm').attr('data-update-url', $(button).data('update-url'));
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
        $.ajax({
            url: url,
            type: 'POST',
            data: form.serialize(),
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
        $(document).on('click', '#saveThirdPartyBtn', function(event) { event.preventDefault(); storeThirdParty(); });
        $(document).on('click', '#updateThirdPartyBtn', function(event) { event.preventDefault(); updateThirdParty(); });
        $(document).on('click', '.delete-tp-btn', function(event) { event.preventDefault(); deleteThirdParty(this); });
    }
})();
