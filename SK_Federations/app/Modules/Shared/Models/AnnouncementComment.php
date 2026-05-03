<?php

namespace App\Modules\Shared\Models;

use Illuminate\Database\Eloquent\Model;

class AnnouncementComment extends Model
{
    protected $fillable = ['announcement_id', 'user_id', 'user_type', 'author_name', 'body'];
}
