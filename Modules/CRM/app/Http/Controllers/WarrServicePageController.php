<?php

namespace Modules\CRM\App\Http\Controllers;

use Modules\CRM\App\Http\Controllers\Controller;
use Modules\CRM\App\Models\WarrServicePage;
use Modules\CRM\App\Models\WarrService;
use Modules\CRM\App\Models\WarrCountry;
use Modules\CRM\App\Models\WarrCity;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WarrServicePageController extends Controller
{
    public function index(Request $request)
    {
        $query = WarrServicePage::with(['service', 'country', 'city'])
            ->orderByDesc('id');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('service_id')) {
            $query->where('service_id', $request->service_id);
        }

        if ($request->filled('country_id')) {
            $query->where('country_id', $request->country_id);
        }

        $data['pages'] = $query->paginate(10);

        $services = WarrService::orderBy('name')->get();
        $countries = WarrCountry::orderBy('name')->get();

        return view('crm.warr-service-pages.index', compact('data', 'services', 'countries'));
    }

    public function create(Request $request)
    {
        $services = WarrService::orderBy('name')->get();
        $countries = WarrCountry::orderBy('name')->get();

        $clone = null;
        $cities = [];
        $selectedMoreServices = [];
        $faqData = [];

        if ($request->filled('clone_id')) {
            $clone = WarrServicePage::with(['moreServices'])->findOrFail((int) $request->clone_id);

            // load cities for clone country so dropdown can preselect
            if (!empty($clone->country_id)) {
                $cities = WarrCity::where('country_id', $clone->country_id)->orderBy('name')->get();
            }

            $selectedMoreServices = $clone->moreServices->pluck('id')->values()->all();
            $faqData = is_array($clone->faq) ? $clone->faq : [];

            // ensure points arrays for JS
            $clone->section1_points = is_array($clone->section1_points) ? $clone->section1_points : [];
            $clone->section2_points = is_array($clone->section2_points) ? $clone->section2_points : [];
            $clone->section3_points = is_array($clone->section3_points) ? $clone->section3_points : [];
            $clone->section4_points = is_array($clone->section4_points) ? $clone->section4_points : [];
        }

        return view('crm.warr-service-pages.create', compact(
            'services',
            'countries',
            'cities',
            'clone',
            'selectedMoreServices',
            'faqData'
        ));
    }


    public function store(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:warr_services,id',
            'hero_title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'status' => 'nullable|in:draft,publish',
        ]);

        // slug generation (same logic as blog)
        $slugSource = $request->filled('slug')
            ? $request->slug
            : $request->hero_title;

        $baseSlug = Str::slug($slugSource);
        $slug = $baseSlug;
        $i = 1;

        while (WarrServicePage::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $i++;
        }

        $page = WarrServicePage::create([
            'service_id' => $request->service_id,
            'country_id' => $request->country_id,
            'city_id' => $request->city_id,

            'hero_title' => $request->hero_title,
            'hero_description' => $request->hero_description,

            'section1_title' => $request->section1_title,
            'section1_description' => $request->section1_description,
            'section1_points' => json_decode($request->section1_points, true),

            'section2_title' => $request->section2_title,
            'section2_description' => $request->section2_description,
            'section2_points' => json_decode($request->section2_points, true),

            'section3_title' => $request->section3_title,
            'section3_description' => $request->section3_description,
            'section3_points' => json_decode($request->section3_points, true),

            'section4_title' => $request->section4_title,
            'section4_description' => $request->section4_description,
            'section4_points' => json_decode($request->section4_points, true),

            'faq' => json_decode($request->faq_data, true),

            'meta_title' => $request->meta_title,
            'meta_description' => $request->meta_description,

            'slug' => $slug,
            'status' => 'draft',
        ]);

        // Sync "More Services"
        if ($request->filled('more_services')) {
            $page->moreServices()->sync($request->more_services);
        }

        return redirect()
            ->route('warr-service-pages.index')
            ->with('success', 'Service page created successfully');
    }

    public function edit($id)
    {
        $page = WarrServicePage::with(['moreServices', 'service', 'country', 'city'])->findOrFail($id);

        $services = WarrService::orderBy('name')->get();
        $countries = WarrCountry::orderBy('name')->get();

        // for edit: load cities for selected country
        $cities = [];
        if (!empty($page->country_id)) {
            $cities = WarrCity::where('country_id', $page->country_id)->orderBy('name')->get();
        }

        // ensure arrays
        $faqData = is_array($page->faq) ? $page->faq : [];

        return view('crm.warr-service-pages.edit', compact('page', 'services', 'countries', 'cities', 'faqData'));
    }

    public function update(Request $request, $id)
    {
        $page = WarrServicePage::findOrFail($id);

        $request->validate([
            'service_id' => 'required|exists:warr_services,id',
            'hero_title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'status' => 'required|in:draft,publish',

            'country_id' => 'nullable|exists:warr_countries,id',
            'city_id' => 'nullable|exists:warr_cities,id',

            'more_services' => 'nullable|array',
            'more_services.*' => 'exists:warr_services,id',
        ]);

        // slug regeneration unique (ignore current id)
        $slugSource = $request->filled('slug') ? $request->slug : $request->hero_title;
        $baseSlug = \Illuminate\Support\Str::slug($slugSource);
        $slug = $baseSlug;
        $i = 1;

        while (
            WarrServicePage::where('slug', $slug)
                ->where('id', '!=', $page->id)
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $i++;
        }

        $page->update([
            'service_id' => $request->service_id,
            'country_id' => $request->country_id,
            'city_id' => $request->city_id,

            'hero_title' => $request->hero_title,
            'hero_description' => $request->hero_description,

            'section1_title' => $request->section1_title,
            'section1_description' => $request->section1_description,
            'section1_points' => $request->filled('section1_points') ? json_decode($request->section1_points, true) : null,

            'section2_title' => $request->section2_title,
            'section2_description' => $request->section2_description,
            'section2_points' => $request->filled('section2_points') ? json_decode($request->section2_points, true) : null,

            'section3_title' => $request->section3_title,
            'section3_description' => $request->section3_description,
            'section3_points' => $request->filled('section3_points') ? json_decode($request->section3_points, true) : null,

            'section4_title' => $request->section4_title,
            'section4_description' => $request->section4_description,
            'section4_points' => $request->filled('section4_points') ? json_decode($request->section4_points, true) : null,

            'faq' => $request->filled('faq_data') ? json_decode($request->faq_data, true) : null,

            'meta_title' => $request->meta_title,
            'meta_description' => $request->meta_description,

            'slug' => $slug,
            'status' => $request->status,
        ]);

        $page->moreServices()->sync($request->input('more_services', []));

        return redirect()->back()->with('success', 'Service page updated successfully');
    }

    public function destroy($id)
    {
        $page = WarrServicePage::findOrFail($id);
        $page->delete();
        return redirect()->back()->with('success', 'Service page deleted successfully');
    }

    // AJAX endpoint for Country -> Cities dropdown
    public function getCities(Request $request)
    {
        $request->validate([
            'country_id' => 'required|exists:warr_countries,id'
        ]);

        $cities = WarrCity::where('country_id', $request->country_id)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($cities);
    }

    public function countriesIndex(Request $request)
    {
        $countries = WarrCountry::orderBy('name')->paginate(20);
        return view('crm.warr-crud.countries', compact('countries'));
    }

    public function countriesStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:120',
            'code' => 'nullable|string|max:10',
            'country_id' => 'nullable|exists:warr_countries,id',
        ]);

        $country = $request->filled('country_id')
            ? WarrCountry::findOrFail($request->country_id)
            : new WarrCountry();

        $country->name = $request->name;
        $country->code = $request->code ? strtoupper(trim($request->code)) : null;

        $country->save();

        return redirect()->back()->with('success', $request->filled('country_id') ? 'Country updated!' : 'Country created!');
    }

    public function countriesDestroy($id)
    {
        $country = WarrCountry::findOrFail($id);
        $country->delete();
        return redirect()->back()->with('success', 'Country deleted!');
    }

    /* -------------------- CITIES -------------------- */

    public function citiesIndex(Request $request)
    {
        $query = WarrCity::with('country')->orderByDesc('id');

        if ($request->filled('country_id')) {
            $query->where('country_id', $request->country_id);
        }

        if ($request->filled('q')) {
            $query->where('name', 'like', '%' . $request->q . '%');
        }

        $cities = $query->paginate(20);
        $countries = WarrCountry::orderBy('name')->get();

        return view('crm.warr-crud.cities', compact('cities', 'countries'));
    }

    public function citiesStore(Request $request)
    {
        $request->validate([
            'country_id' => 'required|exists:warr_countries,id',
            'name' => 'required|string|max:120',
            'city_id' => 'nullable|exists:warr_cities,id',
        ]);

        // Create or Update
        $city = $request->filled('city_id')
            ? WarrCity::findOrFail($request->city_id)
            : new WarrCity();

        $city->country_id = $request->country_id;
        $city->name = $request->name;

        $city->save();

        return redirect()
            ->back()
            ->with(
                'success',
                $request->filled('city_id') ? 'City updated successfully!' : 'City created successfully!'
            );
    }

    public function citiesDestroy($id)
    {
        $city = WarrCity::findOrFail($id);
        $city->delete();
        return redirect()->back()->with('success', 'City deleted!');
    }

    /* -------------------- SERVICES -------------------- */

    public function servicesIndex(Request $request)
    {
        $query = WarrService::orderByDesc('id');

        if ($request->filled('q')) {
            $query->where('name', 'like', '%' . $request->q . '%');
        }

        $services = $query->paginate(20);

        return view('crm.warr-crud.services', compact('services'));
    }

    public function servicesStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:140',
            'service_id' => 'nullable|exists:warr_services,id',
        ]);

        $service = $request->filled('service_id')
            ? WarrService::findOrFail($request->service_id)
            : new WarrService();

        $service->name = $request->name;

        // slug
        $base = Str::slug($request->name);
        $slug = $base;
        $i = 1;
        while (WarrService::where('slug', $slug)->where('id', '!=', $service->id)->exists()) {
            $slug = $base . '-' . $i++;
        }
        $service->slug = $slug;

        $service->save();

        return redirect()->back()->with('success', $request->filled('service_id') ? 'Service updated!' : 'Service created!');
    }

    public function servicesDestroy($id)
    {
        $service = WarrService::findOrFail($id);
        $service->delete();
        return redirect()->back()->with('success', 'Service deleted!');
    }
}


