<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamType extends Model
{
    use HasFactory;
    protected $guarded=[];

    /*public function rateHeads()
    {
        return $this->hasMany(RateHead::class, 'exam_type');
    }*/
    public function rateAmounts()
    {
        return $this->hasMany(RateAmount::class);
    }

    public function sessions()
    {
        return $this->hasMany(Session::class);
    }

    public function rateAssigns()
    {
        return $this->hasMany(RateAssign::class);
    }
}
