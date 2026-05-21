@extends('shared::layouts.app')

@section('content')
  <main>
    <div class="page-header">
      <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
          <h5 class="m-b-10">Cities</h5>
        </div>
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="/dashboard">Home</a></li>
          <li class="breadcrumb-item">Warr Crud</li>
          <li class="breadcrumb-item">Cities</li>
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
            <h6 class="mb-3">Add / Update City</h6>

            <form method="POST" action="{{ route('warr-cities.store') }}" id="cityForm">
              @csrf
              <input type="hidden" name="city_id" id="city_id">

              <div class="mb-3">
                <label class="form-label">Country</label>
                <select name="country_id" id="city_country_id" class="form-select" required>
                  <option value="">Select Country</option>
                  @foreach($countries as $c)
                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                  @endforeach
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label">City Name</label>
                <input type="text" name="name" id="city_name" class="form-control" required>
              </div>

              <div class="d-flex gap-2">
                <button class="btn btn-primary" type="submit">Save</button>
                <button class="btn btn-secondary" type="button" id="cityResetBtn">Reset</button>
              </div>
            </form>
          </div>
        </div>
      </div>

      {{-- RIGHT: List + Filters --}}
      <div class="col-lg-8">
        <div class="card">
          <div class="card-body border-bottom">
            <form method="GET" action="{{ route('warr-cities.index') }}" class="col g-2 align-items-end">
              <div class="row">
                <div class="col-md-5">
                  <label class="form-label">Filter by Country</label>
                  <select name="country_id" class="form-select">
                    <option value="">All Countries</option>
                    @foreach($countries as $c)
                      <option value="{{ $c->id }}" {{ (string) request('country_id') === (string) $c->id ? 'selected' : '' }}>
                        {{ $c->name }}
                      </option>
                    @endforeach
                  </select>
                </div>

                <div class="col-md-5">
                  <label class="form-label">Search</label>
                  <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="City name...">
                </div>
              </div>

              <div class="col-md-2 d-flex gap-2" style="margin-top: 10px;">
                <button class="btn btn-primary w-100" type="submit">Apply</button>
                <a class="btn btn-light w-100" href="{{ route('warr-cities.index') }}">Clear</a>
              </div>
            </form>
          </div>

          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead>
                <tr>
                  <th>City</th>
                  <th>Country</th>
                  <th>Slug</th>
                  <th style="width:160px;">Action</th>
                </tr>
              </thead>
              <tbody>
                @forelse($cities as $ct)
                  <tr>
                    <td>{{ $ct->name }}</td>
                    <td>{{ $ct->country?->name }}</td>
                    <td>{{ $ct->slug }}</td>
                    <td>
                      <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-primary cityEditBtn" data-id="{{ $ct->id }}"
                          data-name="{{ $ct->name }}" data-country="{{ $ct->country_id }}">Edit</button>

                        <form method="POST" action="{{ route('warr-cities.destroy', $ct->id) }}">
                          @csrf
                          @method('DELETE')
                          <button class="btn btn-sm btn-outline-danger" type="submit"
                            onclick="return confirm('Delete this city?')">Delete</button>
                        </form>
                      </div>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="4">No cities found.</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>

          <div class="m-3 d-flex justify-content-center">
            {{ $cities->withQueryString()->links('pagination::bootstrap-4') }}
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener('click', function (e) {
      if (e.target.classList.contains('cityEditBtn')) {
        document.getElementById('city_id').value = e.target.dataset.id || '';
        document.getElementById('city_name').value = e.target.dataset.name || '';
        document.getElementById('city_country_id').value = e.target.dataset.country || '';
        window.scrollTo({ top: 0, behavior: 'smooth' });
      }

      if (e.target.id === 'cityResetBtn') {
        document.getElementById('city_id').value = '';
        document.getElementById('city_name').value = '';
        document.getElementById('city_country_id').value = '';
      }
    });
  </script>
@endsection