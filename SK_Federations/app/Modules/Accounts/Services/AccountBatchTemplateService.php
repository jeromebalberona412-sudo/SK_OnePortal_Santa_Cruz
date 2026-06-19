<?php

namespace App\Modules\Accounts\Services;

use App\Modules\Shared\Models\User;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AccountBatchTemplateService
{
    /**
     * @return list<string>
     */
    public function standardHeaders(): array
    {
        return [
            'First Name',
            'Middle Name',
            'Last Name',
            'Suffix',
            'Sex',
            'Birthdate',
            'Age',
            'Contact Number',
            'Position',
            'Region',
            'Province',
            'Municipality',
            'Barangay',
            'Term Start Date',
            'Term End Date',
            'Email Address',
        ];
    }

    /**
     * @return list<string>
     */
    public function headersForRole(string $role): array
    {
        return $this->standardHeaders();
    }

    /**
     * @return list<string>
     */
    public function optionalHeaderLabels(): array
    {
        return ['Middle Name'];
    }

    /**
     * @return list<string>
     */
    public function requiredHeaderLabels(): array
    {
        return array_values(array_filter(
            $this->standardHeaders(),
            fn (string $header): bool => ! in_array($header, $this->optionalHeaderLabels(), true)
        ));
    }

    /**
     * @return array<string, list<string>>
     */
    public function headerAliasMap(): array
    {
        return [
            'first name' => ['first name', 'first_name'],
            'middle name' => ['middle name', 'middle_name'],
            'last name' => ['last name', 'last_name'],
            'suffix' => ['suffix'],
            'sex' => ['sex', 'gender'],
            'birthdate' => ['birthdate', 'date of birth', 'birth date', 'date_of_birth', 'dob'],
            'age' => ['age'],
            'contact number' => ['contact number', 'contact_number', 'contact'],
            'position' => ['position'],
            'region' => ['region'],
            'province' => ['province'],
            'municipality' => ['municipality'],
            'barangay' => ['barangay', 'barangay name', 'barangay_name'],
            'term start date' => ['term start date', 'term start', 'term_start', 'start date'],
            'term end date' => ['term end date', 'term end', 'term_end', 'end date'],
            'email address' => ['email address', 'email'],
        ];
    }

    /**
     * @param  list<string>  $uploadedHeaders
     * @return list<string>
     */
    public function missingRequiredHeaders(array $uploadedHeaders): array
    {
        $normalized = array_map(
            fn (mixed $header): string => strtolower(trim((string) $header)),
            $uploadedHeaders
        );

        $missing = [];

        foreach ($this->requiredHeaderLabels() as $label) {
            $aliases = $this->headerAliasMap()[strtolower($label)] ?? [strtolower($label)];

            $found = false;
            foreach ($aliases as $alias) {
                if (in_array($alias, $normalized, true)) {
                    $found = true;
                    break;
                }
            }

            if (! $found) {
                $missing[] = $label;
            }
        }

        return $missing;
    }

    public function downloadResponse(string $role): StreamedResponse
    {
        $filename = $role === User::ROLE_SK_FED
            ? 'sk-federation-batch-template.csv'
            : 'sk-officials-batch-template.csv';

        $headers = $this->headersForRole($role);

        return response()->streamDownload(function () use ($headers): void {
            $handle = fopen('php://output', 'w');
            if ($handle === false) {
                return;
            }

            fputcsv($handle, $headers);
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function downloadXlsxResponse(string $role): BinaryFileResponse
    {
        $filename = $role === User::ROLE_SK_FED
            ? 'sk-federation-batch-template.xlsx'
            : 'sk-officials-batch-template.xlsx';

        $path = __DIR__.'/../assets/templates/'.$filename;

        if (! is_file($path)) {
            abort(404, 'Batch template file is missing.');
        }

        return response()->download($path, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
