@extends('shared::layouts.app')

@section('content')
<div class="page-header d-flex align-items-center">
    <div class="page-header-left">
        <div class="page-header-title">
            <h5 class="m-b-10">Payroll Module</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="/dashboard">Home</a></li>
            <li class="breadcrumb-item">{{ isset($is_edit) ? 'Edit Attendance' : 'Add Attendance' }}</li>
        </ul>
    </div>

    @if(!isset($is_edit))
    <!-- <div class="dropdown d-inline-block ms-auto float-end">
        <a href="#" class="btn btn-icon btn-light-brand" data-bs-toggle="dropdown" aria-expanded="false" title="Import Attendance">
            <i class="fas fa-upload"></i>
        </a>

        <div class="dropdown-menu dropdown-menu-end p-4 shadow-sm border-0" style="width: 320px;">
            <form action="{{ route('payroll.attendance.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-bold text-dark mb-2">Import Attendance (Excel)</label>
                    <input type="file" class="form-control" name="import_file" accept=".xlsx, .xls, .csv" required>
                </div>
                <button type="submit" class="btn btn-success w-100 fw-bold">
                    <i class="fas fa-file-import me-2"></i> UPLOAD & CALCULATE
                </button>
            </form>
        </div>
    </div> -->
    @endif
</div>

