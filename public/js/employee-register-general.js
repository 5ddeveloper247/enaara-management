(function () {
    function getRoutes() {
        return window.__employeeRegisterGeneral || {};
    }

    function initLocationCascades() {
        var natSelect = document.getElementById('nationality_select');
        var provSelect = document.getElementById('province_select');
        var distSelect = document.getElementById('district_select');
        var spouseNatSelect = document.getElementById('spouse_nationality');
        var u = getRoutes();

        if (!natSelect || !provSelect || !distSelect || !spouseNatSelect || !u.countriesUrl) {
            return;
        }

        function loadCountries() {
            fetch(u.countriesUrl)
                .then(function (r) {
                    return r.json();
                })
                .then(function (countries) {
                    countries.forEach(function (c) {
                        var opt1 = document.createElement('option');
                        opt1.value = c.name;
                        opt1.textContent = c.name;
                        natSelect.appendChild(opt1);

                        var opt2 = document.createElement('option');
                        opt2.value = c.name;
                        opt2.textContent = c.name;
                        spouseNatSelect.appendChild(opt2);
                    });

                    if (natSelect.dataset.prefill) {
                        natSelect.value = natSelect.dataset.prefill;
                        loadProvinces(natSelect.dataset.prefill);
                    }
                    if (spouseNatSelect.dataset.prefill) {
                        spouseNatSelect.value = spouseNatSelect.dataset.prefill;
                    }
                });
        }

        function loadProvinces(countryName) {
            provSelect.innerHTML = '<option value="">— Select Province —</option>';
            distSelect.innerHTML = '<option value="">— Select District —</option>';
            if (!countryName || !u.provincesBase) return;

            fetch(u.provincesBase + '/' + encodeURIComponent(countryName))
                .then(function (r) {
                    return r.json();
                })
                .then(function (provinces) {
                    provinces.forEach(function (p) {
                        var opt = document.createElement('option');
                        opt.value = p.name;
                        opt.textContent = p.name;
                        provSelect.appendChild(opt);
                    });
                    if (provSelect.dataset.prefill) {
                        provSelect.value = provSelect.dataset.prefill;
                        loadDistricts(countryName, provSelect.dataset.prefill);
                    }
                });
        }

        function loadDistricts(countryName, provinceName) {
            distSelect.innerHTML = '<option value="">— Select District —</option>';
            if (!countryName || !provinceName || !u.districtsBase) return;

            fetch(
                u.districtsBase +
                    '/' +
                    encodeURIComponent(countryName) +
                    '/' +
                    encodeURIComponent(provinceName)
            )
                .then(function (r) {
                    return r.json();
                })
                .then(function (districts) {
                    districts.forEach(function (d) {
                        var opt = document.createElement('option');
                        opt.value = d.name;
                        opt.textContent = d.name;
                        distSelect.appendChild(opt);
                    });
                    if (distSelect.dataset.prefill) {
                        distSelect.value = distSelect.dataset.prefill;
                    }
                });
        }

        loadCountries();

        natSelect.addEventListener('change', function () {
            loadProvinces(this.value);
        });
        provSelect.addEventListener('change', function () {
            loadDistricts(natSelect.value, this.value);
        });
    }

    function initSpouseToggle() {
        var maritalStatusSelect = document.getElementById('marital_status');
        var spouseFieldsWrapper = document.getElementById('spouse_fields_wrapper');
        if (!maritalStatusSelect) return;

        function toggleSpouseFields() {
            var isMarried = maritalStatusSelect.value === 'Married';
            if (spouseFieldsWrapper) {
                spouseFieldsWrapper.style.display = isMarried ? 'flex' : 'none';
            }
            if (!isMarried) {
                var sn = document.getElementById('spouse_name');
                var sc = document.getElementById('spouse_cnic');
                var snat = document.getElementById('spouse_nationality');
                if (sn) sn.value = '';
                if (sc) sc.value = '';
                if (snat) snat.value = '';
            }
        }

        maritalStatusSelect.addEventListener('change', toggleSpouseFields);
        toggleSpouseFields();
    }

    window.previewImg = function (input) {
        var preview = document.getElementById('imgPreview');
        var previewWrapper = document.getElementById('imgPreviewWrapper');
        var removeBtn = document.getElementById('removeImageBtn');
        var uploadBox = document.getElementById('uploadImageBox');
        if (input.files && input.files[0]) {
            preview.src = URL.createObjectURL(input.files[0]);
            previewWrapper.style.display = 'block';
            uploadBox.classList.add('d-none');
            removeBtn.classList.remove('d-none');
        }
    };

    window.removePreviewImg = function () {
        var input = document.getElementById('uploadImage');
        var preview = document.getElementById('imgPreview');
        var previewWrapper = document.getElementById('imgPreviewWrapper');
        var removeBtn = document.getElementById('removeImageBtn');
        var uploadBox = document.getElementById('uploadImageBox');
        var employeeIdInput = document.querySelector('input[name="employee_id"]');
        var employeeId = employeeIdInput ? employeeIdInput.value : '';
        var u = getRoutes();

        if (employeeId && u.deletePhotoUrl) {
            Swal.fire({
                title: 'Are you sure?',
                text: 'You are about to delete the profile photo.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#012445',
                confirmButtonText: 'Yes, delete it!',
            }).then(function (result) {
                if (result.isConfirmed) {
                    var formData = new FormData();
                    formData.append('id', employeeId);
                    formData.append(
                        '_token',
                        document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    );
                    fetch(u.deletePhotoUrl, { method: 'POST', body: formData })
                        .then(function (r) {
                            return r.json();
                        })
                        .then(function (data) {
                            if (data.success) {
                                window.resetEmployeePreviewUi(input, preview, previewWrapper, removeBtn, uploadBox);
                                Swal.fire({
                                    toast: true,
                                    position: 'top-end',
                                    icon: 'success',
                                    title: data.message,
                                    showConfirmButton: false,
                                    timer: 3000,
                                });
                            } else {
                                Swal.fire('Error', data.message || 'Error deleting photo.', 'error');
                            }
                        });
                }
            });
        } else {
            window.resetEmployeePreviewUi(input, preview, previewWrapper, removeBtn, uploadBox);
        }
    };

    window.resetEmployeePreviewUi = function (input, preview, previewWrapper, removeBtn, uploadBox) {
        if (input) input.value = '';
        if (preview) preview.src = '';
        if (previewWrapper) previewWrapper.style.display = 'none';
        if (removeBtn) removeBtn.classList.add('d-none');
        if (uploadBox) uploadBox.classList.remove('d-none');
        if (typeof croppedImageBlob !== 'undefined') croppedImageBlob = null;
    };

    window.syncNokRelationOtherVisibility = function () {
        var typeSel = document.getElementById('nok_relation_type');
        var wrap = document.getElementById('nok_relation_other_wrapper');
        var otherInput = document.getElementById('nok_relation_other');
        if (!typeSel || !wrap) return;
        var show = typeSel.value === 'Other';
        wrap.style.display = show ? '' : 'none';
        if (!show && otherInput) otherInput.value = '';
    };

    function initNokRelationOtherToggle() {
        var typeSel = document.getElementById('nok_relation_type');
        if (!typeSel) return;
        typeSel.addEventListener('change', window.syncNokRelationOtherVisibility);
        window.syncNokRelationOtherVisibility();
    }

    function ymdLocal(d) {
        var y = d.getFullYear();
        var m = String(d.getMonth() + 1);
        if (m.length === 1) m = '0' + m;
        var day = String(d.getDate());
        if (day.length === 1) day = '0' + day;
        return y + '-' + m + '-' + day;
    }

    function initDateGuards(form) {
        if (!form) return;
        var now = new Date();
        var yest = new Date(now.getFullYear(), now.getMonth(), now.getDate() - 1);
        var tom = new Date(now.getFullYear(), now.getMonth(), now.getDate() + 1);
        var dobMax = ymdLocal(yest);
        var expMin = ymdLocal(tom);
        ['dob', 'nok_dob'].forEach(function (name) {
            var el = form.querySelector('[name="' + name + '"]');
            if (el && el.getAttribute('type') === 'date') {
                el.setAttribute('max', dobMax);
            }
        });
        ['cnic_expiry', 'nok_cnic_expiry_date'].forEach(function (name) {
            var el = form.querySelector('[name="' + name + '"]');
            if (el && el.getAttribute('type') === 'date') {
                el.setAttribute('min', expMin);
            }
        });
    }

    function filterLocalePersonName(v) {
        return String(v || '').replace(/[^\p{L}\p{M}\p{Zs}.\-'_]/gu, '');
    }

    function filterSectLabel(v) {
        var x = String(v || '').replace(/[<>]/g, '');
        return x.replace(/[^\p{L}\p{M}\p{Zs}'.,\-&/()]/gu, '');
    }

    function filterLocaleAlphanumericLabel(v) {
        var x = String(v || '').replace(/[<>]/g, '');
        return x.replace(/[^\p{L}\p{M}\p{N}\p{Zs}'.,\-#&/()]/gu, '');
    }

    function initStep1InputGuards() {
        var form = document.getElementById('employeeForm');
        if (!form) return;

        initDateGuards(form);

        var personNames = { full_name: 1, father_name: 1, spouse_name: 1, nok_name: 1 };

        form.addEventListener('input', function (e) {
            var t = e.target;
            if (!t || !t.name || t.disabled || t.readOnly) return;
            var n = t.name;

            if (personNames[n]) {
                var lim = n === 'full_name' || n === 'father_name' ? 50 : 100;
                var nv = filterLocalePersonName(t.value).substring(0, lim);
                if (nv !== t.value) t.value = nv;
                return;
            }

            if (n === 'sect' || n === 'nok_relation_other') {
                var sv = filterSectLabel(t.value).substring(0, 100);
                if (sv !== t.value) t.value = sv;
                return;
            }

            if (n === 'city_of_birth') {
                var cv = filterLocaleAlphanumericLabel(t.value).substring(0, 100);
                if (cv !== t.value) t.value = cv;
                return;
            }

            if (n === 'ntn') {
                var digits = String(t.value || '').replace(/\D/g, '').substring(0, 13);
                if (digits !== t.value) t.value = digits;
                return;
            }

            if (n === 'nok_contact') {
                var cc = String(t.value || '').replace(/\D/g, '').substring(0, 15);
                if (cc !== t.value) t.value = cc;
                return;
            }

            if (/^family\[[^\]]+\]\[name\]$/.test(n)) {
                var fn = filterLocalePersonName(t.value).substring(0, 100);
                if (fn !== t.value) t.value = fn;
                return;
            }

            if (/^family\[[^\]]+\]\[relation\]$/.test(n)) {
                var fr = filterSectLabel(t.value).substring(0, 100);
                if (fr !== t.value) t.value = fr;
                return;
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        initLocationCascades();
        initSpouseToggle();
        initNokRelationOtherToggle();
        initStep1InputGuards();
        if (typeof applyCnicMasks === 'function') {
            applyCnicMasks();
        }
    });
})();
