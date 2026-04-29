(function () {
    'use strict';

    let outsourcedTable = null;
    let currentMode = 'internal';
    let selectedOutsourcedEmployeeId = null;
    let outsourcedCropper = null;
    let outsourcedCroppedImageBlob = null;
    let outsourcedOriginalPhotoName = '';
    let outsourcedExistingPhotoUrl = '';

    function escHtml(str) {
        if (str === null || str === undefined) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function formatCnicInput(input) {
        if (!input) return;
        let val = (input.value || '').replace(/\D/g, '');
        if (val.length > 13) {
            val = val.substring(0, 13);
        }
        let formatted = '';
        if (val.length > 0) {
            formatted = val.substring(0, 5);
            if (val.length > 5) {
                formatted += '-' + val.substring(5, 12);
                if (val.length > 12) {
                    formatted += '-' + val.substring(12, 13);
                }
            }
        }
        input.value = formatted;
    }

    function formatPhoneInput(input) {
        if (!input) return;
        let val = (input.value || '').replace(/\D/g, '');
        if (val.length > 15) {
            val = val.substring(0, 15);
        }
        input.value = val;
    }

    function setOutsourcedPhotoPreview(photoUrl, fromServer) {
        const previewImage = document.getElementById('oePhotoPreviewImage');
        const placeholder = document.getElementById('oePhotoPlaceholderIcon');
        const clearBtn = document.getElementById('oeClearPhotoBtn');
        if (!previewImage || !placeholder || !clearBtn) return;

        const hasPhoto = !!photoUrl;
        if (hasPhoto) {
            previewImage.src = photoUrl;
            previewImage.classList.remove('d-none');
            placeholder.classList.add('d-none');
        } else {
            previewImage.src = '';
            previewImage.classList.add('d-none');
            placeholder.classList.remove('d-none');
        }

        if (fromServer) {
            clearBtn.classList.add('d-none');
        } else {
            clearBtn.classList.toggle('d-none', !hasPhoto);
        }
    }

    function resetOutsourcedPhotoState() {
        outsourcedCroppedImageBlob = null;
        outsourcedOriginalPhotoName = '';
        outsourcedExistingPhotoUrl = '';
        const photoInput = document.getElementById('oePhoto');
        if (photoInput) photoInput.value = '';
        setOutsourcedPhotoPreview('', false);
        if (outsourcedCropper) {
            outsourcedCropper.destroy();
            outsourcedCropper = null;
        }
    }

    function validateOutsourcedPhoto(file) {
        if (!file) return { ok: false, message: 'No file selected.' };
        const allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        const extension = (file.name.split('.').pop() || '').toLowerCase();
        if (!allowedExtensions.includes(extension)) {
            return { ok: false, message: 'Only JPG, JPEG, PNG and WEBP files are allowed.' };
        }
        const maxSize = 2 * 1024 * 1024;
        if (file.size > maxSize) {
            return { ok: false, message: 'Maximum allowed file size is 2MB.' };
        }
        return { ok: true };
    }

    function openOutsourcedPhotoCropper(file) {
        const validation = validateOutsourcedPhoto(file);
        if (!validation.ok) {
            showError(validation.message, 'Invalid Photo');
            return;
        }

        outsourcedOriginalPhotoName = file.name || 'profile.jpg';
        const reader = new FileReader();
        reader.onload = function (e) {
            const cropperImage = document.getElementById('oeCropperImage');
            const modalEl = document.getElementById('oeCropperModal');
            if (!cropperImage || !modalEl || !window.bootstrap) return;
            cropperImage.src = e.target.result;
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();

            const onShown = function () {
                if (outsourcedCropper) outsourcedCropper.destroy();
                outsourcedCropper = new Cropper(cropperImage, {
                    aspectRatio: 1,
                    viewMode: 1,
                    dragMode: 'move',
                    autoCropArea: 0.85,
                    restore: false,
                    guides: true,
                    center: true,
                    highlight: false,
                    cropBoxMovable: true,
                    cropBoxResizable: true,
                    toggleDragModeOnDblclick: false
                });
                modalEl.removeEventListener('shown.bs.modal', onShown);
            };
            modalEl.addEventListener('shown.bs.modal', onShown);
        };
        reader.readAsDataURL(file);
    }

    function getOutsourcedOrganizations() {
        if (!Array.isArray(window.outsourcedOrganizations)) return [];
        return window.outsourcedOrganizations;
    }

    function getOutsourcedVendors() {
        if (!Array.isArray(window.outsourcedVendors)) return [];
        return window.outsourcedVendors;
    }

    function fillSelectOptions(selectEl, items, placeholder, valueKey, labelKey, selectedValue) {
        if (!selectEl) return;
        const selected = selectedValue === null || selectedValue === undefined ? '' : String(selectedValue);
        const list = Array.isArray(items) ? items : [];
        let html = `<option value="">${placeholder}</option>`;
        list.forEach((item) => {
            const value = item && item[valueKey] !== undefined && item[valueKey] !== null ? String(item[valueKey]) : '';
            if (!value) return;
            const label = item && item[labelKey] !== undefined && item[labelKey] !== null ? String(item[labelKey]) : '';
            const isSelected = value === selected ? ' selected' : '';
            html += `<option value="${escHtml(value)}"${isSelected}>${escHtml(label)}</option>`;
        });
        selectEl.innerHTML = html;
    }

    function getOrganizationById(orgId) {
        const organizations = getOutsourcedOrganizations();
        const selected = String(orgId || '');
        return organizations.find((org) => String(org.id) === selected) || null;
    }

    function getSbuById(org, sbuId) {
        if (!org || !Array.isArray(org.sbus)) return null;
        const selected = String(sbuId || '');
        return org.sbus.find((sbu) => String(sbu.id) === selected) || null;
    }

    function populateSbuOptions(orgId, selectedSbuId) {
        const sbuSelect = document.getElementById('oeSbuId');
        const org = getOrganizationById(orgId);
        if (!org || !String(orgId || '').trim()) {
            fillSelectOptions(sbuSelect, [], 'Select organization first', 'id', 'name', selectedSbuId);
            return;
        }
        const sbus = Array.isArray(org.sbus) ? org.sbus : [];
        fillSelectOptions(sbuSelect, sbus, 'Select SBU', 'id', 'name', selectedSbuId);
    }

    function populateDepartmentOptions(orgId, sbuId, selectedDepartmentId) {
        const departmentSelect = document.getElementById('oeDepartmentId');
        const org = getOrganizationById(orgId);
        const sbu = getSbuById(org, sbuId);
        if (!sbu || !String(sbuId || '').trim()) {
            fillSelectOptions(departmentSelect, [], 'Select SBU first', 'id', 'name', selectedDepartmentId);
            return;
        }
        const departments = Array.isArray(sbu.departments) ? sbu.departments : [];
        fillSelectOptions(departmentSelect, departments, 'Select department', 'id', 'name', selectedDepartmentId);
    }

    function populateVendorOptions(orgId, sbuId, selectedVendorId) {
        const vendorSelect = document.getElementById('oeCompanyName');
        if (!vendorSelect) return;
        const selectedOrgId = parseInt(orgId, 10);
        const selectedSbuId = parseInt(sbuId, 10);

        if (!Number.isFinite(selectedOrgId) || selectedOrgId <= 0) {
            vendorSelect.innerHTML = '<option value="">Select organization first</option>';
            return;
        }

        if (!Number.isFinite(selectedSbuId) || selectedSbuId <= 0) {
            vendorSelect.innerHTML = '<option value="">Select SBU first</option>';
            return;
        }

        const vendors = getOutsourcedVendors().filter(function (vendor) {
            const orgIds = Array.isArray(vendor.organization_ids) ? vendor.organization_ids.map(Number) : [];
            const sbuIds = Array.isArray(vendor.sbu_ids) ? vendor.sbu_ids.map(Number) : [];
            return orgIds.includes(selectedOrgId) && sbuIds.includes(selectedSbuId);
        });

        fillSelectOptions(vendorSelect, vendors, 'Select contractor company', 'id', 'third_party_name', selectedVendorId);
    }

    let availableFloors = [];
    
    function renderOutsourcedFloorChips() {
        const floorSelect = document.getElementById('oeAssignedFloorsSelect');
        const floorChips = document.getElementById('oeFloorChips');
        const floorPh = document.getElementById('oeFloorPh');
        if (!floorSelect || !floorChips || !floorPh) return;
        const selectedOptions = Array.from(floorSelect.options).filter(o => o.selected);
        floorChips.innerHTML = '';
        
        if (selectedOptions.length === 0) {
            floorPh.style.display = 'inline-block';
        } else {
            floorPh.style.display = 'none';
            selectedOptions.forEach(opt => {
                const chip = document.createElement('div');
                chip.className = 'emp-dept-chip';
                chip.innerHTML = `${escHtml(opt.text)} <span class="emp-floor-chip-rm flex-shrink-0" data-id="${opt.value}">&times;</span>`;
                floorChips.appendChild(chip);
            });
        }
    }
    
    function syncOutsourcedFloorDropdownState() {
        const floorSelect = document.getElementById('oeAssignedFloorsSelect');
        const floorList = document.getElementById('oeFloorList');
        if (!floorList || !floorSelect) return;
        const selectedIds = Array.from(floorSelect.options).filter(o => o.selected).map(o => o.value);
        const items = floorList.querySelectorAll('.emp-floor-list-opt');
        items.forEach(item => {
            const id = item.getAttribute('data-id');
            if (selectedIds.includes(id)) {
                item.style.backgroundColor = '#eef2f6';
                item.style.fontWeight = 'bold';
            } else {
                item.style.backgroundColor = 'transparent';
                item.style.fontWeight = 'normal';
            }
        });
    }
    
    function buildOutsourcedFloorDropdownOptions(filter = '') {
        const floorSelect = document.getElementById('oeAssignedFloorsSelect');
        const floorList = document.getElementById('oeFloorList');
        if (!floorList || !floorSelect) return;
        floorList.innerHTML = '';
        const lowerFilter = filter.toLowerCase();
        
        availableFloors.forEach(floor => {
            const floorName = String(floor.name || '');
            if (floorName.toLowerCase().includes(lowerFilter)) {
                const div = document.createElement('div');
                div.className = 'emp-floor-list-opt p-2 border-bottom text-dark';
                div.style.cursor = 'pointer';
                div.setAttribute('data-id', floor.id);
                div.innerText = floorName;
                
                div.addEventListener('click', function(e) {
                    e.stopPropagation();
                    let opt = Array.from(floorSelect.options).find(o => o.value == floor.id);
                    if (opt) {
                        opt.selected = !opt.selected;
                    } else {
                        opt = new Option(floorName, floor.id);
                        opt.selected = true;
                        floorSelect.add(opt);
                    }
                    renderOutsourcedFloorChips();
                    syncOutsourcedFloorDropdownState();
                });
                
                floorList.appendChild(div);
            }
        });
        syncOutsourcedFloorDropdownState();
    }

    function setOutsourcedFloorPlaceholder(text) {
        const floorPh = document.getElementById('oeFloorPh');
        if (floorPh) {
            floorPh.textContent = text;
        }
    }

    function populateOutsourcedFloors(orgId, sbuId, selectedFloorIds = []) {
        const floorSelect = document.getElementById('oeAssignedFloorsSelect');
        if (!floorSelect) return;
        availableFloors = [];
        const org = getOrganizationById(orgId);
        const sbu = getSbuById(org, sbuId);
        if (!sbu || !String(sbuId || '').trim()) {
            floorSelect.innerHTML = '';
            setOutsourcedFloorPlaceholder('Select SBU first');
            renderOutsourcedFloorChips();
            buildOutsourcedFloorDropdownOptions();
            return;
        }
        if (sbu.floors) {
            availableFloors = sbu.floors;
        }
        floorSelect.innerHTML = '';
        availableFloors.sort((a,b) => String(a.name).localeCompare(String(b.name)));
        availableFloors.forEach(floor => {
            const opt = new Option(floor.name, floor.id);
            if (selectedFloorIds.includes(parseInt(floor.id))) {
                opt.selected = true;
            }
            floorSelect.add(opt);
        });
        setOutsourcedFloorPlaceholder('Select Floors...');
        renderOutsourcedFloorChips();
        buildOutsourcedFloorDropdownOptions();
    }

    function initOutsourcedTable() {
        if (!document.getElementById('outsourcedEmployeeTable') || outsourcedTable) return;

        outsourcedTable = initUserDataTable('#outsourcedEmployeeTable', {
            pageLength: 10,
            searching: true,
            processing: true,
            ajax: {
                url: window.outsourcedEmployeeDataUrl,
                type: 'GET',
                data: function (d) {
                    const f = window.employeeFilters || {};
                    d.filter_organization = f.organization || '';
                    d.filter_sbu = f.sbu || '';
                    d.filter_department = f.department || '';
                    d.filter_name = f.name || '';
                    d.filter_cnic = f.cnic || '';
                },
                dataSrc: function (res) {
                    return res && res.success ? (res.data || []) : [];
                }
            },
            columns: [
                {
                    data: 'photo_url',
                    orderable: false,
                    render: function (d, type, row) {
                        if (d) {
                            return `<img src="${escHtml(d)}" alt="Photo"
                                        style="width:36px;height:36px;border-radius:50%;object-fit:cover;">`;
                        }
                
                        const name = row.full_name || '';
                        const words = name.trim().split(' ').filter(Boolean);
                
                        let initials = 'OE';
                        if (words.length === 1) {
                            initials = words[0].substring(0, 2).toUpperCase();
                        } else if (words.length > 1) {
                            initials = (words[0][0] + words[1][0]).toUpperCase();
                        }
                
                        return `
                            <div style="
                                width:36px;
                                height:36px;
                                border-radius:50%;
                                display:flex;
                                align-items:center;
                                justify-content:center;
                                background:#0d6efd;
                                color:#fff;
                                font-size:13px;
                                font-weight:600;
                            ">
                                ${escHtml(initials)}
                            </div>
                        `;
                    }
                },
                
                { data: 'full_name' },
                { data: 'cnic_number' },
                { data: 'mobile_number' },
                { data: 'contractor_company_name' },
                { data: 'supervisor_name' },
                { data: 'supervisor_contact_number' },
                { data: 'department' },
                { data: 'job_role_trade' },
                { data: 'placement_floor' },
                { data: 'date_of_deployment' },
                { data: 'biometric_id', render: (d) => d || '-' },
                { data: 'attendance_access', render: (d) => d ? '<span class="badge bg-success">Granted</span>' : '<span class="badge bg-secondary">Not Granted</span>' },
                {
                    data: 'id',
                    className: 'text-end',
                    orderable: false,
                    render: function (id) {
                        const recordId = Number(id);
                        return `
                            <div class="d-inline-flex gap-1">
                                <button type="button" class="action-btn border-0 text-white btn-primary" title="View Details" onclick="window.openOutsourcedEmployeeDetail(${recordId})">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button type="button" class="action-btn border-0 text-white btn-success" title="Edit" onclick="window.openOutsourcedEmployee(${recordId})">
                                    <i class="bi bi-pencil"></i>
                                </button>
                            </div>
                        `;
                    }
                }
            ],
            order: [[0, 'desc']],
            scrollX: false,
            responsive: false,
            dom: '<"row px-4 py-3"<"col-md-6"l><"col-md-6 d-flex justify-content-end"<f>>>r<"employee-datatable-scroll"t><"row px-4 py-2"<"col-md-5"i><"col-md-7"p>>',
            language: {
                search: '',
                searchPlaceholder: 'Search outsourced employees...',
                lengthMenu: 'Show _MENU_ entries',
                info: 'Showing _START_ to _END_ of _TOTAL_ outsourced employees',
                infoEmpty: 'No outsourced employees available',
                zeroRecords: 'No matching outsourced employees found'
            }
        });
    }

    function clearOutsourcedValidation(form) {
        form.querySelectorAll('.is-invalid').forEach((el) => el.classList.remove('is-invalid'));
        const floorBox = document.getElementById('oeFloorBox');
        if (floorBox) floorBox.classList.remove('is-invalid');
        form.querySelectorAll('.field-error-msg').forEach((el) => el.remove());
    }

    function showOutsourcedFieldErrors(form, errors) {
        if (!errors) return;
        Object.entries(errors).forEach(([field, messages]) => {
            let input = form.querySelector(`[name="${field}"]`);
            if (!input && field === 'assigned_floor_ids') {
                input = document.getElementById('oeAssignedFloorsSelect');
            }
            if (!input && field.indexOf('assigned_floor_ids.') === 0) {
                input = document.getElementById('oeAssignedFloorsSelect');
            }
            if (!input) return;
            const anchor = input.classList.contains('d-none') ? document.getElementById('oeFloorBox') : input;
            if (anchor && anchor !== input) {
                anchor.classList.add('is-invalid');
            } else {
                input.classList.add('is-invalid');
            }
            const msg = document.createElement('div');
            msg.className = 'field-error-msg text-danger small mt-1';
            msg.textContent = Array.isArray(messages) ? messages[0] : String(messages);
            (anchor || input).insertAdjacentElement('afterend', msg);
        });
    }

    function resetOutsourcedForm() {
        const form = document.getElementById('outsourcedEmployeeForm');
        if (!form) return;
        form.reset();
        resetOutsourcedPhotoState();
        const idInput = document.getElementById('outsourcedEmployeeId');
        if (idInput) idInput.value = '';
        const title = document.getElementById('outsourcedEmployeeCanvasLabel');
        if (title) title.textContent = 'Add Outsourced Employee';
        populateSbuOptions('', '');
        populateDepartmentOptions('', '', '');
        populateVendorOptions('', '', '');
        populateOutsourcedFloors('', '');
        clearOutsourcedValidation(form);
    }

    async function openOutsourcedEmployee(id) {
        const form = document.getElementById('outsourcedEmployeeForm');
        if (!form) return;
        resetOutsourcedForm();
        const title = document.getElementById('outsourcedEmployeeCanvasLabel');
        if (title) title.textContent = 'Update Outsourced Employee';
        try {
            const response = await fetch(`${window.outsourcedEmployeeShowUrlBase}/${id}`);
            const res = await response.json();
            if (!response.ok || !res.success) {
                showError(res.message || 'Unable to load record.');
                return;
            }
            const d = res.data || {};
            document.getElementById('outsourcedEmployeeId').value = d.id || '';
            document.getElementById('oeFullName').value = d.full_name || '';
            document.getElementById('oeCnic').value = d.cnic_number || '';
            document.getElementById('oeMobile').value = d.mobile_number || '';
            document.getElementById('oeSupervisorName').value = d.supervisor_name || '';
            document.getElementById('oeSupervisorContact').value = d.supervisor_contact_number || '';
            document.getElementById('oeOrganizationId').value = d.organization_id || '';
            populateSbuOptions(d.organization_id || '', d.sbu_id || '');
            populateDepartmentOptions(d.organization_id || '', d.sbu_id || '', d.department_id || '');
            populateVendorOptions(d.organization_id || '', d.sbu_id || '', d.contractor_company_id || '');
            document.getElementById('oeJobRole').value = d.job_role_trade || '';
            populateOutsourcedFloors(d.organization_id || '', d.sbu_id || '', d.assigned_floor_ids || []);
            document.getElementById('oeDeploymentDate').value = d.date_of_deployment || '';
            document.getElementById('oeBiometricId').value = d.biometric_id || '';
            document.getElementById('oeAttendanceAccess').value = d.attendance_access ? '1' : '0';
            outsourcedExistingPhotoUrl = d.photo_url || '';
            setOutsourcedPhotoPreview(outsourcedExistingPhotoUrl, true);
            const canvas = document.getElementById('outsourcedEmployeeCanvas');
            if (canvas && window.bootstrap) {
                bootstrap.Offcanvas.getOrCreateInstance(canvas).show();
            }
        } catch (e) {
            showError('Network error');
        }
    }

    function setDetailValue(id, value) {
        const el = document.getElementById(id);
        if (!el) return;
        el.textContent = value ? String(value) : '-';
    }

    function getInitials(value) {
        const text = (value || '').trim();
        if (!text) return 'OE';
        const words = text.split(/\s+/).filter(Boolean);
        if (!words.length) return 'OE';
        if (words.length === 1) {
            return words[0].substring(0, 2).toUpperCase();
        }
        return (words[0][0] + words[1][0]).toUpperCase();
    }

    async function openOutsourcedEmployeeDetail(id) {
        try {
            const response = await fetch(`${window.outsourcedEmployeeShowUrlBase}/${id}`);
            const res = await response.json();
            if (!response.ok || !res.success) {
                showError(res.message || 'Unable to load details.');
                return;
            }
            const d = res.data || {};
            selectedOutsourcedEmployeeId = d.id || id || null;
            setDetailValue('oeDetailFullName', d.full_name);
            setDetailValue('oeDetailCnic', d.cnic_number);
            setDetailValue('oeDetailMobile', d.mobile_number);
            setDetailValue('oeDetailCompany', d.contractor_company_name);
            setDetailValue('oeDetailSupervisorName', d.supervisor_name);
            setDetailValue('oeDetailSupervisorContact', d.supervisor_contact_number);
            setDetailValue('oeDetailOrganization', d.organization);
            setDetailValue('oeDetailSbu', d.sbu);
            setDetailValue('oeDetailDepartment', d.department);
            setDetailValue('oeDetailJobRole', d.job_role_trade);
            setDetailValue('oeDetailPlacementFloor', d.placement_floor);
            setDetailValue('oeDetailDeploymentDate', d.date_of_deployment);
            setDetailValue('oeDetailBiometricId', d.biometric_id);
            setDetailValue('oeDetailAttendanceAccess', d.attendance_access ? 'Granted' : 'Not Granted');
            setDetailValue('oeDetailInfo', `${d.department || '-'} - ${d.biometric_id || '-'}`);

            const detailPhoto = document.getElementById('oeDetailPhoto');
            const detailPlaceholder = document.getElementById('oeDetailPhotoPlaceholder');
            if (detailPhoto && detailPlaceholder) {
                detailPlaceholder.textContent = getInitials(d.full_name);
                if (d.photo_url) {
                    detailPhoto.src = d.photo_url;
                    detailPhoto.classList.remove('d-none');
                    detailPlaceholder.classList.add('d-none');
                } else {
                    detailPhoto.src = '';
                    detailPhoto.classList.add('d-none');
                    detailPlaceholder.classList.remove('d-none');
                }
            }

            const detailCanvas = document.getElementById('outsourcedEmployeeDetailCanvas');
            if (detailCanvas && window.bootstrap) {
                bootstrap.Offcanvas.getOrCreateInstance(detailCanvas).show();
            }
        } catch (e) {
            showError('Network error');
        }
    }

    function setEmployeeDirectoryMode(mode) {
        const previousMode = currentMode;
        currentMode = mode === 'outsourced' ? 'outsourced' : 'internal';

        const internalWrappers = ['tableViewWrapper', 'gridViewWrapper'];
        const outsourcedWrapper = document.getElementById('outsourcedViewWrapper');
        const btnTable = document.getElementById('btnTableView');
        const btnGrid = document.getElementById('btnGridView');
        const exportBtn = document.getElementById('exportBtn');
        const filterBtn = document.getElementById('filterDropdownBtn');
        const addBtn = document.getElementById('addEmployeeActionBtn');
        const listingTabs = document.querySelector('.employee-listing-tabs')?.closest('.employee-listing-tab-slot');

        if (currentMode === 'outsourced') {
            if (previousMode !== 'outsourced') {
                clearHeaderFiltersAndState();
            }
            internalWrappers.forEach((id) => {
                const el = document.getElementById(id);
                if (el) el.classList.add('d-none');
            });
            if (outsourcedWrapper) outsourcedWrapper.classList.remove('d-none');
            if (btnTable) btnTable.classList.add('d-none');
            if (btnGrid) btnGrid.classList.add('d-none');
            if (exportBtn) exportBtn.classList.remove('d-none');
            if (filterBtn) filterBtn.classList.remove('d-none');
            if (listingTabs) listingTabs.classList.add('d-none');
            if (addBtn) {
                addBtn.innerHTML = '<i class="bi bi-person-plus me-1"></i>Add Outsourced Employee';
                addBtn.setAttribute('href', '#');
            }
            initOutsourcedTable();
            if (outsourcedTable) outsourcedTable.ajax.reload(null, false);
        } else {
            const tableView = document.getElementById('tableViewWrapper');
            if (tableView) tableView.classList.remove('d-none');
            if (outsourcedWrapper) outsourcedWrapper.classList.add('d-none');
            if (btnTable) btnTable.classList.remove('d-none');
            if (btnGrid) btnGrid.classList.remove('d-none');
            if (exportBtn) exportBtn.classList.remove('d-none');
            if (filterBtn) filterBtn.classList.remove('d-none');
            if (listingTabs) listingTabs.classList.remove('d-none');
            if (addBtn) {
                addBtn.innerHTML = '<i class="bi bi-person-plus me-1"></i>Add Employee';
                addBtn.setAttribute('href', addBtn.dataset.internalHref || window.registerUrl || '#');
            }
            if (window.employeeFilters) {
                window.employeeFilters.employeeType = 'Internal';
            }
            if (window.employeeTableRef) {
                window.employeeTableRef.ajax.reload(null, false);
            }
        }

        const employeeTypeFilterWrap = document.getElementById('filterEmployeeType')?.closest('.mb-3');
        if (employeeTypeFilterWrap) {
            employeeTypeFilterWrap.classList.toggle('d-none', currentMode === 'outsourced');
        }
    }

    function syncHeaderFiltersToState() {
        window.employeeFilters = window.employeeFilters || {};
        window.employeeFilters.organization = ($('#filterOrganization').val() || '').trim();
        window.employeeFilters.sbu = ($('#filterSbu').val() || '').trim();
        window.employeeFilters.department = ($('#filterDepartment').val() || '').trim();
        window.employeeFilters.name = ($('#filterName').val() || '').trim();
        window.employeeFilters.cnic = ($('#filterCnic').val() || '').trim();
    }

    function clearHeaderFiltersAndState() {
        $('#filterEmployeeType').val('');
        $('#filterOrganization').val('');
        $('#filterSbu').val('');
        $('#filterDepartment').val('');
        $('#filterName').val('');
        $('#filterCnic').val('');

        window.employeeFilters = window.employeeFilters || {};
        window.employeeFilters.employeeType = '';
        window.employeeFilters.organization = '';
        window.employeeFilters.sbu = '';
        window.employeeFilters.department = '';
        window.employeeFilters.name = '';
        window.employeeFilters.cnic = '';
    }

    document.addEventListener('DOMContentLoaded', function () {
        const addBtn = document.getElementById('addEmployeeActionBtn');
        const outsourcedForm = document.getElementById('outsourcedEmployeeForm');
        const canvas = document.getElementById('outsourcedEmployeeCanvas');
        const cnicInput = document.getElementById('oeCnic');
        const mobileInput = document.getElementById('oeMobile');
        const supervisorContactInput = document.getElementById('oeSupervisorContact');
        const organizationSelect = document.getElementById('oeOrganizationId');
        const sbuSelect = document.getElementById('oeSbuId');
        const detailEditBtn = document.getElementById('oeDetailEditBtn');
        const detailCanvas = document.getElementById('outsourcedEmployeeDetailCanvas');
        const photoInput = document.getElementById('oePhoto');
        const photoTrigger = document.getElementById('oePhotoTrigger');
        const clearPhotoBtn = document.getElementById('oeClearPhotoBtn');
        const cropBtn = document.getElementById('oeCropBtn');
        const cropperModalEl = document.getElementById('oeCropperModal');

        if (addBtn) {
            addBtn.addEventListener('click', function (e) {
                if (currentMode !== 'outsourced') return;
                e.preventDefault();
                resetOutsourcedForm();
                if (canvas && window.bootstrap) {
                    bootstrap.Offcanvas.getOrCreateInstance(canvas).show();
                }
            });
        }

        if (canvas) {
            canvas.addEventListener('hidden.bs.offcanvas', function () {
                resetOutsourcedForm();
            });
        }

        const floorBox = document.getElementById('oeFloorBox');
        const floorDd = document.getElementById('oeFloorDd');
        const floorSearch = document.getElementById('oeFloorSearch');
        const floorSelect = document.getElementById('oeAssignedFloorsSelect');

        if (floorSearch) {
            floorSearch.addEventListener('input', function(e) {
                buildOutsourcedFloorDropdownOptions(e.target.value);
            });
        }
        
        if (floorBox) {
            floorBox.addEventListener('click', function(e) {
                if (e.target.classList && e.target.classList.contains('emp-floor-chip-rm') || e.target.closest('.emp-floor-chip-rm')) {
                    const el = e.target.classList.contains('emp-floor-chip-rm') ? e.target : e.target.closest('.emp-floor-chip-rm');
                    const idToRemove = el.getAttribute('data-id');
                    const opt = Array.from(floorSelect.options).find(o => o.value == idToRemove);
                    if (opt) opt.selected = false;
                    renderOutsourcedFloorChips();
                    syncOutsourcedFloorDropdownState();
                    return;
                }
                if (floorDd) {
                    floorDd.style.display = floorDd.style.display === 'none' ? 'block' : 'none';
                }
            });
        }

        document.addEventListener('click', function(e) {
            if (floorBox && floorDd && !floorBox.contains(e.target) && !floorDd.contains(e.target)) {
                floorDd.style.display = 'none';
            }
        });

        if (photoTrigger && photoInput) {
            photoTrigger.addEventListener('click', function () {
                photoInput.click();
            });
        }

        if (photoInput) {
            photoInput.addEventListener('change', function () {
                if (!photoInput.files || !photoInput.files[0]) return;
                openOutsourcedPhotoCropper(photoInput.files[0]);
            });
        }

        if (clearPhotoBtn) {
            clearPhotoBtn.addEventListener('click', function () {
                resetOutsourcedPhotoState();
                outsourcedExistingPhotoUrl = '';
            });
        }

        if (cropBtn) {
            cropBtn.addEventListener('click', function () {
                if (!outsourcedCropper) return;
                const canvasEl = outsourcedCropper.getCroppedCanvas({
                    width: 500,
                    height: 500,
                    imageSmoothingEnabled: true,
                    imageSmoothingQuality: 'high'
                });
                canvasEl.toBlob(function (blob) {
                    outsourcedCroppedImageBlob = blob;
                    setOutsourcedPhotoPreview(URL.createObjectURL(blob), false);
                    if (cropperModalEl && window.bootstrap) {
                        bootstrap.Modal.getOrCreateInstance(cropperModalEl).hide();
                    }
                    if (outsourcedCropper) {
                        outsourcedCropper.destroy();
                        outsourcedCropper = null;
                    }
                }, 'image/jpeg', 0.9);
            });
        }

        if (cropperModalEl) {
            cropperModalEl.addEventListener('hidden.bs.modal', function () {
                if (outsourcedCropper) {
                    outsourcedCropper.destroy();
                    outsourcedCropper = null;
                }
                if (!outsourcedCroppedImageBlob && !outsourcedExistingPhotoUrl && photoInput) {
                    photoInput.value = '';
                    setOutsourcedPhotoPreview('', false);
                }
            });
        }

        if (detailEditBtn) {
            detailEditBtn.addEventListener('click', function () {
                if (!selectedOutsourcedEmployeeId) return;
                if (detailCanvas && window.bootstrap) {
                    bootstrap.Offcanvas.getOrCreateInstance(detailCanvas).hide();
                }
                openOutsourcedEmployee(selectedOutsourcedEmployeeId);
            });
        }

        $('#outsourcedEmployeeTable tbody').on('click', 'tr', function (e) {
            if ($(e.target).closest('a, button, input, select, textarea, label').length) {
                return;
            }
            if (!outsourcedTable) return;
            const rowData = outsourcedTable.row(this).data();
            if (rowData && rowData.id) {
                openOutsourcedEmployeeDetail(rowData.id);
            }
        });

        $('#applyFiltersBtn').on('click.outsourced', function () {
            if (currentMode !== 'outsourced') return;
            syncHeaderFiltersToState();
            if (outsourcedTable) {
                outsourcedTable.ajax.reload(null, false);
            }
        });

        $('#clearFiltersBtn').on('click.outsourced', function () {
            if (currentMode !== 'outsourced') return;
            clearHeaderFiltersAndState();
            if (outsourcedTable) {
                outsourcedTable.ajax.reload(null, false);
            }
        });

        if (outsourcedForm) {
            outsourcedForm.addEventListener('submit', async function (e) {
                e.preventDefault();
                clearOutsourcedValidation(outsourcedForm);
                const floorSelect = document.getElementById('oeAssignedFloorsSelect');
                if (floorSelect && Array.from(floorSelect.options).filter(function (o) { return o.selected; }).length === 0) {
                    const floorBox = document.getElementById('oeFloorBox');
                    if (floorBox) {
                        floorBox.classList.add('is-invalid');
                        const msg = document.createElement('div');
                        msg.className = 'field-error-msg text-danger small mt-1';
                        msg.textContent = 'Please select at least one assigned floor.';
                        floorBox.insertAdjacentElement('afterend', msg);
                    }
                    return;
                }
                const id = document.getElementById('outsourcedEmployeeId').value;
                const submitBtn = document.getElementById('outsourcedEmployeeSubmitBtn');
                const originalText = submitBtn ? submitBtn.textContent : 'Save';
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving...';
                }
                const formData = new FormData(outsourcedForm);
                if (outsourcedCroppedImageBlob) {
                    formData.set('photo', outsourcedCroppedImageBlob, outsourcedOriginalPhotoName || 'outsourced-profile.jpg');
                }
                try {
                    const url = id ? `${window.outsourcedEmployeeShowUrlBase}/${id}/update` : window.outsourcedEmployeeStoreUrl;
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: formData
                    });
                    const res = await response.json();
                    if (response.status === 422) {
                        showOutsourcedFieldErrors(outsourcedForm, res.errors);
                        return;
                    }
                    if (!response.ok || !res.success) {
                        showError(res.message || 'Unable to save record.');
                        return;
                    }
                    showSuccess(res.message || 'Saved successfully.', 'Success');
                    if (canvas && window.bootstrap) {
                        bootstrap.Offcanvas.getOrCreateInstance(canvas).hide();
                    }
                    if (!outsourcedTable) {
                        initOutsourcedTable();
                    }
                    if (outsourcedTable) {
                        outsourcedTable.ajax.reload(function () {
                            if (typeof window.updateEmployeeStats === 'function') {
                                window.updateEmployeeStats();
                            }
                        }, false);
                    } else if (typeof window.updateEmployeeStats === 'function') {
                        window.updateEmployeeStats();
                    }
                } catch (err) {
                    showError('Network error');
                } finally {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.textContent = originalText;
                    }
                }
            });
        }

        if (cnicInput) {
            cnicInput.addEventListener('input', function () {
                formatCnicInput(cnicInput);
            });
            cnicInput.addEventListener('blur', function () {
                formatCnicInput(cnicInput);
            });
            cnicInput.addEventListener('keypress', function (e) {
                const code = e.which || e.keyCode;
                if (code < 48 || code > 57) {
                    e.preventDefault();
                }
            });
            cnicInput.addEventListener('paste', function () {
                setTimeout(function () {
                    formatCnicInput(cnicInput);
                }, 0);
            });
        }

        [mobileInput, supervisorContactInput].forEach(function (input) {
            if (!input) return;
            input.addEventListener('input', function () {
                formatPhoneInput(input);
            });
            input.addEventListener('blur', function () {
                formatPhoneInput(input);
            });
            input.addEventListener('keypress', function (e) {
                const code = e.which || e.keyCode;
                if (code < 48 || code > 57) {
                    e.preventDefault();
                }
            });
            input.addEventListener('paste', function () {
                setTimeout(function () {
                    formatPhoneInput(input);
                }, 0);
            });
        });

        if (organizationSelect) {
            organizationSelect.addEventListener('change', function () {
                const orgId = organizationSelect.value || '';
                populateSbuOptions(orgId, '');
                populateDepartmentOptions(orgId, '', '');
                populateVendorOptions(orgId, '', '');
                populateOutsourcedFloors(orgId, '');
            });
        }

        if (sbuSelect) {
            sbuSelect.addEventListener('change', function () {
                const orgId = organizationSelect ? organizationSelect.value : '';
                populateDepartmentOptions(orgId, sbuSelect.value || '', '');
                populateVendorOptions(orgId, sbuSelect.value || '', '');
                populateOutsourcedFloors(orgId, sbuSelect.value || '');
            });
        }

        populateSbuOptions('', '');
        populateDepartmentOptions('', '', '');
        populateVendorOptions('', '', '');
        populateOutsourcedFloors('', '');

        const internalTab = document.getElementById('internal-staff-tab');
        const outsourcedTab = document.getElementById('outsourced-staff-tab');
        if (internalTab) {
            internalTab.addEventListener('shown.bs.tab', function () {
                setEmployeeDirectoryMode('internal');
            });
        }
        if (outsourcedTab) {
            outsourcedTab.addEventListener('shown.bs.tab', function () {
                setEmployeeDirectoryMode('outsourced');
            });
        }

        const isOutsourcedActive = outsourcedTab && outsourcedTab.classList.contains('active');
        setEmployeeDirectoryMode(isOutsourcedActive ? 'outsourced' : 'internal');
    });

    window.openOutsourcedEmployee = openOutsourcedEmployee;
    window.openOutsourcedEmployeeDetail = openOutsourcedEmployeeDetail;
    window.setEmployeeDirectoryMode = setEmployeeDirectoryMode;
    window.getEmployeeDirectoryMode = function () {
        return currentMode;
    };
})();

