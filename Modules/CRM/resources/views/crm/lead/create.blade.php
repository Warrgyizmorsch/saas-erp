@extends('layouts.app')

@section('content')
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Leads</h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                <li class="breadcrumb-item active">{{ $lead->exists ? 'Edit' : 'Create' }}</li>
            </ul>
        </div>
        <div class="page-header-right ms-auto">
            <div class="page-header-right-items">
                <div class="d-flex d-md-none">
                    <a href="javascript:void(0)" class="page-header-right-close-toggle">
                        <i class="feather-arrow-left me-2"></i>
                        <span>Back</span>
                    </a>
                </div>
                <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">                    
                    <a href="{{ route('lead.create') }}" class="btn btn-icon btn-light-brand bg-primary text-white">
                        <i class="feather-plus"></i> New Lead
                    </a>
                </div>
            </div>
            <div class="d-md-none d-flex align-items-center">
                <a href="javascript:void(0)" class="page-header-right-open-toggle">
                    <i class="feather-align-right fs-20"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- [ Main Content ] start -->
    <div class="main-content">
        <div class="row">
            <div class="col-lg-12">
                <div class="card stretch stretch-full">

                    <!-- Laravel Form -->
                    <form id="leadForm" method="POST"
                        action="{{ $lead->exists ? route('lead.update', $lead->id) : route('lead.store') }}">
                        @csrf
                        @if($lead->exists)
                            @method('PUT')
                        @endif

                        <div class="card-body lead-status">
                            <div class="mb-5 d-flex align-items-center justify-content-between">
                                <h5 class="fw-bold mb-0 me-4">
                                    <span class="d-block mb-2">Student Details :</span>
                                </h5>
                            </div>

                            <div class="row g-3">

                                <!-- Mobile No. -->
                                <div class="col-lg-4">
                                    <label class="form-label">Mobile</label>
                                    <div class="input-group">
                                        <input id="phone" type="tel" name="mobile" class="form-control"
                                            value="{{ old('mobile', $lead->user->contact_no ?? '') }}"
                                            placeholder="Mobile Number">

                                        <!-- Hidden input to save country_code -->
                                        <input type="hidden" name="country_code" id="country_code"
                                            value="{{ old('country_code', $lead->user->country_code ?? '') }}">
                                    </div>
                                    @error('mobile') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                <!-- Name -->
                                <div class="col-lg-4">
                                    <label class="form-label">Name</label>
                                    <div class="input-group">
                                        <div class="input-group-text"><i class="feather-user"></i></div>
                                        <input type="text" name="name"
                                            value="{{ old('name', $lead->user->name ?? '') }}" class="form-control"
                                            placeholder="Name">
                                    </div>
                                    @error('name') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                <!-- Email -->
                                <div class="col-lg-4">
                                    <label class="form-label">Email</label>
                                    <div class="input-group">
                                        <div class="input-group-text"><i class="feather-mail"></i></div>
                                        <input type="email" name="email"
                                            value="{{ old('email', $lead->user->email ?? '') }}" class="form-control"
                                            placeholder="Email">
                                    </div>
                                    @error('email') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>


                                <!-- City -->
                                <div class="col-lg-4">
                                    <label class="form-label">City</label>
                                    <div class="input-group">
                                        <div class="input-group-text"><i class="feather-map-pin"></i></div>
                                        <input type="text" name="city"
                                            value="{{ old('city', $lead->user->city ?? '') }}" class="form-control"
                                            placeholder="City">
                                    </div>
                                    @error('city') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                            </div>

                        </div>
                        <div class="card-body lead-status">
                            <div class="mb-5 d-flex align-items-center justify-content-between">
                                <h5 class="fw-bold mb-0 me-4">
                                    <span class="d-block mb-2">Lead Details :</span>

                                </h5>
                            </div>
                            <div class="row">

                                <!-- Lead Source -->
                                <div class="col-lg-4 mb-4">
                                    <label class="form-label">Lead Source</label>
                                    <select name="platform" id="platform" class="form-control" data-select2-selector="tag">
                                        <option value="">Select Lead Source</option>

                                        @php
