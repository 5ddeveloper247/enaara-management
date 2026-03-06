{{-- STEP 6: Family Details --}}
<div class="step" id="step-6">
    <div class="section-title">Section F — Family Details <small class="text-muted fw-normal">(Wife/Husband /
            Children / Parents / Brothers / Sisters)</small>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered align-middle">
            <thead class="bg-main">
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Gender</th>
                    <th>Date of Birth</th>
                    <th>Relation</th>
                    <th>Occupation</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="familyTable">
                <tr>
                    <td>1</td>
                    <td><input type="text" class="form-control form-control-sm"></td>
                    <td>
                        <select class="form-select form-select-sm">
                            <option value="">Select</option>
                            <option>Male</option>
                            <option>Female</option>
                        </select>
                    </td>
                    <td><input type="date" class="form-control form-control-sm"></td>
                    <td><input type="text" class="form-control form-control-sm"></td>
                    <td><input type="text" class="form-control form-control-sm"></td>
                    <td>
                        <button type="button"
                            class="action-btn border-0 text-danger bg-danger-subtle delete-shift-type"
                            onclick="removeRow(this)" title="Delete">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <button type="button" class="btn btn-sm btn-outline-primary" onclick="addFamilyRow()">+ Add
        Member</button>
</div>
