<?php

namespace Modules\CRM\App\Http\Controllers;

use Modules\CRM\App\Http\Controllers\Controller;
use Modules\CRM\App\Models\Bucket;
use Illuminate\Http\Request;

class BucketController extends Controller
{
    public function index()
    {
        $buckets = Bucket::with('children')->whereNull('parent_id')->where('is_deleted', 0)->get();
        $allBuckets = Bucket::where('is_deleted', 0)->get();

        return view('crm::crm.bucket.index', compact('buckets', 'allBuckets'))
            ->with('editBucket', null); // default
    }


    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:buckets,id',
            'bucket_color' => 'nullable|string',
        ]);

        Bucket::create([
            'name' => $request->name,
            'parent_id' => $request->parent_id,
            'bucket_color' => $request->bucket_color,
        ]);

        return redirect()->route('bucket.index')->with('success', 'Bucket added successfully.');
    }

    public function edit($id)
    {
        $editBucket = Bucket::findOrFail($id);
        $buckets = Bucket::with('children')->whereNull('parent_id')->where('is_deleted', 0)->get();
        $allBuckets = Bucket::where('is_deleted', 0)->get();

        return view('crm::crm.bucket.index', compact('buckets', 'allBuckets', 'editBucket'));
    }


    public function update(Request $request, Bucket $bucket)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:buckets,id',
            'bucket_color' => 'nullable|string',
        ]);

        $bucket->update([
            'name' => $request->name,
            'parent_id' => $request->parent_id,
            'bucket_color' => $request->bucket_color,
        ]);

        return redirect()->route('bucket.index')->with('success', 'Bucket updated successfully.');
    }

    public function destroy(Bucket $bucket)
    {
        $bucket->delete();
        return redirect()->route('bucket.index')->with('success', 'Bucket deleted successfully.');
    }
}


