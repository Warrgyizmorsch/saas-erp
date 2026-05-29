<?php

namespace Modules\CRM\App\Http\Controllers\API;

use Modules\CRM\App\Http\Controllers\Controller;
use Modules\CRM\App\Models\Blog;
use Illuminate\Http\Request;

class BlogApiController extends Controller
{
    public function index(Request $request)
    {
        $site = $request->query('site', 'wts');

        $blogs = Blog::query()
            ->where('type', 'blog')
            ->where('site', $site)
            ->where('status', 'publish')
            ->with(['author:id,name,photo,description'])
            ->orderByDesc('created_at')
            ->get();

        $blogs->makeHidden(['author_image']);

        return response()->json($blogs);
    }

    public function show($slug)
    {
        $blog = Blog::query()
            ->where('slug', $slug)
            ->where('type', 'blog')
            ->where('status', 'publish')
            ->with(['author:id,name,photo,description'])
            ->firstOrFail();

        $blog->makeHidden(['author_image']);

        return response()->json($blog);
    }
}


