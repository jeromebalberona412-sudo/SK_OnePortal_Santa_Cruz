<?php

namespace App\Services;

use App\Models\KabataanRegistration;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class KkSupportingDocumentService
{
    private const KABATAAN_DISK = 'public';

    /**
     * @return list<array<string, mixed>>
     */
    public function formatForApi(KabataanRegistration $registration, string $documentRouteName = 'kkprofiling-requests.document'): array
    {
        $formData = $registration->form_data ?? [];
        $documents = $formData['supporting_documents'] ?? [];

        if (! is_array($documents) || $documents === []) {
            return [];
        }

        $formatted = [];

        foreach ($documents as $index => $document) {
            if (! is_array($document)) {
                continue;
            }

            $type = (string) ($document['type'] ?? 'document');
            $sides = is_array($document['sides'] ?? null) ? $document['sides'] : [];
            $sideEntries = [];

            if ($sides === [] && ! empty($document['path'])) {
                $sides = ['front' => $document];
            }

            foreach (['front', 'back'] as $side) {
                $meta = is_array($sides[$side] ?? null) ? $sides[$side] : null;

                if ($meta === null) {
                    continue;
                }

                $sideEntries[] = [
                    'side' => $side,
                    'label' => ucfirst($side),
                    'url' => $this->resolvePreviewUrl($registration, (int) $index, $side, $meta, $documentRouteName),
                    'original_name' => (string) ($meta['original_name'] ?? $meta['display_name'] ?? 'Document'),
                ];
            }

            if ($sideEntries === []) {
                continue;
            }

            $formatted[] = [
                'type' => $type,
                'label' => $this->typeLabel($type),
                'sides' => $sideEntries,
            ];
        }

        return $formatted;
    }

    public function streamForOfficial(
        KabataanRegistration $registration,
        int $documentIndex,
        string $side,
        bool $download = false,
    ): Response {
        $documents = $registration->form_data['supporting_documents'] ?? [];

        if (! is_array($documents) || ! isset($documents[$documentIndex])) {
            abort(404, 'Document not found.');
        }

        $document = $documents[$documentIndex];
        $sides = is_array($document['sides'] ?? null) ? $document['sides'] : [];

        if ($sides === [] && ! empty($document['path'])) {
            $sides = ['front' => $document];
        }

        $meta = is_array($sides[$side] ?? null) ? $sides[$side] : null;

        if ($meta === null) {
            abort(404, 'Document side not found.');
        }

        if (($meta['storage'] ?? '') === 'cloudinary' && ! empty($meta['url'])) {
            return redirect()->away((string) $meta['url']);
        }

        $path = (string) ($meta['path'] ?? '');

        if ($path === '') {
            abort(404, 'Document path missing.');
        }

        $absolutePath = $this->resolveAbsolutePath($path);

        if ($absolutePath === null) {
            abort(404, 'Document file not found.');
        }

        $originalName = (string) ($meta['original_name'] ?? basename($path));
        $mime = (string) ($meta['mime'] ?? mime_content_type($absolutePath) ?: 'image/jpeg');
        $disposition = $download ? 'attachment' : 'inline';

        return response()->file($absolutePath, [
            'Content-Type' => $mime,
            'Content-Disposition' => $disposition.'; filename="'.addslashes($originalName).'"',
        ]);
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    private function resolvePreviewUrl(
        KabataanRegistration $registration,
        int $documentIndex,
        string $side,
        array $meta,
        string $documentRouteName = 'kkprofiling-requests.document',
    ): string {
        if (($meta['storage'] ?? '') === 'cloudinary' && ! empty($meta['url'])) {
            return (string) $meta['url'];
        }

        if (! empty($meta['url']) && str_starts_with((string) $meta['url'], 'http')) {
            return (string) $meta['url'];
        }

        return route($documentRouteName, [
            'id' => $registration->id,
            'documentIndex' => $documentIndex,
            'side' => $side,
        ]);
    }

    private function resolveAbsolutePath(string $path): ?string
    {
        if (Storage::disk(self::KABATAAN_DISK)->exists($path)) {
            return Storage::disk(self::KABATAAN_DISK)->path($path);
        }

        $roots = array_filter([
            env('KABATAAN_STORAGE_ROOT'),
            realpath(base_path('../Kabataan/storage/app/public')),
        ]);

        foreach ($roots as $root) {
            $candidate = rtrim((string) $root, DIRECTORY_SEPARATOR)
                .DIRECTORY_SEPARATOR
                .str_replace('/', DIRECTORY_SEPARATOR, $path);

            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private function typeLabel(string $type): string
    {
        return match ($type) {
            'school_id' => 'School ID',
            'national_id' => 'PhilSys / National ID',
            'barangay_clearance' => 'Barangay Clearance',
            default => 'Supporting Document',
        };
    }
}
