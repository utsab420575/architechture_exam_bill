<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Designation extends Model
{
    use HasFactory;
    protected $guarded=[];

    public function teachers()
    {
        return $this->hasMany(Teacher::class);
    }
    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

}
