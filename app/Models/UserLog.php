<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserLog extends Model
{
    protected $fillable = ['user_id','title','filename','mime_type','size'];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}
