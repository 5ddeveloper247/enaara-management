(function () {
    window.validateEmployeeRegisterStep1 = function (api) {
        var valid = true;
        var firstEl = null;
        var markFieldInvalid = api.markFieldInvalid;
        var markRadioInvalid = api.markRadioInvalid;

        function req(name, label) {
            var el = document.querySelector('[name="' + name + '"]');
            if (!el) return;
            var val = el.value ? el.value.trim() : '';
            if (!val) {
                markFieldInvalid(el, label + ' is required.');
                if (!firstEl) firstEl = el;
                valid = false;
            }
        }

        function reqRadio(name, label) {
            var checked = document.querySelector('[name="' + name + '"]:checked');
            if (!checked) {
                markRadioInvalid(name, label + ' is required.');
                var first = document.querySelector('[name="' + name + '"]');
                if (!firstEl && first) firstEl = first;
                valid = false;
            }
        }

        req('full_name', 'Name');
        req('father_name', 'Father Name');
        req('cnic', 'CNIC');
        req('cnic_expiry', 'CNIC Expiry');
        req('father_cnic', 'Father CNIC');
        req('dob', 'Date of Birth');
        req('nationality', 'Nationality');
        req('gender', 'Gender');
        req('domicile_province', 'Province');
        req('domicile_district', 'District');
        req('religion', 'Religion');
        req('sect', 'Sect');
        req('marital_status', 'Marital Status');
        var maritalEl = document.querySelector('[name="marital_status"]');
        if (maritalEl && maritalEl.value === 'Married') {
            req('spouse_name', 'Spouse Name');
            req('spouse_cnic', 'Spouse CNIC');
            req('spouse_nationality', 'Spouse Nationality');
        }
        req('nok_name', 'NOK Name');
        req('nok_cnic', 'NOK CNIC');
        req('nok_cnic_expiry_date', 'NOK CNIC Expiry');
        req('nok_relation_type', 'Relation with NOK');
        var nokType = document.querySelector('[name="nok_relation_type"]');
        if (nokType && nokType.value === 'Other') {
            req('nok_relation_other', 'Specify relation with NOK');
        }
        req('nok_dob', 'NOK DOB');
        req('nok_contact', 'NOK Contact');

        return { valid: valid, firstEl: firstEl };
    };
})();
