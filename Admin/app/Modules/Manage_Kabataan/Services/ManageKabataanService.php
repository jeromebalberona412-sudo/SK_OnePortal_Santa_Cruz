<?php

namespace App\Modules\Manage_Kabataan\Services;

use App\Modules\Manage_Kabataan\Models\KabataanRegistration;
use App\Services\BarangayLogoUrlService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ManageKabataanService
{
    public function __construct(private readonly BarangayLogoUrlService $logoUrls)
    {
    }

    public function listQuery(Request $request, int $tenantId): Builder
    {
        $query = KabataanRegistration::query()
            ->with('barangay')
            ->where('tenant_id', $tenantId)
            ->whereNotNull('submitted_at')
            ->orderByDesc('submitted_at')
            ->orderBy('last_name')
            ->orderBy('first_name');

        if ($request->filled('barangay_id') && $request->barangay_id !== 'all') {
            $query->where('barangay_id', (int) $request->barangay_id);
        }

        if ($request->filled('search')) {
            $search = $request->string('search')->trim()->toString();
            $query->where(function (Builder $builder) use ($search) {
                $builder->where('last_name', 'ilike', "%{$search}%")
                    ->orWhere('first_name', 'ilike', "%{$search}%")
                    ->orWhere('email', 'ilike', "%{$search}%")
                    ->orWhere('contact_number', 'ilike', "%{$search}%")
                    ->orWhere('respondent_number', 'ilike', "%{$search}%");
            });
        }

        return $query;
    }

    public function mapListRow(KabataanRegistration $registration): array
    {
        $formData = $registration->form_data ?? [];
        $val = fn (string $key) => $this->formValue($formData, $key);

        return [
            'id' => $registration->id,
            'respondent_number' => $this->displayRespondentNumber($registration),
            'full_name' => $registration->full_name,
            'last_name' => $registration->last_name,
            'first_name' => $registration->first_name,
            'middle_name' => $registration->middle_name,
            'suffix' => $registration->suffix,
            'email' => $registration->email ?? '—',
            'contact_number' => $registration->contact_number ?? '—',
            'barangay' => $registration->barangay?->name ?? '—',
            'barangay_id' => $registration->barangay_id,
            'age' => $val('age'),
            'sex' => $val('sex'),
            'status' => $registration->status,
            'evaluation_status' => $registration->evaluation_status ?? '—',
            'submitted_at' => $registration->submitted_at?->format('m/d/Y') ?? '—',
        ];
    }

    public function mapDetailRow(KabataanRegistration $registration): array
    {
        $formData = $registration->form_data ?? [];
        $val = fn (string $key) => $this->formValue($formData, $key);
        $barangay = $registration->barangay;

        return [
            'id' => $registration->id,
            'respondent_number' => $registration->respondent_number,
            'respondent_display' => $this->displayRespondentNumber($registration),
            'last_name' => $registration->last_name,
            'first_name' => $registration->first_name,
            'middle_name' => $registration->middle_name,
            'suffix' => $registration->suffix,
            'full_name' => $registration->full_name,
            'age' => $val('age'),
            'birthday' => $val('birthday'),
            'sex' => $val('sex'),
            'email' => $registration->email,
            'contact_number' => $registration->contact_number,
            'barangay' => $barangay?->name ?? $val('barangay'),
            'region' => $barangay?->region ?? $val('region') ?: 'Region IV-A (CALABARZON)',
            'province' => $barangay?->province ?? $val('province') ?: 'Laguna',
            'city' => $barangay?->municipality ?? $val('city') ?: 'Santa Cruz',
            'purok_zone' => $val('purok_zone'),
            'sk_voter' => $val('sk_voter'),
            'national_voter' => $val('national_voter'),
            'civil_status' => $val('civil_status'),
            'youth_classification' => $val('youth_classification'),
            'youth_age_group' => $val('youth_age_group'),
            'work_status' => $val('work_status'),
            'education' => $val('education'),
            'sk_voted' => $val('sk_voted'),
            'kk_assembly' => $val('kk_assembly'),
            'kk_times' => $val('kk_times'),
            'kk_reason' => $val('kk_reason'),
            'facebook' => $val('facebook'),
            'group_chat' => $val('group_chat'),
            'signature' => $formData['signature'] ?? null,
            'status' => $registration->status,
            'evaluation_status' => $registration->evaluation_status,
            'submitted_at' => $registration->submitted_at?->format('m/d/Y'),
            'review_notes' => $registration->review_notes,
            'barangay_logo_url' => $this->logoUrls->resolve($registration->barangay_id),
            'rejection_reason' => $registration->review_notes,
        ];
    }

    public function summarize(Collection $rows): array
    {
        return [
            'total' => $rows->count(),
            'approved' => $rows->filter(fn (KabataanRegistration $row) => in_array($row->evaluation_status, ['active', 'Auto Approved'], true)
                || $row->status === 'active')->count(),
            'pending' => $rows->filter(fn (KabataanRegistration $row) => in_array($row->evaluation_status, ['Not Profiled', 'Wrong Credentials', 'pending_verification'], true)
                && $row->status !== 'rejected')->count(),
            'rejected' => $rows->where('status', 'rejected')->count(),
        ];
    }

    private function formValue(array $formData, string $key): string
    {
        $value = $formData[$key] ?? null;

        if (is_array($value)) {
            return (string) ($value[0] ?? '—');
        }

        return ($value === null || $value === '') ? '—' : (string) $value;
    }

    private function displayRespondentNumber(KabataanRegistration $registration): string
    {
        if ($registration->respondent_sequence) {
            return str_pad((string) $registration->respondent_sequence, 4, '0', STR_PAD_LEFT);
        }

        return $registration->respondent_number ?: '—';
    }
}
