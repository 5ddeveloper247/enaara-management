(function () {
    'use strict';

    let currentStep = 1;
    let maxStepReached = 1;
    const totalSteps = 6;

    // Cropper State
    window.cropper = null;
    window.croppedImageBlob = null;
    window.originalFileName = "";


    const savedId = document.getElementById('saved_employee_id');
    if (savedId && savedId.value) {
        maxStepReached = totalSteps; 
    }

    function goToStep(step) {
        const s = Math.max(1, Math.min(totalSteps, step));
        currentStep = s;
        
        // Reset Edit button when moving steps if in edit mode
        const editBtn = document.getElementById('editBtn');
        if (window.isEditMode && editBtn && editBtn.innerText.trim() === 'Save') {
            editBtn.innerHTML = '<i class="bi bi-pencil-square"></i><span>Edit</span>';
            editBtn.classList.remove('btn-success');
            editBtn.classList.add('bg-main', 'text-white');
        }

        syncStepUi();
    }

    function isExArmedForceChecked() {
        const el = document.getElementById('giExArmyRetiredCheckbox');
        return !!(el && el.checked);
    }

    function getNextStepAfter(step) {
        if (step === 3 && !isExArmedForceChecked()) {
            return 5;
        }
        return Math.min(totalSteps, step + 1);
    }

    function getPrevStepBefore(step) {
        if (step === 5 && !isExArmedForceChecked()) {
            return 3;
        }
        return Math.max(1, step - 1);
    }

    function isStepUnsaved() {
        const editBtn = document.getElementById('editBtn');
        return window.isEditMode && editBtn && (editBtn.innerText.trim() === 'Save' || editBtn.innerText.trim() === 'Saving...');
    }

    function showUnsavedWarning() {
        Swal.fire({
            icon: 'warning',
            title: '<span style="color: #856404; font-size: 15px; font-weight: 600;">Unsaved changes</span>',
            html: '<span style="color: #856404; font-size: 13px;">Click Save before continuing.</span>',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            background: '#fff3cd',
            iconColor: '#ffc107',
            didOpen: (toast) => {
                toast.style.border = '1px solid #ffeeba';
                toast.style.borderRadius = '12px';
                toast.style.padding = '10px 15px';
            }
        });
    }

    // Core selects and global data
    const orgSelect = document.getElementById('employmentOrganizationSelect');
    const sbuSelect = document.getElementById('employmentSbuSelect');
    const roleSelect = document.getElementById('employmentRoleSelect');
    const deptSelect = document.getElementById('employmentDepartmentSelect');
    const deptBox = document.getElementById('employmentDeptBox');
    const deptDd = document.getElementById('employmentDeptDd');
    const deptList = document.getElementById('employmentDeptList');
    const deptSearch = document.getElementById('employmentDeptSearch');
    const deptChips = document.getElementById('employmentDeptChips');
    const deptPh = document.getElementById('employmentDeptPh');
    const deptHint = document.getElementById('employmentDeptHint');
    const floorSelect = document.getElementById('employmentAssignedFloorsSelect');
    const floorBox = document.getElementById('employmentFloorBox');
    const floorDd = document.getElementById('employmentFloorDd');
    const floorList = document.getElementById('employmentFloorList');
    const floorSearch = document.getElementById('employmentFloorSearch');
    const floorChips = document.getElementById('employmentFloorChips');
    const floorPh = document.getElementById('employmentFloorPh');
    const floorHint = document.getElementById('employmentFloorHint');
    const gradeInput = document.getElementById('grade');
    const gradeDisplayInput = document.getElementById('gradeDisplay');
    const joinDateInput = document.getElementById('employmentJoinDateInput');
    const probationStartMirrorInput = document.getElementById('employmentProbationStartDateInput');
    const probationEndInput = document.getElementById('employmentProbationEndDateInput');

    const orgsData = window.orgsData || [];
    const rolesData = window.rolesData || [];
    let availableDepartments = [];
    let availableFloors = [];

    function formatPreviewDate(isoDate) {
        if (!isoDate || isoDate === '-' || isoDate === '') return '-';
        const parts = isoDate.split('-');
        if (parts.length !== 3) return isoDate;
        // Fixed DD/MM/YYYY format to match user's preferred display and avoid locale ambiguity
        return `${parts[2]}/${parts[1]}/${parts[0]}`;
    }

    function formatContactMaskInput(target) {
        if (!target || !target.classList || !target.classList.contains('contact-mask')) return;
        let val = target.value.replace(/\D/g, '');
        if (val.length > 15) {
            val = val.substring(0, 15);
        }
        target.value = val;
        
        const key = fieldKeyFromInput(target);
        const minLen = (key === 'residence_phone') ? 7 : 11;
        
        if (val.length > 0 && val.length < minLen) {
            target.classList.add('is-invalid');
        } else {
            target.classList.remove('is-invalid');
        }
    }

    function removeContactMaskError(input) {
        if (!input) return;
        input.classList.remove('is-invalid');
        let next = input.nextElementSibling;
        while (next && next.classList && next.classList.contains('input-guard-error')) {
            const toRemove = next;
            next = next.nextElementSibling;
            toRemove.remove();
        }
    }

    function showContactMaskError(input, message) {
        if (!input) return;
        removeContactMaskError(input);
        input.classList.add('is-invalid');
        const err = document.createElement('div');
        err.className = 'field-error-msg text-danger small mt-1 fw-bold input-guard-error';
        err.setAttribute('role', 'alert');
        err.textContent = message;
        input.insertAdjacentElement('afterend', err);
    }

    let contactMaskDelegationBound = false;
    function initContactMasks() {
        if (!contactMaskDelegationBound) {
            contactMaskDelegationBound = true;
            document.addEventListener('input', function (e) {
                if (e.target.classList && e.target.classList.contains('contact-mask')) {
                    const original = String(e.target.value ?? '');
                    formatContactMaskInput(e.target);
                    const sanitized = String(e.target.value ?? '');
                    
                    const key = fieldKeyFromInput(e.target);
                    const minLen = (key === 'residence_phone') ? 7 : 11;
                    
                    if (sanitized !== original) {
                        showContactMaskError(e.target, 'Only digits are allowed.');
                    } else if (sanitized.length === 0 || sanitized.length >= minLen) {
                        removeContactMaskError(e.target);
                    }
                }
            });
            document.addEventListener('beforeinput', function (e) {
                const target = e.target;
                if (!target || !target.classList || !target.classList.contains('contact-mask')) return;
                if (e.inputType && e.inputType.startsWith('delete')) return;
                const inserted = typeof e.data === 'string' ? e.data : '';
                if (!inserted) return;
                if (!/^\d+$/.test(inserted)) {
                    e.preventDefault();
                    showContactMaskError(target, 'Only digits are allowed.');
                }
            }, true);
            document.addEventListener('blur', function (e) {
                if (e.target.classList && e.target.classList.contains('contact-mask')) {
                    formatContactMaskInput(e.target);
                    const val = String(e.target.value ?? '');
                    
                    const key = fieldKeyFromInput(e.target);
                    const minLen = (key === 'residence_phone') ? 7 : 11;
                    
                    if (val.length > 0 && val.length < minLen) {
                        showContactMaskError(e.target, `Enter a valid phone number (${minLen} to 15 digits).`);
                    } else {
                        removeContactMaskError(e.target);
                    }
                }
            }, true);
            document.addEventListener('keypress', function (e) {
                if (!e.target.classList || !e.target.classList.contains('contact-mask')) return;
                if (e.which < 48 || e.which > 57) {
                    e.preventDefault();
                    showContactMaskError(e.target, 'Only digits are allowed.');
                }
            }, true);
        }
        document.querySelectorAll('.contact-mask').forEach(formatContactMaskInput);
    }

    window.formatContactMaskInput = formatContactMaskInput;

    initContactMasks();

    function hydrateUniversityLov() {
        const listEl = document.getElementById('academicUniversityList');
        if (!listEl) return;

        const existing = Array.from(listEl.querySelectorAll('option'))
            .map(function (opt) { return String(opt.value || '').trim(); })
            .filter(Boolean);
        const uniqueNames = new Set(existing);

        const appendOptions = function (names) {
            names.forEach(function (name) {
                const clean = String(name || '').trim();
                if (!clean || uniqueNames.has(clean)) return;
                uniqueNames.add(clean);
                listEl.insertAdjacentHTML('beforeend', '<option value="' + clean.replace(/"/g, '&quot;') + '"></option>');
            });
        };

        const replaceOptions = function (names) {
            listEl.innerHTML = '';
            uniqueNames.clear();
            names.forEach(function (name) {
                const clean = String(name || '').trim();
                if (!clean || uniqueNames.has(clean)) return;
                uniqueNames.add(clean);
                listEl.insertAdjacentHTML('beforeend', '<option value="' + clean.replace(/"/g, '&quot;') + '"></option>');
            });
        };

        appendOptions([
            'Allama Iqbal Open University',
            'Aga Khan University',
            'Air University',
            'Bahria University',
            'Bahauddin Zakariya University, Multan',
            'COMSATS University Islamabad',
            'Capital University of Science and Technology',
            'FAST - National University of Computer and Emerging Sciences (NUCES)',
            'Government College University Lahore',
            'Government College University Faisalabad',
            'Ghulam Ishaq Khan Institute of Science and Technology',
            'Institute of Business Administration (IBA)',
            'International Islamic University Islamabad',
            'Islamia University of Bahawalpur',
            'Karachi Institute of Economics and Technology',
            'Lahore University of Management Sciences (LUMS)',
            'Lahore College for Women University',
            'Mehran University of Engineering and Technology',
            'National University of Modern Languages',
            'National University of Sciences and Technology (NUST)',
            'NED University of Engineering and Technology',
            'Pakistan Institute of Engineering and Applied Sciences (PIEAS)',
            'Quaid-i-Azam University',
            'Riphah International University',
            'Shaheed Zulfikar Ali Bhutto Institute of Science and Technology',
            'University of Agriculture Faisalabad',
            'University of Central Punjab',
            'University of Engineering and Technology Lahore',
            'University of Engineering and Technology Taxila',
            'University of Engineering and Technology Peshawar',
            'University of Gujrat',
            'University of Karachi',
            'University of Lahore',
            'University of Malakand',
            'University of Management and Technology',
            'University of Peshawar',
            'University of Sargodha',
            'University of Sindh',
            'University of the Punjab',
            'Virtual University of Pakistan'
        ]);

        if (!window.universitiesDirectoryUrl) {
            return;
        }

        fetch(window.universitiesDirectoryUrl, {
            headers: {
                Accept: 'application/json'
            }
        })
            .then(function (response) {
                return response.ok ? response.json() : { success: false, data: [] };
            })
            .then(function (payload) {
                if (!payload || !payload.success || !Array.isArray(payload.data)) {
                    return;
                }
                replaceOptions(payload.data);
            })
            .catch(function () {
            });
    }

    hydrateUniversityLov();

    const fieldKeyById = {
        employmentDetailsInternDurationInput: 'intern_duration',
        designation: 'designation',
        grade: 'grade',
        branch: 'branch',
        location: 'location',
        biometric_id: 'biometric_id',
        policeVerificationMsrNumberInput: 'msr_letter_no',
        policeVerificationLetterNumberInput: 'verification_letter_no',
        policeVerificationAddresseeInput: 'addressee',
        policeVerificationVerifyingAuthorityInput: 'verifying_authority',
        policeVerificationRemarksInput: 'police_remarks',
        bankDetailsAccountTitleInput: 'account_title',
        bankDetailsAccountNumberInput: 'account_no',
        bankDetailsIbanInput: 'iban',
        bankDetailsBranchNameInput: 'bank_name',
        bankDetailsBranchCodeInput: 'branch_code',
        bankDetailsBranchAddressInput: 'branch_address',
        employmentTerminationReasonInput: 'termination_reason',
    };

    function fieldKeyFromName(name) {
        if (!name) return '';
        const raw = String(name).trim().toLowerCase();
        if (!raw) return '';
        // Keep full snake_case field names (e.g. verifying_authority),
        // but normalize array-style names like family[0][nok_cnic] -> nok_cnic.
        const bracketMatches = raw.match(/[a-z_]+/g);
        if (bracketMatches && bracketMatches.length > 1) {
            return bracketMatches[bracketMatches.length - 1];
        }
        return raw.replace(/\[\]$/, '');
    }

    function fieldKeyFromInput(input) {
        if (!input) return '';
        const keyFromName = fieldKeyFromName(input.name);
        if (keyFromName) return keyFromName;
        if (input.id && fieldKeyById[input.id]) {
            return fieldKeyById[input.id];
        }
        return '';
    }

    function applyInputGuards() {
        function removeInputGuardError(input) {
            if (!input) return;
            input.classList.remove('is-invalid');
            let next = input.nextElementSibling;
            while (next && next.classList && next.classList.contains('input-guard-error')) {
                const toRemove = next;
                next = next.nextElementSibling;
                toRemove.remove();
            }
        }

        function showInputGuardError(input, message) {
            if (!input) return;
            removeInputGuardError(input);
            input.classList.add('is-invalid');
            const err = document.createElement('div');
            err.className = 'field-error-msg text-danger small mt-1 fw-bold input-guard-error';
            err.setAttribute('role', 'alert');
            err.textContent = message;
            input.insertAdjacentElement('afterend', err);
        }

        const maxLengthByField = {
            full_name: 50,
            father_name: 50,
            email: 50,
            phone: 15,
            cnic: 15,
            father_cnic: 15,
            ntn: 13,
            nationality: 100,
            domicile_district: 100,
            domicile_province: 100,
            city_of_birth: 50,
            religion: 50,
            sect: 50,
            spouse_name: 50,
            spouse_cnic: 15,
            spouse_nationality: 100,
            employee_type: 100,
            intern_duration: 10,
            designation: 50,
            grade: 10,
            branch: 30,
            location: 100,
            site: 100,
            biometric_id: 20,
            service_no: 50,
            rank: 50,
            medical_category: 50,
            reason_of_retirement: 255,
            corps_regiment: 100,
            ex_army_unit: 100,
            trade: 50,
            pma_lc_ots: 100,
            msr_letter_no: 20,
            addressee: 100,
            verifying_authority: 50,
            verification_letter_no: 50,
            police_remarks: 2000,
            account_title: 50,
            account_no: 16,
            bank_name: 100,
            branch_code: 10,
            branch_address: 150,
            iban: 34,
            residence_phone: 15,
            emergency_contact: 15,
            cell_no: 15,
            contact_email: 255,
            present_address: 1000,
            permanent_address: 1000,
            nok_name: 100,
            nok_relation: 100,
            nok_contact: 15,
            nok_cnic: 15,
            relation: 100,
            occupation: 100,
            name: 50,
            degree: 50,
            grade_cgpa: 20,
            field_of_study: 50,
            institute: 150,
            organization: 150,
            reason_for_leaving: 200,
            ref1_name: 100,
            ref1_designation: 255,
            ref1_organization: 255,
            ref2_name: 100,
            ref2_designation: 255,
            ref2_organization: 255,
            disability_type: 100,
            disability_description: 1000,
            chronic_disease_description: 1000,
            last_fitness_test: 500,
            termination_reason: 2000,
        };

        const digitsOnlyFields = new Set([
            'phone',
            'residence_phone',
            'emergency_contact',
            'cell_no',
            'nok_contact',
            'account_no',
            'ntn',
            'msr_letter_no',
        ]);

        const cnicFields = new Set(['cnic', 'father_cnic', 'spouse_cnic', 'nok_cnic']);

        const alphaNumericUpperFields = new Set(['iban']);

        const personNameFields = new Set(['full_name', 'father_name', 'spouse_name', 'nok_name', 'name']);
        const personNameAllowedPattern = /^[A-Za-z\s.'-]*$/;
        const cityBirthFields = new Set(['city_of_birth']);
        const cityBirthAllowedPattern = /^[A-Za-z0-9\s.'\-&,\/#()]*$/;
        const locationFields = new Set(['location']);
        const locationAllowedPattern = /^[A-Za-z0-9\s.'\-&,\/#()]*$/;
        const sectFields = new Set(['sect']);
        const sectAllowedPattern = /^[\p{L}\p{M}\s'.,\-&\/()]*$/u;
        const alphaTextFields = new Set(['employee_type', 'designation', 'trade', 'corps_regiment', 'ex_army_unit', 'verifying_authority']);
        const alphaTextAllowedPattern = /^[A-Za-z\s.\-&,\/()']*$/;
        const alphaNumericTextFields = new Set(['grade', 'intern_duration', 'branch', 'medical_category', 'pma_lc_ots', 'addressee']);
        const alphaNumericTextAllowedPattern = /^[A-Za-z0-9\s.\-&,\/()#']*$/;
        const alphanumericCodeFields = new Set(['biometric_id', 'service_no', 'verification_letter_no']);
        const alphanumericCodeAllowedPattern = /^[A-Za-z0-9\/\-_]*$/;
        const rankFields = new Set(['rank']);
        const rankAllowedPattern = /^[A-Za-z0-9\s.\-\/]*$/;
        const boundedIntegerMaxByField = {
            grace_period: 600,
            opening_grace_period: 600,
            closing_grace_period: 600,
        };

        function nextLengthWithInsert(input, insertedText) {
            const current = String(input.value ?? '');
            const start = typeof input.selectionStart === 'number' ? input.selectionStart : current.length;
            const end = typeof input.selectionEnd === 'number' ? input.selectionEnd : current.length;
            const replaced = end - start;
            return current.length - replaced + String(insertedText || '').length;
        }

        function resolveMaxLength(input, fieldKey) {
            if (fieldKey && maxLengthByField[fieldKey]) {
                return Number(maxLengthByField[fieldKey]);
            }
            const attr = input ? input.getAttribute('maxlength') : null;
            if (!attr) return null;
            const parsed = Number(attr);
            return Number.isFinite(parsed) && parsed > 0 ? parsed : null;
        }

        function hasTextSelection(input) {
            return typeof input.selectionStart === 'number'
                && typeof input.selectionEnd === 'number'
                && input.selectionEnd > input.selectionStart;
        }

        function shouldTreatAsTextInsertion(e) {
            if (!e || typeof e.key !== 'string') return false;
            if (e.ctrlKey || e.metaKey || e.altKey) return false;
            return e.key.length === 1;
        }

        function syncNativeMaxlengthAttributes() {
            document.querySelectorAll('#employeeForm input[name], #employeeForm textarea[name]').forEach((el) => {
                const key = fieldKeyFromInput(el);
                const maxLen = maxLengthByField[key];
                if (maxLen) {
                    el.setAttribute('maxlength', String(maxLen));
                }
            });
        }

        function clampInitialValuesToMaxlength() {
            document.querySelectorAll('#employeeForm input[name], #employeeForm textarea[name]').forEach((el) => {
                const key = fieldKeyFromInput(el);
                const maxLen = resolveMaxLength(el, key);
                if (!maxLen) return;
                const str = String(el.value ?? '');
                if (str.length > maxLen) {
                    el.value = str.slice(0, maxLen);
                    el.dataset.maxLimitBlocked = '1';
                    showInputGuardError(el, `Maximum ${maxLen} characters allowed.`);
                }
            });
        }

        function runMaxlengthSync() {
            syncNativeMaxlengthAttributes();
            clampInitialValuesToMaxlength();
        }

        runMaxlengthSync();
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', runMaxlengthSync);
        }

        document.addEventListener('beforeinput', function (e) {
            const target = e.target;
            if (!target) return;
            if (!target.closest('#employeeForm')) return;
            if (e.inputType && e.inputType.startsWith('delete')) return;

            const key = fieldKeyFromInput(target);
            const maxLen = resolveMaxLength(target, key);
            const inserted = typeof e.data === 'string' ? e.data : '';

            if (maxLen && inserted && nextLengthWithInsert(target, inserted) > maxLen) {
                e.preventDefault();
                target.dataset.maxLimitBlocked = '1';
                showInputGuardError(target, `Maximum ${maxLen} characters allowed.`);
                return;
            }

            if (Object.prototype.hasOwnProperty.call(boundedIntegerMaxByField, key) && inserted) {
                if (!/^\d+$/.test(inserted)) {
                    e.preventDefault();
                    showInputGuardError(target, 'Only numeric digits are allowed in this field.');
                    return;
                }

                const current = String(target.value ?? '');
                const start = typeof target.selectionStart === 'number' ? target.selectionStart : current.length;
                const end = typeof target.selectionEnd === 'number' ? target.selectionEnd : current.length;
                const next = current.slice(0, start) + inserted + current.slice(end);
                const nextNum = Number(next);
                const maxAllowed = boundedIntegerMaxByField[key];
                if (Number.isFinite(nextNum) && nextNum > maxAllowed) {
                    e.preventDefault();
                    showInputGuardError(target, `Maximum value is ${maxAllowed}.`);
                    return;
                }
            }

            if (digitsOnlyFields.has(key) && inserted && !/^\d+$/.test(inserted)) {
                e.preventDefault();
                showInputGuardError(target, 'Only numeric digits are allowed in this field.');
                return;
            }

            if (cnicFields.has(key) && inserted && !/^[0-9-]+$/.test(inserted)) {
                e.preventDefault();
                showInputGuardError(target, 'Only digits and hyphen (-) are allowed for CNIC.');
                return;
            }

            if (cityBirthFields.has(key) && inserted && !cityBirthAllowedPattern.test(inserted)) {
                e.preventDefault();
                showInputGuardError(target, 'Only letters, numbers, spaces and basic punctuation are allowed.');
                return;
            }

            if (locationFields.has(key) && inserted && !locationAllowedPattern.test(inserted)) {
                e.preventDefault();
                showInputGuardError(target, 'Only letters, numbers, spaces and basic punctuation are allowed.');
                return;
            }

            if (sectFields.has(key) && inserted && !sectAllowedPattern.test(inserted)) {
                e.preventDefault();
                showInputGuardError(target, 'Sect must be letters and standard punctuation only (no digits).');
                return;
            }

            if (alphaTextFields.has(key) && inserted && !alphaTextAllowedPattern.test(inserted)) {
                e.preventDefault();
                showInputGuardError(target, /\d/.test(inserted)
                    ? 'Digits are not allowed in this field.'
                    : 'Only letters, spaces and standard punctuation are allowed in this field.');
                return;
            }

            if (alphaNumericTextFields.has(key) && inserted && !alphaNumericTextAllowedPattern.test(inserted)) {
                e.preventDefault();
                showInputGuardError(target, 'Only letters, numbers, spaces and standard punctuation are allowed in this field.');
                return;
            }

            if (alphanumericCodeFields.has(key) && inserted && !alphanumericCodeAllowedPattern.test(inserted)) {
                e.preventDefault();
                showInputGuardError(target, 'Only letters, numbers, slash (/), hyphen (-), and underscore (_) are allowed.');
                return;
            }

            if (rankFields.has(key) && inserted && !rankAllowedPattern.test(inserted)) {
                e.preventDefault();
                showInputGuardError(target, 'Rank may contain letters, numbers, spaces, dots, hyphens, and slashes only.');
                return;
            }

            if (!personNameFields.has(key)) return;
            if (!inserted) return;

            if (!personNameAllowedPattern.test(inserted)) {
                e.preventDefault();
                showInputGuardError(target, /\d/.test(inserted)
                    ? 'Digits are not allowed in this field.'
                    : 'Only alphabetic characters are allowed in this field.');
            }
        }, true);

        // Fallback guard: some browsers/inputs may not surface max-length errors reliably via beforeinput.
        document.addEventListener('keydown', function (e) {
            const target = e.target;
            if (!target) return;
            if (!target.closest('#employeeForm')) return;
            if (!shouldTreatAsTextInsertion(e)) return;

            const key = fieldKeyFromInput(target);
            const maxLen = resolveMaxLength(target, key);
            const inserted = e.key;

            if (digitsOnlyFields.has(key) && inserted && !/^\d$/.test(inserted)) {
                e.preventDefault();
                showInputGuardError(target, 'Only numeric digits are allowed in this field.');
                return;
            }

            if (cnicFields.has(key) && inserted && !/^[0-9-]$/.test(inserted)) {
                e.preventDefault();
                showInputGuardError(target, 'Only digits and hyphen (-) are allowed for CNIC.');
                return;
            }

            if (cityBirthFields.has(key) && inserted && !cityBirthAllowedPattern.test(inserted)) {
                e.preventDefault();
                showInputGuardError(target, 'Only letters, numbers, spaces and basic punctuation are allowed.');
                return;
            }

            if (locationFields.has(key) && inserted && !locationAllowedPattern.test(inserted)) {
                e.preventDefault();
                showInputGuardError(target, 'Only letters, numbers, spaces and basic punctuation are allowed.');
                return;
            }

            if (sectFields.has(key) && inserted && !sectAllowedPattern.test(inserted)) {
                e.preventDefault();
                showInputGuardError(target, 'Sect must be letters and standard punctuation only (no digits).');
                return;
            }

            if (alphaTextFields.has(key) && inserted && !alphaTextAllowedPattern.test(inserted)) {
                e.preventDefault();
                showInputGuardError(target, /\d/.test(inserted)
                    ? 'Digits are not allowed in this field.'
                    : 'Only letters, spaces and standard punctuation are allowed in this field.');
                return;
            }

            if (alphaNumericTextFields.has(key) && inserted && !alphaNumericTextAllowedPattern.test(inserted)) {
                e.preventDefault();
                showInputGuardError(target, 'Only letters, numbers, spaces and standard punctuation are allowed in this field.');
                return;
            }

            if (alphanumericCodeFields.has(key) && inserted && !alphanumericCodeAllowedPattern.test(inserted)) {
                e.preventDefault();
                showInputGuardError(target, 'Only letters, numbers, slash (/), hyphen (-), and underscore (_) are allowed.');
                return;
            }

            if (rankFields.has(key) && inserted && !rankAllowedPattern.test(inserted)) {
                e.preventDefault();
                showInputGuardError(target, 'Rank may contain letters, numbers, spaces, dots, hyphens, and slashes only.');
                return;
            }

            if (personNameFields.has(key) && inserted && !personNameAllowedPattern.test(inserted)) {
                e.preventDefault();
                showInputGuardError(target, /\d/.test(inserted)
                    ? 'Digits are not allowed in this field.'
                    : 'Only alphabetic characters are allowed in this field.');
                return;
            }

            if (!maxLen) return;

            const currentLength = String(target.value ?? '').length;
            if (currentLength >= maxLen && !hasTextSelection(target)) {
                e.preventDefault();
                target.dataset.maxLimitBlocked = '1';
                showInputGuardError(target, `Maximum ${maxLen} characters allowed.`);
            }
        }, true);

        document.addEventListener('paste', function (e) {
            const target = e.target;
            if (!target) return;
            if (!target.closest('#employeeForm')) return;

            const key = fieldKeyFromInput(target);
            const maxLen = resolveMaxLength(target, key);
            let pastedText = e.clipboardData ? (e.clipboardData.getData('text') || '') : '';
            if (!pastedText) return;

            if (digitsOnlyFields.has(key)) pastedText = pastedText.replace(/\D/g, '');
            if (cnicFields.has(key)) pastedText = pastedText.replace(/[^0-9-]/g, '');
            if (cityBirthFields.has(key)) pastedText = pastedText.replace(/[^A-Za-z0-9\s.'\-&,\/#()]/g, '');
            if (locationFields.has(key)) pastedText = pastedText.replace(/[^A-Za-z0-9\s.'\-&,\/#()]/g, '');
            if (sectFields.has(key)) pastedText = pastedText.replace(/[^\p{L}\p{M}\s'.,\-&\/()]/gu, '');
            if (alphaTextFields.has(key)) pastedText = pastedText.replace(/[^A-Za-z\s.\-&,\/()']/g, '');
            if (alphaNumericTextFields.has(key)) pastedText = pastedText.replace(/[^A-Za-z0-9\s.\-&,\/()#']/g, '');
            if (alphanumericCodeFields.has(key)) pastedText = pastedText.replace(/[^A-Za-z0-9\/\-_]/g, '');
            if (rankFields.has(key)) pastedText = pastedText.replace(/[^A-Za-z0-9\s.\-\/]/g, '');
            if (personNameFields.has(key)) pastedText = pastedText.replace(/[^A-Za-z\s.'-]/g, '');

            if (!maxLen) return;
            if (nextLengthWithInsert(target, pastedText) <= maxLen) return;

            e.preventDefault();
            target.dataset.maxLimitBlocked = '1';
            const current = String(target.value ?? '');
            const start = typeof target.selectionStart === 'number' ? target.selectionStart : current.length;
            const end = typeof target.selectionEnd === 'number' ? target.selectionEnd : current.length;
            const allowed = Math.max(0, maxLen - (current.length - (end - start)));
            const safeInsert = pastedText.slice(0, allowed);
            target.value = current.slice(0, start) + safeInsert + current.slice(end);
            showInputGuardError(target, `Maximum ${maxLen} characters allowed.`);
        }, true);

        document.addEventListener('input', function (e) {
            const target = e.target;
            if (!target) return;
            if (!target.closest('#employeeForm')) return;

            const key = fieldKeyFromInput(target);
            const maxLen = resolveMaxLength(target, key);
            const originalValue = String(target.value ?? '');
            let value = originalValue;
            let errorMessage = '';

            if (digitsOnlyFields.has(key)) {
                value = value.replace(/\D/g, '');
            }

            if (cnicFields.has(key)) {
                value = value.replace(/[^0-9-]/g, '');
            }

            if (alphaNumericUpperFields.has(key)) {
                value = value.toUpperCase().replace(/[^A-Z0-9]/g, '');
            }

            if (key === 'email' || key === 'contact_email') {
                value = value.replace(/\s/g, '').toLowerCase();
            }

            if (personNameFields.has(key)) {
                const hadDigit = /\d/.test(value);
                const cleaned = value.replace(/[^A-Za-z\s.'-]/g, '');
                if (cleaned !== value && !errorMessage) {
                    errorMessage = hadDigit
                        ? 'Digits are not allowed in this field.'
                        : 'Only alphabetic characters are allowed in this field.';
                }
                value = cleaned;
            }

            if (cityBirthFields.has(key)) {
                const cleaned = value.replace(/[^A-Za-z0-9\s.'\-&,\/#()]/g, '');
                if (cleaned !== value && !errorMessage) {
                    errorMessage = 'Only letters, numbers, spaces and basic punctuation are allowed.';
                }
                value = cleaned;
                if (value && !/[A-Za-z]/.test(value) && !errorMessage) {
                    errorMessage = 'Town / City of Birth must contain at least one letter.';
                }
            }

            if (locationFields.has(key)) {
                const cleaned = value.replace(/[^A-Za-z0-9\s.'\-&,\/#()]/g, '');
                if (cleaned !== value && !errorMessage) {
                    errorMessage = 'Only letters, numbers, spaces and basic punctuation are allowed.';
                }
                value = cleaned;
                if (value && !/[A-Za-z]/.test(value) && !errorMessage) {
                    errorMessage = 'Location must contain at least one letter.';
                }
            }

            if (sectFields.has(key)) {
                const cleaned = value.replace(/[^\p{L}\p{M}\s'.,\-&\/()]/gu, '');
                if (cleaned !== value && !errorMessage) {
                    errorMessage = 'Sect must be letters and standard punctuation only (no digits).';
                }
                value = cleaned;
                if (value && !/[\p{L}\p{M}]/u.test(value) && !errorMessage) {
                    errorMessage = 'Sect must contain at least one letter.';
                }
            }

            if (alphaTextFields.has(key)) {
                const hadDigit = /\d/.test(value);
                const cleaned = value.replace(/[^A-Za-z\s.\-&,\/()']/g, '');
                if (cleaned !== value && !errorMessage) {
                    errorMessage = hadDigit
                        ? 'Digits are not allowed in this field.'
                        : 'Only letters, spaces and standard punctuation are allowed in this field.';
                }
                value = cleaned;
            }

            if (alphaNumericTextFields.has(key)) {
                const cleaned = value.replace(/[^A-Za-z0-9\s.\-&,\/()#']/g, '');
                if (cleaned !== value && !errorMessage) {
                    errorMessage = 'Only letters, numbers, spaces and standard punctuation are allowed in this field.';
                }
                value = cleaned;
            }

            if (alphanumericCodeFields.has(key)) {
                const cleaned = value.replace(/[^A-Za-z0-9\/\-_]/g, '');
                if (cleaned !== value && !errorMessage) {
                    errorMessage = 'Only letters, numbers, slash (/), hyphen (-), and underscore (_) are allowed.';
                }
                value = cleaned;
            }

            if (rankFields.has(key)) {
                const cleaned = value.replace(/[^A-Za-z0-9\s.\-\/]/g, '');
                if (cleaned !== value && !errorMessage) {
                    errorMessage = 'Rank may contain letters, numbers, spaces, dots, hyphens, and slashes only.';
                }
                value = cleaned;
            }

            if (Object.prototype.hasOwnProperty.call(boundedIntegerMaxByField, key)) {
                const cleaned = value.replace(/\D/g, '');
                if (cleaned !== value && !errorMessage) {
                    errorMessage = 'Only numeric digits are allowed in this field.';
                }
                value = cleaned;
                const maxAllowed = boundedIntegerMaxByField[key];
                if (value) {
                    const numericValue = Number(value);
                    if (Number.isFinite(numericValue) && numericValue > maxAllowed) {
                        value = String(maxAllowed);
                        if (!errorMessage) {
                            errorMessage = `Maximum value is ${maxAllowed}.`;
                        }
                    }
                }
            }

            // Generic number min/max validation for all numeric inputs in the form.
            if (!errorMessage && target.type === 'number' && value !== '') {
                const numericValue = Number(value);
                const minAttr = target.getAttribute('min');
                const maxAttr = target.getAttribute('max');
                const min = minAttr !== null && minAttr !== '' ? Number(minAttr) : null;
                const max = maxAttr !== null && maxAttr !== '' ? Number(maxAttr) : null;

                if (Number.isFinite(min) && Number.isFinite(numericValue) && numericValue < min) {
                    errorMessage = `Minimum value is ${min}.`;
                } else if (Number.isFinite(max) && Number.isFinite(numericValue) && numericValue > max) {
                    errorMessage = `Maximum value is ${max}.`;
                }
            }

            if (maxLen && value.length > maxLen) {
                value = value.slice(0, maxLen);
                if (!errorMessage) {
                    errorMessage = `Maximum ${maxLen} characters allowed.`;
                }
            }

            // Browser-native maxlength can silently block extra input without firing our preventDefault path.
            // Show a consistent inline message as soon as the field reaches its allowed limit.
            if (!errorMessage && maxLen && value.length === maxLen && target.dataset.maxLimitBlocked === '1') {
                errorMessage = `Maximum ${maxLen} characters allowed.`;
            }

            if (value !== target.value) {
                target.value = value;
            }

            if (errorMessage) {
                showInputGuardError(target, errorMessage);
            } else {
                const valStr = String(target.value ?? '');
                const keepMaxLimitHint = maxLen && valStr.length >= maxLen && target.dataset.maxLimitBlocked === '1';
                if (!keepMaxLimitHint) {
                    delete target.dataset.maxLimitBlocked;
                    removeInputGuardError(target);
                }
            }
        }, true);

        // If browser blocks extra characters via native maxlength without firing input mutation,
        // keep the same inline error style as General tab.
        document.addEventListener('keyup', function (e) {
            const target = e.target;
            if (!target) return;
            if (!target.closest('#employeeForm')) return;
            const key = fieldKeyFromInput(target);
            const maxLen = resolveMaxLength(target, key);
            if (!maxLen) return;
            const isTypingKey = shouldTreatAsTextInsertion(e);
            const isEmploymentStepField = !!target.closest('#stepPane2');
            const valueLength = String(target.value ?? '').length;

            if (
                valueLength >= maxLen &&
                (
                    target.dataset.maxLimitBlocked === '1' ||
                    (isEmploymentStepField && isTypingKey)
                )
            ) {
                target.dataset.maxLimitBlocked = '1';
                showInputGuardError(target, `Maximum ${maxLen} characters allowed.`);
            }
        }, true);

        // Clear stale maxlength hint when user leaves a now-valid field.
        document.addEventListener('focusout', function (e) {
            const target = e.target;
            if (!target || !target.name) return;
            if (!target.closest('#employeeForm')) return;

            const key = fieldKeyFromInput(target);
            const maxLen = resolveMaxLength(target, key);
            if (!maxLen) return;

            const valueLength = String(target.value ?? '').length;
            if (target.dataset.maxLimitBlocked === '1' && valueLength <= maxLen) {
                delete target.dataset.maxLimitBlocked;
                removeInputGuardError(target);
            }
        }, true);

        // Surface browser constraint failures (required/type/min/max/pattern) as inline errors instantly.
        document.addEventListener('invalid', function (e) {
            const target = e.target;
            if (!target || !target.name) return;
            if (!target.closest('#employeeForm')) return;
            e.preventDefault();
            showInputGuardError(target, target.validationMessage || 'Invalid value.');
        }, true);

        document.addEventListener('change', function (e) {
            const target = e.target;
            if (!target || !target.name) return;
            if (!target.closest('#employeeForm')) return;
            if (typeof target.checkValidity === 'function' && !target.checkValidity()) {
                showInputGuardError(target, target.validationMessage || 'Invalid value.');
            }
        }, true);

        function bindEmploymentExplicitMaxGuard(selector, maxLen) {
            const input = document.querySelector(selector);
            if (!input || input.dataset.employmentMaxGuardBound === '1') return;
            input.dataset.employmentMaxGuardBound = '1';

            input.addEventListener('keydown', function (e) {
                if (!shouldTreatAsTextInsertion(e)) return;
                const value = String(input.value ?? '');
                const hasSelection = hasTextSelection(input);
                if (value.length >= maxLen && !hasSelection) {
                    e.preventDefault();
                    input.dataset.maxLimitBlocked = '1';
                    showInputGuardError(input, `Maximum ${maxLen} characters allowed.`);
                }
            });

            input.addEventListener('input', function () {
                const value = String(input.value ?? '');
                if (value.length > maxLen) {
                    input.value = value.slice(0, maxLen);
                    input.dataset.maxLimitBlocked = '1';
                    showInputGuardError(input, `Maximum ${maxLen} characters allowed.`);
                    return;
                }
                if (value.length === maxLen && input.dataset.maxLimitBlocked === '1') {
                    showInputGuardError(input, `Maximum ${maxLen} characters allowed.`);
                    return;
                }
                if (value.length < maxLen) {
                    delete input.dataset.maxLimitBlocked;
                    removeInputGuardError(input);
                }
            });

            input.addEventListener('blur', function () {
                const value = String(input.value ?? '');
                if (value.length > maxLen) {
                    input.value = value.slice(0, maxLen);
                    showInputGuardError(input, `Maximum ${maxLen} characters allowed.`);
                    return;
                }
                delete input.dataset.maxLimitBlocked;
                removeInputGuardError(input);
            });
        }

        function bindEmploymentAlphaTextGuard(selector, fieldLabel) {
            const input = document.querySelector(selector);
            if (!input || input.dataset.employmentAlphaTextGuardBound === '1') return;
            input.dataset.employmentAlphaTextGuardBound = '1';

            input.addEventListener('beforeinput', function (e) {
                if (e.inputType && e.inputType.startsWith('delete')) return;
                const inserted = typeof e.data === 'string' ? e.data : '';
                if (!inserted) return;
                if (!alphaTextAllowedPattern.test(inserted)) {
                    e.preventDefault();
                    showInputGuardError(
                        input,
                        /\d/.test(inserted)
                            ? `${fieldLabel} cannot contain digits.`
                            : `${fieldLabel} may only contain letters, spaces, and punctuation (like dot or hyphen).`
                    );
                }
            }, true);

            input.addEventListener('paste', function (e) {
                const pastedText = e.clipboardData ? (e.clipboardData.getData('text') || '') : '';
                if (!pastedText) return;
                const cleaned = pastedText.replace(/[^A-Za-z\s.\-&,\/()']/g, '');
                if (cleaned === pastedText) return;
                e.preventDefault();

                const current = String(input.value ?? '');
                const start = typeof input.selectionStart === 'number' ? input.selectionStart : current.length;
                const end = typeof input.selectionEnd === 'number' ? input.selectionEnd : current.length;
                const nextValue = current.slice(0, start) + cleaned + current.slice(end);
                input.value = nextValue;
                showInputGuardError(input, `${fieldLabel} may only contain letters, spaces, and punctuation (like dot or hyphen).`);
            });

            input.addEventListener('input', function () {
                const current = String(input.value ?? '');
                const cleaned = current.replace(/[^A-Za-z\s.\-&,\/()']/g, '');
                if (cleaned !== current) {
                    input.value = cleaned;
                    showInputGuardError(input, `${fieldLabel} may only contain letters, spaces, and punctuation (like dot or hyphen).`);
                    return;
                }
                if (input.classList.contains('is-invalid') && cleaned.length > 0) {
                    removeInputGuardError(input);
                }
            });

            input.addEventListener('blur', function () {
                const value = String(input.value ?? '');
                if (value.length > maxLen) {
                    input.value = value.slice(0, maxLen);
                    showInputGuardError(input, `Maximum ${maxLen} characters allowed.`);
                    return;
                }
                delete input.dataset.maxLimitBlocked;
                removeInputGuardError(input);
            });
        }

        function bindEmploymentBoundedIntegerGuard(selector, maxAllowed, maxDigits) {
            const input = document.querySelector(selector);
            if (!input || input.dataset.employmentBoundedIntegerGuardBound === '1') return;
            input.dataset.employmentBoundedIntegerGuardBound = '1';

            input.addEventListener('beforeinput', function (e) {
                if (e.inputType && e.inputType.startsWith('delete')) return;
                const inserted = typeof e.data === 'string' ? e.data : '';
                if (!inserted) return;
                if (!/^\d+$/.test(inserted)) {
                    e.preventDefault();
                    showInputGuardError(input, 'Only numeric digits are allowed in this field.');
                    return;
                }

                const current = String(input.value ?? '');
                const start = typeof input.selectionStart === 'number' ? input.selectionStart : current.length;
                const end = typeof input.selectionEnd === 'number' ? input.selectionEnd : current.length;
                const next = current.slice(0, start) + inserted + current.slice(end);
                if (next.length > maxDigits) {
                    e.preventDefault();
                    showInputGuardError(input, `Maximum ${maxDigits} digits allowed.`);
                    return;
                }
                const nextNum = Number(next);
                if (Number.isFinite(nextNum) && nextNum > maxAllowed) {
                    e.preventDefault();
                    showInputGuardError(input, `Maximum allowed value is ${maxAllowed}.`);
                }
            }, true);

            input.addEventListener('input', function () {
                let value = String(input.value ?? '');
                const cleaned = value.replace(/\D/g, '');
                if (cleaned !== value) {
                    value = cleaned;
                }

                if (value.length > maxDigits) {
                    value = value.slice(0, maxDigits);
                    input.value = value;
                    showInputGuardError(input, `Maximum ${maxDigits} digits allowed.`);
                    return;
                }

                if (value !== '') {
                    const numericValue = Number(value);
                    if (Number.isFinite(numericValue) && numericValue > maxAllowed) {
                        input.value = String(maxAllowed);
                        showInputGuardError(input, `Maximum allowed value is ${maxAllowed}.`);
                        return;
                    }
                }

                input.value = value;
                removeInputGuardError(input);
            });
        }

        function bindPoliceExplicitMaxGuard(selector, maxLen) {
            const input = document.querySelector(selector);
            if (!input || input.dataset.policeMaxGuardBound === '1') return;
            input.dataset.policeMaxGuardBound = '1';

            input.addEventListener('keydown', function (e) {
                if (!shouldTreatAsTextInsertion(e)) return;
                const value = String(input.value ?? '');
                const hasSelection = hasTextSelection(input);
                if (value.length >= maxLen && !hasSelection) {
                    e.preventDefault();
                    input.dataset.maxLimitBlocked = '1';
                    showInputGuardError(input, `Maximum ${maxLen} characters allowed.`);
                }
            }, true);

            input.addEventListener('input', function () {
                const value = String(input.value ?? '');
                if (value.length > maxLen) {
                    input.value = value.slice(0, maxLen);
                    input.dataset.maxLimitBlocked = '1';
                    showInputGuardError(input, `Maximum ${maxLen} characters allowed.`);
                    return;
                }
                if (value.length === maxLen && input.dataset.maxLimitBlocked === '1') {
                    showInputGuardError(input, `Maximum ${maxLen} characters allowed.`);
                    return;
                }
                if (value.length < maxLen) {
                    delete input.dataset.maxLimitBlocked;
                    removeInputGuardError(input);
                }
            });
        }

        function bindPolicePatternGuard(selector, pattern, message, dynamicMessageBuilder) {
            const input = document.querySelector(selector);
            if (!input || input.dataset.policePatternGuardBound === '1') return;
            input.dataset.policePatternGuardBound = '1';

            const resolveMessage = function (text) {
                if (typeof dynamicMessageBuilder === 'function') {
                    const dynamic = dynamicMessageBuilder(String(text || ''));
                    if (dynamic && typeof dynamic === 'string') {
                        return dynamic;
                    }
                }
                return message;
            };

            input.addEventListener('beforeinput', function (e) {
                if (e.inputType && e.inputType.startsWith('delete')) return;
                const inserted = typeof e.data === 'string' ? e.data : '';
                if (!inserted) return;
                if (!pattern.test(inserted)) {
                    e.preventDefault();
                    showInputGuardError(input, resolveMessage(inserted));
                }
            }, true);

            input.addEventListener('paste', function (e) {
                const pastedText = e.clipboardData ? (e.clipboardData.getData('text') || '') : '';
                if (!pastedText) return;
                const cleaned = Array.from(pastedText).filter((ch) => pattern.test(ch)).join('');
                if (cleaned === pastedText) return;
                e.preventDefault();
                const current = String(input.value ?? '');
                const start = typeof input.selectionStart === 'number' ? input.selectionStart : current.length;
                const end = typeof input.selectionEnd === 'number' ? input.selectionEnd : current.length;
                input.value = current.slice(0, start) + cleaned + current.slice(end);
                showInputGuardError(input, resolveMessage(pastedText));
            });

            input.addEventListener('input', function () {
                const current = String(input.value ?? '');
                const cleaned = Array.from(current).filter((ch) => pattern.test(ch)).join('');
                if (cleaned !== current) {
                    input.value = cleaned;
                    showInputGuardError(input, resolveMessage(current));
                    return;
                }
                if (input.classList.contains('is-invalid')) {
                    removeInputGuardError(input);
                }
            });
        }

        function bindClearGuardOnBlurIfValid(selector) {
            const input = document.querySelector(selector);
            if (!input || input.dataset.clearGuardOnBlurBound === '1') return;
            input.dataset.clearGuardOnBlurBound = '1';

            input.addEventListener('blur', function () {
                const key = fieldKeyFromInput(input);
                const maxLen = resolveMaxLength(input, key);
                const value = String(input.value ?? '');
                if (maxLen && value.length > maxLen) return;
                if (typeof input.checkValidity === 'function' && !input.checkValidity()) return;
                delete input.dataset.maxLimitBlocked;
                removeInputGuardError(input);
            });
        }

        function bindBankAccountTitleGuard(selector) {
            const input = document.querySelector(selector);
            if (!input || input.dataset.bankAccountTitleGuardBound === '1') return;
            input.dataset.bankAccountTitleGuardBound = '1';
            const allowedPattern = /[A-Za-z\s.\-'_]/;

            input.addEventListener('beforeinput', function (e) {
                if (e.inputType && e.inputType.startsWith('delete')) return;
                const inserted = typeof e.data === 'string' ? e.data : '';
                if (!inserted) return;
                if (!allowedPattern.test(inserted)) {
                    e.preventDefault();
                    showInputGuardError(input, /\d/.test(inserted) ? 'Text only (no numbers).' : 'Use text only.');
                }
            }, true);

            input.addEventListener('paste', function (e) {
                const pastedText = e.clipboardData ? (e.clipboardData.getData('text') || '') : '';
                if (!pastedText) return;
                const cleaned = Array.from(pastedText).filter((ch) => allowedPattern.test(ch)).join('');
                if (cleaned === pastedText) return;
                e.preventDefault();
                const current = String(input.value ?? '');
                const start = typeof input.selectionStart === 'number' ? input.selectionStart : current.length;
                const end = typeof input.selectionEnd === 'number' ? input.selectionEnd : current.length;
                input.value = current.slice(0, start) + cleaned + current.slice(end);
                showInputGuardError(input, /\d/.test(pastedText) ? 'Text only (no numbers).' : 'Use text only.');
            });

            input.addEventListener('input', function () {
                const current = String(input.value ?? '');
                const cleaned = Array.from(current).filter((ch) => allowedPattern.test(ch)).join('');
                if (cleaned !== current) {
                    input.value = cleaned;
                    showInputGuardError(input, /\d/.test(current) ? 'Text only (no numbers).' : 'Use text only.');
                    return;
                }
                if (current.length > 0 && current.trim().length < 3) {
                    showInputGuardError(input, 'At least 3 characters required.');
                    return;
                }
                removeInputGuardError(input);
            });
        }

        function bindMoreContactFieldGuards() {
            const contactConfigs = [
                { id: 'moreContactResidencePhoneInput', required: false },
                { id: 'moreContactEmergencyContactInput', required: false },
                { id: 'moreContactCellNoInput', required: true },
            ];

            contactConfigs.forEach((cfg) => {
                const input = document.getElementById(cfg.id);
                if (!input || input.dataset.moreContactGuardBound === '1') return;
                input.dataset.moreContactGuardBound = '1';

                input.addEventListener('beforeinput', function (e) {
                    if (e.inputType && e.inputType.startsWith('delete')) return;
                    const inserted = typeof e.data === 'string' ? e.data : '';
                    if (!inserted) return;
                    if (!/^\d+$/.test(inserted)) {
                        e.preventDefault();
                        showInputGuardError(input, 'Digits only.');
                        return;
                    }
                    const nextLen = nextLengthWithInsert(input, inserted);
                    if (nextLen > 15) {
                        e.preventDefault();
                        showInputGuardError(input, 'Maximum 15 digits allowed.');
                    }
                }, true);

                input.addEventListener('input', function () {
                    const value = String(input.value || '').replace(/\D/g, '');
                    input.value = value.slice(0, 15);
                    if (!value) {
                        if (cfg.required) {
                            showInputGuardError(input, 'Cell number is required.');
                        } else {
                            removeInputGuardError(input);
                        }
                        return;
                    }
                    const minDigits = (cfg.id === 'moreContactResidencePhoneInput') ? 7 : 11;
                    if (value.length < minDigits) {
                        showInputGuardError(input, `Enter ${minDigits} to 15 digits.`);
                        return;
                    }
                    removeInputGuardError(input);
                });
            });

            const emailInput = document.getElementById('moreContactEmailInput');
            if (emailInput && emailInput.dataset.moreContactGuardBound !== '1') {
                emailInput.dataset.moreContactGuardBound = '1';
                bindPoliceExplicitMaxGuard('#moreContactEmailInput', 255);
                emailInput.addEventListener('input', function () {
                    const value = String(emailInput.value || '').trim();
                    if (!value) {
                        showInputGuardError(emailInput, 'Email is required.');
                        return;
                    }
                    if (!isValidEmail(value)) {
                        showInputGuardError(emailInput, 'Enter a valid email address.');
                        return;
                    }
                    removeInputGuardError(emailInput);
                });
            }

            const addressConfigs = [
                { id: 'moreContactPresentAddressInput', label: 'Present address' },
                { id: 'moreContactPermanentAddressInput', label: 'Permanent address' },
            ];
            addressConfigs.forEach((cfg) => {
                const field = document.getElementById(cfg.id);
                if (!field || field.dataset.moreContactGuardBound === '1') return;
                field.dataset.moreContactGuardBound = '1';
                bindPoliceExplicitMaxGuard(`#${cfg.id}`, 1000);
                field.addEventListener('input', function () {
                    const value = String(field.value || '').trim();
                    if (!value) {
                        showInputGuardError(field, `${cfg.label} is required.`);
                        return;
                    }
                    if (value.length < 10) {
                        showInputGuardError(field, `${cfg.label} must be at least 10 characters.`);
                        return;
                    }
                    removeInputGuardError(field);
                });
            });
        }

        bindEmploymentExplicitMaxGuard('#employmentDetailsInternDurationInput', 10);
        bindEmploymentExplicitMaxGuard('#designation', 50);
        bindEmploymentExplicitMaxGuard('#grade', 10);
        bindEmploymentExplicitMaxGuard('#branch', 30);
        bindEmploymentExplicitMaxGuard('#location', 100);
        bindEmploymentExplicitMaxGuard('#biometric_id', 20);
        bindEmploymentAlphaTextGuard('#designation', 'Designation');
        bindEmploymentBoundedIntegerGuard('#employmentCustomGracePeriodInput', 600, 3);
        bindEmploymentExplicitMaxGuard('#employmentTerminationReasonInput', 2000);
        bindPoliceExplicitMaxGuard('#policeVerificationMsrNumberInput', 20);
        bindPoliceExplicitMaxGuard('#policeVerificationLetterNumberInput', 50);
        bindPoliceExplicitMaxGuard('#policeVerificationAddresseeInput', 100);
        bindPoliceExplicitMaxGuard('#policeVerificationVerifyingAuthorityInput', 50);
        bindPoliceExplicitMaxGuard('#policeVerificationRemarksInput', 2000);
        bindPolicePatternGuard('#policeVerificationMsrNumberInput', /[0-9]/, 'MSR number must contain digits only.');
        bindPolicePatternGuard('#policeVerificationLetterNumberInput', /[A-Za-z0-9\/\-_]/, 'Verification letter number may only contain letters, numbers, slash (/), hyphen (-), and underscore (_).');
        bindPolicePatternGuard('#policeVerificationVerifyingAuthorityInput', /[A-Za-z\s.\-&,\/()']/, 'Use letters and basic punctuation only.');
        [
            '#policeVerificationMsrNumberInput',
            '#policeVerificationLetterNumberInput',
            '#policeVerificationAddresseeInput',
            '#policeVerificationVerifyingAuthorityInput',
            '#policeVerificationRemarksInput'
        ].forEach(bindClearGuardOnBlurIfValid);
        bindBankAccountTitleGuard('#bankDetailsAccountTitleInput');
        bindPoliceExplicitMaxGuard('#bankDetailsAccountTitleInput', 50);
        bindPolicePatternGuard(
            '#bankDetailsAccountNumberInput',
            /[0-9]/,
            'Digits only.'
        );
        bindPoliceExplicitMaxGuard('#bankDetailsAccountNumberInput', 16);
        bindPolicePatternGuard(
            '#bankDetailsIbanInput',
            /[A-Za-z0-9]/,
            'Use letters and numbers only (no spaces).'
        );
        bindPoliceExplicitMaxGuard('#bankDetailsIbanInput', 34);
        bindPoliceExplicitMaxGuard('#bankDetailsBranchNameInput', 100);
        bindPoliceExplicitMaxGuard('#bankDetailsBranchAddressInput', 150);
        bindPoliceExplicitMaxGuard('#bankDetailsBranchCodeInput', 10);
        bindPolicePatternGuard('#bankDetailsBranchCodeInput', /[A-Za-z0-9\-]/, 'Branch code may only contain letters, numbers, and hyphens.');
        bindPolicePatternGuard('#bankDetailsBranchAddressInput', /[A-Za-z0-9\s.\-&,\/()#']/, 'Branch address may only contain letters, numbers, spaces, and basic punctuation.');
        bindPolicePatternGuard('#bankDetailsBranchNameInput', /[A-Za-z0-9\s.'\-&,\/#()]/, 'Bank name contains invalid characters.');
        bindPoliceExplicitMaxGuard('#moreContactEmailInput', 255);
        bindPoliceExplicitMaxGuard('#moreContactPresentAddressInput', 1000);
        bindPoliceExplicitMaxGuard('#moreContactPermanentAddressInput', 1000);
        bindPoliceExplicitMaxGuard('#moreMedicalLastFitnessTestInput', 500);
        bindPoliceExplicitMaxGuard('#moreMedicalDisabilityDescriptionInput', 1000);
        bindPoliceExplicitMaxGuard('#moreMedicalChronicDiseaseDescriptionInput', 1000);
        bindPoliceExplicitMaxGuard('#moreReferenceOneNameInput', 50);
        bindPoliceExplicitMaxGuard('#moreReferenceOneDesignationInput', 50);
        bindPoliceExplicitMaxGuard('#moreReferenceOneOrganizationInput', 100);
        bindPoliceExplicitMaxGuard('#moreReferenceTwoNameInput', 50);
        bindPoliceExplicitMaxGuard('#moreReferenceTwoDesignationInput', 50);
        bindPoliceExplicitMaxGuard('#moreReferenceTwoOrganizationInput', 100);
        bindPolicePatternGuard('#moreReferenceOneNameInput', /[A-Za-z\s.\-'_]/, 'Use text only.');
        bindPolicePatternGuard('#moreReferenceTwoNameInput', /[A-Za-z\s.\-'_]/, 'Use text only.');
        [
            '#moreMedicalLastFitnessTestInput',
            '#moreMedicalDisabilityDescriptionInput',
            '#moreMedicalChronicDiseaseDescriptionInput',
            '#moreReferenceOneNameInput',
            '#moreReferenceOneDesignationInput',
            '#moreReferenceOneOrganizationInput',
            '#moreReferenceTwoNameInput',
            '#moreReferenceTwoDesignationInput',
            '#moreReferenceTwoOrganizationInput'
        ].forEach(bindClearGuardOnBlurIfValid);
        bindPoliceExplicitMaxGuard('#armedDetailsServiceNoInput', 50);
        bindPoliceExplicitMaxGuard('#armedDetailsRankInput', 50);
        bindPoliceExplicitMaxGuard('#armedDetailsMedicalCategoryInput', 50);
        bindPoliceExplicitMaxGuard('#armedDetailsRetirementReasonInput', 255);
        bindPoliceExplicitMaxGuard('#armedDetailsCorpsRegimentSquadronInput', 100);
        bindPoliceExplicitMaxGuard('#armedDetailsExArmyUnitInput', 100);
        bindPoliceExplicitMaxGuard('#armedDetailsTradeInput', 50);
        bindPoliceExplicitMaxGuard('#armedDetailsPmaLcOtsInput', 100);
        bindPolicePatternGuard('#armedDetailsServiceNoInput', /[A-Za-z0-9\/\-_]/, 'Service number may only contain letters, numbers, slash (/), hyphen (-), and underscore (_).');
        bindPolicePatternGuard('#armedDetailsRankInput', /[A-Za-z0-9\s.\-\/]/, 'Rank may contain letters, numbers, spaces, dots, hyphens, and slashes only.');
        bindPolicePatternGuard('#armedDetailsMedicalCategoryInput', /[A-Za-z0-9\s.\-&,\/()#']/, 'Medical category may only contain letters, numbers, spaces, and standard punctuation.');
        bindPolicePatternGuard('#armedDetailsCorpsRegimentSquadronInput', /[A-Za-z\s.\-&,\/()']/, 'Use letters and basic punctuation only.');
        bindPolicePatternGuard('#armedDetailsExArmyUnitInput', /[A-Za-z\s.\-&,\/()']/, 'Use letters and basic punctuation only.');
        bindPolicePatternGuard('#armedDetailsTradeInput', /[A-Za-z\s.\-&,\/()']/, 'Use letters and basic punctuation only.');
        bindPolicePatternGuard('#armedDetailsPmaLcOtsInput', /[A-Za-z0-9\s.\-&,\/()#']/, 'Use letters, numbers, spaces, and basic punctuation only.');
        [
            '#armedDetailsServiceNoInput',
            '#armedDetailsRankInput',
            '#armedDetailsMedicalCategoryInput',
            '#armedDetailsRetirementReasonInput',
            '#armedDetailsCorpsRegimentSquadronInput',
            '#armedDetailsExArmyUnitInput',
            '#armedDetailsTradeInput',
            '#armedDetailsPmaLcOtsInput'
        ].forEach(bindClearGuardOnBlurIfValid);
        bindMoreContactFieldGuards();

        function validatePoliceVerificationDateLogic() {
            const statusInput = document.querySelector('input[name="verification_status"]:checked');
            const status = statusInput ? statusInput.value : '';
            const isMandatory = status === 'Cleared' || status === 'Not Cleared';
            const msrDateInput = document.getElementById('policeVerificationMsrDateInput');
            const letterDateInput = document.getElementById('policeVerificationLetterDateInput');
            const nextDateInput = document.getElementById('policeVerificationNextVerificationDateInput');
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            const parseDate = function (value) {
                if (!value) return null;
                const dt = new Date(`${value}T00:00:00`);
                return Number.isNaN(dt.getTime()) ? null : dt;
            };

            if (msrDateInput) {
                const msrDate = parseDate(msrDateInput.value);
                if (isMandatory && msrDate && msrDate > today) {
                    showInputGuardError(msrDateInput, 'MSR date cannot be in the future.');
                } else {
                    removeInputGuardError(msrDateInput);
                }
            }

            if (letterDateInput) {
                const msrDate = parseDate(msrDateInput ? msrDateInput.value : '');
                const letterDate = parseDate(letterDateInput.value);
                if (isMandatory && letterDate && letterDate > today) {
                    showInputGuardError(letterDateInput, 'Verification letter date cannot be in the future.');
                } else if (letterDate && msrDate && letterDate < msrDate) {
                    showInputGuardError(letterDateInput, 'Verification letter date must be on or after MSR date.');
                } else {
                    removeInputGuardError(letterDateInput);
                }
            }

            if (nextDateInput) {
                const letterDate = parseDate(letterDateInput ? letterDateInput.value : '');
                const nextDate = parseDate(nextDateInput.value);
                if (nextDate && nextDate < today) {
                    showInputGuardError(nextDateInput, 'Next verification date must be today or a future date.');
                } else if (isMandatory && nextDate && letterDate && nextDate <= letterDate) {
                    showInputGuardError(nextDateInput, 'Next verification date must be after verification letter date.');
                } else {
                    removeInputGuardError(nextDateInput);
                }
            }
        }

        function validateArmedDatesLogic() {
            const commissioningInput = document.getElementById('armedDetailsCommissioningEnrollmentDateInput');
            const retirementInput = document.getElementById('armedDetailsRetirementDateInput');
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            const parseDate = function (value) {
                if (!value) return null;
                const dt = new Date(`${value}T00:00:00`);
                return Number.isNaN(dt.getTime()) ? null : dt;
            };

            if (commissioningInput) {
                const commissioningDate = parseDate(commissioningInput.value);
                if (commissioningDate && commissioningDate > today) {
                    showInputGuardError(commissioningInput, 'Date of commissioning / enrollment cannot be in the future.');
                } else {
                    removeInputGuardError(commissioningInput);
                }
            }

            if (retirementInput) {
                const retirementDate = parseDate(retirementInput.value);
                const commissioningDate = parseDate(commissioningInput ? commissioningInput.value : '');
                if (retirementDate && commissioningDate && retirementDate < commissioningDate) {
                    showInputGuardError(retirementInput, 'Date of retirement must be after or equal to date of commissioning / enrollment.');
                } else {
                    removeInputGuardError(retirementInput);
                }
            }
        }

        document.addEventListener('change', function (e) {
            const target = e.target;
            if (!target) return;
            if (!target.closest('#employeeForm')) return;
            if (
                target.name === 'verification_status'
                || target.name === 'msr_date'
                || target.name === 'verification_letter_date'
                || target.name === 'next_verification_date'
            ) {
                validatePoliceVerificationDateLogic();
            }
            if (
                target.name === 'date_of_commissioning'
                || target.name === 'date_of_retirement'
            ) {
                validateArmedDatesLogic();
            }
        }, true);

        validatePoliceVerificationDateLogic();
        validateArmedDatesLogic();
    }

    applyInputGuards();

    const initialSbu = sbuSelect ? sbuSelect.value : null;
    const initialRole = roleSelect ? roleSelect.value : null;

    // Conditional Visibility Handling
    function syncConditionalVisibility() {
        const armyCheck = document.getElementById('giExArmyRetiredCheckbox');
        const armyTab = document.querySelector('.profile-tab[data-step="4"]');
        const armyPane = document.getElementById('stepPane4');
        if (armyCheck && armyTab) {
            if (armyCheck.checked) {
                armyTab.classList.remove('d-none');
                if (armyPane) armyPane.classList.remove('d-none');
            } else {
                armyTab.classList.add('d-none');
                if (armyPane) armyPane.classList.add('d-none');
                if (currentStep === 4) {
                    goToStep(3);
                }
            }
        }

        // Father Deceased / CNIC
        const deceasedCheck = document.getElementById('giFatherDeceasedCheckbox');
        const fatherCnicField = document.getElementById('giFatherCnicField');
        const fatherCnicInput = document.querySelector('[name="father_cnic"]');
        if (deceasedCheck && fatherCnicField) {
            if (deceasedCheck.checked) {
                fatherCnicField.classList.add('d-none');
                if (fatherCnicInput) {
                    fatherCnicInput.required = false;
                    fatherCnicInput.value = ''; // Clear if deceased
                    clearFieldStatus(fatherCnicInput);
                }
            } else {
                fatherCnicField.classList.remove('d-none');
                if (fatherCnicInput) {
                    fatherCnicInput.required = true;
                }
            }
        }

        // Marital Status / Spouse Details
        const maritalStatusSelect = document.getElementById('giMaritalStatusSelect');
        const spouseFields = [
            document.getElementById('giSpouseNameField'),
            document.getElementById('giSpouseNationalityField'),
            document.getElementById('giSpouseCnicField'),
        ];
        
        if (maritalStatusSelect) {
            const isMarried = maritalStatusSelect.value === 'Married';
            spouseFields.forEach(field => {
                if (field) {
                    field.classList.toggle('d-none', !isMarried);
                    const input = field.querySelector('input, select');
                    if (input) {
                        input.required = isMarried;
                        if (!isMarried) {
                            input.value = '';
                            clearFieldStatus(input);
                        }
                    }
                }
            });
        }
    }

    function togglePoliceVerificationFields() {
        const activeStatus = document.querySelector('input[name="verification_status"]:checked');
        const status = activeStatus ? activeStatus.value : '';
        const isMandatory = (status === 'Cleared' || status === 'Not Cleared');

        const stars = document.querySelectorAll('.police-mandatory-star');
        const fields = document.querySelectorAll('.police-verification-field');

        stars.forEach(star => {
            if (isMandatory) star.classList.remove('d-none');
            else star.classList.add('d-none');
        });

        fields.forEach(field => {
            field.required = isMandatory;
        });
    }

    function confirmExEmploymentToggle(inputEl, nextCheckedValue, message) {
        const applyCheckedState = function () {
            inputEl.checked = nextCheckedValue;
            inputEl.dataset.skipExEmploymentConfirm = '1';
            inputEl.dispatchEvent(new Event('change', { bubbles: true }));
        };

        if (window.Swal && typeof window.Swal.fire === 'function') {
            window.Swal.fire({
                title: 'Please Confirm',
                text: message,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#1a237e',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes',
                cancelButtonText: 'No'
            }).then(function (result) {
                if (result.isConfirmed) {
                    applyCheckedState();
                }
            });
            return;
        }

        if (window.confirm(message)) {
            applyCheckedState();
        }
    }

    function bindExEmploymentConfirmations() {
        const optionConfig = {
            giExArmyRetiredCheckbox: {
                onEnable: 'Do you want to mark this employee as Ex-Army Retired?',
                onDisable: 'Do you want to unmark Ex-Army Retired for this employee?'
            },
            giFatherDeceasedCheckbox: {
                onEnable: 'Do you want to mark Father Deceased?',
                onDisable: 'Do you want to unmark Father Deceased?'
            }
        };

        if (document.body.dataset.exEmploymentConfirmBound === '1') return;
        document.body.dataset.exEmploymentConfirmBound = '1';

        document.addEventListener('change', function (event) {
            const el = event.target;
            if (!el || !el.id || !optionConfig[el.id]) return;

            if (el.dataset.skipExEmploymentConfirm === '1') {
                el.dataset.skipExEmploymentConfirm = '';
                return;
            }

            const nextCheckedValue = !!el.checked;
            const message = nextCheckedValue ? optionConfig[el.id].onEnable : optionConfig[el.id].onDisable;

            event.stopImmediatePropagation();
            el.checked = !nextCheckedValue;
            confirmExEmploymentToggle(el, nextCheckedValue, message);
        }, true);
    }


    document.addEventListener('change', function(e) {
        if (e.target.id === 'giExArmyRetiredCheckbox' || e.target.id === 'giFatherDeceasedCheckbox' || e.target.id === 'giMaritalStatusSelect') {
            syncConditionalVisibility();
        }
        if (e.target.name === 'verification_status') {
            togglePoliceVerificationFields();
        }
    });
    bindExEmploymentConfirmations();

    // SweetAlert2 Helpers
    const showSuccess = (message, title = 'Success') => {
        return Swal.fire({
            icon: 'success',
            title: title,
            text: message,
            confirmButtonColor: '#1a237e',
            timer: 3000,
            timerProgressBar: true
        });
    };

    const showError = (message, title = 'Error') => {
        return Swal.fire({
            icon: 'error',
            title: title,
            text: message,
            confirmButtonColor: '#1a237e'
        });
    };

    const showConfirm = (message, title = 'Are you sure?') => {
        return Swal.fire({
            title: title,
            text: message,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#1a237e',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, proceed!'
        });
    };

    // Error and State Handling
    function clearFieldStatus(el) {
        if (!el) return;
        const container = el.closest('.col-12, .col-md-6, .col-md-4, .col-md-3, .col-xl-3, .col, .form-group, .mb-3');
        if (container && container.querySelector('.input-guard-error')) {
            return;
        }

        el.classList.remove('is-invalid');
        // Find the closest parent that might contain error messages
        if (container) {
            container.querySelectorAll('.field-error-msg').forEach(err => err.remove());
        } else {
            // Fallback: check immediate siblings
            const siblings = Array.from(el.parentElement.children);
            siblings.forEach(node => {
                if (node.classList && node.classList.contains('field-error-msg')) {
                    node.remove();
                }
            });
        }
    }

    window.showToast = function(title, icon = 'success') {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: icon,
            title: title,
            showConfirmButton: false,
            timer: 2000,
            timerProgressBar: true
        });
    };
    const showToast = window.showToast;

    function clearStepErrors() {
        document.querySelectorAll('.is-invalid').forEach(el => {
            el.classList.remove('is-invalid');
        });
        document.querySelectorAll('.field-error-msg').forEach(err => err.remove());
        
        // Remove any legacy or stray error containers
        const generalErrors = document.getElementById('error-container');
        if (generalErrors) generalErrors.innerHTML = '';
    }

    // Field name -> visible element mapping overrides
    const fieldElementMap = {
        'engagement_mode':          null, // handled as radio group below
        'standard_schedule_mode':   null,
        'hybrid_days':              null,
        'working_days':             null,
        'employee_contract_start_date': 'employmentDetailsEmployeeContractStartDateInput',
        'employee_contract_end_date':   'employmentDetailsEmployeeContractEndDateInput',
        'contract_start_date':          'employmentDetailsContractStartDateInput',
        'contract_end_date':            'employmentDetailsContractEndDateInput',
        'employee_status':              'employmentStatusInput',
        'termination_reason':           'employmentTerminationReasonInput',
        'termination_date':             'employmentTerminationDateInput',
        'probation_start_date':         'employmentProbationStartDateInput',
        'probation_end_date':           'employmentProbationEndDateInput',
        'probation_contract_start_date': 'employmentProbationContractStartDateInput',
        'assigned_floor_ids':           'employmentAssignedFloorsSelect',
        'working_start_time':           'employmentCustomWorkingStartInput',
        'working_end_time':             'employmentCustomWorkingEndInput',
        'grace_period':                 'employmentCustomGracePeriodInput',
        'opening_grace_period':         'employmentCustomGracePeriodInput',
        'closing_grace_period':         'employmentCustomGracePeriodInput',
        'join_date':                    'employmentJoinDateInput',
        'designation':                  'designation',
        'grade':                        'grade',
        'branch':                       'branch',
        'location':                     'location',
        'biometric_id':                 'biometric_id',
        'service_no':                   'armedDetailsServiceNoInput',
        'rank':                         'armedDetailsRankInput',
        'medical_category':             'armedDetailsMedicalCategoryInput',
        'date_of_commissioning':        'armedDetailsCommissioningEnrollmentDateInput',
        'date_of_retirement':           'armedDetailsRetirementDateInput',
        'reason_of_retirement':         'armedDetailsRetirementReasonInput',
        'corps_regiment':               'armedDetailsCorpsRegimentSquadronInput',
        'ex_army_unit':                 'armedDetailsExArmyUnitInput',
        'trade':                        'armedDetailsTradeInput',
        'pma_lc_ots':                   'armedDetailsPmaLcOtsInput',
        'account_title':                'bankDetailsAccountTitleInput',
        'account_no':                   'bankDetailsAccountNumberInput',
        'iban':                         'bankDetailsIbanInput',
        'bank_name':                    'bankDetailsBranchNameInput',
        'branch_code':                  'bankDetailsBranchCodeInput',
        'branch_address':               'bankDetailsBranchAddressInput',
    };

    // For radio groups: map field name -> wrapper element id to append error immediately after the pill row
    const radioGroupWrapperMap = {
        'working_days':           'employmentWorkArrangementCustomFields',
        'account_category':      'bankAccountCategoryWrapper',
        'is_salary_account':     'isSalaryAccountWrapper',
        'account_type':          'bankAccountTypeWrapper',
        'banks':                 'bankDetailsList',
    };

    function showFieldErrors(errors, container = document) {
        if (container === document) {
            clearStepErrors();
        } else {
            // Localized clear within the row
            container.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            container.querySelectorAll('.field-error-msg').forEach(err => err.remove());
        }
        
        Object.entries(errors).forEach(([field, messages]) => {
            let fieldName = field;
            if (field.includes('.')) {
                const parts = field.split('.');
                fieldName = parts[0] + parts.slice(1).map(p => `[${p}]`).join('');
            }

            const msg = messages[0];

            // Handle radio/checkbox groups specially
            if (radioGroupWrapperMap[field]) {
                const wrapper = document.getElementById(radioGroupWrapperMap[field]);
                if (wrapper) {
                    // Mark all inputs in the group
                    document.querySelectorAll(`input[name="${field}"]`).forEach(r => r.classList.add('is-invalid'));
                    // Only inject once
                    if (!wrapper.nextElementSibling || !wrapper.nextElementSibling.classList.contains('field-error-msg')) {
                        const err = document.createElement('div');
                        err.className = 'field-error-msg text-danger small mt-1 fw-bold';
                        err.textContent = msg;
                        wrapper.insertAdjacentElement('afterend', err);
                    }
                }
                return;
            }

            // Handle element id overrides
            let input = null;
            if (fieldElementMap[field] !== undefined && container === document) {
                if (fieldElementMap[field]) {
                    input = document.getElementById(fieldElementMap[field]);
                }
            } else {
                input = container.querySelector(`[name="${fieldName}"]`) ||
                        container.querySelector(`[name="${fieldName}[]"]`) ||
                        container.querySelector(`[name$="[][${fieldName}]"]`) ||
                        (container === document ? document.getElementById(fieldName) : container.querySelector(`#${fieldName}`));
            }

            if (input) {
                input.classList.add('is-invalid');
                
                // If the input is in a hidden container (e.g. custom schedule fields while in default mode), 
                // we should reveal it so the user can see the error, OR at least the user should know.
                let parent = input.parentElement;
                while (parent && parent !== document.body) {
                    if (parent.classList.contains('d-none')) {
                        parent.classList.remove('d-none');
                    }
                    parent = parent.parentElement;
                }

                const err = document.createElement('div');
                err.className = 'field-error-msg text-danger small mt-1 fw-bold';
                err.textContent = msg;
                
                const container = input.closest('[class^="col-"], [class*=" col-"], .col, .form-group, .mb-3');
                if (container) {
                    container.appendChild(err);
                } else if (input.parentElement) {
                    input.parentElement.appendChild(err);
                } else {
                    input.insertAdjacentElement('afterend', err);
                }
            } else {
                // Fallback: if no input found, log it and maybe show a global error
                console.warn(`Could not find input for field: ${field}`);
            }
        });

        const firstInvalid = document.querySelector('.is-invalid, .field-error-msg');
        if (firstInvalid) {
            firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }

    function syncStepUi() {
        for (let i = 1; i <= totalSteps; i++) {
            const pane = document.getElementById('stepPane' + i);
            if (pane) pane.classList.toggle('active', i === currentStep);
        }

        const tabs = document.querySelectorAll('.profile-tab');
        tabs.forEach((tab) => {
            const step = Number(tab.getAttribute('data-step'));
            tab.classList.remove('active');
            
            // Manage Tab locking
            if (step > maxStepReached && step !== currentStep) {
                tab.classList.add('locked-tab');
                tab.style.opacity = '0.5';
                tab.style.pointerEvents = 'none';
                tab.setAttribute('title', 'Complete current step to unlock');
            } else {
                tab.classList.remove('locked-tab');
                tab.style.opacity = '1';
                tab.style.pointerEvents = 'auto';
                tab.removeAttribute('title');
            }

            if (step === currentStep) tab.classList.add('active');
        });

        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');
        const editBtn = document.getElementById('editBtn');
        const form = document.getElementById('employeeForm');

        if (prevBtn) prevBtn.style.visibility = currentStep === 1 ? 'hidden' : 'visible';
        
        if (window.isEditMode && editBtn) {
            editBtn.classList.remove('d-none');
            const btnText = editBtn.innerText.trim();
            // Ensure inputs are disabled if not in active Edit mode (Save or Saving state)
            if (btnText !== 'Save' && btnText !== 'Saving...') {
                setStepDisabled(currentStep, true);
                if (form) form.classList.add('form-readonly');
            } else {
                // If we are in Save or Saving mode, ensure inputs are ENABLED
                setStepDisabled(currentStep, false);
                if (form) form.classList.remove('form-readonly');
            }
        }

        if (nextBtn) {
            const isLastStep = currentStep === totalSteps;
            const isLastMoreStep = typeof window.isLastMoreStep === 'function' ? window.isLastMoreStep() : true;

            if (isLastStep && isLastMoreStep) {
                nextBtn.textContent = 'Finish Registration';
            } else {
                nextBtn.textContent = 'Next Step';
            }
        }
    }

    function toggleInputs(container, disabled) {
        if (!container) return;
        const elements = container.querySelectorAll('input, select, textarea, button');
        elements.forEach(el => {
            if (['editBtn', 'nextBtn', 'prevBtn', 'saved_employee_id', 'employee_id'].includes(el.id)) return;
            if (el.classList.contains('more-sub-tab')) return;

            el.disabled = disabled;
            
            if (el.classList.contains('btn-check')) {
                const label = document.querySelector(`label[for="${el.id}"]`);
                if (label) {
                    label.style.opacity = disabled ? '0.6' : '1';
                    label.style.pointerEvents = disabled ? 'none' : 'auto';
                }
            }
        });
        
        const addButtons = container.querySelectorAll('.btn-sm[id^="more"][id$="Btn"], .btn-sm[id^="add"], #bankDetailsAddBtn, .avatar-upload-overlay, #removePhotoBtn');
        addButtons.forEach(btn => {
            btn.disabled = disabled;
            
            // Don't set opacity for profile photo elements here, CSS handles it
            if (!btn.classList.contains('avatar-upload-overlay') && btn.id !== 'removePhotoBtn') {
                btn.style.opacity = disabled ? '0.6' : '1';
            }
            
            btn.style.pointerEvents = disabled ? 'none' : 'auto';
        });

        // Handle custom multi-select components (Departments, Floors)
        const customSelects = container.querySelectorAll('.emp-dept-input-box, .emp-dept-chip-x');
        customSelects.forEach(el => {
            el.style.pointerEvents = disabled ? 'none' : 'auto';
            if (el.classList.contains('emp-dept-input-box')) {
                el.style.backgroundColor = disabled ? '#f8fafc' : '#fff';
            }
        });

        const rowActions = container.querySelectorAll('[data-family-remove], [data-family-save], [data-academic-remove], [data-academic-save], [data-certificate-remove], [data-certificate-save], [data-employment-remove], [data-employment-save], .edit-bank-btn, .delete-bank-btn');
        rowActions.forEach(btn => {
            btn.disabled = disabled;
            btn.style.opacity = disabled ? '0.6' : '1';
            btn.style.pointerEvents = disabled ? 'none' : 'auto';
        });
    }

    function setStepDisabled(step, disabled) {
        const pane = document.getElementById('stepPane' + step);
        if (pane) toggleInputs(pane, disabled);
    }

    // Edit Button Logic
    document.addEventListener('DOMContentLoaded', () => {
        const editBtn = document.getElementById('editBtn');
        if (editBtn) {
            editBtn.addEventListener('click', async function() {
                const isSaving = editBtn.innerText.trim() === 'Save';
                if (isSaving) {
                    const originalHtml = editBtn.innerHTML;
                    editBtn.disabled = true;
                    editBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> <span>Saving...</span>';

                    try {
                        const current = currentStep;
                        if (current === 6) {
                            const moreStep = typeof currentMoreStep !== 'undefined' ? currentMoreStep : 1;
                            if ([1, 6, 7].includes(moreStep)) {
                                await saveMoreSubSection(moreStep, () => {
                                    setStepDisabled(6, true);
                                    editBtn.innerHTML = '<i class="bi bi-pencil-square"></i><span>Edit</span>';
                                    editBtn.classList.remove('btn-success');
                                    editBtn.classList.add('bg-main', 'text-white');
                                }, { skipButtonState: true });
                            } else if ([2, 3, 4, 5].includes(moreStep)) {
                                const autoSaved = await autoSaveMoreDynamicRows(moreStep);
                                if (autoSaved) {
                                    setStepDisabled(6, true);
                                    editBtn.innerHTML = '<i class="bi bi-pencil-square"></i><span>Edit</span>';
                                    editBtn.classList.remove('btn-success');
                                    editBtn.classList.add('bg-main', 'text-white');
                                }
                            } else {
                                setStepDisabled(6, true);
                                editBtn.innerHTML = '<i class="bi bi-pencil-square"></i><span>Edit</span>';
                                editBtn.classList.remove('btn-success');
                                editBtn.classList.add('bg-main', 'text-white');
                            }
                        } else {
                            await processStepSave(current, () => {
                                setStepDisabled(current, true);
                                editBtn.innerHTML = '<i class="bi bi-pencil-square"></i><span>Edit</span>';
                                editBtn.classList.remove('btn-success');
                                editBtn.classList.add('bg-main', 'text-white');
                            }, { skipButtonState: true });
                        }
                    } catch (err) {
                        console.error('Save failed:', err);
                    } finally {
                        editBtn.disabled = false;
                        if (editBtn.innerText.trim() === 'Saving...') {
                            editBtn.innerHTML = originalHtml;
                        }
                    }
                } else {
                    // Enable editing
                    setStepDisabled(currentStep, false);
                    editBtn.innerHTML = '<i class="bi bi-save"></i><span>Save</span>';
                    editBtn.classList.remove('bg-main', 'text-white');
                    editBtn.classList.add('btn-success');
                    const form = document.getElementById('employeeForm');
                    if (form) form.classList.remove('form-readonly');
                }
            });
        }

        // Global Toast Notification for Read-Only Mode
        window.addEventListener('click', function (e) {
            const form = document.getElementById('employeeForm');
            const editBtn = document.getElementById('editBtn');
            
            if (window.isEditMode && form && form.classList.contains('form-readonly') && editBtn && editBtn.innerText.trim() === 'Edit') {
                // Allow clicks on navigation and edit controls
                const isNav = e.target.closest('#prevBtn') || 
                              e.target.closest('#nextBtn') || 
                              e.target.closest('#editBtn') || 
                              e.target.closest('.profile-tab') || 
                              e.target.closest('.more-sub-tab') ||
                              e.target.closest('.swal2-container'); // Allow clicking on toasts/alerts
                
                if (isNav) return;

                // If click is inside the form, show toast
                if (form.contains(e.target)) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'info',
                        title: 'Please click the edit button to make changes',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true,
                        background: '#fff',
                        color: '#012445',
                        iconColor: '#0d6efd'
                    });
                }
            }
        }, true);
    });

    const employmentCodeInput = document.getElementById('employmentEmployeeNumberInput');
    const orgSelectForCode = document.getElementById('employmentOrganizationSelect');
    const roleSelectForCode = document.getElementById('employmentRoleSelect');
    const sbuSelectForCode = document.getElementById('employmentSbuSelect');
    let employeeCodePreviewTimer = null;

    function updateEmployeeCodePreview() {
        if (!employmentCodeInput) return;
        if (!window.previewEmployeeCodeUrl) return;
        if (window.isEditMode) return;

        const organizationId = orgSelectForCode?.value || '';
        const roleId = roleSelectForCode?.value || '';
        const sbuId = sbuSelectForCode?.value || '';

        if (!organizationId || !roleId) {
            return;
        }

        const params = new URLSearchParams({
            organization_id: organizationId,
            role_id: roleId
        });
        if (sbuId) {
            params.set('sbu_id', sbuId);
        }

        fetch(window.previewEmployeeCodeUrl + '?' + params.toString(), {
            headers: { Accept: 'application/json' }
        })
        .then((response) => response.json())
        .then((res) => {
            if (res && res.success && res.code && employmentCodeInput) {
                employmentCodeInput.value = res.code;
            }
        })
        .catch(() => {});
    }

    function scheduleEmployeeCodePreview() {
        if (employeeCodePreviewTimer) {
            clearTimeout(employeeCodePreviewTimer);
        }
        employeeCodePreviewTimer = setTimeout(updateEmployeeCodePreview, 200);
    }

    if (orgSelectForCode) orgSelectForCode.addEventListener('change', scheduleEmployeeCodePreview);
    if (roleSelectForCode) roleSelectForCode.addEventListener('change', scheduleEmployeeCodePreview);
    if (sbuSelectForCode) sbuSelectForCode.addEventListener('change', scheduleEmployeeCodePreview);
    updateEmployeeCodePreview();

    async function processStepSave(step, onSuccess, options = {}) {
        const form = document.getElementById('employeeForm');
        if (!form) return;

        const formData = new FormData(form);
        formData.append('step', step);

        // --- Step 5: Append Saved Bank Accounts ---
        if (step === 5) {
            savedBanks.forEach((bank, index) => {
                Object.entries(bank).forEach(([key, value]) => {
                    if (value !== null && value !== undefined) {
                        formData.append(`banks[${index}][${key}]`, value);
                    }
                });
            });
        }

        // --- Step 6: Append Subsection Data ---
        if (step === 6) {
            if (typeof window.ensureFamilyNokBeforeStepSave === 'function') {
                window.ensureFamilyNokBeforeStepSave();
            }
            const subsystems = ['family', 'academic', 'certificate', 'employment'];
            subsystems.forEach(sub => {
                const containerId = {
                    'family': 'moreFamilyMembersContainer',
                    'academic': 'moreAcademicRecordsContainer',
                    'certificate': 'moreCertificateRecordsContainer',
                    'employment': 'moreEmploymentRecordsContainer'
                }[sub];
                const container = document.getElementById(containerId);
                if (container) {
                    const rows = container.querySelectorAll(`[data-${sub}-row]`);
                    rows.forEach((row, index) => {
                        const dbId = row.getAttribute('data-db-id');
                        if (dbId) formData.append(`${sub === 'employment' ? 'employments' : (sub === 'certificate' ? 'certificates' : sub)}[${index}][id]`, dbId);
                        
                        row.querySelectorAll('input, select, textarea').forEach(input => {
                            const name = input.getAttribute('name');
                            if (name) {
                                const cleanKey = name.match(/\[([^\]]*)\]$/)?.[1] || name;
                                if (cleanKey) formData.append(`${sub === 'employment' ? 'employments' : (sub === 'certificate' ? 'certificates' : sub)}[${index}][${cleanKey}]`, input.value);
                            }
                        });
                    });
                }
            });
        }

        if (typeof window.getAttachmentPayload === 'function') {
            const attachmentPayload = window.getAttachmentPayload();
            const keptIds = Array.isArray(attachmentPayload?.keptAttachmentIds) ? attachmentPayload.keptAttachmentIds : [];
            keptIds.forEach((id) => {
                formData.append('kept_attachment_ids[]', id);
            });
        }

        if (window.croppedImageBlob) {
            const photoName = window.profilePhotoUploadName || 'profile-photo.jpg';
            formData.append('profile_photo', window.croppedImageBlob, photoName);
        }

        const nextBtn = document.getElementById('nextBtn');
        const prevBtn = document.getElementById('prevBtn');
        const originalText = nextBtn ? nextBtn.textContent : '';
        const skipButtonState = !!options.skipButtonState;

        if (!skipButtonState && nextBtn) {
            nextBtn.disabled = true;
            nextBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';
        }
        if (!skipButtonState && prevBtn) prevBtn.disabled = true;

        try {
            const response = await fetch('/admin/employees/save-step', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: formData
            });

            const data = await response.json();

            if (response.status === 422) {
                showFieldErrors(data.errors);
                if (step === 6 && typeof window.setMoreSubStep === 'function' && data.errors) {
                    const refKeys = Object.keys(data.errors).filter((k) => k.startsWith('ref'));
                    if (refKeys.length) {
                        window.setMoreSubStep(7);
                    }
                }
                let errorMsg = 'Please check the highlighted fields.';
                if (data.errors) {
                    const firstError = Object.values(data.errors)[0];
                    if (Array.isArray(firstError) && firstError.length > 0) {
                        errorMsg = firstError[0];
                    }
                }

                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'error',
                    title: '<span style="color: #721c24; font-size: 15px; font-weight: 600;">Action Required</span>',
                                        html: `<span style="color: #721c24; font-size: 13px;">${errorMsg}</span>`,
                    showConfirmButton: false,
                    timer: 5000,
                    timerProgressBar: true,
                    background: '#f8d7da',
                    iconColor: '#dc3545',
                    didOpen: (toast) => {
                        toast.style.border = '1px solid #f5c6cb';
                        toast.style.borderRadius = '12px';
                    }
                });
            } else if (!response.ok) {
                throw new Error(data.message || 'Server error occurred');
            } else if (data.success) {
                clearStepErrors();
                if (data.employee_id) {
                    const idInput = document.getElementById('saved_employee_id');
                    if (idInput) idInput.value = data.employee_id;
                    if (data.employee_code) {
                        const employeeNoInput = document.getElementById('employmentEmployeeNumberInput');
                        if (employeeNoInput) {
                            employeeNoInput.value = data.employee_code;
                        }
                    }
                    
                    if (step === maxStepReached) {
                        maxStepReached = Math.min(totalSteps, getNextStepAfter(step));
                    }
                }
                
                // Keep the preview UI but clear the blob so it's not sent multiple times
                if (window.croppedImageBlob) {
                    window.croppedImageBlob = null;
                }

                if (step === totalSteps) {
                    showSuccess('Employee registration completed successfully!', 'Success').then(() => {
                        window.location.href = '/admin/employees';
                    });
                } else {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: data.message || 'Saved successfully.',
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true
                    });
                    if (onSuccess) onSuccess();
                }
            } else {
                showError(data.message || 'Something went wrong.');
            }
        } catch (error) {
            console.error('Save step error:', error);
            showError(error.message || 'Unable to connect to server.');
        } finally {
            if (!skipButtonState && nextBtn) {
                nextBtn.disabled = false;
                nextBtn.textContent = originalText;
            }
            if (!skipButtonState && prevBtn) prevBtn.disabled = false;
            syncStepUi();
        }
    }

    // Event Listeners
    document.querySelectorAll('.profile-tab').forEach((tab) => {
        tab.addEventListener('click', function () {
            if (isStepUnsaved()) {
                showUnsavedWarning();
                return;
            }
            const step = Number(this.getAttribute('data-step'));
            if (step === 4 && !isExArmedForceChecked()) {
                return;
            }
            if (step <= maxStepReached) {
                currentStep = step;
                syncStepUi();
            }
        });
    });

    document.getElementById('nextBtn').addEventListener('click', async function () {
        if (isStepUnsaved()) {
            showUnsavedWarning();
            return;
        }
        const editBtn = document.getElementById('editBtn');
        const inViewMode = window.isEditMode && editBtn && editBtn.innerText.trim() === 'Edit';

        if (currentStep === 6) {
            const isLastMoreStep = typeof window.isLastMoreStep === 'function' ? window.isLastMoreStep() : true;
            if (!isLastMoreStep) {
                if (inViewMode) {
                    if (typeof window.nextMoreSubStep === 'function') {
                        window.nextMoreSubStep();
                        syncStepUi();
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    }
                    return;
                }
                const moreStep = currentMoreStep;
                if ([1, 6, 7].includes(moreStep)) {
                    saveMoreSubSection(moreStep, () => {
                        if (typeof window.nextMoreSubStep === 'function') {
                            window.nextMoreSubStep();
                            syncStepUi();
                            window.scrollTo({ top: 0, behavior: 'smooth' });
                        }
                    });
                    return;
                }

                // For dynamic rows (2,3,4), auto-save unsaved cards before moving next.
                if ([2, 3, 4, 5].includes(moreStep)) {
                    const autoSaved = await autoSaveMoreDynamicRows(moreStep);
                    if (!autoSaved) {
                        return;
                    }
                    if (typeof window.nextMoreSubStep === 'function') {
                        window.nextMoreSubStep();
                        syncStepUi();
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                        return;
                    }
                }
            }
        }

        if (currentStep < totalSteps) {
            if (inViewMode) {
                currentStep = getNextStepAfter(currentStep);
                syncStepUi();
                window.scrollTo({ top: 0, behavior: 'smooth' });
                return;
            }
            processStepSave(currentStep, () => {
                currentStep = getNextStepAfter(currentStep);
                syncStepUi();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        } else {
            if (inViewMode) {
                showSuccess('Employee registration completed successfully!', 'Success').then(() => {
                    window.location.href = '/admin/employees';
                });
                return;
            }
            // Final submission
            processStepSave(currentStep); 
        }
    });

    function validateMoreContactSubsection() {
        const pane = document.getElementById('moreStepPane1');
        if (!pane) return true;

        const residencePhone = String(document.getElementById('moreContactResidencePhoneInput')?.value ?? '').trim();
        const emergencyContact = String(document.getElementById('moreContactEmergencyContactInput')?.value ?? '').trim();
        const cellNo = String(document.getElementById('moreContactCellNoInput')?.value ?? '').trim();
        const contactEmail = String(document.getElementById('moreContactEmailInput')?.value ?? '').trim();
        const presentAddress = String(document.getElementById('moreContactPresentAddressInput')?.value ?? '').trim();
        const permanentAddress = String(document.getElementById('moreContactPermanentAddressInput')?.value ?? '').trim();

        const phoneRegex = /^\d{10,15}$/;
        const residencePhoneRegex = /^\d{7,15}$/;
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        const errors = {};

        if (residencePhone && !residencePhoneRegex.test(residencePhone)) {
            errors.residence_phone = ['Residence phone must be 7 to 15 digits.'];
        }
        if (emergencyContact && !phoneRegex.test(emergencyContact)) {
            errors.emergency_contact = ['Emergency contact must be 10 to 15 digits.'];
        }
        if (!cellNo) {
            errors.cell_no = ['Cell number is required.'];
        } else if (!phoneRegex.test(cellNo)) {
            errors.cell_no = ['Cell number must be 10 to 15 digits.'];
        }
        if (!contactEmail) {
            errors.contact_email = ['Email is required.'];
        } else if (contactEmail.length > 255) {
            errors.contact_email = ['Email must not exceed 255 characters.'];
        } else if (!emailRegex.test(contactEmail)) {
            errors.contact_email = ['Enter a valid email address.'];
        }
        if (!presentAddress) {
            errors.present_address = ['Present address is required.'];
        } else if (presentAddress.length < 10) {
            errors.present_address = ['Present address must be at least 10 characters.'];
        } else if (presentAddress.length > 1000) {
            errors.present_address = ['Present address must not exceed 1000 characters.'];
        }
        if (!permanentAddress) {
            errors.permanent_address = ['Permanent address is required.'];
        } else if (permanentAddress.length < 10) {
            errors.permanent_address = ['Permanent address must be at least 10 characters.'];
        } else if (permanentAddress.length > 1000) {
            errors.permanent_address = ['Permanent address must not exceed 1000 characters.'];
        }

        if (Object.keys(errors).length > 0) {
            showFieldErrors(errors, pane);
            return false;
        }
        return true;
    }

    function validateMoreMedicalSubsection() {
        const pane = document.getElementById('moreStepPane6');
        if (!pane) return true;

        const hasDisability = String(document.querySelector('input[name="has_disability"]:checked')?.value ?? '').trim().toLowerCase();
        const bloodGroup = String(document.getElementById('moreMedicalBloodGroupInput')?.value ?? '').trim();
        const disabilityType = String(document.getElementById('moreMedicalDisabilityTypeInput')?.value ?? '').trim();
        const disabilityDescription = String(document.getElementById('moreMedicalDisabilityDescriptionInput')?.value ?? '').trim();
        const fitnessDate = String(document.getElementById('moreMedicalLastFitnessTestDateInput')?.value ?? '').trim();
        const fitnessResult = String(document.getElementById('moreMedicalLastFitnessTestResultInput')?.value ?? '').trim();
        const lastFitnessTest = String(document.getElementById('moreMedicalLastFitnessTestInput')?.value ?? '').trim();
        const todayMedical = new Date();
        todayMedical.setHours(0, 0, 0, 0);

        const errors = {};
        const bloodGroupRegex = /^(A|B|AB|O)[+-]$/;

        if (!['yes', 'no'].includes(hasDisability)) {
            errors.has_disability = ['Select disability status (Yes or No).'];
        }
        if (lastFitnessTest.length > 500) {
            errors.last_fitness_test = ['Last fitness test notes must not exceed 500 characters.'];
        }
        if (fitnessDate) {
            if (!isValidDate(fitnessDate)) {
                errors.last_fitness_test_date = ['Enter a valid fitness test date.'];
            } else if (dateValue(fitnessDate) > todayMedical) {
                errors.last_fitness_test_date = ['Fitness test date cannot be in the future.'];
            }
        }
        if (fitnessResult && !['Positive', 'Negative'].includes(fitnessResult)) {
            errors.last_fitness_test_result = ['Select Positive or Negative.'];
        }
        if ((fitnessDate && !fitnessResult) || (!fitnessDate && fitnessResult)) {
            if (!fitnessDate) errors.last_fitness_test_date = ['Select the fitness test date when a result is chosen.'];
            if (!fitnessResult) errors.last_fitness_test_result = ['Select the fitness test result when a date is entered.'];
        }
        if (bloodGroup && !bloodGroupRegex.test(bloodGroup)) {
            errors.blood_group = ['Blood group must be like A+, O-, or AB+.'];
        }
        if (hasDisability === 'yes') {
            if (!disabilityType) {
                errors.disability_type = ['Disability type is required when disability is Yes.'];
            } else if (disabilityType.length > 100) {
                errors.disability_type = ['Disability type must not exceed 100 characters.'];
            }
        }
        if (disabilityType === 'Other') {
            if (!disabilityDescription) {
                errors.disability_description = ['Specify disability details for Other.'];
            } else if (disabilityDescription.length > 1000) {
                errors.disability_description = ['Disability details must not exceed 1000 characters.'];
            }
        } else if (disabilityDescription.length > 1000) {
            errors.disability_description = ['Disability details must not exceed 1000 characters.'];
        }

        const hasChronicDisease = String(document.querySelector('input[name="has_chronic_disease"]:checked')?.value ?? '').trim().toLowerCase();
        const chronicDescription = String(document.getElementById('moreMedicalChronicDiseaseDescriptionInput')?.value ?? '').trim();
        if (!['yes', 'no'].includes(hasChronicDisease)) {
            errors.has_chronic_disease = ['Select chronic disease status (Yes or No).'];
        }
        if (hasChronicDisease === 'yes') {
            if (!chronicDescription) {
                errors.chronic_disease_description = ['Specify the chronic disease.'];
            } else if (chronicDescription.length > 1000) {
                errors.chronic_disease_description = ['Chronic disease description must not exceed 1000 characters.'];
            }
        } else if (chronicDescription.length > 1000) {
            errors.chronic_disease_description = ['Chronic disease description must not exceed 1000 characters.'];
        }

        if (Object.keys(errors).length > 0) {
            showFieldErrors(errors, pane);
            return false;
        }
        return true;
    }

    async function saveMoreSubSection(step, onSuccess, options = {}) {
        const typeMap = { 1: 'contact', 6: 'medical', 7: 'references' };
        const subsection = typeMap[step];
        if (!subsection) {
            if (onSuccess) onSuccess();
            return;
        }

        if (subsection === 'contact' && !validateMoreContactSubsection()) {
            return;
        }
        if (subsection === 'medical' && !validateMoreMedicalSubsection()) {
            return;
        }

        const employeeId = document.getElementById('saved_employee_id')?.value;
        if (!employeeId) return showError('Save general information first.');

        const nextBtn = document.getElementById('nextBtn');
        const originalText = nextBtn ? nextBtn.textContent : '';
        const skipButtonState = !!options.skipButtonState;

        if (!skipButtonState && nextBtn) {
            nextBtn.disabled = true;
            nextBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>Saving...';
        }

        if (subsection === 'contact') {
            const contactPane = document.getElementById('moreStepPane1');
            const residencePhoneInput = document.getElementById('moreContactResidencePhoneInput');
            const emergencyContactInput = document.getElementById('moreContactEmergencyContactInput');
            const cellNoInput = document.getElementById('moreContactCellNoInput');
            const emailInput = document.getElementById('moreContactEmailInput');
            const presentAddressInput = document.getElementById('moreContactPresentAddressInput');
            const permanentAddressInput = document.getElementById('moreContactPermanentAddressInput');

            const residencePhone = String(residencePhoneInput?.value ?? '').replace(/\D/g, '');
            const emergencyContact = String(emergencyContactInput?.value ?? '').replace(/\D/g, '');
            const cellNo = String(cellNoInput?.value ?? '').replace(/\D/g, '');
            const contactEmail = String(emailInput?.value ?? '').trim().toLowerCase();
            const presentAddress = String(presentAddressInput?.value ?? '').trim();
            const permanentAddress = String(permanentAddressInput?.value ?? '').trim();

            if (residencePhoneInput) residencePhoneInput.value = residencePhone;
            if (emergencyContactInput) emergencyContactInput.value = emergencyContact;
            if (cellNoInput) cellNoInput.value = cellNo;
            if (emailInput) emailInput.value = contactEmail;
            if (presentAddressInput) presentAddressInput.value = presentAddress;
            if (permanentAddressInput) permanentAddressInput.value = permanentAddress;

            const errors = {};
            const phoneRegex = /^[0-9]{11,15}$/;
            const residencePhoneRegex = /^[0-9]{7,15}$/;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            if (residencePhone && !residencePhoneRegex.test(residencePhone)) {
                errors.residence_phone = ['Residence phone must contain 7 to 15 digits.'];
            }
            if (emergencyContact && !phoneRegex.test(emergencyContact)) {
                errors.emergency_contact = ['Emergency contact must contain 11 to 15 digits.'];
            }
            if (!cellNo) {
                errors.cell_no = ['Cell number is required.'];
            } else if (!phoneRegex.test(cellNo)) {
                errors.cell_no = ['Cell number must contain 11 to 15 digits.'];
            }

            if (!contactEmail) {
                errors.contact_email = ['Email is required.'];
            } else if (contactEmail.length > 255) {
                errors.contact_email = ['Email must not exceed 255 characters.'];
            } else if (!emailRegex.test(contactEmail)) {
                errors.contact_email = ['Enter a valid email address.'];
            }

            if (!presentAddress) {
                errors.present_address = ['Present address is required.'];
            } else if (presentAddress.length < 10) {
                errors.present_address = ['Present address must be at least 10 characters.'];
            } else if (presentAddress.length > 1000) {
                errors.present_address = ['Present address must not exceed 1000 characters.'];
            }

            if (!permanentAddress) {
                errors.permanent_address = ['Permanent address is required.'];
            } else if (permanentAddress.length < 10) {
                errors.permanent_address = ['Permanent address must be at least 10 characters.'];
            } else if (permanentAddress.length > 1000) {
                errors.permanent_address = ['Permanent address must not exceed 1000 characters.'];
            }

            if (Object.keys(errors).length > 0) {
                showFieldErrors(errors, contactPane || document);
                if (!skipButtonState && nextBtn) {
                    nextBtn.disabled = false;
                    nextBtn.textContent = originalText;
                }
                return;
            }
        }

        const form = document.getElementById('employeeForm');
        const formData = new FormData(form);
        formData.append('subsection', subsection);
        formData.append('employee_id', employeeId);

        const subsectionErrors = validateMoreSubsectionData(subsection, formData);
        if (Object.keys(subsectionErrors).length > 0) {
            showFieldErrors(subsectionErrors);
            if (!skipButtonState && nextBtn) {
                nextBtn.disabled = false;
                nextBtn.textContent = originalText;
            }
            return;
        }

        try {
            const response = await fetch('/admin/employees/save-subsection', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: formData
            });

            const res = await response.json();
            if (response.status === 422) {
                showFieldErrors(res.errors);
                if (subsection === 'references' && typeof window.setMoreSubStep === 'function' && res.errors) {
                    const refKeys = Object.keys(res.errors).filter((k) => k.startsWith('ref'));
                    if (refKeys.length) {
                        window.setMoreSubStep(7);
                    }
                }
                let errorMsg = 'Please check the highlighted fields.';
                if (res.errors) {
                    const firstError = Object.values(res.errors)[0];
                    if (Array.isArray(firstError) && firstError.length > 0) {
                        errorMsg = firstError[0];
                    }
                }

                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'error',
                    title: '<span style="color: #721c24; font-size: 15px; font-weight: 600;">Action Required</span>',
                                        html: `<span style="color: #721c24; font-size: 13px;">${errorMsg}</span>`,
                    showConfirmButton: false,
                    timer: 5000,
                    timerProgressBar: true,
                    background: '#f8d7da',
                    iconColor: '#dc3545',
                    didOpen: (toast) => {
                        toast.style.border = '1px solid #f5c6cb';
                        toast.style.borderRadius = '12px';
                    }
                });
            } else if (res.success) {
                showToast(`${subsection.charAt(0).toUpperCase() + subsection.slice(1)} information saved successfully`);

                // Update medical document UI if a file was uploaded
                if (subsection === 'medical') {
                    const fileInput = document.getElementById('moreMedicalFileInput');
                    if (fileInput && fileInput.files.length > 0 && res.attachment_url) {
                        const uploadContainer = document.getElementById('moreMedicalUploadContainer');
                        const viewContainer = document.getElementById('moreMedicalViewContainer');
                        const viewLink = document.getElementById('moreMedicalDocumentLink');
                        const filenameEl = document.getElementById('moreMedicalFilename');
                        if (viewLink) viewLink.href = res.attachment_url;
                        if (filenameEl) filenameEl.textContent = fileInput.files[0].name;
                        if (viewContainer) {
                            viewContainer.classList.remove('d-none');
                            if (res.attachment_id) viewContainer.setAttribute('data-attachment-id', res.attachment_id);
                        }
                        if (uploadContainer) uploadContainer.classList.add('d-none');
                        fileInput.value = '';
                    }
                }

                if (onSuccess) onSuccess();
            } else {
                showError(res.message);
            }
        } catch (e) { showError('Network error'); }
        finally {
            if (!skipButtonState && nextBtn) {
                nextBtn.disabled = false;
                nextBtn.textContent = originalText;
            }
        }
    }

    function isValidEmail(value) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(String(value || '').trim());
    }

    function isValidContact(value) {
        return /^\d{11,15}$/.test(String(value || '').trim());
    }

    function isValidResidencePhone(value) {
        return /^\d{7,15}$/.test(String(value || '').trim());
    }

    function isValidDate(value) {
        if (!value) return false;
        const d = new Date(`${value}T00:00:00`);
        return !Number.isNaN(d.getTime());
    }

    function dateValue(value) {
        if (!isValidDate(value)) return null;
        const d = new Date(`${value}T00:00:00`);
        d.setHours(0, 0, 0, 0);
        return d;
    }

    function validateMoreSubsectionData(subsection, formData) {
        const errors = {};
        const addErr = (key, msg) => {
            if (!errors[key]) errors[key] = [];
            errors[key].push(msg);
        };
        const get = (key) => String(formData.get(key) || '').trim();

        if (subsection === 'contact') {
            const residencePhone = get('residence_phone');
            const emergencyContact = get('emergency_contact');
            const cellNo = get('cell_no');
            const contactEmail = get('contact_email');
            const presentAddress = get('present_address');
            const permanentAddress = get('permanent_address');

            if (residencePhone && !isValidResidencePhone(residencePhone)) addErr('residence_phone', 'Residence phone must be 7 to 15 digits.');
            if (emergencyContact && !isValidContact(emergencyContact)) addErr('emergency_contact', 'Emergency contact must be 11 to 15 digits.');
            if (!cellNo) addErr('cell_no', 'Cell number is required.');
            else if (!isValidContact(cellNo)) addErr('cell_no', 'Cell number must be 11 to 15 digits.');

            if (!contactEmail) addErr('contact_email', 'Email is required.');
            else if (!isValidEmail(contactEmail)) addErr('contact_email', 'Please enter a valid email address.');
            else if (contactEmail.length > 255) addErr('contact_email', 'Email must not exceed 255 characters.');

            if (!presentAddress) addErr('present_address', 'Present address is required.');
            else if (presentAddress.length < 10) addErr('present_address', 'Present address must be at least 10 characters.');
            else if (presentAddress.length > 1000) addErr('present_address', 'Present address must not exceed 1000 characters.');

            if (!permanentAddress) addErr('permanent_address', 'Permanent address is required.');
            else if (permanentAddress.length < 10) addErr('permanent_address', 'Permanent address must be at least 10 characters.');
            else if (permanentAddress.length > 1000) addErr('permanent_address', 'Permanent address must not exceed 1000 characters.');
        }

        if (subsection === 'medical') {
            const fitnessDate = get('last_fitness_test_date');
            const fitnessResult = get('last_fitness_test_result');
            const lastFitness = get('last_fitness_test');
            const hasDisability = get('has_disability');
            const bloodGroup = get('blood_group');
            const disabilityType = get('disability_type');
            const disabilityDescription = get('disability_description');
            const todaySub = new Date();
            todaySub.setHours(0, 0, 0, 0);

            if (lastFitness.length > 500) addErr('last_fitness_test', 'Last fitness test notes must not exceed 500 characters.');
            if (fitnessDate) {
                if (!isValidDate(fitnessDate)) addErr('last_fitness_test_date', 'Enter a valid fitness test date.');
                else if (dateValue(fitnessDate) > todaySub) addErr('last_fitness_test_date', 'Fitness test date cannot be in the future.');
            }
            if (fitnessResult && !['Positive', 'Negative'].includes(fitnessResult)) {
                addErr('last_fitness_test_result', 'Select Positive or Negative.');
            }
            if ((fitnessDate && !fitnessResult) || (!fitnessDate && fitnessResult)) {
                if (!fitnessDate) addErr('last_fitness_test_date', 'Select the fitness test date when a result is chosen.');
                if (!fitnessResult) addErr('last_fitness_test_result', 'Select the fitness test result when a date is entered.');
            }
            if (!['yes', 'no'].includes(hasDisability)) addErr('has_disability', 'Please select disability status.');
            if (bloodGroup && !/^(A|B|AB|O)[+-]$/.test(bloodGroup)) addErr('blood_group', 'Blood group format is invalid.');
            if (hasDisability === 'yes' && !disabilityType) addErr('disability_type', 'Disability type is required when disability is Yes.');
            if (disabilityType && disabilityType.length > 100) addErr('disability_type', 'Disability type must not exceed 100 characters.');
            if (disabilityType === 'Other' && !disabilityDescription) {
                addErr('disability_description', 'Please specify disability details.');
            }
            if (disabilityDescription.length > 1000) addErr('disability_description', 'Disability description must not exceed 1000 characters.');

            const hasChronic = get('has_chronic_disease');
            const chronicDescription = get('chronic_disease_description');
            if (!['yes', 'no'].includes(hasChronic)) addErr('has_chronic_disease', 'Please select chronic disease status.');
            if (hasChronic === 'yes' && !chronicDescription) addErr('chronic_disease_description', 'Please specify the chronic disease.');
            if (chronicDescription.length > 1000) addErr('chronic_disease_description', 'Chronic disease description must not exceed 1000 characters.');
        }

        if (subsection === 'references') {
            const refs = [
                { p: 'ref1', label: 'Reference 1' },
                { p: 'ref2', label: 'Reference 2' },
            ];
            const nameRegex = /^[A-Za-z]+(?:[A-Za-z\s.\-'_]*[A-Za-z])?$/;
            const allowedRel = ['Family', 'Friend', 'Colleague', 'Academic', 'Professional', 'Other'];
            refs.forEach(({ p, label }) => {
                const name = get(`${p}_name`);
                const designation = get(`${p}_designation`);
                const org = get(`${p}_organization`);
                const contact = get(`${p}_contact`);
                const relation = get(`${p}_relationship`);

                if (name) {
                    if (name.length > 50) addErr(`${p}_name`, `${label} name must not exceed 50 characters.`);
                    else if (!nameRegex.test(name)) addErr(`${p}_name`, `${label} name format is invalid.`);
                }
                if (designation.length > 50) addErr(`${p}_designation`, `${label} designation must not exceed 50 characters.`);
                if (org.length > 100) addErr(`${p}_organization`, `${label} organization must not exceed 100 characters.`);
                if (contact && !isValidContact(contact)) addErr(`${p}_contact`, `${label} contact must be 11 to 15 digits.`);
                if (relation && !allowedRel.includes(relation)) addErr(`${p}_relationship`, `${label} relationship is invalid.`);
            });
        }

        return errors;
    }

    function rowHasAnyData(rowElement) {
        if (!rowElement) return false;
        const fields = rowElement.querySelectorAll('input, select, textarea');
        for (const input of fields) {
            const name = input.getAttribute('name');
            if (!name || name === 'family_nok_selector') continue;
            if (input.type === 'checkbox' || input.type === 'radio') {
                if (input.checked) return true;
                continue;
            }
            if (String(input.value ?? '').trim() !== '') return true;
        }
        return false;
    }

    async function autoSaveMoreDynamicRows(moreStep) {
        const stepTypeMap = { 2: 'family', 3: 'academic', 4: 'certificate', 5: 'employment' };
        const containerIdMap = {
            family: 'moreFamilyMembersContainer',
            academic: 'moreAcademicRecordsContainer',
            certificate: 'moreCertificateRecordsContainer',
            employment: 'moreEmploymentRecordsContainer',
        };
        const type = stepTypeMap[moreStep];
        if (!type) return true;
        const container = document.getElementById(containerIdMap[type]);
        if (!container) return true;

        const rows = Array.from(container.querySelectorAll(`[data-${type}-row]`));
        let savedCount = 0;
        for (const row of rows) {
            const isPreview = row.classList.contains('preview-mode');
            if (isPreview) continue;
            if (!rowHasAnyData(row)) continue;
            const ok = await saveSubsectionRow(type, row, { silentSuccess: true });
            if (!ok) {
                return false;
            }
            savedCount += 1;
        }

        if (savedCount > 0) {
            showToast(`${savedCount} ${type} record${savedCount > 1 ? 's' : ''} saved`);
        }
        return true;
    }

    document.getElementById('prevBtn').addEventListener('click', function () {
        if (isStepUnsaved()) {
            showUnsavedWarning();
            return;
        }
        if (currentStep === 6) {
            const isFirstMoreStep = typeof window.isFirstMoreStep === 'function' ? window.isFirstMoreStep() : true;
            if (!isFirstMoreStep) {
                if (typeof window.prevMoreSubStep === 'function') {
                    window.prevMoreSubStep();
                    syncStepUi();
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                    return;
                }
            }
        }

        if (currentStep > 1) {
            currentStep = getPrevStepBefore(currentStep);
            syncStepUi();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    });

    function formatCNIC(input) {
        if (!input) return;
        let val = input.value.replace(/\D/g, '');
        if (val.length > 13) val = val.substring(0, 13);
        let formatted = '';
        if (val.length > 0) {
            formatted = val.substring(0, 5);
            if (val.length > 5) {
                formatted += '-' + val.substring(5, 12);
                if (val.length > 12) formatted += '-' + val.substring(12, 13);
            }
        }
        input.value = formatted;
    }

    window.formatCNIC = formatCNIC;

    function initCnicMaskDisplayFromValues() {
        document.querySelectorAll('input.cnic-mask').forEach(function (el) {
            if (el.value && /\d/.test(el.value)) {
                formatCNIC(el);
            }
        });
    }

    function scheduleInitCnicMaskDisplay() {
        const run = function () {
            setTimeout(initCnicMaskDisplayFromValues, 0);
        };
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', run);
        } else {
            run();
        }
    }
    scheduleInitCnicMaskDisplay();

    document.addEventListener('input', function (e) {
        if (e.target.classList.contains('cnic-mask')) {
            formatCNIC(e.target);
        }
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'SELECT' || e.target.tagName === 'TEXTAREA') {
            clearFieldStatus(e.target);
        }
    });

    // Profile Photo Cropper
    window.openCropper = function(inputFile) {
        if (!inputFile.files || !inputFile.files[0]) return;

        const file = inputFile.files[0];
        const allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'svg'];
        const extension = file.name.split('.').pop().toLowerCase();

        if (!allowedExtensions.includes(extension)) {
            showError('Only JPG, PNG, GIF, and SVG files are allowed.', 'Invalid File Type');
            inputFile.value = '';
            return;
        }

        const maxSize = 20 * 1024 * 1024; // 20MB
        if (file.size > maxSize) {
            showError('Maximum allowed file size is 20MB.', 'File Too Large');
            inputFile.value = '';
            return;
        }

        window.originalFileName = file.name;
        const reader = new FileReader();

        reader.onload = function(e) {
            const cropperImage = document.getElementById('cropperImage');
            if (cropperImage) {
                cropperImage.src = e.target.result;

                const modalEl = document.getElementById('cropperModal');
                if (modalEl) {
                    const modal = new bootstrap.Modal(modalEl);
                    modal.show();

                    const onShown = function() {
                        if (window.cropper) window.cropper.destroy();
                        window.cropper = new Cropper(cropperImage, {
                            aspectRatio: 1,
                            viewMode: 1,
                            dragMode: 'move',
                            autoCropArea: 0.8,
                            restore: false,
                            guides: true,
                            center: true,
                            highlight: false,
                            cropBoxMovable: true,
                            cropBoxResizable: true,
                            toggleDragModeOnDblclick: false,
                        });
                        modalEl.removeEventListener('shown.bs.modal', onShown);
                    };
                    modalEl.addEventListener('shown.bs.modal', onShown);
                }
            }
        };
        reader.readAsDataURL(file);
    };

    window.cancelCrop = function() {
        if (window.cropper) {
            window.cropper.destroy();
            window.cropper = null;
        }
        if (!window.croppedImageBlob) {
            const inp = document.getElementById('profilePhotoInput');
            if (inp) inp.value = '';
        }
    };

    const cropBtn = document.getElementById('cropBtn');
    if (cropBtn) {
        cropBtn.addEventListener('click', function() {
            if (!window.cropper) return;

            const canvas = window.cropper.getCroppedCanvas({
                width: 500,
                height: 500,
                imageSmoothingEnabled: true,
                imageSmoothingQuality: 'high',
            });

            canvas.toBlob(function(blob) {
                window.croppedImageBlob = blob;
                window.profilePhotoUploadName = 'profile-photo.jpg';

                // Update preview
                const avatarPreviewImage = document.getElementById('avatarPreviewImage');
                const avatarPlaceholderIcon = document.getElementById('avatarPlaceholderIcon');

                if (avatarPreviewImage && avatarPlaceholderIcon) {
                    avatarPreviewImage.src = URL.createObjectURL(blob);
                    avatarPreviewImage.classList.remove('d-none');
                    avatarPlaceholderIcon.classList.add('d-none');
                }

                const removeBtn = document.getElementById('removePhotoBtn');
                if (removeBtn) {
                    removeBtn.classList.remove('d-none');
                }

                // Close modal
                const modalEl = document.getElementById('cropperModal');
                const modalInstance = bootstrap.Modal.getInstance(modalEl);
                if (modalInstance) modalInstance.hide();

                window.cropper.destroy();
                window.cropper = null;

                // If Employee ID exists, save the photo instantly
                const savedIdInput = document.getElementById('saved_employee_id');
                const employeeId = savedIdInput ? savedIdInput.value : '';

                if (employeeId) {
                    const formData = new FormData();
                    formData.append('employee_id', employeeId);
                    formData.append('subsection', 'photo');
                    formData.append('profile_photo', blob, window.profilePhotoUploadName || 'profile-photo.jpg');
                    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

                    fetch('/admin/employees/save-subsection', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: data.message || 'Profile photo saved successfully.',
                                showConfirmButton: false,
                                timer: 3000,
                                timerProgressBar: true
                            });
                        } else {
                            Swal.fire({
                                toast: true, position: 'top-end', icon: 'error',
                                title: data.message || 'Failed to save photo.',
                                showConfirmButton: false, timer: 3000, timerProgressBar: true
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error saving photo:', error);
                    });
                }
            }, 'image/jpeg', 0.9);
        });
    }

    // Dependent Fields Logic (NOK Relation, etc.)
    const giNokRelationSelect = document.getElementById('giNokRelationSelect');
    const giNokSpecifyRelationField = document.getElementById('giNokSpecifyRelationField');

    function syncNokSpecifyRelationField() {
        if (!giNokRelationSelect || !giNokSpecifyRelationField) return;
        const isOtherSelected = giNokRelationSelect.value === 'Other';
        giNokSpecifyRelationField.classList.toggle('d-none', !isOtherSelected);
    }

    if (giNokRelationSelect) {
        giNokRelationSelect.addEventListener('change', syncNokSpecifyRelationField);
        syncNokSpecifyRelationField();
    }

    // Employment Category Toggles
    function toggleEmploymentCategoryFields() {
        const internFields = document.getElementById('employmentDetailsInternFields');
        const contractualFields = document.getElementById('employmentDetailsContractualFields');
        const engagementFields = document.getElementById('employmentDetailsEngagementFields');
        
        const catIntern = document.getElementById('employmentDetailsCategoryIntern');
        const catContractual = document.getElementById('employmentDetailsCategoryContractual');
        const catEngagement = document.getElementById('employmentDetailsCategoryEngagement');

        if (internFields) internFields.classList.toggle('d-none', !(catIntern && catIntern.checked));
        if (contractualFields) contractualFields.classList.toggle('d-none', !(catContractual && catContractual.checked));
        if (engagementFields) engagementFields.classList.toggle('d-none', !(catEngagement && catEngagement.checked));
    }

    document.querySelectorAll('input[name="employment_category"]').forEach(input => {
        input.addEventListener('change', toggleEmploymentCategoryFields);
    });
    toggleEmploymentCategoryFields();

    // Inner Employment Toggles (for Employee Resource Type)
    function toggleEmployeeInnerFields() {
        const engagementModeInput = document.getElementById('employmentDetailsEngagementModeInput');
        const contractTypeField = document.getElementById('employmentDetailsEmployeeContractTypeField');
        const contractTypeInput = document.getElementById('employmentDetailsEmployeeContractTypeInput');
        const contractDatesField = document.getElementById('employmentDetailsEmployeeContractDatesField');
        
        if (engagementModeInput && contractTypeField) {
            const isContractual = engagementModeInput.value === 'contractual';
            contractTypeField.classList.toggle('d-none', !isContractual);

            // If employment type switches away from contractual, hide dependent date fields too.
            if (!isContractual && contractDatesField) {
                contractDatesField.classList.add('d-none');
            } else if (isContractual && contractTypeInput && contractDatesField) {
                const isTimeBound = contractTypeInput.value === 'time_bound';
                contractDatesField.classList.toggle('d-none', !isTimeBound);
            }
        }
    }

    function toggleContractualInnerFields() {
        const contractTypeInput = document.getElementById('employmentDetailsEmployeeContractTypeInput');
        const contractDatesField = document.getElementById('employmentDetailsEmployeeContractDatesField');
        
        if (contractTypeInput && contractDatesField) {
            const isTimeBound = contractTypeInput.value === 'time_bound';
            contractDatesField.classList.toggle('d-none', !isTimeBound);
        }
    }

    const mEngagementModeInput = document.getElementById('employmentDetailsEngagementModeInput');
    if (mEngagementModeInput) {
        mEngagementModeInput.addEventListener('change', toggleEmployeeInnerFields);
        toggleEmployeeInnerFields();
    }

    const mContractTypeInput = document.getElementById('employmentDetailsEmployeeContractTypeInput');
    if (mContractTypeInput) {
        mContractTypeInput.addEventListener('change', toggleContractualInnerFields);
        toggleContractualInnerFields();
    }

    // â”€â”€â”€ Work Arrangement Toggles â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    function getScheduleSource() {
        // Look up live at call time â€” avoids temporal dead zone with orgSelect/sbuSelect consts below
        const oSel = document.getElementById('employmentOrganizationSelect');
        const sSel = document.getElementById('employmentSbuSelect');
        const orgs = window.orgsData || [];

        const oId = oSel ? oSel.value : null;
        const sId = sSel ? sSel.value : null;
        if (!oId) return null;
        const org = orgs.find(o => o.id == oId);
        if (!org) return null;
        if (sId && org.sbus) {
            const sbu = org.sbus.find(s => s.id == sId);
            if (sbu) return { label: sbu.name, initial: sbu.name.charAt(0).toUpperCase(), data: sbu };
        }
        return { label: org.name, initial: org.name.charAt(0).toUpperCase(), data: org };
    }

    function formatDaysList(days) {
        if (!days || !days.length) return '- - -';
        const map = { Mon: 'Mon', Tue: 'Tue', Wed: 'Wed', Thu: 'Thu', Fri: 'Fri', Sat: 'Sat', Sun: 'Sun',
                      monday:'Mon', tuesday:'Tue', wednesday:'Wed', thursday:'Thu', friday:'Fri', saturday:'Sat', sunday:'Sun' };
        return days.map(d => map[d] || d).join(', ');
    }

    function formatTime(t) {
        if (!t) return '- - -';
        // HH:MM or HH:MM:SS -> 12-hour
        const parts = t.split(':');
        let h = parseInt(parts[0], 10), m = parseInt(parts[1] || '0', 10);
        const ampm = h >= 12 ? 'PM' : 'AM';
        h = h % 12 || 12;
        return `${h}:${String(m).padStart(2,'0')} ${ampm}`;
    }

    function updateDefaultScheduleCard() {
        const src = getScheduleSource();
        const orgInitial  = document.getElementById('employmentWorkArrangementOrgInitial');
        const orgName     = document.getElementById('employmentWorkArrangementOrgName');
        const wkDays      = document.getElementById('employmentDefaultWorkingDays');
        const wkTime      = document.getElementById('employmentDefaultWorkingTime');
        const graceEl     = document.getElementById('employmentDefaultGracePeriod');

        if (!src) {
            if (orgInitial) orgInitial.textContent = '-';
            if (orgName)    orgName.textContent    = '-';
            if (wkDays)     wkDays.textContent     = '- - -';
            if (wkTime)     wkTime.textContent     = '- - -';
            if (graceEl)    graceEl.textContent    = '-';
            return;
        }

        const d = src.data;
        const graceMin = d.opening_grace_period != null && d.opening_grace_period !== ''
            ? d.opening_grace_period
            : d.closing_grace_period;
        if (orgInitial) orgInitial.textContent = src.initial;
        if (orgName)    orgName.textContent    = src.label;
        if (wkDays)     wkDays.textContent     = formatDaysList(d.working_days);
        if (wkTime)     wkTime.textContent     = (d.working_start_time && d.working_end_time)
                                                    ? `${formatTime(d.working_start_time)} â€“ ${formatTime(d.working_end_time)}`
                                                    : '- - -';
        if (graceEl)    graceEl.textContent    = graceMin != null && graceMin !== '' ? `${graceMin} min` : '-';
    }

    function toggleWorkArrangementFields() {
        const active = document.querySelector('input[name="engagement_mode"]:checked');
        const mode = active ? active.value : null;

        const standardFields = document.getElementById('employmentWorkArrangementStandardFields');
        const defaultCard    = document.getElementById('employmentWorkArrangementDefaultCardWrap');
        const customFields   = document.getElementById('employmentWorkArrangementCustomFields');
        const hybridFields   = document.getElementById('employmentWorkArrangementHybridFields');

        // Hide everything first
        if (standardFields) standardFields.classList.add('d-none');
        if (defaultCard)    defaultCard.classList.add('d-none');
        if (customFields)   customFields.classList.add('d-none');
        if (hybridFields)   hybridFields.classList.add('d-none');

        if (mode === 'standard') {
            if (standardFields) standardFields.classList.remove('d-none');
            // Also trigger inner standard-type toggle
            toggleStandardTypeFields();
        } else if (mode === 'hybrid') {
            if (hybridFields) hybridFields.classList.remove('d-none');
        }
        // shift_based and remote: nothing extra to show
    }

    function toggleStandardTypeFields() {
        const active = document.querySelector('input[name="standard_schedule_mode"]:checked');
        const schedMode = active ? active.value : null;

        const defaultCard  = document.getElementById('employmentWorkArrangementDefaultCardWrap');
        const customFields = document.getElementById('employmentWorkArrangementCustomFields');

        if (defaultCard)  defaultCard.classList.toggle('d-none', schedMode !== 'default');
        if (customFields) customFields.classList.toggle('d-none', schedMode !== 'custom');

        if (schedMode === 'default') {
            updateDefaultScheduleCard();
        }
    }

    // Bind engagement_mode change
    document.querySelectorAll('input[name="engagement_mode"]').forEach(input => {
        input.addEventListener('change', toggleWorkArrangementFields);
    });

    // Bind standard_schedule_mode change
    document.querySelectorAll('input[name="standard_schedule_mode"]').forEach(input => {
        input.addEventListener('change', toggleStandardTypeFields);
    });

    // Re-run default card update when org or sbu changes
    if (orgSelect) orgSelect.addEventListener('change', updateDefaultScheduleCard);
    if (sbuSelect) sbuSelect.addEventListener('change', updateDefaultScheduleCard);

    // Init on page load
    toggleWorkArrangementFields();

    // â”€â”€â”€ End Work Arrangement Toggles â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    // Location Dependent Selects (Nationality -> Province -> District)
    function resetLocationSelect(select, placeholderText, disabled) {
        if (!select) return;
        const text = placeholderText || (select.options[0] ? select.options[0].text : 'Select');
        if (!select.options.length) {
            select.add(new Option(text, ''));
        }
        select.selectedIndex = 0;
        select.value = '';
        while (select.options.length > 1) {
            select.remove(1);
        }
        select.options[0].text = text;
        select.disabled = !!disabled;
    }

    async function loadLocationData(select, url, currentValue = null) {
        if (!select) return;

        const originalText = select.options[0] ? select.options[0].text : 'Select';
        select.options[0].text = 'Loading...';
        select.disabled = true;

        try {
            const response = await fetch(url);
            if (!response.ok) {
                throw new Error(`Request failed: ${response.status}`);
            }
            const data = await response.json();
            const items = Array.isArray(data) ? data : [];

            while (select.options.length > 1) {
                select.remove(1);
            }

            items.forEach(item => {
                const option = new Option(item.name, item.name);
                select.add(option);
            });

            select.options[0].text = originalText;
            
            const editBtn = document.getElementById('editBtn');
            const inViewMode = window.isEditMode && editBtn && editBtn.innerText.trim() === 'Edit';
            if (!inViewMode) {
                select.disabled = false;
            }

            const rawVal = currentValue != null && String(currentValue).length
                ? currentValue
                : select.getAttribute('data-current-value');
            const valToSelect = rawVal != null ? String(rawVal).trim() : '';
            if (valToSelect) {
                select.value = valToSelect;
                if (select.value !== valToSelect) {
                    const opt = Array.prototype.find.call(select.options, function(o) {
                        return o.value && String(o.value).trim() === valToSelect;
                    });
                    if (opt) {
                        select.value = opt.value;
                    }
                }
                if (select.value && String(select.value).trim() === valToSelect) {
                    select.dispatchEvent(new Event('change'));
                }
            }
        } catch (error) {
            console.error('Failed to load location data:', error);
            select.options[0].text = 'Unable to load';
            const editBtn = document.getElementById('editBtn');
            const inViewMode = window.isEditMode && editBtn && editBtn.innerText.trim() === 'Edit';
            if (!inViewMode) {
                select.disabled = false;
            }
        }
    }

    function initLocationSelectors() {
        const nationalitySelect = document.getElementById('giNationalityInput');
        const provinceSelect = document.getElementById('giProvinceSelect');
        const districtSelect = document.getElementById('giDistrictSelect');
        const spouseNationalitySelect = document.getElementById('giSpouseNationalityInput');

        if (provinceSelect && provinceSelect.options[0]) {
            resetLocationSelect(provinceSelect, provinceSelect.options[0].text, true);
        }
        if (districtSelect && districtSelect.options[0]) {
            resetLocationSelect(districtSelect, districtSelect.options[0].text, true);
        }

        if (nationalitySelect) {
            loadLocationData(nationalitySelect, '/admin/locations/countries');
        }

        if (spouseNationalitySelect) {
            loadLocationData(spouseNationalitySelect, '/admin/locations/countries');
        }

        if (nationalitySelect) {
            nationalitySelect.addEventListener('change', function(e) {
                const countryName = this.value;
                if (provinceSelect) {
                    if (e.isTrusted) {
                        provinceSelect.removeAttribute('data-current-value');
                        if (districtSelect) {
                            districtSelect.removeAttribute('data-current-value');
                        }
                    }
                    resetLocationSelect(provinceSelect, 'Select province', true);
                    if (districtSelect) {
                        resetLocationSelect(districtSelect, 'Select district', true);
                    }

                    if (countryName) {
                        provinceSelect.disabled = false;
                        loadLocationData(provinceSelect, `/admin/locations/provinces/${encodeURIComponent(countryName)}`);
                    } else {
                        resetLocationSelect(provinceSelect, 'Select province', true);
                    }
                }
            });
        }

        if (provinceSelect) {
            provinceSelect.addEventListener('change', function(e) {
                const provinceName = this.value;
                const countryName = nationalitySelect ? nationalitySelect.value : null;

                if (districtSelect) {
                    if (e.isTrusted) {
                        districtSelect.removeAttribute('data-current-value');
                    }
                    resetLocationSelect(districtSelect, 'Select district', true);
                    if (provinceName && countryName) {
                        districtSelect.disabled = false;
                        loadLocationData(districtSelect, `/admin/locations/districts/${encodeURIComponent(countryName)}/${encodeURIComponent(provinceName)}`);
                    } else {
                        resetLocationSelect(districtSelect, 'Select district', true);
                    }
                }
            });
        }
    }

    function toggleEmploymentTerminationFields() {
        const sel = document.getElementById('employmentStatusInput');
        const terminationRow = document.getElementById('employmentTerminationFieldsRow');
        const suspensionRow = document.getElementById('employmentSuspensionFieldsRow');
        if (!sel) return;
        
        if (terminationRow) {
            terminationRow.classList.toggle('d-none', sel.value !== 'Terminated');
        }
        if (suspensionRow) {
            suspensionRow.classList.toggle('d-none', sel.value !== 'Suspend');
        }
    }

    const employmentStatusInputEl = document.getElementById('employmentStatusInput');
    if (employmentStatusInputEl) {
        employmentStatusInputEl.addEventListener('change', toggleEmploymentTerminationFields);
        toggleEmploymentTerminationFields();
    }

    // Initialize UI
    initLocationSelectors();
    syncStepUi();
    syncConditionalVisibility();
    togglePoliceVerificationFields();

    // Dynamic Sidebar Updates
    function updateSidebarSummary() {
        if (!document.getElementById('summaryName')) {
            return;
        }
        const nameInput = document.querySelector('input[name="full_name"]');
        if (nameInput) document.getElementById('summaryName').textContent = nameInput.value || 'Not provided';
        const sidebarName = document.getElementById('sidebarEmployeeName');
        if (nameInput && sidebarName) sidebarName.textContent = nameInput.value || 'New Employee';

        const cnicInput = document.querySelector('input[name="cnic"]');
        const summaryCnic = document.getElementById('summaryCnic');
        if (cnicInput && summaryCnic) summaryCnic.textContent = cnicInput.value || 'Not provided';

        const genderSelect = document.querySelector('select[name="gender"]');
        const summaryGender = document.getElementById('summaryGender');
        if (genderSelect && summaryGender) summaryGender.textContent = genderSelect.value || 'Not selected';

        const religionSelect = document.querySelector('select[name="religion"]');
        const summaryReligion = document.getElementById('summaryReligion');
        if (religionSelect && summaryReligion) summaryReligion.textContent = religionSelect.value || 'Not selected';

        const nationalitySelect = document.querySelector('select[name="nationality"]');
        const summaryNationality = document.getElementById('summaryNationality');
        if (nationalitySelect && summaryNationality) {
            const label = nationalitySelect.options[nationalitySelect.selectedIndex]?.text;
            summaryNationality.textContent = (label && label !== 'Select Nationality') ? label : 'Not selected';
        }
    }

    document.querySelectorAll('input[name="full_name"], input[name="cnic"]').forEach(el => {
        el.addEventListener('input', updateSidebarSummary);
    });
    document.querySelectorAll('select[name="gender"], select[name="religion"], select[name="nationality"]').forEach(el => {
        el.addEventListener('change', updateSidebarSummary);
    });

    // Remove Photo Logic
    const removePhotoBtn = document.getElementById('removePhotoBtn');
    if (removePhotoBtn) {
        removePhotoBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            
            showConfirm('Are you sure you want to remove the profile photo?', 'Remove Photo')
            .then((result) => {
                if (result.isConfirmed) {
                    const savedIdInput = document.getElementById('saved_employee_id');
                    const employeeId = savedIdInput ? savedIdInput.value : '';

                    if (employeeId) {
                        // Delete from DB
                        fetch('/admin/employees/delete-photo', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ id: employeeId })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                clearPreview();
                                Swal.fire({
                                    toast: true, position: 'top-end', icon: 'success',
                                    title: 'Photo removed successfully.',
                                    showConfirmButton: false, timer: 3000, timerProgressBar: true
                                });
                            } else {
                                showError(data.message || 'Failed to remove photo.');
                            }
                        })
                        .catch(err => {
                            console.error(err);
                            showError('Error deleting photo');
                        });
                    } else {
                        // Just clear local
                        window.croppedImageBlob = null;
                        clearPreview();
                    }
                }
            });
        });
    }

    function clearPreview() {
        const avatarPreviewImage = document.getElementById('avatarPreviewImage');
        const avatarPlaceholderIcon = document.getElementById('avatarPlaceholderIcon');
        const rBtn = document.getElementById('removePhotoBtn');
        const inp = document.getElementById('profilePhotoInput');
        
        if (avatarPreviewImage) {
            avatarPreviewImage.src = '';
            avatarPreviewImage.classList.add('d-none');
        }
        if (avatarPlaceholderIcon) avatarPlaceholderIcon.classList.remove('d-none');
        if (rBtn) rBtn.classList.add('d-none');
        if (inp) inp.value = '';
        window.profilePhotoUploadName = '';
    }

    // Organization -> SBU -> Roles dependent dropdowns


    function populateSbus(orgId, selectedSbuId = null) {
        if (!sbuSelect) return;
        
        sbuSelect.innerHTML = '<option value="" selected disabled>Select SBU</option>';

        if (!orgId) return;

        const org = orgsData.find(o => o.id == orgId);
        if (org && org.sbus) {
            org.sbus.forEach(sbu => {
                const opt = new Option(sbu.name, sbu.id);
                if (selectedSbuId == sbu.id) {
                    opt.selected = true;
                }
                sbuSelect.add(opt);
            });
        }
    }

    function formatDateForInput(dateObj) {
        const year = dateObj.getFullYear();
        const month = String(dateObj.getMonth() + 1).padStart(2, '0');
        const day = String(dateObj.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    function syncProbationStartFromJoinDate() {
        if (!joinDateInput || !probationStartMirrorInput) return;
        probationStartMirrorInput.value = joinDateInput.value || '';
    }

    function syncProbationEndMinDate() {
        if (!joinDateInput || !probationEndInput) return;
        if (!joinDateInput.value) {
            probationEndInput.removeAttribute('min');
            return;
        }

        const startDate = new Date(joinDateInput.value + 'T00:00:00');
        if (Number.isNaN(startDate.getTime())) {
            probationEndInput.removeAttribute('min');
            return;
        }

        startDate.setDate(startDate.getDate() + 1);
        const minEndDate = formatDateForInput(startDate);
        probationEndInput.min = minEndDate;

        if (probationEndInput.value) {
            const selectedEndDate = new Date(probationEndInput.value + 'T00:00:00');
            if (Number.isNaN(selectedEndDate.getTime()) || probationEndInput.value < minEndDate) {
                probationEndInput.value = '';
            }
        }
    }

    function syncProbationContractStartDate(forceFill = false) {
        if (!probationEndInput || !probationEndInput.value) return;

        const endDate = new Date(probationEndInput.value + 'T00:00:00');
        if (Number.isNaN(endDate.getTime())) return;
        endDate.setDate(endDate.getDate() + 1);
        const computedDate = formatDateForInput(endDate);

        const employeeContractualStartInput = document.getElementById('employmentDetailsEmployeeContractStartDateInput');
        if (employeeContractualStartInput && (forceFill || !employeeContractualStartInput.value)) {
            employeeContractualStartInput.value = computedDate;
        }
    }

    function populateFloors(orgId, sbuId) {
        if (!floorSelect) return;
        availableFloors = [];

        const org = orgsData.find(o => o.id == orgId);
        if (org && org.sbus) {
            const sbu = org.sbus.find(s => s.id == sbuId);
            if (sbu && Array.isArray(sbu.floors)) {
                availableFloors = sbu.floors;
            }
        }

        const currentSelectedIds = Array.from(floorSelect.options)
            .filter(o => o.selected)
            .map(o => parseInt(o.value, 10))
            .filter(Number.isFinite);

        const selectedFromDataAttr = floorSelect.dataset.selectedValues
            ? JSON.parse(floorSelect.dataset.selectedValues)
            : [];

        const selectedIds = (currentSelectedIds.length ? currentSelectedIds : selectedFromDataAttr)
            .map(v => parseInt(v, 10))
            .filter(Number.isFinite);

        floorSelect.innerHTML = '';

        if (availableFloors.length === 0) {
            if (floorHint) floorHint.style.display = 'block';
        } else {
            if (floorHint) floorHint.style.display = 'none';
            availableFloors
                .slice()
                .sort((a, b) => String(a.name || '').localeCompare(String(b.name || '')))
                .forEach(floor => {
                    const option = new Option(floor.name, floor.id);
                    option.selected = selectedIds.includes(parseInt(floor.id, 10));
                    floorSelect.add(option);
                });
        }
        renderFloorChips();
        buildFloorDropdownOptions();
    }

    function populateRoles(orgId, sbuId, selectedRoleId = null) {
        if (!roleSelect) return;

        roleSelect.innerHTML = '<option value="" selected disabled>Select role</option>';

        if (!orgId) return;

        let filteredRoles = rolesData.filter(role => role.organization_id == orgId);

        if (sbuId) {
            filteredRoles = filteredRoles.filter(role => {
                if (role.is_organization_level) return true;
                return role.sbu_id == sbuId || (role.linked_sbu_ids && role.linked_sbu_ids.includes(parseInt(sbuId)));
            });
        }

        function roleLevelSortKey(role) {
            const v = role.level;
            if (v === null || v === undefined || v === '') return Number.POSITIVE_INFINITY;
            const n = parseInt(v, 10);
            return Number.isFinite(n) ? n : Number.POSITIVE_INFINITY;
        }
        filteredRoles.sort((a, b) => {
            const da = roleLevelSortKey(a);
            const db = roleLevelSortKey(b);
            if (da !== db) return da - db;
            return String(a.name || '').localeCompare(String(b.name || ''));
        });

        filteredRoles.forEach(role => {
            const opt = new Option(role.name, role.id);
            if (selectedRoleId == role.id) {
                opt.selected = true;
            }
            roleSelect.add(opt);
        });
        syncGradeWithRole(roleSelect.value || '');
    }

    function syncGradeWithRole(roleId) {
        if (!gradeInput && !gradeDisplayInput) return;
        const role = rolesData.find(r => String(r.id) === String(roleId));
        const roleGrade = role && role.grade !== null && role.grade !== undefined && role.grade !== ''
            ? String(role.grade)
            : '';
        if (gradeInput) {
            gradeInput.value = roleGrade;
        }
        if (gradeDisplayInput) {
            gradeDisplayInput.value = roleGrade;
        }
    }

    if (orgSelect) {
        orgSelect.addEventListener('change', function () {
            const orgId = this.value;
            populateSbus(orgId);
            populateRoles(orgId, null);
            populateFloors(orgId, null);
            const sbuIdAfterOrg = sbuSelect && sbuSelect.value ? sbuSelect.value : null;
            populateDepartments(orgId, sbuIdAfterOrg);
        });
    }

    if (sbuSelect) {
        sbuSelect.addEventListener('change', function () {
            const orgId = orgSelect ? orgSelect.value : null;
            const sbuId = this.value;
            populateRoles(orgId, sbuId);
            populateDepartments(orgId, sbuId);
            populateFloors(orgId, sbuId);
        });
    }

    if (orgSelect && orgSelect.value) {
        const oId = orgSelect.value;
        const sId = initialSbu; 
        const rId = initialRole;

        populateSbus(oId, sId);
        populateRoles(oId, sId, rId);
        populateDepartments(oId, sId);
        populateFloors(oId, sId);
    }
    if (probationEndInput) {
        probationEndInput.addEventListener('change', function () {
            syncProbationContractStartDate(true);
        });
    }
    if (joinDateInput) {
        joinDateInput.addEventListener('change', function () {
            syncProbationStartFromJoinDate();
            syncProbationEndMinDate();
        });
    }
    syncProbationStartFromJoinDate();
    syncProbationEndMinDate();
    syncProbationContractStartDate(false);

    // --- Department Required based on Role Level ---
    const deptRequiredBadge  = document.getElementById('employmentDeptRequired');
    const deptBoxEl          = document.getElementById('employmentDeptBox');

    function updateDeptRequired(roleId) {
        if (!deptRequiredBadge) return;
        const role = rolesData.find(r => r.id == roleId);
        const level = role ? (role.level ?? null) : null;
        const isRequired = level !== null && parseInt(level) >= 4;

        if (isRequired) {
            deptRequiredBadge.className = 'text-danger fw-bold';
            deptRequiredBadge.textContent = '*';
            if (deptBoxEl) deptBoxEl.setAttribute('data-dept-required', '1');
        } else {
            deptRequiredBadge.className = 'text-muted fw-normal small';
            deptRequiredBadge.textContent = '(optional)';
            if (deptBoxEl) deptBoxEl.removeAttribute('data-dept-required');
        }
    }

    if (roleSelect) {
        roleSelect.addEventListener('change', function () {
            updateDeptRequired(this.value);
            syncGradeWithRole(this.value);
        });
        // Run on page load for edit mode
        if (roleSelect.value) {
            updateDeptRequired(roleSelect.value);
            syncGradeWithRole(roleSelect.value);
        } else {
            syncGradeWithRole('');
        }
    }

    // Custom Multi-Select logic for Departments


    function renderDeptChips() {
        if (!deptChips || !deptSelect) return;
        deptChips.innerHTML = '';
        
        const selectedOptions = Array.from(deptSelect.options).filter(o => o.selected && o.value);
        if (selectedOptions.length > 0) {
            if (deptPh) deptPh.style.display = 'none';
        } else {
            if (deptPh) deptPh.style.display = 'inline';
        }

        selectedOptions.forEach(opt => {
            const chip = document.createElement('div');
            chip.className = 'emp-dept-chip';
            chip.style.cssText = 'display:inline-block; padding: 2px 8px; border-radius: 4px; background: #e9ecef; margin: 2px; font-size: 13px;';
            chip.innerHTML = `${opt.text} <span class="emp-dept-chip-rm fw-bold text-danger ms-1" style="cursor:pointer;" data-id="${opt.value}">&times;</span>`;
            deptChips.appendChild(chip);
        });
    }

    function renderFloorChips() {
        if (!floorChips || !floorSelect) return;
        floorChips.innerHTML = '';

        const selectedOptions = Array.from(floorSelect.options).filter(o => o.selected && o.value);
        if (selectedOptions.length > 0) {
            if (floorPh) floorPh.style.display = 'none';
        } else {
            if (floorPh) floorPh.style.display = 'inline';
        }

        selectedOptions.forEach(opt => {
            const chip = document.createElement('div');
            chip.className = 'emp-dept-chip';
            chip.style.cssText = 'display:inline-block; padding: 2px 8px; border-radius: 4px; background: #e9ecef; margin: 2px; font-size: 13px;';
            chip.innerHTML = `${opt.text} <span class="emp-floor-chip-rm fw-bold text-danger ms-1" style="cursor:pointer;" data-id="${opt.value}">&times;</span>`;
            floorChips.appendChild(chip);
        });
    }

    function syncDeptDropdownState() {
        if (!deptList || !deptSelect) return;
        const selectedIds = Array.from(deptSelect.options).filter(o => o.selected).map(o => o.value);
        const items = deptList.querySelectorAll('.emp-dept-list-opt');
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

    function syncFloorDropdownState() {
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

    function buildDeptDropdownOptions(filter = '') {
        if (!deptList) return;
        deptList.innerHTML = '';
        const lowerFilter = filter.toLowerCase();

        availableDepartments.forEach(dept => {
            if (dept.name.toLowerCase().includes(lowerFilter)) {
                const div = document.createElement('div');
                div.className = 'emp-dept-list-opt p-2 border-bottom';
                div.style.cursor = 'pointer';
                div.setAttribute('data-id', dept.id);
                div.innerText = dept.name;

                div.addEventListener('click', function(e) {
                    e.stopPropagation();
                    let opt = Array.from(deptSelect.options).find(o => o.value == dept.id);
                    if (opt) {
                        opt.selected = !opt.selected;
                    } else {
                        opt = new Option(dept.name, dept.id);
                        opt.selected = true;
                        deptSelect.add(opt);
                    }
                    renderDeptChips();
                    syncDeptDropdownState();
                });

                deptList.appendChild(div);
            }
        });
        syncDeptDropdownState();
    }

    function buildFloorDropdownOptions(filter = '') {
        if (!floorList) return;
        floorList.innerHTML = '';
        const lowerFilter = filter.toLowerCase();

        availableFloors.forEach(floor => {
            const floorName = String(floor.name || '');
            if (floorName.toLowerCase().includes(lowerFilter)) {
                const div = document.createElement('div');
                div.className = 'emp-floor-list-opt p-2 border-bottom';
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
                    renderFloorChips();
                    syncFloorDropdownState();
                });

                floorList.appendChild(div);
            }
        });
        syncFloorDropdownState();
    }

    if (deptSearch) {
        deptSearch.addEventListener('input', function(e) {
            buildDeptDropdownOptions(e.target.value);
        });
    }
    if (floorSearch) {
        floorSearch.addEventListener('input', function(e) {
            buildFloorDropdownOptions(e.target.value);
        });
    }

    if (deptBox) {
        deptBox.addEventListener('click', function(e) {
            if (e.target.classList.contains('emp-dept-chip-rm')) {
                const idToRemove = e.target.getAttribute('data-id');
                const opt = Array.from(deptSelect.options).find(o => o.value == idToRemove);
                if (opt) opt.selected = false;
                renderDeptChips();
                syncDeptDropdownState();
                return;
            }
            if (deptDd) {
                deptDd.style.display = deptDd.style.display === 'none' ? 'block' : 'none';
            }
        });
    }
    if (floorBox) {
        floorBox.addEventListener('click', function(e) {
            if (e.target.classList.contains('emp-floor-chip-rm')) {
                const idToRemove = e.target.getAttribute('data-id');
                const opt = Array.from(floorSelect.options).find(o => o.value == idToRemove);
                if (opt) opt.selected = false;
                renderFloorChips();
                syncFloorDropdownState();
                return;
            }
            if (floorDd) {
                floorDd.style.display = floorDd.style.display === 'none' ? 'block' : 'none';
            }
        });
    }

    document.addEventListener('click', function(e) {
        if (deptBox && deptDd && !deptBox.contains(e.target) && !deptDd.contains(e.target)) {
            deptDd.style.display = 'none';
        }
        if (floorBox && floorDd && !floorBox.contains(e.target) && !floorDd.contains(e.target)) {
            floorDd.style.display = 'none';
        }
    });

    function populateDepartments(orgId, sbuId) {
        availableDepartments = [];
        if (!deptSelect) return;
        
        const org = orgsData.find(o => o.id == orgId);
        if (org && org.sbus) {
            const sbu = org.sbus.find(s => s.id == sbuId);
            if (sbu && sbu.departments) {
                availableDepartments = sbu.departments;
            }
        }

        if (availableDepartments.length === 0) {
            if (deptHint) deptHint.style.display = 'block';
            deptSelect.innerHTML = '';
        } else {
            if (deptHint) deptHint.style.display = 'none';
            const currentSelectedIds = Array.from(deptSelect.options).filter(o => o.selected).map(o => parseInt(o.value));
            deptSelect.innerHTML = '';
            availableDepartments.sort((a,b) => a.name.localeCompare(b.name));
            
            availableDepartments.forEach(dept => {
                const opt = new Option(dept.name, dept.id);
                if (currentSelectedIds.includes(parseInt(dept.id))) {
                    opt.selected = true;
                }
                deptSelect.add(opt);
            });
        }
        renderDeptChips();
        buildDeptDropdownOptions();
    }

    window.empDeptComboBox = {
        clearSbuChange: function() {} // Safely override previously referenced fallback
    };

    if (deptSelect && Array.from(deptSelect.options).length > 0) {
        renderDeptChips();
    }

    // --- STEP 5: COMPACT BANK DETAILS LOGIC ---
    
    // Original editData check
    let savedBanks = window.editData && window.editData.bankDetails ? JSON.parse(JSON.stringify(window.editData.bankDetails)) : [];

    window.addBankDetail = function() {
        resetBankForm();
        window.scrollTo({
            top: document.getElementById('step-5').offsetTop - 100,
            behavior: 'smooth'
        });
    };

    window.resetBankForm = function() {
        document.getElementById('bank_detail_id').value = '';
        document.getElementById('bankDetailsAccountTitleInput').value = '';
        document.getElementById('bankDetailsAccountNumberInput').value = '';
        document.getElementById('bankDetailsIbanInput').value = '';
        document.getElementById('bankDetailsBankNameInput').value = '';
        document.getElementById('bankDetailsBranchNameInput').value = '';
        document.getElementById('bankDetailsBranchCodeInput').value = '';
        document.getElementById('bankDetailsBranchAddressInput').value = '';
        
        if (document.getElementById('accountCategoryPersonal')) document.getElementById('accountCategoryPersonal').checked = true;
        if (document.getElementById('salaryAccountNo')) document.getElementById('salaryAccountNo').checked = true;
        if (document.getElementById('bankDetailsAccountTypeSaving')) document.getElementById('bankDetailsAccountTypeSaving').checked = true;
        
        document.getElementById('bankResetBtn').classList.add('d-none');
        document.querySelectorAll('#bankEntryForm .is-invalid').forEach(el => el.classList.remove('is-invalid'));
    };

    function validateBankFormBeforeSave() {
        const bankForm = document.getElementById('bankEntryForm');
        if (!bankForm) return null;

        const accountCategory = document.querySelector('input[name="account_category"]:checked')?.value || '';
        const accountType = document.querySelector('input[name="account_type"]:checked')?.value || '';
        const salaryRaw = document.querySelector('input[name="is_salary_account"]:checked')?.value;
        const isSalaryAccount = salaryRaw === '1' || salaryRaw === '0';

        const accountTitleInput = document.getElementById('bankDetailsAccountTitleInput');
        const accountNoInput = document.getElementById('bankDetailsAccountNumberInput');
        const ibanInput = document.getElementById('bankDetailsIbanInput');
        const bankNameInput = document.getElementById('bankDetailsBankNameInput');
        const branchNameInput = document.getElementById('bankDetailsBranchNameInput');
        const branchCodeInput = document.getElementById('bankDetailsBranchCodeInput');
        const branchAddressInput = document.getElementById('bankDetailsBranchAddressInput');

        const accountTitle = String(accountTitleInput?.value ?? '').trim();
        const accountNo = String(accountNoInput?.value ?? '').replace(/\s+/g, '');
        const iban = String(ibanInput?.value ?? '').replace(/\s+/g, '').toUpperCase();
        const bankName = String(bankNameInput?.value ?? '').trim();
        const branchName = String(branchNameInput?.value ?? '').trim();
        const branchCode = String(branchCodeInput?.value ?? '').trim();
        const branchAddress = String(branchAddressInput?.value ?? '').trim();

        if (accountNoInput) accountNoInput.value = accountNo;
        if (ibanInput) ibanInput.value = iban;
        if (accountTitleInput) accountTitleInput.value = accountTitle;
        if (bankNameInput) bankNameInput.value = bankName;
        if (branchNameInput) branchNameInput.value = branchName;
        if (branchCodeInput) branchCodeInput.value = branchCode;
        if (branchAddressInput) branchAddressInput.value = branchAddress;

        const errors = {};
        const titleRegex = /^[A-Za-z0-9]+(?:[A-Za-z0-9\s.\-'_]*[A-Za-z0-9])?$/;
        const digitsOnlyRegex = /^[0-9]+$/;
        const bankNameRegex = /^(?!.*[<>])(?=.*[A-Za-z])[A-Za-z0-9\s.'\-&,\/#()]{2,255}$/;
        const branchCodeRegex = /^[A-Za-z0-9\-]+$/;
        const branchAddressRegex = /^[A-Za-z0-9]+[\sA-Za-z0-9.\-&,\/()#']*$/;
        const ibanRegex = /^[A-Z0-9]+$/;

        if (!['Personal', 'Company'].includes(accountCategory)) {
            errors.account_category = ['Account category must be Personal or Company operated.'];
        }

        if (!accountTitle) {
            errors.account_title = ['Account title is required.'];
        } else if (accountTitle.length < 3) {
            errors.account_title = ['Account title must be at least 3 characters.'];
        } else if (accountTitle.length > 50) {
            errors.account_title = ['Account title must not exceed 50 characters.'];
        } else if (!titleRegex.test(accountTitle)) {
            errors.account_title = ['Account title may only contain letters, numbers, spaces, apostrophes, dots, hyphens, and underscores.'];
        }

        if (!accountNo) {
            errors.account_no = ['Account number is required.'];
        } else if (accountNo.length < 8) {
            errors.account_no = ['Account number must be at least 8 digits.'];
        } else if (accountNo.length > 16) {
            errors.account_no = ['Account number must not exceed 16 digits.'];
        } else if (!digitsOnlyRegex.test(accountNo)) {
            errors.account_no = ['Account number must contain digits only.'];
        }

        if (!bankName) {
            errors.bank_name = ['Bank institution name is required.'];
        }

        if (!branchName) {
            errors.branch_name = ['Branch name is required.'];
        } else if (branchName.length > 100) {
            errors.branch_name = ['Branch name must not exceed 100 characters.'];
        }

        if (!branchCode) {
            errors.branch_code = ['Branch code is required.'];
        } else if (branchCode.length > 10) {
            errors.branch_code = ['Branch code must not exceed 10 characters.'];
        } else if (!branchCodeRegex.test(branchCode)) {
            errors.branch_code = ['Branch code may only contain letters, numbers, and hyphens (no spaces).'];
        }

        if (!branchAddress) {
            errors.branch_address = ['Branch address is required.'];
        } else if (branchAddress.length < 2) {
            errors.branch_address = ['Branch address must be at least 2 characters.'];
        } else if (branchAddress.length > 150) {
            errors.branch_address = ['Branch address must not exceed 150 characters.'];
        } else if (!branchAddressRegex.test(branchAddress)) {
            errors.branch_address = ['Branch address may only contain letters, numbers, spaces, and basic punctuation.'];
        }

        if (!iban) {
            errors.iban = ['IBAN is required.'];
        } else if (iban.length > 34) {
            errors.iban = ['IBAN must not exceed 34 characters.'];
        } else if (!ibanRegex.test(iban)) {
            errors.iban = ['IBAN must contain letters and digits only (no spaces).'];
        }

        if (!['Saving', 'Current'].includes(accountType)) {
            errors.account_type = ['A/C type is required.'];
        }

        if (!isSalaryAccount) {
            errors.is_salary_account = ['Indicate whether this account is used for salary (payroll).'];
        }

        if (Object.keys(errors).length > 0) {
            showFieldErrors(errors, bankForm);
            return null;
        }

        return {
            account_category: accountCategory,
            account_title: accountTitle,
            account_no: accountNo,
            iban,
            bank_name: bankName,
            branch_name: branchName,
            branch_code: branchCode,
            branch_address: branchAddress,
            account_type: accountType,
            is_salary_account: salaryRaw === '1',
        };
    }

    function collectStep5Data() {
        const banks = [];
        const editingId = document.getElementById('bank_detail_id').value;
        
        savedBanks.forEach(b => {
             if (b.id != editingId) banks.push(b);
        });

        const title = document.getElementById('bankDetailsAccountTitleInput').value.trim();
        const no = document.getElementById('bankDetailsAccountNumberInput').value.trim();
        if (title !== '' || no !== '') {
            const validatedDraft = validateBankFormBeforeSave();
            if (!validatedDraft) {
                return null;
            }
            validatedDraft.id = editingId || null;
            banks.push(validatedDraft);
        }
        return banks;
    }

    // Capture original processStepSave if we haven't already
    const baseProcessStepSave = window.processStepSave;

    window.processStepSave = function(step, onSuccess) {
        if (step === 5) {
            const banks = collectStep5Data();
            if (banks === null) {
                return; // Draft validation failed
            }
            if (banks.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Bank Account Saved',
                    text: 'Please save at least one bank account using the "Save Account" button before proceeding.',
                    confirmButtonColor: '#1a237e'
                });
                return;
            }

            const form = document.getElementById('employeeForm');
            const formData = new FormData(form);
            
            formData.delete('banks'); 
            banks.forEach((bank, index) => {
                for (const [key, value] of Object.entries(bank)) {
                    if (value !== null && value !== undefined) {
                        // Convert booleans to "1"/"0" â€” FormData sends "true"/"false" strings
                        // which Laravel's boolean validator rejects
                        let sendValue = value;
                        if (key === 'is_salary_account') {
                            sendValue = value ? '1' : '0';
                        }
                        // Map 'id' as 'bank_detail_id' so backend uniqueness check ignores the record
                        if (key === 'id') {
                            formData.append(`banks[${index}][bank_detail_id]`, sendValue);
                        }
                        formData.append(`banks[${index}][${key}]`, sendValue);
                    }
                }
            });

            return executeStep5Save(formData, onSuccess);
        }
        
        return baseProcessStepSave(step, onSuccess);
    };

    async function executeStep5Save(formData, onSuccess) {
        const nextBtn = document.getElementById('nextBtn');
        const prevBtn = document.getElementById('prevBtn');
        const originalText = nextBtn.textContent;
        nextBtn.disabled = true;
        nextBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';

        formData.append('step', 5);
        const employeeId = document.getElementById('saved_employee_id')?.value;
        if (employeeId) formData.append('employee_id', employeeId);

        try {
            const response = await fetch('/admin/employees/save-step', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: formData
            });

            const data = await response.json();
            if (response.status === 422) {
                showFieldErrors(data.errors);
            } else if (data.success) {
                clearStepErrors();
                if (data.employee_id) document.getElementById('saved_employee_id').value = data.employee_id;
                
                // Show success message and move to Step 6
                Swal.fire({
                    icon: 'success',
                    title: 'Step 5 Saved',
                    text: 'Bank details have been saved successfully. Moving to final details.',
                    confirmButtonColor: '#1a237e',
                    timer: 2000,
                    timerProgressBar: true
                }).then(() => {
                    if (currentStep === maxStepReached) maxStepReached = Math.min(totalSteps, currentStep + 1);
                    if (onSuccess) onSuccess();
                    // Ensure Step 6 starts at sub-step 1 (Contact)
                    window.setMoreSubStep(1);
                });
            } else {
                showError(data.message || 'Something went wrong.');
            }
        } catch (error) {
            showError('Network error');
        } finally {
            nextBtn.disabled = false;
            nextBtn.textContent = originalText;
            if (prevBtn) prevBtn.disabled = false;
            syncStepUi();
        }
    }

    window.saveBankDetail = async function() {
        const saveBtn = document.querySelector('button[onclick="saveBankDetail()"]');
        if (saveBtn && saveBtn.disabled) return;

        const employeeId = document.getElementById('saved_employee_id')?.value;
        if (!employeeId) {
            showError('Please save the "General Information" step first.');
            return;
        }

        const validatedBank = validateBankFormBeforeSave();
        if (!validatedBank) {
            return;
        }

        const bankId = document.getElementById('bank_detail_id').value;
        const payload = {
            employee_id: employeeId,
            subsection: 'bank_row',
            bank_detail_id: bankId,
            account_category: validatedBank.account_category,
            account_title: validatedBank.account_title,
            account_no: validatedBank.account_no,
            iban: validatedBank.iban,
            bank_name: validatedBank.bank_name,
            branch_name: validatedBank.branch_name,
            branch_code: validatedBank.branch_code,
            branch_address: validatedBank.branch_address,
            account_type: validatedBank.account_type,
            is_salary_account: validatedBank.is_salary_account
        };

        const originalText = saveBtn.innerHTML;
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';

        try {
            const response = await fetch('/admin/employees/save-subsection', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(payload)
            });

            let data = null;
            try {
                data = await response.json();
            } catch (parseError) {
                data = null;
            }

            if (response.status === 422) {
                if (data && data.errors && typeof data.errors === 'object') {
                    showFieldErrors(data.errors);
                } else if (data && data.message) {
                    showError(data.message);
                } else {
                    showError('Validation failed.');
                }
            } else if (response.ok && data && data.success) {
                Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Saved', showConfirmButton: false, timer: 2000 });
                
                const rec = {
                    id: data.id ? parseInt(data.id) : (bankId ? parseInt(bankId) : null),
                    account_category: payload.account_category,
                    account_title: payload.account_title,
                    account_no: payload.account_no,
                    iban: payload.iban,
                    bank_name: payload.bank_name,
                    branch_name: payload.branch_name,
                    branch_code: payload.branch_code,
                    branch_address: payload.branch_address,
                    account_type: payload.account_type,
                    is_salary_account: payload.is_salary_account
                };

                if (bankId) {
                    const idx = savedBanks.findIndex(b => b.id == bankId);
                    if (idx !== -1) savedBanks[idx] = rec;
                } else {
                    savedBanks.push(rec);
                }

                if (payload.is_salary_account) {
                    savedBanks.forEach(b => { if (b.id != rec.id) b.is_salary_account = false; });
                }

                renderBankList();
                resetBankForm();
                showToast(bankId ? 'Bank account updated' : 'Bank account saved');
            } else if (data && data.message) {
                showError(data.message);
            } else {
                showError('Unable to save bank details.');
            }
        } catch (error) { showError('Unable to connect. Please try again.'); }
        finally {
            saveBtn.disabled = false;
            saveBtn.innerHTML = originalText;
        }
    };

    window.editBankDetail = function(id) {
        const bank = savedBanks.find(b => b.id == id);
        if (!bank) return;

        resetBankForm();
        document.getElementById('bank_detail_id').value = bank.id || '';
        document.getElementById('bankDetailsAccountTitleInput').value = bank.account_title || '';
        document.getElementById('bankDetailsAccountNumberInput').value = bank.account_no || '';
        document.getElementById('bankDetailsIbanInput').value = bank.iban || '';
        document.getElementById('bankDetailsBankNameInput').value = bank.bank_name || '';
        document.getElementById('bankDetailsBranchNameInput').value = bank.branch_name || '';
        document.getElementById('bankDetailsBranchCodeInput').value = bank.branch_code || '';
        document.getElementById('bankDetailsBranchAddressInput').value = bank.branch_address || '';
        
        // Use the actual values from the savedBanks array
        const categoryInput = document.querySelector(`input[name="account_category"][value="${bank.account_category}"]`);
        if (categoryInput) categoryInput.checked = true;

        const salaryInput = document.querySelector(`input[name="is_salary_account"][value="${bank.is_salary_account ? '1' : '0'}"]`);
        if (salaryInput) salaryInput.checked = true;

        const typeInput = document.querySelector(`input[name="account_type"][value="${bank.account_type}"]`);
        if (typeInput) typeInput.checked = true;

        document.getElementById('bankResetBtn').classList.remove('d-none');
        window.scrollTo({ top: document.getElementById('bankEntryForm').offsetTop - 100, behavior: 'smooth' });
    };

    window.deleteBankDetail = async function(id) {
        const result = await showConfirm('Delete this bank account?');
        if (!result.isConfirmed) return;

        const employeeId = document.getElementById('saved_employee_id')?.value;
        try {
            const resp = await fetch('/admin/employees/delete-bank-detail', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ employee_id: employeeId, id: id })
            });
            const d = await resp.json();
            if (d.success) {
                const currentEditId = document.getElementById('bank_detail_id')?.value;
                if (currentEditId && currentEditId == id) {
                    resetBankForm();
                }
                savedBanks = savedBanks.filter(b => b.id != id);
                renderBankList();
                showToast('Bank account deleted');
            } else showError(d.message);
        } catch (e) { showError('Network error'); }
    };

    function renderBankList() {
        const list = document.getElementById('bankDetailsList');
        if (!list) return;

        list.innerHTML = '';
        if (savedBanks.length === 0) {
            list.innerHTML = `<div class="col-12" id="bankEmptyState"><div class="text-center py-3 bg-light rounded text-muted small border" style="border-style: dotted !important;">No bank accounts saved yet.</div></div>`;
            return;
        }

        const template = document.getElementById('bankCardTemplate');
        savedBanks.forEach(bank => {
            const clone = template.content.cloneNode(true);
            const card = clone.querySelector('.bank-card-item');
            card.setAttribute('data-id', bank.id);
            if (bank.is_salary_account) {
                clone.querySelector('.salary-account-badge').classList.remove('d-none');
            }
            
            const initial = (bank.bank_name || 'B').charAt(0).toUpperCase();
            clone.querySelector('.bank-initial-icon').innerText = initial;
            clone.querySelector('.bank-title-label').innerText = bank.account_title || 'Account';
            clone.querySelector('.bank-institution-label').innerText = bank.bank_name || 'N/A';
            
            const branchLabel = clone.querySelector('.bank-branch-label');
            const branchRow = clone.querySelector('.bank-branch-row');
            if (bank.branch_name) {
                if (branchLabel) branchLabel.innerText = bank.branch_name;
                if (branchRow) branchRow.classList.remove('d-none');
            }
            clone.querySelector('.bank-category-label').innerText = `(${bank.account_category || '-'})`;
            clone.querySelector('.bank-no-label').innerText = bank.account_no || 'N/A';
            clone.querySelector('.bank-iban-label').innerText = bank.iban || 'N/A';
            
            clone.querySelector('.edit-bank-btn').onclick = () => editBankDetail(bank.id);
            clone.querySelector('.delete-bank-btn').onclick = () => deleteBankDetail(bank.id);
            list.appendChild(clone);
        });
    }

    // Validation override for specific navigation rules
    document.getElementById('nextBtn').addEventListener('click', function(e) {
        if (currentStep === 5) {
            // Check if we have SAVED banks. 
            // We ignore unsaved form data here because the user must use "Save Account"
            if (savedBanks.length === 0) {
                 e.stopImmediatePropagation();
                 
                 // Show visual error if not already showing
                 if (!document.querySelector('#bankDetailsList .field-error-msg')) {
                    const list = document.getElementById('bankDetailsList');
                    const err = document.createElement('div');
                    err.className = 'field-error-msg text-danger small mt-2 fw-bold text-center w-100';
                    err.textContent = 'Please save at least one bank account using the "Save Account" button.';
                    list.appendChild(err);
                 }
                 
                showError('At least one bank account is required.', 'Action Required');
                 return;
            }
            
            const hasSalaryAccount = savedBanks.some(b => b.is_salary_account);
            if (!hasSalaryAccount) {
                e.stopImmediatePropagation();
                showError('One bank account must be marked as the Salary Account (Primary).', 'Action Required');
                return;
            }
        }
    }, true);

    // --- STEP 6: MORE INFORMATION LOGIC ---
    let currentMoreStep = 1;
    const totalMoreSteps = 8;

    window.setMoreSubStep = function(step) {
        currentMoreStep = step;
        document.querySelectorAll('.more-sub-pane').forEach(p => p.classList.remove('active'));
        document.querySelectorAll('.more-sub-tab').forEach(t => t.classList.remove('active'));
        
        const pane = document.getElementById('moreStepPane' + step);
        if (pane) pane.classList.add('active');
        
        const tab = document.querySelector(`.more-sub-tab[data-more-step="${step}"]`);
        if (tab) tab.classList.add('active');
        
        if (typeof syncStepUi === 'function') syncStepUi();
    };

    window.isFirstMoreStep = function() { return currentMoreStep === 1; };
    window.isLastMoreStep = function() { return currentMoreStep === totalMoreSteps; };

    window.nextMoreSubStep = function() {
        if (currentMoreStep < totalMoreSteps) {
            window.setMoreSubStep(currentMoreStep + 1);
        }
    };

    window.prevMoreSubStep = function() {
        if (currentMoreStep > 1) {
            window.setMoreSubStep(currentMoreStep - 1);
        }
    };

    document.querySelectorAll('.more-sub-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            if (isStepUnsaved()) {
                showUnsavedWarning();
                return;
            }
            window.setMoreSubStep(parseInt(this.getAttribute('data-more-step')));
        });
    });

    // --- Subsection Management Helpers (Family, Academic, Certificates, Employment) ---

    function setSubsectionRowMode(row, type, isPreview) {
        if (!row) return;
        row.classList.toggle('preview-mode', isPreview);
        const saveBtn = row.querySelector(`[data-${type}-save]`);
        if (!saveBtn) return;
        
        if (isPreview) {
            saveBtn.classList.remove('btn-outline-primary');
            saveBtn.classList.add('btn-outline-secondary');
            saveBtn.innerHTML = '<i class="bi bi-pencil"></i>';
            saveBtn.setAttribute('title', 'Edit record');
        } else {
            saveBtn.classList.remove('btn-outline-secondary');
            saveBtn.classList.add('btn-outline-primary');
            saveBtn.innerHTML = '<i class="bi bi-floppy"></i>';
            saveBtn.setAttribute('title', 'Save record');
        }

        // Hide NOK toggle in preview mode for family members
        if (type === 'family') {
            const nokToggle = row.querySelector('[data-family-nok-toggle]');
            if (nokToggle) {
                const radio = row.querySelector('.family-nok-selector');
                const isNok = radio && radio.checked;
                
                if (isPreview && !isNok) {
                    nokToggle.classList.add('d-none');
                } else {
                    nokToggle.classList.remove('d-none');
                }
                syncFamilyNokFromRadios();
            }
        }
    }

    function updateSubsectionPreview(row, type) {
        if (!row) return;
        row.querySelectorAll('input, select, textarea').forEach(input => {
            const name = input.getAttribute('name');
            if (name) {
                const cleanKey = name.match(/\[([^\]]*)\]$/)?.[1] || name;
                if (cleanKey) {
                    const preview = row.querySelector(`[data-${type}-preview-${cleanKey.replace(/_/g, '-')}]`) ||
                                   row.querySelector(`[data-${type}-preview-${cleanKey}]`);
                    if (preview) {
                        let displayValue = input.value || '-';
                        if (input.tagName === 'SELECT' && input.value) {
                            displayValue = input.options[input.selectedIndex].text;
                        } else if (input.type === 'date' && input.value) {
                            displayValue = formatPreviewDate(input.value);
                        }
                        
                        // Special case for Relation "Other"
                        if (cleanKey === 'relation' && input.value === 'Other') {
                            const otherInput = row.querySelector('[name*="relation_other"]');
                            if (otherInput && otherInput.value) {
                                displayValue = otherInput.value;
                            }
                        }
                        preview.textContent = displayValue;
                    }
                }
            }
        });

        // Special preview for academic certificates
        if (type === 'academic') {
            const transcriptView = row.querySelector('[data-academic-transcript-view-container]');
            const transcriptFile = row.querySelector('[data-academic-transcript-file]');
            const transcriptPreview = row.querySelector('[data-academic-transcript-preview-status]');

            const degreeView = row.querySelector('[data-academic-degree-view-container]');
            const degreeFile = row.querySelector('[data-academic-degree-file]');
            const degreePreview = row.querySelector('[data-academic-degree-preview-status]');

            if (transcriptPreview) {
                const hasTranscript = (transcriptView && !transcriptView.classList.contains('d-none')) || 
                                     (transcriptFile && transcriptFile.files.length > 0);
                transcriptPreview.classList.toggle('d-none', hasTranscript);
            }

            if (degreePreview) {
                const hasDegree = (degreeView && !degreeView.classList.contains('d-none')) || 
                                 (degreeFile && degreeFile.files.length > 0);
                degreePreview.classList.toggle('d-none', hasDegree);
            }
        }

        // Special preview for professional certificates
        if (type === 'certificate') {
            const certView = row.querySelector('[data-certificate-view-container]');
            const certFile = row.querySelector('[data-certificate-file]');
            const certPreview = row.querySelector('[data-certificate-preview-status]');

            if (certPreview) {
                const hasCert = (certView && !certView.classList.contains('d-none')) || 
                               (certFile && certFile.files.length > 0);
                certPreview.classList.toggle('d-none', hasCert);
            }
        }
    }

    async function saveSubsectionRow(type, rowElement, options = {}) {
        const silentSuccess = !!options.silentSuccess;
        const isPreview = rowElement.classList.contains('preview-mode');
        if (isPreview) {
            setSubsectionRowMode(rowElement, type, false);
            return false;
        }

        const employeeId = document.getElementById('saved_employee_id')?.value;
        if (!employeeId) {
            showError('Save general information first.');
            return false;
        }

        const saveBtn = rowElement.querySelector(`[data-${type}-save]`);
        const originalHtml = saveBtn.innerHTML;
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

        // Collect data using FormData to support file uploads
        const formData = new FormData();
        formData.append('employee_id', employeeId);
        formData.append('subsection', `${type}_row`);
        
        const dbId = rowElement.getAttribute('data-db-id');
        if (dbId) {
            formData.append(`${type}_id`, dbId);
            formData.append('id', dbId); // Consistent ID for backend
        }

        rowElement.querySelectorAll('input, select, textarea').forEach(input => {
            const name = input.getAttribute('name');
            if (name) {
                if (name === 'family_nok_selector') return;
                const cleanKey = name.match(/\[([^\]]*)\]$/)?.[1] || name;
                if (cleanKey) {
                    if (input.type === 'file') {
                        if (input.files.length > 0) {
                            formData.append(cleanKey, input.files[0]);
                        }
                    } else {
                        formData.append(cleanKey, input.value);
                    }
                }
            }
        });

        // Special handling for academic transcript and degree files
        const academicTranscriptFile = rowElement.querySelector('[data-academic-transcript-file]');
        if (academicTranscriptFile && academicTranscriptFile.files.length > 0) {
            formData.append('transcript_file', academicTranscriptFile.files[0]);
        }
        const academicDegreeFile = rowElement.querySelector('[data-academic-degree-file]');
        if (academicDegreeFile && academicDegreeFile.files.length > 0) {
            formData.append('degree_file', academicDegreeFile.files[0]);
        }

        // For validation, we still need a plain object
        const validationData = {};
        formData.forEach((value, key) => {
            if (!(value instanceof File)) {
                validationData[key] = value;
            } else {
                validationData[key] = '[FILE]'; // Placeholder for validation logic
            }
        });

        const rowErrors = validateMoreRowData(type, validationData, rowElement);
        if (Object.keys(rowErrors).length > 0) {
            // Handle _doc_required as a toast since there is no direct input field element
            if (rowErrors._doc_required) {
                showError(rowErrors._doc_required[0], 'Document Required');
                delete rowErrors._doc_required;
            }
            if (Object.keys(rowErrors).length > 0) {
                showFieldErrors(rowErrors, rowElement);
            }
            saveBtn.disabled = false;
            saveBtn.innerHTML = originalHtml;
            return false;
        }

        try {
            const response = await fetch('/admin/employees/save-subsection', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                    // Note: Content-Type header is omitted so browser sets it with boundary
                },
                body: formData
            });

            const res = await response.json();
            if (response.status === 422) {
                showFieldErrors(res.errors, rowElement);
                saveBtn.innerHTML = originalHtml;
                return false;
            } else if (res.success) {
                // Remove any remaining visual errors on successful save
                rowElement.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                rowElement.querySelectorAll('.field-error-msg').forEach(err => err.remove());
                
                if (res.id) rowElement.setAttribute('data-db-id', res.id);
                
                // Update academic document UI if it was just uploaded
                if (type === 'academic' && res.success) {
                    const transcriptInput = rowElement.querySelector('[data-academic-transcript-file]');
                    if (transcriptInput && transcriptInput.files.length > 0) {
                        if (res.transcript_url) {
                            const viewWrap = rowElement.querySelector('[data-academic-transcript-view-container]');
                            const viewLink = rowElement.querySelector('[data-academic-transcript-document-link]');
                            const filenameEl = rowElement.querySelector('[data-academic-transcript-filename]');
                            if (viewLink) {
                                viewLink.href = res.transcript_url;
                                viewLink.style.pointerEvents = '';
                                viewLink.style.opacity = '';
                                viewLink.title = 'View';
                            }
                            if (filenameEl) filenameEl.textContent = transcriptInput.files[0].name;
                            if (viewWrap) {
                                viewWrap.classList.remove('d-none');
                                if (res.transcript_id) viewWrap.setAttribute('data-attachment-id', res.transcript_id);
                            }
                            rowElement.querySelector('[data-academic-transcript-upload-container]').classList.add('d-none');
                        }
                        transcriptInput.value = '';
                    }

                    const degreeInput = rowElement.querySelector('[data-academic-degree-file]');
                    if (degreeInput && degreeInput.files.length > 0) {
                        if (res.degree_url) {
                            const viewWrap = rowElement.querySelector('[data-academic-degree-view-container]');
                            const viewLink = rowElement.querySelector('[data-academic-degree-document-link]');
                            const filenameEl = rowElement.querySelector('[data-academic-degree-filename]');
                            if (viewLink) {
                                viewLink.href = res.degree_url;
                                viewLink.style.pointerEvents = '';
                                viewLink.style.opacity = '';
                                viewLink.title = 'View';
                            }
                            if (filenameEl) filenameEl.textContent = degreeInput.files[0].name;
                            if (viewWrap) {
                                viewWrap.classList.remove('d-none');
                                if (res.degree_id) viewWrap.setAttribute('data-attachment-id', res.degree_id);
                            }
                            rowElement.querySelector('[data-academic-degree-upload-container]').classList.add('d-none');
                        }
                        degreeInput.value = '';
                    }
                }

                // Update employment document UI if they were just uploaded
                if (type === 'employment' && res.success) {
                    // Handle Experience Letter
                    const expInput = rowElement.querySelector('[data-employment-exp-file]');
                    if (expInput && expInput.files.length > 0 && res.exp_letter_url) {
                        const viewWrap = rowElement.querySelector('[data-employment-exp-view-container]');
                        const viewLink = rowElement.querySelector('[data-employment-exp-link]');
                        const filenameEl = rowElement.querySelector('[data-employment-exp-filename]');
                        if (viewLink) viewLink.href = res.exp_letter_url;
                        if (filenameEl) filenameEl.textContent = expInput.files[0].name;
                        if (viewWrap) {
                            viewWrap.classList.remove('d-none');
                            if (res.exp_letter_id) viewWrap.setAttribute('data-attachment-id', res.exp_letter_id);
                        }
                        rowElement.querySelector('[data-employment-exp-upload-container]').classList.add('d-none');
                        expInput.value = '';
                    }

                    // Handle Salary Slip
                    const salaryInput = rowElement.querySelector('[data-employment-salary-file]');
                    if (salaryInput && salaryInput.files.length > 0 && res.salary_slip_url) {
                        const viewWrap = rowElement.querySelector('[data-employment-salary-view-container]');
                        const viewLink = rowElement.querySelector('[data-employment-salary-link]');
                        const filenameEl = rowElement.querySelector('[data-employment-salary-filename]');
                        if (viewLink) viewLink.href = res.salary_slip_url;
                        if (filenameEl) filenameEl.textContent = salaryInput.files[0].name;
                        if (viewWrap) {
                            viewWrap.classList.remove('d-none');
                            if (res.salary_slip_id) viewWrap.setAttribute('data-attachment-id', res.salary_slip_id);
                        }
                        rowElement.querySelector('[data-employment-salary-upload-container]').classList.add('d-none');
                        salaryInput.value = '';
                    }
                }

                // Update certificate document UI if it was just uploaded
                if (type === 'certificate' && res.success) {
                    const fileInput = rowElement.querySelector('[data-certificate-file]');
                    if (fileInput && fileInput.files.length > 0 && res.attachment_url) {
                        const viewWrap = rowElement.querySelector('[data-certificate-view-container]');
                        const viewLink = rowElement.querySelector('[data-certificate-document-link]');
                        const filenameEl = rowElement.querySelector('[data-certificate-filename]');
                        if (viewLink) {
                            viewLink.href = res.attachment_url;
                            viewLink.style.pointerEvents = '';
                            viewLink.style.opacity = '';
                            viewLink.title = 'View';
                        }
                        if (filenameEl) filenameEl.textContent = fileInput.files[0].name;
                        if (viewWrap) {
                            viewWrap.classList.remove('d-none');
                            if (res.attachment_id) viewWrap.setAttribute('data-attachment-id', res.attachment_id);
                        }
                        rowElement.querySelector('[data-certificate-upload-container]').classList.add('d-none');
                        fileInput.value = '';
                    }
                }

                if (!silentSuccess) {
                    showToast(`${type.charAt(0).toUpperCase() + type.slice(1)} record saved successfully`);
                }
                updateSubsectionPreview(rowElement, type);
                setSubsectionRowMode(rowElement, type, true);
                rowElement.classList.add('saved-row');
                return true;
            } else {
                showError(res.message);
                saveBtn.innerHTML = originalHtml;
                return false;
            }
        } catch (e) { 
            showError('Network error'); 
            saveBtn.innerHTML = originalHtml;
            return false;
        } finally {
            saveBtn.disabled = false;
        }
    }

    function validateMoreRowData(type, data, rowElement) {
        const errors = {};
        const addErr = (key, msg) => {
            if (!errors[key]) errors[key] = [];
            errors[key].push(msg);
        };
        const v = (k) => String(data[k] || '').trim();
        const countWords = (text) => String(text || '').trim().split(/\s+/).filter(Boolean).length;
        const today = new Date();
        today.setHours(0, 0, 0, 0);

        if (type === 'family') {
            const name = v('name');
            const gender = v('gender');
            const dob = v('dob');
            const relation = v('relation');
            const relationOther = v('relation_other');
            const occupation = v('occupation');
            const isNok = String(data.is_next_of_kin || data.is_next_of_kin_hidden || '0') === '1';
            const nokCnic = String(v('nok_cnic')).replace(/-/g, '');
            const nokExpiry = v('nok_cnic_expiry_date');
            const nokContact = v('nok_contact');
            const personNameRegex = /^(?!.*[<>])(?=.*[A-Za-z])[A-Za-z\s.\-'_]{3,100}$/;
            const alphaLabelRegex = /^(?!.*[<>])(?=.*[A-Za-z])[A-Za-z\s'.,\-&\/()]{2,100}$/;
            const alphaNumLabelRegex = /^(?!.*[<>])(?=.*[A-Za-z])[A-Za-z0-9\s'.,\-&\/#()]{1,100}$/;

            if (!name) addErr('name', 'Name is required.');
            else if (name.length < 3 || name.length > 50) addErr('name', 'Name must be 3 to 50 characters.');
            else if (!personNameRegex.test(name)) addErr('name', 'Name may only contain letters and standard punctuation.');
            if (!['Male', 'Female'].includes(gender)) addErr('gender', 'Gender is required.');
            if (!dob || !isValidDate(dob)) addErr('dob', 'Date of birth is required.');
            else if (dateValue(dob) >= today) addErr('dob', 'Date of birth must be before today.');
            if (!relation) addErr('relation', 'Relation is required.');
            else if (relation.length < 2 || relation.length > 100) addErr('relation', 'Relation must be 2 to 100 characters.');
            else if (!alphaLabelRegex.test(relation)) addErr('relation', 'Relation may only contain letters and standard punctuation.');
            if (relation === 'Other' && !relationOther) addErr('relation_other', 'Specify relation when Other is selected.');
            if (relationOther) {
                if (relationOther.length < 2 || relationOther.length > 100) addErr('relation_other', 'Specify relation must be 2 to 100 characters.');
                else if (!alphaLabelRegex.test(relationOther)) addErr('relation_other', 'Specify relation may only contain letters and standard punctuation.');
            }
            if (occupation) {
                if (occupation.length > 100) addErr('occupation', 'Occupation must not exceed 100 characters.');
                else if (!alphaNumLabelRegex.test(occupation)) addErr('occupation', 'Occupation may only contain letters, numbers, spaces, and standard punctuation.');
            }

            if (isNok) {
                if (!/^\d{13,15}$/.test(nokCnic)) addErr('nok_cnic', 'NOK CNIC must be 13 to 15 digits.');
                if (!nokExpiry || !isValidDate(nokExpiry)) addErr('nok_cnic_expiry_date', 'NOK CNIC expiry date is required.');
                else if (dateValue(nokExpiry) <= today) addErr('nok_cnic_expiry_date', 'NOK CNIC expiry must be after today.');
                if (!isValidContact(nokContact)) addErr('nok_contact', 'NOK contact must be 11 to 15 digits.');
            }
        }

        if (type === 'academic') {
            const degree = v('degree');
            const grade = v('grade_cgpa');
            const start = v('start_date');
            const end = v('end_date');
            const field = v('field_of_study');
            const institute = v('institute');
            const alphaNumericTextRegex = /^[A-Za-z0-9]+[\sA-Za-z0-9.\-&,\/()#']*$/;

            if (!degree) addErr('degree', 'Degree type is required.');
            else if (degree.length > 50) addErr('degree', 'Degree type must not exceed 50 characters.');
            else if (countWords(degree) > 20) addErr('degree', 'Degree type can be at most 20 words.');

            const degreeTitle = v('degree_title');
            if (!degreeTitle) addErr('degree_title', 'Degree title is required.');
            else if (degreeTitle.length > 100) addErr('degree_title', 'Degree title must not exceed 100 characters.');
            else if (countWords(degreeTitle) > 20) addErr('degree_title', 'Degree title can be at most 20 words.');

            if (!grade) addErr('grade_cgpa', 'Grade / CGPA is required.');
            else if (grade.length > 20) addErr('grade_cgpa', 'Grade / CGPA must not exceed 20 characters.');
            else if (countWords(grade) > 10) addErr('grade_cgpa', 'Grade / CGPA can be at most 10 words.');
            if (!start || !isValidDate(start)) addErr('start_date', 'Start date is required.');
            if (!end || !isValidDate(end)) addErr('end_date', 'End date is required.');
            if (isValidDate(start) && isValidDate(end) && dateValue(end) < dateValue(start)) {
                addErr('end_date', 'End date must be on or after start date.');
            }
            if (field) {
                if (field.length > 50) addErr('field_of_study', 'Field of study must not exceed 50 characters.');
                else if (!alphaNumericTextRegex.test(field)) addErr('field_of_study', 'Field of study may only contain letters, numbers, spaces, and standard punctuation.');
            }
            if (institute) {
                if (institute.length > 150) addErr('institute', 'Institute must not exceed 150 characters.');
                else if (countWords(institute) > 20) addErr('institute', 'University can be at most 20 words.');
            }
            if ((degree === 'Matric' || degree === 'Intermediate / Diploma' || degree === 'Intermediate') && !institute) {
                addErr('institute', 'Board is required for selected degree.');
            }

            // Mandatory Document Check (either transcript or degree)
            const transcriptInput = rowElement ? rowElement.querySelector('[data-academic-transcript-file]') : null;
            const transcriptView = rowElement ? rowElement.querySelector('[data-academic-transcript-view-container]') : null;
            const isTranscriptSelected = transcriptInput && transcriptInput.files.length > 0;
            const isTranscriptExisting = transcriptView && !transcriptView.classList.contains('d-none');

            const degreeInput = rowElement ? rowElement.querySelector('[data-academic-degree-file]') : null;
            const degreeView = rowElement ? rowElement.querySelector('[data-academic-degree-view-container]') : null;
            const isDegreeSelected = degreeInput && degreeInput.files.length > 0;
            const isDegreeExisting = degreeView && !degreeView.classList.contains('d-none');
            
            if (!isTranscriptSelected && !isTranscriptExisting && !isDegreeSelected && !isDegreeExisting) {
                addErr('_doc_required', 'Please upload at least one document: Transcript or Degree Certificate.');
            }
        }

        if (type === 'certificate') {
            const certificateName = v('certificate_name');
            const start = v('start_date');
            const end = v('end_date');
            const institute = v('institute');
            const alphaNumericTextRegex = /^[A-Za-z0-9]+[\sA-Za-z0-9.\-&,\/()#']*$/;

            if (!certificateName) addErr('certificate_name', 'Certificate name is required.');
            else if (certificateName.length > 150) addErr('certificate_name', 'Certificate name must not exceed 150 characters.');
            else if (countWords(certificateName) > 20) addErr('certificate_name', 'Certificate name can be at most 20 words.');
            else if (!alphaNumericTextRegex.test(certificateName)) addErr('certificate_name', 'Certificate name may only contain letters, numbers, spaces, and standard punctuation.');

            if (!start || !isValidDate(start)) addErr('start_date', 'Start date is required.');
            if (!end || !isValidDate(end)) addErr('end_date', 'End date is required.');
            if (isValidDate(start) && isValidDate(end) && dateValue(end) < dateValue(start)) {
                addErr('end_date', 'End date must be on or after start date.');
            }

            if (!institute) addErr('institute', 'Institute is required.');
            else if (institute.length > 255) addErr('institute', 'Institute must not exceed 255 characters.');
            else if (countWords(institute) > 20) addErr('institute', 'Institute can be at most 20 words.');

            // Mandatory Certificate Document Check
            const certInput = rowElement ? rowElement.querySelector('[data-certificate-file]') : null;
            const certView = rowElement ? rowElement.querySelector('[data-certificate-view-container]') : null;
            const isCertSelected = certInput && certInput.files.length > 0;
            const isCertExisting = certView && !certView.classList.contains('d-none');
            
            if (!isCertSelected && !isCertExisting) {
                addErr('_doc_required', 'Please upload a copy of the professional certificate.');
            }
        }

        if (type === 'employment') {
            const org = v('organization');
            const designation = v('designation');
            const from = v('from_date');
            const to = v('to_date');
            const salary = v('salary');
            const reason = v('reason_for_leaving');

            if (!org) addErr('organization', 'Organization is required.');
            else if (org.length > 100) addErr('organization', 'Organization must not exceed 100 characters.');
            if (!designation) addErr('designation', 'Designation is required.');
            else if (designation.length > 50) addErr('designation', 'Designation must not exceed 50 characters.');
            if (!from || !isValidDate(from)) addErr('from_date', 'From date is required.');
            else if (dateValue(from) > today) addErr('from_date', 'From date cannot be in the future.');
            if (!to || !isValidDate(to)) addErr('to_date', 'To date is required.');
            if (isValidDate(from) && isValidDate(to) && dateValue(to) < dateValue(from)) {
                addErr('to_date', 'To date must be on or after from date.');
            }
            if (salary && salary.length > 20) addErr('salary', 'Salary must not exceed 20 digits.');
            else if (salary && !/^\d+$/.test(salary)) addErr('salary', 'Salary must contain digits only.');
            if (reason && reason.length > 200) addErr('reason_for_leaving', 'Reason for leaving must not exceed 200 characters.');

            const hrContact = v('hr_contact');
            const hrEmail = v('hr_email');
            if (hrContact && hrContact.length > 15) addErr('hr_contact', 'HR contact must not exceed 15 characters.');
            if (hrEmail) {
                if (hrEmail.length > 100) addErr('hr_email', 'HR email must not exceed 100 characters.');
                else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(hrEmail)) addErr('hr_email', 'HR email must be a valid email address.');
            }
        }

        return errors;
    }

    async function removeSubsectionRow(type, rowElement) {
        const dbId = rowElement.getAttribute('data-db-id');
        const employeeId = document.getElementById('saved_employee_id')?.value;

        const result = await showConfirm(`Are you sure you want to delete this ${type} record?`);
        if (!result.isConfirmed) return;

        if (dbId) {
            try {
                // Capitalize first letter for endpoint: delete-family, delete-academic, delete-employment
                const endpointMap = {
                    'family': 'family',
                    'academic': 'academic',
                    'certificate': 'certificate',
                    'employment': 'employment'
                };
                const endpoint = `/admin/employees/delete-${endpointMap[type]}`;
                
                const response = await fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ employee_id: employeeId, id: dbId })
                });

                const res = await response.json();
                if (res.success) {
                    rowElement.remove();
                    updateRowIndices(type);
                    showToast(`${type.charAt(0).toUpperCase() + type.slice(1)} record deleted successfully`);
                } else {
                    showError(res.message);
                }
            } catch (e) { showError('Network error'); }
        } else {
            rowElement.remove();
            updateRowIndices(type);
        }
    }

    function updateRowIndices(type) {
        const containerId = {
            'family': 'moreFamilyMembersContainer',
            'academic': 'moreAcademicRecordsContainer',
            'certificate': 'moreCertificateRecordsContainer',
            'employment': 'moreEmploymentRecordsContainer'
        }[type];
        
        const countId = {
            'family': 'moreFamilyMemberCount',
            'academic': 'moreAcademicRecordCount',
            'certificate': 'moreCertificateRecordCount',
            'employment': 'moreEmploymentRecordCount'
        }[type];

        const container = document.getElementById(containerId);
        const rows = container.querySelectorAll(`[data-${type}-row]`);
        if (type === 'family') {
            rows.forEach((row, idx) => {
                const indexSpan = row.querySelector('[data-family-index]');
                if (indexSpan) indexSpan.textContent = 'Member ' + String(idx + 1);
                const removeBtn = row.querySelector('[data-family-remove]');
                if (removeBtn) removeBtn.disabled = false;
            });
            const countLabel = document.getElementById(countId);
            if (countLabel) {
                countLabel.textContent = rows.length + (rows.length === 1 ? ' Member' : ' Members');
            }
            return;
        }

        rows.forEach((row, idx) => {
            const indexSpan = row.querySelector(`[data-${type}-index]`);
            if (indexSpan) indexSpan.textContent = `${type.charAt(0).toUpperCase() + type.slice(1)} ${idx + 1}`;
        });

        const countLabel = document.getElementById(countId);
        if (countLabel) countLabel.textContent = `${rows.length} ${rows.length === 1 ? 'Record' : 'Records'}`;
    }

    function syncFamilyNokFromRadios() {
        const container = document.getElementById('moreFamilyMembersContainer');
        if (!container) return;
        const rows = Array.from(container.querySelectorAll('[data-family-row]'));
        const selectedRow = rows.find(function (r) {
            const radio = r.querySelector('.family-nok-selector');
            return radio && radio.checked;
        }) || null;

        rows.forEach(function (row) {
            const radio = row.querySelector('.family-nok-selector');
            const hidden = row.querySelector('[data-family-is-nok-hidden]');
            const block = row.querySelector('[data-family-nok-fields]');
            const badge = row.querySelector('[data-family-nok-badge]');
            const selectedBadge = row.querySelector('[data-family-nok-selected-badge]');
            const toggleCard = row.querySelector('[data-family-nok-toggle]');
            const memberIndicator = row.querySelector('[data-family-nok-member-indicator]');
            const lockedNote = row.querySelector('[data-family-nok-locked-note]');
            const isOn = radio && radio.checked;
            const isLockedForOther = !!selectedRow && selectedRow !== row && !isOn;

            if (toggleCard) {
                const icon = toggleCard.querySelector('i');
                if (isOn) {
                    toggleCard.classList.remove('btn-light', 'border-success');
                    toggleCard.classList.add('btn-success');
                    if (icon) {
                        icon.className = 'bi bi-people-fill text-white';
                    }
                } else {
                    toggleCard.classList.remove('btn-success');
                    toggleCard.classList.add('btn-light', 'border-success');
                    if (icon) {
                        icon.className = 'bi bi-people text-success';
                    }
                }
                toggleCard.setAttribute('title', isOn ? 'Remove Next of Kin' : 'Set as Next of Kin');
            }

            if (hidden) hidden.value = isOn ? '1' : '0';
            if (block) block.classList.toggle('d-none', !isOn);
            if (badge) badge.classList.toggle('d-none', !isOn);
            if (selectedBadge) selectedBadge.classList.toggle('d-none', !isOn);
            if (memberIndicator) memberIndicator.classList.toggle('d-none', !isOn);
            
            row.querySelectorAll('[data-family-nok-input]').forEach(function (inp) {
                inp.required = !!isOn;
            });
        });
    }

    window.ensureFamilyNokBeforeStepSave = syncFamilyNokFromRadios;

    const moreFamilyMembersContainerEl = document.getElementById('moreFamilyMembersContainer');
    if (moreFamilyMembersContainerEl) {
        const removeFamilyInlineError = function (input) {
            if (!input) return;
            input.classList.remove('is-invalid');
            let next = input.nextElementSibling;
            while (next && next.classList && next.classList.contains('input-guard-error')) {
                const toRemove = next;
                next = next.nextElementSibling;
                toRemove.remove();
            }
        };
        const showFamilyInlineError = function (input, message) {
            if (!input) return;
            removeFamilyInlineError(input);
            input.classList.add('is-invalid');
            const err = document.createElement('div');
            err.className = 'field-error-msg text-danger small mt-1 fw-bold input-guard-error';
            err.setAttribute('role', 'alert');
            err.textContent = message;
            input.insertAdjacentElement('afterend', err);
        };
        const familyFieldConfig = function (target) {
            if (!target) return null;
            if (target.matches('[data-family-name]')) {
                return {
                    max: 50,
                    allowed: /[A-Za-z\s.\-'_]/,
                    clean: /[^A-Za-z\s.\-'_]/g,
                    invalidMessage: /\d/.test(String(target.value ?? '')) ? 'Name cannot contain numbers.' : 'Name may contain letters only.',
                    maxMessage: 'Maximum 50 characters allowed.',
                };
            }
            if (target.matches('[data-family-relation-other]')) {
                return {
                    max: 100,
                    allowed: /[A-Za-z\s'.,\-&\/()]/,
                    clean: /[^A-Za-z\s'.,\-&\/()]/g,
                    invalidMessage: 'Use letters and basic punctuation only.',
                    maxMessage: 'Maximum 100 characters allowed.',
                };
            }
            if (target.matches('[data-family-occupation]')) {
                return {
                    max: 100,
                    allowed: /[A-Za-z0-9\s'.,\-&\/#()]/,
                    clean: /[^A-Za-z0-9\s'.,\-&\/#()]/g,
                    invalidMessage: 'Use letters, numbers, and basic punctuation only.',
                    maxMessage: 'Maximum 100 characters allowed.',
                };
            }
            if (target.matches('[data-family-nok-contact]')) {
                return {
                    max: 15,
                    allowed: /[0-9]/,
                    clean: /[^0-9]/g,
                    invalidMessage: 'Only digits are allowed.',
                    maxMessage: 'Maximum 15 digits allowed.',
                };
            }
            if (target.matches('[data-family-nok-cnic]')) {
                return {
                    max: 15,
                    allowed: /[0-9-]/,
                    clean: /[^0-9-]/g,
                    invalidMessage: 'Only digits and hyphen (-) are allowed.',
                    maxMessage: 'Maximum 15 characters allowed.',
                };
            }
            return null;
        };

        moreFamilyMembersContainerEl.addEventListener('beforeinput', function (e) {
            const target = e.target;
            const config = familyFieldConfig(target);
            if (!config) return;
            if (e.inputType && e.inputType.startsWith('delete')) return;
            const inserted = typeof e.data === 'string' ? e.data : '';
            if (!inserted) return;

            if (!Array.from(inserted).every((ch) => config.allowed.test(ch))) {
                e.preventDefault();
                showFamilyInlineError(target, config.invalidMessage);
                return;
            }

            const current = String(target.value ?? '');
            const start = typeof target.selectionStart === 'number' ? target.selectionStart : current.length;
            const end = typeof target.selectionEnd === 'number' ? target.selectionEnd : current.length;
            const nextLength = current.length - (end - start) + inserted.length;
            if (nextLength > config.max) {
                e.preventDefault();
                showFamilyInlineError(target, config.maxMessage);
            }
        }, true);

        moreFamilyMembersContainerEl.addEventListener('input', function (e) {
            const target = e.target;
            const config = familyFieldConfig(target);
            if (!config) return;

            const original = String(target.value ?? '');
            let cleaned = original.replace(config.clean, '');
            if (cleaned.length > config.max) {
                cleaned = cleaned.slice(0, config.max);
                target.value = cleaned;
                showFamilyInlineError(target, config.maxMessage);
                return;
            }
            if (cleaned !== original) {
                target.value = cleaned;
                showFamilyInlineError(target, config.invalidMessage);
                return;
            }

            if (target.classList.contains('is-invalid')) {
                removeFamilyInlineError(target);
            }
        }, true);

        moreFamilyMembersContainerEl.addEventListener('focusout', function (e) {
            const target = e.target;
            const config = familyFieldConfig(target);
            if (!config) return;
            const value = String(target.value ?? '');
            const cleaned = value.replace(config.clean, '');
            if (value === cleaned && value.length <= config.max) {
                removeFamilyInlineError(target);
            }
        }, true);

        moreFamilyMembersContainerEl.addEventListener('change', function (e) {
            if (e.target && e.target.classList && e.target.classList.contains('family-nok-selector')) {
                syncFamilyNokFromRadios();
            }
        });
        moreFamilyMembersContainerEl.addEventListener('click', async function (e) {
            const toggleCard = e.target.closest('[data-family-nok-toggle]');
            if (toggleCard) {
                const row = toggleCard.closest('[data-family-row]');
                if (row) {
                    const radio = row.querySelector('.family-nok-selector');
                    if (radio) {
                        const currentlyChecked = radio.checked;
                        if (currentlyChecked) {
                            // If untoggling, ask for confirmation and clear fields
                            const result = await showConfirm('Are you sure you want to remove this member as Next of Kin?', 'Remove Next of Kin');
                            if (!result.isConfirmed) return;
                            
                            radio.checked = false;
                            row.querySelectorAll('[data-family-nok-input]').forEach(function (inp) {
                                inp.value = '';
                            });
                            
                            if (row.getAttribute('data-db-id')) {
                                updateSubsectionPreview(row, 'family');
                                setSubsectionRowMode(row, 'family', false);
                                await saveSubsectionRow('family', row);
                            }
                        } else {
                            // CHECK IF ANOTHER MEMBER IS ALREADY NOK
                            const container = document.getElementById('moreFamilyMembersContainer');
                            const otherSelected = Array.from(container.querySelectorAll('.family-nok-selector'))
                                .find(r => r.checked && r !== radio);
                            
                            if (otherSelected) {
                                showError('Next of Kin is already selected in another member. Remove that first to change.', 'Selection Locked');
                                return;
                            }
                            
                            radio.checked = true;
                        }
                    }
                    syncFamilyNokFromRadios();
                }
                return;
            }
        });

    }

    // --- FAMILY Member Specifics ---

    window.addFamilyMember = function(data = null) {
        const container = document.getElementById('moreFamilyMembersContainer');
        const template = document.getElementById('moreFamilyMemberTemplate');
        if (!container || !template) return;

        const clone = template.content.cloneNode(true);
        const row = clone.querySelector('[data-family-row]');
        
        if (data) {
            row.setAttribute('data-db-id', data.id || '');
            if (data.name) row.querySelector('[data-family-name]').value = data.name;
            if (data.gender) row.querySelector('[data-family-gender]').value = data.gender;
            if (data.dateOfBirth) row.querySelector('[data-family-date-of-birth]').value = data.dateOfBirth;
            
            // Relation mapping
            const relSelect = row.querySelector('[data-family-relation]');
            const relOtherWrapper = row.querySelector('[data-family-relation-other-wrapper]');
            const relOtherInput = row.querySelector('[data-family-relation-other]');
            
            if (data.relation && relSelect) {
                const standardOptions = ['Father', 'Mother', 'Husband', 'Wife', 'Son', 'Daughter', 'Brother', 'Sister'];
                if (standardOptions.includes(data.relation)) {
                    relSelect.value = data.relation;
                } else {
                    relSelect.value = 'Other';
                    if (relOtherInput) relOtherInput.value = data.relation;
                    if (relOtherWrapper) relOtherWrapper.classList.remove('d-none');
                }
            }
            if (data.occupation) row.querySelector('[data-family-occupation]').value = data.occupation;
            const nokCnicEl = row.querySelector('[data-family-nok-cnic]');
            const nokExpiryEl = row.querySelector('[data-family-nok-cnic-expiry]');
            const nokContactEl = row.querySelector('[data-family-nok-contact]');
            if (nokCnicEl && data.nok_cnic) {
                nokCnicEl.value = data.nok_cnic;
                formatCNIC(nokCnicEl);
            }
            if (nokExpiryEl && data.nok_cnic_expiry_date) nokExpiryEl.value = data.nok_cnic_expiry_date;
            if (nokContactEl && data.nok_contact) {
                nokContactEl.value = data.nok_contact;
                formatContactMaskInput(nokContactEl);
            }
            if (data.is_next_of_kin) {
                const nokRadio = row.querySelector('.family-nok-selector');
                if (nokRadio) nokRadio.checked = true;
            }
            if (typeof window.ensureFamilyNokBeforeStepSave === 'function') {
                window.ensureFamilyNokBeforeStepSave();
            }
        }

        // Toggling logic for Relation dropdown
        const relationSelect = row.querySelector('[data-family-relation]');
        if (relationSelect) {
            relationSelect.addEventListener('change', function() {
                const wrapper = row.querySelector('[data-family-relation-other-wrapper]');
                const otherInput = row.querySelector('[data-family-relation-other]');
                if (this.value === 'Other') {
                    if (wrapper) wrapper.classList.remove('d-none');
                    if (otherInput) otherInput.required = true;
                } else {
                    if (wrapper) wrapper.classList.add('d-none');
                    if (otherInput) {
                        otherInput.required = false;
                        otherInput.value = '';
                    }
                }
            });
        }

        row.querySelector('[data-family-save]').onclick = () => saveSubsectionRow('family', row);
        row.querySelector('[data-family-remove]').onclick = () => removeSubsectionRow('family', row);

        container.appendChild(clone);
        if (data && data.id) {
            updateSubsectionPreview(row, 'family');
            setSubsectionRowMode(row, 'family', true);
        }
        syncFamilyNokFromRadios();
        updateRowIndices('family');
    };

    const addFamilyBtn = document.getElementById('moreFamilyAddMemberBtn');
    if (addFamilyBtn) addFamilyBtn.onclick = () => addFamilyMember();

    // --- ACADEMIC Record Specifics ---

    window.addAcademicRecord = function(data = null) {
        const container = document.getElementById('moreAcademicRecordsContainer');
        const template = document.getElementById('moreAcademicRecordTemplate');
        if (!container || !template) return;

        const clone = template.content.cloneNode(true);
        const row = clone.querySelector('[data-academic-row]');
        const degreeEl = row.querySelector('[data-academic-degree]');
        const boardEl = row.querySelector('[data-academic-board]');
        const boardWrap = row.querySelector('[data-academic-board-wrap]');
        const instituteEl = row.querySelector('[data-academic-institute]');
        const boardPreviewEl = row.querySelector('[data-academic-preview-board]');
        const boardEligibleDegrees = new Set(['Matric', 'Intermediate / Diploma', 'Intermediate']);

        if (data) {
            row.setAttribute('data-db-id', data.id || '');
            if (data.degree && degreeEl) {
                const hasMatchingOption = Array.from(degreeEl.options || []).some(function (opt) {
                    return String(opt.value) === String(data.degree);
                });
                if (!hasMatchingOption) {
                    const legacyOption = new Option(String(data.degree), String(data.degree), true, true);
                    degreeEl.add(legacyOption);
                }
                degreeEl.value = data.degree;
            }
            if (data.degree_title) row.querySelector('[data-academic-degree-title]').value = data.degree_title;
            if (data.grade_cgpa) row.querySelector('[data-academic-grade]').value = data.grade_cgpa;
            if (data.start_date) row.querySelector('[data-academic-start-date]').value = data.start_date;
            if (data.end_date) row.querySelector('[data-academic-end-date]').value = data.end_date;
            if (data.fieldOfStudy || data.field_of_study) row.querySelector('[data-academic-field-of-study]').value = data.fieldOfStudy || data.field_of_study;
            if (data.institute) row.querySelector('[data-academic-institute]').value = data.institute;
        }

        if (boardWrap && boardEl && instituteEl && degreeEl) {
            const degreeValue = String(degreeEl.value || '').trim();
            const shouldShowBoard = boardEligibleDegrees.has(degreeValue);
            boardWrap.classList.toggle('d-none', !shouldShowBoard);
            boardEl.required = shouldShowBoard;
            if (shouldShowBoard && instituteEl.value) {
                const hasBoardOption = Array.from(boardEl.options || []).some(function (opt) {
                    return String(opt.value) === String(instituteEl.value);
                });
                if (hasBoardOption) {
                    boardEl.value = instituteEl.value;
                }
            }
            if (boardPreviewEl) {
                boardPreviewEl.textContent = boardEl.value || '-';
            }
        }

        row.querySelector('[data-academic-save]').onclick = () => saveSubsectionRow('academic', row);
        row.querySelector('[data-academic-remove]').onclick = () => removeSubsectionRow('academic', row);

        // Delete/Change document logic
        const setupDeleteLogic = (type) => {
            const deleteBtn = row.querySelector(`[data-academic-${type}-document-remove]`);
            if (deleteBtn) {
                deleteBtn.onclick = async () => {
                    const viewWrap = row.querySelector(`[data-academic-${type}-view-container]`);
                    const attachmentId = viewWrap.getAttribute('data-attachment-id');
                    
                    if (!attachmentId) {
                        // Unsaved file selection — just reset to the upload zone
                        row.querySelector(`[data-academic-${type}-upload-container]`).classList.remove('d-none');
                        viewWrap.classList.add('d-none');
                        row.querySelector(`[data-academic-${type}-file]`).value = '';
                        showToast('Selection cleared');
                        return;
                    }

                    const result = await Swal.fire({
                        title: 'Are you sure?',
                        text: "This document will be permanently deleted.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Yes, delete it!'
                    });

                    if (result.isConfirmed) {
                        try {
                            const response = await fetch('/admin/employees/delete-attachment', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({ id: attachmentId })
                            });

                            const res = await response.json();
                            if (res.success) {
                                row.querySelector(`[data-academic-${type}-upload-container]`).classList.remove('d-none');
                                viewWrap.classList.add('d-none');
                                viewWrap.removeAttribute('data-attachment-id');
                                row.querySelector(`[data-academic-${type}-file]`).value = '';
                                
                                if (window.employeeAttachments) {
                                    window.employeeAttachments = window.employeeAttachments.filter(a => a.id != attachmentId);
                                }
                                if (window.editData && window.editData.attachments) {
                                    window.editData.attachments = window.editData.attachments.filter(a => a.id != attachmentId);
                                }

                                Swal.fire({
                                    icon: 'success', title: 'Deleted', text: 'Document deleted successfully', timer: 1500, showConfirmButton: false
                                });
                            } else {
                                showError(res.message || 'Failed to delete document');
                            }
                        } catch (e) {
                            showError('Network error during deletion');
                        }
                    }
                };
            }

            const fileInput = row.querySelector(`[data-academic-${type}-file]`);
            if (fileInput) {
                fileInput.onchange = (e) => {
                    if (e.target.files && e.target.files.length > 0) {
                        const file = e.target.files[0];

                        // Client-side file size validation (20 MB max)
                        if (file.size > 20 * 1024 * 1024) {
                            showToast('File size must not exceed 20 MB. Please choose a smaller file.', 'warning');
                            e.target.value = '';
                            return;
                        }

                        const uploadContainer = row.querySelector(`[data-academic-${type}-upload-container]`);
                        const viewContainer = row.querySelector(`[data-academic-${type}-view-container]`);
                        const filenameEl = row.querySelector(`[data-academic-${type}-filename]`);
                        const viewLink = row.querySelector(`[data-academic-${type}-document-link]`);

                        // Hide upload zone, show file badge
                        if (uploadContainer) uploadContainer.classList.add('d-none');
                        if (viewContainer) {
                            viewContainer.classList.remove('d-none');
                            // Clear any saved attachment ID — this is a new (unsaved) selection
                            viewContainer.removeAttribute('data-attachment-id');
                        }
                        if (filenameEl) filenameEl.textContent = file.name;
                        // Disable view link since file is not yet uploaded to server
                        if (viewLink) {
                            viewLink.removeAttribute('href');
                            viewLink.style.pointerEvents = 'none';
                            viewLink.style.opacity = '0.45';
                            viewLink.title = 'Save record to view';
                        }
                    }
                };
            }
        };

        setupDeleteLogic('transcript');
        setupDeleteLogic('degree');

        container.appendChild(clone);
        if (data && data.id) {
            // Check for existing attachments (can be multiple sources)
            const staticAttachments = (window.editData && window.editData.attachments) ? window.editData.attachments : [];
            const dynamicAttachments = window.employeeAttachments || [];
            const allAttachments = [...staticAttachments, ...dynamicAttachments];
            
            const transcriptSubsection = 'academic_' + data.id + '_transcript';
            const degreeSubsection = 'academic_' + data.id + '_degree';
            
            const loadAttachment = (type, subsectionMatch) => {
                const attachments = allAttachments.filter(a => String(a.subsection) === subsectionMatch);
                if (attachments.length > 0) {
                    const uploadWrap = row.querySelector(`[data-academic-${type}-upload-container]`);
                    const viewWrap = row.querySelector(`[data-academic-${type}-view-container]`);
                    const viewLink = row.querySelector(`[data-academic-${type}-document-link]`);
                    const filenameEl = row.querySelector(`[data-academic-${type}-filename]`);
                    
                    if (viewWrap && viewLink) {
                        const mainAttachment = attachments[0]; 
                        if (uploadWrap) uploadWrap.classList.add('d-none');
                        viewWrap.classList.remove('d-none');
                        viewWrap.setAttribute('data-attachment-id', mainAttachment.id);
                        viewLink.href = mainAttachment.url || '#';
                        if (filenameEl) filenameEl.textContent = mainAttachment.file_name || mainAttachment.name || type;
                    }
                }
            };

            loadAttachment('transcript', transcriptSubsection);
            loadAttachment('degree', degreeSubsection);

            // Backwards compatibility for old records that had just 'academic_{id}'
            const oldSubsection = 'academic_' + data.id;
            const oldAttachments = allAttachments.filter(a => String(a.subsection) === oldSubsection);
            if (oldAttachments.length > 0) {
                // Load them into degree by default to not break UI
                loadAttachment('degree', oldSubsection);
            }

            updateSubsectionPreview(row, 'academic');
            setSubsectionRowMode(row, 'academic', true);
        }
        updateRowIndices('academic');
    };

    const addAcademicBtn = document.getElementById('moreAcademicAddRecordBtn');
    if (addAcademicBtn) addAcademicBtn.onclick = () => addAcademicRecord();

    const moreAcademicRecordsContainerEl = document.getElementById('moreAcademicRecordsContainer');
    if (moreAcademicRecordsContainerEl) {
        const boardEligibleDegrees = new Set(['Matric', 'Intermediate / Diploma', 'Intermediate']);
        const syncAcademicBoardField = function (row, keepBoardValue) {
            if (!row) return;
            const degreeEl = row.querySelector('[data-academic-degree]');
            const boardWrap = row.querySelector('[data-academic-board-wrap]');
            const boardEl = row.querySelector('[data-academic-board]');
            const instituteEl = row.querySelector('[data-academic-institute]');
            const boardPreviewEl = row.querySelector('[data-academic-preview-board]');
            if (!degreeEl || !boardWrap || !boardEl || !instituteEl) return;

            const degreeValue = String(degreeEl.value || '').trim();
            const shouldShowBoard = boardEligibleDegrees.has(degreeValue);
            boardWrap.classList.toggle('d-none', !shouldShowBoard);

            if (!shouldShowBoard) {
                boardEl.required = false;
                boardEl.value = '';
                if (boardPreviewEl) boardPreviewEl.textContent = '-';
                return;
            }

            boardEl.required = true;
            if (!keepBoardValue && instituteEl.value) {
                const matchingOption = Array.from(boardEl.options || []).some(function (opt) {
                    return String(opt.value) === String(instituteEl.value);
                });
                if (matchingOption) {
                    boardEl.value = instituteEl.value;
                }
            }
            if (boardEl.value) {
                instituteEl.value = boardEl.value;
            } else {
                instituteEl.value = '';
            }
            if (boardPreviewEl) {
                boardPreviewEl.textContent = boardEl.value || '-';
            }
        };

        const removeAcademicInlineError = function (input) {
            if (!input) return;
            input.classList.remove('is-invalid');
            let next = input.nextElementSibling;
            while (next && next.classList && next.classList.contains('input-guard-error')) {
                const toRemove = next;
                next = next.nextElementSibling;
                toRemove.remove();
            }
        };
        const showAcademicInlineError = function (input, message) {
            if (!input) return;
            removeAcademicInlineError(input);
            input.classList.add('is-invalid');
            const err = document.createElement('div');
            err.className = 'field-error-msg text-danger small mt-1 fw-bold input-guard-error';
            err.setAttribute('role', 'alert');
            err.textContent = message;
            input.insertAdjacentElement('afterend', err);
        };
        const academicFieldConfig = function (target) {
            if (!target) return null;
            if (target.matches('[data-academic-degree]')) {
                return { max: 50, allowed: /[A-Za-z0-9\s.\-&,\/()#']/, clean: /[^A-Za-z0-9\s.\-&,\/()#']/g, invalid: 'Use letters, numbers, spaces, and basic punctuation only.', maxMsg: 'Maximum 50 characters allowed.' };
            }
            if (target.matches('[data-academic-degree-title]')) {
                return { max: 100, allowed: /[A-Za-z0-9\s.\-&,\/()#']/, clean: /[^A-Za-z0-9\s.\-&,\/()#']/g, invalid: 'Use letters, numbers, spaces, and basic punctuation only.', maxMsg: 'Maximum 100 characters allowed.' };
            }
            if (target.matches('[data-academic-grade]')) {
                return { max: 20, allowed: /[A-Za-z0-9\s.\-&,\/()#']/, clean: /[^A-Za-z0-9\s.\-&,\/()#']/g, invalid: 'Use letters, numbers, spaces, and basic punctuation only.', maxMsg: 'Maximum 20 characters allowed.' };
            }
            if (target.matches('[data-academic-field-of-study]')) {
                return { max: 50, allowed: /[A-Za-z0-9\s.\-&,\/()#']/, clean: /[^A-Za-z0-9\s.\-&,\/()#']/g, invalid: 'Use letters, numbers, spaces, and basic punctuation only.', maxMsg: 'Maximum 50 characters allowed.' };
            }
            if (target.matches('[data-academic-institute]')) {
                return { max: 150, allowed: /[A-Za-z0-9\s.\-&,\/()#']/, clean: /[^A-Za-z0-9\s.\-&,\/()#']/g, invalid: 'Use letters, numbers, spaces, and basic punctuation only.', maxMsg: 'Maximum 150 characters allowed.' };
            }
            return null;
        };

        moreAcademicRecordsContainerEl.addEventListener('beforeinput', function (e) {
            const target = e.target;
            const cfg = academicFieldConfig(target);
            if (!cfg) return;
            if (e.inputType && e.inputType.startsWith('delete')) return;
            const inserted = typeof e.data === 'string' ? e.data : '';
            if (!inserted) return;
            if (!Array.from(inserted).every((ch) => cfg.allowed.test(ch))) {
                e.preventDefault();
                showAcademicInlineError(target, cfg.invalid);
                return;
            }
            const current = String(target.value ?? '');
            const start = typeof target.selectionStart === 'number' ? target.selectionStart : current.length;
            const end = typeof target.selectionEnd === 'number' ? target.selectionEnd : current.length;
            const nextLength = current.length - (end - start) + inserted.length;
            if (nextLength > cfg.max) {
                e.preventDefault();
                showAcademicInlineError(target, cfg.maxMsg);
            }
        }, true);

        moreAcademicRecordsContainerEl.addEventListener('input', function (e) {
            const target = e.target;
            const cfg = academicFieldConfig(target);
            if (!cfg) return;
            const original = String(target.value ?? '');
            let cleaned = original.replace(cfg.clean, '');
            if (cleaned.length > cfg.max) {
                cleaned = cleaned.slice(0, cfg.max);
                target.value = cleaned;
                showAcademicInlineError(target, cfg.maxMsg);
                return;
            }
            if (cleaned !== original) {
                target.value = cleaned;
                showAcademicInlineError(target, cfg.invalid);
                return;
            }
            if (target.classList.contains('is-invalid')) {
                removeAcademicInlineError(target);
            }
        }, true);

        moreAcademicRecordsContainerEl.addEventListener('focusout', function (e) {
            const target = e.target;
            if (!target) return;
            const cfg = academicFieldConfig(target);
            if (cfg) {
                const value = String(target.value ?? '');
                const cleaned = value.replace(cfg.clean, '');
                if (value === cleaned && value.length <= cfg.max) {
                    removeAcademicInlineError(target);
                }
            }

            if (target.matches('[data-academic-start-date], [data-academic-end-date]')) {
                const row = target.closest('[data-academic-row]');
                if (!row) return;
                const start = row.querySelector('[data-academic-start-date]')?.value || '';
                const endInput = row.querySelector('[data-academic-end-date]');
                const end = endInput?.value || '';
                if (!start || !end || !endInput) {
                    if (target.classList.contains('is-invalid')) {
                        removeAcademicInlineError(target);
                    }
                    return;
                }
                const startDate = new Date(`${start}T00:00:00`);
                const endDate = new Date(`${end}T00:00:00`);
                if (!Number.isNaN(startDate.getTime()) && !Number.isNaN(endDate.getTime()) && endDate >= startDate) {
                    removeAcademicInlineError(endInput);
                }
            }
        }, true);

        moreAcademicRecordsContainerEl.addEventListener('change', function (e) {
            const target = e.target;
            if (!target) return;
            if (target.matches('[data-academic-degree]')) {
                const row = target.closest('[data-academic-row]');
                syncAcademicBoardField(row, false);
            }
            if (target.matches('[data-academic-board]')) {
                const row = target.closest('[data-academic-row]');
                if (row) {
                    const instituteEl = row.querySelector('[data-academic-institute]');
                    const boardPreviewEl = row.querySelector('[data-academic-preview-board]');
                    if (instituteEl) instituteEl.value = target.value || '';
                    if (boardPreviewEl) boardPreviewEl.textContent = target.value || '-';
                }
            }
            if (target.matches('[data-academic-start-date], [data-academic-end-date]')) {
                const row = target.closest('[data-academic-row]');
                if (!row) return;
                const start = row.querySelector('[data-academic-start-date]')?.value || '';
                const end = row.querySelector('[data-academic-end-date]')?.value || '';
                if (start && end) {
                    const startDate = new Date(`${start}T00:00:00`);
                    const endDate = new Date(`${end}T00:00:00`);
                    if (!Number.isNaN(startDate.getTime()) && !Number.isNaN(endDate.getTime()) && endDate < startDate) {
                        showAcademicInlineError(row.querySelector('[data-academic-end-date]'), 'End date must be on or after start date.');
                    }
                }
            }
        });
    }

    // --- CERTIFICATE Record Specifics ---

    window.addCertificateRecord = function(data = null) {
        const container = document.getElementById('moreCertificateRecordsContainer');
        const template = document.getElementById('moreCertificateRecordTemplate');
        if (!container || !template) return;

        const clone = template.content.cloneNode(true);
        const row = clone.querySelector('[data-certificate-row]');

        if (data) {
            row.setAttribute('data-db-id', data.id || '');
            if (data.certificate_name) row.querySelector('[data-certificate-name]').value = data.certificate_name;
            if (data.start_date) row.querySelector('[data-certificate-start-date]').value = data.start_date;
            if (data.end_date) row.querySelector('[data-certificate-end-date]').value = data.end_date;
            if (data.institute) row.querySelector('[data-certificate-institute]').value = data.institute;
        }

        row.querySelector('[data-certificate-save]').onclick = () => saveSubsectionRow('certificate', row);
        row.querySelector('[data-certificate-remove]').onclick = () => removeSubsectionRow('certificate', row);

        // Certificate document logic
        const setupCertificateDocLogic = () => {
            const fileInput = row.querySelector('[data-certificate-file]');
            const uploadContainer = row.querySelector('[data-certificate-upload-container]');
            const viewContainer = row.querySelector('[data-certificate-view-container]');
            const removeBtn = row.querySelector('[data-certificate-document-remove]');
            
            if (fileInput) {
                fileInput.onchange = (e) => {
                    if (e.target.files && e.target.files.length > 0) {
                        const filename = e.target.files[0].name;
                        const filenameEl = row.querySelector('[data-certificate-filename]');
                        const viewLink = row.querySelector('[data-certificate-document-link]');
                        
                        if (filenameEl) filenameEl.textContent = filename;
                        if (uploadContainer) uploadContainer.classList.add('d-none');
                        if (viewContainer) {
                            viewContainer.classList.remove('d-none');
                            viewContainer.removeAttribute('data-attachment-id');
                        }
                        
                        // Disable view link since file is not yet uploaded to server
                        if (viewLink) {
                            viewLink.removeAttribute('href');
                            viewLink.style.pointerEvents = 'none';
                            viewLink.style.opacity = '0.45';
                            viewLink.title = 'Save record to view';
                        }
                    }
                };
            }

            if (removeBtn) {
                removeBtn.onclick = async () => {
                    const attachmentId = viewContainer.getAttribute('data-attachment-id');
                    if (!attachmentId) {
                        uploadContainer.classList.remove('d-none');
                        viewContainer.classList.add('d-none');
                        fileInput.value = '';
                        const placeholderText = uploadContainer.querySelector('.small');
                        const uploadIcon = uploadContainer.querySelector('i');
                        if (placeholderText) {
                            placeholderText.textContent = 'No file chosen';
                            placeholderText.classList.remove('text-primary', 'fw-bold');
                            placeholderText.classList.add('text-secondary');
                        }
                        if (uploadIcon) uploadIcon.className = 'bi bi-upload';
                        showToast('Selection cleared');
                        return;
                    }

                    const result = await Swal.fire({
                        title: 'Are you sure?',
                        text: "This document will be permanently deleted.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Yes, delete it!'
                    });

                    if (result.isConfirmed) {
                        try {
                            const response = await fetch('/admin/employees/delete-attachment', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({ id: attachmentId })
                            });
                            const res = await response.json();
                            if (res.success) {
                                uploadContainer.classList.remove('d-none');
                                viewContainer.classList.add('d-none');
                                viewContainer.removeAttribute('data-attachment-id');
                                fileInput.value = '';
                                if (window.employeeAttachments) window.employeeAttachments = window.employeeAttachments.filter(a => a.id != attachmentId);
                                if (window.editData && window.editData.attachments) window.editData.attachments = window.editData.attachments.filter(a => a.id != attachmentId);
                                Swal.fire({ icon: 'success', title: 'Deleted', text: 'Document deleted successfully', timer: 1500, showConfirmButton: false });
                            } else {
                                showError(res.message || 'Failed to delete document');
                            }
                        } catch (e) { showError('Network error'); }
                    }
                };
            }
        };

        setupCertificateDocLogic();

        container.appendChild(clone);
        if (data && data.id) {
            // Load existing certificate attachment
            const staticAttachments = (window.editData && window.editData.attachments) ? window.editData.attachments : [];
            const dynamicAttachments = window.employeeAttachments || [];
            const allAttachments = [...staticAttachments, ...dynamicAttachments];
            
            const targetSubsection = 'certificate_' + data.id;
            const attachments = allAttachments.filter(a => String(a.subsection) === targetSubsection);
            
            if (attachments.length > 0) {
                const uploadWrap = row.querySelector('[data-certificate-upload-container]');
                const viewWrap = row.querySelector('[data-certificate-view-container]');
                const viewLink = row.querySelector('[data-certificate-document-link]');
                const filenameEl = row.querySelector('[data-certificate-filename]');
                
                if (viewWrap && viewLink) {
                    const mainAttachment = attachments[0]; 
                    if (uploadWrap) uploadWrap.classList.add('d-none');
                    viewWrap.classList.remove('d-none');
                    viewWrap.setAttribute('data-attachment-id', mainAttachment.id);
                    viewLink.href = mainAttachment.url || '#';
                    if (filenameEl) filenameEl.textContent = mainAttachment.file_name || mainAttachment.name || 'certificate';
                }
            }

            updateSubsectionPreview(row, 'certificate');
            setSubsectionRowMode(row, 'certificate', true);
        }
        updateRowIndices('certificate');
    };

    const addCertificateBtn = document.getElementById('moreCertificateAddRecordBtn');
    if (addCertificateBtn) addCertificateBtn.onclick = () => addCertificateRecord();

    // --- EMPLOYMENT Record Specifics ---

    window.addEmploymentRecord = function(data = null) {
        const container = document.getElementById('moreEmploymentRecordsContainer');
        const template = document.getElementById('moreEmploymentRecordTemplate');
        if (!container || !template) return;

        const clone = template.content.cloneNode(true);
        const row = clone.querySelector('[data-employment-row]');

        if (data) {
            row.setAttribute('data-db-id', data.id || '');
            if (data.organization) row.querySelector('[data-employment-organization]').value = data.organization;
            if (data.designation) row.querySelector('[data-employment-designation]').value = data.designation;
            if (data.from_date) row.querySelector('[data-employment-from-date]').value = data.from_date;
            if (data.to_date) row.querySelector('[data-employment-to-date]').value = data.to_date;
            if (data.salary) row.querySelector('[data-employment-salary]').value = data.salary;
            if (data.reason_for_leaving) row.querySelector('[data-employment-reason]').value = data.reason_for_leaving;
            if (data.hr_contact) row.querySelector('[data-employment-hr-contact]').value = data.hr_contact;
            if (data.hr_email) row.querySelector('[data-employment-hr-email]').value = data.hr_email;
        }

        row.querySelector('[data-employment-save]').onclick = () => saveSubsectionRow('employment', row);
        row.querySelector('[data-employment-remove]').onclick = () => removeSubsectionRow('employment', row);

        // Document logic for Experience Letter and Salary Slip
        const setupDocLogic = (fileInputAttr, uploadContainerAttr, viewContainerAttr, removeBtnAttr, linkAttr, filenameAttr) => {
            const fileInput = row.querySelector(`[${fileInputAttr}]`);
            const uploadContainer = row.querySelector(`[${uploadContainerAttr}]`);
            const viewContainer = row.querySelector(`[${viewContainerAttr}]`);
            const removeBtn = row.querySelector(`[${removeBtnAttr}]`);
            const linkEl = row.querySelector(`[${linkAttr}]`);
            const filenameEl = row.querySelector(`[${filenameAttr}]`);

            if (fileInput) {
                fileInput.onchange = (e) => {
                    if (e.target.files && e.target.files.length > 0) {
                        const filename = e.target.files[0].name;
                        const placeholderText = uploadContainer.querySelector('.small');
                        const uploadIcon = uploadContainer.querySelector('i');
                        if (placeholderText) {
                            placeholderText.textContent = filename;
                            placeholderText.classList.remove('text-secondary');
                            placeholderText.classList.add('text-primary', 'fw-bold');
                        }
                        if (uploadIcon) uploadIcon.className = 'bi bi-check-circle-fill text-success';
                    }
                };
            }

            if (removeBtn) {
                removeBtn.onclick = async () => {
                    const attachmentId = viewContainer.getAttribute('data-attachment-id');
                    if (!attachmentId) {
                        uploadContainer.classList.remove('d-none');
                        viewContainer.classList.add('d-none');
                        fileInput.value = '';
                        const placeholderText = uploadContainer.querySelector('.small');
                        const uploadIcon = uploadContainer.querySelector('i');
                        if (placeholderText) {
                            placeholderText.textContent = 'No file chosen';
                            placeholderText.classList.remove('text-primary', 'fw-bold');
                            placeholderText.classList.add('text-secondary');
                        }
                        if (uploadIcon) uploadIcon.className = 'bi bi-upload';
                        showToast('Selection cleared');
                        return;
                    }

                    const result = await Swal.fire({
                        title: 'Are you sure?',
                        text: "This document will be permanently deleted.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Yes, delete it!'
                    });

                    if (result.isConfirmed) {
                        try {
                            const response = await fetch('/admin/employees/delete-attachment', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({ id: attachmentId })
                            });
                            const res = await response.json();
                            if (res.success) {
                                uploadContainer.classList.remove('d-none');
                                viewContainer.classList.add('d-none');
                                viewContainer.removeAttribute('data-attachment-id');
                                fileInput.value = '';
                                if (window.employeeAttachments) window.employeeAttachments = window.employeeAttachments.filter(a => a.id != attachmentId);
                                if (window.editData && window.editData.attachments) window.editData.attachments = window.editData.attachments.filter(a => a.id != attachmentId);
                                Swal.fire({ icon: 'success', title: 'Deleted', text: 'Document deleted successfully', timer: 1500, showConfirmButton: false });
                            } else {
                                showError(res.message || 'Failed to delete document');
                            }
                        } catch (e) { showError('Network error'); }
                    }
                };
            }
        };

        setupDocLogic('data-employment-exp-file', 'data-employment-exp-upload-container', 'data-employment-exp-view-container', 'data-employment-exp-remove', 'data-employment-exp-link', 'data-employment-exp-filename');
        setupDocLogic('data-employment-salary-file', 'data-employment-salary-upload-container', 'data-employment-salary-view-container', 'data-employment-salary-remove', 'data-employment-salary-link', 'data-employment-salary-filename');

        container.appendChild(clone);
        if (data && data.id) {
            // Load documents
            const staticAttachments = (window.editData && window.editData.attachments) ? window.editData.attachments : [];
            const dynamicAttachments = window.employeeAttachments || [];
            const allAttachments = [...staticAttachments, ...dynamicAttachments];

            const loadDoc = (suffix, uploadContainerAttr, viewContainerAttr, linkAttr, filenameAttr) => {
                const targetSub = `ex_employment_${data.id}_${suffix}`;
                const found = allAttachments.filter(a => String(a.subsection) === targetSub);
                if (found.length > 0) {
                    const uploadBox = row.querySelector(`[${uploadContainerAttr}]`);
                    const viewBox = row.querySelector(`[${viewContainerAttr}]`);
                    const linkEl = row.querySelector(`[${linkAttr}]`);
                    const nameEl = row.querySelector(`[${filenameAttr}]`);
                    if (viewBox && linkEl) {
                        if (uploadBox) uploadBox.classList.add('d-none');
                        viewBox.classList.remove('d-none');
                        viewBox.setAttribute('data-attachment-id', found[0].id);
                        linkEl.href = found[0].url || '#';
                        if (nameEl) nameEl.textContent = found[0].file_name || found[0].name || 'document';
                    }
                }
            };
            loadDoc('exp', 'data-employment-exp-upload-container', 'data-employment-exp-view-container', 'data-employment-exp-link', 'data-employment-exp-filename');
            loadDoc('salary', 'data-employment-salary-upload-container', 'data-employment-salary-view-container', 'data-employment-salary-link', 'data-employment-salary-filename');

            updateSubsectionPreview(row, 'employment');
            setSubsectionRowMode(row, 'employment', true);
        }
        updateRowIndices('employment');
    };

    const addEmploymentBtn = document.getElementById('moreEmploymentAddRecordBtn');
    if (addEmploymentBtn) addEmploymentBtn.onclick = () => addEmploymentRecord();

    const moreEmploymentRecordsContainerEl = document.getElementById('moreEmploymentRecordsContainer');
    if (moreEmploymentRecordsContainerEl) {
        const removeEmploymentInlineError = function (input) {
            if (!input) return;
            input.classList.remove('is-invalid');
            let next = input.nextElementSibling;
            while (next && next.classList && next.classList.contains('input-guard-error')) {
                const toRemove = next;
                next = next.nextElementSibling;
                toRemove.remove();
            }
        };
        const showEmploymentInlineError = function (input, message) {
            if (!input) return;
            removeEmploymentInlineError(input);
            input.classList.add('is-invalid');
            const err = document.createElement('div');
            err.className = 'field-error-msg text-danger small mt-1 fw-bold input-guard-error';
            err.setAttribute('role', 'alert');
            err.textContent = message;
            input.insertAdjacentElement('afterend', err);
        };
        const employmentFieldConfig = function (target) {
            if (!target) return null;
            if (target.matches('[data-employment-organization]')) {
                return { max: 100, allowed: /[A-Za-z0-9\s.\-&,\/()#']/, clean: /[^A-Za-z0-9\s.\-&,\/()#']/g, invalid: 'Use letters, numbers, spaces, and basic punctuation only.', maxMsg: 'Maximum 100 characters allowed.' };
            }
            if (target.matches('[data-employment-designation]')) {
                return { max: 50, allowed: /[A-Za-z0-9\s.\-&,\/()#']/, clean: /[^A-Za-z0-9\s.\-&,\/()#']/g, invalid: 'Use letters, numbers, spaces, and basic punctuation only.', maxMsg: 'Maximum 50 characters allowed.' };
            }
            if (target.matches('[data-employment-salary]')) {
                return { max: 20, allowed: /[0-9]/, clean: /[^0-9]/g, invalid: 'Use digits only.', maxMsg: 'Maximum 20 digits allowed.' };
            }
            if (target.matches('[data-employment-reason]')) {
                return { max: 200, allowed: /[A-Za-z0-9\s.\-&,\/()#']/, clean: /[^A-Za-z0-9\s.\-&,\/()#']/g, invalid: 'Use letters, numbers, spaces, and basic punctuation only.', maxMsg: 'Maximum 200 characters allowed.' };
            }
            if (target.matches('[data-employment-hr-contact]')) {
                return { max: 15, allowed: /[0-9+\-()\s]/, clean: /[^0-9+\-()\s]/g, invalid: 'Use digits, +, -, and parentheses only.', maxMsg: 'Maximum 15 characters allowed.' };
            }
            if (target.matches('[data-employment-hr-email]')) {
                return { max: 100, allowed: /[A-Za-z0-9@._\-]/, clean: /[^A-Za-z0-9@._\-]/g, invalid: 'Use valid email characters only.', maxMsg: 'Maximum 100 characters allowed.' };
            }
            return null;
        };

        moreEmploymentRecordsContainerEl.addEventListener('beforeinput', function (e) {
            const target = e.target;
            const cfg = employmentFieldConfig(target);
            if (!cfg) return;
            if (e.inputType && e.inputType.startsWith('delete')) return;
            const inserted = typeof e.data === 'string' ? e.data : '';
            if (!inserted) return;
            if (!Array.from(inserted).every((ch) => cfg.allowed.test(ch))) {
                e.preventDefault();
                showEmploymentInlineError(target, cfg.invalid);
                return;
            }
            const current = String(target.value ?? '');
            const start = typeof target.selectionStart === 'number' ? target.selectionStart : current.length;
            const end = typeof target.selectionEnd === 'number' ? target.selectionEnd : current.length;
            const nextLength = current.length - (end - start) + inserted.length;
            if (nextLength > cfg.max) {
                e.preventDefault();
                showEmploymentInlineError(target, cfg.maxMsg);
            }
        }, true);

        moreEmploymentRecordsContainerEl.addEventListener('input', function (e) {
            const target = e.target;
            const cfg = employmentFieldConfig(target);
            if (!cfg) return;
            const original = String(target.value ?? '');
            let cleaned = original.replace(cfg.clean, '');
            if (cleaned.length > cfg.max) {
                cleaned = cleaned.slice(0, cfg.max);
                target.value = cleaned;
                showEmploymentInlineError(target, cfg.maxMsg);
                return;
            }
            if (cleaned !== original) {
                target.value = cleaned;
                showEmploymentInlineError(target, cfg.invalid);
                return;
            }
            if (target.matches('[data-employment-salary]') && cleaned && Number(cleaned) < 0) {
                showEmploymentInlineError(target, 'Salary cannot be negative.');
                return;
            }
            if (target.classList.contains('is-invalid')) {
                removeEmploymentInlineError(target);
            }
        }, true);

        moreEmploymentRecordsContainerEl.addEventListener('focusout', function (e) {
            const target = e.target;
            if (!target) return;
            const cfg = employmentFieldConfig(target);
            if (cfg) {
                const value = String(target.value ?? '');
                const cleaned = value.replace(cfg.clean, '');
                if (value === cleaned && value.length <= cfg.max) {
                    removeEmploymentInlineError(target);
                }
            }

            if (target.matches('[data-employment-from-date], [data-employment-to-date]')) {
                const row = target.closest('[data-employment-row]');
                if (!row) return;
                const fromInput = row.querySelector('[data-employment-from-date]');
                const toInput = row.querySelector('[data-employment-to-date]');
                const from = fromInput?.value || '';
                const to = toInput?.value || '';
                const today = new Date();
                today.setHours(0, 0, 0, 0);

                if (fromInput && from) {
                    const fromDate = new Date(`${from}T00:00:00`);
                    if (!Number.isNaN(fromDate.getTime()) && fromDate <= today) {
                        removeEmploymentInlineError(fromInput);
                    }
                }

                if (fromInput && toInput && from && to) {
                    const fromDate = new Date(`${from}T00:00:00`);
                    const toDate = new Date(`${to}T00:00:00`);
                    if (!Number.isNaN(fromDate.getTime()) && !Number.isNaN(toDate.getTime()) && toDate >= fromDate) {
                        removeEmploymentInlineError(toInput);
                    }
                }
            }
        }, true);

        moreEmploymentRecordsContainerEl.addEventListener('change', function (e) {
            const target = e.target;
            if (!target) return;
            if (target.matches('[data-employment-from-date], [data-employment-to-date]')) {
                const row = target.closest('[data-employment-row]');
                if (!row) return;
                const from = row.querySelector('[data-employment-from-date]')?.value || '';
                const to = row.querySelector('[data-employment-to-date]')?.value || '';
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                if (from) {
                    const fromDate = new Date(`${from}T00:00:00`);
                    if (!Number.isNaN(fromDate.getTime()) && fromDate > today) {
                        showEmploymentInlineError(row.querySelector('[data-employment-from-date]'), 'From date cannot be in the future.');
                        return;
                    }
                }
                if (from && to) {
                    const fromDate = new Date(`${from}T00:00:00`);
                    const toDate = new Date(`${to}T00:00:00`);
                    if (!Number.isNaN(fromDate.getTime()) && !Number.isNaN(toDate.getTime()) && toDate < fromDate) {
                        showEmploymentInlineError(row.querySelector('[data-employment-to-date]'), 'To date must be on or after from date.');
                        return;
                    }
                }
            }
        });
    }

    // --- Error Clearing on Input ---
    const mainForm = document.getElementById('employeeForm');
    if (mainForm) {
        const clearLocalError = function(e) {
            const target = e.target;
            if (target && target.classList.contains('is-invalid')) {
                const container = target.closest('[class^="col-"], [class*=" col-"], .col, .form-group, .mb-3');
                const guardSibling = target.nextElementSibling
                    && target.nextElementSibling.classList
                    && target.nextElementSibling.classList.contains('input-guard-error')
                    ? target.nextElementSibling
                    : null;

                if (guardSibling) {
                    const val = String(target.value ?? '');
                    const key = fieldKeyFromInput(target);
                    const maxLen = resolveMaxLength(target, key);
                    if (maxLen && val.length > maxLen) return;
                    if (target.dataset && target.dataset.maxLimitBlocked === '1' && maxLen && val.length >= maxLen) return;
                    if (typeof target.checkValidity === 'function' && !target.checkValidity()) return;
                }

                target.classList.remove('is-invalid');

                // Clear sibling/nearby error message
                if (container) {
                    const scopedErrors = Array.from(container.querySelectorAll('.field-error-msg'));
                    scopedErrors.forEach(function (err) {
                        if (err.classList.contains('input-guard-error') && err !== guardSibling) return;
                        err.remove();
                    });
                } else if (target.nextElementSibling && target.nextElementSibling.classList.contains('field-error-msg')) {
                    target.nextElementSibling.remove();
                }
            }
        };

        mainForm.addEventListener('input', clearLocalError);
        
        mainForm.addEventListener('change', function(e) {
            clearLocalError(e);
            
            if (e.target.type === 'radio' || e.target.tagName === 'SELECT') {
                const radioWrapper = e.target.closest('[id$="Wrapper"], [id$="Fields"], [id$="List"]');
                if (radioWrapper) {
                    const err = radioWrapper.querySelector('.field-error-msg') || radioWrapper.nextElementSibling;
                    if (err && err.classList.contains('field-error-msg')) {
                        err.remove();
                    }
                    if (e.target.type === 'radio') {
                        document.querySelectorAll(`input[name="${e.target.name}"]`).forEach(r => r.classList.remove('is-invalid'));
                    } else {
                        e.target.classList.remove('is-invalid');
                    }
                }
            }

        });

        mainForm.addEventListener('focusout', function (e) {
            const target = e.target;
            if (!target || !target.closest('#stepPane6')) return;   
            clearLocalError({ target: target });
        }, true);
    }
})();

if (typeof window.setExistingAttachments === 'function' && window.editData && Array.isArray(window.editData.attachments)) {
    window.setExistingAttachments(window.editData.attachments);
}

document.addEventListener('change', function(e) {
    if (e.target && e.target.name === 'has_disability') {
        const typeContainer = document.getElementById('moreMedicalDisabilityTypeContainer');
        const descContainer = document.getElementById('moreMedicalDisabilityDescriptionContainer');
        const isYes = e.target.value === 'yes';
        const needsSpecify = (v) => v === 'Other';

        if (typeContainer) {
            typeContainer.style.display = isYes ? 'block' : 'none';
        }
        if (descContainer) {
            if (!isYes) {
                descContainer.style.display = 'none';
            } else {
                const typeInput = document.getElementById('moreMedicalDisabilityTypeInput');
                descContainer.style.display = (typeInput && needsSpecify(typeInput.value)) ? 'block' : 'none';
            }
        }

        if (!isYes) {
            const select = document.getElementById('moreMedicalDisabilityTypeInput');
            if (select) select.value = '';
            const textarea = document.getElementById('moreMedicalDisabilityDescriptionInput');
            if (textarea) textarea.value = '';
        }
    }

    if (e.target && e.target.name === 'has_chronic_disease') {
        const descContainer = document.getElementById('moreMedicalChronicDiseaseDescriptionContainer');
        const isYes = e.target.value === 'yes';
        if (descContainer) {
            descContainer.style.display = isYes ? 'block' : 'none';
        }
        if (!isYes) {
            const textarea = document.getElementById('moreMedicalChronicDiseaseDescriptionInput');
            if (textarea) textarea.value = '';
        }
    }

    if (e.target && e.target.id === 'moreMedicalDisabilityTypeInput') {
        const descContainer = document.getElementById('moreMedicalDisabilityDescriptionContainer');
        const needsSpecify = e.target.value === 'Other';
        if (descContainer) {
            descContainer.style.display = needsSpecify ? 'block' : 'none';
        }
        if (!needsSpecify) {
            const textarea = document.getElementById('moreMedicalDisabilityDescriptionInput');
            if (textarea) textarea.value = '';
        }
    }

    // Medical file selection feedback
    if (e.target && e.target.id === 'moreMedicalFileInput') {
        const fileInput = e.target;
        const uploadContainer = document.getElementById('moreMedicalUploadContainer');
        if (fileInput.files && fileInput.files.length > 0) {
            const filename = fileInput.files[0].name;
            const placeholderText = uploadContainer ? uploadContainer.querySelector('.small') : null;
            const uploadIcon = uploadContainer ? uploadContainer.querySelector('i') : null;
            if (placeholderText) {
                placeholderText.textContent = filename;
                placeholderText.classList.remove('text-secondary');
                placeholderText.classList.add('text-primary', 'fw-bold');
            }
            if (uploadIcon) uploadIcon.className = 'bi bi-check-circle-fill text-success';
        }
    }
});

// â”€â”€â”€ Medical Document: Deletion Workflow â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

(function initMedicalDocHandlers() {
    const removeBtn = document.getElementById('moreMedicalDocumentRemove');
    if (!removeBtn) return;

    removeBtn.addEventListener('click', async function() {
        const viewContainer = document.getElementById('moreMedicalViewContainer');
        const uploadContainer = document.getElementById('moreMedicalUploadContainer');
        const fileInput = document.getElementById('moreMedicalFileInput');
        const attachmentId = viewContainer ? viewContainer.getAttribute('data-attachment-id') : null;

        if (!attachmentId) {
            // No saved attachment, just reset the upload UI
            if (uploadContainer) uploadContainer.classList.remove('d-none');
            if (viewContainer) viewContainer.classList.add('d-none');
            if (fileInput) {
                fileInput.value = '';
                const placeholderText = uploadContainer ? uploadContainer.querySelector('.small') : null;
                const uploadIcon = uploadContainer ? uploadContainer.querySelector('i') : null;
                if (placeholderText) {
                    placeholderText.textContent = 'No file chosen';
                    placeholderText.classList.remove('text-primary', 'fw-bold');
                    placeholderText.classList.add('text-secondary');
                }
                if (uploadIcon) uploadIcon.className = 'bi bi-upload';
            }
            if (typeof showToast === 'function') showToast('Selection cleared');
            return;
        }

        const result = await Swal.fire({
            title: 'Are you sure?',
            text: 'This medical document will be permanently deleted.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        });

        if (!result.isConfirmed) return;

        try {
            const response = await fetch('/admin/employees/delete-attachment', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ id: attachmentId })
            });
            const res = await response.json();

            if (res.success) {
                if (uploadContainer) uploadContainer.classList.remove('d-none');
                if (viewContainer) {
                    viewContainer.classList.add('d-none');
                    viewContainer.removeAttribute('data-attachment-id');
                }
                if (fileInput) fileInput.value = '';

                // Reset upload placeholder text
                if (uploadContainer) {
                    const placeholderText = uploadContainer.querySelector('.small');
                    const uploadIcon = uploadContainer.querySelector('i');
                    if (placeholderText) {
                        placeholderText.textContent = 'No file chosen';
                        placeholderText.classList.remove('text-primary', 'fw-bold');
                        placeholderText.classList.add('text-secondary');
                    }
                    if (uploadIcon) uploadIcon.className = 'bi bi-upload';
                }

                // Remove from memory
                if (window.employeeAttachments) window.employeeAttachments = window.employeeAttachments.filter(a => a.id != attachmentId);
                if (window.editData && window.editData.attachments) window.editData.attachments = window.editData.attachments.filter(a => a.id != attachmentId);

                Swal.fire({ icon: 'success', title: 'Deleted', text: 'Medical document deleted successfully', timer: 1500, showConfirmButton: false });
            } else {
                if (typeof showError === 'function') showError(res.message || 'Failed to delete document');
            }
        } catch (err) {
            if (typeof showError === 'function') showError('Network error');
        }
    });
})();

// â”€â”€â”€ Medical Document: Page-Load Restore â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

(function restoreMedicalDocument() {
    function doRestore(attachments) {
        const medicalAttachment = (attachments || []).find(a => String(a.subsection) === 'medical');
        if (!medicalAttachment) return;

        const uploadContainer = document.getElementById('moreMedicalUploadContainer');
        const viewContainer = document.getElementById('moreMedicalViewContainer');
        const viewLink = document.getElementById('moreMedicalDocumentLink');
        const filenameEl = document.getElementById('moreMedicalFilename');

        if (!viewContainer) return;

        if (uploadContainer) uploadContainer.classList.add('d-none');
        viewContainer.classList.remove('d-none');
        viewContainer.setAttribute('data-attachment-id', medicalAttachment.id);
        if (viewLink) viewLink.href = medicalAttachment.url || '#';
        if (filenameEl) filenameEl.textContent = medicalAttachment.file_name || medicalAttachment.name || 'Medical Report';
    }

    // Try static edit data first
    if (window.editData && Array.isArray(window.editData.attachments) && window.editData.attachments.length > 0) {
        doRestore(window.editData.attachments);
        return;
    }

    // Fall back to dynamically fetched attachments
    if (window.employeeAttachmentsFetchUrl) {
        fetch(window.employeeAttachmentsFetchUrl, { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(data => {
                const atts = data.attachments || data || [];
                window.employeeAttachments = atts;
                doRestore(atts);
            })
            .catch(() => {});
    }
})();