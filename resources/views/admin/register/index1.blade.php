@extends('layouts.app')

@section('title', 'Employee Profile Wizard - Admin Panel')

@section('page-title', 'Employee Profile Wizard')

@push('styles')
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

        #avatarPreviewWrap:hover .avatar-upload-overlay {
            opacity: 1;
        }
        .avatar-upload-overlay {
            opacity: 0;
            transition: opacity .2s ease;
        }

        .card {
            box-shadow: none !important;
            border: 1px solid #0124452b !important;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid py-2">
        <div class="d-flex justify-content-between mb-4 align-items-center">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('admin.employee.index') }}"
                    class="btn btn-secondary d-flex align-items-center border-0 px-3">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <h5 class="mb-0">Employee Registration</h5>
            </div>
            <button class="btn text-white bg-main border-0 px-3">Save Draft</button>
        </div>

        <div class="row">
            <div class="col-md-3">
                <aside class="card border-0 shadow-sm overflow-hidden sticky-top">
                    <div class="bg-main text-center p-2">
                        <div class="rounded-circle mx-auto mt-2 d-flex align-items-center justify-content-center position-relative overflow-hidden border-2 border-white shadow-sm bg-secondary-subtle text-secondary"
                            id="avatarPreviewWrap" style="width: 110px; height: 110px;">
                            <img id="avatarPreviewImage" alt="Employee photo preview" class="w-100 h-100 object-fit-cover d-none">
                            <i class="bi bi-person-fill" id="avatarPlaceholderIcon"></i>
                            <label class="avatar-upload-overlay position-absolute top-0 start-0 w-100 h-100 rounded-circle bg-dark bg-opacity-50 text-white d-flex flex-column align-items-center justify-content-center gap-1 small fw-semibold"
                                for="profilePhotoInput">
                                <i class="bi bi-cloud-arrow-up"></i>
                                <span>Upload Photo</span>
                            </label>
                        </div>
                        <input type="file" id="profilePhotoInput" accept="image/*" class="d-none">
                        <div class="small fw-bold text-center text-white mt-2">Shehryar Shahid</div>
                        <div class="text-white small opacity-50">nag837</div>
                    </div>
                    
                    <div class="card-body bg-white p-3">
                        <ul class="list-unstyled mb-0">
                            <li class="d-flex align-items-center gap-2 py-2 small">
                                <i class="bi bi-person-fill text-secondary"></i>
                                <span class="text-secondary-emphasis fw-semibold opacity-75">Name</span>
                                <span class="ms-auto text-end fw-semibold text-dark" id="summaryName">Not provided</span>
                            </li>
                            <li class="d-flex align-items-center gap-2 py-2 small">
                                <i class="bi bi-credit-card-2-front-fill text-secondary"></i>
                                <span class="text-secondary-emphasis fw-semibold opacity-75">CNIC</span>
                                <span class="ms-auto text-end fw-semibold text-dark" id="summaryCnic">Not provided</span>
                            </li>
                            <li class="d-flex align-items-center gap-2 py-2 small">
                                <i class="bi bi-gender-ambiguous text-secondary"></i>
                                <span class="text-secondary-emphasis fw-semibold opacity-75">Gender</span>
                                <span class="ms-auto text-end fw-semibold text-dark" id="summaryGender">Not selected</span>
                            </li>
                            <li class="d-flex align-items-center gap-2 py-2 small">
                                <i class="bi bi-star-fill text-secondary"></i>
                                <span class="text-secondary-emphasis fw-semibold opacity-75">Religion</span>
                                <span class="ms-auto text-end fw-semibold text-dark" id="summaryReligion">Not selected</span>
                            </li>
                            <li class="d-flex align-items-center gap-2 py-2 small">
                                <i class="bi bi-globe-central-south-asia text-secondary"></i>
                                <span class="text-secondary-emphasis fw-semibold opacity-75">Nationality</span>
                                <span class="ms-auto text-end fw-semibold text-dark" id="summaryNationality">Not selected</span>
                            </li>
                        </ul>
                    </div>
                </aside>
            </div>
    
            <div class="col-md-9">
                <div class="card border-0 shadow-sm overflow-hidden bg-white">
                    <div class="d-flex flex-wrap bg-main">
                        <button type="button"
                            class="profile-tab btn btn-link text-decoration-none text-white fw-semibold px-3 py-3 rounded-0 border-0 active"
                            data-step="1">
                            <i class="bi bi-person-badge me-1"></i>
                            General Details
                        </button>
                        <button type="button"
                            class="profile-tab btn btn-link text-decoration-none text-white fw-semibold px-3 py-3 rounded-0 border-0"
                            data-step="2">
                            <i class="bi bi-people me-1"></i>
                            Employment Details
                        </button>
                        <button type="button"
                            class="profile-tab btn btn-link text-decoration-none text-white fw-semibold px-3 py-3 rounded-0 border-0"
                            data-step="3">
                            <i class="bi bi-file-earmark-text me-1"></i>
                            Police Verification
                        </button>
                        <button type="button"
                            class="profile-tab btn btn-link text-decoration-none text-white fw-semibold px-3 py-3 rounded-0 border-0"
                            data-step="4">
                            <i class="bi bi-signpost-2 me-1"></i>
                            Armed Details
                        </button>
                        <button type="button"
                            class="profile-tab btn btn-link text-decoration-none text-white fw-semibold px-3 py-3 rounded-0 border-0"
                            data-step="5">
                            <i class="bi bi-bank2 me-1"></i>
                            Bank Details
                        </button>
                        <button type="button"
                            class="profile-tab btn btn-link text-decoration-none text-white fw-semibold px-3 py-3 rounded-0 border-0"
                            data-step="6">
                            <i class="bi bi-plus-circle me-1"></i>
                            More
                        </button>
                    </div>
        
                    <div class="card-body p-3">
                        {{-- STEP 1: General Information --}}
                        <div class="wizard-pane active" id="stepPane1">
                            <div>
                                <section class="d-grid gap-3">
                                    <div class="card border-0 shadow-sm bg-light">
                                        <div class="card-body p-3">
                                            <div class="fw-bold text-dark mb-3">
                                            <span>Personal Information</span>
                                        </div>
                                        <div class="row g-3 w-100">
                                            <div class="col-12">
                                                <div class="d-flex flex-wrap gap-4 justify-content-end">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="giExArmyRetiredCheckbox">
                                                        <label class="form-check-label" for="giExArmyRetiredCheckbox">Ex-Army Retired</label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="giFatherDeceasedCheckbox">
                                                        <label class="form-check-label" for="giFatherDeceasedCheckbox">Father Deceased</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="giNameInput" placeholder="Enter full name">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Father Name</label>
                                                <input type="text" class="form-control" placeholder="Enter father name">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">CNIC <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="giCnicInput" placeholder="00000-0000000-0">
                                            </div>
                                            <div class="col">
                                                <label class="form-label">CNIC Expiry Date <span class="text-danger">*</span></label>
                                                <input type="date" class="form-control" placeholder="yyyy-mm-dd">
                                            </div>
                                            <div class="col" id="giFatherCnicField">
                                                <label class="form-label">Father CNIC</label>
                                                <input type="text" class="form-control" id="giFatherCnicInput" placeholder="00000-0000000-0">
                                            </div>
                                            <div class="col">
                                                <label class="form-label">NTN #</label>
                                                <input type="text" class="form-control" placeholder="Enter NTN number">
                                            </div>
                                        </div>
                                    </div>
                                </div>
        
                                    <div class="row g-3">
                                        <div class="col-12 col-xl-6">
                                            <div class="card border-0 bg-light h-100">
                                                <div class="card-body p-3">
                                                    <div class="fw-bold text-uppercase small mb-3">Birth and Domicile</div>
                                                    <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label">Date of Birth <span class="text-danger">*</span></label>
                                                    <input type="date" class="form-control" placeholder="yyyy-mm-dd">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Town / City of Birth</label>
                                                    <input type="text" class="form-control" placeholder="Enter town or city">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Nationality <span class="text-danger">*</span></label>
                                                    <select class="form-select" id="giNationalityInput">
                                                        <option selected disabled>-- Select Nationality --</option>
                                                        <option>Pakistani</option>
                                                        <option>Other</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Gender</label>
                                                    <select class="form-select" id="giGenderInput">
                                                        <option selected disabled>-- Select --</option>
                                                        <option>Male</option>
                                                        <option>Female</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">District</label>
                                                    <select class="form-select">
                                                        <option value="" selected disabled>Select district</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Province</label>
                                                    <select class="form-select">
                                                        <option value="" selected disabled>Select province</option>
                                                        <option>Punjab</option>
                                                        <option>Sindh</option>
                                                        <option>Khyber Pakhtunkhwa</option>
                                                        <option>Balochistan</option>
                                                        <option>Islamabad Capital Territory</option>
                                                        <option>Gilgit-Baltistan</option>
                                                        <option>Azad Jammu and Kashmir</option>
                                                    </select>
                                                </div>
                                            </div>
                                                </div>
                                            </div>
                                        </div>
        
                                        <div class="col-12 col-xl-6">
                                            <div class="card border-0 bg-light h-100">
                                                <div class="card-body p-3">
                                                    <div class="fw-bold text-uppercase small mb-3">Religion and Marital</div>
                                                    <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label">Religion</label>
                                                    <select class="form-select" id="giReligionInput">
                                                        <option selected disabled>-- Select --</option>
                                                        <option>Islam</option>
                                                        <option>Other</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Sect</label>
                                                    <input type="text" class="form-control" placeholder="Enter sect">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">Marital Status <span class="text-danger">*</span></label>
                                                    <select class="form-select">
                                                        <option selected disabled>Select</option>
                                                        <option>Single</option>
                                                        <option>Married</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">Spouse Name</label>
                                                    <input type="text" class="form-control" placeholder="Enter spouse name">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">Spouse Nationality</label>
                                                    <input type="text" class="form-control" placeholder="Enter spouse nationality">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">Spouse CNIC</label>
                                                    <input type="text" class="form-control" placeholder="00000-0000000-0">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">Spouse CNIC Expiry</label>
                                                    <input type="date" class="form-control" placeholder="yyyy-mm-dd">
                                                </div>
                                            </div>
                                                </div>
                                            </div>
                                        </div>
        
                                        <div class="col-12">
                                            <div class="card border-0 bg-light">
                                                <div class="card-body p-3">
                                                    <div class="fw-bold text-uppercase small mb-3">Next of Kin (NOK)</div>
                                                    <div class="row g-3">
                                                <div class="col-md-4">
                                                    <label class="form-label">Name of Next of Kin</label>
                                                    <input type="text" class="form-control" placeholder="Enter NOK name">
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label">Relation with NOK</label>
                                                    <select class="form-select" id="giNokRelationSelect">
                                                        <option value="" selected disabled>Select relation</option>
                                                        <option value="Son">Son</option>
                                                        <option value="Daughter">Daughter</option>
                                                        <option value="Spouse">Spouse</option>
                                                        <option value="Brother">Brother</option>
                                                        <option value="Sister">Sister</option>
                                                        <option value="Father">Father</option>
                                                        <option value="Mother">Mother</option>
                                                        <option value="Guardian">Guardian</option>
                                                        <option value="Other">Other</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-2 d-none" id="giNokSpecifyRelationField">
                                                    <label class="form-label">Specify Relation</label>
                                                    <input type="text" class="form-control" id="giNokSpecifyRelationInput" placeholder="Specify relation">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">NOK Date of Birth</label>
                                                    <input type="date" class="form-control" placeholder="yyyy-mm-dd">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label">NOK CNIC</label>
                                                    <input type="text" class="form-control" placeholder="00000-0000000-0">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label">NOK CNIC Expiry</label>
                                                    <input type="date" class="form-control" placeholder="yyyy-mm-dd">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">NOK Contact No</label>
                                                    <input type="text" class="form-control" placeholder="03XXXXXXXXX">
                                                </div>
                                            </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </section>
                            </div>
                        </div>
                        
                        {{-- STEP 2: Employment Information --}}
                        <div class="wizard-pane" id="stepPane2">
                            <div>
                                <div class="card bg-light border-0 shadow-sm mb-3">
                                    <div class="card-body p-3">
                                        <div class="fw-bold text-dark mb-3">
                                            <span>Employment Information</span>
                                        </div>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label">TAS ID</label>
                                                <input type="text" class="form-control" placeholder="Enter service or biometric ID">
                                            </div>
                                            <div class="col-12">
                                                <div class="border rounded p-3" style="background-color: #01244518">
                                                    <label class="form-label fw-semibold d-block mb-2">Category <span
                                                            class="text-danger">*</span></label>
                                                    <div class="d-flex flex-wrap gap-2">
                                                        <input type="radio" class="btn-check" name="employmentCategory"
                                                            id="employmentDetailsCategoryEngagement" value="Engagement" required>
                                                        <label class="btn btn-outline-secondary rounded-pill px-3 py-1"
                                                            for="employmentDetailsCategoryEngagement">Employee</label>

                                                        <input type="radio" class="btn-check" name="employmentCategory"
                                                            id="employmentDetailsCategoryContractual" value="Contractual">
                                                        <label class="btn btn-outline-secondary rounded-pill px-3 py-1"
                                                            for="employmentDetailsCategoryContractual">Consultant / Retainer</label>

                                                        <input type="radio" class="btn-check" name="employmentCategory"
                                                            id="employmentDetailsCategoryIntern" value="Intern">
                                                        <label class="btn btn-outline-secondary rounded-pill px-3 py-1"
                                                            for="employmentDetailsCategoryIntern">Intern</label>
                                                    </div>

                                                    <div class="row g-3 d-none mt-1" id="employmentDetailsInternFields">
                                                        <div class="col-md-6">
                                                            <label class="form-label">Intern Type <span class="text-danger">*</span></label>
                                                            <select class="form-select" id="employmentDetailsInternTypeInput">
                                                                <option value="" selected disabled>Select intern type</option>
                                                                <option value="Paid">Paid</option>
                                                                <option value="Unpaid">Unpaid</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">Employee Number <span class="text-danger">*</span></label>
                                                            <input type="text" class="form-control" id="employmentDetailsEmployeeNumberInput"
                                                                placeholder="EMP-CEO-VIUQ">
                                                        </div>
                                                    </div>

                                                    <div class="row g-3 d-none mt-1" id="employmentDetailsContractualFields">
                                                        <div class="col-md-6">
                                                            <label class="form-label">Start Date <span class="text-danger">*</span></label>
                                                            <input type="date" class="form-control" id="employmentDetailsContractStartDateInput"
                                                                placeholder="yyyy-mm-dd">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">End Date <span class="text-danger">*</span></label>
                                                            <input type="date" class="form-control" id="employmentDetailsContractEndDateInput"
                                                                placeholder="yyyy-mm-dd">
                                                        </div>
                                                    </div>

                                                    <div class="row g-3 d-none mt-1" id="employmentDetailsEngagementFields">
                                                        <div class="col-md-6">
                                                            <label class="form-label">Employment Type <span class="text-danger">*</span></label>
                                                            <select class="form-select" id="employmentDetailsEngagementModeInput">
                                                                <option value="" selected disabled>Select employment type</option>
                                                                <option value="Permanent">Permanent</option>
                                                                <option value="Contractual">Contractual</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6 d-none" id="employmentDetailsEmployeeContractTypeField">
                                                            <label class="form-label">Contract Type <span class="text-danger">*</span></label>
                                                            <select class="form-select" id="employmentDetailsEmployeeContractTypeInput">
                                                                <option value="" selected disabled>Select contract type</option>
                                                                <option value="Time bound">Time bound</option>
                                                                <option value="Open ended">Open ended</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-12 d-none" id="employmentDetailsEmployeeContractDatesField">
                                                            <div class="row g-3">
                                                                <div class="col-md-6">
                                                                    <label class="form-label">Contract Start Date <span class="text-danger">*</span></label>
                                                                    <input type="date" class="form-control" id="employmentDetailsEmployeeContractStartDateInput"
                                                                        placeholder="yyyy-mm-dd">
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label class="form-label">Contract End Date <span class="text-danger">*</span></label>
                                                                    <input type="date" class="form-control" id="employmentDetailsEmployeeContractEndDateInput"
                                                                        placeholder="yyyy-mm-dd">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-3">
                                    <div class="col-12 col-xl-6">
                                        <div class="card border-0 bg-light h-100">
                                            <div class="card-body p-3">
                                                <div class="fw-bold text-uppercase small mb-3">Organization and Role</div>
                                                <div class="row g-3">
                                                    <div class="col-md-4">
                                                        <label class="form-label">Organization <span
                                                                class="text-danger">*</span></label>
                                                        <select class="form-select" id="employmentOrganizationSelect">
                                                            <option value="" selected disabled>Select organization</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label">SBU <span class="text-danger">*</span></label>
                                                        <select class="form-select" id="employmentSbuSelect">
                                                            <option value="" selected disabled>Select SBU</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label">Roles <span class="text-danger">*</span></label>
                                                        <select class="form-select" id="employmentRoleSelect">
                                                            <option value="" selected disabled>Select role</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-12">
                                                        <label class="form-label">Departments <span class="text-muted fw-normal small">(optional)</span></label>
                                                        <select name="department_ids[]" id="employmentDepartmentSelect" class="form-select d-none" multiple size="4">
                                                            <option value="" selected disabled>No departments under this SBU</option>
                                                        </select>
                                                        <div class="emp-dept-input-box" id="employmentDeptBox" onclick="employmentDeptBoxClick(event)">
                                                            <div id="employmentDeptChips" style="display:contents"></div>
                                                            <span class="emp-dept-ph" id="employmentDeptPh">Select Departments...</span>
                                                            <svg class="emp-dept-chevron" id="employmentDeptChevron" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                                <path d="M4 6l4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                            </svg>
                                                        </div>
                                                        <div class="emp-dept-dropdown" id="employmentDeptDd" style="display:none">
                                                            <div class="emp-dept-search-row">
                                                                <input id="employmentDeptSearch" placeholder="Search Department..." oninput="employmentDeptRenderList()" onclick="event.stopPropagation()" autocomplete="off">
                                                            </div>
                                                            <div class="emp-dept-opt-list" id="employmentDeptList"></div>
                                                        </div>
                                                        <small class="text-muted d-block mt-1" id="employmentDeptHint">No departments are set up for this SBU yet.</small>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Date of Joining <span
                                                                class="text-danger">*</span></label>
                                                        <input type="date" class="form-control" placeholder="yyyy-mm-dd">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Designation</label>
                                                        <input type="text" class="form-control" placeholder="Designation">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12 col-xl-6">
                                        <div class="card border-0 bg-light h-100">
                                            <div class="card-body p-3">
                                                <div class="fw-bold text-uppercase small mb-3">Placement and Grade</div>
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label">Grade</label>
                                                        <input type="text" class="form-control" placeholder="Grade">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Branch</label>
                                                        <input type="text" class="form-control" placeholder="Branch">
                                                    </div>
                                                    <div class="col-12">
                                                        <label class="form-label">Location</label>
                                                        <input type="text" class="form-control" placeholder="Location">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card border-0 bg-light mt-3">
                                    <div class="card-body p-3">
                                        <div class="border rounded p-3" style="background-color: #01244518">
                                            <label class="form-label fw-semibold d-block mb-2">Work Arrangement <span
                                                    class="text-danger">*</span></label>
                                            <div class="d-flex flex-wrap gap-2">
                                                <input type="radio" class="btn-check" name="employmentWorkArrangement"
                                                    id="employmentWorkArrangementStandard" value="Standard" required>
                                                <label class="btn btn-outline-secondary rounded-pill px-3 py-1"
                                                    for="employmentWorkArrangementStandard">Standard</label>

                                                <input type="radio" class="btn-check" name="employmentWorkArrangement"
                                                    id="employmentWorkArrangementShift" value="Shift-Based">
                                                <label class="btn btn-outline-secondary rounded-pill px-3 py-1"
                                                    for="employmentWorkArrangementShift">Shift-Based</label>

                                                <input type="radio" class="btn-check" name="employmentWorkArrangement"
                                                    id="employmentWorkArrangementRemote" value="Remote">
                                                <label class="btn btn-outline-secondary rounded-pill px-3 py-1"
                                                    for="employmentWorkArrangementRemote">Remote</label>

                                                <input type="radio" class="btn-check" name="employmentWorkArrangement"
                                                    id="employmentWorkArrangementHybrid" value="Hybrid">
                                                <label class="btn btn-outline-secondary rounded-pill px-3 py-1"
                                                    for="employmentWorkArrangementHybrid">Hybrid</label>
                                            </div>

                                            <div class="row g-3 d-none mt-1" id="employmentWorkArrangementStandardFields">
                                                <div class="col-12">
                                                    <label class="form-label fw-semibold d-block mb-2">Standard Type <span class="text-danger">*</span></label>
                                                    <div class="d-flex flex-wrap gap-2">
                                                        <input type="radio" class="btn-check" name="employmentWorkArrangementStandardType"
                                                            id="employmentWorkArrangementStandardTypeDefault" value="Default">
                                                        <label class="btn btn-outline-secondary rounded-pill px-3 py-1"
                                                            for="employmentWorkArrangementStandardTypeDefault">Default</label>

                                                        <input type="radio" class="btn-check" name="employmentWorkArrangementStandardType"
                                                            id="employmentWorkArrangementStandardTypeCustom" value="Custom">
                                                        <label class="btn btn-outline-secondary rounded-pill px-3 py-1"
                                                            for="employmentWorkArrangementStandardTypeCustom">Custom</label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row g-3 d-none mt-1" id="employmentWorkArrangementDefaultCardWrap">
                                                <div class="col-md-6">
                                                    <div class="card border shadow-sm">
                                                        <div class="card-body p-2">
                                                            <div class="d-flex align-items-center gap-2 mb-2">
                                                                <div class="d-flex align-items-center justify-content-center text-white fw-bold rounded"
                                                                    id="employmentWorkArrangementOrgInitial"
                                                                    style="width:44px;height:44px;background:#012d5a;font-size:18px;">-</div>
                                                                <div>
                                                                    <div class="text-muted small">Source: Organization - <span class="fw-semibold text-dark"
                                                                            id="employmentWorkArrangementOrgName">-</span></div>
                                                                </div>
                                                            </div>
                                                            <div class="fw-semibold small">Working days: <span class="fw-normal">- - -</span></div>
                                                            <div class="fw-semibold small">Working time: <span class="fw-normal">- - -</span></div>
                                                            <div class="fw-semibold small">Check-in grace: <span class="fw-normal">-</span></div>
                                                            <div class="fw-semibold small">Check-out grace: <span class="fw-normal">-</span></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row g-3 d-none mt-1" id="employmentWorkArrangementCustomFields">
                                                <div class="col-12">
                                                    <label class="form-label fw-semibold">Working days <span class="text-danger">*</span></label>
                                                    <div class="d-flex flex-wrap gap-3">
                                                        <div class="form-check mb-0">
                                                            <input class="form-check-input" type="checkbox" id="employmentCustomDayMon" name="employment_custom_days[]" value="Mon">
                                                            <label class="form-check-label" for="employmentCustomDayMon">Mon</label>
                                                        </div>
                                                        <div class="form-check mb-0">
                                                            <input class="form-check-input" type="checkbox" id="employmentCustomDayTue" name="employment_custom_days[]" value="Tue">
                                                            <label class="form-check-label" for="employmentCustomDayTue">Tue</label>
                                                        </div>
                                                        <div class="form-check mb-0">
                                                            <input class="form-check-input" type="checkbox" id="employmentCustomDayWed" name="employment_custom_days[]" value="Wed">
                                                            <label class="form-check-label" for="employmentCustomDayWed">Wed</label>
                                                        </div>
                                                        <div class="form-check mb-0">
                                                            <input class="form-check-input" type="checkbox" id="employmentCustomDayThu" name="employment_custom_days[]" value="Thu">
                                                            <label class="form-check-label" for="employmentCustomDayThu">Thu</label>
                                                        </div>
                                                        <div class="form-check mb-0">
                                                            <input class="form-check-input" type="checkbox" id="employmentCustomDayFri" name="employment_custom_days[]" value="Fri">
                                                            <label class="form-check-label" for="employmentCustomDayFri">Fri</label>
                                                        </div>
                                                        <div class="form-check mb-0">
                                                            <input class="form-check-input" type="checkbox" id="employmentCustomDaySat" name="employment_custom_days[]" value="Sat">
                                                            <label class="form-check-label" for="employmentCustomDaySat">Sat</label>
                                                        </div>
                                                        <div class="form-check mb-0">
                                                            <input class="form-check-input" type="checkbox" id="employmentCustomDaySun" name="employment_custom_days[]" value="Sun">
                                                            <label class="form-check-label" for="employmentCustomDaySun">Sun</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label">Working start <span class="text-danger">*</span></label>
                                                    <input type="time" class="form-control" id="employmentCustomWorkingStartInput">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label">Working end <span class="text-danger">*</span></label>
                                                    <input type="time" class="form-control" id="employmentCustomWorkingEndInput">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label">Check-in grace (min)</label>
                                                    <input type="number" min="0" class="form-control" id="employmentCustomCheckInGraceInput" placeholder="Optional">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label">Check-out grace (min)</label>
                                                    <input type="number" min="0" class="form-control" id="employmentCustomCheckOutGraceInput" placeholder="Optional">
                                                </div>
                                            </div>

                                            <div class="row g-3 d-none mt-1" id="employmentWorkArrangementHybridFields">
                                                <div class="col-12">
                                                    <label class="form-label fw-semibold d-block mb-2">Hybrid - on-site days <span class="text-danger">*</span></label>
                                                    <div class="d-flex flex-wrap gap-2">
                                                        <input type="checkbox" class="btn-check" name="employment_hybrid_days[]" id="employmentHybridDayMon" value="Mon">
                                                        <label class="btn btn-outline-secondary rounded-pill px-3 py-1 fw-semibold" for="employmentHybridDayMon">Mon</label>

                                                        <input type="checkbox" class="btn-check" name="employment_hybrid_days[]" id="employmentHybridDayTue" value="Tue">
                                                        <label class="btn btn-outline-secondary rounded-pill px-3 py-1 fw-semibold" for="employmentHybridDayTue">Tue</label>

                                                        <input type="checkbox" class="btn-check" name="employment_hybrid_days[]" id="employmentHybridDayWed" value="Wed">
                                                        <label class="btn btn-outline-secondary rounded-pill px-3 py-1 fw-semibold" for="employmentHybridDayWed">Wed</label>

                                                        <input type="checkbox" class="btn-check" name="employment_hybrid_days[]" id="employmentHybridDayThu" value="Thu">
                                                        <label class="btn btn-outline-secondary rounded-pill px-3 py-1 fw-semibold" for="employmentHybridDayThu">Thu</label>

                                                        <input type="checkbox" class="btn-check" name="employment_hybrid_days[]" id="employmentHybridDayFri" value="Fri">
                                                        <label class="btn btn-outline-secondary rounded-pill px-3 py-1 fw-semibold" for="employmentHybridDayFri">Fri</label>

                                                        <input type="checkbox" class="btn-check" name="employment_hybrid_days[]" id="employmentHybridDaySat" value="Sat">
                                                        <label class="btn btn-outline-secondary rounded-pill px-3 py-1 fw-semibold" for="employmentHybridDaySat">Sat</label>

                                                        <input type="checkbox" class="btn-check" name="employment_hybrid_days[]" id="employmentHybridDaySun" value="Sun">
                                                        <label class="btn btn-outline-secondary rounded-pill px-3 py-1 fw-semibold" for="employmentHybridDaySun">Sun</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
        
                        {{-- STEP 3: Police Verification --}}
                        <div class="wizard-pane" id="stepPane3">
                            <div class="d-grid gap-3">
                                <div class="card bg-light border-0 shadow-sm">
                                    <div class="card-body p-3">
                                        <div class="fw-bold text-dark mb-3">
                                            <span>Police Verification Information</span>
                                        </div>
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <div class="border rounded p-3" style="background-color: #01244518">
                                                    <label class="form-label fw-semibold d-block mb-2">Verification Status <span
                                                            class="text-danger">*</span></label>
                                                    <div class="d-flex flex-wrap gap-2">
                                                        <input type="radio" class="btn-check" name="policeVerificationStatus"
                                                            id="policeVerificationStatusCleared" value="Cleared" required>
                                                        <label class="btn btn-outline-secondary rounded-pill px-3 py-1"
                                                            for="policeVerificationStatusCleared">Cleared</label>

                                                        <input type="radio" class="btn-check" name="policeVerificationStatus"
                                                            id="policeVerificationStatusNotCleared" value="Not Cleared">
                                                        <label class="btn btn-outline-secondary rounded-pill px-3 py-1"
                                                            for="policeVerificationStatusNotCleared">Not Cleared</label>

                                                        <input type="radio" class="btn-check" name="policeVerificationStatus"
                                                            id="policeVerificationStatusInProcess" value="In Process">
                                                        <label class="btn btn-outline-secondary rounded-pill px-3 py-1"
                                                            for="policeVerificationStatusInProcess">In Process</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-3">
                                    <div class="col-12 col-xl-6">
                                        <div class="card border-0 bg-light h-100">
                                            <div class="card-body p-3">
                                                <div class="fw-bold text-uppercase small mb-3">Verification Details</div>
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label">MSR Number</label>
                                                        <input type="text" class="form-control"
                                                            id="policeVerificationMsrNumberInput"
                                                            placeholder="Enter MSR number">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">MSR Date</label>
                                                        <input type="date" class="form-control"
                                                            id="policeVerificationMsrDateInput"
                                                            placeholder="yyyy-mm-dd">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Verification Letter Number</label>
                                                        <input type="text" class="form-control"
                                                            id="policeVerificationLetterNumberInput"
                                                            placeholder="Enter verification letter number">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Verification Letter Date</label>
                                                        <input type="date" class="form-control"
                                                            id="policeVerificationLetterDateInput"
                                                            placeholder="yyyy-mm-dd">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12 col-xl-6">
                                        <div class="card border-0 bg-light h-100">
                                            <div class="card-body p-3">
                                                <div class="fw-bold text-uppercase small mb-3">Authority and Follow-up</div>
                                                <div class="row g-3">
                                                    <div class="col-12">
                                                        <label class="form-label">Addressee</label>
                                                        <input type="text" class="form-control" id="policeVerificationAddresseeInput"
                                                            placeholder="Enter addressee">
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="form-label">Verifying Authority</label>
                                                        <input type="text" class="form-control"
                                                            id="policeVerificationVerifyingAuthorityInput"
                                                            placeholder="Enter verifying authority">
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="form-label">Next Verification Date</label>
                                                        <input type="date" class="form-control"
                                                            id="policeVerificationNextVerificationDateInput"
                                                            placeholder="yyyy-mm-dd">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card border-0 bg-light">
                                    <div class="card-body p-3">
                                        <div class="fw-bold text-uppercase small mb-3">Remarks</div>
                                        <label class="form-label">Remarks</label>
                                        <textarea class="form-control" id="policeVerificationRemarksInput" rows="3"
                                            placeholder="Enter remarks"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
        
                        {{-- STEP 4: Armed Details --}}
                        <div class="wizard-pane" id="stepPane4">
                            <div>
                                <section class="d-grid gap-3">
                                    <div class="card bg-light border-0 shadow-sm">
                                        <div class="card-body p-3">
                                            <div class="fw-bold text-dark mb-3">
                                                <span>Armed Details Information</span>
                                            </div>
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label">Service No</label>
                                                        <input type="text" class="form-control" id="armedDetailsServiceNoInput"
                                                            placeholder="Enter service number">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Rank</label>
                                                        <input type="text" class="form-control" id="armedDetailsRankInput"
                                                            placeholder="Enter rank">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row g-3">
                                        <div class="col-12 col-xl-6">
                                            <div class="card border-0 bg-light h-100">
                                                <div class="card-body p-3">
                                                    <div class="fw-bold text-uppercase small mb-3">Service and Retirement</div>
                                                    <div class="row g-3">
                                                        <div class="col-12">
                                                            <label class="form-label">Medical Category</label>
                                                            <input type="text" class="form-control"
                                                                id="armedDetailsMedicalCategoryInput"
                                                                placeholder="Enter medical category">
                                                        </div>
                                                        <div class="col-12">
                                                            <label class="form-label">Date of Commissioning / Enrollment</label>
                                                            <input type="date" class="form-control"
                                                                id="armedDetailsCommissioningEnrollmentDateInput"
                                                                placeholder="yyyy-mm-dd">
                                                        </div>
                                                        <div class="col-12">
                                                            <label class="form-label">Date of Retirement</label>
                                                            <input type="date" class="form-control" id="armedDetailsRetirementDateInput"
                                                                placeholder="yyyy-mm-dd">
                                                        </div>
                                                        <div class="col-12">
                                                            <label class="form-label">Reason of Retirement</label>
                                                            <input type="text" class="form-control" id="armedDetailsRetirementReasonInput"
                                                                placeholder="Enter reason of retirement">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12 col-xl-6">
                                            <div class="card border-0 bg-light h-100">
                                                <div class="card-body p-3">
                                                    <div class="fw-bold text-uppercase small mb-3">Unit and Officer Details</div>
                                                    <div class="row g-3">
                                                        <div class="col-12">
                                                            <label class="form-label">Corps / Regiment / Squadron</label>
                                                            <input type="text" class="form-control"
                                                                id="armedDetailsCorpsRegimentSquadronInput"
                                                                placeholder="Enter corps, regiment, or squadron">
                                                        </div>
                                                        <div class="col-12">
                                                            <label class="form-label">Ex Army Unit</label>
                                                            <input type="text" class="form-control" id="armedDetailsExArmyUnitInput"
                                                                placeholder="Enter ex army unit">
                                                        </div>
                                                        <div class="col-12">
                                                            <label class="form-label">Trade</label>
                                                            <input type="text" class="form-control" id="armedDetailsTradeInput"
                                                                placeholder="Enter trade">
                                                        </div>
                                                        <div class="col-12">
                                                            <label class="form-label">PMA L/C & OTS (For Army Officers)</label>
                                                            <input type="text" class="form-control" id="armedDetailsPmaLcOtsInput"
                                                                placeholder="Enter PMA L/C & OTS details">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </section>
                            </div>
                        </div>

                        <div class="wizard-pane" id="stepPane5">
                            <div>
                                <section class="d-grid gap-3">
                                    <div class="card bg-light border-0 shadow-sm">
                                        <div class="card-body p-3">
                                            <div class="fw-bold text-dark mb-3">
                                                <span>Bank Details Information</span>
                                            </div>
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label">Account Title <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" id="bankDetailsAccountTitleInput"
                                                        placeholder="Enter account title">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Account No <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" id="bankDetailsAccountNumberInput"
                                                        placeholder="Enter account number">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row g-3">
                                        <div class="col-12 col-xl-6">
                                            <div class="card border-0 bg-light h-100">
                                                <div class="card-body p-3">
                                                    <div class="fw-bold text-uppercase small mb-3">Branch Details</div>
                                                    <div class="row g-3">
                                                        <div class="col-12">
                                                            <label class="form-label">Bank & Branch / Branch Code <span
                                                                    class="text-danger">*</span></label>
                                                            <input type="text" class="form-control"
                                                                id="bankDetailsBranchCodeInput"
                                                                placeholder="Enter bank, branch, and branch code">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12 col-xl-6">
                                            <div class="card border-0 bg-light h-100">
                                                <div class="card-body p-3">
                                                    <div class="fw-bold text-uppercase small mb-3">Account Type</div>
                                                    <label class="form-label fw-semibold d-block mb-2">A/C Type <span
                                                            class="text-danger">*</span></label>
                                                    <div class="d-flex flex-wrap gap-3">
                                                        <input class="btn-check" type="radio" name="bankDetailsAccountType"
                                                            id="bankDetailsAccountTypeSaving" value="Saving" required>
                                                        <label class="btn btn-outline-secondary option-chip"
                                                            for="bankDetailsAccountTypeSaving">Saving</label>

                                                        <input class="btn-check" type="radio" name="bankDetailsAccountType"
                                                            id="bankDetailsAccountTypeCurrent" value="Current">
                                                        <label class="btn btn-outline-secondary option-chip"
                                                            for="bankDetailsAccountTypeCurrent">Current</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </section>
                            </div>
                        </div>

                        <div class="wizard-pane" id="stepPane6">
                            <div>
                                <section class="d-grid gap-3">
                                    <div>
                                        <div class="fw-bold text-dark mb-3">
                                            <span>More Details Information</span>
                                        </div>
                                        <div class="more-sub-nav d-flex flex-wrap gap-2 mb-3">
                                            <button type="button" class="btn btn-sm btn-outline-secondary more-sub-tab active"
                                                data-more-step="1"><span class="more-step-index">1</span><span>Contact</span></button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary more-sub-tab"
                                                data-more-step="2"><span class="more-step-index">2</span><span>Family</span></button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary more-sub-tab"
                                                data-more-step="3"><span class="more-step-index">3</span><span>Academic</span></button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary more-sub-tab"
                                                data-more-step="4"><span class="more-step-index">4</span><span>Employement</span></button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary more-sub-tab"
                                                data-more-step="5"><span class="more-step-index">5</span><span>Medical</span></button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary more-sub-tab"
                                                data-more-step="6"><span class="more-step-index">6</span><span>Reference</span></button>
                                        </div>

                                        <div class="more-sub-pane active" id="moreStepPane1">
                                            <div class="card border-0 bg-light">
                                                <div class="card-body p-3">
                                                    <div class="fw-bold text-uppercase small mb-3">Contact</div>
                                                    <div class="row g-3">
                                                        <div class="col-md-4">
                                                            <label class="form-label">Residence Phone</label>
                                                            <input type="text" class="form-control"
                                                                id="moreContactResidencePhoneInput"
                                                                placeholder="Enter residence phone">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label">In Case of Emergency Contact No</label>
                                                            <input type="text" class="form-control"
                                                                id="moreContactEmergencyContactInput"
                                                                placeholder="Enter emergency contact number">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label">Cell No <span class="text-danger">*</span></label>
                                                            <input type="text" class="form-control"
                                                                id="moreContactCellNoInput"
                                                                placeholder="Enter cell number">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label">Email <span class="text-danger">*</span></label>
                                                            <input type="email" class="form-control"
                                                                id="moreContactEmailInput"
                                                                placeholder="Enter email address">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label">Present Address <span class="text-danger">*</span></label>
                                                            <textarea class="form-control" id="moreContactPresentAddressInput" rows="1"
                                                                placeholder="Enter present address"></textarea>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label">Permanent Address <span class="text-danger">*</span></label>
                                                            <textarea class="form-control" id="moreContactPermanentAddressInput" rows="1"
                                                                placeholder="Enter permanent address"></textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="more-sub-pane" id="moreStepPane2">
                                            <div>
                                                <div class="fw-bold text-uppercase small mb-3">Family</div>
                                                <div class="family-members-wrap bg-white">
                                                    <div class="family-members-toolbar">
                                                        <div class="small text-secondary">Add each family member as a separate card.</div>
                                                        <span class="family-members-count" id="moreFamilyMemberCount">0 Members</span>
                                                    </div>
                                                    <div id="moreFamilyMembersContainer"></div>
                                                </div>
                                                <div class="mt-3">
                                                    <button type="button" class="btn btn-sm text-white bg-main border-0"
                                                        id="moreFamilyAddMemberBtn">
                                                        <i class="bi bi-plus-lg me-1"></i>Add Member
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <template id="moreFamilyMemberTemplate">
                                            <div class="family-member-row mb-2 bg-light" data-family-row>
                                                <div class="family-member-header">
                                                    <span class="family-member-index" data-family-index>Member 1</span>
                                                    <div class="family-member-actions">
                                                        <button type="button" class="btn btn-sm btn-outline-primary" data-family-save title="Save member">
                                                            <i class="bi bi-floppy"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-outline-danger" data-family-remove>
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="row g-2">
                                                    <div class="col-12 col-md-6 col-xl-3">
                                                        <label class="form-label">Name <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control family-field-input" name="familyMembers[][name]"
                                                            data-family-name placeholder="Enter name" required>
                                                        <div class="family-field-preview" data-family-preview-name>-</div>
                                                    </div>
                                                    <div class="col-12 col-md-6 col-xl-2">
                                                        <label class="form-label">Gender <span class="text-danger">*</span></label>
                                                        <select class="form-select family-field-input" name="familyMembers[][gender]" data-family-gender required>
                                                            <option value="" selected disabled>Select</option>
                                                            <option value="Male">Male</option>
                                                            <option value="Female">Female</option>
                                                            <option value="Other">Other</option>
                                                        </select>
                                                        <div class="family-field-preview" data-family-preview-gender>-</div>
                                                    </div>
                                                    <div class="col-12 col-md-6 col-xl-2">
                                                        <label class="form-label">Date of Birth <span class="text-danger">*</span></label>
                                                        <input type="date" class="form-control family-field-input" name="familyMembers[][date_of_birth]"
                                                            data-family-date-of-birth placeholder="yyyy-mm-dd" required>
                                                        <div class="family-field-preview" data-family-preview-date-of-birth>-</div>
                                                    </div>
                                                    <div class="col-12 col-md-6 col-xl-2">
                                                        <label class="form-label">Relation <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control family-field-input" name="familyMembers[][relation]"
                                                            data-family-relation placeholder="Enter relation" required>
                                                        <div class="family-field-preview" data-family-preview-relation>-</div>
                                                    </div>
                                                    <div class="col-12 col-md-6 col-xl-3">
                                                        <label class="form-label">Occupation</label>
                                                        <input type="text" class="form-control family-field-input" name="familyMembers[][occupation]"
                                                            data-family-occupation placeholder="Enter occupation">
                                                        <div class="family-field-preview" data-family-preview-occupation>-</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>

                                        <div class="more-sub-pane" id="moreStepPane3">
                                            <div>
                                                <div class="fw-bold text-uppercase small mb-3">Academic</div>
                                                <div class="academic-records-wrap bg-white">
                                                    <div class="academic-records-toolbar">
                                                        <div class="small text-secondary">Add each academic record as a separate row.</div>
                                                        <span class="academic-records-count" id="moreAcademicRecordCount">0 Records</span>
                                                    </div>
                                                    <div id="moreAcademicRecordsContainer"></div>
                                                </div>
                                                <div class="mt-3">
                                                    <button type="button" class="btn btn-sm text-white bg-main border-0"
                                                        id="moreAcademicAddRecordBtn">
                                                        <i class="bi bi-plus-lg me-1"></i>Add Academic Record
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <template id="moreAcademicRecordTemplate">
                                            <div class="academic-record-row mb-2 bg-light" data-academic-row>
                                                <div class="academic-record-header">
                                                    <span class="academic-record-index" data-academic-index>Record 1</span>
                                                    <div class="academic-record-actions">
                                                        <button type="button" class="btn btn-sm btn-outline-primary" data-academic-save title="Save record">
                                                            <i class="bi bi-floppy"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-outline-danger" data-academic-remove>
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="row g-2">
                                                    <div class="col-12 col-md-6 col-xl-3">
                                                        <label class="form-label">Degree / Certificate <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control academic-field-input" name="academicRecords[][degree_certificate]"
                                                            data-academic-degree placeholder="Enter degree or certificate" required>
                                                        <div class="academic-field-preview" data-academic-preview-degree>-</div>
                                                    </div>
                                                    <div class="col-12 col-md-6 col-xl-3">
                                                        <label class="form-label">Grade / Div / CGPA <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control academic-field-input" name="academicRecords[][grade_div_cgpa]"
                                                            data-academic-grade placeholder="Enter grade, division, or CGPA" required>
                                                        <div class="academic-field-preview" data-academic-preview-grade>-</div>
                                                    </div>
                                                    <div class="col-12 col-md-6 col-xl-2">
                                                        <label class="form-label">Start Date <span class="text-danger">*</span></label>
                                                        <input type="date" class="form-control academic-field-input" name="academicRecords[][start_date]"
                                                            data-academic-start-date placeholder="yyyy-mm-dd" required>
                                                        <div class="academic-field-preview" data-academic-preview-start-date>-</div>
                                                    </div>
                                                    <div class="col-12 col-md-6 col-xl-2">
                                                        <label class="form-label">End Date <span class="text-danger">*</span></label>
                                                        <input type="date" class="form-control academic-field-input" name="academicRecords[][end_date]"
                                                            data-academic-end-date placeholder="yyyy-mm-dd" required>
                                                        <div class="academic-field-preview" data-academic-preview-end-date>-</div>
                                                    </div>
                                                    <div class="col-12 col-md-6 col-xl-2">
                                                        <label class="form-label">Field of Study</label>
                                                        <input type="text" class="form-control academic-field-input" name="academicRecords[][field_of_study]"
                                                            data-academic-field-of-study placeholder="Enter field of study">
                                                        <div class="academic-field-preview" data-academic-preview-field-of-study>-</div>
                                                    </div>
                                                    <div class="col-12">
                                                        <label class="form-label">University / Board / Institute</label>
                                                        <input type="text" class="form-control academic-field-input" name="academicRecords[][institute]"
                                                            data-academic-institute placeholder="Enter university, board, or institute">
                                                        <div class="academic-field-preview" data-academic-preview-institute>-</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>

                                        <div class="more-sub-pane" id="moreStepPane4">
                                            <div>
                                                <div class="fw-bold text-uppercase small mb-3">Employement</div>
                                                <div class="employment-records-wrap bg-white">
                                                    <div class="employment-records-toolbar">
                                                        <div class="small text-secondary">Add each employement record as a separate row.</div>
                                                        <span class="employment-records-count" id="moreEmployementRecordCount">0 Records</span>
                                                    </div>
                                                    <div id="moreEmployementRecordsContainer"></div>
                                                </div>
                                                <div class="mt-3">
                                                    <button type="button" class="btn btn-sm text-white bg-main border-0"
                                                        id="moreEmployementAddRecordBtn">
                                                        <i class="bi bi-plus-lg me-1"></i>Add Employement Record
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <template id="moreEmployementRecordTemplate">
                                            <div class="employment-record-row mb-2 bg-light" data-employement-row>
                                                <div class="employment-record-header">
                                                    <span class="employment-record-index" data-employement-index>Record 1</span>
                                                    <div class="employment-record-actions">
                                                        <button type="button" class="btn btn-sm btn-outline-primary" data-employement-save title="Save record">
                                                            <i class="bi bi-floppy"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-outline-danger" data-employement-remove>
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="row g-2">
                                                    <div class="col-12 col-md-6 col-xl-3">
                                                        <label class="form-label">Organization <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control employment-field-input" name="employementRecords[][organization]"
                                                            data-employement-organization placeholder="Enter organization" required>
                                                        <div class="employment-field-preview" data-employement-preview-organization>-</div>
                                                    </div>
                                                    <div class="col-12 col-md-6 col-xl-3">
                                                        <label class="form-label">Designation <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control employment-field-input" name="employementRecords[][designation]"
                                                            data-employement-designation placeholder="Enter designation" required>
                                                        <div class="employment-field-preview" data-employement-preview-designation>-</div>
                                                    </div>
                                                    <div class="col-12 col-md-6 col-xl-2">
                                                        <label class="form-label">From <span class="text-danger">*</span></label>
                                                        <input type="date" class="form-control employment-field-input" name="employementRecords[][from_date]"
                                                            data-employement-from-date placeholder="yyyy-mm-dd" required>
                                                        <div class="employment-field-preview" data-employement-preview-from-date>-</div>
                                                    </div>
                                                    <div class="col-12 col-md-6 col-xl-2">
                                                        <label class="form-label">To <span class="text-danger">*</span></label>
                                                        <input type="date" class="form-control employment-field-input" name="employementRecords[][to_date]"
                                                            data-employement-to-date placeholder="yyyy-mm-dd" required>
                                                        <div class="employment-field-preview" data-employement-preview-to-date>-</div>
                                                    </div>
                                                    <div class="col-12 col-md-6 col-xl-2">
                                                        <label class="form-label">Salary</label>
                                                        <input type="text" class="form-control employment-field-input" name="employementRecords[][salary]"
                                                            data-employement-salary placeholder="Enter salary">
                                                        <div class="employment-field-preview" data-employement-preview-salary>-</div>
                                                    </div>
                                                    <div class="col-12">
                                                        <label class="form-label">Reason for Leaving</label>
                                                        <input type="text" class="form-control employment-field-input" name="employementRecords[][reason_for_leaving]"
                                                            data-employement-reason placeholder="Enter reason for leaving">
                                                        <div class="employment-field-preview" data-employement-preview-reason>-</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>

                                        <div class="more-sub-pane" id="moreStepPane5">
                                            <div>
                                                <div class="fw-bold text-uppercase small mb-3">Medical</div>
                                                <div class="card border-0 bg-light">
                                                    <div class="card-body p-3">
                                                        <div class="row g-3">
                                                            <div class="col-12">
                                                                <label class="form-label">Last Medical Fitness Test - Date & Results</label>
                                                                <textarea class="form-control" id="moreMedicalLastFitnessTestInput" rows="2"
                                                                    placeholder="Enter date and results of last medical fitness test"></textarea>
                                                            </div>
                                                            <div class="col-md-3">
                                                                <label class="form-label d-block">Do you have any disability?</label>
                                                                <div class="d-flex gap-4 pt-1">
                                                                    <input class="btn-check" type="radio" name="moreMedicalHasDisability"
                                                                        id="moreMedicalHasDisabilityYes" value="Yes">
                                                                    <label class="btn btn-outline-secondary option-chip"
                                                                        for="moreMedicalHasDisabilityYes">Yes</label>

                                                                    <input class="btn-check" type="radio" name="moreMedicalHasDisability"
                                                                        id="moreMedicalHasDisabilityNo" value="No">
                                                                    <label class="btn btn-outline-secondary option-chip"
                                                                        for="moreMedicalHasDisabilityNo">No</label>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-3">
                                                                <label class="form-label">Blood Group</label>
                                                                <input type="text" class="form-control" id="moreMedicalBloodGroupInput"
                                                                    placeholder="Enter blood group">
                                                            </div>
                                                            <div class="col-md-3">
                                                                <label class="form-label">If Yes (Disability Type)</label>
                                                                <select class="form-select" id="moreMedicalDisabilityTypeInput">
                                                                    <option value="" selected disabled>Select</option>
                                                                    <option value="Physical">Physical</option>
                                                                    <option value="Visual">Visual</option>
                                                                    <option value="Hearing">Hearing</option>
                                                                    <option value="Speech">Speech</option>
                                                                    <option value="Other">Other</option>
                                                                </select>
                                                            </div>
                                                            <div class="col-12">
                                                                <label class="form-label">Disease / Disability Description</label>
                                                                <textarea class="form-control" id="moreMedicalDisabilityDescriptionInput" rows="2"
                                                                    placeholder="Enter disease or disability description"></textarea>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="more-sub-pane" id="moreStepPane6">
                                            <div>
                                                <div class="fw-bold text-uppercase small mb-3">Reference</div>
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <div class="card border-0 bg-light h-100">
                                                            <div class="card-body p-3">
                                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                                    <div class="fw-bold text-dark">Reference 1</div>
                                                                    <span class="badge text-bg-light border">Primary</span>
                                                                </div>
                                                                <div class="row g-3">
                                                                    <div class="col-6">
                                                                        <label class="form-label">Name</label>
                                                                        <input type="text" class="form-control" id="moreReferenceOneNameInput"
                                                                            placeholder="Enter name">
                                                                    </div>
                                                                    <div class="col-6">
                                                                        <label class="form-label">Designation</label>
                                                                        <input type="text" class="form-control" id="moreReferenceOneDesignationInput"
                                                                            placeholder="Enter designation">
                                                                    </div>
                                                                    <div class="col-6">
                                                                        <label class="form-label">Organization</label>
                                                                        <input type="text" class="form-control" id="moreReferenceOneOrganizationInput"
                                                                            placeholder="Enter organization">
                                                                    </div>
                                                                    <div class="col-6">
                                                                        <label class="form-label">Contact No</label>
                                                                        <input type="text" class="form-control" id="moreReferenceOneContactNoInput"
                                                                            placeholder="Enter contact number">
                                                                    </div>
                                                                    <div class="col-6">
                                                                        <label class="form-label">Relationship</label>
                                                                        <select class="form-select" id="moreReferenceOneRelationshipInput">
                                                                            <option value="" selected disabled>Select</option>
                                                                            <option value="Family">Family</option>
                                                                            <option value="Friend">Friend</option>
                                                                            <option value="Colleague">Colleague</option>
                                                                            <option value="Professional">Professional</option>
                                                                            <option value="Other">Other</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="card border-0 bg-light h-100">
                                                            <div class="card-body p-3">
                                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                                    <div class="fw-bold text-dark">Reference 2</div>
                                                                    <span class="badge text-bg-light border">Secondary</span>
                                                                </div>
                                                                <div class="row g-3">
                                                                    <div class="col-6">
                                                                        <label class="form-label">Name</label>
                                                                        <input type="text" class="form-control" id="moreReferenceTwoNameInput"
                                                                            placeholder="Enter name">
                                                                    </div>
                                                                    <div class="col-6">
                                                                        <label class="form-label">Designation</label>
                                                                        <input type="text" class="form-control" id="moreReferenceTwoDesignationInput"
                                                                            placeholder="Enter designation">
                                                                    </div>
                                                                    <div class="col-6">
                                                                        <label class="form-label">Organization</label>
                                                                        <input type="text" class="form-control" id="moreReferenceTwoOrganizationInput"
                                                                            placeholder="Enter organization">
                                                                    </div>
                                                                    <div class="col-6">
                                                                        <label class="form-label">Contact No</label>
                                                                        <input type="text" class="form-control" id="moreReferenceTwoContactNoInput"
                                                                            placeholder="Enter contact number">
                                                                    </div>
                                                                    <div class="col-6">
                                                                        <label class="form-label">Relationship</label>
                                                                        <select class="form-select" id="moreReferenceTwoRelationshipInput">
                                                                            <option value="" selected disabled>Select</option>
                                                                            <option value="Family">Family</option>
                                                                            <option value="Friend">Friend</option>
                                                                            <option value="Colleague">Colleague</option>
                                                                            <option value="Professional">Professional</option>
                                                                            <option value="Other">Other</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </section>
                            </div>
                        </div>
        
                        <div class="d-flex justify-content-between gap-2 mt-3">
                            <button type="button" class="btn btn-outline-secondary" id="prevBtn"
                                style="visibility:hidden;">Back</button>
                            <button type="button" class="btn text-white bg-main border-0 px-4 rounded-2"
                                id="nextBtn">Next</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let currentStep = 1;
        const totalSteps = 6;

        function syncStepUi() {
            for (let i = 1; i <= totalSteps; i++) {
                const pane = document.getElementById('stepPane' + i);
                pane.classList.toggle('active', i === currentStep);
            }

            const tabs = document.querySelectorAll('.profile-tab');
            tabs.forEach((tab) => {
                const step = Number(tab.getAttribute('data-step'));
                tab.classList.remove('active');
                if (step === currentStep) tab.classList.add('active');
            });

            const prevBtn = document.getElementById('prevBtn');
            const nextBtn = document.getElementById('nextBtn');
            prevBtn.style.visibility = currentStep === 1 ? 'hidden' : 'visible';
            nextBtn.textContent = currentStep === totalSteps ? 'Finish' : 'Next';
        }

        document.querySelectorAll('.profile-tab').forEach((tab) => {
            tab.addEventListener('click', function() {
                currentStep = Number(this.getAttribute('data-step'));
                syncStepUi();
            });
        });

        document.getElementById('nextBtn').addEventListener('click', function() {
            if (currentStep < totalSteps) {
                currentStep += 1;
                syncStepUi();
            } else {
                this.disabled = true;
                this.classList.remove('bg-main');
                this.classList.add('btn-success');
                this.textContent = 'Completed';
            }
        });

        document.getElementById('prevBtn').addEventListener('click', function() {
            if (currentStep > 1) {
                currentStep -= 1;
                syncStepUi();
            }
        });

        const profilePhotoInput = document.getElementById('profilePhotoInput');
        const avatarPreviewImage = document.getElementById('avatarPreviewImage');
        const avatarPlaceholderIcon = document.getElementById('avatarPlaceholderIcon');

        if (profilePhotoInput && avatarPreviewImage && avatarPlaceholderIcon) {
            profilePhotoInput.addEventListener('change', function(e) {
                const file = e.target.files && e.target.files[0] ? e.target.files[0] : null;
                if (!file) return;
                const objectUrl = URL.createObjectURL(file);
                avatarPreviewImage.src = objectUrl;
                avatarPreviewImage.classList.remove('d-none');
                avatarPreviewImage.classList.add('d-block');
                avatarPlaceholderIcon.classList.add('d-none');
            });
        }

        const giFatherDeceasedCheckbox = document.getElementById('giFatherDeceasedCheckbox');
        const giFatherCnicField = document.getElementById('giFatherCnicField');
        const giFatherCnicInput = document.getElementById('giFatherCnicInput');

        function syncFatherCnicVisibility() {
            if (!giFatherDeceasedCheckbox || !giFatherCnicField) return;
            const hideFatherCnic = giFatherDeceasedCheckbox.checked;
            giFatherCnicField.classList.toggle('d-none', hideFatherCnic);
            if (hideFatherCnic && giFatherCnicInput) {
                giFatherCnicInput.value = '';
            }
        }

        if (giFatherDeceasedCheckbox) {
            giFatherDeceasedCheckbox.addEventListener('change', syncFatherCnicVisibility);
            syncFatherCnicVisibility();
        }

        const giNokRelationSelect = document.getElementById('giNokRelationSelect');
        const giNokSpecifyRelationField = document.getElementById('giNokSpecifyRelationField');
        const giNokSpecifyRelationInput = document.getElementById('giNokSpecifyRelationInput');

        function syncNokSpecifyRelationField() {
            if (!giNokRelationSelect || !giNokSpecifyRelationField) return;
            const isOtherSelected = giNokRelationSelect.value === 'Other';
            giNokSpecifyRelationField.classList.toggle('d-none', !isOtherSelected);
            if (giNokSpecifyRelationInput) {
                giNokSpecifyRelationInput.required = isOtherSelected;
                if (!isOtherSelected) {
                    giNokSpecifyRelationInput.value = '';
                }
            }
        }

        if (giNokRelationSelect) {
            giNokRelationSelect.addEventListener('change', syncNokSpecifyRelationField);
            syncNokSpecifyRelationField();
        }

        function employmentDeptGetSelect() {
            return document.getElementById('employmentDepartmentSelect');
        }

        function employmentDeptRenderFromSelect() {
            const deptSel = employmentDeptGetSelect();
            const chips = document.getElementById('employmentDeptChips');
            const ph = document.getElementById('employmentDeptPh');
            if (!deptSel || !chips) return;
            const selected = Array.from(deptSel.selectedOptions || []);
            if (!selected.length) {
                chips.innerHTML = '';
                if (ph) ph.style.display = '';
                return;
            }
            if (ph) ph.style.display = 'none';
            chips.innerHTML = selected
                .map(function (opt) {
                    const val = String(opt.value || '');
                    const text = String(opt.textContent || '');
                    if (!val) {
                        return '<span class="emp-dept-chip">' + text + '</span>';
                    }
                    return '<span class="emp-dept-chip">' + text + '<span class="emp-dept-chip-x" onclick="employmentDeptRemoveId(\'' + val + '\', event)">×</span></span>';
                })
                .join('');
        }

        window.employmentDeptRenderList = function employmentDeptRenderList() {
            const deptSel = employmentDeptGetSelect();
            const list = document.getElementById('employmentDeptList');
            const search = document.getElementById('employmentDeptSearch');
            if (!deptSel || !list) return;
            const q = String((search && search.value) || '').toLowerCase().trim();
            const options = Array.from(deptSel.options || []).filter(function (opt) {
                return opt.value && (!q || String(opt.textContent || '').toLowerCase().indexOf(q) !== -1);
            });
            if (!options.length) {
                list.innerHTML = '<div class="emp-dept-no-result">No departments under this SBU</div>';
                return;
            }
            list.innerHTML = options
                .map(function (opt) {
                    const picked = !!opt.selected;
                    return '<div class="emp-dept-opt ' + (picked ? 'picked' : '') + '" onclick="employmentDeptToggleId(\'' + String(opt.value || '') + '\')"><span class="emp-dept-opt-cb"><svg class="emp-dept-opt-ck" viewBox="0 0 16 16" fill="none"><path d="M3.5 8.2l3 3L12.5 5" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></span><span class="emp-dept-opt-name">' + String(opt.textContent || '') + '</span></div>';
                })
                .join('');
        };

        window.employmentDeptToggleId = function employmentDeptToggleId(id) {
            const deptSel = employmentDeptGetSelect();
            if (!deptSel) return;
            const opt = deptSel.querySelector('option[value="' + String(id) + '"]');
            if (!opt) return;
            opt.selected = !opt.selected;
            employmentDeptRenderFromSelect();
            window.employmentDeptRenderList();
        };

        window.employmentDeptRemoveId = function employmentDeptRemoveId(id, e) {
            if (e) e.stopPropagation();
            const deptSel = employmentDeptGetSelect();
            if (!deptSel) return;
            const opt = deptSel.querySelector('option[value="' + String(id) + '"]');
            if (opt) opt.selected = false;
            employmentDeptRenderFromSelect();
            window.employmentDeptRenderList();
        };

        window.employmentDeptBoxClick = function employmentDeptBoxClick(e) {
            if (e) e.stopPropagation();
            const dd = document.getElementById('employmentDeptDd');
            const box = document.getElementById('employmentDeptBox');
            if (!dd || !box) return;
            const isOpen = dd.style.display !== 'none';
            if (!isOpen) {
                dd.style.display = '';
                box.classList.add('open');
                window.employmentDeptRenderList();
                const search = document.getElementById('employmentDeptSearch');
                if (search) setTimeout(function () { search.focus(); }, 0);
            } else {
                dd.style.display = 'none';
                box.classList.remove('open');
            }
        };

        document.addEventListener('click', function (evt) {
            const dd = document.getElementById('employmentDeptDd');
            const box = document.getElementById('employmentDeptBox');
            if (!dd || !box) return;
            if (!dd.contains(evt.target) && !box.contains(evt.target)) {
                dd.style.display = 'none';
                box.classList.remove('open');
            }
        });

        const employmentDeptSearch = document.getElementById('employmentDeptSearch');
        if (employmentDeptSearch) {
            employmentDeptSearch.addEventListener('keydown', function (evt) {
                evt.stopPropagation();
            });
        }
        employmentDeptRenderFromSelect();
        window.employmentDeptRenderList();

        const employmentDetailsCategoryIntern = document.getElementById('employmentDetailsCategoryIntern');
        const employmentDetailsCategoryContractual = document.getElementById('employmentDetailsCategoryContractual');
        const employmentDetailsCategoryEngagement = document.getElementById('employmentDetailsCategoryEngagement');
        const employmentDetailsInternFields = document.getElementById('employmentDetailsInternFields');
        const employmentDetailsContractualFields = document.getElementById('employmentDetailsContractualFields');
        const employmentDetailsEngagementFields = document.getElementById('employmentDetailsEngagementFields');
        const employmentDetailsInternTypeInput = document.getElementById('employmentDetailsInternTypeInput');
        const employmentDetailsEmployeeNumberInput = document.getElementById('employmentDetailsEmployeeNumberInput');
        const employmentDetailsContractStartDateInput = document.getElementById('employmentDetailsContractStartDateInput');
        const employmentDetailsContractEndDateInput = document.getElementById('employmentDetailsContractEndDateInput');
        const employmentDetailsContractualEmployeeNumberInput = document.getElementById('employmentDetailsContractualEmployeeNumberInput');
        const employmentDetailsEngagementModeInput = document.getElementById('employmentDetailsEngagementModeInput');
        const employmentDetailsEmployeeContractTypeField = document.getElementById('employmentDetailsEmployeeContractTypeField');
        const employmentDetailsEmployeeContractTypeInput = document.getElementById('employmentDetailsEmployeeContractTypeInput');
        const employmentDetailsEmployeeContractDatesField = document.getElementById('employmentDetailsEmployeeContractDatesField');
        const employmentDetailsEmployeeContractStartDateInput = document.getElementById('employmentDetailsEmployeeContractStartDateInput');
        const employmentDetailsEmployeeContractEndDateInput = document.getElementById('employmentDetailsEmployeeContractEndDateInput');
        const employmentDetailsEngagementEmployeeNumberInput = document.getElementById('employmentDetailsEngagementEmployeeNumberInput');
        const employmentDetailsCategoryInputs = document.querySelectorAll('input[name="employmentCategory"]');

        function toggleEmploymentCategoryFields() {
            if (!employmentDetailsCategoryIntern || !employmentDetailsInternFields || !employmentDetailsContractualFields || !employmentDetailsEngagementFields) return;
            const showInternFields = employmentDetailsCategoryIntern.checked;
            const showContractualFields = employmentDetailsCategoryContractual ? employmentDetailsCategoryContractual.checked : false;
            const showEngagementFields = employmentDetailsCategoryEngagement ? employmentDetailsCategoryEngagement.checked : false;
            employmentDetailsInternFields.classList.toggle('d-none', !showInternFields);
            employmentDetailsContractualFields.classList.toggle('d-none', !showContractualFields);
            employmentDetailsEngagementFields.classList.toggle('d-none', !showEngagementFields);

            if (employmentDetailsInternTypeInput) {
                employmentDetailsInternTypeInput.required = showInternFields;
                if (!showInternFields) {
                    employmentDetailsInternTypeInput.value = '';
                }
            }

            if (employmentDetailsEmployeeNumberInput) {
                employmentDetailsEmployeeNumberInput.required = showInternFields;
                if (!showInternFields) {
                    employmentDetailsEmployeeNumberInput.value = '';
                }
            }

            if (employmentDetailsContractStartDateInput) {
                employmentDetailsContractStartDateInput.required = showContractualFields;
                if (!showContractualFields) {
                    employmentDetailsContractStartDateInput.value = '';
                }
            }

            if (employmentDetailsContractEndDateInput) {
                employmentDetailsContractEndDateInput.required = showContractualFields;
                if (!showContractualFields) {
                    employmentDetailsContractEndDateInput.value = '';
                }
            }

            if (employmentDetailsContractualEmployeeNumberInput) {
                employmentDetailsContractualEmployeeNumberInput.required = showContractualFields;
                if (!showContractualFields) {
                    employmentDetailsContractualEmployeeNumberInput.value = '';
                }
            }

            if (employmentDetailsEngagementModeInput) {
                employmentDetailsEngagementModeInput.required = showEngagementFields;
                if (!showEngagementFields) {
                    employmentDetailsEngagementModeInput.value = '';
                }
            }

            const showEmploymentContractType = showEngagementFields && employmentDetailsEngagementModeInput && employmentDetailsEngagementModeInput.value === 'Contractual';
            if (employmentDetailsEmployeeContractTypeField) {
                employmentDetailsEmployeeContractTypeField.classList.toggle('d-none', !showEmploymentContractType);
            }
            if (employmentDetailsEmployeeContractTypeInput) {
                employmentDetailsEmployeeContractTypeInput.required = !!showEmploymentContractType;
                if (!showEmploymentContractType) {
                    employmentDetailsEmployeeContractTypeInput.value = '';
                }
            }

            const showEmploymentContractDates = !!showEmploymentContractType && employmentDetailsEmployeeContractTypeInput && employmentDetailsEmployeeContractTypeInput.value === 'Time bound';
            if (employmentDetailsEmployeeContractDatesField) {
                employmentDetailsEmployeeContractDatesField.classList.toggle('d-none', !showEmploymentContractDates);
            }
            if (employmentDetailsEmployeeContractStartDateInput) {
                employmentDetailsEmployeeContractStartDateInput.required = !!showEmploymentContractDates;
                if (!showEmploymentContractDates) {
                    employmentDetailsEmployeeContractStartDateInput.value = '';
                }
            }
            if (employmentDetailsEmployeeContractEndDateInput) {
                employmentDetailsEmployeeContractEndDateInput.required = !!showEmploymentContractDates;
                if (!showEmploymentContractDates) {
                    employmentDetailsEmployeeContractEndDateInput.value = '';
                }
            }

            if (employmentDetailsEngagementEmployeeNumberInput) {
                employmentDetailsEngagementEmployeeNumberInput.required = showEngagementFields;
                if (!showEngagementFields) {
                    employmentDetailsEngagementEmployeeNumberInput.value = '';
                }
            }
        }

        employmentDetailsCategoryInputs.forEach((input) => {
            input.addEventListener('change', toggleEmploymentCategoryFields);
        });
        if (employmentDetailsEngagementModeInput) {
            employmentDetailsEngagementModeInput.addEventListener('change', toggleEmploymentCategoryFields);
        }
        if (employmentDetailsEmployeeContractTypeInput) {
            employmentDetailsEmployeeContractTypeInput.addEventListener('change', toggleEmploymentCategoryFields);
        }
        toggleEmploymentCategoryFields();

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

        function setSummaryValue(targetId, value, fallback) {
            const el = document.getElementById(targetId);
            if (!el) return;
            const nextVal = value && String(value).trim() ? String(value).trim() : fallback;
            el.textContent = nextVal;
        }

        function bindSummaryField(inputId, targetId, fallback) {
            const input = document.getElementById(inputId);
            if (!input) return;
            const handler = function() {
                setSummaryValue(targetId, input.value, fallback);
            };
            input.addEventListener('input', handler);
            input.addEventListener('change', handler);
            handler();
        }

        bindSummaryField('giNameInput', 'summaryName', 'Not provided');
        bindSummaryField('giCnicInput', 'summaryCnic', 'Not provided');
        bindSummaryField('giGenderInput', 'summaryGender', 'Not selected');
        bindSummaryField('giReligionInput', 'summaryReligion', 'Not selected');
        bindSummaryField('giNationalityInput', 'summaryNationality', 'Not selected');
    </script>
@endpush






