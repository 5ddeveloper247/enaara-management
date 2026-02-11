<table id="usersTable" class="display nowrap table table-striped" style="width:100%">
    <thead class="bg-main">
        <tr>
            <th class="">User</th>
            <th class="">Employee ID</th>
            <th class="">Department</th>
            <th class="">Role</th>
            <th class="">Last Login</th>
            <th class="">Status</th>
            <th class="text-end ">Actions</th>
        </tr>
    </thead>
    <tbody class="bg-transparent">
        <!-- Sample data - Replace with dynamic data from backend -->
        @for ($i = 0; $i < 40; $i++)
        <tr>
            <td>
                <div class="d-flex align-items-center">
                    <div class="user-avatar me-3">AA</div>
                    <div>
                        <div class="fw-semibold">Ahmed Ali</div>
                        <small class="text-muted">ahmed.ali@enaara.com</small>
                    </div>
                </div>
            </td>
            <td><span class="badge px-3 rounded-1 bg-light text-dark">EMP-001</span></td>
            <td><span class="badge px-3 rounded-1 bg-primary">Sales</span></td>
            <td>Manager</td>
            <td>
                <small class="text-muted">2 hours ago</small>
            </td>
            <td>
                <div class="form-check form-switch">
                    <input class="form-check-input status-toggle" type="checkbox" checked data-user-id="1">
                </div>
            </td>
            <td class="text-end">
                <div class="btn-group d-flex align-items-center gap-1">
                    <button type="button" class="action-btn border-0 text-white btn-primary edit-user"
                        data-bs-toggle="offcanvas" 
                        data-bs-target="#userCanvas"
                        data-mode="edit"
                        data-user-id="1" 
                        data-user-name="Ahmed Ali"
                        data-user-email="ahmed.ali@enaara.com"
                        data-employee-id="EMP-001"
                        data-department="Sales"
                        data-role="Manager"
                        title="Edit">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button type="button" class="action-btn border-0 text-white btn-primary view-user"
                        data-bs-toggle="offcanvas" 
                        data-bs-target="#userCanvas"
                        data-mode="view"
                        data-user-id="1" 
                        data-user-name="Ahmed Ali"
                        data-user-email="ahmed.ali@enaara.com"
                        data-employee-id="EMP-001"
                        data-department="Sales"
                        data-role="Manager"
                        title="View">
                        <i class="bi bi-eye"></i>
                    </button>
                    <button type="button" class="action-btn border-0 text-danger bg-danger-subtle delete-user"
                        data-bs-toggle="modal" 
                        data-bs-target="#deleteConfirmModal"
                        data-user-id="1"
                        data-user-name="Ahmed Ali"
                        data-user-email="ahmed.ali@enaara.com"
                        title="Delete">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </td>
        </tr>
        @endfor
    </tbody>
</table>