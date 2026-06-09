<?php

namespace App\Modules\Rejected_Sports\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Program_Management\Controllers\RejectedProgramApplicationController;

class RejectedSportsController extends RejectedProgramApplicationController
{
    protected string $letter = 'I';

    protected string $viewName = 'Rejected_Sports::rejected-sports';

    protected string $restoreRoute = 'rejected-sports.restore';

    protected string $dataRoute = 'rejected-sports.data';
}
