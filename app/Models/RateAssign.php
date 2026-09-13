<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class RateAssign extends Model
{
    use HasFactory;
    protected $guarded=[];
    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

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

    public static function getModerationCommitteeData($sessionId, $examTypeId, $rateHeadId)
    {
        // Log the input parameters for clarity
        Log::info("Fetching teachers for moderation committee with parameters:", [
            'session_id' => $sessionId,
            'exam_type_id' => $examTypeId,
            'rate_head_id' => $rateHeadId,
        ]);


        return self::with([
            'teacher.user',
            'teacher.designation',
            'teacher.department'
        ])
            ->where('session_id', $sessionId)
            ->where('exam_type_id', $examTypeId)
            ->where('rate_head_id', $rateHeadId)
            ->get();
    }

    public static function getTeachersFromCommittee($sessionId, $examTypeId, $rateHeadId)
    {

        // Log the input parameters for clarity
        Log::info("Fetching teachers for committee with parameters:", [
            'session_id' => $sessionId,
            'exam_type_id' => $examTypeId,
            'rate_head_id' => $rateHeadId,
        ]);

        $data =  self::with([
            'teacher.user',
            'teacher.designation',
            'teacher.department'
        ])
            ->where('session_id', $sessionId)
            ->where('exam_type_id', $examTypeId)
            ->where('rate_head_id', $rateHeadId)
            ->get();


        // Log the data fetched in pretty-printed JSON format
        Log::info("📘 Teachers fetched from committee:", ['data' => json_encode($data->toArray(), JSON_PRETTY_PRINT)]);


        return $data;
    }

    public static function getTeacherWithCourse($sessionId, $examTypeId, $rateHeadId)
    {
        Log::info('📥 getTeacherWithCourse() input received', [
            'session_id' => $sessionId,
            'exam_type_id' => $examTypeId,
            'rate_head_id' => $rateHeadId,
        ]);
        $data = self::where('session_id', $sessionId)
            ->where('exam_type_id', $examTypeId)
            ->where('rate_head_id', $rateHeadId)
            ->get()
            ->groupBy('course_code');

        Log::info("📘 getTeacherWithCourse() grouped results:\n" . json_encode([
                'session_id' => $sessionId,
                'exam_type_id' => $examTypeId,
                'rate_head_id' => $rateHeadId,
                'grouped_keys' => $data->keys()->toArray(),
                'full_grouped_data' => $data->map->toArray(),
            ], JSON_PRETTY_PRINT));

        return $data;
    }
  
    
   public static function getTeacherWithGroup($sessionId, $examTypeId, $rateHeadId)
    {
        Log::info('📥 getTeacherWithGroup() input received', [
            'session_id' => $sessionId,
            'exam_type_id' => $examTypeId,
            'rate_head_id' => $rateHeadId,
        ]);

        $records = self::with([
            'teacher.user',
            'teacher.designation',
            'teacher.department'
        ])
            ->where('session_id', $sessionId)
            ->where('exam_type_id', $examTypeId)
            ->where('rate_head_id', $rateHeadId)
            ->get();

        if ($records->isEmpty()) {
            Log::info("📘 No records found for getTeacherWithGroup()");
            return (object) [
                'grouped_keys' => [],
                'full_grouped_data' => []
            ];
        }

        // Group by group_no, but if group_no is null, create artificial groups
        $grouped = $records->groupBy(function($item) {
            if (!is_null($item->group_no)) {
                return $item->group_no;
            }

            // If group_no is null, create artificial groups based on total_students
            // This will group teachers with the same student count together
           /* return 'group_' . ($item->total_students ?? 'default');*/
        });

        $result = [];
        $groupedKeys = [];
        $groupIndex = 1;

        foreach ($grouped as $groupKey => $items) {
            // For artificial groups, use numeric keys for consistency
            if (strpos($groupKey, 'group_') === 0) {
                $finalKey = $groupIndex;
                $groupIndex++;
            } else {
                $finalKey = $groupKey;
            }

            $groupedKeys[] = $finalKey;
            $result[$finalKey] = $items->toArray();
        }

        Log::info("📘 getTeacherWithGroup() grouped results:\n" . json_encode([
                'session_id' => $sessionId,
                'exam_type_id' => $examTypeId,
                'rate_head_id' => $rateHeadId,
                'grouped_keys' => $groupedKeys,
                'record_count' => $records->count(),
                'groups_count' => count($result),
                'raw_groups' => array_keys($grouped->toArray())
            ], JSON_PRETTY_PRINT));

        // Return in the expected format
        return (object) [
            'grouped_keys' => $groupedKeys,
            'full_grouped_data' => $result
        ];
    }
  
    public static function getInternalExternalTeacher($sessionId, $examTypeId, $rateHeadId)
    {
        // Pull only rows that have a real group_no
        $records = self::query()
            ->with(['teacher.user','teacher.designation','teacher.department']) // optional; remove if not needed
            ->where('session_id', $sessionId)
            ->where('exam_type_id', $examTypeId)
            ->where('rate_head_id', $rateHeadId)
            ->whereNotNull('group_no')
            ->orderBy('group_no')
            ->orderBy('id')
            ->get();

        if ($records->isEmpty()) {
            return [];
        }

        // Group strictly by group_no and shape for Blade prefill
        $rows = $records->groupBy('group_no')->map(function ($items, $groupNo) {
            $internalIds   = $items->where('is_internal', 1)->pluck('teacher_id')->unique()->values()->all();
            $externalIds   = $items->where('is_external', 1)->pluck('teacher_id')->unique()->values()->all();
            $studentCount  = (int) ($items->first()->total_students ?? 0); // per-row total stored at save time

            return [
                'group_no'             => (int) $groupNo,
                'internal_teacher_ids' => $internalIds,
                'external_teacher_ids' => $externalIds,
                'student_count'        => $studentCount,
            ];
        })
            ->values()     // drop group keys, keep array of rows
            ->toArray();

        return $rows;
    }
}