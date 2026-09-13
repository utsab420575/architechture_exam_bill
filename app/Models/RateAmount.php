<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class RateAmount extends Model
{
    use HasFactory;
    protected $guarded=[];
    public function rateHead()
    {
        return $this->belongsTo(RateHead::class);
    }

    public function session()
    {
        return $this->belongsTo(Session::class);
    }

    public function examType()
    {
        return $this->belongsTo(ExamType::class);
    }


    // App\Models\RateAmount.php
    public static function getFor(int $sessionId, int $examTypeId, string $orderNo): ?self
    {
        $head = RateHead::where('order_no', $orderNo)->first();
        if (! $head) return null;

        return self::where('session_id',   $sessionId)
            ->where('exam_type_id',        $examTypeId)
            ->where('rate_head_id',        $head->id)
            ->where('saved',               1)
            ->first();
    }

    public static function isRateAmountSaved($ugr_id, $exam_type_id, $order_no)
    {
        Log::info("Checking RateAmount saved status", [
            'ugr_id' => $ugr_id,
            'exam_type_id' => $exam_type_id,
            'order_no' => $order_no,
        ]);

        $rate_head = RateHead::where('order_no', $order_no)->first();
        $session = Session::where('ugr_id', $ugr_id)
            ->where('exam_type_id', $exam_type_id)
            ->first();

        if (!$rate_head) {
            Log::warning("RateHead not found", ['order_no' => $order_no]);
            return false;
        }

        if (!$session) {
            Log::warning("Session not found", ['ugr_id' => $ugr_id, 'exam_type_id' => $exam_type_id]);
            return false;
        }

        $exists = self::where('session_id', $session->id)
            ->where('rate_head_id', $rate_head->id)
            ->where('exam_type_id', $exam_type_id)
            ->where('saved', 1)
            ->exists();

        Log::info("RateAmount saved status", [
            'session_id' => $session->id,
            'rate_head_id' => $rate_head->id,
            'exists' => $exists
        ]);

        return $exists;
    }


}