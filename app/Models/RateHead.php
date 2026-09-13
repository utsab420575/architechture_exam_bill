<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RateHead extends Model
{
    use HasFactory;
    protected $guarded=[];
    public function examType()
    {
        return $this->belongsTo(ExamType::class);
    }

    public function mergedWith()
    {
        return $this->belongsTo(RateHead::class, 'marge_with');
    }

    public function rateAmounts()
    {
        return $this->hasMany(RateAmount::class);
    }

    public function rateAssigns()
    {
        return $this->hasMany(RateAssign::class);
    }
}