<?php

namespace App\Modules\Rejected_Scholarship\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Program_Management\Controllers\RejectedProgramApplicationController;

class RejectedScholarshipController extends RejectedProgramApplicationController
{
    protected string $letter = 'A';

    protected string $viewName = 'Rejected_Scholarship::rejected-scholarship';

    protected string $restoreRoute = 'rejected-scholars.restore';

    protected string $dataRoute = 'rejected-scholars.data';
}
