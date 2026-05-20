@extends('layouts.app')

@section('content')
<main>
  <div class="page-header">
    <div class="page-header-left d-flex align-items-center">
      <div class="page-header-title"><h5 class="m-b-10">Countries</h5></div>
      <ul class="breadcrumb">
        <li class="breadcrumb-item"><a href="/dashboard">Home</a></li>
        <li class="breadcrumb-item">Warr Crud</li>
        <li class="breadcrumb-item">Countries</li>
      </ul>
    </div>
  </div>
</main>

<div class="crm-page-container">
  <div class="row g-3">
    <div class="col-lg-4">
      <div class="card">
        <div class="card-body">
          <h6 class="mb-3">Add / Update Country</h6>

          <form method="POST" action="{{ route('warr-countries.store') }}">
            @csrf
            <input type="hidden" name="country_id" id="country_id">

            <div class="mb-3">
              <label class="form-label">Country Name</label>
              <input type="text" name="name" id="country_name" class="form-control" required>
            </div>

            <div class="mb-3">
              <label class="form-label">Code (optional)</label>
              <input type="text" name="code" id="country_code" class="form-control" placeholder="IN, AU, UK...">
            </div>

            <div class="d-flex gap-2">
              <button class="btn btn-primary" type="submit">Save</button>
              <button class="btn btn-secondary" type="button" id="countryResetBtn">Reset</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <div class="col-lg-8">
      <div class="card">
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead>
              <tr>
                <th>Name</th>
                <th>Code</th>
                <th>Slug</th>
                <th style="width:140px;">Action</th>
              </tr>
            </thead>
            <tbody>
              @forelse($countries as $c)
                <tr>
                  <td>{{ $c->name }}</td>
                  <td>{{ $c->code }}</td>
                  <td>{{ $c->slug }}</td>
                  <td>
                    <div class="d-flex gap-2">
                      <button type="button"
                        class="btn btn-sm btn-outline-primary countryEditBtn"
                        data-id="{{ $c->id }}"
                        data-name="{{ $c->name }}"
                        data-code="{{ $c->code }}"
                      >Edit</button>

                      <form method="POST" action="{{ route('warr-countries.destroy', $c->id) }}">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger" type="submit"
                          onclick="return confirm('Delete this country?')">Delete</button>
                      </form>
                    </div>
                  </td>
                </tr>
              @empty
                <tr><td colspan="4">No countries found.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <div class="m-3 d-flex justify-content-center">
          {{ $countries->withQueryString()->links('pagination::bootstrap-4') }}
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  document.addEventListener('click', function(e){
    if(e.target.classList.contains('countryEditBtn')){
      document.getElementById('country_id').value = e.target.dataset.id;
      document.getElementById('country_name').value = e.target.dataset.name || '';
      document.getElementById('country_code').value = e.target.dataset.code || '';
      window.scrollTo({ top: 0, behavior: 'smooth' });
    }
    if(e.target.id === 'countryResetBtn'){
      document.getElementById('country_id').value = '';
      document.getElementById('country_name').value = '';
      document.getElementById('country_code').value = '';
    }
  });
</script>
@endsection
