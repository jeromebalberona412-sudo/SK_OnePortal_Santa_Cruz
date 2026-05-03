<?php

namespace App\Modules\Shared\Models;

use Illuminate\Database\Eloquent\Model;

class AnnouncementReaction extends Model
{
    protected $fillable = ['announcement_id', 'user_id', 'user_type'];
}
