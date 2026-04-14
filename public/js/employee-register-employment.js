(function () {
    function escHtmlBasic(str) {
        return String(str || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function roleById(id) {
        return (window._rolesData || []).find(function (r) {
            return r && String(r.id) === String(id);
        });
    }

    function isOrgLevelRole(role) {
        if (!role) return false;
        if (typeof role.is_organization_level === 'boolean') return role.is_organization_level;
        return role.department_id === null || role.department_id === undefined || role.department_id === '';
    }

    function isDeptScopedRole(role) {
        if (!role) return false;
        return !(role.department_id === null || role.department_id === undefined || role.department_id === '');
    }

    function departmentInOrgsData(orgId, deptId) {
        var org = orgById(orgId);
        if (!org || !org.sbus || !deptId) return null;
        var did = String(deptId);
        for (var i = 0; i < org.sbus.length; i++) {
            var sbu = org.sbus[i];
            if (!sbu.departments) continue;
            for (var j = 0; j < sbu.departments.length; j++) {
                if (String(sbu.departments[j].id) === did) {
                    return sbu.departments[j];
                }
            }
        }
        return null;
    }

    function scheduleShapeFromEntity(ent) {
        if (!ent) {
            return {
                working_days: [],
                working_start_time: '',
                working_end_time: '',
                opening_grace_period: null,
                closing_grace_period: null,
            };
        }
        var days = Array.isArray(ent.working_days) ? ent.working_days : [];
        var st = ent.working_start_time != null ? String(ent.working_start_time).slice(0, 5) : '';
        var en = ent.working_end_time != null ? String(ent.working_end_time).slice(0, 5) : '';
        return {
            working_days: days,
            working_start_time: st,
            working_end_time: en,
            opening_grace_period: ent.opening_grace_period != null && ent.opening_grace_period !== '' ? ent.opening_grace_period : null,
            closing_grace_period: ent.closing_grace_period != null && ent.closing_grace_period !== '' ? ent.closing_grace_period : null,
        };
    }

    function resolveLiveStandardSchedule() {
        var orgEl = document.getElementById('org_select');
        var sbuEl = document.getElementById('sbu_select');
        var roleEl = document.getElementById('role_select');
        var orgId = orgEl ? orgEl.value : '';
        var sbuId = sbuEl ? sbuEl.value : '';
        var role = roleEl && roleEl.value ? roleById(roleEl.value) : null;
        if (!orgId || !role) {
            return { sourceLabel: '', schedule: scheduleShapeFromEntity(null) };
        }
        if (isOrgLevelRole(role)) {
            var org = orgById(orgId);
            return {
                sourceLabel: org ? 'Source: Organization — ' + org.name : 'Organization',
                schedule: scheduleShapeFromEntity(org),
            };
        }
        if (isDeptScopedRole(role)) {
            var dept = departmentInOrgsData(orgId, role.department_id);
            return {
                sourceLabel: dept ? 'Source: Department — ' + dept.name : 'Department (role)',
                schedule: scheduleShapeFromEntity(dept),
            };
        }
        if (!sbuId) {
            return { sourceLabel: 'Select SBU to load SBU office hours.', schedule: scheduleShapeFromEntity(null) };
        }
        var org2 = orgById(orgId);
        var sbu =
            org2 && org2.sbus
                ? org2.sbus.find(function (s) {
                      return String(s.id) === String(sbuId);
                  })
                : null;
        return {
            sourceLabel: sbu ? 'Source: SBU — ' + sbu.name : 'SBU',
            schedule: scheduleShapeFromEntity(sbu),
        };
    }

    var DAY_SHORT = {
        monday: 'Mon',
        tuesday: 'Tue',
        wednesday: 'Wed',
        thursday: 'Thu',
        friday: 'Fri',
        saturday: 'Sat',
        sunday: 'Sun',
    };

    function formatScheduleHtml(sched) {
        if (!sched) return '<p class="text-muted mb-0">No schedule is configured yet for this source.</p>';
        var days = sched.working_days || [];
        var dayStr =
            days.length > 0
                ? days
                      .map(function (d) {
                          return DAY_SHORT[d] || d;
                      })
                      .join(', ')
                : '—';
        var st = sched.working_start_time || '—';
        var en = sched.working_end_time || '—';
        var og = sched.opening_grace_period != null && sched.opening_grace_period !== '' ? sched.opening_grace_period + ' min' : '—';
        var cg = sched.closing_grace_period != null && sched.closing_grace_period !== '' ? sched.closing_grace_period + ' min' : '—';
        return (
            '<ul class="list-unstyled mb-0 small">' +
            '<li><strong>Working days:</strong> ' +
            escHtmlBasic(dayStr) +
            '</li>' +
            '<li><strong>Working time:</strong> ' +
            escHtmlBasic(st) +
            ' – ' +
            escHtmlBasic(en) +
            '</li>' +
            '<li><strong>Check-in grace:</strong> ' +
            escHtmlBasic(og) +
            '</li>' +
            '<li><strong>Check-out grace:</strong> ' +
            escHtmlBasic(cg) +
            '</li></ul>'
        );
    }

    function setStandardCustomInputsDisabled(disabled) {
        var wrap = document.getElementById('standardScheduleCustomFields');
        if (!wrap) return;
        wrap.querySelectorAll('input[name], select[name]').forEach(function (inp) {
            inp.disabled = !!disabled;
        });
    }

    function refreshStandardScheduleReadonly() {
        var srcEl = document.getElementById('standardScheduleSourceLabel');
        var bodyEl = document.getElementById('standardScheduleReadonlyBody');
        if (!srcEl || !bodyEl) return;
        var useSnapshot = window.__employeeEditMode && window._prefilledStandardSchedule;
        var pack;
        if (useSnapshot) {
            pack = {
                sourceLabel: window._prefilledStandardSchedule.sourceLabel || '',
                schedule: window._prefilledStandardSchedule.schedule,
            };
        } else {
            pack = resolveLiveStandardSchedule();
        }
        srcEl.textContent = pack.sourceLabel || '';
        bodyEl.innerHTML = formatScheduleHtml(pack.schedule);
    }

    function syncStandardScheduleModeUi() {
        var section = document.getElementById('standardScheduleSection');
        var modeEl = document.getElementById('engagementMode');
        var engagementMode = modeEl ? modeEl.value : '';
        if (!section) return;
        if (engagementMode !== 'standard') {
            section.style.display = 'none';
            return;
        }
        section.style.display = '';
        var customRadio = document.getElementById('standardScheduleCustom');
        var defRead = document.getElementById('standardScheduleDefaultReadonly');
        var custWrap = document.getElementById('standardScheduleCustomFields');
        var isCustom = customRadio && customRadio.checked;
        if (defRead) defRead.style.display = isCustom ? 'none' : '';
        if (custWrap) {
            custWrap.classList.toggle('d-none', !isCustom);
        }
        setStandardCustomInputsDisabled(!isCustom);
        if (!isCustom) {
            refreshStandardScheduleReadonly();
        }
    }

    window.clearEmploymentSchedulePrefillSnapshot = function () {
        window._prefilledStandardSchedule = null;
    };

    window.applyStandardSchedulePrefill = function (d) {
        window._prefilledStandardSchedule = null;
        var em = d && d.engagement_mode;
        if (em === 'on_site') {
            em = 'standard';
        }
        if (!d || em !== 'standard') {
            syncStandardScheduleModeUi();
            return;
        }
        var mode = d.standard_schedule_mode || 'default';
        var defR = document.getElementById('standardScheduleDefault');
        var custR = document.getElementById('standardScheduleCustom');
        if (mode === 'custom') {
            if (custR) custR.checked = true;
        } else {
            if (defR) defR.checked = true;
        }
        if (mode === 'custom') {
            var days = Array.isArray(d.working_days) ? d.working_days : [];
            document.querySelectorAll('#standardScheduleCustomFields input[name="working_days[]"]').forEach(function (cb) {
                cb.checked = days.indexOf(cb.value) !== -1;
            });
            var st = document.getElementById('empWorkingStartTime');
            var en = document.getElementById('empWorkingEndTime');
            var og = document.getElementById('empOpeningGrace');
            var cg = document.getElementById('empClosingGrace');
            if (st) st.value = d.working_start_time || '';
            if (en) en.value = d.working_end_time || '';
            if (og) og.value = d.opening_grace_period != null && d.opening_grace_period !== '' ? d.opening_grace_period : '';
            if (cg) cg.value = d.closing_grace_period != null && d.closing_grace_period !== '' ? d.closing_grace_period : '';
        } else if (window.__employeeEditMode) {
            var hasSaved =
                (Array.isArray(d.working_days) && d.working_days.length > 0) ||
                (d.working_start_time && String(d.working_start_time).length > 0) ||
                (d.working_end_time && String(d.working_end_time).length > 0);
            if (hasSaved) {
                window._prefilledStandardSchedule = {
                    sourceLabel: 'Saved standard schedule',
                    schedule: scheduleShapeFromEntity({
                        working_days: d.working_days,
                        working_start_time: d.working_start_time,
                        working_end_time: d.working_end_time,
                        opening_grace_period: d.opening_grace_period,
                        closing_grace_period: d.closing_grace_period,
                    }),
                };
            }
        }
        syncStandardScheduleModeUi();
    };

    function orgById(orgId) {
        return (window._orgsData || []).find(function (o) {
            return String(o.id) === String(orgId);
        });
    }

    function linkedSbuIds(role) {
        if (!role || !Array.isArray(role.linked_sbu_ids)) return [];
        return role.linked_sbu_ids.map(function (x) {
            return String(x);
        });
    }

    function roleMatchesOrgAndSbu(role, orgId, sbuId) {
        if (!role || !orgId || !sbuId) return false;
        var ro = role.organization_id != null && role.organization_id !== '' ? String(role.organization_id) : '';
        if (!ro || ro !== String(orgId)) return false;
        var sbuStr = String(sbuId);
        if (role.sbu_id != null && role.sbu_id !== '' && String(role.sbu_id) === sbuStr) {
            return true;
        }
        if (linkedSbuIds(role).indexOf(sbuStr) !== -1) {
            return true;
        }
        if (isOrgLevelRole(role)) {
            return true;
        }
        return false;
    }

    function setDeptMultiHint(text) {
        var deptSection = document.getElementById('deptMultiSection');
        if (!deptSection) return;
        var hint = deptSection.querySelector('.dept-multi-hint');
        if (hint) {
            hint.textContent = text;
        }
    }

    function deptGetSelect() {
        return document.getElementById('dept_select');
    }

    function deptRenderFromSelect() {
        var deptSel = deptGetSelect();
        var chips = document.getElementById('dept-chips');
        var ph = document.getElementById('dept-ph');
        if (!deptSel || !chips) return;
        var selected = Array.from(deptSel.selectedOptions || []);
        if (!selected.length) {
            chips.innerHTML = '';
            if (ph) ph.style.display = '';
            return;
        }
        if (ph) ph.style.display = 'none';
        chips.innerHTML = selected
            .map(function (opt) {
                return (
                    '<span class="dept-chip">' +
                    escHtmlBasic(opt.textContent || '') +
                    '<span class="dept-chip-x" onclick="deptRemoveId(\'' +
                    escHtmlBasic(String(opt.value || '')) +
                    "', event)\">×</span></span>"
                );
            })
            .join('');
    }

    window.deptRenderList = function deptRenderList() {
        var deptSel = deptGetSelect();
        var list = document.getElementById('dept-list');
        var search = document.getElementById('dept-search');
        if (!deptSel || !list) return;
        var q = String((search && search.value) || '').toLowerCase().trim();
        var options = Array.from(deptSel.options || []).filter(function (opt) {
            if (opt.disabled && (!opt.value || String(opt.value) === '')) return false;
            return !q || String(opt.textContent || '').toLowerCase().indexOf(q) !== -1;
        });
        if (!options.length) {
            list.innerHTML = '<div class="dept-no-result">No department found</div>';
            return;
        }
        list.innerHTML = options
            .map(function (opt) {
                var picked = !!opt.selected;
                return (
                    '<div class="dept-opt ' +
                    (picked ? 'picked' : '') +
                    '" onclick="deptToggleId(\'' +
                    escHtmlBasic(String(opt.value || '')) +
                    "')\">" +
                    '<span class="dept-opt-cb"><svg class="dept-opt-ck" viewBox="0 0 16 16" fill="none"><path d="M3.5 8.2l3 3L12.5 5" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></span>' +
                    '<span class="dept-opt-name">' +
                    escHtmlBasic(opt.textContent || '') +
                    '</span></div>'
                );
            })
            .join('');
    };

    window.deptToggleId = function deptToggleId(id) {
        var deptSel = deptGetSelect();
        if (!deptSel) return;
        var opt = deptSel.querySelector('option[value="' + String(id) + '"]');
        if (!opt) return;
        opt.selected = !opt.selected;
        deptRenderFromSelect();
        window.deptRenderList();
    };

    window.deptRemoveId = function deptRemoveId(id, e) {
        if (e) e.stopPropagation();
        var deptSel = deptGetSelect();
        if (!deptSel) return;
        var opt = deptSel.querySelector('option[value="' + String(id) + '"]');
        if (!opt) return;
        opt.selected = false;
        deptRenderFromSelect();
        window.deptRenderList();
    };

    window.deptBoxClick = function deptBoxClick(e) {
        if (e) e.stopPropagation();
        var deptSection = document.getElementById('deptMultiSection');
        if (deptSection && deptSection.classList.contains('is-locked')) return;
        var dd = document.getElementById('dept-dd');
        var box = document.getElementById('dept-box');
        var search = document.getElementById('dept-search');
        if (!dd || !box) return;
        var open = dd.style.display !== 'none';
        if (open) {
            dd.style.display = 'none';
            box.classList.remove('open');
        } else {
            dd.style.display = '';
            box.classList.add('open');
            window.deptRenderList();
            if (search) setTimeout(function () { search.focus(); }, 0);
        }
    };

    function populateDepartmentsForOrgSbu(orgId, sbuId) {
        var deptSel = document.getElementById('dept_select');
        var deptSection = document.getElementById('deptMultiSection');
        var deptBox = document.getElementById('dept-box');
        var deptDd = document.getElementById('dept-dd');
        if (!deptSel || !deptSection) return;
        if (deptDd) deptDd.style.display = 'none';
        if (deptBox) deptBox.classList.remove('open');
        deptSel.innerHTML = '';
        if (!orgId || !sbuId) {
            deptSel.disabled = true;
            deptSection.classList.add('is-locked');
            setDeptMultiHint('Select Organization and SBU to enable this list. Optional.');
            deptRenderFromSelect();
            window.deptRenderList();
            return;
        }
        deptSel.disabled = false;
        deptSection.classList.remove('is-locked');
        var org = orgById(orgId);
        var sbu =
            org && org.sbus
                ? org.sbus.find(function (s) {
                      return String(s.id) === String(sbuId);
                  })
                : null;
        if (sbu && sbu.departments && sbu.departments.length) {
            sbu.departments.forEach(function (d) {
                deptSel.insertAdjacentHTML(
                    'beforeend',
                    '<option value="' + d.id + '">' + escHtmlBasic(d.name) + '</option>'
                );
            });
            setDeptMultiHint('Optional. Select one or more departments from the dropdown.');
        } else {
            deptSel.insertAdjacentHTML(
                'beforeend',
                '<option value="" disabled selected>No departments under this SBU</option>'
            );
            setDeptMultiHint('No departments are set up for this SBU yet.');
        }
        deptRenderFromSelect();
        window.deptRenderList();
    }

    window.onOrgChange = function (orgId) {
        var sbuSel = document.getElementById('sbu_select');
        var roleSel = document.getElementById('role_select');

        if (sbuSel) {
            sbuSel.innerHTML = '<option value="">— Select SBU —</option>';
            sbuSel.disabled = !orgId;
        }
        if (roleSel) {
            roleSel.innerHTML =
                '<option value="">' +
                (orgId ? '— Select SBU first —' : '— Select Organization first —') +
                '</option>';
            roleSel.disabled = true;
        }
        populateDepartmentsForOrgSbu('', '');
        if (typeof window.clearEmploymentSchedulePrefillSnapshot === 'function') {
            window.clearEmploymentSchedulePrefillSnapshot();
        }

        if (orgId && sbuSel) {
            var org = orgById(orgId);
            if (org && org.sbus) {
                org.sbus.forEach(function (s) {
                    sbuSel.insertAdjacentHTML(
                        'beforeend',
                        '<option value="' + s.id + '">' + escHtmlBasic(s.name) + '</option>'
                    );
                });
            }
        }
        window.refreshEmployeeNumberPreview();
    };

    window.onSbuChange = function (sbuId) {
        var orgEl = document.getElementById('org_select');
        var orgId = orgEl ? orgEl.value : '';
        var roleSel = document.getElementById('role_select');

        if (roleSel) {
            roleSel.innerHTML = '<option value="">— Select Role —</option>';
            roleSel.disabled = !orgId || !sbuId;
        }
        populateDepartmentsForOrgSbu(orgId, sbuId);
        if (typeof window.clearEmploymentSchedulePrefillSnapshot === 'function') {
            window.clearEmploymentSchedulePrefillSnapshot();
        }

        if (!orgId || !sbuId || !roleSel) {
            window.refreshEmployeeNumberPreview();
            return;
        }

        (window._rolesData || []).forEach(function (role) {
            if (!roleMatchesOrgAndSbu(role, orgId, sbuId)) return;
            roleSel.insertAdjacentHTML(
                'beforeend',
                '<option value="' + role.id + '">' + escHtmlBasic(role.name) + '</option>'
            );
        });
        window.refreshEmployeeNumberPreview();
    };

    window.onRoleChange = function () {
        if (typeof window.clearEmploymentSchedulePrefillSnapshot === 'function') {
            window.clearEmploymentSchedulePrefillSnapshot();
        }
        window.refreshEmployeeNumberPreview();
        if (typeof window.syncWorkArrangementUI === 'function') {
            window.syncWorkArrangementUI();
        }
    };

    function mergeSavedDepartmentOptions(savedList) {
        var deptSel = document.getElementById('dept_select');
        var deptSection = document.getElementById('deptMultiSection');
        if (!deptSel || !Array.isArray(savedList) || !savedList.length) {
            return;
        }
        deptSel.querySelectorAll('option').forEach(function (opt) {
            if (opt.disabled && (!opt.value || String(opt.value) === '')) {
                opt.remove();
            }
        });
        savedList.forEach(function (sd) {
            if (!sd || sd.id == null) {
                return;
            }
            var idStr = String(sd.id);
            if (deptSel.querySelector('option[value="' + idStr + '"]')) {
                return;
            }
            deptSel.insertAdjacentHTML(
                'beforeend',
                '<option value="' + idStr + '">' + escHtmlBasic(sd.name || 'Department') + '</option>'
            );
        });
        if (deptSection) {
            deptSection.classList.remove('is-locked');
        }
        deptSel.disabled = false;
        setDeptMultiHint('Optional. Select one or more departments from the dropdown.');
        deptRenderFromSelect();
        window.deptRenderList();
    }

    window.applyEmploymentMappingPrefill = function (d) {
        if (!d || d.organization_id == null || d.organization_id === '') {
            return;
        }
        var orgSel = document.getElementById('org_select');
        if (!orgSel) {
            return;
        }
        orgSel.value = String(d.organization_id);
        window.onOrgChange(String(d.organization_id));

        var sbuToSet = d.sbu_id;
        if (!sbuToSet && d.role_id && Array.isArray(window._rolesData)) {
            var r0 = window._rolesData.find(function (x) {
                return x && String(x.id) === String(d.role_id);
            });
            if (r0 && r0.is_organization_level && Array.isArray(window._orgsData)) {
                var org0 = window._orgsData.find(function (o) {
                    return String(o.id) === String(d.organization_id);
                });
                if (org0 && org0.sbus && org0.sbus.length) {
                    sbuToSet = org0.sbus[0].id;
                }
            }
        }

        var sbuSel = document.getElementById('sbu_select');
        if (sbuToSet && sbuSel) {
            var sid = String(sbuToSet);
            if (!sbuSel.querySelector('option[value="' + sid + '"]') && d.sbu_name) {
                sbuSel.insertAdjacentHTML(
                    'beforeend',
                    '<option value="' + sid + '">' + escHtmlBasic(d.sbu_name) + '</option>'
                );
            }
            sbuSel.value = sid;
            window.onSbuChange(sid);
        }

        mergeSavedDepartmentOptions(d.saved_departments);

        var roleSel = document.getElementById('role_select');
        if (d.role_id && roleSel) {
            var rid = String(d.role_id);
            if (!roleSel.querySelector('option[value="' + rid + '"]')) {
                var rmeta = roleById(d.role_id);
                var label = rmeta && rmeta.name ? rmeta.name : d.role_name || 'Role #' + rid;
                roleSel.insertAdjacentHTML(
                    'beforeend',
                    '<option value="' + rid + '">' + escHtmlBasic(label) + '</option>'
                );
            }
            roleSel.value = rid;
            window.onRoleChange();
        }

        var deptSel = document.getElementById('dept_select');
        if (deptSel) {
            var deptIds = d.department_ids;
            if (Array.isArray(deptIds) && deptIds.length) {
                deptIds.forEach(function (id) {
                    var opt = deptSel.querySelector('option[value="' + String(id) + '"]');
                    if (opt) {
                        opt.selected = true;
                    }
                });
            } else if (d.department_id) {
                var optOne = deptSel.querySelector('option[value="' + String(d.department_id) + '"]');
                if (optOne) {
                    optOne.selected = true;
                }
            }
            deptRenderFromSelect();
            window.deptRenderList();
        }

        if (window.__employeeEditMode && d.employee_code) {
            var disp = document.getElementById('employee_number_display');
            if (disp) {
                disp.value = d.employee_code;
                disp.placeholder = '';
            }
        }
    };

    window.refreshEmployeeNumberPreview = function () {
        var el = document.getElementById('employee_number_display');
        if (!el || window.__employeeEditMode || !window.__previewEmployeeCodeUrl) return;

        var orgEl = document.getElementById('org_select');
        var orgId = orgEl ? orgEl.value : '';
        var roleEl = document.getElementById('role_select');
        var roleId = roleEl ? roleEl.value : '';
        var sbuEl = document.getElementById('sbu_select');
        var sbuId = sbuEl ? sbuEl.value : '';

        if (!orgId || !sbuId || !roleId) {
            el.value = '';
            el.placeholder = '— Select Organization, SBU & Role —';
            return;
        }

        var role = roleById(roleId);
        if (role && !isOrgLevelRole(role) && !sbuId) {
            el.value = '';
            el.placeholder = '— Select SBU —';
            return;
        }

        var params = new URLSearchParams({ organization_id: orgId, role_id: roleId });
        if (sbuId) params.set('sbu_id', sbuId);

        fetch(window.__previewEmployeeCodeUrl + '?' + params.toString(), { headers: { Accept: 'application/json' } })
            .then(function (r) {
                return r.json();
            })
            .then(function (data) {
                if (data.success && data.code) {
                    el.value = data.code;
                    el.placeholder = '';
                } else {
                    el.value = '';
                    el.placeholder = (data.message || '—').substring(0, 80);
                }
            })
            .catch(function () {
                el.value = '';
                el.placeholder = '—';
            });
    };

    function syncHybridDaysVisibility() {
        var modeEl = document.getElementById('engagementMode');
        var engagementMode = modeEl ? modeEl.value : '';
        var hybridDaysWrapper = document.getElementById('hybridDaysWrapper');
        if (hybridDaysWrapper) {
            hybridDaysWrapper.style.display = engagementMode === 'hybrid' ? '' : 'none';
        }
    }

    window.syncWorkArrangementUI = function () {
        syncHybridDaysVisibility();
        syncStandardScheduleModeUi();
    };

    window.syncEmployeeResourceSubfields = function () {
        var empTypeEl = document.getElementById('resourceEmploymentType');
        var contractualSub = document.getElementById('employeeContractualSub');
        var timeBoundWrap = document.getElementById('timeBoundContractDates');
        var ctEl = document.getElementById('resourceContractualType');
        var v = empTypeEl ? empTypeEl.value : '';
        if (contractualSub) {
            contractualSub.style.display = v === 'contractual' ? '' : 'none';
        }
        if (v !== 'contractual' && ctEl) {
            ctEl.value = '';
        }
        var ct = ctEl ? ctEl.value : '';
        if (timeBoundWrap) {
            timeBoundWrap.style.display = v === 'contractual' && ct === 'time_bound' ? '' : 'none';
        }
        if (!(v === 'contractual' && ct === 'time_bound')) {
            var sd = document.getElementById('contract_start_date');
            var ed = document.getElementById('contract_end_date');
            if (sd) sd.value = '';
            if (ed) ed.value = '';
        }
    };

    function toggleCategoryBlocks() {
        var catEl = document.querySelector('input[name="employment_category"]:checked');
        var selectedCategory = catEl ? catEl.value : '';

        var internFields = document.getElementById('internFields');
        var employeeResourceFields = document.getElementById('employeeResourceFields');

        if (internFields) internFields.style.display = selectedCategory === 'intern' ? '' : 'none';
        if (employeeResourceFields) {
            employeeResourceFields.style.display = selectedCategory === 'employee' ? '' : 'none';
        }
        if (selectedCategory !== 'employee') {
            var empTypeEl = document.getElementById('resourceEmploymentType');
            var ctEl = document.getElementById('resourceContractualType');
            if (empTypeEl) empTypeEl.value = '';
            if (ctEl) ctEl.value = '';
            var sd = document.getElementById('contract_start_date');
            var ed = document.getElementById('contract_end_date');
            if (sd) sd.value = '';
            if (ed) ed.value = '';
        }
        window.syncEmployeeResourceSubfields();
        window.syncWorkArrangementUI();
    }

    window.toggleCategoryBlocks = toggleCategoryBlocks;

    window.syncEmploymentRoleUI = window.onRoleChange;

    document.addEventListener('DOMContentLoaded', function () {
        var orgSel = document.getElementById('org_select');
        if (orgSel && window._orgsData) {
            window._orgsData.forEach(function (o) {
                orgSel.insertAdjacentHTML(
                    'beforeend',
                    '<option value="' + o.id + '">' + escHtmlBasic(o.name) + '</option>'
                );
            });
            orgSel.addEventListener('change', function () {
                window.onOrgChange(this.value);
            });
            if (orgSel.dataset.prefill) {
                orgSel.value = orgSel.dataset.prefill;
                window.onOrgChange(orgSel.value);
                if (orgSel.dataset.prefillSbu) {
                    var sbuSel = document.getElementById('sbu_select');
                    if (sbuSel) {
                        sbuSel.value = orgSel.dataset.prefillSbu;
                        window.onSbuChange(sbuSel.value);
                    }
                }
                if (orgSel.dataset.prefillRole) {
                    var roleSel = document.getElementById('role_select');
                    if (roleSel) {
                        roleSel.value = orgSel.dataset.prefillRole;
                        window.onRoleChange();
                        var raw = orgSel.dataset.prefillDeptIds;
                        if (raw) {
                            try {
                                var arr = JSON.parse(raw);
                                var deptSel = document.getElementById('dept_select');
                                if (deptSel && Array.isArray(arr)) {
                                    arr.forEach(function (id) {
                                        var opt = deptSel.querySelector('option[value="' + id + '"]');
                                        if (opt) opt.selected = true;
                                    });
                                    deptRenderFromSelect();
                                    window.deptRenderList();
                                }
                            } catch (e) {}
                        }
                    }
                }
            }
        }

        var sbuSel = document.getElementById('sbu_select');
        if (sbuSel) {
            sbuSel.addEventListener('change', function () {
                window.onSbuChange(this.value);
            });
        }

        var roleSel = document.getElementById('role_select');
        if (roleSel) {
            roleSel.addEventListener('change', window.onRoleChange);
        }

        document.addEventListener('click', function (evt) {
            var dd = document.getElementById('dept-dd');
            var box = document.getElementById('dept-box');
            if (!dd || !box) return;
            if (!dd.contains(evt.target) && !box.contains(evt.target)) {
                dd.style.display = 'none';
                box.classList.remove('open');
            }
        });

        var deptSearch = document.getElementById('dept-search');
        if (deptSearch) {
            deptSearch.addEventListener('keydown', function (e) {
                e.stopPropagation();
            });
        }

        document.querySelectorAll('input[name="employment_category"]').forEach(function (el) {
            el.addEventListener('change', toggleCategoryBlocks);
        });
        var resourceEmpType = document.getElementById('resourceEmploymentType');
        if (resourceEmpType) {
            resourceEmpType.addEventListener('change', window.syncEmployeeResourceSubfields);
        }
        var resourceCt = document.getElementById('resourceContractualType');
        if (resourceCt) {
            resourceCt.addEventListener('change', window.syncEmployeeResourceSubfields);
        }
        var engagementModeEl = document.getElementById('engagementMode');
        if (engagementModeEl) {
            engagementModeEl.addEventListener('change', function () {
                window.syncWorkArrangementUI();
            });
        }

        document.querySelectorAll('input[name="standard_schedule_mode"]').forEach(function (r) {
            r.addEventListener('change', function () {
                window.syncWorkArrangementUI();
            });
        });

        if (!window.__employeeEditMode) {
            toggleCategoryBlocks();
        } else {
            window.syncWorkArrangementUI();
        }
        deptRenderFromSelect();
        window.deptRenderList();
    });
})();
