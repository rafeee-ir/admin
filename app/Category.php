<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $guarded=[];


    public function tasks()
    {
        return $this->belongsToMany('App\Task', 'category_task');
    }
}
