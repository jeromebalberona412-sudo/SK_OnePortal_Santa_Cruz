<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnnouncementReaction extends Model
{
    protected $fillable = ['announcement_id', 'user_id', 'user_type'];
}
