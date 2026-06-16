/* global bootstrap, $ */
(function () {
  function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  }

  function isPreloadedLeaveTypeSelect(selectEl) {
    return !!(selectEl && selectEl.dataset && selectEl.dataset.preloaded === '1');
  }

  function setLeaveDurationRowVisibility(rowId, count) {
    var row = document.getElementById(rowId);
    if (!row) {
      return;
    }

    if (count > 0) {
      row.classList.remove('d-none');
    } else {
      row.classList.add('d-none');
    }
  }

  function formatLeaveHeadline(calendarDays, duration, isHalfDay) {
    if (isHalfDay && duration === 0.5) {
      return '0.5 working day';
    }

    if (isHalfDay && duration === 0) {
      return 'Short leave not available';
    }

    var totalDays = parseInt(calendarDays || 0, 10);
    return totalDays + (totalDays === 1 ? ' total day of leave' : ' total days of leave');
  }

  function renderLeaveDurationBreakdown(summary, state) {
    var headlineEl = document.getElementById('leaveDurationHeadline');
    var emptyEl = document.getElementById('leaveDurationBreakdownEmpty');
    var bodyEl = document.getElementById('leaveDurationBreakdownBody');
    var typeBadgeEl = document.getElementById('leaveDurationTypeBadge');
    var offDaysEl = document.getElementById('leaveOffDays');
    var holidayDaysEl = document.getElementById('leaveHolidayDays');
    var workingDaysEl = document.getElementById('leaveWorkingDays');
    var travelExemptEl = document.getElementById('leaveTravelExemptDays');
    var billableEl = document.getElementById('leaveBillableDays');

    if (!headlineEl || !emptyEl || !bodyEl) {
      return;
    }

    if (!summary) {
      headlineEl.textContent = state === 'loading' ? 'Calculating...' : 'Select dates';
      emptyEl.classList.remove('d-none');
      emptyEl.textContent = state === 'loading'
        ? 'Calculating leave days...'
        : 'Select start and end dates to see how leave days are calculated.';
      bodyEl.classList.add('d-none');
      if (typeBadgeEl) {
        typeBadgeEl.classList.add('d-none');
      }
      return;
    }

    var isHalfDay = !!summary.is_half_day;
    var duration = parseFloat(summary.duration || 0);
    var workingDays = parseFloat(summary.working_days !== undefined ? summary.working_days : duration);
    var billableDuration = parseFloat(
      summary.billable_duration !== undefined ? summary.billable_duration : duration
    );
    var exemptDays = parseFloat(summary.exempt_days || 0);
    var calendarDays = parseInt(summary.calendar_days || 0, 10);
    var offDays = parseInt(summary.off_days || 0, 10);
    var holidayDays = parseInt(summary.public_holiday_days || 0, 10);

    emptyEl.classList.add('d-none');
    bodyEl.classList.remove('d-none');

    if (typeBadgeEl) {
      typeBadgeEl.classList.remove('d-none');
      typeBadgeEl.textContent = isHalfDay ? 'Short leave' : 'Full leave';
      typeBadgeEl.classList.toggle('is-half-day', isHalfDay);
    }

    headlineEl.textContent = formatLeaveHeadline(calendarDays, duration, isHalfDay);

    if (offDaysEl) {
      offDaysEl.textContent = String(offDays);
    }
    if (holidayDaysEl) {
      holidayDaysEl.textContent = String(holidayDays);
    }
    if (workingDaysEl) {
      workingDaysEl.textContent = isHalfDay ? String(duration) : String(workingDays);
    }
    if (travelExemptEl) {
      travelExemptEl.textContent = String(exemptDays);
    }
    if (billableEl) {
      billableEl.textContent = String(billableDuration);
    }

    setLeaveDurationRowVisibility('leaveOffDaysRow', offDays);
    setLeaveDurationRowVisibility('leaveHolidayDaysRow', holidayDays);
    setLeaveDurationRowVisibility('leaveTravelExemptRow', exemptDays);
  }

  function calculateDays(startDateInput, endDateInput, isHalfDayInput, outstationState) {
    if (!startDateInput.value || !endDateInput.value) {
      renderLeaveDurationBreakdown(null);
      return;
    }

    var start = new Date(startDateInput.value);
    var end = new Date(endDateInput.value);

    if (end < start) {
      renderLeaveDurationBreakdown(null);
      return;
    }

    var employeeSelect = document.getElementById('leaveEmployee');
    var employeeId = employeeSelect ? employeeSelect.value : null;
    var isHalfDay = !!(isHalfDayInput && isHalfDayInput.checked);
    var isOutstation = !!(outstationState && outstationState.isOutstationInput && outstationState.isOutstationInput.checked);
    var destination = null;

    if (isOutstation && outstationState && outstationState.getSelectedDestination) {
      destination = outstationState.getSelectedDestination();
    }

    if (!employeeId) {
      renderLeaveDurationBreakdown(null);
      return;
    }

    renderLeaveDurationBreakdown(null, 'loading');

    $.ajax({
      url: '/admin/leave-request/calculate-duration',
      method: 'GET',
      headers: { Accept: 'application/json' },
      data: {
        employee_id: employeeId,
        start_date: startDateInput.value,
        end_date: endDateInput.value,
        is_half_day: isHalfDay ? 1 : 0,
        is_outstation_leave: isOutstation ? 1 : 0,
        outstation_destination: destination || ''
      },
      success: function (resp) {
        if (resp && resp.success) {
          renderLeaveDurationBreakdown(resp);
        } else {
          renderLeaveDurationBreakdown(null);
        }
      },
      error: function () {
        renderLeaveDurationBreakdown(null);
      }
    });
  }

  function renderOutstationDestinations(payload, optionsContainer, noAddressMessage, exemptNotice) {
    if (!optionsContainer) {
      return;
    }

    optionsContainer.innerHTML = '';
    var destinations = payload && payload.destinations ? payload.destinations : [];

    if (!destinations.length) {
      if (noAddressMessage) {
        noAddressMessage.classList.remove('d-none');
      }
      if (exemptNotice) {
        exemptNotice.classList.add('d-none');
      }
      return;
    }

    if (noAddressMessage) {
      noAddressMessage.classList.add('d-none');
    }

    destinations.forEach(function (destination, index) {
      var wrapper = document.createElement('label');
      wrapper.className = 'd-flex align-items-start gap-2 p-2 rounded-3 border mb-0';
      wrapper.style.borderColor = '#ffffff1a';
      wrapper.style.cursor = 'pointer';

      var input = document.createElement('input');
      input.type = 'radio';
      input.className = 'form-check-input mt-1';
      input.name = 'outstation_destination';
      input.value = destination.key;
      input.required = true;
      if (index === 0) {
        input.checked = true;
      }

      var content = document.createElement('div');
      content.innerHTML =
        '<div class="small fw-semibold text-white">' + destination.label + '</div>' +
        '<div class="small opacity-75 text-white">' + destination.address + '</div>';

      wrapper.appendChild(input);
      wrapper.appendChild(content);
      optionsContainer.appendChild(wrapper);
    });

    syncOutstationExemptNotice(optionsContainer, exemptNotice);
    optionsContainer.querySelectorAll('input[name="outstation_destination"]').forEach(function (radio) {
      radio.addEventListener('change', function () {
        syncOutstationExemptNotice(optionsContainer, exemptNotice);
        var startDateInput = document.getElementById('leaveStartDate');
        var endDateInput = document.getElementById('leaveEndDate');
        var isHalfDayInput = document.getElementById('leaveIsHalfDay');
        var isOutstationInput = document.getElementById('leaveIsOutstation');
        if (startDateInput && endDateInput) {
          calculateDays(startDateInput, endDateInput, isHalfDayInput, {
            isOutstationInput: isOutstationInput,
            getSelectedDestination: function () {
              var selected = optionsContainer.querySelector('input[name="outstation_destination"]:checked');
              return selected ? selected.value : null;
            }
          });
        }
      });
    });
  }

  function syncOutstationExemptNotice(optionsContainer, exemptNotice) {
    if (!exemptNotice || !optionsContainer) {
      return;
    }

    var selected = optionsContainer.querySelector('input[name="outstation_destination"]:checked');
    if (!selected) {
      exemptNotice.classList.add('d-none');
      return;
    }

    var wrapper = selected.closest('label');
    var addressText = wrapper ? wrapper.querySelector('.opacity-75') : null;
    var address = addressText ? addressText.textContent : '';
    var normalized = address.toLowerCase();
    var outside = normalized.indexOf('rawalpindi') === -1 && normalized.indexOf('rwp') === -1;

    if (outside && address.trim() !== '') {
      exemptNotice.classList.remove('d-none');
    } else {
      exemptNotice.classList.add('d-none');
    }
  }

  function loadEmployeeAddresses(employeeId, callbacks) {
    if (!employeeId) {
      if (callbacks && callbacks.onEmpty) {
        callbacks.onEmpty();
      }
      return null;
    }

    if (window.initialEmployeeOutstationAddresses && callbacks && callbacks.useInitial) {
      if (callbacks.onSuccess) {
        callbacks.onSuccess(window.initialEmployeeOutstationAddresses);
      }
      return null;
    }

    var url = window.leaveEmployeeAddressesUrl || '/admin/leave-request/employee-addresses';

    return $.ajax({
      url: url,
      method: 'GET',
      headers: { Accept: 'application/json' },
      data: { employee_id: employeeId },
      success: function (resp) {
        if (resp && resp.success && callbacks && callbacks.onSuccess) {
          callbacks.onSuccess(resp);
        }
      },
      error: function () {
        if (callbacks && callbacks.onError) {
          callbacks.onError();
        }
      }
    });
  }

  function isLeaveTypeSelected(leaveTypeSelect) {
    return !!(leaveTypeSelect && leaveTypeSelect.value);
  }

  function isShortLeaveApplicable(leaveTypeSelect) {
    if (!leaveTypeSelect || leaveTypeSelect.selectedIndex < 0) {
      return false;
    }

    var selected = leaveTypeSelect.options[leaveTypeSelect.selectedIndex];
    return selected.getAttribute('data-short-leave-applicable') === '1';
  }

  function syncHalfDayUi(leaveTypeSelect, halfDaySection, isHalfDayInput, halfDaySessionSection, halfDaySessionInput, startDateInput, endDateInput) {
    var allowed = isLeaveTypeSelected(leaveTypeSelect) && isShortLeaveApplicable(leaveTypeSelect);

    if (halfDaySection) {
      halfDaySection.style.display = allowed ? 'block' : 'none';
    }

    if (!allowed) {
      if (isHalfDayInput) {
        isHalfDayInput.checked = false;
      }
      if (halfDaySessionSection) {
        halfDaySessionSection.style.display = 'none';
      }
      if (halfDaySessionInput) {
        halfDaySessionInput.value = '';
        halfDaySessionInput.required = false;
      }
      if (endDateInput) {
        endDateInput.readOnly = false;
        endDateInput.classList.remove('opacity-75');
      }
      return;
    }

    var isHalfDay = !!(isHalfDayInput && isHalfDayInput.checked);

    if (halfDaySessionSection) {
      halfDaySessionSection.style.display = isHalfDay ? 'block' : 'none';
    }

    if (halfDaySessionInput) {
      halfDaySessionInput.required = isHalfDay;
      if (!isHalfDay) {
        halfDaySessionInput.value = '';
      }
    }

    if (endDateInput) {
      if (isHalfDay && startDateInput && startDateInput.value) {
        endDateInput.value = startDateInput.value;
        endDateInput.readOnly = true;
        endDateInput.classList.add('opacity-75');
      } else {
        endDateInput.readOnly = false;
        endDateInput.classList.remove('opacity-75');
      }
    }
  }

  function selectedLeaveCondition(leaveTypeSelect) {
    if (!leaveTypeSelect || leaveTypeSelect.selectedIndex < 0) {
      return '';
    }

    var selected = leaveTypeSelect.options[leaveTypeSelect.selectedIndex];
    return (selected.getAttribute('data-leave-condition') || '').toLowerCase();
  }

  function requiresSupportingDocument(leaveTypeSelect) {
    if (!leaveTypeSelect || leaveTypeSelect.selectedIndex < 0) {
      return false;
    }

    var selected = leaveTypeSelect.options[leaveTypeSelect.selectedIndex];
    return selected.getAttribute('data-requires-document') === '1';
  }

  function showDocumentSectionIfRequired(leaveTypeSelect, documentSection, fileInput) {
    var needsDocument = requiresSupportingDocument(leaveTypeSelect);
    var requiredMark = documentSection ? documentSection.querySelector('.document-required-mark') : null;

    if (documentSection) {
      documentSection.style.display = needsDocument ? 'block' : 'none';
    }

    if (requiredMark) {
      requiredMark.style.display = needsDocument ? 'inline' : 'none';
    }

    if (fileInput) {
      fileInput.required = needsDocument;
      if (!needsDocument) {
        fileInput.value = '';
      }
    }
  }

  function setSubmitting(btn, isSubmitting) {
    if (!btn) return;
    btn.disabled = !!isSubmitting;
    btn.dataset.originalText = btn.dataset.originalText || btn.innerHTML;
    btn.innerHTML = isSubmitting ? 'Submitting...' : btn.dataset.originalText;
  }

  function showFormError(form, message) {
    showError(message || 'Something went wrong.');
  }

  function clearFormError(form) {
    var container = form.querySelector('[data-form-errors]');
    if (!container) return;
    container.textContent = '';
    container.classList.add('d-none');
  }

  function clearFieldErrors(form) {
    var invalidFields = form.querySelectorAll('.is-invalid');
    invalidFields.forEach(function (el) {
      el.classList.remove('is-invalid');
    });

    var feedbacks = form.querySelectorAll('.invalid-feedback[data-field-error]');
    feedbacks.forEach(function (el) {
      el.remove();
    });
  }

  function showFieldErrors(form, errors) {
    var firstField = null;
    Object.keys(errors || {}).forEach(function (fieldName) {
      var messages = errors[fieldName];
      var message = Array.isArray(messages) ? messages[0] : String(messages || '');
      if (!message) return;

      var field = form.querySelector('[name="' + fieldName + '"]');
      if (!field) return;

      field.classList.add('is-invalid');
      if (!firstField) firstField = field;

      var fb = document.createElement('div');
      fb.className = 'invalid-feedback d-block text-warning';
      fb.setAttribute('data-field-error', '1');
      fb.textContent = message;

      field.insertAdjacentElement('afterend', fb);
    });

    if (firstField) {
      firstField.scrollIntoView({ block: 'center', behavior: 'smooth' });
      try {
        firstField.focus();
      } catch (e) {
        // ignore
      }
    }
  }

  function setLeaveTypesLoading(selectEl, isLoading) {
    if (!selectEl) return;
    selectEl.disabled = !!isLoading;
  }

  function resetEmployeeSelectControl(selectEl) {
    if (!selectEl || selectEl.tagName !== 'SELECT') {
      return;
    }

    selectEl.value = '';

    if (typeof window.jQuery !== 'undefined') {
      var $select = window.jQuery(selectEl);
      if ($select.data('select2')) {
        $select.val(null).trigger('change.select2');
      }
    }
  }

  function populateLeaveTypes(selectEl, items, selectedId) {
    if (!selectEl) return;

    var keepSelected = selectedId !== undefined && selectedId !== null
      ? String(selectedId)
      : (selectEl.value || '');

    selectEl.innerHTML = '<option value="">Select Leave Type</option>';

    (items || []).forEach(function (it) {
      var opt = document.createElement('option');
      opt.value = it.id;
      opt.textContent = it.name;
      if (it.leave_condition) {
        opt.setAttribute('data-leave-condition', it.leave_condition);
      }
      if (it.code) {
        opt.setAttribute('data-leave-code', String(it.code).toUpperCase());
      }
      opt.setAttribute(
        'data-requires-document',
        it.requires_supporting_document ? '1' : '0'
      );
      opt.setAttribute(
        'data-short-leave-applicable',
        it.short_leave_applicable ? '1' : '0'
      );
      selectEl.appendChild(opt);
    });

    if (keepSelected && selectEl.querySelector('option[value="' + keepSelected + '"]')) {
      selectEl.value = keepSelected;
    }
  }

  function appendQuotaBalanceRow(parentRow, quota) {
    var col = document.createElement('div');
    col.className = 'col-6';
    col.innerHTML = '<div class="small">' + quota.type + ': <strong>' + quota.remaining + '</strong> days</div>';
    parentRow.appendChild(col);
  }

  var leaveBalanceHeadingClasses = {
    'Unconditional Leaves': 'leave-balance-heading--unconditional',
    'General Leaves': 'leave-balance-heading--general',
    'Conditional Leaves': 'leave-balance-heading--conditional'
  };

  function appendQuotaBalanceGroup(container, title, quotas, addTopMargin) {
    if (!quotas.length) {
      return;
    }

    var section = document.createElement('div');
    section.className = 'col-12' + (addTopMargin ? ' mt-3' : '');

    var heading = document.createElement('div');
    heading.className = 'small fw-semibold mb-2 ' + (leaveBalanceHeadingClasses[title] || '');
    heading.textContent = title;
    section.appendChild(heading);

    var row = document.createElement('div');
    row.className = 'row g-2';
    quotas.forEach(function (quota) {
      appendQuotaBalanceRow(row, quota);
    });
    section.appendChild(row);
    container.appendChild(section);
  }

  function renderQuotaSummary(container, quotaSummary) {
    if (!container) return;

    container.innerHTML = '';

    if (!quotaSummary || quotaSummary.length === 0) {
      container.innerHTML = '<div class="col-12 text-center py-2 opacity-50 small">No leave quotas assigned</div>';
      return;
    }

    var unconditional = [];
    var general = [];
    var conditional = [];

    quotaSummary.forEach(function (q) {
      if (q.leave_condition === 'conditional') {
        conditional.push(q);
      } else if (q.leave_condition === 'general') {
        general.push(q);
      } else {
        unconditional.push(q);
      }
    });

    var hasPriorGroup = false;
    if (unconditional.length) {
      appendQuotaBalanceGroup(container, 'Unconditional Leaves', unconditional, false);
      hasPriorGroup = true;
    }
    if (general.length) {
      appendQuotaBalanceGroup(container, 'General Leaves', general, hasPriorGroup);
      hasPriorGroup = true;
    }
    if (conditional.length) {
      appendQuotaBalanceGroup(container, 'Conditional Leaves', conditional, hasPriorGroup);
    }

    if (!unconditional.length && !general.length && !conditional.length) {
      container.innerHTML = '<div class="col-12 text-center py-2 opacity-50 small">No leave quotas assigned</div>';
    }
  }

  function formatWorkflowApproverLabel(approver) {
    if (!approver) {
      return 'Unknown';
    }

    var label = approver.full_name || 'Unknown';
    if (approver.employee_code) {
      label += ' (' + approver.employee_code + ')';
    }

    return label;
  }

  function syncLeaveSubmitState(preview) {
    var submitBtn = document.getElementById('submitLeaveRequestBtn');
    if (!submitBtn) {
      return;
    }

    var canSubmit = !!(preview && preview.can_submit);
    submitBtn.disabled = !canSubmit;
    submitBtn.title = canSubmit ? '' : (preview && preview.top_level_message ? preview.top_level_message : 'Leave cannot be submitted for this employee.');
  }

  function renderApprovalWorkflow(preview) {
    var stepsEl = document.getElementById('leaveApprovalWorkflowSteps');
    var warningEl = document.getElementById('leaveApprovalWorkflowWarning');
    var introEl = document.getElementById('leaveApprovalWorkflowIntro');

    if (!stepsEl) {
      return;
    }

    stepsEl.innerHTML = '';

    if (preview && preview.is_top_level) {
      if (introEl) {
        introEl.classList.add('d-none');
      }

      var alertBox = document.createElement('div');
      alertBox.className = 'd-flex align-items-start gap-2 p-2 rounded-3 border border-warning border-opacity-50 bg-warning bg-opacity-10';
      alertBox.innerHTML =
        '<i class="bi bi-shield-exclamation text-warning fs-5 flex-shrink-0"></i>' +
        '<div>' +
        '<div class="fw-semibold text-warning mb-1">Top-Level Role — No Approval Route</div>' +
        '<div class="opacity-90 text-white">' + (preview.top_level_message || 'No line manager or approver is configured for this employee.') + '</div>' +
        '</div>';
      stepsEl.appendChild(alertBox);

      if (warningEl) {
        warningEl.textContent = '';
        warningEl.classList.add('d-none');
      }

      syncLeaveSubmitState(preview);
      window.lastLeaveWorkflowPreview = preview || null;
      return;
    }

    if (introEl) {
      introEl.classList.remove('d-none');
    }

    var steps = preview && preview.steps ? preview.steps : [];
    if (!steps.length) {
      stepsEl.innerHTML = '<div class="opacity-50">No approval workflow could be resolved for this employee.</div>';
    } else {
      steps.forEach(function (step) {
        var row = document.createElement('div');
        row.className = 'mb-1';
        var approverLabel = formatWorkflowApproverLabel(step.approver);
        var roleLabel = step.role_label || 'Approver';
        var action = step.action || '';
        row.textContent = step.level + '. ' + approverLabel + ' (' + roleLabel + ') \u2192 ' + action;
        stepsEl.appendChild(row);
      });
    }

    if (warningEl) {
      if (preview && preview.warning) {
        warningEl.textContent = preview.warning;
        warningEl.classList.remove('d-none');
      } else {
        warningEl.textContent = '';
        warningEl.classList.add('d-none');
      }
    }

    syncLeaveSubmitState(preview);
    window.lastLeaveWorkflowPreview = preview || null;
  }

  function renderApprovalWorkflowPlaceholder(message) {
    var stepsEl = document.getElementById('leaveApprovalWorkflowSteps');
    var warningEl = document.getElementById('leaveApprovalWorkflowWarning');
    var introEl = document.getElementById('leaveApprovalWorkflowIntro');

    if (stepsEl) {
      stepsEl.innerHTML = '<div class="opacity-50">' + message + '</div>';
    }

    if (introEl) {
      introEl.classList.remove('d-none');
    }

    if (warningEl) {
      warningEl.textContent = '';
      warningEl.classList.add('d-none');
    }

    syncLeaveSubmitState(null);
    window.lastLeaveWorkflowPreview = null;
  }

  function loadApprovalWorkflow(employeeId) {
    if (!employeeId) {
      renderApprovalWorkflowPlaceholder('Select an employee to see approval workflow.');
      return null;
    }

    var workflowUrl = window.leaveApprovalWorkflowUrl || '/admin/leave-request/approval-workflow';
    renderApprovalWorkflowPlaceholder('Loading approval workflow...');

    return $.ajax({
      url: workflowUrl,
      method: 'GET',
      headers: { Accept: 'application/json' },
      data: { employee_id: employeeId },
      success: function (resp) {
        if (resp && resp.success) {
          renderApprovalWorkflow(resp);
        } else {
          renderApprovalWorkflowPlaceholder('Unable to load approval workflow.');
        }
      },
      error: function () {
        renderApprovalWorkflowPlaceholder('Failed to load approval workflow.');
      }
    });
  }

  function initLeaveRequestForm() {
    var form = document.getElementById('addLeaveRequestForm');
    if (!form) return;

    var employeeSelect = document.getElementById('leaveEmployee');
    var startDateInput = document.getElementById('leaveStartDate');
    var endDateInput = document.getElementById('leaveEndDate');
    var leaveTypeSelect = document.getElementById('leaveType');
    var medicalCertSection = document.getElementById('medicalCertSection');
    var medicalReportInput = document.getElementById('medical_report');
    var halfDaySection = document.getElementById('halfDaySection');
    var isHalfDayInput = document.getElementById('leaveIsHalfDay');
    var halfDaySessionSection = document.getElementById('halfDaySessionSection');
    var halfDaySessionInput = document.getElementById('leaveHalfDaySession');
    var isOutstationInput = document.getElementById('leaveIsOutstation');
    var outstationSection = document.getElementById('outstationSection');
    var outstationDestinationOptions = document.getElementById('outstationDestinationOptions');
    var outstationNoAddressMessage = document.getElementById('outstationNoAddressMessage');
    var outstationExemptNotice = document.getElementById('outstationExemptNotice');
    var submitBtn = document.getElementById('submitLeaveRequestBtn');
    var canvasEl = document.getElementById('addLeaveRequestCanvas');
    var keepPreloadedLeaveTypes = isPreloadedLeaveTypeSelect(leaveTypeSelect);
    var lastLoadedEmployeeId = null;
    var leaveTypesRequest = null;
    var workflowRequest = null;
    var addressesRequest = null;

    function getSelectedOutstationDestination() {
      if (!outstationDestinationOptions) {
        return null;
      }
      var selected = outstationDestinationOptions.querySelector('input[name="outstation_destination"]:checked');
      return selected ? selected.value : null;
    }

    function getOutstationState() {
      return {
        isOutstationInput: isOutstationInput,
        getSelectedDestination: getSelectedOutstationDestination
      };
    }

    function syncOutstationSection() {
      var enabled = !!(isOutstationInput && isOutstationInput.checked);

      if (outstationSection) {
        outstationSection.classList.toggle('d-none', !enabled);
      }

      if (enabled && isHalfDayInput && isHalfDayInput.checked) {
        isHalfDayInput.checked = false;
      }

      if (!enabled && outstationDestinationOptions) {
        outstationDestinationOptions.querySelectorAll('input[name="outstation_destination"]').forEach(function (radio) {
          radio.checked = false;
        });
        if (outstationExemptNotice) {
          outstationExemptNotice.classList.add('d-none');
        }
      }

      refreshDurationAndHalfDayUi();
    }

    function loadOutstationAddresses(employeeId, useInitial) {
      if (!outstationDestinationOptions) {
        return;
      }

      if (addressesRequest && addressesRequest.readyState !== 4) {
        addressesRequest.abort();
      }

      addressesRequest = loadEmployeeAddresses(employeeId, {
        useInitial: useInitial,
        onSuccess: function (payload) {
          renderOutstationDestinations(
            payload,
            outstationDestinationOptions,
            outstationNoAddressMessage,
            outstationExemptNotice
          );
        },
        onEmpty: function () {
          renderOutstationDestinations(
            { destinations: [] },
            outstationDestinationOptions,
            outstationNoAddressMessage,
            outstationExemptNotice
          );
        },
        onError: function () {
          if (outstationNoAddressMessage) {
            outstationNoAddressMessage.classList.remove('d-none');
            outstationNoAddressMessage.textContent = 'Unable to load employee addresses.';
          }
        }
      });
    }

    if (window.initialLeaveWorkflowPreview) {
      renderApprovalWorkflow(window.initialLeaveWorkflowPreview);
    }

    if (window.initialEmployeeOutstationAddresses && outstationDestinationOptions) {
      renderOutstationDestinations(
        window.initialEmployeeOutstationAddresses,
        outstationDestinationOptions,
        outstationNoAddressMessage,
        outstationExemptNotice
      );
    }

    function refreshDurationAndHalfDayUi() {
      if (isHalfDayInput && isHalfDayInput.checked && isOutstationInput) {
        isOutstationInput.checked = false;
        if (outstationSection) {
          outstationSection.classList.add('d-none');
        }
      }

      syncHalfDayUi(
        leaveTypeSelect,
        halfDaySection,
        isHalfDayInput,
        halfDaySessionSection,
        halfDaySessionInput,
        startDateInput,
        endDateInput
      );
      calculateDays(startDateInput, endDateInput, isHalfDayInput, getOutstationState());
    }

    if (startDateInput && endDateInput) {
      var onDateChange = function () {
        if (startDateInput.value) {
          endDateInput.min = startDateInput.value;
          if (endDateInput.value && endDateInput.value < startDateInput.value) {
            endDateInput.value = startDateInput.value;
          }
        } else {
          endDateInput.min = '';
        }
        refreshDurationAndHalfDayUi();
      };
      startDateInput.addEventListener('change', onDateChange);
      endDateInput.addEventListener('change', onDateChange);
    }

    if (leaveTypeSelect) {
      leaveTypeSelect.addEventListener('change', function () {
        showDocumentSectionIfRequired(leaveTypeSelect, medicalCertSection, medicalReportInput);
        refreshDurationAndHalfDayUi();
      });
    }

    if (isHalfDayInput) {
      isHalfDayInput.addEventListener('change', refreshDurationAndHalfDayUi);
    }

    if (isOutstationInput) {
      isOutstationInput.addEventListener('change', syncOutstationSection);
    }

    function loadEmployeeLeaveData(employeeId, options) {
      options = options || {};
      var reloadLeaveTypes = options.reloadLeaveTypes !== false;
      var balanceContainer = document.getElementById('leaveBalanceContainer');
      var selectedLeaveTypeId = leaveTypeSelect ? leaveTypeSelect.value : '';

      if (!employeeId) {
        if (reloadLeaveTypes && !keepPreloadedLeaveTypes && leaveTypeSelect) {
          populateLeaveTypes(leaveTypeSelect, []);
        }
        if (balanceContainer) {
          balanceContainer.innerHTML =
            '<div class="col-12 text-center py-2 opacity-50 small">Select an employee to see balances</div>';
        }
        renderApprovalWorkflowPlaceholder('Select an employee to see approval workflow.');
        renderOutstationDestinations(
          { destinations: [] },
          outstationDestinationOptions,
          outstationNoAddressMessage,
          outstationExemptNotice
        );
        lastLoadedEmployeeId = null;
        return;
      }

      if (workflowRequest && workflowRequest.readyState !== 4) {
        workflowRequest.abort();
      }
      workflowRequest = loadApprovalWorkflow(employeeId);
      loadOutstationAddresses(employeeId, keepPreloadedLeaveTypes && !!window.initialEmployeeOutstationAddresses);

      if (leaveTypesRequest && leaveTypesRequest.readyState !== 4) {
        leaveTypesRequest.abort();
      }

      if (reloadLeaveTypes && !keepPreloadedLeaveTypes && leaveTypeSelect) {
        populateLeaveTypes(leaveTypeSelect, []);
      }

      setLeaveTypesLoading(leaveTypeSelect, true);

      leaveTypesRequest = $.ajax({
        url: '/admin/leave-request/leave-types',
        method: 'GET',
        headers: { Accept: 'application/json' },
        data: { employee_id: employeeId },
        success: function (resp) {
          if (reloadLeaveTypes && !keepPreloadedLeaveTypes && leaveTypeSelect) {
            populateLeaveTypes(
              leaveTypeSelect,
              resp && resp.leaveTypes ? resp.leaveTypes : [],
              selectedLeaveTypeId
            );
          }

          showDocumentSectionIfRequired(leaveTypeSelect, medicalCertSection, medicalReportInput);
          refreshDurationAndHalfDayUi();
          renderQuotaSummary(balanceContainer, resp && resp.quotaSummary ? resp.quotaSummary : []);
          lastLoadedEmployeeId = employeeId;
        },
        error: function (xhr, status) {
          if (status === 'abort') {
            return;
          }
          if (!keepPreloadedLeaveTypes) {
            showFormError(form, 'Failed to load leave types for selected employee.');
          }
        },
        complete: function () {
          setLeaveTypesLoading(leaveTypeSelect, false);
          leaveTypesRequest = null;
        }
      });
    }

    if (canvasEl) {
      canvasEl.addEventListener('shown.bs.offcanvas', function () {
        if (employeeSelect && employeeSelect.tagName === 'SELECT' && !employeeSelect.value) {
          renderApprovalWorkflowPlaceholder('Select an employee to see approval workflow.');
        }

        if (employeeSelect && employeeSelect.value) {
          var employeeId = employeeSelect.value;
          var employeeChanged = lastLoadedEmployeeId !== employeeId;
          loadEmployeeLeaveData(employeeId, {
            reloadLeaveTypes: employeeChanged && !keepPreloadedLeaveTypes
          });
        }
        refreshDurationAndHalfDayUi();
      });

      canvasEl.addEventListener('hidden.bs.offcanvas', function () {
        if (leaveTypesRequest && leaveTypesRequest.readyState !== 4) {
          leaveTypesRequest.abort();
        }
        if (workflowRequest && workflowRequest.readyState !== 4) {
          workflowRequest.abort();
        }
        if (addressesRequest && addressesRequest.readyState !== 4) {
          addressesRequest.abort();
        }

        form.reset();
        resetEmployeeSelectControl(employeeSelect);
        clearFormError(form);
        clearFieldErrors(form);

        if (!keepPreloadedLeaveTypes && leaveTypeSelect) {
          populateLeaveTypes(leaveTypeSelect, []);
        }

        renderLeaveDurationBreakdown(null);
        if (halfDaySection) halfDaySection.style.display = 'none';
        if (isHalfDayInput) isHalfDayInput.checked = false;
        if (isOutstationInput) isOutstationInput.checked = false;
        if (outstationSection) outstationSection.classList.add('d-none');
        renderOutstationDestinations(
          { destinations: [] },
          outstationDestinationOptions,
          outstationNoAddressMessage,
          outstationExemptNotice
        );
        if (halfDaySessionSection) halfDaySessionSection.style.display = 'none';
        if (halfDaySessionInput) {
          halfDaySessionInput.value = '';
          halfDaySessionInput.required = false;
        }
        if (endDateInput) {
          endDateInput.readOnly = false;
          endDateInput.classList.remove('opacity-75');
          endDateInput.min = '';
        }
        if (startDateInput) {
          startDateInput.value = '';
        }
        var container = document.getElementById('leaveBalanceContainer');
        if (container) {
          container.innerHTML = '<div class="col-12 text-center py-2 opacity-50 small">Select an employee to see balances</div>';
        }
        window.lastLeaveWorkflowPreview = null;
        if (window.initialLeaveWorkflowPreview) {
          renderApprovalWorkflow(window.initialLeaveWorkflowPreview);
        } else {
          renderApprovalWorkflowPlaceholder('Select an employee to see approval workflow.');
        }
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.title = '';
        }
        if (medicalCertSection) medicalCertSection.style.display = 'none';
        if (medicalReportInput) {
          medicalReportInput.required = false;
          medicalReportInput.value = '';
        }
        lastLoadedEmployeeId = null;
        setSubmitting(submitBtn, false);
      });
    }

    if (employeeSelect && leaveTypeSelect) {
      employeeSelect.addEventListener('change', function () {
        clearFieldErrors(form);

        var employeeId = employeeSelect.value;
        var employeeChanged = lastLoadedEmployeeId !== employeeId;

        calculateDays(startDateInput, endDateInput, isHalfDayInput, getOutstationState());

        loadEmployeeLeaveData(employeeId, {
          reloadLeaveTypes: employeeChanged && !keepPreloadedLeaveTypes
        });
      });
    }

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      clearFormError(form);
      clearFieldErrors(form);

      showDocumentSectionIfRequired(leaveTypeSelect, medicalCertSection, medicalReportInput);
      syncHalfDayUi(
        leaveTypeSelect,
        halfDaySection,
        isHalfDayInput,
        halfDaySessionSection,
        halfDaySessionInput,
        startDateInput,
        endDateInput
      );

      if (isHalfDayInput && isHalfDayInput.checked && halfDaySessionInput && !halfDaySessionInput.value) {
        halfDaySessionInput.classList.add('is-invalid');
        showFieldErrors(form, {
          half_day_session: ['Please select a session (morning or afternoon) for half-day leave.']
        });
        showFormError(form, 'Please select a session for half-day leave.');
        return;
      }

      if (requiresSupportingDocument(leaveTypeSelect) && medicalReportInput && !medicalReportInput.files.length) {
        medicalReportInput.classList.add('is-invalid');
        showFieldErrors(form, {
          medical_report: ['A supporting document is required for this leave type.']
        });
        showFormError(form, 'A supporting document is required for this leave type.');
        return;
      }

      if (!form.checkValidity()) {
        form.reportValidity();
        return;
      }

      if (window.lastLeaveWorkflowPreview && window.lastLeaveWorkflowPreview.can_submit === false) {
        showFormError(
          form,
          window.lastLeaveWorkflowPreview.top_level_message
            || 'Leave cannot be submitted because no approver is configured for this employee.'
        );
        return;
      }

      if (isOutstationInput && isOutstationInput.checked && !getSelectedOutstationDestination()) {
        showFormError(form, 'Please select where you want to go for outstation leave.');
        return;
      }

      setSubmitting(submitBtn, true);

      var formData = new FormData(form);

      $.ajax({
        url: form.getAttribute('action'),
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': getCsrfToken(), Accept: 'application/json' },
        data: formData,
        processData: false,
        contentType: false,
        success: function () {
          showSuccess('Leave request submitted successfully!', 'Submitted');
          var offcanvas = bootstrap.Offcanvas.getInstance(canvasEl) || new bootstrap.Offcanvas(canvasEl);
          offcanvas.hide();
          window.setTimeout(function () {
            window.location.reload();
          }, 1500);
        },
        error: function (xhr) {
          setSubmitting(submitBtn, false);

          if (xhr && xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
            var errors = xhr.responseJSON.errors;
            showFieldErrors(form, errors);
            var firstKey = Object.keys(errors)[0];
            var firstMsg = firstKey ? errors[firstKey][0] : 'Validation failed.';
            showFormError(form, firstMsg);
            return;
          }

          showFormError(form, 'Failed to submit leave request. Please try again.');
        }
      });
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initLeaveRequestForm);
  } else {
    initLeaveRequestForm();
  }
})();
