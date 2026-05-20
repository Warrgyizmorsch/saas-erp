<?php

namespace Modules\CRM\App\Http\Controllers;

use Modules\CRM\App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Modules\CRM\App\Models\SubjectPage;
use Illuminate\Validation\Rule;
use Modules\CRM\App\Services\HomePageDataService;

class SubjectPageController extends Controller
{
    protected $homeData;

    public function __construct(HomePageDataService $homeData)
    {
        $this->homeData = $homeData;
    }
    public function index(Request $request)
    {
        $title = $request->input('title');
        $query = SubjectPage::query();

        if ($title) {
            $query->where('title', 'like', '%' . $title . '%');
        }        

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $data['SubjectPage'] = $query
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view("crm.subject-pages.index", compact('data'));
    }

    public function create () {        
        return view('crm.subject-pages.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'Title' => 'required|string',
            'Content' => 'required|string',
            'MetaTag' => 'required|string',
            'Metadescription' => 'required|string',            
            'photo' => 'nullable|image|max:5120',
            'Url' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('subject_pages', 'slug'),
            ],
            'status' => 'nullable|in:draft,publish',
        ],
        [
            'Url.unique' => 'This subject URL already exists. Please choose a different URL.',
            'Url.regex' => 'URL can contain only lowercase letters, numbers, and hyphens.',
        ]);                

        //prevent same title collision PER SITE (optional but safer than global)
        $existingSubjectPage = SubjectPage::where('slug', $request->input('Url'))
            ->where('title', $request->input('Title'))
            ->first();

        if ($existingSubjectPage) {
            return redirect()->back()->with('error', 'Subject-Page with this title already exists.');
        }

        //Slug source
        $slug = $request->input('Url');        

        $subjectPage = new SubjectPage();
        $Content = $request->input('Content');

        // ✅ Save base64 images with unique names (no overwrite)
        if (strpos($Content, '<img') !== false) {
            preg_match_all('/<img[^>]+src="([^">]+)"/', $Content, $matches);

            foreach ($matches[1] as $imageSrc) {
                if (preg_match('/^data:image\/(\w+);base64,/', $imageSrc, $base64Matches)) {
                    $imageType = strtolower($base64Matches[1]);
                    $base64Data = substr($imageSrc, strpos($imageSrc, ',') + 1);
                    $decodedImage = base64_decode($base64Data);

                    if ($decodedImage !== false) {
                        $destinationPath = public_path('subject-page/content-images/');
                        if (!file_exists($destinationPath)) {
                            mkdir($destinationPath, 0755, true);
                        }

                        // ✅ unique file name
                        $fileName = $slug . '-' . Str::random(8) . '.' . $imageType;
                        $fullPath = $destinationPath . '/' . $fileName;

                        file_put_contents($fullPath, $decodedImage);

                        $relativePath = '/subject-page/content-images/' . $fileName;
                        $Content = str_replace($imageSrc, $relativePath, $Content);
                    }
                }
            }
        }
        
        $subjectPage->title = $request->input('Title');
        $subjectPage->content = $Content;        
        $subjectPage->slug = $slug;
        $subjectPage->faq = $request->input('faq_data');
        $subjectPage->meta_title = $request->input('MetaTag');
        $subjectPage->meta_description = $request->input('Metadescription');        
        
        $subjectPage->status = $request->input('status', 'draft');

        // ✅ thumbnail upload (use site+slug to avoid overwrite)
        if ($request->hasFile('photo')) {
            $uploadedFile = $request->file('photo');
            $fileExtension = $uploadedFile->getClientOriginalExtension();

            $fileName = $slug . '.' . $fileExtension;
            $destinationPath = public_path('assets/media/subjectthumbnail');

            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            $uploadedFile->move($destinationPath, $fileName);
            $subjectPage->images = 'assets/media/subjectthumbnail/' . $fileName;
        } else {
            $subjectPage->images = '/images/blank.jpeg';
        }

        $subjectPage->save();

        return redirect()->back()->with('success', 'Subject Page Created Successfully');
    }

    public function edit(Request $request, $id)
    {
        $data = [
            'SubjectPage' => SubjectPage::findOrFail($id)
        ];

        $faqData = !empty($data['SubjectPage']->faq) ? json_decode($data['SubjectPage']->faq, true) : [];

        return view("crm.subject-pages.edit", compact('data', 'faqData'));
    }


    public function update(Request $request, $id)
    {
        $subjectPage = SubjectPage::find($id);
        if (!$subjectPage) {
            return redirect()->back()->with('error', 'Subject Page Not Found');
        }

        $request->validate([
            'Title' => 'required|string',
            'Content' => 'required|string',
            'MetaTag' => 'required|string',
            'Metadescription' => 'required|string',            
            'photo' => 'nullable|image|max:5120',            
            'Url' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',                
            ],
            'author_image' => 'nullable|exists:author,id',
            'created_at' => 'nullable|date',
            'status' => 'nullable|in:draft,publish',
        ],
        [            
            'Url.regex' => 'URL can contain only lowercase letters, numbers, and hyphens.',
        ]);        

        // regenerate slug, unique per site BUT ignore current id
        $slug = $request->input('Url');             

        $Content = $request->input('Content');

        // ✅ Save base64 images with unique names (no overwrite)
        if (strpos($Content, '<img') !== false) {
            preg_match_all('/<img[^>]+src="([^">]+)"/', $Content, $matches);

            foreach ($matches[1] as $imageSrc) {
                if (preg_match('/^data:image\/(\w+);base64,/', $imageSrc, $base64Matches)) {
                    $imageType = strtolower($base64Matches[1]);
                    $base64Data = substr($imageSrc, strpos($imageSrc, ',') + 1);
                    $decodedImage = base64_decode($base64Data);

                    if ($decodedImage !== false) {
                        $destinationPath = public_path('subject-page/content-images/');
                        if (!file_exists($destinationPath)) {
                            mkdir($destinationPath, 0755, true);
                        }

                        $fileName = $slug . '-' . Str::random(8) . '.' . $imageType;
                        $fullPath = $destinationPath . '/' . $fileName;

                        file_put_contents($fullPath, $decodedImage);

                        $relativePath = '/subject-page/content-images/' . $fileName;
                        $Content = str_replace($imageSrc, $relativePath, $Content);
                    }
                }
            }
        }

        $subjectPage->title = $request->input('Title');
        $subjectPage->slug = $slug;
        $subjectPage->content = $Content;
        $subjectPage->faq = $request->input('faq_data');
        $subjectPage->meta_title = $request->input('MetaTag');
        $subjectPage->meta_description = $request->input('Metadescription');        
        $subjectPage->status = $request->input('status', $subjectPage->status ?? 'draft');

        // ✅ thumbnail upload: prevent overwrite + delete old thumbnail if it was ours
        if ($request->hasFile('photo')) {
            $uploadedFile = $request->file('photo');
            $extension = $uploadedFile->getClientOriginalExtension();

            $destinationPath = public_path('assets/media/subjectthumbnail');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            // delete old thumbnail if it exists AND is not default
            if (!empty($subjectPage->images) && $subjectPage->images !== '/images/blank.jpeg') {
                $oldFullPath = public_path($subjectPage->images);
                if (file_exists($oldFullPath)) {
                    @unlink($oldFullPath);
                }
            }

            $fileName = $slug . '.' . $extension;
            $uploadedFile->move($destinationPath, $fileName);

            $subjectPage->images = 'assets/media/subjectthumbnail/' . $fileName; // ✅ fixed case
        }

        $subjectPage->save();

        return redirect()->back()->with('success', 'Subject Page Updated Successfully');
    }

    public function destroy($id)
    {
        $subjectPage = SubjectPage::find($id);
        if ($subjectPage->status !== 'draft') {
            return redirect()
                ->back()
                ->with('error', 'Only draft subject pages can be deleted.');
        }
        $subjectPage->delete();

        // Redirect or respond as needed
        return redirect()->back()->with('success', 'Subject Page Entry Deleted Successfully');
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
    public function getSubjectBySlug($slug)
    {        

        $data['SubjectPage'] = SubjectPage::where('slug', $slug)->where('status', 'publish')->first();        

        // Check if exists
        if (!$data['SubjectPage']) {
            abort(404); // Or handle the case where the subject with the given slug is not found
        }


        $data['title'] = $data['SubjectPage']->meta_title;
        $data['description'] = $data['SubjectPage']->meta_description;
        $data['keyword'] = $data['SubjectPage']->meta_tag;
        $data['canonical'] = 'https://wtsvisa.com/' . $data['SubjectPage']->slug;
        if ($data['SubjectPage']->faq) {

            $faqs = json_decode($data['SubjectPage']->faq, true);
            $data['Faqschema'] = $this->generateFaqSchema($faqs);


        } else {
            $data['Faqschema'] = $data['SubjectPage']->schema;
        }
        $canonical = 'https://wtsvisa.com/' . $data['SubjectPage']->slug;


        $data['artical'] = $this->Artical($data['SubjectPage']->title, $data['description'], $data['SubjectPage']->created_at, $data['SubjectPage']->updated_at);
        $data['org'] = $this->generateOrganizationSchema();

        $data['BreadcrumbList'] = $this->BreadcrumbList([
            ['name' => 'Home', 'url' => 'https://wtsvisa.com/'],
            ['name' => 'WTS Visa Consultancy', 'url' => 'https://wtsvisa.com/'],
            ['name' => $data['SubjectPage']->title, 'url' => 'https://wtsvisa.com/' . $slug]
        ]);

        $meta = [
            'title' => $data['SubjectPage']->meta_title ?? $data['SubjectPage']->title,
            'description' => $data['SubjectPage']->meta_description ?? Str::limit(strip_tags($data['SubjectPage']->content), 160),
            'keywords' => $data['SubjectPage']->meta_tag ?? '',
        ];
        $testimonials = $this->homeData->getTestimonials();
        return view("subject-pages.subject-detail", compact('data', 'meta', 'testimonials'));
    }

    public function subjectSitemap()
{
    $subjects = SubjectPage::where('status', 'publish')->latest()->get();

    return response()
        ->view('subject-pages.subjectSitemap', compact('subjects'))
        ->header('Content-Type', 'text/xml');
}
}

