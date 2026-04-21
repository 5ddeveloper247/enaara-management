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

        .family-nok-toggle:hover {
            border-color: #93c5fd !important;
            background: #f8fbff !important;
        }

        .family-nok-toggle.active {
            border-color: #198754 !important;
            background: #f0fdf4 !important;
            box-shadow: 0 0 0 2px rgba(25, 135, 84, 0.12) inset;
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

        .academic-record-row .form-label {
            font-size: .73rem;
            font-weight: 600;
            margin-bottom: .2rem;
            color: #475569;
        }

        .academic-field-preview {
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

        .academic-record-row.preview-mode .academic-field-input {
            display: none;
        }

        .academic-record-row.preview-mode .academic-field-preview {
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

        #avatarPreviewContainer:hover .avatar-upload-overlay,
        #avatarPreviewContainer:hover .remove-photo-btn {
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
    </style>
