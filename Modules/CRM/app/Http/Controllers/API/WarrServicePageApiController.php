<?php

namespace Modules\CRM\App\Http\Controllers\API;

use Modules\CRM\App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\CRM\App\Models\WarrServicePage;
use Illuminate\Support\Facades\Schema;

class WarrServicePageApiController extends Controller
{
    public function index(Request $request)
    {
        $query = WarrServicePage::query()
            ->with(['service', 'country', 'city'])
            ->where('status', 'publish')
            ->orderByDesc('id');

        if ($request->filled('country_id')) {
            $query->where('country_id', (int) $request->country_id);
        }

        if ($request->filled('city_id')) {
            $query->where('city_id', (int) $request->city_id);
        }

        if ($request->filled('service_id')) {
            $query->where('service_id', (int) $request->service_id);
        }

        $pages = $query->get();

        return response()->json([
            'success' => true,
            'data' => $pages,
        ]);
    }
public function serviceSlugSitemap(): JsonResponse
{
    $slugs = WarrServicePage::query()
        ->select('warr_service_pages.slug')
        ->join('warr_services', 'warr_services.id', '=', 'warr_service_pages.service_id')
        ->join('warr_countries', 'warr_countries.id', '=', 'warr_service_pages.country_id')
        ->leftJoin('warr_cities', 'warr_cities.id', '=', 'warr_service_pages.city_id')
        ->where('warr_service_pages.status', 'publish')
        ->orderBy('warr_services.name')          // 1) service name A-Z
        ->orderBy('warr_countries.name')         // 2) country name A-Z
        ->orderBy('warr_service_pages.slug')     // 3) page slug A-Z
        ->orderBy('warr_cities.name')            // 4) city name A-Z (if null, comes last)
        ->pluck('warr_service_pages.slug');

    return response()->json([
        'success' => true,
        'count'   => $slugs->count(),
        'data'    => $slugs,
    ]);
}

    public function showBySlug(string $slug)
    {
        $page = WarrServicePage::query()
            ->with([
                    'service:id,name,slug',
                    'country:id,name,code',
                    'city:id,name,country_id',
                    'moreServices:id,name,slug',
                ])
            ->where('slug', $slug)
            ->where('status', 'publish')
            ->first();

        if (!$page) {
            return response()->json([
                'success' => false,
                'message' => 'Service page not found',
            ], 404);
        }

        // Location priority:
        $locationColumn = $page->city_id ? 'city_id' : 'country_id';
        $locationValue = $page->city_id ?: $page->country_id;

        // Selected services from pivot (can be empty)
        $selectedServiceIds = $page->moreServices->pluck('id')->values()->all();

        // Base query: same location + published + not current page
        $moreQuery = WarrServicePage::query()
            ->with(['service:id,name,slug'])
            ->where('status', 'publish')
            ->where($locationColumn, $locationValue)
            ->where('id', '!=', $page->id);

        // ✅ If pivot is filled → filter by selected services
// ✅ If pivot empty → show ALL services pages for that location
        if (!empty($selectedServiceIds)) {
            $moreQuery->whereIn('service_id', $selectedServiceIds);
        }

        // Fetch
        $moreServicePages = $moreQuery
            ->orderBy('service_id')
            ->get([
                    'id',
                    'service_id',
                    'country_id',
                    'city_id',
                    'hero_title',
                    'hero_description',
                    'slug',
                ]);

        return response()->json([
            'success' => true,
            'data' => [
                'page' => [
                    'id' => $page->id,
                    'slug' => $page->slug,
                    'status' => $page->status,

                    'service' => $page->service,
                    'country' => $page->country,
                    'city' => $page->city,

                    'hero_title' => $page->hero_title,
                    'hero_description' => $page->hero_description,

                    'section1' => [
                        'title' => $page->section1_title,
                        'description' => $page->section1_description,
                        'points' => $page->section1_points ?? [],
                    ],
                    'section2' => [
                        'title' => $page->section2_title,
                        'description' => $page->section2_description,
                        'points' => $page->section2_points ?? [],
                    ],
                    'section3' => [
                        'title' => $page->section3_title,
                        'description' => $page->section3_description,
                        'points' => $page->section3_points ?? [],
                    ],
                    'section4' => [
                        'title' => $page->section4_title,
                        'description' => $page->section4_description,
                        'points' => $page->section4_points ?? [],
                    ],

                    'faq' => $page->faq ?? [],

                    'seo' => [
                        'meta_title' => $page->meta_title,
                        'meta_description' => $page->meta_description,
                    ],
                ],

                // these are the actual pages to show in "More Services"
                'more_service_pages' => $moreServicePages,

                // helpful for Next.js UI heading like:
                // "More Services in Melbourne" or "More Services in Australia"
                'location_context' => [
                    'mode' => $page->city_id ? 'city' : 'country',
                    'city_id' => $page->city_id,
                    'country_id' => $page->country_id,
                ],
            ],
        ]);
    }
}


