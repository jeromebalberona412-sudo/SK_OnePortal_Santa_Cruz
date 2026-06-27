<?php

namespace App\Modules\Program_Management\Services;

use App\Models\ProgramApplication;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProgramDocumentService
{
    private const DISK = 'public';

    /**
     * @param  list<array<string, mixed>>  $documents
     * @return list<array<string, mixed>>
     */
    public function enrichDocumentsForApplication(ProgramApplication $application, array $documents): array
    {
        $enriched = [];

        foreach ($documents as $meta) {
            if (! is_array($meta) || empty($meta['path'])) {
                continue;
            }

            $questionId = (string) ($meta['question_id'] ?? '');
            if ($questionId === '') {
                continue;
            }

            $size = (int) ($meta['size'] ?? 0);

            $enriched[] = array_merge($meta, [
                'question_id' => $questionId,
                'size_display' => $this->formatFileSize($size),
                'preview_url' => route('api.program-applications.document', [
                    'id' => $application->id,
                    'questionId' => $questionId,
                ]),
                'download_url' => route('api.program-applications.document', [
                    'id' => $application->id,
                    'questionId' => $questionId,
                    'download' => 1,
                ]),
                'status' => 'uploaded',
            ]);
        }

        return $enriched;
    }

    public function streamForOfficial(ProgramApplication $application, string $questionId, bool $download = false): BinaryFileResponse
    {
        $documents = $application->required_documents ?? [];
        $meta = is_array($documents[$questionId] ?? null) ? $documents[$questionId] : null;

        if ($meta === null || empty($meta['path'])) {
            abort(404, 'Document not found.');
        }

        $path = (string) $meta['path'];

        $absolutePath = $this->resolveAbsolutePath($path);
        if ($absolutePath === null) {
            abort(404, 'Document file not found.');
        }

        $originalName = (string) ($meta['original_name'] ?? basename($path));
        $disposition = $download ? 'attachment' : 'inline';

        return response()->file($absolutePath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => $disposition.'; filename="'.addslashes($originalName).'"',
        ]);
    }

    private function resolveAbsolutePath(string $path): ?string
    {
        if (Storage::disk(self::DISK)->exists($path)) {
            return Storage::disk(self::DISK)->path($path);
        }

        $roots = array_filter([
            env('PROGRAM_APPLICATIONS_STORAGE_ROOT'),
            realpath(base_path('../Kabataan/storage/app/public')),
        ]);

        foreach ($roots as $root) {
            $candidate = rtrim((string) $root, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $path);
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private function formatFileSize(int $bytes): string
    {
        if ($bytes <= 0) {
            return '0 Bytes';
        }

        $units = ['Bytes', 'KB', 'MB', 'GB'];
        $power = min((int) floor(log($bytes, 1024)), count($units) - 1);

        return round($bytes / (1024 ** $power), 1).' '.$units[$power];
    }
}
