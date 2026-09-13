<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class University extends Model
{
    use HasFactory;

    protected $guarded=[];

    /**
     * A university has many teachers
     */
    public function teachers()
    {
        return $this->hasMany(Teacher::class);
    }
}
