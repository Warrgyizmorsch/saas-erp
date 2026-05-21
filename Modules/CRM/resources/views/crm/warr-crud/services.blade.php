@extends('shared::layouts.app')

@section('content')
  <main>
    <div class="page-header">
      <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
          <h5 class="m-b-10">Services</h5>
        </div>
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="/dashboard">Home</a></li>
          <li class="breadcrumb-item">Warr Crud</li>
          <li class="breadcrumb-item">Services</li>
        </ul>
      </div>
    </div>
  </main>

  <div class="crm-page-container">
    <div class="row g-3">
      {{-- LEFT: Create/Update --}}
      <div class="col-lg-4">
        <div class="card">
          <div class="card-body">
            <h6 class="mb-3">Add / Update Service</h6>

            <form method="POST" action="{{ route('warr-services.store') }}" id="serviceForm">
              @csrf
              <input type="hidden" name="service_id" id="service_id">

              <div class="mb-3">
                <label class="form-label">Service Name</label>
                <input type="text" name="name" id="service_name" class="form-control" required>
              </div>

              <div class="d-flex gap-2">
                <button class="btn btn-primary" type="submit">Save</button>
                <button class="btn btn-secondary" type="button" id="serviceResetBtn">Reset</button>
              </div>

              <div class="form-text mt-2">
                Slug auto-generates from name and remains unique.
              </div>
            </form>
          </div>
        </div>
      </div>

      {{-- RIGHT: List + Search --}}
      <div class="col-lg-8">
        <div class="card">
          <div class="card-body border-bottom">
            <form method="GET" action="{{ route('warr-services.index') }}" class="col g-2 align-items-end">
              <div class="col-md-10">
                <label class="form-label">Search</label>
                <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Service name...">
              </div>

              <div class="col-md-2 d-flex gap-2" style="margin-top:10px;">
                <button class="btn btn-primary w-100" type="submit">Apply</button>
                <a class="btn btn-light w-100" href="{{ route('warr-services.index') }}">Clear</a>
              </div>
            </form>
          </div>

          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead>
                <tr>
                  <th>Service</th>
                  <th>Slug</th>
                  <th style="width:160px;">Action</th>
                </tr>
              </thead>
              <tbody>
                @forelse($services as $s)
                  <tr>
                    <td>{{ $s->name }}</td>
                    <td>{{ $s->slug }}</td>
                    <td>
                      <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-primary serviceEditBtn" data-id="{{ $s->id }}"
                          data-name="{{ $s->name }}">Edit</button>

                        <form method="POST" action="{{ route('warr-services.destroy', $s->id) }}">
                          @csrf
                          @method('DELETE')
                          <button class="btn btn-sm btn-outline-danger" type="submit"
                            onclick="return confirm('Delete this service?')">Delete</button>
                        </form>
                      </div>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="3">No services found.</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>

          <div class="m-3 d-flex justify-content-center">
            {{ $services->withQueryString()->links('pagination::bootstrap-4') }}
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener('click', function (e) {
      if (e.target.classList.contains('serviceEditBtn')) {
        document.getElementById('service_id').value = e.target.dataset.id || '';
        document.getElementById('service_name').value = e.target.dataset.name || '';
        window.scrollTo({ top: 0, behavior: 'smooth' });
      }

      if (e.target.id === 'serviceResetBtn') {
        document.getElementById('service_id').value = '';
        document.getElementById('service_name').value = '';
      }
    });
  </script>
@endsection