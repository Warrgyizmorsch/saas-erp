@extends('shared::layouts.app')

@section('content')
    <div class="page-header d-flex justify-content-between align-items-center">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Lead Sources</h5>
            </div>
            <ul class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item active">Lead Sources</li>
            </ul>
        </div>
        <button type="button" class="btn btn-primary" id="add-source-btn">
            <i class="feather-plus me-2"></i> Add Source
        </button>
    </div>

    <div class="main-content mt-3">
        <div class="row">
            <div class="col-12">

                <!-- Collapsible Form -->
                <div class="card mb-4 d-none" id="form-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0" id="form-title">Add Source</h5>
                        <button type="button" class="btn-close" id="close-form"></button>
                    </div>
                    <div class="card-body">
                        <form id="sourceForm" method="POST" action="{{ route('lead_sources.store') }}">
                            @csrf
                            <input type="hidden" name="_method" id="form-method" value="POST">
                            <input type="hidden" name="source_id" id="source_id">

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Source Name</label>
                                    <input type="text" name="source_name" id="source_name" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Description</label>
                                    <input type="text" name="description" id="description" class="form-control">
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

                <!-- Sources Table -->
                <div class="card stretch min-h-100">
                    <div class="card-header">
                        <h5 class="mb-0">Sources List</h5>
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table table-bordered align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Sr. No</th>
                                    <th>Source Name</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sources as $s)
                                    <tr>
                                        <td>{{ $loop->iteration + ($sources->currentPage() - 1) * $sources->perPage() }}</td>
                                        <td>{{ $s->source_name }}</td>
                                        <td>{{ $s->description }}</td>
                                        <td class="text-center">
                                            <form method="POST" action="{{ route('lead_sources.toggle', $s) }}"
                                                class="status-toggle-form d-inline-block m-0">
                                                @csrf
                                                @method('PUT')
                                                <button type="submit"
                                                    class="btn btn-sm {{ $s->is_active ? 'btn-success' : 'btn-danger' }}">
                                                    {{ $s->is_active ? 'Active' : 'Inactive' }}
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
                                                                            btn.textContent = currentStatus;
                                                                        }
                                                                    })
                                                                    .catch(err => {
                                                                        console.error(err);
                                                                        Swal.fire('Error', 'Something went wrong!', 'error');
                                                                        btn.textContent = currentStatus;
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
                                            <button class="btn btn-sm btn-warning edit-btn" data-id="{{ $s->id }}"
                                                data-name="{{ $s->source_name }}" data-description="{{ $s->description }}"
                                                data-status="{{ $s->is_active }}">
                                                <i class="feather-edit"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="d-flex justify-content-end mt-3">
                            {{ $sources->links('pagination::bootstrap-5') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const formCard = document.getElementById('form-card');
        const addBtn = document.getElementById('add-source-btn');
        const closeForm = document.getElementById('close-form');
        const form = document.getElementById('sourceForm');
        const methodInput = document.getElementById('form-method');
        const idInput = document.getElementById('source_id');
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
                const name = btn.dataset.name;
                const desc = btn.dataset.description;
                const status = btn.dataset.status;

                form.action = `/lead-sources/update/${id}`;
                methodInput.value = 'PUT';
                idInput.value = id;
                document.getElementById('source_name').value = name;
                document.getElementById('description').value = desc;
                statusSelect.value = status;

                title.innerText = "Edit Source";
                submitBtn.innerHTML = '<i class="feather-check me-2"></i> Update';
                cancelBtn.classList.remove('d-none');
                formCard.classList.remove('d-none');
            });
        });

        cancelBtn.addEventListener('click', resetForm);

        function resetForm() {
            form.action = `{{ route('lead_sources.store') }}`;
            methodInput.value = 'POST';
            idInput.value = '';
            document.getElementById('source_name').value = '';
            document.getElementById('description').value = '';
            statusSelect.value = 1;

            title.innerText = "Add Source";
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

        /* ✅ Maintain footer at bottom when no data */
        .main-content {
            min-height: 100vh;
        }
    </style>
@endsection