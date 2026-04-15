(function () {
    'use strict';

const moreFamilyMembersContainer = document.getElementById('moreFamilyMembersContainer');
    const moreFamilyAddMemberBtn = document.getElementById('moreFamilyAddMemberBtn');
    const moreFamilyMemberTemplate = document.getElementById('moreFamilyMemberTemplate');
    const moreFamilyMemberCount = document.getElementById('moreFamilyMemberCount');
    
    function formatFamilyDatePreview(value) {
        if (!value) return '-';
        const parsedDate = new Date(value);
        if (Number.isNaN(parsedDate.getTime())) return value;
        return parsedDate.toLocaleDateString();
    }
    
    function updateFamilyMemberIndexes() {
        if (!moreFamilyMembersContainer) return;
        const rows = moreFamilyMembersContainer.querySelectorAll('[data-family-row]');
        rows.forEach((row, index) => {
            const indexEl = row.querySelector('[data-family-index]');
            if (indexEl) {
                indexEl.textContent = 'Member ' + String(index + 1);
            }
            const removeBtn = row.querySelector('[data-family-remove]');
            if (removeBtn) {
                removeBtn.disabled = rows.length === 1;
            }
        });
        if (moreFamilyMemberCount) {
            moreFamilyMemberCount.textContent = rows.length + (rows.length === 1 ? ' Member' : ' Members');
        }
    }
    
    function getFamilyMemberValues(row) {
        if (!row) return {};
        const nameInput = row.querySelector('[data-family-name]');
        const genderInput = row.querySelector('[data-family-gender]');
        const dobInput = row.querySelector('[data-family-date-of-birth]');
        const relationInput = row.querySelector('[data-family-relation]');
        const occupationInput = row.querySelector('[data-family-occupation]');
        return {
            name: nameInput ? nameInput.value : '',
            gender: genderInput ? genderInput.value : '',
            dateOfBirth: dobInput ? dobInput.value : '',
            relation: relationInput ? relationInput.value : '',
            occupation: occupationInput ? occupationInput.value : ''
        };
    }
    
    function setFamilyRowPreviewData(row) {
        if (!row) return;
        const values = getFamilyMemberValues(row);
        const previewName = row.querySelector('[data-family-preview-name]');
        const previewGender = row.querySelector('[data-family-preview-gender]');
        const previewDateOfBirth = row.querySelector('[data-family-preview-date-of-birth]');
        const previewRelation = row.querySelector('[data-family-preview-relation]');
        const previewOccupation = row.querySelector('[data-family-preview-occupation]');
    
        if (previewName) previewName.textContent = values.name || '-';
        if (previewGender) previewGender.textContent = values.gender || '-';
        if (previewDateOfBirth) previewDateOfBirth.textContent = formatFamilyDatePreview(values.dateOfBirth);
        if (previewRelation) previewRelation.textContent = values.relation || '-';
        if (previewOccupation) previewOccupation.textContent = values.occupation || '-';
    }
    
    function setFamilyRowMode(row, isPreviewMode) {
        if (!row) return;
        row.classList.toggle('preview-mode', isPreviewMode);
        const saveBtn = row.querySelector('[data-family-save]');
        if (!saveBtn) return;
        if (isPreviewMode) {
            saveBtn.classList.remove('btn-outline-primary');
            saveBtn.classList.add('btn-outline-secondary');
            saveBtn.innerHTML = '<i class="bi bi-pencil"></i>';
            saveBtn.setAttribute('title', 'Edit member');
        } else {
            saveBtn.classList.remove('btn-outline-secondary');
            saveBtn.classList.add('btn-outline-primary');
            saveBtn.innerHTML = '<i class="bi bi-floppy"></i>';
            saveBtn.setAttribute('title', 'Save member');
        }
    }
    
    function createFamilyMemberRow(values) {
        if (!moreFamilyMemberTemplate) return null;
        const wrapper = document.createElement('div');
        wrapper.innerHTML = moreFamilyMemberTemplate.innerHTML.trim();
        const row = wrapper.firstElementChild;
        if (!row) return null;
        const nameInput = row.querySelector('[data-family-name]');
        const genderInput = row.querySelector('[data-family-gender]');
        const dobInput = row.querySelector('[data-family-date-of-birth]');
        const relationInput = row.querySelector('[data-family-relation]');
        const occupationInput = row.querySelector('[data-family-occupation]');
        if (nameInput) nameInput.value = values && values.name ? values.name : '';
        if (genderInput) genderInput.value = values && values.gender ? values.gender : '';
        if (dobInput) dobInput.value = values && values.dateOfBirth ? values.dateOfBirth : '';
        if (relationInput) relationInput.value = values && values.relation ? values.relation : '';
        if (occupationInput) occupationInput.value = values && values.occupation ? values.occupation : '';
        setFamilyRowPreviewData(row);
        setFamilyRowMode(row, false);
        return row;
    }
    
    function addFamilyMember(values) {
        if (!moreFamilyMembersContainer) return;
        const newRow = createFamilyMemberRow(values);
        if (!newRow) return;
        moreFamilyMembersContainer.appendChild(newRow);
        updateFamilyMemberIndexes();
    }
    
    if (moreFamilyAddMemberBtn) {
        moreFamilyAddMemberBtn.addEventListener('click', function() {
            addFamilyMember();
        });
    }
    
    if (moreFamilyMembersContainer) {
        moreFamilyMembersContainer.addEventListener('click', function(e) {
            const saveBtn = e.target.closest('[data-family-save]');
            if (saveBtn) {
                const row = saveBtn.closest('[data-family-row]');
                if (row) {
                    const isPreviewMode = row.classList.contains('preview-mode');
                    if (isPreviewMode) {
                        setFamilyRowMode(row, false);
                        return;
                    }
                    const fields = row.querySelectorAll('input, select, textarea');
                    const invalidField = Array.from(fields).find((field) => !field.checkValidity());
                    if (invalidField) {
                        invalidField.reportValidity();
                        return;
                    }
                    setFamilyRowPreviewData(row);
                    setFamilyRowMode(row, true);
                }
            }
    
            const removeBtn = e.target.closest('[data-family-remove]');
            if (removeBtn) {
                const row = removeBtn.closest('[data-family-row]');
                if (row && moreFamilyMembersContainer.querySelectorAll('[data-family-row]').length > 1) {
                    row.remove();
                    updateFamilyMemberIndexes();
                }
            }
        });
    }
    
    if (moreFamilyMembersContainer && !moreFamilyMembersContainer.querySelector('[data-family-row]')) {
        addFamilyMember();
    }
    
    const moreAcademicRecordsContainer = document.getElementById('moreAcademicRecordsContainer');
    const moreAcademicAddRecordBtn = document.getElementById('moreAcademicAddRecordBtn');
    const moreAcademicRecordTemplate = document.getElementById('moreAcademicRecordTemplate');
    const moreAcademicRecordCount = document.getElementById('moreAcademicRecordCount');
    
    function formatAcademicDatePreview(value) {
        if (!value) return '-';
        const parsedDate = new Date(value);
        if (Number.isNaN(parsedDate.getTime())) return value;
        return parsedDate.toLocaleDateString();
    }
    
    function getAcademicRecordValues(row) {
        if (!row) return {};
        const degreeInput = row.querySelector('[data-academic-degree]');
        const gradeInput = row.querySelector('[data-academic-grade]');
        const startDateInput = row.querySelector('[data-academic-start-date]');
        const endDateInput = row.querySelector('[data-academic-end-date]');
        const fieldOfStudyInput = row.querySelector('[data-academic-field-of-study]');
        const instituteInput = row.querySelector('[data-academic-institute]');
        return {
            degree: degreeInput ? degreeInput.value : '',
            grade: gradeInput ? gradeInput.value : '',
            startDate: startDateInput ? startDateInput.value : '',
            endDate: endDateInput ? endDateInput.value : '',
            fieldOfStudy: fieldOfStudyInput ? fieldOfStudyInput.value : '',
            institute: instituteInput ? instituteInput.value : ''
        };
    }
    
    function setAcademicRecordPreviewData(row) {
        if (!row) return;
        const values = getAcademicRecordValues(row);
        const previewDegree = row.querySelector('[data-academic-preview-degree]');
        const previewGrade = row.querySelector('[data-academic-preview-grade]');
        const previewStartDate = row.querySelector('[data-academic-preview-start-date]');
        const previewEndDate = row.querySelector('[data-academic-preview-end-date]');
        const previewFieldOfStudy = row.querySelector('[data-academic-preview-field-of-study]');
        const previewInstitute = row.querySelector('[data-academic-preview-institute]');
    
        if (previewDegree) previewDegree.textContent = values.degree || '-';
        if (previewGrade) previewGrade.textContent = values.grade || '-';
        if (previewStartDate) previewStartDate.textContent = formatAcademicDatePreview(values.startDate);
        if (previewEndDate) previewEndDate.textContent = formatAcademicDatePreview(values.endDate);
        if (previewFieldOfStudy) previewFieldOfStudy.textContent = values.fieldOfStudy || '-';
        if (previewInstitute) previewInstitute.textContent = values.institute || '-';
    }
    
    function setAcademicRecordMode(row, isPreviewMode) {
        if (!row) return;
        row.classList.toggle('preview-mode', isPreviewMode);
        const saveBtn = row.querySelector('[data-academic-save]');
        if (!saveBtn) return;
        if (isPreviewMode) {
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
    
    function updateAcademicRecordIndexes() {
        if (!moreAcademicRecordsContainer) return;
        const rows = moreAcademicRecordsContainer.querySelectorAll('[data-academic-row]');
        rows.forEach((row, index) => {
            const indexEl = row.querySelector('[data-academic-index]');
            if (indexEl) {
                indexEl.textContent = 'Record ' + String(index + 1);
            }
            const removeBtn = row.querySelector('[data-academic-remove]');
            if (removeBtn) {
                removeBtn.disabled = rows.length === 1;
            }
        });
        if (moreAcademicRecordCount) {
            moreAcademicRecordCount.textContent = rows.length + (rows.length === 1 ? ' Record' : ' Records');
        }
    }
    
    function createAcademicRecordRow(values) {
        if (!moreAcademicRecordTemplate) return null;
        const wrapper = document.createElement('div');
        wrapper.innerHTML = moreAcademicRecordTemplate.innerHTML.trim();
        const row = wrapper.firstElementChild;
        if (!row) return null;
        const degreeInput = row.querySelector('[data-academic-degree]');
        const gradeInput = row.querySelector('[data-academic-grade]');
        const startDateInput = row.querySelector('[data-academic-start-date]');
        const endDateInput = row.querySelector('[data-academic-end-date]');
        const fieldOfStudyInput = row.querySelector('[data-academic-field-of-study]');
        const instituteInput = row.querySelector('[data-academic-institute]');
        if (degreeInput) degreeInput.value = values && values.degree ? values.degree : '';
        if (gradeInput) gradeInput.value = values && values.grade ? values.grade : '';
        if (startDateInput) startDateInput.value = values && values.startDate ? values.startDate : '';
        if (endDateInput) endDateInput.value = values && values.endDate ? values.endDate : '';
        if (fieldOfStudyInput) fieldOfStudyInput.value = values && values.fieldOfStudy ? values.fieldOfStudy : '';
        if (instituteInput) instituteInput.value = values && values.institute ? values.institute : '';
        setAcademicRecordPreviewData(row);
        setAcademicRecordMode(row, false);
        return row;
    }
    
    function addAcademicRecord(values) {
        if (!moreAcademicRecordsContainer) return;
        const newRow = createAcademicRecordRow(values);
        if (!newRow) return;
        moreAcademicRecordsContainer.appendChild(newRow);
        updateAcademicRecordIndexes();
    }
    
    if (moreAcademicAddRecordBtn) {
        moreAcademicAddRecordBtn.addEventListener('click', function() {
            addAcademicRecord();
        });
    }
    
    if (moreAcademicRecordsContainer) {
        moreAcademicRecordsContainer.addEventListener('click', function(e) {
            const saveBtn = e.target.closest('[data-academic-save]');
            if (saveBtn) {
                const row = saveBtn.closest('[data-academic-row]');
                if (row) {
                    const isPreviewMode = row.classList.contains('preview-mode');
                    if (isPreviewMode) {
                        setAcademicRecordMode(row, false);
                        return;
                    }
                    const fields = row.querySelectorAll('input, select, textarea');
                    const invalidField = Array.from(fields).find((field) => !field.checkValidity());
                    if (invalidField) {
                        invalidField.reportValidity();
                        return;
                    }
                    setAcademicRecordPreviewData(row);
                    setAcademicRecordMode(row, true);
                }
                return;
            }
    
            const removeBtn = e.target.closest('[data-academic-remove]');
            if (removeBtn) {
                const row = removeBtn.closest('[data-academic-row]');
                if (row && moreAcademicRecordsContainer.querySelectorAll('[data-academic-row]').length > 1) {
                    row.remove();
                    updateAcademicRecordIndexes();
                }
            }
        });
    }
    
    if (moreAcademicRecordsContainer && !moreAcademicRecordsContainer.querySelector('[data-academic-row]')) {
        addAcademicRecord();
    }
    
    const moreEmployementRecordsContainer = document.getElementById('moreEmployementRecordsContainer');
    const moreEmployementAddRecordBtn = document.getElementById('moreEmployementAddRecordBtn');
    const moreEmployementRecordTemplate = document.getElementById('moreEmployementRecordTemplate');
    const moreEmployementRecordCount = document.getElementById('moreEmployementRecordCount');
    
    function formatEmployementDatePreview(value) {
        if (!value) return '-';
        const parsedDate = new Date(value);
        if (Number.isNaN(parsedDate.getTime())) return value;
        return parsedDate.toLocaleDateString();
    }
    
    function getEmployementRecordValues(row) {
        if (!row) return {};
        const organizationInput = row.querySelector('[data-employement-organization]');
        const designationInput = row.querySelector('[data-employement-designation]');
        const fromDateInput = row.querySelector('[data-employement-from-date]');
        const toDateInput = row.querySelector('[data-employement-to-date]');
        const salaryInput = row.querySelector('[data-employement-salary]');
        const reasonInput = row.querySelector('[data-employement-reason]');
        return {
            organization: organizationInput ? organizationInput.value : '',
            designation: designationInput ? designationInput.value : '',
            fromDate: fromDateInput ? fromDateInput.value : '',
            toDate: toDateInput ? toDateInput.value : '',
            salary: salaryInput ? salaryInput.value : '',
            reason: reasonInput ? reasonInput.value : ''
        };
    }
    
    function setEmployementRecordPreviewData(row) {
        if (!row) return;
        const values = getEmployementRecordValues(row);
        const previewOrganization = row.querySelector('[data-employement-preview-organization]');
        const previewDesignation = row.querySelector('[data-employement-preview-designation]');
        const previewFromDate = row.querySelector('[data-employement-preview-from-date]');
        const previewToDate = row.querySelector('[data-employement-preview-to-date]');
        const previewSalary = row.querySelector('[data-employement-preview-salary]');
        const previewReason = row.querySelector('[data-employement-preview-reason]');
    
        if (previewOrganization) previewOrganization.textContent = values.organization || '-';
        if (previewDesignation) previewDesignation.textContent = values.designation || '-';
        if (previewFromDate) previewFromDate.textContent = formatEmployementDatePreview(values.fromDate);
        if (previewToDate) previewToDate.textContent = formatEmployementDatePreview(values.toDate);
        if (previewSalary) previewSalary.textContent = values.salary || '-';
        if (previewReason) previewReason.textContent = values.reason || '-';
    }
    
    function setEmployementRecordMode(row, isPreviewMode) {
        if (!row) return;
        row.classList.toggle('preview-mode', isPreviewMode);
        const saveBtn = row.querySelector('[data-employement-save]');
        if (!saveBtn) return;
        if (isPreviewMode) {
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
    
    function updateEmployementRecordIndexes() {
        if (!moreEmployementRecordsContainer) return;
        const rows = moreEmployementRecordsContainer.querySelectorAll('[data-employement-row]');
        rows.forEach((row, index) => {
            const indexEl = row.querySelector('[data-employement-index]');
            if (indexEl) {
                indexEl.textContent = 'Record ' + String(index + 1);
            }
            const removeBtn = row.querySelector('[data-employement-remove]');
            if (removeBtn) {
                removeBtn.disabled = rows.length === 1;
            }
        });
        if (moreEmployementRecordCount) {
            moreEmployementRecordCount.textContent = rows.length + (rows.length === 1 ? ' Record' : ' Records');
        }
    }
    
    function createEmployementRecordRow(values) {
        if (!moreEmployementRecordTemplate) return null;
        const wrapper = document.createElement('div');
        wrapper.innerHTML = moreEmployementRecordTemplate.innerHTML.trim();
        const row = wrapper.firstElementChild;
        if (!row) return null;
        const organizationInput = row.querySelector('[data-employement-organization]');
        const designationInput = row.querySelector('[data-employement-designation]');
        const fromDateInput = row.querySelector('[data-employement-from-date]');
        const toDateInput = row.querySelector('[data-employement-to-date]');
        const salaryInput = row.querySelector('[data-employement-salary]');
        const reasonInput = row.querySelector('[data-employement-reason]');
        if (organizationInput) organizationInput.value = values && values.organization ? values.organization : '';
        if (designationInput) designationInput.value = values && values.designation ? values.designation : '';
        if (fromDateInput) fromDateInput.value = values && values.fromDate ? values.fromDate : '';
        if (toDateInput) toDateInput.value = values && values.toDate ? values.toDate : '';
        if (salaryInput) salaryInput.value = values && values.salary ? values.salary : '';
        if (reasonInput) reasonInput.value = values && values.reason ? values.reason : '';
        setEmployementRecordPreviewData(row);
        setEmployementRecordMode(row, false);
        return row;
    }
    
    function addEmployementRecord(values) {
        if (!moreEmployementRecordsContainer) return;
        const newRow = createEmployementRecordRow(values);
        if (!newRow) return;
        moreEmployementRecordsContainer.appendChild(newRow);
        updateEmployementRecordIndexes();
    }
    
    if (moreEmployementAddRecordBtn) {
        moreEmployementAddRecordBtn.addEventListener('click', function() {
            addEmployementRecord();
        });
    }
    
    if (moreEmployementRecordsContainer) {
        moreEmployementRecordsContainer.addEventListener('click', function(e) {
            const saveBtn = e.target.closest('[data-employement-save]');
            if (saveBtn) {
                const row = saveBtn.closest('[data-employement-row]');
                if (row) {
                    const isPreviewMode = row.classList.contains('preview-mode');
                    if (isPreviewMode) {
                        setEmployementRecordMode(row, false);
                        return;
                    }
                    const fields = row.querySelectorAll('input, select, textarea');
                    const invalidField = Array.from(fields).find((field) => !field.checkValidity());
                    if (invalidField) {
                        invalidField.reportValidity();
                        return;
                    }
                    setEmployementRecordPreviewData(row);
                    setEmployementRecordMode(row, true);
                }
                return;
            }
    
            const removeBtn = e.target.closest('[data-employement-remove]');
            if (removeBtn) {
                const row = removeBtn.closest('[data-employement-row]');
                if (row && moreEmployementRecordsContainer.querySelectorAll('[data-employement-row]').length > 1) {
                    row.remove();
                    updateEmployementRecordIndexes();
                }
            }
        });
    }
    
    if (moreEmployementRecordsContainer && !moreEmployementRecordsContainer.querySelector('[data-employement-row]')) {
        addEmployementRecord();
    }
    
    const moreMedicalHasDisabilityYes = document.getElementById('moreMedicalHasDisabilityYes');
    const moreMedicalHasDisabilityNo = document.getElementById('moreMedicalHasDisabilityNo');
    const moreMedicalDisabilityTypeInput = document.getElementById('moreMedicalDisabilityTypeInput');
    const moreMedicalDisabilityDescriptionInput = document.getElementById('moreMedicalDisabilityDescriptionInput');
    
    function syncMedicalDisabilityFields() {
        const hasDisability = moreMedicalHasDisabilityYes ? moreMedicalHasDisabilityYes.checked : false;
        if (moreMedicalDisabilityTypeInput) {
            moreMedicalDisabilityTypeInput.disabled = !hasDisability;
            moreMedicalDisabilityTypeInput.required = hasDisability;
            if (!hasDisability) {
                moreMedicalDisabilityTypeInput.value = '';
            }
        }
        if (moreMedicalDisabilityDescriptionInput) {
            moreMedicalDisabilityDescriptionInput.disabled = !hasDisability;
            moreMedicalDisabilityDescriptionInput.required = hasDisability;
            if (!hasDisability) {
                moreMedicalDisabilityDescriptionInput.value = '';
            }
        }
    }
    
    if (moreMedicalHasDisabilityYes) {
        moreMedicalHasDisabilityYes.addEventListener('change', syncMedicalDisabilityFields);
    }
    if (moreMedicalHasDisabilityNo) {
        moreMedicalHasDisabilityNo.addEventListener('change', syncMedicalDisabilityFields);
    }
    syncMedicalDisabilityFields();
    
    let currentMoreStep = 1;
    const totalMoreSteps = 6;
    const moreSubTabs = document.querySelectorAll('.more-sub-tab');
    const morePrevBtn = document.getElementById('morePrevBtn');
    const moreNextBtn = document.getElementById('moreNextBtn');
    
    function syncMoreStepUi() {
        for (let i = 1; i <= totalMoreSteps; i++) {
            const pane = document.getElementById('moreStepPane' + i);
            if (pane) {
                pane.classList.toggle('active', i === currentMoreStep);
            }
        }
    
        moreSubTabs.forEach((tab) => {
            const step = Number(tab.getAttribute('data-more-step'));
            const active = step === currentMoreStep;
            tab.classList.toggle('active', active);
        });
    
        if (morePrevBtn) {
            morePrevBtn.style.visibility = currentMoreStep === 1 ? 'hidden' : 'visible';
        }
        if (moreNextBtn) {
            moreNextBtn.textContent = currentMoreStep === totalMoreSteps ? 'Done' : 'Next';
        }
    }
    
    moreSubTabs.forEach((tab) => {
        tab.addEventListener('click', function() {
            currentMoreStep = Number(this.getAttribute('data-more-step'));
            syncMoreStepUi();
        });
    });
    
    if (moreNextBtn) {
        moreNextBtn.addEventListener('click', function() {
            if (currentMoreStep < totalMoreSteps) {
                currentMoreStep += 1;
                syncMoreStepUi();
            }
        });
    }
    
    if (morePrevBtn) {
        morePrevBtn.addEventListener('click', function() {
            if (currentMoreStep > 1) {
                currentMoreStep -= 1;
                syncMoreStepUi();
            }
        });
    }
    syncMoreStepUi();
    
})();
