@extends('shared::layouts.app')

@section('content')

<div class="main-content">
<div class="container-fluid">

    <div class="card shadow-sm custom-card">
        <div class="card-header bg-light fw-bold">
            Edit Holiday
        </div>

        <div class="card-body">

            <form method="POST" action="{{ route('holidays.update', $holiday->id) }}">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="fw-bold">Holiday Name</label>
                    <input type="text" name="title"
                        class="form-control uppercase"
                        value="{{ strtoupper($holiday->title) }}" required>
                </div>

                <div class="mb-3">
                    <label class="fw-bold">Date</label>
                    <input type="date" name="date"
                        class="form-control"
                        value="{{ $holiday->date }}" required>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">

                    <a href="{{ route('holidays.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Back
                    </a>

                    <button class="btn btn-primary">
                        Update
                    </button>

                </div>

            </form>

        </div>
    </div>

</div>
</div>

<!-- UPPERCASE SCRIPT -->
<script>
document.querySelectorAll('.uppercase').forEach(input => {
    input.addEventListener('input', function () {
        this.value = this.value.toUpperCase();
    });
});
</script>

@endsection