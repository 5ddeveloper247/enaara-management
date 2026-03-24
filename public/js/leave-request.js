/* global bootstrap, $ */
(function () {
  function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
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
    var diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1; // include both days
    calculatedDaysEl.textContent = String(diffDays);
  }

  function showMedicalSectionIfSick(leaveTypeSelect, medicalCertSection) {
    var selectedText = (leaveTypeSelect.options[leaveTypeSelect.selectedIndex]?.text || '').toLowerCase();
    medicalCertSection.style.display = selectedText.includes('sick') ? 'block' : 'none';
  }

  function setSubmitting(btn, isSubmitting) {
    if (!btn) return;
    btn.disabled = !!isSubmitting;
    btn.dataset.originalText = btn.dataset.originalText || btn.innerHTML;
    btn.innerHTML = isSubmitting ? 'Submitting...' : btn.dataset.originalText;
  }

  function showFormError(form, message) {
    var container = form.querySelector('[data-form-errors]');
    if (!container) return;
    container.textContent = message || 'Something went wrong.';
    container.classList.remove('d-none');
    // Make sure user sees it
    container.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
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
      // dark canvas => use high contrast
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

  function populateLeaveTypes(selectEl, items) {
    if (!selectEl) return;
    selectEl.innerHTML = '<option value="">Select Leave Type</option>';

    (items || []).forEach(function (it) {
      var opt = document.createElement('option');
      opt.value = it.id;
      opt.textContent = it.name;
      selectEl.appendChild(opt);
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('addLeaveRequestForm');
    if (!form) return;

    var employeeSelect = document.getElementById('leaveEmployee');
    var startDateInput = document.getElementById('leaveStartDate');
    var endDateInput = document.getElementById('leaveEndDate');
    var leaveTypeSelect = document.getElementById('leaveType');
    var calculatedDaysEl = document.getElementById('calculatedDays');
    var medicalCertSection = document.getElementById('medicalCertSection');
    var submitBtn = document.getElementById('submitLeaveRequestBtn');
    var canvasEl = document.getElementById('addLeaveRequestCanvas');

    if (startDateInput && endDateInput && calculatedDaysEl) {
      var onDateChange = function () {
        calculateDays(startDateInput, endDateInput, calculatedDaysEl);
      };
      startDateInput.addEventListener('change', onDateChange);
      endDateInput.addEventListener('change', onDateChange);
    }

    if (leaveTypeSelect && medicalCertSection) {
      leaveTypeSelect.addEventListener('change', function () {
        showMedicalSectionIfSick(leaveTypeSelect, medicalCertSection);
      });
    }

    if (canvasEl) {
      canvasEl.addEventListener('hidden.bs.offcanvas', function () {
        form.reset();
        clearFormError(form);
        clearFieldErrors(form);
        if (calculatedDaysEl) calculatedDaysEl.textContent = '0';
        ['balanceAnnual', 'balanceSick', 'balanceCasual', 'balanceCompOff'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.textContent = '0';
        });
        if (medicalCertSection) medicalCertSection.style.display = 'none';
        setSubmitting(submitBtn, false);
      });
    }

    if (employeeSelect && leaveTypeSelect) {
      employeeSelect.addEventListener('change', function () {
        clearFieldErrors(form);

        var employeeId = employeeSelect.value;
        populateLeaveTypes(leaveTypeSelect, []);
        if (!employeeId) return;

        setLeaveTypesLoading(leaveTypeSelect, true);

        $.ajax({
          url: '/admin/leave-request/leave-types',
          method: 'GET',
          headers: { Accept: 'application/json' },
          data: { employee_id: employeeId },
          success: function (resp) {
            populateLeaveTypes(leaveTypeSelect, resp && resp.leaveTypes ? resp.leaveTypes : []);

            if (resp && resp.quotaSummary) {
                var balances = {
                    annual: document.getElementById('balanceAnnual'),
                    sick: document.getElementById('balanceSick'),
                    casual: document.getElementById('balanceCasual'),
                    comp: document.getElementById('balanceCompOff')
                };

                // Reset
                Object.keys(balances).forEach(function(key) {
                    if (balances[key]) balances[key].textContent = '0';
                });

                // Map
                resp.quotaSummary.forEach(function (q) {
                    let typeName = (q.type || '').toLowerCase();
                    if (typeName.includes('annual') && balances.annual) balances.annual.textContent = q.remaining;
                    else if (typeName.includes('sick') && balances.sick) balances.sick.textContent = q.remaining;
                    else if (typeName.includes('casual') && balances.casual) balances.casual.textContent = q.remaining;
                    else if (typeName.includes('comp') && balances.comp) balances.comp.textContent = q.remaining;
                });
            }
          },
          error: function () {
            showFormError(form, 'Failed to load leave types for selected employee.');
          },
          complete: function () {
            setLeaveTypesLoading(leaveTypeSelect, false);
          },
        });
      });
    }

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      clearFormError(form);
      clearFieldErrors(form);

      if (!form.checkValidity()) {
        form.reportValidity();
        return;
      }

      setSubmitting(submitBtn, true);

      $.ajax({
        url: form.getAttribute('action'),
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': getCsrfToken(), Accept: 'application/json' },
        data: $(form).serialize(),
        success: function () {
          var offcanvas = bootstrap.Offcanvas.getInstance(canvasEl) || new bootstrap.Offcanvas(canvasEl);
          offcanvas.hide();
          window.setTimeout(function () {
            window.location.reload();
          }, 700);
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
        },
      });
    });
  });
})();

