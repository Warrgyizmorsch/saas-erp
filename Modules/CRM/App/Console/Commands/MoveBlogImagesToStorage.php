<?php

namespace Modules\CRM\App\Console\Commands;

use Illuminate\Console\Command;
use Modules\CRM\App\Models\Blog;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MoveBlogImagesToStorage extends Command
{
    protected $signature = 'blogs:move-images';
    protected $description = 'Move old blog images to storage safely';

    public function handle()
    {
        $blogs = Blog::all();

        foreach ($blogs as $blog) {

            // ===========================
            // ✅ 1. MOVE THUMBNAIL
            // ===========================
            if (!empty($blog->images)) {

                // skip if already in storage
                if (Str::startsWith($blog->images, 'storage/')) {
                    continue;
                }

                $cleanPath = ltrim($blog->images, '/');
                $oldPath = public_path($cleanPath);

                if (File::exists($oldPath)) {

                    $filename = time() . '_' . uniqid() . '_' . basename($oldPath);
                    $newPath = 'blogthumbnail/' . $filename;

                    Storage::disk('public')->put(
                        $newPath,
                        File::get($oldPath)
                    );

                    // update DB
                    $blog->images = 'storage/' . $newPath;

                    $this->info("✔ Thumbnail moved: " . $filename);
                } else {
                    $this->warn("⚠ Thumbnail not found: " . $oldPath);
                }
            }

            // ===========================
            // ✅ 2. MOVE CONTENT IMAGES
            // ===========================
            if (!empty($blog->content)) {

                preg_match_all('/<img[^>]+src="([^">]+)"/i', $blog->content, $matches);

                foreach ($matches[1] as $src) {

                    // skip external or already moved images
                    if (
                        Str::contains($src, 'storage') ||
                        preg_match('/^https?:\/\//', $src)
                    ) {
                        continue;
                    }

                    $cleanSrc = ltrim($src, '/');
                    $oldPath = public_path($cleanSrc);

                    if (File::exists($oldPath)) {

                        $filename = time() . '_' . uniqid() . '_' . basename($oldPath);
                        $newPath = 'blog-content-images/' . $filename;

                        Storage::disk('public')->put(
                            $newPath,
                            File::get($oldPath)
                        );

                        $newUrl = '/storage/' . $newPath;

                        // replace in content
                        $blog->content = str_replace($src, $newUrl, $blog->content);

                        $this->info("✔ Content image moved: " . $filename);
                    } else {
                        $this->warn("⚠ Content image not found: " . $oldPath);
                    }
                }
            }

            // ===========================
            // ✅ SAVE BLOG
            // ===========================
            $blog->save();
        }

        $this->info("🎉 All images migrated successfully!");
    }
}
