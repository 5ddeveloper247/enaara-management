/* global bootstrap, $ */
(function () {
  function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  }

  function isPreloadedLeaveTypeSelect(selectEl) {
    return !!(selectEl && selectEl.dataset && selectEl.dataset.preloaded === '1');
  }

  function calculateDays(startDateInput, endDateInput, calculatedDaysEl, isHalfDayInput) {
    if (!startDateInput.value || !endDateInput.value) {
      calculatedDaysEl.textContent = '0';
      return;
    }

    var start = new Date(startDateInput.value);
    var end = new Date(endDateInput.value);

    if (end < start) {
      calculatedDaysEl.textContent = '0';
      return;
    }

    var isHalfDay = !!(isHalfDayInput && isHalfDayInput.checked);
    var diffTime = end.getTime() - start.getTime();
    var diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
    calculatedDaysEl.textContent = isHalfDay ? '0.5' : String(diffDays);

    var employeeSelect = document.getElementById('leaveEmployee');
    var employeeId = employeeSelect ? employeeSelect.value : null;
    if (employeeId && startDateInput.value && endDateInput.value) {
      $.ajax({
        url: '/admin/leave-request/calculate-duration',
        method: 'GET',
        headers: { Accept: 'application/json' },
        data: {
          employee_id: employeeId,
          start_date: startDateInput.value,
          end_date: endDateInput.value,
          is_half_day: isHalfDay ? 1 : 0
        },
        success: function (resp) {
          if (resp && resp.success && resp.duration !== undefined) {
            calculatedDaysEl.textContent = String(resp.duration);
          }
        }
      });
    }
  }

  function isLeaveTypeSelected(leaveTypeSelect) {
    return !!(leaveTypeSelect && leaveTypeSelect.value);
  }

  function syncHalfDayUi(leaveTypeSelect, halfDaySection, isHalfDayInput, halfDaySessionSection, halfDaySessionInput, startDateInput, endDateInput) {
    var allowed = isLeaveTypeSelected(leaveTypeSelect);

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

  function showDocumentSectionIfRequired(leaveTypeSelect, documentSection, fileInput) {
    var isConditional = selectedLeaveCondition(leaveTypeSelect) === 'conditional';
    var requiredMark = documentSection ? documentSection.querySelector('.document-required-mark') : null;

    if (documentSection) {
      documentSection.style.display = isConditional ? 'block' : 'none';
    }

    if (requiredMark) {
      requiredMark.style.display = isConditional ? 'inline' : 'none';
    }

    if (fileInput) {
      fileInput.required = isConditional;
      if (!isConditional) {
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
      opt.setAttribute(
        'data-half-day-applicable',
        it.half_day_applicable ? '1' : '0'
      );
      selectEl.appendChild(opt);
    });

    if (keepSelected && selectEl.querySelector('option[value="' + keepSelected + '"]')) {
      selectEl.value = keepSelected;
    }
  }

  function renderQuotaSummary(container, quotaSummary) {
    if (!container) return;

    container.innerHTML = '';
    (quotaSummary || []).forEach(function (q) {
      var div = document.createElement('div');
      div.className = 'col-6';
      div.innerHTML = '<div class="small">' + q.type + ': <strong>' + q.remaining + '</strong> days</div>';
      container.appendChild(div);
    });

    if (!quotaSummary || quotaSummary.length === 0) {
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
    var calculatedDaysEl = document.getElementById('calculatedDays');
    var medicalCertSection = document.getElementById('medicalCertSection');
    var medicalReportInput = document.getElementById('medical_report');
    var halfDaySection = document.getElementById('halfDaySection');
    var isHalfDayInput = document.getElementById('leaveIsHalfDay');
    var halfDaySessionSection = document.getElementById('halfDaySessionSection');
    var halfDaySessionInput = document.getElementById('leaveHalfDaySession');
    var submitBtn = document.getElementById('submitLeaveRequestBtn');
    var canvasEl = document.getElementById('addLeaveRequestCanvas');
    var keepPreloadedLeaveTypes = isPreloadedLeaveTypeSelect(leaveTypeSelect);
    var lastLoadedEmployeeId = null;
    var leaveTypesRequest = null;
    var workflowRequest = null;

    if (window.initialLeaveWorkflowPreview) {
      renderApprovalWorkflow(window.initialLeaveWorkflowPreview);
    }

    function refreshDurationAndHalfDayUi() {
      syncHalfDayUi(
        leaveTypeSelect,
        halfDaySection,
        isHalfDayInput,
        halfDaySessionSection,
        halfDaySessionInput,
        startDateInput,
        endDateInput
      );
      calculateDays(startDateInput, endDateInput, calculatedDaysEl, isHalfDayInput);
    }

    if (startDateInput && endDateInput && calculatedDaysEl) {
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
        lastLoadedEmployeeId = null;
        return;
      }

      if (workflowRequest && workflowRequest.readyState !== 4) {
        workflowRequest.abort();
      }
      workflowRequest = loadApprovalWorkflow(employeeId);

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
        form.reset();
        clearFormError(form);
        clearFieldErrors(form);
        if (calculatedDaysEl) calculatedDaysEl.textContent = '0';
        if (halfDaySection) halfDaySection.style.display = 'none';
        if (isHalfDayInput) isHalfDayInput.checked = false;
        if (halfDaySessionSection) halfDaySessionSection.style.display = 'none';
        if (halfDaySessionInput) {
          halfDaySessionInput.value = '';
          halfDaySessionInput.required = false;
        }
        if (endDateInput) {
          endDateInput.readOnly = false;
          endDateInput.classList.remove('opacity-75');
        }
        var container = document.getElementById('leaveBalanceContainer');
        if (container) {
          container.innerHTML = '<div class="col-12 text-center py-2 opacity-50 small">Select an employee to see balances</div>';
        }
        if (window.initialLeaveWorkflowPreview) {
          renderApprovalWorkflow(window.initialLeaveWorkflowPreview);
        } else {
          renderApprovalWorkflowPlaceholder('Select an employee to see approval workflow.');
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

        calculateDays(startDateInput, endDateInput, calculatedDaysEl, isHalfDayInput);

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

      if (selectedLeaveCondition(leaveTypeSelect) === 'conditional' && medicalReportInput && !medicalReportInput.files.length) {
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
