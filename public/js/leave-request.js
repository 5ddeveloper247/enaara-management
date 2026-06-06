/* global bootstrap, $ */
(function () {
  function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  }

  function isPreloadedLeaveTypeSelect(selectEl) {
    return !!(selectEl && selectEl.dataset && selectEl.dataset.preloaded === '1');
  }

  function calculateDays(startDateInput, endDateInput, calculatedDaysEl) {
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

    var diffTime = end.getTime() - start.getTime();
    var diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
    calculatedDaysEl.textContent = String(diffDays);

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
          end_date: endDateInput.value
        },
        success: function (resp) {
          if (resp && resp.success && resp.duration !== undefined) {
            calculatedDaysEl.textContent = String(resp.duration);
          }
        }
      });
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
    var submitBtn = document.getElementById('submitLeaveRequestBtn');
    var canvasEl = document.getElementById('addLeaveRequestCanvas');
    var keepPreloadedLeaveTypes = isPreloadedLeaveTypeSelect(leaveTypeSelect);
    var lastLoadedEmployeeId = null;
    var leaveTypesRequest = null;

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
        calculateDays(startDateInput, endDateInput, calculatedDaysEl);
      };
      startDateInput.addEventListener('change', onDateChange);
      endDateInput.addEventListener('change', onDateChange);
    }

    if (leaveTypeSelect && medicalCertSection) {
      leaveTypeSelect.addEventListener('change', function () {
        showDocumentSectionIfRequired(leaveTypeSelect, medicalCertSection, medicalReportInput);
      });
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
        lastLoadedEmployeeId = null;
        return;
      }

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
      });

      canvasEl.addEventListener('hidden.bs.offcanvas', function () {
        if (leaveTypesRequest && leaveTypesRequest.readyState !== 4) {
          leaveTypesRequest.abort();
        }
        form.reset();
        clearFormError(form);
        clearFieldErrors(form);
        if (calculatedDaysEl) calculatedDaysEl.textContent = '0';
        var container = document.getElementById('leaveBalanceContainer');
        if (container) {
          container.innerHTML = '<div class="col-12 text-center py-2 opacity-50 small">Select an employee to see balances</div>';
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

        calculateDays(startDateInput, endDateInput, calculatedDaysEl);

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
