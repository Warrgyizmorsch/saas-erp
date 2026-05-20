@extends('layouts.app')

@section('content')

    <div class="page-header d-flex justify-content-between align-items-center">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Lead Questions</h5>
            </div>
            <ul class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item active">Lead Questions</li>
            </ul>
        </div>
        <button type="button" class="btn btn-primary" id="add-question-btn">
            <i class="feather-plus me-2"></i> Add Question
        </button>
    </div>

    <div class="main-content mt-3">
        <div class="row">
            <div class="col-12">

                <!-- Collapsible Form -->
                <div class="card mb-4 d-none" id="form-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0" id="form-title">Add Question</h5>
                        <button type="button" class="btn-close" id="close-form"></button>
                    </div>
                    <div class="card-body">
                        <form id="questionForm" method="POST" action="{{ route('lead_questions.store') }}">
                            @csrf
                            <input type="hidden" name="_method" id="form-method" value="POST">
                            <input type="hidden" name="question_id" id="question_id">

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Field Name</label>
                                    <input type="text" name="field_name" id="field_name" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Label</label>
                                    <input type="text" name="label" id="label" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Status</label>
                                    <select name="is_active" id="status" class="form-control">
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                </div>
                            </div>

                            <div class="card-footer mt-4 d-flex justify-content-end gap-2 flex-wrap">
                                <button type="button" class="btn btn-secondary d-none" id="form-cancel">Cancel</button>
                                <button class="btn btn-primary" id="form-submit">
                                    <i class="feather-save me-2"></i> Save
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Questions Table -->
                <div class="card stretch">
                    <div class="card-header">
                        <h5 class="mb-0">Questions List</h5>
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table table-bordered align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Sr. No</th>
                                    <th>Field Name</th>
                                    <th>Label</th>
                                    <th>Status</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($questions as $q)
                                    <tr>
                                        <td>{{ $loop->iteration + ($questions->currentPage() - 1) * $questions->perPage() }}
                                        </td>
                                        <td>{{ $q->field_name }}</td>
                                        <td>{{ $q->label }}</td>
                                        <td class="text-center">
                                            <form method="POST" action="{{ route('lead_questions.toggle', $q) }}"
                                                class="status-toggle-form d-inline-block m-0">
                                                @csrf
                                                @method('PUT')
                                                <button type="submit"
                                                    class="btn btn-sm {{ $q->is_active ? 'btn-success' : 'btn-danger' }}">
                                                    {{ $q->is_active ? 'Active' : 'Inactive' }}
                                                </button>
                                            </form>
                                        </td>

                                        @push('scripts')
                                            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
                                            <script>
                                                document.querySelectorAll('.status-toggle-form').forEach(form => {
                                                    form.addEventListener('submit', function (e) {
                                                        e.preventDefault();

                                                        const btn = this.querySelector('button');
                                                        const currentStatus = btn.textContent.trim();
                                                        const newStatus = (currentStatus === 'Active') ? 'Inactive' : 'Active';
                                                        const actionUrl = this.action;
                                                        const token = this.querySelector('[name="_token"]').value;

                                                        Swal.fire({
                                                            title: 'Confirm Status Change',
                                                            html: `<strong>${currentStatus}</strong> ➝ <strong>${newStatus}</strong>`,
                                                            icon: 'warning',
                                                            showCancelButton: true,
                                                            confirmButtonText: 'Yes, change it!',
                                                            cancelButtonText: 'Cancel'
                                                        }).then((result) => {
                                                            if (result.isConfirmed) {
                                                                // disable button while processing
                                                                btn.disabled = true;
                                                                btn.innerHTML = 'Changing...';

                                                                fetch(actionUrl, {
                                                                    method: 'POST',
                                                                    headers: {
                                                                        'X-CSRF-TOKEN': token,
                                                                        'X-Requested-With': 'XMLHttpRequest',
                                                                        'Content-Type': 'application/json'
                                                                    },
                                                                    body: JSON.stringify({ _method: 'PUT' })
                                                                })
                                                                    .then(res => res.json())
                                                                    .then(data => {
                                                                        if (data.success) {
                                                                            // update button UI
                                                                            btn.textContent = data.is_active ? 'Active' : 'Inactive';
                                                                            btn.classList.toggle('btn-success', data.is_active);
                                                                            btn.classList.toggle('btn-danger', !data.is_active);

                                                                            Swal.fire({
                                                                                icon: 'success',
                                                                                title: 'Updated!',
                                                                                text: `Status changed to "${btn.textContent}"`,
                                                                                timer: 1500,
                                                                                showConfirmButton: false
                                                                            });
                                                                        } else {
                                                                            Swal.fire('Failed', 'Unable to update status', 'error');
                                                                            btn.textContent = currentStatus; // reset
                                                                        }
                                                                    })
                                                                    .catch(err => {
                                                                        console.error(err);
                                                                        Swal.fire('Error', 'Something went wrong!', 'error');
                                                                        btn.textContent = currentStatus; // reset
                                                                    })
                                                                    .finally(() => {
                                                                        btn.disabled = false;
                                                                    });
                                                            }
                                                        });
                                                    });
                                                });
                                            </script>
                                        @endpush

                                        <td class="text-center d-flex justify-content-center gap-2 flex-wrap">
                                            <button class="btn btn-sm btn-warning edit-btn" data-id="{{ $q->id }}"
                                                data-field="{{ $q->field_name }}" data-label="{{ $q->label }}"
                                                data-status="{{ $q->is_active }}">
                                                <i class="feather-edit"></i>
                                            </button>
                                            <!-- <form method="POST" action="{{ route('lead_questions.destroy',$q) }}">
                                                    @csrf @method('DELETE')
                                                    <button class="btn btn-sm btn-danger">
                                                        <i class="feather-trash-2"></i> Delete
                                                    </button>
                                                </form> -->
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="d-flex justify-content-end mt-3">
                            {{ $questions->links('pagination::bootstrap-5') }}
                        </div>

                    </div>
                </div>
                <!-- End Table -->
            </div>
        </div>
    </div>

    <script>
        const formCard = document.getElementById('form-card');
        const addBtn = document.getElementById('add-question-btn');
        const closeForm = document.getElementById('close-form');
        const form = document.getElementById('questionForm');
        const methodInput = document.getElementById('form-method');
        const idInput = document.getElementById('question_id');
        const title = document.getElementById('form-title');
        const submitBtn = document.getElementById('form-submit');
        const cancelBtn = document.getElementById('form-cancel');
        const statusSelect = document.getElementById('status');

        addBtn.addEventListener('click', () => {
            resetForm();
            formCard.classList.remove('d-none');
        });

        closeForm.addEventListener('click', () => {
            formCard.classList.add('d-none');
            resetForm();
        });

        document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.dataset.id;
                const field = btn.dataset.field;
                const label = btn.dataset.label;
                const status = btn.dataset.status;

                form.action = `/lead-questions/update/${id}`;
                methodInput.value = 'PUT';
                idInput.value = id;
                document.getElementById('field_name').value = field;
                document.getElementById('label').value = label;
                statusSelect.value = status;

                title.innerText = "Edit Question";
                submitBtn.innerHTML = '<i class="feather-check me-2"></i> Update';
                cancelBtn.classList.remove('d-none');
                formCard.classList.remove('d-none');
            });
        });

        cancelBtn.addEventListener('click', resetForm);

        function resetForm() {
            form.action = `{{ route('lead_questions.store') }}`;
            methodInput.value = 'POST';
            idInput.value = '';
            document.getElementById('field_name').value = '';
            document.getElementById('label').value = '';
            statusSelect.value = 1;

            title.innerText = "Add Question";
            submitBtn.innerHTML = '<i class="feather-save me-2"></i> Save';
            cancelBtn.classList.add('d-none');
        }
    </script>

    <style>
        #form-card {
            height: auto;
        }

        .table td,
        .table th {
            vertical-align: middle;
        }

        .card-footer {
            border-top: 1px solid #e9ecef;
        }

        .d-flex.gap-2>form,
        .d-flex.gap-2>button {
            margin: 0;
        }

        @media (max-width: 576px) {
            .d-flex.gap-2 {
                flex-direction: column !important;
            }
        }
    </style>
@endsection