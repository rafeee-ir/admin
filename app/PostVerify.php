<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PostVerify extends Model
{
    protected $guarded=[];

    public function user()
    {
        return $this->belongsTo('App\User');
    }
    public function post()
    {
        return $this->belongsTo('App\Post');
    }
}
