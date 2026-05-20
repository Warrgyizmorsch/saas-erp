<?php

namespace Modules\CRM\App\Http\Controllers;

use Modules\CRM\App\Http\Controllers\Controller;
use Modules\CRM\App\Models\University;
use Modules\CRM\App\Models\UniversityDetail;
use Modules\CRM\App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UniversityDetailController extends Controller
{
    /**
     * Display a listing of universities for detail management
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $universities = University::with('details')
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('country', 'like', '%' . $search . '%');
            })
            ->orderBy('name')
            ->paginate(15);

        return view('crm.universities.index', compact('universities'));
    }

    /**
     * Show the form for creating/editing university details
     */

    public function create()
    {
        $university = new University();
        $detail = new UniversityDetail([
            'currency_code' => 'GBP',
            'status' => 'draft'
        ]);

        $minFee = null;

        return view('crm.universities.edit', compact('university', 'detail', 'minFee'));
    }

    public function storeNew(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:250',
            'slug' => 'nullable|string|max:255|unique:university,slug',
            'country' => 'nullable|string|max:100',
            'city' => 'nullable|string',
            'description' => 'nullable|string',

            // details
            'overview' => 'nullable|string',
            'ranking_info' => 'nullable|string',
            'status' => 'nullable|in:draft,published',
        ]);

        // ✅ Create University
        $university = University::create([
            'name' => $validated['name'],
            'slug' => $validated['slug'] ?? null,
            'country' => $validated['country'] ?? null,
            'city' => $validated['city'] ?? null,
            'description' => $validated['description'] ?? null,
        ]);

        // ✅ Create Details
        UniversityDetail::create([
            'university_id' => $university->id,
            'overview' => $validated['overview'] ?? null,
            'ranking_info' => $validated['ranking_info'] ?? null,
            'status' => $validated['status'] ?? 'draft',
            'currency_code' => 'GBP'
        ]);

        return redirect()
            ->route('university-details.edit', $university->id)
            ->with('success', 'University created successfully!');
    }
    public function edit($universityId)
    {
        $university = University::with(['details', 'courses'])->findOrFail($universityId);

        $detail = $university->details;

        // If no details exist → create empty object
        if (!$detail) {
            $detail = new UniversityDetail([
                'university_id' => $universityId,
                'currency_code' => 'GBP',
                'status' => 'draft'
            ]);
        }

        // 🔥 Auto-calc default tuition fee (important)
        $minFee = $university->courses->min('tuition_fee');

        return view('crm.universities.edit', compact('university', 'detail', 'minFee'));
    }

    /**
     * Store or update university details
     */
    public function store(Request $request, $universityId)
    {
        $university = University::findOrFail($universityId);

        $validated = $request->validate([
            'overview' => 'nullable|string',
            'ranking_info' => 'nullable|string',
            'global_ranking' => 'nullable|string|max:100',
            'country_ranking' => 'nullable|string|max:100',
            'batch_strength' => 'nullable|integer',
            'global_diversity' => 'nullable|string|max:50',
            'cost_of_living' => 'nullable|numeric|min:0',
            'tuition_fee_from' => 'nullable|numeric|min:0',
            'currency_code' => 'nullable|string|max:3',
            'admission_requirements' => 'nullable|string',
            'entry_requirements_url' => 'nullable|url',
            'scholarship_info' => 'nullable|string',
            'scholarship_url' => 'nullable|url',
            'finances_info' => 'nullable|string',
            'finances_url' => 'nullable|url',
            'accommodation_info' => 'nullable|string',
            'accommodation_url' => 'nullable|url',
            'faq_content' => 'nullable|string',
            'banner_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'thumbnail_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'status' => 'nullable|in:draft,published',
            'slug' => 'nullable|string|max:255|unique:university,slug,' . $universityId,
        ]);

        $validated['currency_code'] = $validated['currency_code'] ?? 'GBP';
        $validated['status'] = $validated['status'] ?? 'draft';

        $university->update([
            'name' => $request->name ?? $university->name,
            'slug' => $request->slug ?? $university->slug,
            'country' => $request->country ?? $university->country,
            'city' => $request->city ?? $university->city,
            'description' => $request->description ?? $university->description,
        ]);

        $detail = UniversityDetail::updateOrCreate(
            ['university_id' => $universityId],
            $validated
        );
        
        // Handle banner image upload
        if ($request->hasFile('banner_image')) {
            if ($detail->banner_image && Storage::disk('public')->exists($detail->banner_image)) {
                Storage::disk('public')->delete($detail->banner_image);
            }

            $detail->banner_image = $request->file('banner_image')->store('universities/banners', 'public');
        }

        if ($request->hasFile('thumbnail_image')) {
            if ($detail->thumbnail_image && Storage::disk('public')->exists($detail->thumbnail_image)) {
                Storage::disk('public')->delete($detail->thumbnail_image);
            }

            $detail->thumbnail_image = $request->file('thumbnail_image')->store('universities/thumbnails', 'public');
        }

        $detail->save();

        return redirect()
            ->route('university-details.index')
            ->with('success', 'University details saved successfully!');
    }

    /**
     * Quick update status
     */
    public function updateStatus(Request $request, $universityId)
    {
        $detail = UniversityDetail::where('university_id', $universityId)->firstOrFail();

        $request->validate([
            'status' => 'required|in:draft,published',
        ]);

        $detail->update(['status' => $request->status]);

        return response()->json(['success' => true, 'message' => 'Status updated successfully!']);
    }

    /**
     * Delete university detail
     */
    public function destroy($universityId)
    {
        $university = University::with(['details', 'courses'])->findOrFail($universityId);

        // ✅ Delete detail images
        if ($university->details) {
            if ($university->details->banner_image && Storage::disk('public')->exists($university->details->banner_image)) {
                Storage::disk('public')->delete($university->details->banner_image);
            }

            if ($university->details->thumbnail_image && Storage::disk('public')->exists($university->details->thumbnail_image)) {
                Storage::disk('public')->delete($university->details->thumbnail_image);
            }

            $university->details->delete();
        }

        // ✅ Delete courses
        if ($university->courses && $university->courses->count()) {
            foreach ($university->courses as $course) {
                $course->delete();
            }
        }

        // ✅ Delete university itself
        $university->delete();

        return redirect()
            ->route('university-details.index')
            ->with('success', 'University deleted successfully!');
    }

    /**
     * Preview university detail page
     */
    public function preview($universityId)
    {
        $university = University::with('details')->findOrFail($universityId);
        $detail = $university->details;

        if (!$detail || $detail->status !== 'published') {
            return redirect()->route('university-details.index')->with('error', 'University detail not found or not published.');
        }

        return view('crm.universities.preview', compact('university', 'detail'));
    }

    public function addCourse(Request $request)
    {
        $request->validate([
            'university_id' => 'required|exists:university,id',
            'course_name' => 'required',
            'duration' => 'required',
            'tuition_fee' => 'required|numeric',
            'course_type' => 'nullable|string',
            'location' => 'nullable|string',
            'application_fee' => 'nullable|numeric',
            'currency_code' => 'nullable|string',
            'currency_symbol' => 'nullable|string',
        ]);

        try {
            $course = Course::create([
                'university_id' => $request->university_id,
                'course_name' => $request->course_name,
                'duration' => $request->duration,
                'course_type' => $request->course_type,
                'tuition_fee' => $request->tuition_fee,
                'location' => $request->location,
                'application_fee' => $request->application_fee,
                'currency_code' => $request->currency_code ?? 'USD',
                'currency_symbol' => $request->currency_symbol ?? '$'
            ]);

            $minFee = Course::where('university_id', $request->university_id)->min('tuition_fee');

            UniversityDetail::updateOrCreate(
                ['university_id' => $request->university_id],
                ['tuition_fee_from' => $minFee]
            );

            // Return JSON if AJAX request
            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Course added successfully!']);
            }

            return back()->with('success', 'Course added');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
            }
            return back()->withErrors('Error adding course');
        }
    }

    public function updateCourse(Request $request, $id)
    {
        $course = Course::findOrFail($id);

        $request->validate([
            'course_name' => 'required',
            'duration' => 'required',
            'tuition_fee' => 'required|numeric',
            'course_type' => 'nullable|string',
            'location' => 'nullable|string',
            'application_fee' => 'nullable|numeric',
            'currency_code' => 'nullable|string',
            'currency_symbol' => 'nullable|string',
        ]);

        try {
            $course->update([
                'course_name' => $request->course_name,
                'duration' => $request->duration,
                'course_type' => $request->course_type,
                'tuition_fee' => $request->tuition_fee,
                'location' => $request->location,
                'application_fee' => $request->application_fee,
                'currency_code' => $request->currency_code,
                'currency_symbol' => $request->currency_symbol ?? $this->getCurrencySymbol($request->currency_code),
            ]);

            // Return JSON if AJAX request
            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Course updated successfully!']);
            }

            return back()->with('success', 'Course updated');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
            }
            return back()->withErrors('Error updating course');
        }
    }

    public function deleteCourse($id)
    {
        try {
            $course = Course::findOrFail($id);
            $course->delete();

            // Return JSON if AJAX request
            if (request()->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Course deleted successfully!']);
            }

            return back()->with('success', 'Course deleted');
        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
            }
            return back()->withErrors('Error deleting course');
        }
    }

    private function getCurrencySymbol($code)
    {
        return match ($code) {
            'USD' => '$',
            'GBP' => '£',
            'EUR' => '€',
            'INR' => '₹',
            'AUD' => 'A$',
            'CAD' => 'C$',
            default => '$',
        };
    }
}


