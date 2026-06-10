<?php

namespace App\Modules\Profile\Services;

use App\Models\User;
use App\Services\CloudinaryService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ProfileImageService
{
    private const MAX_BYTES = 10 * 1024 * 1024;

    /** @var list<string> */
    private const ALLOWED_MIMES = [
        'image/jpeg',
        'image/jpg',
        'image/png',
        'image/webp',
    ];

    public function __construct(private readonly CloudinaryService $cloudinary)
    {
    }

    public function defaultAvatarUrl(User $user, ?string $displayName = null): string
    {
        $name = $displayName ?: ($user->name ?: 'Youth User');

        return 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&size=150&background=667eea&color=fff';
    }

    public function resolveDisplayUrl(User $user, ?string $displayName = null): string
    {
        if ($user->profile_image_url) {
            return CloudinaryService::cacheBust(
                $user->profile_image_url,
                $user->profile_image_uploaded_at
            );
        }

        return $this->defaultAvatarUrl($user, $displayName);
    }

    public function canChangeProfileImage(User $user): bool
    {
        if (! $user->profile_image_uploaded_at) {
            return true;
        }

        if (! $user->profile_image_change_available_at) {
            return true;
        }

        return now()->gte($user->profile_image_change_available_at);
    }

    public function nextChangeDisplayDate(User $user): ?string
    {
        if (! $user->profile_image_change_available_at || $this->canChangeProfileImage($user)) {
            return null;
        }

        return $user->profile_image_change_available_at->format('F j, Y');
    }

    /**
     * @return array{
     *     picture_url: string,
     *     next_change_available_at: string,
     *     next_change_display: string,
     *     can_change: bool
     * }
     */
    public function upload(User $user, UploadedFile $file): array
    {
        $this->assertValidFile($file);

        if (! $this->canChangeProfileImage($user)) {
            $date = $user->profile_image_change_available_at?->format('F j, Y') ?? 'a later date';

            throw ValidationException::withMessages([
                'profile_picture' => [
                    "You can change your profile picture again on {$date}. Profile pictures can only be updated once every 30 days.",
                ],
            ]);
        }

        $oldPublicId = $user->profile_image_public_id;
        $publicId    = 'user_' . $user->id;

        $result = $this->cloudinary->uploadProfileImage($file, $publicId);

        $uploadedAt   = now();
        $nextChangeAt = $uploadedAt->copy()->addDays(30);

        $user->forceFill([
            'profile_image_url'                 => $result['url'],
            'profile_image_public_id'           => $result['public_id'],
            'profile_image_uploaded_at'         => $uploadedAt,
            'profile_image_change_available_at' => $nextChangeAt,
        ])->save();

        if ($oldPublicId) {
            try {
                $this->cloudinary->delete($oldPublicId);
            } catch (\Throwable $exception) {
                Log::warning('Failed to delete previous Kabataan profile image from Cloudinary', [
                    'user_id'   => $user->id,
                    'public_id' => $oldPublicId,
                    'error'     => $exception->getMessage(),
                ]);
            }
        }

        $fresh = $user->fresh();

        return [
            'picture_url'              => $this->resolveDisplayUrl($fresh),
            'next_change_available_at' => $nextChangeAt->toIso8601String(),
            'next_change_display'    => $nextChangeAt->format('F j, Y'),
            'can_change'             => false,
        ];
    }

    private function assertValidFile(UploadedFile $file): void
    {
        if (! $file->isValid()) {
            throw ValidationException::withMessages([
                'profile_picture' => ['Please select a valid image file.'],
            ]);
        }

        if ($file->getSize() > self::MAX_BYTES) {
            throw ValidationException::withMessages([
                'profile_picture' => ['Profile image must be 10MB or smaller.'],
            ]);
        }

        $mime = strtolower((string) $file->getMimeType());

        if (! in_array($mime, self::ALLOWED_MIMES, true)) {
            throw ValidationException::withMessages([
                'profile_picture' => ['Only JPG, JPEG, PNG, and WEBP images are allowed.'],
            ]);
        }
    }
}
