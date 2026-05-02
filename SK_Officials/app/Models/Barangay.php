<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Barangay extends Model
{
    protected $fillable = ['name', 'municipality', 'province', 'region', 'tenant_id'];
}
