(function() {
    'use strict';

    function getCsrfToken() {
        return $('meta[name="csrf-token"]').attr('content');
    }

    function showSwal(type, title, message) {
        Swal.fire({ icon: type, title: title, text: message });
    }

    function getOrganizations() {
        return Array.isArray(window.biometricOrganizations) ? window.biometricOrganizations : [];
    }

    function getSbus() {
        return Array.isArray(window.biometricSbus) ? window.biometricSbus : [];
    }

    function filterSbusByOrg(orgId) {
        var id = String(orgId || '');
        return getSbus().filter(function(s) {
            return String(s.organization_id) === id;
        });
    }

    function populateSbuSelect($sel, orgId, selectedId, placeholderDisabled) {
        var list = filterSbusByOrg(orgId);
        $sel.empty();
        if (!orgId) {
            $sel.append($('<option>', { value: '', text: 'Select organisation first' }));
            $sel.prop('disabled', true);
            return;
        }
        $sel.prop('disabled', false);
        $sel.append($('<option>', { value: '', text: 'Select SBU' }));
        list.forEach(function(s) {
            var opt = $('<option>', { value: s.id, text: s.name });
            if (String(selectedId) === String(s.id)) opt.prop('selected', true);
            $sel.append(opt);
        });
        if (placeholderDisabled && list.length === 0) {
            $sel.prop('disabled', true);
        }
    }

    if (typeof validate !== 'undefined') {
        validate.validators.nameLikeField = function(value) {
            var v = String(value || '').trim();
            if (!v) {
                return null;
            }
            if (!/[A-Za-z]/.test(v)) {
                return 'must contain at least one letter';
            }
            if (/[<>]/.test(v) || /<\s*script/i.test(v)) {
                return 'contains invalid characters';
            }
            return null;
        };

        validate.validators.serialField = function(value) {
            var v = String(value || '').trim();
            if (!v) {
                return null;
            }
            if (!/^[A-Za-z0-9\-_]+$/.test(v)) {
                return 'may only contain letters, numbers, hyphens, and underscores';
            }
            return null;
        };

        validate.validators.ipv4Field = function(value) {
            var v = String(value || '').trim();
            if (!v) {
                return null;
            }
            if (!/^(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}$/.test(v)) {
                return 'must be a valid IPv4 address';
            }
            return null;
        };
    }

    function formToObject(selector) {
        var obj = {};
        $(selector).serializeArray().forEach(function(item) {
            obj[item.name] = item.value;
        });
        return obj;
    }

    function biometricConstraints() {
        return {
            organization_id: { presence: { allowEmpty: false }, numericality: { onlyInteger: true, greaterThan: 0 } },
            sbu_id: { presence: { allowEmpty: false }, numericality: { onlyInteger: true, greaterThan: 0 } },
            device_name: { presence: { allowEmpty: false }, nameLikeField: true, length: { maximum: 255 } },
            serial_number: { presence: { allowEmpty: false }, serialField: true, length: { maximum: 100 } },
            device_type: { presence: { allowEmpty: false }, nameLikeField: true, length: { maximum: 100 } },
            brand_model: { presence: { allowEmpty: false }, nameLikeField: true, length: { maximum: 255 } },
            ip_address: { presence: { allowEmpty: false }, ipv4Field: true },
            port: { presence: { allowEmpty: false }, numericality: { onlyInteger: true, greaterThanOrEqualTo: 1, lessThanOrEqualTo: 65535 } },
            connection_type: { presence: { allowEmpty: false }, inclusion: { within: ['lan', 'wifi'], message: '^Select LAN or WiFi' } },
            device_status: { presence: { allowEmpty: false }, inclusion: { within: ['active', 'inactive', 'faulty'], message: '^Select a valid device status' } },
            installation_date: { presence: { allowEmpty: false }, format: { pattern: /^\d{4}-\d{2}-\d{2}$/, message: '^Enter a valid installation date' } }
        };
    }

    function clearFormMessages(formSelector) {
        $(formSelector + ' .is-invalid').removeClass('is-invalid');
        $(formSelector + ' .invalid-feedback').remove();
    }

    function showValidationErrors(formSelector, errors) {
        clearFormMessages(formSelector);
        if (!errors) {
            return;
        }
        Object.keys(errors).forEach(function(field) {
            var messages = errors[field];
            var msg = Array.isArray(messages) ? messages[0] : String(messages);
            var input = $(formSelector + ' [name="' + field + '"]');
            if (input.length) {
                input.addClass('is-invalid');
                input.after('<div class="invalid-feedback d-block">' + msg + '</div>');
            }
        });
    }

    function runClientValidation(formSelector) {
        if (typeof validate === 'undefined') {
            return true;
        }
        var values = formToObject(formSelector);
        var errs = validate(values, biometricConstraints(), { fullMessages: false });
        if (errs) {
            showValidationErrors(formSelector, errs);
            showSwal('error', 'Validation', 'Please fix the highlighted fields.');
            return false;
        }
        clearFormMessages(formSelector);
        return true;
    }

    function applyBdFilters() {
        var status = $('input[name="filterBdStatus"]:checked').val();
        $('#biometricDevicesGrid .col-md-6').each(function() {
            var card = $(this).find('.biometric-device-card');
            var st = String(card.data('bd-status'));
            $(this).toggle(status === 'all' || st === status);
        });
    }

    function clearBdFilters() {
        $('input#filterBdStatusAll').prop('checked', true);
        $('#biometricDevicesGrid .col-md-6').show();
    }

    function populateDetailCanvas(button) {
        var get = function(attr, fallback) {
            var value = button.getAttribute(attr);
            return (value !== null && value !== '') ? value : (fallback || '—');
        };
        $('#detailBdDeviceName').text(get('data-bd-device-name'));
        $('#detailBdSerial').text(get('data-bd-serial'));
        $('#detailBdType').text(get('data-bd-type'));
        $('#detailBdBrand').text(get('data-bd-brand'));
        $('#detailBdOrg').text(get('data-bd-org'));
        $('#detailBdSbu').text(get('data-bd-sbu'));
        var ip = get('data-bd-ip');
        var port = get('data-bd-port');
        $('#detailBdIpPort').text((ip !== '—' && port) ? ip + ':' + port : '—');
        var conn = get('data-bd-conn');
        $('#detailBdConn').text(conn === 'lan' ? 'LAN' : (conn === 'wifi' ? 'WiFi' : conn));
        var ds = get('data-bd-device-status');
        $('#detailBdDeviceStatus').text(ds.charAt(0).toUpperCase() + ds.slice(1));
        var on = get('data-bd-online');
        $('#detailBdOnline').text(on.charAt(0).toUpperCase() + on.slice(1));
        $('#detailBdLastSync').text(get('data-bd-last-sync') || '—');
        $('#detailBdInstall').text(get('data-bd-install') || '—');
        $('#detailBdCreatedBy').text(get('data-bd-created-by'));
        $('#detailBdCreatedAt').text(get('data-bd-created-at') || '—');
        $('#detailBdUpdatedAt').text(get('data-bd-updated-at') || '—');
    }

    function resetAddForm() {
        var form = document.getElementById('addBiometricDeviceForm');
        if (form) {
            form.reset();
        }
        clearFormMessages('#addBiometricDeviceForm');
        populateSbuSelect($('#bd_sbu_id'), '', '', true);
        applyDefaultScopeToAddForm();
    }

    function applyDefaultScopeToAddForm() {
        var scope = window.viewerEmployeeScope || {};
        var sbus = getSbus();

        if (scope.restricted && scope.organization_id && scope.sbu_id) {
            $('#bd_organization_id').val(String(scope.organization_id));
            populateSbuSelect($('#bd_sbu_id'), scope.organization_id, scope.sbu_id, false);
            return;
        }

        if (sbus.length === 1) {
            $('#bd_organization_id').val(String(sbus[0].organization_id));
            populateSbuSelect($('#bd_sbu_id'), sbus[0].organization_id, sbus[0].id, false);
        }
    }

    function resetEditForm() {
        var form = document.getElementById('editBiometricDeviceForm');
        if (form) {
            form.reset();
        }
        clearFormMessages('#editBiometricDeviceForm');
        $('#editBiometricDeviceForm').attr('data-update-url', '');
        populateSbuSelect($('#edit_bd_sbu_id'), '', '', true);
    }

    function storeDevice() {
        var form = $('#addBiometricDeviceForm');
        var url = form.data('store-url');
        if (!url) {
            showSwal('error', 'Error', 'Store URL not found.');
            return;
        }
        if (!runClientValidation('#addBiometricDeviceForm')) {
            return;
        }
        $.ajax({
            url: url,
            type: 'POST',
            data: form.serialize(),
            headers: { 'X-CSRF-TOKEN': getCsrfToken(), 'Accept': 'application/json' },
            beforeSend: function() { $('#saveBiometricDeviceBtn').prop('disabled', true).html('Saving...'); },
            success: function(response) {
                if (response.success) {
                    var canvasEl = document.getElementById('addBiometricDeviceCanvas');
                    var offcanvas = bootstrap.Offcanvas.getInstance(canvasEl);
                    if (offcanvas) {
                        offcanvas.hide();
                    }
                    Swal.fire({ icon: 'success', title: 'Success', text: response.message || 'Registered.', timer: 1500, showConfirmButton: false })
                        .then(function() { location.reload(); });
                } else {
                    showSwal('error', 'Error', response.message || 'Failed.');
                }
            },
            error: function(xhr) {
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    showValidationErrors('#addBiometricDeviceForm', xhr.responseJSON.errors);
                    showSwal('error', 'Validation', 'Please fix the highlighted fields.');
                } else {
                    showSwal('error', 'Error', (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Failed to register.');
                }
            },
            complete: function() { $('#saveBiometricDeviceBtn').prop('disabled', false).html('<i class="bi bi-check-lg me-1"></i>Register'); }
        });
    }

    function loadEditData(button) {
        var editUrl = $(button).data('edit-url');
        if (!editUrl) {
            showSwal('error', 'Error', 'Edit URL not found.');
            return;
        }
        resetEditForm();
        $.ajax({
            url: editUrl,
            type: 'GET',
            headers: { 'Accept': 'application/json' },
            success: function(response) {
                if (response.success && response.data) {
                    var data = response.data;
                    $('#edit_bd_id').val(data.id || '');
                    $('#edit_bd_organization_id').val(String(data.organization_id || ''));
                    populateSbuSelect($('#edit_bd_sbu_id'), data.organization_id, data.sbu_id, false);
                    $('#edit_bd_device_name').val(data.device_name || '');
                    $('#edit_bd_serial_number').val(data.serial_number || '');
                    $('#edit_bd_device_type').val(data.device_type || '');
                    $('#edit_bd_brand_model').val(data.brand_model || '');
                    $('#edit_bd_ip_address').val(data.ip_address || '');
                    $('#edit_bd_port').val(data.port != null ? data.port : '');
                    $('#edit_bd_connection_type').val(data.connection_type || '');
                    $('#edit_bd_device_status').val(data.device_status || '');
                    $('#edit_bd_installation_date').val(data.installation_date || '');
                    $('#editBiometricDeviceForm').attr('data-update-url', $(button).data('update-url'));
                } else {
                    showSwal('error', 'Error', response.message || 'Failed to load.');
                }
            },
            error: function(xhr) {
                showSwal('error', 'Error', (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Failed to load.');
            }
        });
    }

    function updateDevice() {
        var form = $('#editBiometricDeviceForm');
        var url = form.attr('data-update-url');
        if (!url) {
            showSwal('error', 'Error', 'Update URL not found.');
            return;
        }
        if (!runClientValidation('#editBiometricDeviceForm')) {
            return;
        }
        $.ajax({
            url: url,
            type: 'POST',
            data: form.serialize(),
            headers: { 'X-CSRF-TOKEN': getCsrfToken(), 'Accept': 'application/json' },
            beforeSend: function() { $('#updateBiometricDeviceBtn').prop('disabled', true).html('Updating...'); },
            success: function(response) {
                if (response.success) {
                    var canvasEl = document.getElementById('editBiometricDeviceCanvas');
                    var offcanvas = bootstrap.Offcanvas.getInstance(canvasEl);
                    if (offcanvas) {
                        offcanvas.hide();
                    }
                    Swal.fire({ icon: 'success', title: 'Success', text: response.message || 'Updated.', timer: 1500, showConfirmButton: false })
                        .then(function() { location.reload(); });
                } else {
                    showSwal('error', 'Error', response.message || 'Failed.');
                }
            },
            error: function(xhr) {
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    showValidationErrors('#editBiometricDeviceForm', xhr.responseJSON.errors);
                    showSwal('error', 'Validation', 'Please fix the highlighted fields.');
                } else {
                    showSwal('error', 'Error', (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Failed to update.');
                }
            },
            complete: function() { $('#updateBiometricDeviceBtn').prop('disabled', false).html('<i class="bi bi-check-lg me-1"></i>Update'); }
        });
    }

    function deleteDevice(button) {
        var deleteUrl = $(button).data('delete-url');
        if (!deleteUrl) {
            showSwal('error', 'Error', 'Delete URL not found.');
            return;
        }
        Swal.fire({
            title: 'Are you sure?',
            text: 'This device registration will be removed.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!'
        }).then(function(result) {
            if (!result.isConfirmed) {
                return;
            }
            $.ajax({
                url: deleteUrl,
                type: 'POST',
                data: { _method: 'DELETE', _token: getCsrfToken() },
                headers: { 'X-CSRF-TOKEN': getCsrfToken(), 'Accept': 'application/json' },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({ icon: 'success', title: 'Deleted', text: response.message || 'Deleted.', timer: 1500, showConfirmButton: false })
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

    $(document).ready(function() {
        populateSbuSelect($('#bd_sbu_id'), '', '', true);
        populateSbuSelect($('#edit_bd_sbu_id'), '', '', true);

        $('#bd_organization_id').on('change', function() {
            var orgId = $(this).val();
            populateSbuSelect($('#bd_sbu_id'), orgId, '', false);
        });

        $('#edit_bd_organization_id').on('change', function() {
            var orgId = $(this).val();
            populateSbuSelect($('#edit_bd_sbu_id'), orgId, '', false);
        });

        var detailCanvas = document.getElementById('biometricDeviceDetailCanvas');
        if (detailCanvas) {
            detailCanvas.addEventListener('show.bs.offcanvas', function(event) {
                var button = event.relatedTarget;
                if (button && button.classList.contains('view-bd-btn')) {
                    populateDetailCanvas(button);
                }
            });
        }

        var addCanvas = document.getElementById('addBiometricDeviceCanvas');
        if (addCanvas) {
            addCanvas.addEventListener('show.bs.offcanvas', resetAddForm);
        }

        var editCanvas = document.getElementById('editBiometricDeviceCanvas');
        if (editCanvas) {
            editCanvas.addEventListener('show.bs.offcanvas', function(event) {
                var button = event.relatedTarget;
                if (button && button.classList.contains('edit-bd-btn')) {
                    loadEditData(button);
                }
            });
            editCanvas.addEventListener('hidden.bs.offcanvas', resetEditForm);
        }

        $('.filter-bd-status').on('change', applyBdFilters);
        $('#clearBdFiltersBtn').on('click', clearBdFilters);
        $(document).on('click', '#saveBiometricDeviceBtn', function(e) { e.preventDefault(); storeDevice(); });
        $(document).on('click', '#updateBiometricDeviceBtn', function(e) { e.preventDefault(); updateDevice(); });
        $(document).on('click', '.delete-bd-btn', function(e) { e.preventDefault(); deleteDevice(this); });
    });
})();
