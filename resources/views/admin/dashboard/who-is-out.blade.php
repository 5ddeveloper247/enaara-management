<div class="col-12">
    <div class="card rounded-5 p-4 border-0">
        <div class="card-header border-0 p-0 d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Who is Out Today?</h5>
            <span class="badge bg-main" id="whoIsOutCount">0</span>
        </div>
        <div class="card-body pt-3 p-0">
            <div class="avatar-gallery who-is-out-gallery p-0" id="whoIsOutContainer">
                <!-- Loading State -->
                <div class="text-center py-4 w-100" id="whoIsOutLoading">
                    <div class="spinner-border spinner-border-sm text-main" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<template id="whoIsOutTemplate">
    <div class="avatar-gallery-item" title="">
        <div class="avatar-gallery-avatar">
            <span class="initials"></span>
            <span class="avatar-status-dot on-leave"></span>
        </div>
        <div class="avatar-gallery-name"></div>
        <div class="avatar-gallery-role"></div>
    </div>
</template>
