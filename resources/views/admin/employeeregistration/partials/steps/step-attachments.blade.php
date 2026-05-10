<style>
    .attachment-dark-theme {
        background-color: #012445;
        color: #f8fafc;
        font-family: 'Inter', sans-serif;
    }
    .attachment-dark-theme .upload-dropzone {
        border: 1px dashed rgba(255,255,255,0.2);
        background-color: rgba(255,255,255,0.02);
        transition: all 0.3s ease;
        cursor: pointer;
    }
    .attachment-dark-theme .upload-dropzone:hover {
        border-color: #ffc107;
        background-color: rgba(255,193,7,0.05);
    }
    .attachment-dark-theme .circular-chart {
        display: block;
        margin: 0 auto;
        max-width: 80%;
        max-height: 250px;
    }
    .attachment-dark-theme .circle-bg {
        fill: none;
        stroke: rgba(255,255,255,0.1);
        stroke-width: 3.8;
    }
    .attachment-dark-theme .circle {
        fill: none;
        stroke-width: 2.8;
        stroke-linecap: round;
        animation: progress 1s ease-out forwards;
    }
    @keyframes progress {
        0% { stroke-dasharray: 0 100; }
    }
    .attachment-dark-theme .form-check-input:checked {
        background-color: #ffc107;
        border-color: #ffc107;
    }
    .attachment-dark-theme .form-check-input {
        border-color: rgba(255,255,255,0.3);
        background-color: transparent;
    }
    .attachment-dark-theme .doc-item {
        padding: 12px 16px;
        border-radius: 8px;
        background: transparent;
        transition: background 0.2s;
    }
    .attachment-dark-theme .doc-item:hover {
        background: rgba(255,255,255,0.03);
    }
    .attachment-dark-theme .form-control, 
    .attachment-dark-theme .form-select {
        background-color: rgba(255,255,255,0.05);
        border: 1px solid rgba(255,255,255,0.1);
        color: #fff;
        border-radius: 12px;
        padding: 0.75rem 1rem;
        transition: all 0.2s ease;
    }
    .attachment-dark-theme .form-control:focus, 
    .attachment-dark-theme .form-select:focus {
        background-color: rgba(255,255,255,0.08);
        border-color: #ffc107;
        box-shadow: 0 0 0 0.25rem rgba(255, 193, 7, 0.1);
        color: #fff;
    }
    .attachment-dark-theme .form-control::placeholder {
        color: rgba(255,255,255,0.3);
    }
    .attachment-dark-theme .form-select {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23ffffff' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e");
    }
    .attachment-dark-theme .form-select option {
        background-color: #012445;
        color: #fff;
    }
</style>

