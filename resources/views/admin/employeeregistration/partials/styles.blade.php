    <!-- Cropper.js CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
    
    <style>
        .wizard-pane {
            display: none;
        }

        .wizard-pane.active {
            display: block;
        }

        .more-sub-pane {
            display: none;
        }

        .more-sub-pane.active {
            display: block;
        }

        .more-sub-nav {
            padding: .5rem;
            border: 1px solid #dbe3ed;
            border-radius: .8rem;
            background: #f8fafc;
        }

        .more-sub-tab {
            border: 1px solid #dbe3ed !important;
            border-radius: 999px !important;
            background: #fff !important;
            color: #334155 !important;
            font-weight: 600;
            padding: .4rem .75rem !important;
            display: inline-flex;
            align-items: center;
            gap: .45rem;
        }

        .more-sub-tab .more-step-index {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: .72rem;
            font-weight: 700;
            color: #334155;
            background: #e2e8f0;
        }

        .more-sub-tab.active {
            background: var(--main-color) !important;
            color: #fff !important;
            border-color: var(--main-color) !important;
        }

        .more-sub-tab.active .more-step-index {
            color: var(--main-color);
            background: #fff;
        }

        .family-members-wrap {
            border: 1px solid #dbe3ed;
            border-radius: .9rem;
            background: #f8fafc;
            padding: .75rem;
        }

        .family-members-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: .6rem;
        }

        .family-members-count {
            font-size: .78rem;
            font-weight: 700;
            color: #334155;
            background: #e2e8f0;
            border-radius: 999px;
            padding: .25rem .55rem;
        }

        .family-member-row {
            border: 1px solid #dbe3ed;
            border-radius: .75rem;
            background: #fff;
            padding: .6rem;
        }

        .family-member-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: .45rem;
        }

        .family-member-index {
            font-size: .78rem;
            font-weight: 700;
            color: #0f172a;
            background: #e2e8f0;
            border-radius: 999px;
            padding: .2rem .55rem;
        }

        .family-member-actions {
            display: flex;
            gap: .35rem;
        }

        .family-member-row .form-label {
            font-size: .73rem;
            font-weight: 600;
            margin-bottom: .2rem;
            color: #475569;
        }

        .family-field-preview {
            display: none;
            min-height: 38px;
            border: 1px solid #dbe3ed;
            border-radius: .375rem;
            background: #f8fafc;
            padding: .45rem .65rem;
            font-size: .86rem;
            color: #1f2937;
            align-items: center;
        }

        .family-member-row.preview-mode .family-field-input {
            display: none;
        }

        .family-member-row.preview-mode .family-field-preview {
            display: flex;
        }

        .family-member-row.preview-mode .family-nok-edit-controls {
            display: none !important;
        }

        .family-member-row:not(.preview-mode) .family-nok-preview-toolbar {
            display: none !important;
        }

        .family-nok-toggle {
            cursor: pointer;
            transition: all .2s ease;
        }

        .family-nok-locked-note {
            margin-top: .25rem;
        }

        .academic-records-wrap {
            border: 1px solid #dbe3ed;
            border-radius: .9rem;
            background: #f8fafc;
            padding: .75rem;
        }

        .academic-records-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: .6rem;
        }

        .academic-records-count {
            font-size: .78rem;
            font-weight: 700;
            color: #334155;
            background: #e2e8f0;
            border-radius: 999px;
            padding: .25rem .55rem;
        }

        .academic-record-row {
            border: 1px solid #dbe3ed;
            border-radius: .75rem;
            background: #fff;
            padding: .6rem;
        }

        .academic-record-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: .45rem;
        }

        .academic-record-index {
            font-size: .78rem;
            font-weight: 700;
            color: #0f172a;
            background: #e2e8f0;
            border-radius: 999px;
            padding: .2rem .55rem;
        }

        .academic-record-actions {
            display: flex;
            gap: .35rem;
        }

        .academic-record-row .form-label,
        .certificate-record-row .form-label,
        .employment-record-row .form-label {
            font-size: .73rem;
            font-weight: 600;
            margin-bottom: .2rem;
            color: #475569;
        }



        .academic-field-preview,
        .certificate-field-preview,
        .employment-field-preview {
            display: none;
            min-height: 38px;
            border: 1px solid #dbe3ed;
            border-radius: .375rem;
            background: #f8fafc;
            padding: .45rem .65rem;
            font-size: .86rem;
            color: #1f2937;
            align-items: center;
        }

        .academic-record-row.preview-mode .academic-field-input,
        .certificate-record-row.preview-mode .certificate-field-input,
        .employment-record-row.preview-mode .employment-field-input,
        .medical-record-row.preview-mode .form-control,
        .medical-record-row.preview-mode .form-select,
        .medical-record-row.preview-mode .btn-check + label {
            display: none;
        }

        .academic-record-row.preview-mode .academic-field-preview,
        .certificate-record-row.preview-mode .certificate-field-preview,
        .employment-record-row.preview-mode .employment-field-preview,
        .medical-record-row.preview-mode .academic-field-preview {
            display: flex;
        }

        .employment-records-wrap {
            border: 1px solid #dbe3ed;
            border-radius: .9rem;
            background: #f8fafc;
            padding: .75rem;
        }

        .employment-records-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: .6rem;
        }

        .employment-records-count {
            font-size: .78rem;
            font-weight: 700;
            color: #334155;
            background: #e2e8f0;
            border-radius: 999px;
            padding: .25rem .55rem;
        }

        .employment-record-row {
            border: 1px solid #dbe3ed;
            border-radius: .75rem;
            background: #fff;
            padding: .6rem;
        }

        .employment-record-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: .45rem;
        }

        .employment-record-index {
            font-size: .78rem;
            font-weight: 700;
            color: #0f172a;
            background: #e2e8f0;
            border-radius: 999px;
            padding: .2rem .55rem;
        }

        .employment-record-actions {
            display: flex;
            gap: .35rem;
        }

        .employment-record-row .form-label {
            font-size: .73rem;
            font-weight: 600;
            margin-bottom: .2rem;
            color: #475569;
        }

        .employment-field-preview {
            display: none;
            min-height: 38px;
            border: 1px solid #dbe3ed;
            border-radius: .375rem;
            background: #f8fafc;
            padding: .45rem .65rem;
            font-size: .86rem;
            color: #1f2937;
            align-items: center;
        }

        .employment-record-row.preview-mode .employment-field-input {
            display: none;
        }

        .employment-record-row.preview-mode .employment-field-preview {
            display: flex;
        }

        .certificate-record-row .form-label {
            font-size: .73rem;
            font-weight: 600;
            margin-bottom: .2rem;
            color: #475569;
        }

        .certificate-field-preview {
            display: none;
            min-height: 38px;
            border: 1px solid #dbe3ed;
            border-radius: .375rem;
            background: #f8fafc;
            padding: .45rem .65rem;
            font-size: .86rem;
            color: #1f2937;
            align-items: center;
        }

        .certificate-record-row.preview-mode .certificate-field-input {
            display: none;
        }

        .certificate-record-row.preview-mode .certificate-field-preview {
            display: flex;
        }

        .emp-dept-input-box {
            background: #fff;
            border: 1px solid #ced4da;
            border-radius: .75rem;
            padding: 8px 36px 8px 10px;
            min-height: 46px;
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            align-items: center;
            cursor: text;
            position: relative;
        }
        .emp-dept-input-box.open,
        .emp-dept-input-box:focus-within {
            border-color: #86b7fe;
            box-shadow: 0 0 0 3px rgba(13,110,253,.12);
        }
        .emp-dept-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #e9f2ff;
            border: 1px solid #b6d4fe;
            color: #0a3060;
            font-size: 12px;
            font-weight: 500;
            padding: 3px 8px 3px 10px;
            border-radius: 999px;
        }
        .emp-dept-chip-x {
            width: 16px;
            height: 16px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: #185FA5;
        }
        .emp-dept-chip-x:hover {
            background: #85B7EB;
            color: #042C53;
        }
        .emp-dept-ph {
            font-size: 14px;
            color: #adb5bd;
            padding: 2px 4px;
            pointer-events: none;
        }
        .emp-dept-chevron {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            pointer-events: none;
            color: #adb5bd;
            transition: transform .18s;
        }
        .emp-dept-input-box.open .emp-dept-chevron {
            transform: translateY(-50%) rotate(180deg);
        }
        .emp-dept-dropdown {
            background: #fff;
            border: 1px solid #ced4da;
            border-radius: .75rem;
            margin-top: 6px;
            overflow: hidden;
            z-index: 1050;
            position: relative;
        }
        .emp-dept-search-row {
            padding: 8px;
            border-bottom: 1px solid #f0f0f0;
        }
        .emp-dept-search-row input {
            width: 100%;
            border: 1px solid #ced4da;
            border-radius: 8px;
            padding: 7px 12px;
            font-size: 13px;
            background: #f8f9fa;
            color: #212529;
            outline: none;
        }
        .emp-dept-opt-list {
            max-height: 210px;
            overflow-y: auto;
            padding: 4px 0;
        }
        .emp-dept-opt {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 14px;
            cursor: pointer;
            font-size: 14px;
            color: #212529;
        }
        .emp-dept-opt:hover {
            background: #f8f9fa;
        }
        .emp-dept-opt.picked {
            background: #e9f2ff;
        }
        .emp-dept-opt-cb {
            width: 17px;
            height: 17px;
            border-radius: 5px;
            border: 1.5px solid #adb5bd;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .emp-dept-opt.picked .emp-dept-opt-cb {
            background: #0d6efd;
            border-color: #0d6efd;
        }
        .emp-dept-opt-ck {
            display: none;
            width: 10px;
            height: 10px;
        }
        .emp-dept-opt.picked .emp-dept-opt-ck {
            display: block;
        }
        .emp-dept-no-result {
            padding: 14px;
            font-size: 13px;
            color: #adb5bd;
            text-align: center;
        }

        .option-chip {
            min-width: 96px;
            text-align: center;
            font-weight: 600;
            border-radius: 999px !important;
        }

        .btn-check:checked + .option-chip {
            background: var(--main-color) !important;
            border-color: var(--main-color) !important;
            color: #fff !important;
        }

        .profile-tab.active {
            color: #012445 !important;
            background: var(--primary-color) !important;
        }

        /* Locked Tab Styles */
        .profile-tab.locked-tab {
            opacity: 0.5 !important;
            cursor: not-allowed !important;
            filter: grayscale(100%);
            border-bottom: 2px solid transparent !important;
        }

        .profile-tab.locked-tab:hover {
            background: transparent !important;
            color: #fff !important;
        }

        #employeeForm:not(.form-readonly) #avatarPreviewContainer:hover .avatar-upload-overlay,
        #employeeForm:not(.form-readonly) #avatarPreviewContainer:hover .remove-photo-btn {
            opacity: 1;
            pointer-events: auto;
        }
        .avatar-upload-overlay,
        .remove-photo-btn {
            opacity: 0;
            pointer-events: none;
            transition: opacity .2s ease;
        }

        .card {
            box-shadow: none !important;
            border: 1px solid #0124452b !important;
        }

        /* Cropper Styles */
        .cropper-container-wrapper {
            max-height: 500px; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            overflow: hidden;
            background: #f8fafc;
        }
        #cropperImage {
            max-width: 100%;
            display: block;
        }

        /* Inline validation errors (employee registration wizard) */
        .field-error-msg {
            font-size: 0.75rem !important;
            line-height: 1.15 !important;
            font-weight: 600 !important;
        }

        .employment-termination-reason-input {
            resize: none;
            min-height: 4.25rem;
            max-height: 7rem;
            overflow-y: auto;
            line-height: 1.45;
            padding-top: 0.45rem;
            padding-bottom: 0.45rem;
        }

        .form-readonly .emp-dept-input-box {
             cursor: not-allowed !important;
             background-color: #f1f5f9 !important;
        }

        .form-readonly .emp-dept-chip-x {
             display: none !important;
        }

        /* UNBLOCKABLE READONLY STYLES */
        #employeeForm.form-readonly,
        #employeeForm.form-readonly .wizard-pane,
        #employeeForm.form-readonly .wizard-pane *,
        #employeeForm.form-readonly .card,
        #employeeForm.form-readonly .card * {
            cursor: not-allowed !important;
        }

        /* EXCEPT FOR NAV */
        #employeeForm.form-readonly #prevBtn,
        #employeeForm.form-readonly #nextBtn,
        #employeeForm.form-readonly #editBtn,
        #employeeForm.form-readonly .profile-tab,
        #employeeForm.form-readonly .more-sub-tab,
        #employeeForm.form-readonly #prevBtn *,
        #employeeForm.form-readonly #nextBtn *,
        #employeeForm.form-readonly #editBtn *,
        #employeeForm.form-readonly .profile-tab *,
        #employeeForm.form-readonly .more-sub-tab * {
            cursor: pointer !important;
            pointer-events: auto !important;
        }

        /* DISABLE INPUTS AND SELECTS IN READONLY */
        #employeeForm.form-readonly input,
        #employeeForm.form-readonly select,
        #employeeForm.form-readonly textarea,
        #employeeForm.form-readonly button:not(#prevBtn):not(#nextBtn):not(#editBtn) {
            pointer-events: none !important;
        }

        /* Restore interaction for navigation and edit controls */
        .form-readonly #prevBtn,
        .form-readonly #nextBtn,
        .form-readonly #editBtn,
        .form-readonly .profile-tab,
        .form-readonly .more-sub-tab {
            cursor: pointer !important;
            pointer-events: auto !important;
        }

        .form-readonly .avatar-upload-overlay,
        .form-readonly .remove-photo-btn {
             display: none !important;
             opacity: 0 !important;
             pointer-events: none !important;
        }

        /* Avatar Hover Effects in Edit Mode */
        #avatarPreviewContainer .remove-photo-btn {
            opacity: 0;
            visibility: hidden;
            transition: all 0.2s ease-in-out;
            transform: scale(0.8);
        }

        #avatarPreviewContainer:hover .remove-photo-btn {
            opacity: 1;
            visibility: visible;
            transform: scale(1);
        }

        /* Also hide overlay by default and show on hover */
        .avatar-upload-overlay {
            opacity: 0 !important;
            visibility: hidden;
            transition: all 0.2s ease-in-out;
        }

        #avatarPreviewWrap:hover .avatar-upload-overlay {
            opacity: 1 !important;
            visibility: visible;
        }

        #avatarPreviewWrap,
        #avatarPreviewImage {
            cursor: pointer;
        }

        /* Academic Certificate Section Enhancements */
        .academic-transcript-section,
        .academic-degree-section {
            margin-top: 0.75rem;
        }

        /* Doc card wrapper */
        .academic-doc-card {
            border: 1.5px solid #e2e8f0;
            border-radius: 0.85rem;
            background: #fff;
            overflow: hidden;
        }

        /* Doc card header */
        .academic-doc-header {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.45rem 0.75rem;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            font-size: 0.78rem;
            font-weight: 700;
            color: #334155;
        }

        .academic-doc-header i {
            font-size: 0.85rem;
            color: #64748b;
        }

        .academic-doc-hint {
            font-size: 0.68rem;
            font-weight: 500;
            color: #94a3b8;
            letter-spacing: 0.3px;
        }

        /* Upload zone */
        .academic-upload-placeholder {
            border: 1.5px dashed #cbd5e1;
            border-radius: 0;
            padding: 0.45rem 0.85rem;
            min-height: 48px;
            display: flex;
            align-items: center;
            justify-content: flex-start;
            cursor: pointer;
            transition: background 0.18s ease, border-color 0.18s ease;
            background: #fff;
            gap: 0.6rem;
        }

        /* Remove outer border-radius for first upload child inside card */
        .academic-doc-card .academic-upload-placeholder {
            border-left: none;
            border-right: none;
            border-bottom: none;
        }

        .academic-upload-placeholder:hover {
            background: #f1f5f9;
            border-color: #6366f1;
        }

        .academic-upload-icon-wrap {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #eff6ff;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .academic-upload-icon-wrap i {
            font-size: 1.1rem;
            color: #3b82f6;
        }

        .academic-upload-label {
            font-size: 0.8rem;
            font-weight: 600;
            color: #374151;
        }

        .academic-upload-types {
            font-size: 0.6rem;
            color: #94a3b8;
            letter-spacing: 0.2px;
            margin-top: 0px;
        }

        /* File badge row (shown when file selected or saved) */
        .academic-file-badge {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.45rem 0.85rem;
            min-height: 48px;
            background: #fafafa;
        }

        /* Not-provided preview text */
        .academic-not-provided {
            display: none;
            align-items: center;
            padding: 0.45rem 0.85rem;
            min-height: 48px;
            font-size: 0.78rem;
            color: #94a3b8;
            font-style: italic;
            background: #fafafa;
        }

        /* In preview mode, academic-field-preview elements show */
        .academic-record-row.preview-mode .academic-not-provided,
        .certificate-record-row.preview-mode .academic-not-provided,
        .employment-record-row.preview-mode .academic-not-provided,
        .medical-record-row.preview-mode .academic-not-provided {
            display: flex !important;
        }
        /* But hide those with d-none (meaning file is present) */
        .academic-record-row.preview-mode .academic-not-provided.d-none,
        .certificate-record-row.preview-mode .academic-not-provided.d-none,
        .employment-record-row.preview-mode .academic-not-provided.d-none,
        .medical-record-row.preview-mode .academic-not-provided.d-none {
            display: none !important;
        }

        /* Hide upload zone, view and delete controls in preview mode */
        .academic-record-row.preview-mode .academic-upload-placeholder,
        .academic-record-row.preview-mode .academic-doc-delete-btn,
        .academic-record-row.preview-mode .academic-doc-view-btn,
        .certificate-record-row.preview-mode .academic-upload-placeholder,
        .certificate-record-row.preview-mode .academic-doc-delete-btn,
        .certificate-record-row.preview-mode .academic-doc-view-btn,
        .employment-record-row.preview-mode .academic-upload-placeholder,
        .employment-record-row.preview-mode .academic-doc-delete-btn,
        .employment-record-row.preview-mode .academic-doc-view-btn,
        .medical-record-row.preview-mode .academic-upload-placeholder,
        .medical-record-row.preview-mode .academic-doc-delete-btn,
        .medical-record-row.preview-mode .academic-doc-view-btn {
            display: none !important;
        }

        .btn-icon-delete {
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            color: #ef4444;
            background: transparent;
            border: none;
            transition: all 0.2s;
        }

        .btn-icon-delete:hover {
            background: #fee2e2;
            color: #b91c1c;
        }
    </style>
