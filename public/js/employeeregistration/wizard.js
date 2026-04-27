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
    const probationEndInput = document.getElementById('employmentProbationEndDateInput');

    const orgsData = window.orgsData || [];
    const rolesData = window.rolesData || [];
    let availableDepartments = [];
    let availableFloors = [];

    function formatContactMaskInput(target) {
        if (!target || !target.classList || !target.classList.contains('contact-mask')) return;
        let val = target.value.replace(/\D/g, '');
        if (val.length > 15) {
            val = val.substring(0, 15);
        }
        target.value = val;
        if (val.length > 0 && val.length < 11) {
            target.classList.add('is-invalid');
        } else {
            target.classList.remove('is-invalid');
        }
    }

    let contactMaskDelegationBound = false;
    function initContactMasks() {
        if (!contactMaskDelegationBound) {
            contactMaskDelegationBound = true;
            document.addEventListener('input', function (e) {
                if (e.target.classList && e.target.classList.contains('contact-mask')) {
                    formatContactMaskInput(e.target);
                }
            });
            document.addEventListener('blur', function (e) {
                if (e.target.classList && e.target.classList.contains('contact-mask')) {
                    formatContactMaskInput(e.target);
                }
            }, true);
            document.addEventListener('keypress', function (e) {
                if (!e.target.classList || !e.target.classList.contains('contact-mask')) return;
                if (e.which < 48 || e.which > 57) {
                    e.preventDefault();
                }
            }, true);
        }
        document.querySelectorAll('.contact-mask').forEach(formatContactMaskInput);
    }

    window.formatContactMaskInput = formatContactMaskInput;

    initContactMasks();

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
            branch: 50,
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
            pma_lc_ots: 50,
            msr_letter_no: 20,
            addressee: 100,
            verifying_authority: 50,
            verification_letter_no: 100,
            police_remarks: 2000,
            account_title: 255,
            account_no: 16,
            bank_name: 255,
            branch_code: 50,
            branch_address: 500,
            iban: 30,
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
            name: 100,
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
                const key = fieldKeyFromName(el.name);
                const maxLen = maxLengthByField[key];
                if (maxLen) {
                    el.setAttribute('maxlength', String(maxLen));
                }
            });
        }

        function clampInitialValuesToMaxlength() {
            document.querySelectorAll('#employeeForm input[name], #employeeForm textarea[name]').forEach((el) => {
                const key = fieldKeyFromName(el.name);
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
            if (!target || !target.name) return;
            if (!target.closest('#employeeForm')) return;
            if (e.inputType && e.inputType.startsWith('delete')) return;

            const key = fieldKeyFromName(target.name);
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
            if (!target || !target.name) return;
            if (!target.closest('#employeeForm')) return;
            if (!shouldTreatAsTextInsertion(e)) return;

            const key = fieldKeyFromName(target.name);
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
            if (!target || !target.name) return;
            if (!target.closest('#employeeForm')) return;

            const key = fieldKeyFromName(target.name);
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
            if (!target || !target.name) return;
            if (!target.closest('#employeeForm')) return;

            const key = fieldKeyFromName(target.name);
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
                const unchanged = valStr === originalValue;
                const siblingGuard = target.nextElementSibling
                    && target.nextElementSibling.classList
                    && target.nextElementSibling.classList.contains('input-guard-error');
                const keepMaxLimitHint = maxLen && valStr.length >= maxLen && target.dataset.maxLimitBlocked === '1';
                const keepPendingGuard = unchanged && siblingGuard && target.classList.contains('is-invalid');
                if (!keepMaxLimitHint && !keepPendingGuard) {
                    delete target.dataset.maxLimitBlocked;
                    removeInputGuardError(target);
                }
            }
        }, true);

        // If browser blocks extra characters via native maxlength without firing input mutation,
        // keep the same inline error style as General tab.
        document.addEventListener('keyup', function (e) {
            const target = e.target;
            if (!target || !target.name) return;
            if (!target.closest('#employeeForm')) return;
            const key = fieldKeyFromName(target.name);
            const maxLen = resolveMaxLength(target, key);
            if (!maxLen) return;

            if (target.dataset.maxLimitBlocked === '1' && String(target.value ?? '').length >= maxLen) {
                showInputGuardError(target, `Maximum ${maxLen} characters allowed.`);
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
                if (msrDate && msrDate > today) {
                    showInputGuardError(msrDateInput, 'MSR date cannot be in the future.');
                } else {
                    removeInputGuardError(msrDateInput);
                }
            }

            if (letterDateInput) {
                const msrDate = parseDate(msrDateInput ? msrDateInput.value : '');
                const letterDate = parseDate(letterDateInput.value);
                if (letterDate && letterDate > today) {
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
        }, true);

        validatePoliceVerificationDateLogic();
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

    document.addEventListener('change', function(e) {
        if (e.target.id === 'giExArmyRetiredCheckbox' || e.target.id === 'giFatherDeceasedCheckbox' || e.target.id === 'giMaritalStatusSelect') {
            syncConditionalVisibility();
        }
        if (e.target.name === 'verification_status') {
            togglePoliceVerificationFields();
        }
    });

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
        'probation_start_date':         'employmentProbationStartDateInput',
        'probation_end_date':           'employmentProbationEndDateInput',
        'probation_contract_start_date': 'employmentProbationContractStartDateInput',
        'assigned_floor_ids':           'employmentAssignedFloorsSelect',
        'working_start_time':           'employmentCustomWorkingStartInput',
        'working_end_time':             'employmentCustomWorkingEndInput',
        'opening_grace_period':         'employmentCustomCheckInGraceInput',
        'closing_grace_period':         'employmentCustomCheckOutGraceInput',
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
        if (prevBtn) prevBtn.style.visibility = currentStep === 1 ? 'hidden' : 'visible';
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

    const employmentCodeInput = document.getElementById('employmentEmployeeNumberInput');
    const orgSelectForCode = document.getElementById('employmentOrganizationSelect');
    const roleSelectForCode = document.getElementById('employmentRoleSelect');
    const sbuSelectForCode = document.getElementById('employmentSbuSelect');
    let employeeCodePreviewTimer = null;

    function updateEmployeeCodePreview() {
        if (!employmentCodeInput) return;
        if (!window.previewEmployeeCodeUrl) return;

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

    async function processStepSave(step, onSuccess) {
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
            const subsystems = ['family', 'academic', 'employment'];
            subsystems.forEach(sub => {
                const containerId = {
                    'family': 'moreFamilyMembersContainer',
                    'academic': 'moreAcademicRecordsContainer',
                    'employment': 'moreEmploymentRecordsContainer'
                }[sub];
                const container = document.getElementById(containerId);
                if (container) {
                    const rows = container.querySelectorAll(`[data-${sub}-row]`);
                    rows.forEach((row, index) => {
                        const dbId = row.getAttribute('data-db-id');
                        if (dbId) formData.append(`${sub === 'employment' ? 'employments' : sub}[${index}][id]`, dbId);
                        
                        row.querySelectorAll('input, select, textarea').forEach(input => {
                            const name = input.getAttribute('name');
                            if (name) {
                                const cleanKey = name.match(/\[([^\]]*)\]$/)?.[1] || name;
                                if (cleanKey) formData.append(`${sub === 'employment' ? 'employments' : sub}[${index}][${cleanKey}]`, input.value);
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
        const originalText = nextBtn.textContent;

        nextBtn.disabled = true;
        nextBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';
        if (prevBtn) prevBtn.disabled = true;

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
                        window.setMoreSubStep(6);
                    }
                }
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'error',
                    title: 'Validation Failed',
                    text: 'Please check the highlighted fields.',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
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
            nextBtn.disabled = false;
            nextBtn.textContent = originalText;
            if (prevBtn) prevBtn.disabled = false;
            syncStepUi();
        }
    }

    // Event Listeners
    document.querySelectorAll('.profile-tab').forEach((tab) => {
        tab.addEventListener('click', function () {
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

    document.getElementById('nextBtn').addEventListener('click', function () {
        if (currentStep === 6) {
            const isLastMoreStep = typeof window.isLastMoreStep === 'function' ? window.isLastMoreStep() : true;
            if (!isLastMoreStep) {
                // If it's a "static" more subsection, save it first
                const moreStep = currentMoreStep;
                if ([1, 5, 6].includes(moreStep)) {
                    saveMoreSubSection(moreStep, () => {
                        if (typeof window.nextMoreSubStep === 'function') {
                            window.nextMoreSubStep();
                            syncStepUi();
                            window.scrollTo({ top: 0, behavior: 'smooth' });
                        }
                    });
                    return;
                }
                
                // For dynamic rows (2,3,4), just move next (they have independent save buttons)
                if (typeof window.nextMoreSubStep === 'function') {
                    window.nextMoreSubStep();
                    syncStepUi();
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                    return;
                }
            }
        }

        if (currentStep < totalSteps) {
            processStepSave(currentStep, () => {
                currentStep = getNextStepAfter(currentStep);
                syncStepUi();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        } else {
            // Final submission
            processStepSave(currentStep); 
        }
    });

    async function saveMoreSubSection(step, onSuccess) {
        const typeMap = { 1: 'contact', 5: 'medical', 6: 'references' };
        const subsection = typeMap[step];
        if (!subsection) {
            if (onSuccess) onSuccess();
            return;
        }

        const employeeId = document.getElementById('saved_employee_id')?.value;
        if (!employeeId) return showError('Save general information first.');

        const nextBtn = document.getElementById('nextBtn');
        const originalText = nextBtn.textContent;
        nextBtn.disabled = true;
        nextBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>Saving...';

        const form = document.getElementById('employeeForm');
        const formData = new FormData(form);
        formData.append('subsection', subsection);
        formData.append('employee_id', employeeId);

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
                        window.setMoreSubStep(6);
                    }
                }
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'error',
                    title: 'Validation Failed',
                    text: 'Please check the highlighted fields.',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });
            } else if (res.success) {
                showToast(`${subsection.charAt(0).toUpperCase() + subsection.slice(1)} information saved successfully`);
                if (onSuccess) onSuccess();
            } else {
                showError(res.message);
            }
        } catch (e) { showError('Network error'); }
        finally {
            nextBtn.disabled = false;
            nextBtn.textContent = originalText;
        }
    }

    document.getElementById('prevBtn').addEventListener('click', function () {
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

        const maxSize = 2 * 1024 * 1024; // 2MB
        if (file.size > maxSize) {
            showError('Maximum allowed file size is 2MB.', 'File Too Large');
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

    // ─── Work Arrangement Toggles ────────────────────────────────────────────────

    function getScheduleSource() {
        // Look up live at call time — avoids temporal dead zone with orgSelect/sbuSelect consts below
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
        const checkIn     = document.getElementById('employmentDefaultCheckInGrace');
        const checkOut    = document.getElementById('employmentDefaultCheckOutGrace');

        if (!src) {
            if (orgInitial) orgInitial.textContent = '-';
            if (orgName)    orgName.textContent    = '-';
            if (wkDays)     wkDays.textContent     = '- - -';
            if (wkTime)     wkTime.textContent     = '- - -';
            if (checkIn)    checkIn.textContent    = '-';
            if (checkOut)   checkOut.textContent   = '-';
            return;
        }

        const d = src.data;
        if (orgInitial) orgInitial.textContent = src.initial;
        if (orgName)    orgName.textContent    = src.label;
        if (wkDays)     wkDays.textContent     = formatDaysList(d.working_days);
        if (wkTime)     wkTime.textContent     = (d.working_start_time && d.working_end_time)
                                                    ? `${formatTime(d.working_start_time)} – ${formatTime(d.working_end_time)}`
                                                    : '- - -';
        if (checkIn)    checkIn.textContent    = d.opening_grace_period != null ? `${d.opening_grace_period} min` : '-';
        if (checkOut)   checkOut.textContent   = d.closing_grace_period != null ? `${d.closing_grace_period} min` : '-';
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

    // ─── End Work Arrangement Toggles ────────────────────────────────────────────

    // Location Dependent Selects (Nationality -> Province -> District)
    async function loadLocationData(select, url, currentValue = null) {
        if (!select) return;

        // Reset and show loading state
        const originalText = select.options[0].text;
        select.options[0].text = 'Loading...';
        select.disabled = true;

        try {
            const response = await fetch(url);
            const data = await response.json();

            // Clear except first option
            while (select.options.length > 1) {
                select.remove(1);
            }

            data.forEach(item => {
                const option = new Option(item.name, item.name);
                select.add(option);
            });

            // Restore placeholder
            select.options[0].text = originalText;
            select.disabled = false;

            // Pre-selection logic
            const valToSelect = currentValue || select.getAttribute('data-current-value');
            if (valToSelect) {
                select.value = valToSelect;
                // Trigger change to update dependent selects
                if (select.value === valToSelect) {
                    select.dispatchEvent(new Event('change'));
                }
            }

        } catch (error) {
            console.error('Failed to load location data:', error);
            select.options[0].text = 'Error loading data';
        }
    }

    function initLocationSelectors() {
        const selects = document.querySelectorAll('.location-select');
        const nationalitySelect = document.getElementById('giNationalityInput');
        const provinceSelect = document.getElementById('giProvinceSelect');
        const districtSelect = document.getElementById('giDistrictSelect');

        if (nationalitySelect) {
            // Load Countries (Nationality) initially
            loadLocationData(nationalitySelect, '/admin/locations/countries');
        }

        const spouseNationalitySelect = document.getElementById('giSpouseNationalityInput');
        if (spouseNationalitySelect) {
            loadLocationData(spouseNationalitySelect, '/admin/locations/countries');
        }

        if (nationalitySelect) {
            nationalitySelect.addEventListener('change', function() {
                const countryName = this.value;
                if (provinceSelect) {
                    // Reset district when nationality changes
                    if (districtSelect) {
                        while (districtSelect.options.length > 1) districtSelect.remove(1);
                        districtSelect.selectedIndex = 0;
                    }

                    if (countryName) {
                        loadLocationData(provinceSelect, `/admin/locations/provinces/${encodeURIComponent(countryName)}`);
                    } else {
                        while (provinceSelect.options.length > 1) provinceSelect.remove(1);
                        provinceSelect.selectedIndex = 0;
                    }
                }
            });
        }

        if (provinceSelect) {
            provinceSelect.addEventListener('change', function() {
                const provinceName = this.value;
                const countryName = nationalitySelect ? nationalitySelect.value : null;

                if (districtSelect) {
                    if (provinceName && countryName) {
                        loadLocationData(districtSelect, `/admin/locations/districts/${encodeURIComponent(countryName)}/${encodeURIComponent(provinceName)}`);
                    } else {
                        while (districtSelect.options.length > 1) districtSelect.remove(1);
                        districtSelect.selectedIndex = 0;
                    }
                }
            });
        }
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
        });
        // Run on page load for edit mode
        if (roleSelect.value) updateDeptRequired(roleSelect.value);
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
        document.getElementById('bankDetailsBranchNameInput').value = '';
        document.getElementById('bankDetailsBranchCodeInput').value = '';
        document.getElementById('bankDetailsBranchAddressInput').value = '';
        
        if (document.getElementById('accountCategoryPersonal')) document.getElementById('accountCategoryPersonal').checked = true;
        if (document.getElementById('salaryAccountNo')) document.getElementById('salaryAccountNo').checked = true;
        if (document.getElementById('bankDetailsAccountTypeSaving')) document.getElementById('bankDetailsAccountTypeSaving').checked = true;
        
        document.getElementById('bankResetBtn').classList.add('d-none');
        document.querySelectorAll('#bankEntryForm .is-invalid').forEach(el => el.classList.remove('is-invalid'));
    };

    function collectStep5Data() {
        const banks = [];
        const editingId = document.getElementById('bank_detail_id').value;
        
        savedBanks.forEach(b => {
             if (b.id != editingId) banks.push(b);
        });

        const title = document.getElementById('bankDetailsAccountTitleInput').value.trim();
        const no = document.getElementById('bankDetailsAccountNumberInput').value.trim();
        if (title !== '' && no !== '') {
            banks.push({
                id: editingId || null,
                account_category: document.querySelector('input[name="account_category"]:checked')?.value || 'Personal',
                account_title: title,
                account_no: no,
                iban: document.getElementById('bankDetailsIbanInput').value,
                bank_name: document.getElementById('bankDetailsBranchNameInput').value,
                branch_code: document.getElementById('bankDetailsBranchCodeInput').value,
                branch_address: document.getElementById('bankDetailsBranchAddressInput').value,
                account_type: document.querySelector('input[name="account_type"]:checked')?.value || 'Saving',
                is_salary_account: document.querySelector('input[name="is_salary_account"]:checked')?.value === '1'
            });
        }
        return banks;
    }

    // Capture original processStepSave if we haven't already
    const baseProcessStepSave = window.processStepSave;

    window.processStepSave = function(step, onSuccess) {
        if (step === 5) {
            const banks = collectStep5Data();
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
                        formData.append(`banks[${index}][${key}]`, value);
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
        const employeeId = document.getElementById('saved_employee_id')?.value;
        if (!employeeId) {
            showError('Please save the "General Information" step first.');
            return;
        }

        const bankId = document.getElementById('bank_detail_id').value;
        const payload = {
            employee_id: employeeId,
            subsection: 'bank_row',
            bank_detail_id: bankId,
            account_category: document.querySelector('input[name="account_category"]:checked')?.value || 'Personal',
            account_title: document.getElementById('bankDetailsAccountTitleInput').value,
            account_no: document.getElementById('bankDetailsAccountNumberInput').value,
            iban: document.getElementById('bankDetailsIbanInput').value,
            bank_name: document.getElementById('bankDetailsBranchNameInput').value,
            branch_code: document.getElementById('bankDetailsBranchCodeInput').value,
            branch_address: document.getElementById('bankDetailsBranchAddressInput').value,
            account_type: document.querySelector('input[name="account_type"]:checked')?.value || 'Saving',
            is_salary_account: document.querySelector('input[name="is_salary_account"]:checked')?.value === '1'
        };

        const saveBtn = document.querySelector('button[onclick="saveBankDetail()"]');
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

            const data = await response.json();

            if (response.status === 422) {
                showFieldErrors(data.errors);
            } else if (data.success) {
                Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Saved', showConfirmButton: false, timer: 2000 });
                
                const rec = {
                    id: data.id || bankId,
                    account_category: payload.account_category,
                    account_title: payload.account_title,
                    account_no: payload.account_no,
                    iban: payload.iban,
                    bank_name: payload.bank_name,
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
            }
        } catch (error) { showError('Network error'); }
        finally {
            saveBtn.disabled = false;
            saveBtn.innerHTML = originalText;
        }
    };

    window.editBankDetail = function(id) {
        const bank = savedBanks.find(b => b.id == id);
        if (!bank) return;

        resetBankForm();
        document.getElementById('bank_detail_id').value = bank.id;
        document.getElementById('bankDetailsAccountTitleInput').value = bank.account_title || '';
        document.getElementById('bankDetailsAccountNumberInput').value = bank.account_no || '';
        document.getElementById('bankDetailsIbanInput').value = bank.iban || '';
        document.getElementById('bankDetailsBranchNameInput').value = bank.bank_name || '';
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
            clone.querySelector('.bank-sub-label').innerText = `Source: ${bank.bank_name || 'N/A'} - (${bank.account_category || '-'})`;
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
                 
                 showError('At least one bank account is required.', 'Validation Error');
                 return;
            }
            
            const hasSalaryAccount = savedBanks.some(b => b.is_salary_account);
            if (!hasSalaryAccount) {
                e.stopImmediatePropagation();
                showError('One bank account must be marked as the Salary Account (Primary).', 'Validation Error');
                return;
            }
        }
    }, true);

    // --- STEP 6: MORE INFORMATION LOGIC ---
    let currentMoreStep = 1;
    const totalMoreSteps = 7;

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
            window.setMoreSubStep(parseInt(this.getAttribute('data-more-step')));
        });
    });

    // --- Subsection Management Helpers (Family, Academic, Employment) ---

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
    }

    async function saveSubsectionRow(type, rowElement) {
        const isPreview = rowElement.classList.contains('preview-mode');
        if (isPreview) {
            setSubsectionRowMode(rowElement, type, false);
            return;
        }

        const employeeId = document.getElementById('saved_employee_id')?.value;
        if (!employeeId) return showError('Save general information first.');

        const saveBtn = rowElement.querySelector(`[data-${type}-save]`);
        const originalHtml = saveBtn.innerHTML;
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

        // Collect inputs in this row
        const data = { employee_id: employeeId, subsection: `${type}_row` };
        if (rowElement.getAttribute('data-db-id')) {
            data[`${type}_id`] = rowElement.getAttribute('data-db-id');
        }

        rowElement.querySelectorAll('input, select, textarea').forEach(input => {
            const name = input.getAttribute('name');
            if (name) {
                if (name === 'family_nok_selector') {
                    return;
                }
                const cleanKey = name.match(/\[([^\]]*)\]$/)?.[1] || name;
                if (cleanKey) data[cleanKey] = input.value;
            }
        });

        try {
            const response = await fetch('/admin/employees/save-subsection', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });

            const res = await response.json();
            if (response.status === 422) {
                showFieldErrors(res.errors, rowElement);
                saveBtn.innerHTML = originalHtml;
            } else if (res.success) {
                // Remove any remaining visual errors on successful save
                rowElement.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                rowElement.querySelectorAll('.field-error-msg').forEach(err => err.remove());
                
                if (res.id) rowElement.setAttribute('data-db-id', res.id);
                showToast(`${type.charAt(0).toUpperCase() + type.slice(1)} record saved successfully`);
                updateSubsectionPreview(rowElement, type);
                setSubsectionRowMode(rowElement, type, true);
                rowElement.classList.add('saved-row');
            } else {
                showError(res.message);
                saveBtn.innerHTML = originalHtml;
            }
        } catch (e) { 
            showError('Network error'); 
            saveBtn.innerHTML = originalHtml;
        } finally {
            saveBtn.disabled = false;
        }
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
                    showToast(`${type.charAt(0).toUpperCase() + type.slice(1)} member deleted successfully`);
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
            'employment': 'moreEmploymentRecordsContainer'
        }[type];
        
        const countId = {
            'family': 'moreFamilyMemberCount',
            'academic': 'moreAcademicRecordCount',
            'employment': 'moreEmploymentRecordCount'
        }[type];

        const container = document.getElementById(containerId);
        const rows = container.querySelectorAll(`[data-${type}-row]`);
        if (type === 'family') {
            rows.forEach((row, idx) => {
                const indexSpan = row.querySelector('[data-family-index]');
                if (indexSpan) indexSpan.textContent = 'Member ' + String(idx + 1);
                const removeBtn = row.querySelector('[data-family-remove]');
                if (removeBtn) removeBtn.disabled = rows.length === 1;
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
            const removeBtn = row.querySelector('.family-nok-remove');
            const memberIndicator = row.querySelector('[data-family-nok-member-indicator]');
            const lockedNote = row.querySelector('[data-family-nok-locked-note]');
            const helperText = row.querySelector('[data-family-nok-helper]');
            const titleText = row.querySelector('[data-family-nok-title]');
            const isOn = radio && radio.checked;
            const isLockedForOther = !!selectedRow && selectedRow !== row && !isOn;

            if (toggleCard) {
                toggleCard.classList.toggle('d-none', isLockedForOther);
            }
            if (titleText) {
                titleText.classList.toggle('d-none', isLockedForOther);
            }
            if (lockedNote) {
                lockedNote.classList.toggle('d-none', !isLockedForOther);
            }
            if (helperText) {
                helperText.classList.toggle('d-none', isLockedForOther);
            }

            if (hidden) hidden.value = isOn ? '1' : '0';
            if (block) block.classList.toggle('d-none', !isOn);
            if (badge) badge.classList.toggle('d-none', !isOn);
            if (selectedBadge) selectedBadge.classList.toggle('d-none', !isOn);
            if (toggleCard) toggleCard.classList.toggle('active', !!isOn);
            if (removeBtn) removeBtn.classList.toggle('d-none', !isOn);
            if (memberIndicator) memberIndicator.classList.toggle('d-none', !isOn);
            row.querySelectorAll('[data-family-nok-input]').forEach(function (inp) {
                inp.required = !!isOn;
            });
        });
    }

    window.ensureFamilyNokBeforeStepSave = syncFamilyNokFromRadios;

    const moreFamilyMembersContainerEl = document.getElementById('moreFamilyMembersContainer');
    if (moreFamilyMembersContainerEl) {
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
                        radio.checked = !radio.checked;
                    }
                    syncFamilyNokFromRadios();
                }
                return;
            }
        });
        moreFamilyMembersContainerEl.addEventListener('click', async function (e) {
            const removeBtn = e.target.closest('.family-nok-remove');
            if (!removeBtn) {
                return;
            }
            e.preventDefault();
            const row = removeBtn.closest('[data-family-row]');
            if (!row) return;
            const result = await showConfirm('Are you sure you want to remove this member as Next of Kin?', 'Remove Next of Kin');
            if (!result.isConfirmed) {
                return;
            }

            const radio = row.querySelector('.family-nok-selector');
            if (radio) {
                radio.checked = false;
            }
            row.querySelectorAll('[data-family-nok-input]').forEach(function (inp) {
                inp.value = '';
            });
            syncFamilyNokFromRadios();
            updateSubsectionPreview(row, 'family');

            if (row.getAttribute('data-db-id')) {
                setSubsectionRowMode(row, 'family', false);
                await saveSubsectionRow('family', row);
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

        if (data) {
            row.setAttribute('data-db-id', data.id || '');
            if (data.degree) row.querySelector('[data-academic-degree]').value = data.degree;
            if (data.grade_cgpa) row.querySelector('[data-academic-grade]').value = data.grade_cgpa;
            if (data.start_date) row.querySelector('[data-academic-start-date]').value = data.start_date;
            if (data.end_date) row.querySelector('[data-academic-end-date]').value = data.end_date;
            if (data.fieldOfStudy || data.field_of_study) row.querySelector('[data-academic-field-of-study]').value = data.fieldOfStudy || data.field_of_study;
            if (data.institute) row.querySelector('[data-academic-institute]').value = data.institute;
        }

        row.querySelector('[data-academic-save]').onclick = () => saveSubsectionRow('academic', row);
        row.querySelector('[data-academic-remove]').onclick = () => removeSubsectionRow('academic', row);

        container.appendChild(clone);
        if (data && data.id) {
            updateSubsectionPreview(row, 'academic');
            setSubsectionRowMode(row, 'academic', true);
        }
        updateRowIndices('academic');
    };

    const addAcademicBtn = document.getElementById('moreAcademicAddRecordBtn');
    if (addAcademicBtn) addAcademicBtn.onclick = () => addAcademicRecord();

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
        }

        row.querySelector('[data-employment-save]').onclick = () => saveSubsectionRow('employment', row);
        row.querySelector('[data-employment-remove]').onclick = () => removeSubsectionRow('employment', row);

        container.appendChild(clone);
        if (data && data.id) {
            updateSubsectionPreview(row, 'employment');
            setSubsectionRowMode(row, 'employment', true);
        }
        updateRowIndices('employment');
    };

    const addEmploymentBtn = document.getElementById('moreEmploymentAddRecordBtn');
    if (addEmploymentBtn) addEmploymentBtn.onclick = () => addEmploymentRecord();

    // --- Error Clearing on Input ---
    const mainForm = document.getElementById('employeeForm');
    if (mainForm) {
        const clearLocalError = function(e) {
            const target = e.target;
            if (target && target.classList.contains('is-invalid')) {
                const container = target.closest('[class^="col-"], [class*=" col-"], .col, .form-group, .mb-3');
                if (container && container.querySelector('.input-guard-error')) {
                    return;
                }

                target.classList.remove('is-invalid');

                // Clear sibling/nearby error message
                if (container) {
                    const err = container.querySelector('.field-error-msg');
                    if (err) err.remove();
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
    }
})();

if (typeof window.setExistingAttachments === 'function' && window.editData && Array.isArray(window.editData.attachments)) {
    window.setExistingAttachments(window.editData.attachments);
}

// --- Medical disability toggling (Robust version) ---
document.addEventListener('change', function(e) {
    if (e.target && e.target.name === 'has_disability') {
        const typeContainer = document.getElementById('moreMedicalDisabilityTypeContainer');
        const descContainer = document.getElementById('moreMedicalDisabilityDescriptionContainer');
        const isYes = e.target.value === 'yes';
        
        if (typeContainer) {
            typeContainer.style.display = isYes ? 'block' : 'none';
        }
        if (descContainer) {
            if (!isYes) {
                descContainer.style.display = 'none';
            } else {
                const typeInput = document.getElementById('moreMedicalDisabilityTypeInput');
                descContainer.style.display = (typeInput && typeInput.value === 'Other') ? 'block' : 'none';
            }
        }

        if (!isYes) {
            const select = document.getElementById('moreMedicalDisabilityTypeInput');
            if (select) select.value = '';
            const textarea = document.getElementById('moreMedicalDisabilityDescriptionInput');
            if (textarea) textarea.value = '';
        }
    }

    if (e.target && e.target.id === 'moreMedicalDisabilityTypeInput') {
        const descContainer = document.getElementById('moreMedicalDisabilityDescriptionContainer');
        if (descContainer) {
            descContainer.style.display = e.target.value === 'Other' ? 'block' : 'none';
        }
        if (e.target.value !== 'Other') {
            const textarea = document.getElementById('moreMedicalDisabilityDescriptionInput');
            if (textarea) textarea.value = '';
        }
    }
});

