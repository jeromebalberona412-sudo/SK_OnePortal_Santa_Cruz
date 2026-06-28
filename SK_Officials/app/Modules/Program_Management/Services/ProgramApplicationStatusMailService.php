<?php

namespace App\Modules\Program_Management\Services;

use App\Models\ProgramApplication;
use App\Modules\Program_Management\Notifications\ProgramApplicationStatusNotification;
use App\Modules\Program_Management\Services\ScheduleProgramService;
use Illuminate\Support\Facades\Notification;

class ProgramApplicationStatusMailService
{
    public function notify(ProgramApplication $application, string $eventType, ?string $reason = null): void
    {
        $email = trim((string) ($application->email ?? $application->kabataan?->email ?? ''));

        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        $fullName = trim(implode(' ', array_filter([
            $application->first_name,
            $application->middle_name,
            $application->last_name,
            $application->suffix,
        ])));

        $programName = trim((string) (
            $application->scheduleProgram?->program_name
            ?? $application->scheduleProgram?->program_type
            ?? 'Program'
        ));

        try {
            Notification::route('mail', $email)
                ->notify(new ProgramApplicationStatusNotification(
                    $fullName !== '' ? $fullName : 'Kabataan Member',
                    $programName,
                    $eventType,
                    $reason,
                ));
        } catch (\Throwable $exception) {
            report($exception);
        }
    }

    public function isProgramLetterNotifiable(string $letter): bool
    {
        $letter = strtoupper(trim($letter));

        return in_array($letter, [
            ScheduleProgramService::LETTER_EDUCATION,
            ScheduleProgramService::LETTER_SPORTS,
        ], true);
    }
}
