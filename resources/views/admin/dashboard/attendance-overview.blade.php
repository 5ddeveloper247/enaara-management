<div class="col-12">
    <div class="card bg-transparent rounded-5 border-0 p-4">
        <div class="card-header p-0 pb-4 border-2 border-dark d-flex justify-content-between align-items-center">
            <h6 class="mb-0">Attendance Overview</h6>
            <div class="d-flex gap-0" role="group" aria-label="Period selection">
                <button type="button" class="btn btn-sm btn-outline-primary period-btn active" data-period="7"
                    id="period7Days">7 Days</button>
                <button type="button" class="btn btn-sm btn-outline-primary period-btn" data-period="14"
                    id="period14Days">14 Days</button>
                {{-- <button type="button" class="btn btn-sm btn-outline-primary period-btn" data-period="28"
                    id="period28Days">28 Days</button> --}}
            </div>
        </div>
        <div class="card-body px-0">
            <div class="chart-container">
                <canvas id="attendanceChart"></canvas>
            </div>
        </div>
    </div>
</div>
