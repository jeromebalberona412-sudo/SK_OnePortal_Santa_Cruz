<?php

namespace App\Modules\Reports\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Reports\Models\ReportDocument;
use App\Modules\Reports\Services\WordDocumentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportsDocumentController extends Controller
{
    private const MAX_UPLOAD_KB = 10240; // 10 MB

    public function __construct(
        private readonly WordDocumentService $wordService
    ) {}

    public function index()
    {
        $documents = ReportDocument::with('uploader')
            ->latest()
            ->paginate(15);

        return view('Reports::documents.index', compact('documents'));
    }

    public function upload(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'document' => 'required|file|mimes:docx|max:' . self::MAX_UPLOAD_KB,
        ]);

        $file = $request->file('document');
        $originalName = $file->getClientOriginalName();
        $storedName = Str::uuid() . '.docx';
        $path = $file->storeAs('reports/documents', $storedName, 'local');
        $fullPath = Storage::disk('local')->path($path);

        $html = '';
        try {
            $html = $this->wordService->convertDocxToHtml($fullPath);
        } catch (\Throwable $e) {
            report($e);
        }

        $doc = ReportDocument::create([
            'title' => $validated['title'],
            'filename' => $originalName,
            'file_path' => $path,
            'content' => $html ?: '<p></p>',
            'uploaded_by' => Auth::id(),
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Document uploaded successfully.',
                'document' => $this->documentPayload($doc),
            ]);
        }

        return redirect()
            ->route('reports.documents.view', $doc)
            ->with('success', 'Document uploaded successfully.');
    }

    public function view(ReportDocument $document)
    {
        $document->load('uploader');

        return view('Reports::documents.view', compact('document'));
    }

    public function preview(ReportDocument $document)
    {
        return response()->json([
            'success' => true,
            'document' => $this->documentPayload($document->load('uploader')),
        ]);
    }

    public function edit(ReportDocument $document)
    {
        return view('Reports::documents.edit', compact('document'));
    }

    public function update(Request $request, ReportDocument $document)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'content' => 'required|string',
            'regenerate_docx' => 'nullable|boolean',
        ]);

        $document->title = $validated['title'];
        $document->content = $validated['content'];
        $document->save();

        if ($request->boolean('regenerate_docx')) {
            $this->regenerateStoredDocx($document);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Document saved.',
            ]);
        }

        return redirect()
            ->route('reports.documents.view', $document)
            ->with('success', 'Document saved successfully.');
    }

    public function download(ReportDocument $document)
    {
        if (! $document->hasStoredFile()) {
            $this->regenerateStoredDocx($document);
            $document->refresh();
        }

        if (! $document->hasStoredFile()) {
            abort(404, 'File not found.');
        }

        $downloadName = Str::endsWith(strtolower($document->filename), '.docx')
            ? $document->filename
            : $document->filename . '.docx';

        return Storage::disk('local')->download($document->file_path, $downloadName);
    }

    public function export(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'content' => 'required|string',
            'subtitle' => 'nullable|string|max:300',
            'include_table' => 'nullable|boolean',
        ]);

        $tableRows = [];
        if ($request->boolean('include_table')) {
            $tableRows = [
                ['Name' => 'Juan Dela Cruz', 'Program' => 'SK Scholarship', 'Status' => 'Active'],
                ['Name' => 'Maria Santos', 'Program' => 'SK Scholarship', 'Status' => 'Active'],
                ['Name' => 'Pedro Reyes', 'Program' => 'Educational Assistance', 'Status' => 'Pending'],
            ];
        }

        $storedName = Str::uuid() . '.docx';
        $path = 'reports/documents/' . $storedName;
        $fullPath = Storage::disk('local')->path($path);

        Storage::disk('local')->makeDirectory('reports/documents');

        $this->wordService->generateReportDocx(
            $validated['title'],
            $validated['content'],
            $fullPath,
            $tableRows,
            $validated['subtitle'] ?? null
        );

        $html = '';
        try {
            $html = $this->wordService->convertDocxToHtml($fullPath);
        } catch (\Throwable $e) {
            $html = $validated['content'];
        }

        $doc = ReportDocument::create([
            'title' => $validated['title'],
            'filename' => Str::slug($validated['title']) . '.docx',
            'file_path' => $path,
            'content' => $html,
            'uploaded_by' => Auth::id(),
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Word document generated.',
                'document' => $this->documentPayload($doc),
                'download_url' => route('reports.documents.download', $doc),
            ]);
        }

        return redirect()
            ->route('reports.documents.index')
            ->with('success', 'Word document generated and saved.');
    }

    public function destroy(ReportDocument $document)
    {
        if ($document->file_path) {
            Storage::disk('local')->delete($document->file_path);
        }
        $document->delete();

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Document deleted.']);
        }

        return redirect()
            ->route('reports.documents.index')
            ->with('success', 'Document deleted.');
    }

    private function regenerateStoredDocx(ReportDocument $document): void
    {
        Storage::disk('local')->makeDirectory('reports/documents');
        $storedName = Str::uuid() . '.docx';
        $path = 'reports/documents/' . $storedName;
        $fullPath = Storage::disk('local')->path($path);

        $this->wordService->generateDocxFromHtml(
            $document->title,
            $document->content ?? '',
            $fullPath
        );

        if ($document->file_path) {
            Storage::disk('local')->delete($document->file_path);
        }

        $document->file_path = $path;
        $document->filename = Str::slug($document->title) . '.docx';
        $document->save();
    }

  private function documentPayload(ReportDocument $document): array
    {
        return [
            'id' => $document->id,
            'title' => $document->title,
            'filename' => $document->filename,
            'content' => $document->content,
            'uploaded_by' => $document->uploader?->name,
            'created_at' => $document->created_at?->format('M d, Y g:i A'),
            'view_url' => route('reports.documents.view', $document),
            'edit_url' => route('reports.documents.edit', $document),
            'download_url' => route('reports.documents.download', $document),
        ];
    }
}