<div style="padding: 30px;">
    <div style="background: #f0f8ff; padding: 10px; margin-bottom: 15px; border-radius: 4px; border-left: 4px solid #3858f9;">
        <small style="color: #0066cc;">
            <strong>Debug:</strong> Total Employees Found: <strong>{{ count($employees) }}</strong>
        </small>
    </div>

    <div style="background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        <form action="{{ isset($is_edit) ? route('payroll.attendance.updateByDate', $edit_date) : route('payroll.attendance.store') }}" method="POST">
            @csrf
            
            @if(isset($is_edit))
                @method('PUT')
            @endif
            <div class="w-100 d-flex justify-content-between">

                <div style="margin-bottom: 25px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: bold; color: #333;">Attendance Date:</label>
                    <input type="date" name="attendance_date" 
                           value="{{ $edit_date ?? date('Y-m-d') }}" 
                           {{ isset($is_edit) ? 'readonly' : 'required' }} 
                           style="padding: 10px; border: 1px solid #ccc; border-radius: 4px; width: 200px; font-size: 14px; background: {{ isset($is_edit) ? '#f5f5f5' : 'white' }};">
                    
                    @if(isset($is_edit))
                        <small style="color: #d9534f; margin-left: 10px;">Date cannot be changed in edit mode.</small>
                    @endif
                </div>
    
                <!-- Right: Bulk Status Dropdown -->
                <div>
                    <select id="bulk_status"
                            onchange="applyBulkStatus()"
                            style="padding: 8px; border: 1px solid #ddd; border-radius: 4px; width: 150px; cursor: pointer; height: 40px; margin: 35px;">
                        <option value="">Select</option>
                        <option value="present">Present</option>
                        <option value="absent">Absent</option>
                        <option value="half_day">Half Day</option>
                        <option value="wfh">WFH</option>
                        <option value="leave">Leave</option>
                        <option value="late">Late</option>
                        <option value="activity">Activity</option>
                        <option value="early_leave">Early Leave</option>
                    </select>
                </div>
            </div>

            @if(count($employees) == 0)
                <div style="background: #fff3cd; padding: 20px; border-radius: 4px; border: 1px solid #ffc107; color: #856404;">
                    <strong>⚠️ No Employees Found</strong>
                    <p style="margin: 10px 0 0 0;">Please add employees first before marking attendance.</p>
                    <a href="{{ route('employees.create') }}" style="color: #0066cc; text-decoration: none; font-weight: bold;">+ Add Employee Now</a>
                </div>
            @else
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
                        <thead>
                            <tr style="background: #f5f5f5; border-bottom: 2px solid #ddd;">
                                <th style="padding: 15px; text-align: center; border-right: 1px solid #ddd;">
                                    <input type="checkbox" id="select_all" onclick="toggleAllCheckboxes(this)">
                                </th>
                                <th style="padding: 15px; text-align: left; border-right: 1px solid #ddd; font-weight: bold;">Employee Name</th>
                                <th style="padding: 15px; text-align: center; border-right: 1px solid #ddd; font-weight: bold; width: 140px;">Check In</th>
                                <th style="padding: 15px; text-align: center; border-right: 1px solid #ddd; font-weight: bold; width: 140px;">Check Out</th>
                                <th style="padding: 15px; text-align: center; border-right: 1px solid #ddd; font-weight: bold; width: 110px;">Total Time</th>
                                <th style="padding: 15px; text-align: left; font-weight: bold; width: 130px;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($employees as $index => $emp)
                            <tr style="border-bottom: 1px solid #ddd; background: {{ $loop->even ? '#fafafa' : 'white' }};">
                                <td style="padding: 15px; text-align: center; border-right: 1px solid #ddd;">
                                    <input type="checkbox" class="row_checkbox" data-index="{{ $index }}">
                                </td>
                                <td style="padding: 15px; border-right: 1px solid #ddd; font-weight: 500;">
                                    {{ $emp->name }}
                                    <input type="hidden" name="employees[{{ $index }}][employee_id]" value="{{ $emp->id }}">
                                </td>
                                <td style="padding: 15px; border-right: 1px solid #ddd; text-align: center;">
                                    <div style="margin-bottom: 8px;">
                                        <input type="time"
                                        id="check_in_{{ $index }}"
                                        name="employees[{{ $index }}][check_in]"
                                        value="{{ $emp->old_check_in ?? '' }}"
                                        onchange="calculateDuration({{ $index }})">
                                    </div>
                                    <label style="font-size: 12px; cursor: pointer;">
                                        <input type="checkbox" id="toggle_in_{{ $index }}" 
                                               onchange="setNowTime('check_in_{{ $index }}', {{ $index }})">
                                        <span>Set Now</span>
                                    </label>
                                </td>
                                <td style="padding: 15px; border-right: 1px solid #ddd; text-align: center;">
                                    <div style="margin-bottom: 8px;">
                                       <input type="time"
                                       id="check_out_{{ $index }}"
                                        name="employees[{{ $index }}][check_out]"
                                        value="{{ $emp->old_check_out ?? '' }}"
                                        onchange="calculateDuration({{ $index }})">
                                    </div>
                                    <label style="font-size: 12px; cursor: pointer;">
                                        <input type="checkbox" id="toggle_out_{{ $index }}" 
                                               onchange="setNowTime('check_out_{{ $index }}', {{ $index }})">
                                        <span>Set Now</span>
                                    </label>
                                </td>
                                <td style="padding: 15px; border-right: 1px solid #ddd; text-align: center;">
                                    <span id="duration_{{ $index }}" style="font-weight: bold; color: {{ (isset($emp->old_duration) && $emp->old_duration != '--') ? '#3858f9' : '#999' }};">
                                        {{ $emp->old_duration ?? '--' }}
                                    </span>
                                </td>
                                <td style="padding: 15px;">
                                    <select name="employees[{{ $index }}][status]"  id="status_{{ $index }}" style="padding: 8px; border: 1px solid #ddd; border-radius: 3px; width: 120px; cursor: pointer;">
                                        <option value="" {{ !isset($emp->old_status) ? 'selected' : '' }}>Select</option>
                                        <option value="present" {{ (isset($emp->old_status) && $emp->old_status == 'present') ? 'selected' : '' }}>Present</option>
                                        <option value="absent" {{ (isset($emp->old_status) && $emp->old_status == 'absent') ? 'selected' : '' }}>Absent</option>
                                        <option value="half_day" {{ (isset($emp->old_status) && $emp->old_status == 'half_day') ? 'selected' : '' }}>Half Day</option>
                                        <option value="wfh" {{ (isset($emp->old_status) && $emp->old_status == 'wfh') ? 'selected' : '' }}>WFH</option>
                                        <option value="leave" {{ (isset($emp->old_status) && $emp->old_status == 'leave') ? 'selected' : '' }}>Leave</option>
                                        <option value="late" {{ (isset($emp->old_status) && $emp->old_status == 'late') ? 'selected' : '' }}>Late</option>
                                        <option value="activity" {{ (isset($emp->old_status) && $emp->old_status == 'activity') ? 'selected' : '' }}>Activity</option>
                                        <option value="early_leave" {{ (isset($emp->old_status) && $emp->old_status == 'early_leave') ? 'selected' : '' }}>Early Leave</option>
                                    </select>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

             <div class="d-flex flex-column flex-sm-row justify-content-end gap-2 mt-4">
                @if(isset($is_edit))
                    <a href="{{ route('payroll.attendance.add') }}" 
                    class="btn btn-outline-secondary w-100 w-sm-auto">
                        Cancel
                    </a>
                @endif

                <button type="submit" 
                        class="btn btn-primary fw-bold w-100 w-sm-auto px-4">
                    {{ isset($is_edit) ? 'UPDATE ATTENDANCE' : 'SAVE ATTENDANCE' }}
                </button>

            </div>
            @endif
        </form>
    </div>