// Collect all options (sources + current lead platform if not already in sources)
$allSources = $sources;
if ($lead->platform && !in_array($lead->platform, $sources)) {
    $allSources[] = $lead->platform;
}
                                        @endphp

                                        @foreach($allSources as $source)
                                            <option value="{{ $source }}" {{ old('platform', $lead->platform) == $source ? 'selected' : '' }}>
                                                {{ $source }}
                                            </option>
                                        @endforeach
                                    </select>

                                    @error('platform')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <!-- Campaign Name -->
                                <div class="col-lg-4 mb-4">
                                    <label class="form-label">Campaign Name</label>
                                    <div class="input-group">
                                        <div class="input-group-text"><i class="feather-map-pin"></i></div>
                                        <input type="text" name="campaign_name"
                                            value="{{ old('campaign_name', $lead->campaign_name) }}"
                                            class="form-control" placeholder="Campaign Name">
                                    </div>
                                    @error('campaign_name') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                <!-- New: Adset Name -->
                                <div class="col-lg-4 mb-4">
                                    <label class="form-label">Adset Name</label>
                                    <div class="input-group">
                                        <div class="input-group-text"><i class="feather-grid"></i></div>
                                        <input type="text" name="adset_name"
                                            value="{{ old('adset_name', $lead->adset_name) }}" class="form-control"
                                            placeholder="Adset Name">
                                    </div>
                                    @error('adset_name') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                <!-- New: Ad Name -->
                                <div class="col-lg-4 mb-4">
                                    <label class="form-label">Ad Name</label>
                                    <div class="input-group">
                                        <div class="input-group-text"><i class="feather-monitor"></i></div>
                                        <input type="text" name="ad_name" value="{{ old('ad_name', $lead->ad_name) }}"
                                            class="form-control" placeholder="Ad Name">
                                    </div>
                                    @error('ad_name') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                <!-- New: Form Name -->
                                <div class="col-lg-4 mb-4">
                                    <label class="form-label">Form Name</label>
                                    <div class="input-group">
                                        <div class="input-group-text"><i class="feather-file-text"></i></div>
                                        <input type="text" name="form_name"
                                            value="{{ old('form_name', $lead->form_name) }}" class="form-control"
                                            placeholder="Form Name">
                                    </div>
                                    @error('form_name') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                <!-- Lead Owner -->
                                <div class="col-lg-4 mb-4">
                                    <label class="form-label">Lead Owner</label>
                                    <select name="lead_owner" class="form-control" data-select2-selector="tag">
                                        <option value="">Select Owner</option>
                                        @foreach($owners as $owner)
                                            <option value="{{ $owner->id }}" {{ old('lead_owner', $lead->lead_owner) == $owner->id ? 'selected' : '' }}>
                                                {{ $owner->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('lead_owner')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <!-- Budget -->
                                <div class="col-lg-4 mb-4">
                                    <label class="form-label">Budget</label>
                                    <div class="input-group">
                                        <div class="input-group-text"><i class="feather-dollar-sign"></i></div>
                                        <input type="text" name="budget" value="{{ old('budget', $lead->budget) }}"
                                            class="form-control" placeholder="Budget">
                                    </div>
                                    @error('budget') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                <!-- Applying Country -->
                                <div class="col-lg-4 mb-4">
                                    <label class="form-label">Applying Country</label>
                                    <div class="input-group">
                                        <div class="input-group-text"><i class="feather-globe"></i></div>
                                        <input type="text" name="applying_country_for_a_visa"
                                            value="{{ old('applying_country_for_a_visa', $lead->applying_country_for_a_visa ?? '') }}"
                                            class="form-control" placeholder="Country">
                                    </div>
                                    @error('applying_country_for_a_visa') <small
                                    class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                <!-- Planned Course -->
                                <div class="col-lg-4 mb-4">
                                    <label class="form-label">Planned Course</label>
                                    <div class="input-group">
                                        <div class="input-group-text"><i class="feather-book"></i></div>
                                        <input type="text" name="what_course_are_you_planning_to_study"
                                            value="{{ old('what_course_are_you_planning_to_study', $lead->what_course_are_you_planning_to_study ?? '') }}"
                                            class="form-control" placeholder="Course">
                                    </div>
                                    @error('what_course_are_you_planning_to_study') <small
                                    class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                <!-- Academic Gap -->
                                <div class="col-lg-4 mb-4">
                                    <label class="form-label">Academic Gap</label>
                                    <div class="input-group">
                                        <div class="input-group-text"><i class="feather-info"></i></div>
                                        <input type="text" name="any_academic_gap"
                                            value="{{ old('any_academic_gap', $lead->any_academic_gap ?? '') }}"
                                            class="form-control" placeholder="Academic Gap">
                                    </div>
                                    @error('any_academic_gap') <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <!-- Highest Completed -->
                                <div class="col-lg-4 mb-4">
                                    <label class="form-label">Highest Completed</label>
                                    <div class="input-group">
                                        <div class="input-group-text"><i class="feather-award"></i></div>
                                        <input type="text" name="highest_completed"
                                            value="{{ old('highest_completed', $lead->highest_completed) }}"
                                            class="form-control" placeholder="Highest Completed">
                                    </div>
                                    @error('highest_completed') <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <!-- Preffered Intake -->
                                <div class="col-lg-4 mb-4">
                                    <label class="form-label">Preferred Intake</label>
                                    <div class="input-group">
                                        <div class="input-group-text"><i class="feather-calendar"></i></div>
                                        <input type="text" name="whats_your_preferred_intake"
                                            value="{{ old('whats_your_preferred_intake', $lead->whats_your_preferred_intake) }}"
                                            class="form-control" placeholder="Preferred Intake">
                                    </div>
                                    @error('whats_your_preferred_intake') <small
                                        class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="col-lg-12 mb-4">
                                    <label class="form-label">Description</label>
                                    <textarea name="description" class="form-control" rows="4"
                                        placeholder="Add a description about the lead">{{ old('description', $lead->description) }}</textarea>
                                    @error('description') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                @if($lead->exists)
                                    <div class="card-body lead-status mt-4">
                                        <h5 class="fw-bold mb-3">Additional Lead Attributes :</h5>
                                        <div class="row g-3">

                                            @forelse($mergedAttributes as $attr)
                                                <div class="col-lg-4 mb-4">
                                                    <label class="form-label">{{ ucwords(str_replace('_', ' ', $attr->field_name)) }}</label>
                                                    <input type="text" name="attributes[{{ $attr->id }}]" 
                                                        value="{{ old('attributes.' . $attr->id, $attr->field_value) }}" 
                                                        class="form-control" placeholder="{{ ucwords(str_replace('_', ' ', $attr->field_name)) }}">
                                                </div>
                                            @empty
                                                <div class="col-12 text-muted">No additional attributes found.</div>
                                            @endforelse

                                        </div>
                                    </div>
                                @endif

                            </div>
                        </div>

                        <!-- SUBMIT BUTTON -->
                        <div class="m-4" style="display: flex; justify-content: end;">
                            <button type="submit" class="btn btn-primary">
                                {{ $lead->exists ? 'Update Lead' : 'Create Lead' }}
                            </button>
                        </div>

                    </form>
                    <!-- End Form -->

                </div>
            </div>
        </div>
    </div>
    <!-- [ Main Content ] end -->

    @push('scripts')
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                var input = document.querySelector("#phone");

                if (!input) return;

                var iti = window.intlTelInput(input, {
                    initialCountry: "in",
                    separateDialCode: true,
                    preferredCountries: ["in", "us", "gb"],
                    utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.13/js/utils.js"
                });

                @if(isset($lead) && isset($lead->user->country_code))
                    var savedDialCode = "{{ str_replace('+', '', $lead->user->country_code) }}";
                    var country = window.intlTelInputGlobals.getCountryData()
                        .find(c => c.dialCode === savedDialCode);
                    if (country) {
                        iti.setCountry(country.iso2);
                    }
                @endif

                function updateCountryCode() {
                    var countryData = iti.getSelectedCountryData();
                    document.querySelector("#country_code").value = "+" + countryData.dialCode;
                }

                updateCountryCode();
                input.addEventListener("countrychange", updateCountryCode);
                input.form && input.form.addEventListener("submit", updateCountryCode);
            });
        </script>
    @endpush

    <!-- Auto Assign User if Exists  -->
    @push('scripts')
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                const phoneInput = document.querySelector("#phone");

                phoneInput.addEventListener("blur", function () {
                    let mobile = phoneInput.value.trim();
                    if (!mobile) return;

                    fetch("{{ route('user.search.byMobile') }}?mobile=" + encodeURIComponent(mobile))
                        .then(res => res.json())
                        .then(data => {
                            if (data.exists) {
                                // Autofill fields
                                document.querySelector("input[name='name']").value = data.user.name;
                                document.querySelector("input[name='email']").value = data.user.email;
                                document.querySelector("input[name='city']").value = data.user.city;

                                // Make them readonly
                                document.querySelector("input[name='name']").readOnly = true;
                                document.querySelector("input[name='email']").readOnly = true;
                                document.querySelector("input[name='city']").readOnly = true;
                            } else {
                                // Reset + make editable
                                document.querySelector("input[name='name']").value = "";
                                document.querySelector("input[name='email']").value = "";
                                document.querySelector("input[name='city']").value = "";

                                document.querySelector("input[name='name']").readOnly = false;
                                document.querySelector("input[name='email']").readOnly = false;
                                document.querySelector("input[name='city']").readOnly = false;
                            }
                        });
                });
            });
        </script>
    @endpush


    <style>
        .iti__country-list {
            width: 250px !important;
            max-height: 250px;
            overflow-y: auto;
        }
    </style>

@endsection