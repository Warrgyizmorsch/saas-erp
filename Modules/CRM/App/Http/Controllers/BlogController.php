<?php

namespace Modules\CRM\App\Http\Controllers;

use Modules\CRM\App\Http\Controllers\Controller;
use Modules\CRM\App\Models\Author;
use Illuminate\Http\Request;
use Modules\CRM\App\Models\Blog;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $title = $request->input('title');
        $type = $request->input('type');
        $site = $request->input('site');
        $query = Blog::query();

        if ($title) {
            $query->where('title', 'like', '%' . $title . '%');
        }

        if ($type) {
            $query->where('type', $type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($site === 'warrgyizmorsch') {
            $query->where('site', 'warrgyizmorsch');
        } else {
            // DEFAULT → WTS only
            $query->where('site', 'wts');
        }

        $data['blog'] = $query
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view("crm.blog.index", compact('data'));
    }


    public function blogAuthor(Request $request)
    {
        $authors = Author::orderBy('id', 'asc')->get();
        return view("crm.blog.author", compact('authors'));
    }

    public function authorEdit($id)
    {
        $authors = Author::orderBy('id', 'asc')->get();

        $editAuthor = Author::findOrFail($id);

        return view('crm::crm.blog.author', compact('authors', 'editAuthor'));
    }

    public function authorstore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp,avif|max:2048',
            'author_id' => 'nullable|exists:author,id',
        ]);

        $author = $request->filled('author_id')
            ? Author::findOrFail($request->author_id)
            : new Author();

        $author->name = $request->name;
        $author->description = $request->description;

        if ($request->hasFile('photo')) {
            if (!empty($author->photo) && Storage::disk('public')->exists($author->photo)) {
                Storage::disk('public')->delete($author->photo);
            }

            $author->photo = $request->file('photo')->store('authors', 'public');
        }

        $author->save();

        return redirect()
            ->route('author.index')
            ->with('success', $request->filled('author_id') ? 'Author updated successfully!' : 'Author created successfully!');
    }
    public function authorDestroy($id)
    {
        $author = Author::findOrFail($id);

        // delete image from storage
        if (!empty($author->photo) && Storage::disk('public')->exists($author->photo)) {
            Storage::disk('public')->delete($author->photo);
        }

        $author->delete();

        return redirect()
            ->route('author.index')
            ->with('success', 'Author deleted successfully!');
    }

    public function create()
    {
        $authors = Author::orderBy('id', 'asc')->get();
        return view('crm::crm.blog.create', compact('authors'));
    }

    public function store(Request $request)
    {
        if ($request->input('type') !== "blog") {
            return redirect()->back()->with('error', 'Invalid type.');
        }

        $request->validate([
            'blogTitle' => 'required|string',
            'blogContent' => 'required|string',
            'MetaTag' => 'required|string',
            'Metadescription' => 'required|string',
            'site' => 'nullable|in:wts,warrgyizmorsch',
            'photo' => 'nullable|mimes:jpeg,jpg,png,webp|max:5120',
            'blogUrl' => 'nullable|string|max:255|regex:/^[a-zA-Z0-9-]+$/',
            'author_image' => 'nullable|exists:author,id',
            'status' => 'nullable|in:draft,publish',
        ]);

        $site = $request->input('site', 'wts');

        $allowedCategories = [
            "IT Services",
            "Digital Marketing",
            "Web Development & Design",
            "Software & Business Solutions",
            "SEO",
            "E-commerce",
        ];

        $category = $request->input('category');

        if ($site === 'warrgyizmorsch') {
            if (!$category || !in_array($category, $allowedCategories, true)) {
                return redirect()->back()->withInput()->with('error', 'Please select a valid category for Warrgyizmorsch.');
            }
        } else {
            $category = null;
        }

        //prevent same title collision PER SITE (optional but safer than global)
        $existingBlog = Blog::where('site', $site)
            ->where('title', $request->input('blogTitle'))
            ->first();

        if ($existingBlog) {
            return redirect()->back()->with('error', 'Blog with this title already exists for this site.');
        }

        //Slug source: for warrgyizmorsch allow custom, else always title
        $slugSource = $request->input('blogTitle');

        $slugSource = $request->filled('blogUrl')
            ? $request->input('blogUrl')
            : $request->input('blogTitle');

        $baseSlug = Str::slug($slugSource, '-');
        $slug = $baseSlug;
        $counter = 1;

        while (Blog::where('site', $site)->where('slug', $slug)->exists()) {
            $counter++;
            $slug = $baseSlug . '-' . $counter;
        }

        $blog = new Blog();
        $blogContent = $request->input('blogContent');

        // ✅ Save base64 images with unique names (no overwrite)
        if (strpos($blogContent, '<img') !== false) {
            preg_match_all('/<img[^>]+src="([^">]+)"/', $blogContent, $matches);

            foreach ($matches[1] as $imageSrc) {
                if (preg_match('/^data:image\/(\w+);base64,/', $imageSrc, $base64Matches)) {
                    $imageType = strtolower($base64Matches[1]);
                    $base64Data = substr($imageSrc, strpos($imageSrc, ',') + 1);
                    $decodedImage = base64_decode($base64Data);

                    if ($decodedImage !== false) {
                        $path = 'blog-content-images/' . $site . '-' . $slug . '-' . Str::random(8) . '.' . $imageType;

                        Storage::disk('public')->put($path, $decodedImage);

                        $relativePath = '/storage/' . $path;
                        $blogContent = str_replace($imageSrc, $relativePath, $blogContent);
                    }
                }
            }
        }

        $blog->site = $site;
        $blog->title = $request->input('blogTitle');
        $blog->content = $blogContent;
        $blog->type = $request->input('type');
        $blog->slug = $slug;
        $blog->faq = $request->input('faq_data');
        $blog->meta_title = $request->input('MetaTag');
        $blog->meta_discribtion = $request->input('Metadescription');
        $blog->category = $category;
        $blog->author_image = $request->filled('author_image')
            ? (int) $request->author_image
            : null;
        $blog->status = $request->input('status', 'draft');

        // ✅ thumbnail upload (use site+slug to avoid overwrite)
        if ($request->hasFile('photo')) {

            $path = $request->file('photo')->storeAs(
                'blogthumbnail',
                $site . '-' . $slug . '.' . $request->file('photo')->getClientOriginalExtension(),
                'public'
            );

            $blog->images = 'storage/' . $path;
        } else {
            $blog->images = '/images/blank.jpeg';
        }

        $blog->save();

        return redirect()->back()->with('success', 'Blog submitted successfully');
    }

    public function edit(Request $request, $id)
    {
        $data = [
            'blog' => Blog::findOrFail($id)
        ];

        $authors = Author::orderBy('id', 'asc')->get();
        $faqData = !empty($data['blog']->faq) ? json_decode($data['blog']->faq, true) : [];

        return view("crm.blog.edit", compact('data', 'faqData', 'authors'));
    }


    public function update(Request $request, $id)
    {
        $blog = Blog::find($id);
        if (!$blog) {
            return redirect()->back()->with('error', 'Blog not found');
        }

        $request->validate([
            'blogTitle' => 'required|string',
            'blogContent' => 'required|string',
            'MetaTag' => 'required|string',
            'Metadescription' => 'required|string',
            'site' => 'nullable|in:wts,warrgyizmorsch',
            'photo' => 'nullable|mimes:jpeg,jpg,png,webp|max:5120',
            'category' => 'nullable|string',
            'blogUrl' => 'nullable|string|max:255|regex:/^[a-zA-Z0-9-]+$/',
            'author_image' => 'nullable|exists:author,id',
            'created_at' => 'nullable|date',
            'status' => 'nullable|in:draft,publish',
        ]);

        $site = $request->input('site', $blog->site ?? 'wts');

        $allowedCategories = [
            "IT Services",
            "Digital Marketing",
            "Web Development & Design",
            "Software & Business Solutions",
            "SEO",
            "E-commerce",
        ];
        $category = $request->input('category');

        if ($request->filled('created_at')) {
            $blog->created_at = $request->input('created_at');
        }


        if ($site === 'warrgyizmorsch') {
            if (!$category || !in_array($category, $allowedCategories, true)) {
                return redirect()->back()->withInput()->with('error', 'Please select a valid category for Warrgyizmorsch.');
            }
        } else {
            $category = null;
        }

        // regenerate slug, unique per site BUT ignore current blog id
        $slugSource = $request->input('blogTitle');

        $slugSource = $request->filled('blogUrl')
            ? $request->input('blogUrl')
            : $request->input('blogTitle');

        $baseSlug = Str::slug($slugSource, '-');
        $slug = $baseSlug;
        $counter = 1;

        while (
            Blog::where('site', $site)
                ->where('slug', $slug)
                ->where('id', '!=', $blog->id)
                ->exists()
        ) {
            $counter++;
            $slug = $baseSlug . '-' . $counter;
        }

        $blogContent = $request->input('blogContent');

        // ✅ Save base64 images with unique names (no overwrite)
        if (strpos($blogContent, '<img') !== false) {
            preg_match_all('/<img[^>]+src="([^">]+)"/', $blogContent, $matches);

            foreach ($matches[1] as $imageSrc) {
                if (preg_match('/^data:image\/(\w+);base64,/', $imageSrc, $base64Matches)) {
                    $imageType = strtolower($base64Matches[1]);
                    $base64Data = substr($imageSrc, strpos($imageSrc, ',') + 1);
                    $decodedImage = base64_decode($base64Data);

                    if ($decodedImage !== false) {
                        $path = 'blog-content-images/' . $site . '-' . $slug . '-' . Str::random(8) . '.' . $imageType;

                        Storage::disk('public')->put($path, $decodedImage);

                        $relativePath = '/storage/' . $path;
                        $blogContent = str_replace($imageSrc, $relativePath, $blogContent);
                    }
                }
            }
        }

        $blog->site = $site;
        $blog->title = $request->input('blogTitle');
        $blog->slug = $slug;
        $blog->content = $blogContent;
        $blog->faq = $request->input('faq_data');
        $blog->meta_title = $request->input('MetaTag');
        $blog->meta_discribtion = $request->input('Metadescription');
        $blog->category = $category;
        $blog->author_image = $request->filled('author_image')
            ? (int) $request->author_image
            : null;
        $blog->status = $request->input('status', $blog->status ?? 'draft');

        // ✅ thumbnail upload: prevent overwrite + delete old thumbnail if it was ours
        if ($request->hasFile('photo')) {

            // delete old thumbnail if it exists AND is not default
            if (!empty($blog->images) && $blog->images !== '/images/blank.jpeg') {
                if (Str::startsWith($blog->images, 'storage/')) {
                    Storage::disk('public')->delete(str_replace('storage/', '', $blog->images));
                } else {
                    $oldFullPath = public_path($blog->images);
                    if (file_exists($oldFullPath)) {
                        @unlink($oldFullPath);
                    }
                }
            }

            $path = $request->file('photo')->storeAs(
                'blogthumbnail',
                $site . '-' . $slug . '.' . $request->file('photo')->getClientOriginalExtension(),
                'public'
            );

            $blog->images = 'storage/' . $path;
        }

        $blog->save();

        return redirect()->back()->with('success', 'Blog updated successfully');
    }

    public function destroy($id)
    {
        $blog = Blog::find($id);

        if (!$blog) {
            return redirect()->back()->with('error', 'Blog not found');
        }

        // ✅ delete thumbnail
        if (!empty($blog->images) && $blog->images !== '/images/blank.jpeg') {
            if (Str::startsWith($blog->images, 'storage/')) {
                Storage::disk('public')->delete(str_replace('storage/', '', $blog->images));
            } else {
                $oldFullPath = public_path($blog->images);
                if (file_exists($oldFullPath)) {
                    @unlink($oldFullPath);
                }
            }
        }

        $blog->delete();

        return redirect()->back()->with('success', 'Blog entry deleted successfully');
    }

    public function blog(Request $request)
    {
        $blogs = Blog::where('type', 'blog')
            ->where('site', 'wts')
            ->where('status', 'publish')
            ->orderByDesc('id')
            ->paginate(5);

        $canonical = $request->has('page')
            ? 'https://wtsvisa.com/blog?page=' . $request->input('page')
            : 'https://wtsvisa.com/blog';

        $meta = [
            'title' => 'Study Abroad Consultants | Expert Guidance for Students',
            'description' => 'Explore expert tips, guides, and latest updates on studying abroad. WTS Study Abroad Consultant blogs help students choose courses, universities, and countries.',
            'keywords' => 'visa blogs, study abroad blogs, uk visa blog, canada visa blog, australia visa blog, student visa guidance, immigration updates, wts visa consultancy blogs',
            'canonical' => $canonical,
        ];

        $data = [
            'canonical' => $canonical,
        ];

        return view('blog.blog-list', compact('blogs', 'data', 'meta'));
    }

    public function getBlogBySlug($slug)
    {
        // $data['blog'] = Blog::find($id);

        $data['blog'] = Blog::where('slug', $slug)->where('status', 'publish')->first();
        $data['recent_post'] = Blog::where('type', 'blog')->where('status', 'publish')->latest()->take(5)->get();

        // Check if the blog exists
        if (!$data['blog']) {
            abort(404); // Or handle the case where the blog with the given slug is not found
        }


        $data['title'] = $data['blog']->meta_title;
        $data['description'] = $data['blog']->meta_discribtion;
        $data['keyword'] = $data['blog']->meta_tag;
        $data['canonical'] = 'https://wtsvisa.com/blog/' . $data['blog']->slug;
        if ($data['blog']->faq) {

            $faqs = json_decode($data['blog']->faq, true);
            $data['Faqschema'] = $this->generateFaqSchema($faqs);


        } else {
            $data['Faqschema'] = $data['blog']->schema;
        }
        $canonical = 'https://wtsvisa.com/' . $data['blog']->slug;


        $data['artical'] = $this->Artical($data['blog']->title, $data['description'], $data['blog']->created_at, $data['blog']->updated_at);
        $data['org'] = $this->generateOrganizationSchema();

        $data['BreadcrumbList'] = $this->BreadcrumbList([
            ['name' => 'Home', 'url' => 'https://wtsvisa.com/'],
            ['name' => 'WTS Visa Consultancy Blogs', 'url' => 'https://wtsvisa.com/blog'],
            ['name' => $data['blog']->title, 'url' => 'https://wtsvisa.com/blog/' . $slug]
        ]);

        $meta = [
            'title' => $data['blog']->meta_title ?? $data['blog']->title,
            'description' => $data['blog']->meta_discribtion ?? Str::limit(strip_tags($data['blog']->content), 160),
            'keywords' => $data['blog']->meta_tag ?? '',
        ];

        return view("blog.blog-detail", compact('data', 'meta'));
    }

    public function blogSitemap()
    {
        $blogs = Blog::where('type', 'blog')
            ->where('site', 'wts')
            ->where('status', 'publish')
            ->select('created_at', 'images', 'slug', 'title')
            ->get();

        return response()
            ->view('blog.blogSitemap', ['blogs' => $blogs])
            ->header('Content-Type', 'text/xml');
    }

    public function loadMore(Request $request)
    {
        $offset = (int) $request->get('offset', 0);
        $limit = 4;

        $baseQuery = Blog::where('type', 'blog')
            ->where('site', 'wts')
            ->where('status', 'publish');

        $blogs = (clone $baseQuery)
            ->orderByDesc('id')
            ->skip($offset)
            ->take($limit)
            ->get();

        $totalBlogs = (clone $baseQuery)->count();

        return response()->json([
            'html' => view('blog.blog-cards', compact('blogs'))->render(),
            'hasMore' => $totalBlogs > ($offset + $limit),
        ]);
    }

    function generateOrganizationSchema()
    {
        return json_encode([
            "@context" => "https://schema.org",
            "@type" => "Organization",
            "name" => "WTS Visa Consultancy",
            "url" => "https://wtsvisa.com",
            "logo" => "https://wtsvisa.com/new-home-images/wts-logo.png",
            "contactPoint" => [
                "@type" => "ContactPoint",
                "telephone" => "+44 7435256433",
                "contactType" => "Customer Service",
                "availableLanguage" => ["English"]
            ],
            "sameAs" => [
                "https://www.facebook.com/WTSvisaconsultancy",
                "https://www.instagram.com/wts_visaconsultancy/",
            ]
        ]);
    }
    function generateFaqSchema(array $faqEntries): string
    {
        $mainEntity = array_map(fn($entry) => [
            "@type" => "Question",
            "name" => $entry['question'],
            "acceptedAnswer" => [
                "@type" => "Answer",
                "text" => $entry['answer']
            ]
        ], $faqEntries);

        return json_encode([
            "@context" => "https://schema.org",
            "@type" => "FAQPage",
            "mainEntity" => $mainEntity
        ]);
    }

    function BreadcrumbList(array $breadcrumbs): string
    {
        $breadcrumbSchema = [
            "@context" => "https://schema.org",
            "@type" => "BreadcrumbList",
            "itemListElement" => []
        ];

        foreach ($breadcrumbs as $position => $breadcrumb) {
            $breadcrumbSchema['itemListElement'][] = [
                "@type" => "ListItem",
                "position" => $position + 1,
                "name" => $breadcrumb['name'],
                "item" => $breadcrumb['url']
            ];
        }

        // Return as a JSON string
        return json_encode($breadcrumbSchema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    function artical($title, $description, $pdalishDate, $updalishDate)
    {
        return json_encode([
            "@context" => "http://schema.org",
            "@type" => "Article",
            "image" => "https://wtsvisa.com/new-home-images/wts-logo.png",


            "mainEntityOfPage" => [
                "@type" => "WebPage",
                "@id" => env('CANONICAL_URL', url()->current()) ?? ""
            ],
            "headline" => $title ?? "",
            "datePublished" => $pdalishDate ?? "",
            "dateModified" => $updalishDate ?? "",
            "author" => [
                "@type" => "Organization",
                "name" => "WTSVisaConsultancy",
                "url" => "https://wtsvisa.com",
            ],
            "publisher" => [
                "@type" => "Organization",
                "name" => 'WTSVisaConsultancy',

            ],
            "description" => $description
        ], JSON_UNESCAPED_SLASHES);
    }

    public function searchBlogs(Request $request)
    {
        $query = trim($request->get('q', ''));

        if (strlen($query) < 2) {
            return response()->json(['results' => []]);
        }

        $blogs = Blog::where('type', 'blog')
            ->where('site', 'wts')
            ->where('status', 'publish')
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', $query . '%')
                    ->orWhere('title', 'like', '%' . $query . '%');
            })
            ->orderByRaw("
            CASE 
                WHEN title LIKE ? THEN 1
                WHEN title LIKE ? THEN 2
                ELSE 3
            END
        ", [$query . '%', '%' . $query . '%'])
            ->orderBy('created_at', 'desc')
            ->select('id', 'title', 'slug', 'images', 'created_at')
            ->limit(10)
            ->get();

        $results = $blogs->map(function ($blog) {
            return [
                'title' => $blog->title,
                'url' => url('/blog/' . $blog->slug),
                'image' => $blog->images ? asset($blog->images) : asset('default.jpg'),
                'date' => $blog->created_at->format('F j, Y'),
            ];
        });

        return response()->json(['results' => $results]);
    }
}