</div>

<script>
    window.onload = function () {
    document.querySelectorAll('[id^="check_in_"]').forEach((el, index) => {
        calculateDuration(index);
    });
    };
    function setNowTime(fieldId, index) {
        const field = document.getElementById(fieldId);
        const now = new Date();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        field.value = hours + ':' + minutes;
        
        // Enable checkout if check-in is set
        if (fieldId.includes('check_in')) {
            const checkOutField = document.getElementById('check_out_' + index);
            const checkOutToggle = document.getElementById('toggle_out_' + index);
            checkOutField.disabled = false;
            checkOutField.style.background = 'white';
            checkOutToggle.disabled = false;
        }
        
        calculateDuration(index);
    }

   function calculateDuration(index) {
        const checkInField = document.getElementById('check_in_' + index);
        const checkOutField = document.getElementById('check_out_' + index);
        const toggleOut = document.getElementById('toggle_out_' + index);
        const durationDisplay = document.getElementById('duration_' + index);

        const checkIn = checkInField.value;
        const checkOut = checkOutField.value;

        // ✅ ENABLE / DISABLE CHECK-OUT BASED ON CHECK-IN
        // if (checkIn) {
        //     checkOutField.disabled = false;
        //     toggleOut.disabled = false;
        //     checkOutField.style.background = 'white';
        // } else {
        //     checkOutField.disabled = true;
        //     toggleOut.disabled = true;
        //     checkOutField.style.background = '#f5f5f5';
        //     checkOutField.value = ''; // reset checkout
        // }

        // ✅ DURATION CALCULATION
        if (checkIn && checkOut) {
            const [inHours, inMinutes] = checkIn.split(':').map(Number);
            const [outHours, outMinutes] = checkOut.split(':').map(Number);

            let inTotal = inHours * 60 + inMinutes;
            let outTotal = outHours * 60 + outMinutes;

            // Handle night shift (cross midnight)
            if (outTotal < inTotal) {
                outTotal += 24 * 60;
            }

            const diffMinutes = outTotal - inTotal;
            const hours = Math.floor(diffMinutes / 60);
            const minutes = diffMinutes % 60;

            durationDisplay.textContent = hours + 'h ' + minutes + 'm';
            durationDisplay.style.color = '#3858f9';
        } else {
            durationDisplay.textContent = '--';
            durationDisplay.style.color = '#999';
        }
    }

    function toggleAllCheckboxes(source) {
        let checkboxes = document.querySelectorAll('.row_checkbox');
        checkboxes.forEach(cb => cb.checked = source.checked);
    }

    function applyBulkStatus() {
        let selectedStatus = document.getElementById('bulk_status').value;

        if (!selectedStatus) return;

        let checkboxes = document.querySelectorAll('.row_checkbox:checked');

        if (checkboxes.length === 0) {
            alert("Please select at least one employee.");
            return;
        }

        checkboxes.forEach(cb => {
            let index = cb.getAttribute('data-index');
            let statusDropdown = document.getElementById('status_' + index);

            if (statusDropdown) {
                statusDropdown.value = selectedStatus;
            }
        });

        // Optional: reset dropdown after applying
        document.getElementById('bulk_status').value = "";
    }
</script>
@endsection