{{-- STEP 1: General Information --}}
<div class="step active" id="step-1">
    <div class="section-title">Section A — General Information</div>
    <div class="row g-3">

        {{-- Personal --}}
        <div class="d-flex align-items-end gap-2">
            <div class="col-2">
                <label class="form-label">Upload Image</label>
                <label for="uploadImage" class="d-flex flex-column align-items-center justify-content-center gap-2"
                    style="width:160px; height:160px; border: 2px dashed var(--main-color); border-radius: 10px; cursor:pointer; background:#f8f9fa1a;">
                    <i class="bi bi-cloud-arrow-up fs-1 text-secondary"></i>
                    <span style="font-size:.72rem; color:#6c757d;">Click to upload</span>
                    <input type="file" id="uploadImage" accept="image/*" class="d-none" onchange="previewImg(this)">
                </label>
                <img id="imgPreview" src="" alt=""
                    style="display:none; width:160px; height:160px; object-fit:cover; border-radius:10px; margin-top:6px; border:2px solid var(--main-color);">
            </div>

            <div class="col-md-5">
                <label class="form-label">Name</label>
                <input type="text" class="form-control">
            </div>

            <div class="col-md-5">
                <label class="form-label">Father Name</label>
                <input type="text" class="form-control">
            </div>
        </div>

        <div class="col-md-4">
            <label class="form-label">CNIC</label>
            <input type="text" class="form-control" placeholder="00000-0000000-0">
        </div>

        <div class="col-md-4">
            <label class="form-label">CNIC Expiry Date</label>
            <input type="date" class="form-control">
        </div>


        <div class="col-md-4">
            <label class="form-label">Father CNIC</label>
            <input type="text" class="form-control" placeholder="00000-0000000-0">
        </div>

        <div class="col-md-6">
            <label class="form-label">Nationality</label>
            <input type="text" class="form-control">
        </div>


        <div class="col-md-6">
            <label class="form-label">Date of Birth</label>
            <input type="date" class="form-control">
        </div>

        <div class="col-md-6">
            <label class="form-label">NTN #</label>
            <input type="text" class="form-control">
        </div>

        <div class="col-md-6">
            <label class="form-label">Gender</label>
            <div class="d-flex gap-3 mt-1">
                <div class="form-check d-flex align-items-center gap-1">
                    <input class="check-input" type="radio" name="gender">
                    <label class="form-check-label">Male</label>
                </div>
                <div class="form-check d-flex align-items-center gap-1">
                    <input class="check-input" type="radio" name="gender">
                    <label class="form-check-label">Female</label>
                </div>
            </div>
        </div>


        <div class="col-md-4">
            <label class="form-label">Domicile (District)</label>
            <input type="text" class="form-control">
        </div>

        <div class="col-md-4">
            <label class="form-label">Domicile (Province)</label>
            <input type="text" class="form-control">
        </div>

        <div class="col-md-4">
            <label class="form-label">Town / City of Birth</label>
            <input type="text" class="form-control">
        </div>


        <div class="col-md-6">
            <label class="form-label">Religion</label>
            <input type="text" class="form-control">
        </div>

        <div class="col-md-6">
            <label class="form-label">Sect</label>
            <input type="text" class="form-control">
        </div>

        <div class="col-md-6">
            <label class="form-label">Spouse Name & Nationality</label>
            <input type="text" class="form-control">
        </div>


        <div class="col-md-6">
            <label class="form-label">Marital Status</label>
            <div class="position-relative">
                <select class="form-select pe-4"
                    style="background-color: transparent !important; border: 1px solid #012445; box-shadow: 0 0 4px 2px #5a59593d; appearance: none; -webkit-appearance: none;">
                    <option value="">Select</option>
                    <option>Single</option>
                    <option>Married</option>
                    <option>Separated</option>
                    <option>Divorced</option>
                    <option>Widowed</option>
                </select>
            </div>
        </div>

        {{-- NOK --}}

        <div class="col-md-3">
            <label class="form-label">Name of Next of Kin (NOK)</label>
            <input type="text" class="form-control">
        </div>

        <div class="col-md-3">
            <label class="form-label">NOK CNIC & Date of Expiry</label>
            <input type="text" class="form-control">
        </div>


        <div class="col-md-3">
            <label class="form-label">Relation with NOK</label>
            <input type="text" class="form-control">
        </div>

        <div class="col-md-3">
            <label class="form-label">NOK Date of Birth</label>
            <input type="date" class="form-control">
        </div>

        <div class="col-md-6">
            <label class="form-label">NOK Contact No</label>
            <input type="tel" class="form-control">
        </div>

        {{-- Employment --}}

        <div class="col-md-6">
            <label class="form-label">Date of Joining</label>
            <input type="date" class="form-control">
        </div>

        <div class="col-md-6">
            <label class="form-label">Designation</label>
            <input type="text" class="form-control">
        </div>

        <div class="col-md-6">
            <label class="form-label">Grade</label>
            <input type="text" class="form-control">
        </div>


        <div class="col-md-6">
            <label class="form-label">Branch</label>
            <input type="text" class="form-control">
        </div>

        <div class="col-md-6">
            <label class="form-label">Location</label>
            <input type="text" class="form-control">
        </div>

    </div>
</div>