<div class="card border-0 rounded-4 attachment-dark-theme w-100" style="min-height: 600px;">
    <div class="card-body p-4 p-md-5">
        <!-- Header -->
        <div class="mb-4">
            <h2 class="fw-bold mb-1 text-white" style="font-size: 2rem;">Attachments</h2>
        </div>

        <div class="row g-4">
            <!-- Left Column: Upload Area -->
            <div class="col-lg-8">
                <!-- Attachment Info Inputs -->
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label text-white small fw-semibold opacity-75">Attachment Name</label>
                        <input type="text" id="onPageAttachmentName" class="form-control" placeholder="e.g. National ID Front">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-white small fw-semibold opacity-75">Attachment Type</label>
                        <select id="onPageAttachmentType" class="form-select">
                            <option value="" selected disabled>Select attachment type...</option>
                            @foreach($requiredDocumentTypes as $type)
                                <option value="{{ $type->name }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Dropzone -->
                <div class="upload-dropzone p-5 text-center rounded-4 mb-4" id="onPageDropzone">
                    <input type="file" id="onPageAttachmentUpload" class="d-none" multiple accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xls,.xlsx,.zip,.txt">
                    <div class="d-inline-flex align-items-center justify-content-center mb-3 rounded-circle" style="width: 60px; height: 60px; border: 1px solid rgba(255,193,7,0.5);">
                        <i class="bi bi-cloud-arrow-up text-warning fs-3"></i>
                    </div>
                    <h5 class="fw-bold mb-2 text-white">Drop files here or click to browse</h5>
                    <p class="text-muted small mb-4" style="color: rgba(255,255,255,0.5)!important;">Supported formats: JPG, PNG, PDF, DOC, DOCX, XLS, XLSX, ZIP, TXT &mdash; up to 20MB each</p>
                    <div class="d-flex justify-content-center gap-2 flex-wrap">
                        <span class="badge rounded-pill fw-normal px-3 py-2" style="background: rgba(255,255,255,0.08); color: rgba(255,255,255,0.7); letter-spacing: 1px;">JPG</span>
                        <span class="badge rounded-pill fw-normal px-3 py-2" style="background: rgba(255,255,255,0.08); color: rgba(255,255,255,0.7); letter-spacing: 1px;">PNG</span>
                        <span class="badge rounded-pill fw-normal px-3 py-2" style="background: rgba(255,255,255,0.08); color: rgba(255,255,255,0.7); letter-spacing: 1px;">PDF</span>
                        <span class="badge rounded-pill fw-normal px-3 py-2" style="background: rgba(255,255,255,0.08); color: rgba(255,255,255,0.7); letter-spacing: 1px;">DOC</span>
                        <span class="badge rounded-pill fw-normal px-3 py-2" style="background: rgba(255,255,255,0.08); color: rgba(255,255,255,0.7); letter-spacing: 1px;">DOCX</span>
                        <span class="badge rounded-pill fw-normal px-3 py-2" style="background: rgba(255,255,255,0.08); color: rgba(255,255,255,0.7); letter-spacing: 1px;">XLSX</span>
                        <span class="badge rounded-pill fw-normal px-3 py-2" style="background: rgba(255,255,255,0.08); color: rgba(255,255,255,0.7); letter-spacing: 1px;">ZIP</span>
                    </div>
                </div>

                <!-- Uploaded Files Section -->
                <div class="text-center position-relative my-4 mt-5">
                    <hr style="border-color: rgba(255,255,255,0.1);">
                    <span class="position-absolute top-50 start-50 translate-middle px-3 text-muted fw-semibold" style="background-color: #012445; letter-spacing: 2px; font-size: 0.7rem;">UPLOADED FILES</span>
                </div>

                <div id="onPageAttachmentListingEmpty" class="text-center py-4 d-none">
                    <i class="bi bi-folder text-muted opacity-25 display-4 mb-3 d-block"></i>
                    <p class="text-muted mb-1" style="color: rgba(255,255,255,0.6)!important;">No files uploaded yet.</p>
                    <p class="text-muted small" style="color: rgba(255,255,255,0.4)!important;">Use the area above to get started.</p>
                </div>

                <!-- Hidden container for JS to append actual attachments while keeping the design untouched -->
                <div id="onPageAttachmentListing" class="row g-3"></div>
            </div>

            <!-- Right Column: Documents List -->
            <div class="col-lg-4">
                <div class="card border-0 rounded-4 h-100" style="background-color: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05)!important;">
                    <div class="card-header border-bottom-0 bg-transparent p-4 pb-0 d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h6 class="fw-bold mb-0 text-white fs-5">Documents List</h6>
                        </div>
                        <button type="button" id="toggleAddDocType" class="btn btn-sm rounded-circle d-flex align-items-center justify-content-center p-0" style="width: 36px; height: 36px; background: rgba(255,193,7,0.1); color: #ffc107; border: 1px solid rgba(255,193,7,0.2);">
                            <i class="bi bi-plus-lg fs-6"></i>
                        </button>
                    </div>
                    
                    <div class="card-body p-4 pt-1">
                        <!-- Add Document Type Input (Hidden by default) -->
                        <div id="addDocTypeContainer" class="mb-4" style="display: none;">
                            <div class="input-group">
                                <input type="text" id="newDocTypeName" class="form-control form-control-sm" placeholder="Document name...">
                                <button class="btn btn-warning btn-sm" type="button" id="submitNewDocType">Add</button>
                            </div>
                        </div>

                        <div class="documents-checklist" id="requiredDocumentsList">
                            @foreach($requiredDocumentTypes as $type)
                                <div class="doc-item d-flex align-items-center justify-content-between p-3 rounded-3 mb-2" data-doc-type="{{ $type->name }}">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="form-check m-0 p-0 d-flex align-items-center">
                                            <input class="form-check-input m-0 doc-checkbox" type="checkbox" disabled style="width: 18px; height: 18px;">
                                        </div>
                                        <span class="text-white-50 small fw-semibold doc-name">{{ $type->name }}</span>
                                    </div>
                                    <span class="badge status-badge rounded-pill px-3 py-1" style="background: rgba(255,255,255,0.05); color: rgba(255,255,255,0.4); font-size: 0.65rem;">Pending</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
