<?php

namespace Modules\CRM\App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;
use Modules\CRM\App\Models\University;

class DownloadUniversityLogos extends Command
{
    protected $signature = 'university:download-logos';
    protected $description = 'Download university logo images from S3 to local storage';

    public function handle()
    {
        $s3Base = 'https://crizac-assets.s3.ap-south-1.amazonaws.com/system';
        $savePath = public_path('university-logos');

        // Create directory if not exists
        if (!File::exists($savePath)) {
            File::makeDirectory($savePath, 0755, true);
        }

        $universities = University::whereNotNull('logo_image')
            ->where('logo_image', '!=', '0')
            ->where('logo_image', '!=', '')
            ->get();

        $this->info('Found ' . $universities->count() . ' logos');

        foreach ($universities as $uni) {

            $remotePath = ltrim($uni->logo_image, '/');
            $imageUrl   = $s3Base . '/' . $remotePath;

            $fileName   = basename($remotePath);
            $localFile  = $savePath . '/' . $fileName;

            // Skip if already downloaded
            if (File::exists($localFile)) {
                $this->line("✔ Already exists: {$fileName}");
                continue;
            }

            try {
                $response = Http::timeout(15)->get($imageUrl);

                if ($response->successful()) {
                    File::put($localFile, $response->body());

                    // OPTIONAL: update DB to local path
                    // $uni->update([
                    //     'logo_image' => 'university-logos/' . $fileName
                    // ]);

                    $this->info("⬇ Downloaded: {$fileName}");
                } else {
                    $this->error("✖ Failed: {$imageUrl}");
                }
            } catch (\Exception $e) {
                $this->error("⚠ Error: {$imageUrl}");
            }
        }

        $this->info('🎉 Logo download completed');
        return Command::SUCCESS;
    }
}

