<?php

namespace App\Modules\CommunityFeed\Services;

use Cloudinary\Cloudinary;
use Illuminate\Http\UploadedFile;

class CloudinaryService
{
    private Cloudinary $cloudinary;
    private string $folder;

    public function __construct()
    {
        $cloudName = config('services.cloudinary.cloud_name');
        $apiKey    = config('services.cloudinary.api_key');
        $apiSecret = config('services.cloudinary.api_secret');

        $this->cloudinary = new Cloudinary(
            "cloudinary://{$apiKey}:{$apiSecret}@{$cloudName}"
        );

        $this->folder = config('services.cloudinary.folder', 'sk_oneportal/sk_fed_posts');
    }

    /** @return array{public_id: string, url: string} */
    public function upload(UploadedFile $file, string $publicId): array
    {
        $result = $this->cloudinary->uploadApi()->upload(
            $file->getRealPath(),
            [
                'public_id'      => $this->folder . '/' . $publicId,
                'overwrite'      => true,
                'resource_type'  => 'image',
                'transformation' => [['quality' => 'auto', 'fetch_format' => 'auto']],
            ]
        );

        return [
            'public_id' => $result['public_id'],
            'url'       => $result['secure_url'],
        ];
    }
}
