(function () {
    'use strict';

let currentStep = 1;
    const totalSteps = 6;
    
    function syncStepUi() {
        for (let i = 1; i <= totalSteps; i++) {
            const pane = document.getElementById('stepPane' + i);
            pane.classList.toggle('active', i === currentStep);
        }
    
        const tabs = document.querySelectorAll('.profile-tab');
        tabs.forEach((tab) => {
            const step = Number(tab.getAttribute('data-step'));
            tab.classList.remove('active');
            if (step === currentStep) tab.classList.add('active');
        });
    
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');
        prevBtn.style.visibility = currentStep === 1 ? 'hidden' : 'visible';
        nextBtn.textContent = currentStep === totalSteps ? 'Finish' : 'Next';
    }
    
    document.querySelectorAll('.profile-tab').forEach((tab) => {
        tab.addEventListener('click', function() {
            currentStep = Number(this.getAttribute('data-step'));
            syncStepUi();
        });
    });
    
    document.getElementById('nextBtn').addEventListener('click', function() {
        if (currentStep < totalSteps) {
            currentStep += 1;
            syncStepUi();
        } else {
            this.disabled = true;
            this.classList.remove('bg-main');
            this.classList.add('btn-success');
            this.textContent = 'Completed';
        }
    });
    
    document.getElementById('prevBtn').addEventListener('click', function() {
        if (currentStep > 1) {
            currentStep -= 1;
            syncStepUi();
        }
    });
    
    const profilePhotoInput = document.getElementById('profilePhotoInput');
    const avatarPreviewImage = document.getElementById('avatarPreviewImage');
    const avatarPlaceholderIcon = document.getElementById('avatarPlaceholderIcon');
    
    if (profilePhotoInput && avatarPreviewImage && avatarPlaceholderIcon) {
        profilePhotoInput.addEventListener('change', function(e) {
            const file = e.target.files && e.target.files[0] ? e.target.files[0] : null;
            if (!file) return;
            const objectUrl = URL.createObjectURL(file);
            avatarPreviewImage.src = objectUrl;
            avatarPreviewImage.classList.remove('d-none');
            avatarPreviewImage.classList.add('d-block');
            avatarPlaceholderIcon.classList.add('d-none');
        });
    }

    const giFatherDeceasedCheckbox = document.getElementById('giFatherDeceasedCheckbox');
    const giFatherCnicField = document.getElementById('giFatherCnicField');
    const giFatherCnicInput = document.getElementById('giFatherCnicInput');

    function syncFatherCnicVisibility() {
        if (!giFatherDeceasedCheckbox || !giFatherCnicField) return;
        const hideFatherCnic = giFatherDeceasedCheckbox.checked;
        giFatherCnicField.classList.toggle('d-none', hideFatherCnic);
        if (hideFatherCnic && giFatherCnicInput) {
            giFatherCnicInput.value = '';
        }
    }

    if (giFatherDeceasedCheckbox) {
        giFatherDeceasedCheckbox.addEventListener('change', syncFatherCnicVisibility);
        syncFatherCnicVisibility();
    }

    const giNokRelationSelect = document.getElementById('giNokRelationSelect');
    const giNokSpecifyRelationField = document.getElementById('giNokSpecifyRelationField');
    const giNokSpecifyRelationInput = document.getElementById('giNokSpecifyRelationInput');

    function syncNokSpecifyRelationField() {
        if (!giNokRelationSelect || !giNokSpecifyRelationField) return;
        const isOtherSelected = giNokRelationSelect.value === 'Other';
        giNokSpecifyRelationField.classList.toggle('d-none', !isOtherSelected);
        if (giNokSpecifyRelationInput) {
            giNokSpecifyRelationInput.required = isOtherSelected;
            if (!isOtherSelected) {
                giNokSpecifyRelationInput.value = '';
            }
        }
    }

    if (giNokRelationSelect) {
        giNokRelationSelect.addEventListener('change', syncNokSpecifyRelationField);
        syncNokSpecifyRelationField();
    }

    function employmentDeptGetSelect() {
        return document.getElementById('employmentDepartmentSelect');
    }

    function employmentDeptRenderFromSelect() {
        const deptSel = employmentDeptGetSelect();
        const chips = document.getElementById('employmentDeptChips');
        const ph = document.getElementById('employmentDeptPh');
        if (!deptSel || !chips) return;
        const selected = Array.from(deptSel.selectedOptions || []);
        if (!selected.length) {
            chips.innerHTML = '';
            if (ph) ph.style.display = '';
            return;
        }
        if (ph) ph.style.display = 'none';
        chips.innerHTML = selected
            .map(function (opt) {
                const val = String(opt.value || '');
                const text = String(opt.textContent || '');
                if (!val) {
                    return '<span class="emp-dept-chip">' + text + '</span>';
                }
                return '<span class="emp-dept-chip">' + text + '<span class="emp-dept-chip-x" onclick="employmentDeptRemoveId(\'' + val + '\', event)">×</span></span>';
            })
            .join('');
    }

    window.employmentDeptRenderList = function employmentDeptRenderList() {
        const deptSel = employmentDeptGetSelect();
        const list = document.getElementById('employmentDeptList');
        const search = document.getElementById('employmentDeptSearch');
        if (!deptSel || !list) return;
        const q = String((search && search.value) || '').toLowerCase().trim();
        const options = Array.from(deptSel.options || []).filter(function (opt) {
            return opt.value && (!q || String(opt.textContent || '').toLowerCase().indexOf(q) !== -1);
        });
        if (!options.length) {
            list.innerHTML = '<div class="emp-dept-no-result">No departments under this SBU</div>';
            return;
        }
        list.innerHTML = options
            .map(function (opt) {
                const picked = !!opt.selected;
                return '<div class="emp-dept-opt ' + (picked ? 'picked' : '') + '" onclick="employmentDeptToggleId(\'' + String(opt.value || '') + '\')"><span class="emp-dept-opt-cb"><svg class="emp-dept-opt-ck" viewBox="0 0 16 16" fill="none"><path d="M3.5 8.2l3 3L12.5 5" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></span><span class="emp-dept-opt-name">' + String(opt.textContent || '') + '</span></div>';
            })
            .join('');
    };

    window.employmentDeptToggleId = function employmentDeptToggleId(id) {
        const deptSel = employmentDeptGetSelect();
        if (!deptSel) return;
        const opt = deptSel.querySelector('option[value="' + String(id) + '"]');
        if (!opt) return;
        opt.selected = !opt.selected;
        employmentDeptRenderFromSelect();
        window.employmentDeptRenderList();
    };

    window.employmentDeptRemoveId = function employmentDeptRemoveId(id, e) {
        if (e) e.stopPropagation();
        const deptSel = employmentDeptGetSelect();
        if (!deptSel) return;
        const opt = deptSel.querySelector('option[value="' + String(id) + '"]');
        if (opt) opt.selected = false;
        employmentDeptRenderFromSelect();
        window.employmentDeptRenderList();
    };

    window.employmentDeptBoxClick = function employmentDeptBoxClick(e) {
        if (e) e.stopPropagation();
        const dd = document.getElementById('employmentDeptDd');
        const box = document.getElementById('employmentDeptBox');
        if (!dd || !box) return;
        const isOpen = dd.style.display !== 'none';
        if (!isOpen) {
            dd.style.display = '';
            box.classList.add('open');
            window.employmentDeptRenderList();
            const search = document.getElementById('employmentDeptSearch');
            if (search) setTimeout(function () { search.focus(); }, 0);
        } else {
            dd.style.display = 'none';
            box.classList.remove('open');
        }
    };

    document.addEventListener('click', function (evt) {
        const dd = document.getElementById('employmentDeptDd');
        const box = document.getElementById('employmentDeptBox');
        if (!dd || !box) return;
        if (!dd.contains(evt.target) && !box.contains(evt.target)) {
            dd.style.display = 'none';
            box.classList.remove('open');
        }
    });

    const employmentDeptSearch = document.getElementById('employmentDeptSearch');
    if (employmentDeptSearch) {
        employmentDeptSearch.addEventListener('keydown', function (evt) {
            evt.stopPropagation();
        });
    }
    employmentDeptRenderFromSelect();
    window.employmentDeptRenderList();
    
    const employmentDetailsCategoryIntern = document.getElementById('employmentDetailsCategoryIntern');
    const employmentDetailsCategoryContractual = document.getElementById('employmentDetailsCategoryContractual');
    const employmentDetailsCategoryEngagement = document.getElementById('employmentDetailsCategoryEngagement');
    const employmentDetailsInternFields = document.getElementById('employmentDetailsInternFields');
    const employmentDetailsContractualFields = document.getElementById('employmentDetailsContractualFields');
    const employmentDetailsEngagementFields = document.getElementById('employmentDetailsEngagementFields');
    const employmentDetailsInternTypeInput = document.getElementById('employmentDetailsInternTypeInput');
    const employmentDetailsEmployeeNumberInput = document.getElementById('employmentDetailsEmployeeNumberInput');
    const employmentDetailsContractStartDateInput = document.getElementById('employmentDetailsContractStartDateInput');
    const employmentDetailsContractEndDateInput = document.getElementById('employmentDetailsContractEndDateInput');
    const employmentDetailsContractualEmployeeNumberInput = document.getElementById('employmentDetailsContractualEmployeeNumberInput');
    const employmentDetailsEngagementModeInput = document.getElementById('employmentDetailsEngagementModeInput');
    const employmentDetailsEmployeeContractTypeField = document.getElementById('employmentDetailsEmployeeContractTypeField');
    const employmentDetailsEmployeeContractTypeInput = document.getElementById('employmentDetailsEmployeeContractTypeInput');
    const employmentDetailsEmployeeContractDatesField = document.getElementById('employmentDetailsEmployeeContractDatesField');
    const employmentDetailsEmployeeContractStartDateInput = document.getElementById('employmentDetailsEmployeeContractStartDateInput');
    const employmentDetailsEmployeeContractEndDateInput = document.getElementById('employmentDetailsEmployeeContractEndDateInput');
    const employmentDetailsEngagementEmployeeNumberInput = document.getElementById('employmentDetailsEngagementEmployeeNumberInput');
    const employmentDetailsCategoryInputs = document.querySelectorAll('input[name="employmentCategory"]');
    const employmentWorkArrangementStandard = document.getElementById('employmentWorkArrangementStandard');
    const employmentWorkArrangementHybrid = document.getElementById('employmentWorkArrangementHybrid');
    const employmentWorkArrangementStandardFields = document.getElementById('employmentWorkArrangementStandardFields');
    const employmentWorkArrangementStandardTypeDefault = document.getElementById('employmentWorkArrangementStandardTypeDefault');
    const employmentWorkArrangementStandardTypeCustom = document.getElementById('employmentWorkArrangementStandardTypeCustom');
    const employmentWorkArrangementInputs = document.querySelectorAll('input[name="employmentWorkArrangement"]');
    const employmentWorkArrangementStandardTypeInputs = document.querySelectorAll('input[name="employmentWorkArrangementStandardType"]');
    const employmentWorkArrangementDefaultCardWrap = document.getElementById('employmentWorkArrangementDefaultCardWrap');
    const employmentWorkArrangementCustomFields = document.getElementById('employmentWorkArrangementCustomFields');
    const employmentWorkArrangementHybridFields = document.getElementById('employmentWorkArrangementHybridFields');
    const employmentWorkArrangementOrgInitial = document.getElementById('employmentWorkArrangementOrgInitial');
    const employmentWorkArrangementOrgName = document.getElementById('employmentWorkArrangementOrgName');
    const employmentOrganizationSelect = document.getElementById('employmentOrganizationSelect');
    const employmentCustomWorkingStartInput = document.getElementById('employmentCustomWorkingStartInput');
    const employmentCustomWorkingEndInput = document.getElementById('employmentCustomWorkingEndInput');
    const employmentCustomCheckInGraceInput = document.getElementById('employmentCustomCheckInGraceInput');
    const employmentCustomCheckOutGraceInput = document.getElementById('employmentCustomCheckOutGraceInput');
    const employmentCustomDayInputs = document.querySelectorAll('input[name="employment_custom_days[]"]');
    const employmentHybridDayInputs = document.querySelectorAll('input[name="employment_hybrid_days[]"]');
    
    function toggleEmploymentCategoryFields() {
        if (!employmentDetailsCategoryIntern || !employmentDetailsInternFields || !employmentDetailsContractualFields || !employmentDetailsEngagementFields) return;
        const showInternFields = employmentDetailsCategoryIntern.checked;
        const showContractualFields = employmentDetailsCategoryContractual ? employmentDetailsCategoryContractual.checked : false;
        const showEngagementFields = employmentDetailsCategoryEngagement ? employmentDetailsCategoryEngagement.checked : false;
        employmentDetailsInternFields.classList.toggle('d-none', !showInternFields);
        employmentDetailsContractualFields.classList.toggle('d-none', !showContractualFields);
        employmentDetailsEngagementFields.classList.toggle('d-none', !showEngagementFields);
    
        if (employmentDetailsInternTypeInput) {
            employmentDetailsInternTypeInput.required = showInternFields;
            if (!showInternFields) {
                employmentDetailsInternTypeInput.value = '';
            }
        }
    
        if (employmentDetailsEmployeeNumberInput) {
            employmentDetailsEmployeeNumberInput.required = showInternFields;
            if (!showInternFields) {
                employmentDetailsEmployeeNumberInput.value = '';
            }
        }
    
        if (employmentDetailsContractStartDateInput) {
            employmentDetailsContractStartDateInput.required = showContractualFields;
            if (!showContractualFields) {
                employmentDetailsContractStartDateInput.value = '';
            }
        }

        if (employmentDetailsContractEndDateInput) {
            employmentDetailsContractEndDateInput.required = showContractualFields;
            if (!showContractualFields) {
                employmentDetailsContractEndDateInput.value = '';
            }
        }
    
        if (employmentDetailsContractualEmployeeNumberInput) {
            employmentDetailsContractualEmployeeNumberInput.required = showContractualFields;
            if (!showContractualFields) {
                employmentDetailsContractualEmployeeNumberInput.value = '';
            }
        }
    
        if (employmentDetailsEngagementModeInput) {
            employmentDetailsEngagementModeInput.required = showEngagementFields;
            if (!showEngagementFields) {
                employmentDetailsEngagementModeInput.value = '';
            }
        }

        const showEmploymentContractType = showEngagementFields && employmentDetailsEngagementModeInput && employmentDetailsEngagementModeInput.value === 'Contractual';
        if (employmentDetailsEmployeeContractTypeField) {
            employmentDetailsEmployeeContractTypeField.classList.toggle('d-none', !showEmploymentContractType);
        }
        if (employmentDetailsEmployeeContractTypeInput) {
            employmentDetailsEmployeeContractTypeInput.required = !!showEmploymentContractType;
            if (!showEmploymentContractType) {
                employmentDetailsEmployeeContractTypeInput.value = '';
            }
        }

        const showEmploymentContractDates = !!showEmploymentContractType && employmentDetailsEmployeeContractTypeInput && employmentDetailsEmployeeContractTypeInput.value === 'Time bound';
        if (employmentDetailsEmployeeContractDatesField) {
            employmentDetailsEmployeeContractDatesField.classList.toggle('d-none', !showEmploymentContractDates);
        }
        if (employmentDetailsEmployeeContractStartDateInput) {
            employmentDetailsEmployeeContractStartDateInput.required = !!showEmploymentContractDates;
            if (!showEmploymentContractDates) {
                employmentDetailsEmployeeContractStartDateInput.value = '';
            }
        }
        if (employmentDetailsEmployeeContractEndDateInput) {
            employmentDetailsEmployeeContractEndDateInput.required = !!showEmploymentContractDates;
            if (!showEmploymentContractDates) {
                employmentDetailsEmployeeContractEndDateInput.value = '';
            }
        }
    
        if (employmentDetailsEngagementEmployeeNumberInput) {
            employmentDetailsEngagementEmployeeNumberInput.required = showEngagementFields;
            if (!showEngagementFields) {
                employmentDetailsEngagementEmployeeNumberInput.value = '';
            }
        }
    }

    function syncWorkArrangementFields() {
        if (!employmentWorkArrangementStandardFields) return;
        const showStandardFields = !!(employmentWorkArrangementStandard && employmentWorkArrangementStandard.checked);
        const showDefaultCard = !!(showStandardFields && employmentWorkArrangementStandardTypeDefault && employmentWorkArrangementStandardTypeDefault.checked);
        const showCustomFields = !!(showStandardFields && employmentWorkArrangementStandardTypeCustom && employmentWorkArrangementStandardTypeCustom.checked);
        const showHybridFields = !!(employmentWorkArrangementHybrid && employmentWorkArrangementHybrid.checked);
        employmentWorkArrangementStandardFields.classList.toggle('d-none', !showStandardFields);
        if (employmentWorkArrangementDefaultCardWrap) {
            employmentWorkArrangementDefaultCardWrap.classList.toggle('d-none', !showDefaultCard);
        }
        if (employmentWorkArrangementCustomFields) {
            employmentWorkArrangementCustomFields.classList.toggle('d-none', !showCustomFields);
        }
        if (employmentWorkArrangementHybridFields) {
            employmentWorkArrangementHybridFields.classList.toggle('d-none', !showHybridFields);
        }
        if (employmentWorkArrangementStandardTypeDefault) {
            employmentWorkArrangementStandardTypeDefault.required = showStandardFields;
            if (!showStandardFields) {
                employmentWorkArrangementStandardTypeDefault.checked = false;
            }
        }
        if (employmentWorkArrangementStandardTypeCustom) {
            employmentWorkArrangementStandardTypeCustom.required = showStandardFields;
            if (!showStandardFields) {
                employmentWorkArrangementStandardTypeCustom.checked = false;
            }
        }
        if (employmentCustomWorkingStartInput) {
            employmentCustomWorkingStartInput.required = showCustomFields;
            if (!showCustomFields) {
                employmentCustomWorkingStartInput.value = '';
            }
        }
        if (employmentCustomWorkingEndInput) {
            employmentCustomWorkingEndInput.required = showCustomFields;
            if (!showCustomFields) {
                employmentCustomWorkingEndInput.value = '';
            }
        }
        if (employmentCustomCheckInGraceInput && !showCustomFields) {
            employmentCustomCheckInGraceInput.value = '';
        }
        if (employmentCustomCheckOutGraceInput && !showCustomFields) {
            employmentCustomCheckOutGraceInput.value = '';
        }
        if (!showCustomFields) {
            employmentCustomDayInputs.forEach((input) => {
                input.checked = false;
            });
        }
        if (employmentHybridDayInputs.length) {
            employmentHybridDayInputs.forEach((input, idx) => {
                input.required = showHybridFields && idx === 0;
                if (!showHybridFields) {
                    input.checked = false;
                }
            });
        }
    }

    function syncWorkArrangementOrganizationPreview() {
        if (!employmentWorkArrangementOrgInitial || !employmentWorkArrangementOrgName) return;
        let orgName = '-';
        if (employmentOrganizationSelect && employmentOrganizationSelect.selectedIndex >= 0) {
            const selectedOption = employmentOrganizationSelect.options[employmentOrganizationSelect.selectedIndex];
            const selectedText = selectedOption ? String(selectedOption.textContent || '').trim() : '';
            if (selectedOption && selectedOption.value && selectedText) {
                orgName = selectedText;
            }
        }
        const firstMatch = orgName.match(/[A-Za-z0-9]/);
        employmentWorkArrangementOrgName.textContent = orgName;
        employmentWorkArrangementOrgInitial.textContent = firstMatch ? firstMatch[0].toUpperCase() : '-';
    }
    
    employmentDetailsCategoryInputs.forEach((input) => {
        input.addEventListener('change', toggleEmploymentCategoryFields);
    });
    if (employmentDetailsEngagementModeInput) {
        employmentDetailsEngagementModeInput.addEventListener('change', toggleEmploymentCategoryFields);
    }
    if (employmentDetailsEmployeeContractTypeInput) {
        employmentDetailsEmployeeContractTypeInput.addEventListener('change', toggleEmploymentCategoryFields);
    }
    toggleEmploymentCategoryFields();
    employmentWorkArrangementInputs.forEach((input) => {
        input.addEventListener('change', syncWorkArrangementFields);
    });
    employmentWorkArrangementStandardTypeInputs.forEach((input) => {
        input.addEventListener('change', syncWorkArrangementFields);
    });
    if (employmentOrganizationSelect) {
        employmentOrganizationSelect.addEventListener('change', syncWorkArrangementOrganizationPreview);
    }
    syncWorkArrangementFields();
    syncWorkArrangementOrganizationPreview();
    
    function setSummaryValue(targetId, value, fallback) {
        const el = document.getElementById(targetId);
        if (!el) return;
        const nextVal = value && String(value).trim() ? String(value).trim() : fallback;
        el.textContent = nextVal;
    }
    
    function bindSummaryField(inputId, targetId, fallback) {
        const input = document.getElementById(inputId);
        if (!input) return;
        const handler = function() {
            setSummaryValue(targetId, input.value, fallback);
        };
        input.addEventListener('input', handler);
        input.addEventListener('change', handler);
        handler();
    }
    
    bindSummaryField('giNameInput', 'summaryName', 'Not provided');
    bindSummaryField('giCnicInput', 'summaryCnic', 'Not provided');
    bindSummaryField('giGenderInput', 'summaryGender', 'Not selected');
    bindSummaryField('giReligionInput', 'summaryReligion', 'Not selected');
    bindSummaryField('giNationalityInput', 'summaryNationality', 'Not selected');
})();
