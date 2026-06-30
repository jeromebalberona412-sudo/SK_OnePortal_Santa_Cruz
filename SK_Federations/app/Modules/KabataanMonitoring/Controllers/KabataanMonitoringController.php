<?php

namespace App\Modules\KabataanMonitoring\Controllers;

use App\Modules\KabataanMonitoring\Services\KabataanMonitoringService;
use App\Modules\Profile\Models\Barangay;
use App\Services\BarangayLogoUrlService;
use App\Modules\Shared\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KabataanMonitoringController extends Controller
{
    public function __construct(private readonly KabataanMonitoringService $service)
    {
    }

    public function index(Request $request): View
    {
        return view('kabataan_monitoring::index', [
            'user' => $request->user(),
        ]);
    }

    public function data(): JsonResponse
    {
        return response()->json([
            'data' => $this->service->listAll(),
            'years' => $this->service->registrationYears(),
        ]);
    }

    public function questionnaire(int $id): JsonResponse
    {
        $html = $this->service->renderQuestionnaireHtml($id);

        if ($html === null) {
            return response()->json(['message' => 'Record not found.'], 404);
        }

        return response()->json(['html' => $html]);
    }

    public function show(Request $request, string $kabataan): View
    {
        return view('kabataan_monitoring::show', [
            'user' => $request->user(),
            'kabataan' => $kabataan,
        ]);
    }

    public function barangayDetail(Request $request, string $barangay): View
    {
        $barangayName = urldecode($barangay);
        $barangayId = Barangay::query()->where('name', $barangayName)->value('id');
        $logoUrl = $barangayId ? app(BarangayLogoUrlService::class)->resolve((int) $barangayId) : null;

        return view('kabataan_monitoring::barangay-detail', [
            'user' => $request->user(),
            'barangay' => $barangayName,
            'barangayLogoUrl' => $logoUrl,
            'registrationYears' => $this->service->registrationYears($barangayName),
        ]);
    }
}
