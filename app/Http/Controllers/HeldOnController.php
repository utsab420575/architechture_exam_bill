<?php

namespace App\Http\Controllers;

use App\Models\ExamType;
use App\Models\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class HeldOnController extends Controller
{
    /**
     * Show all active Regular sessions for held-on date entry.
     */
    public function regularSession()
    {
        $exam_type = ExamType::where('type', 'Regular')->first();
        $sessions = Session::where('exam_type_id', $exam_type->id)
            ->where('status', 1)
            ->orderBy('session')
            ->orderBy('year')
            ->orderBy('semester')
            ->get();

        return view('held_on.regular_session', compact('sessions'));
    }

    /**
     * Show all active Review sessions for held-on date entry.
     */
    public function reviewSession()
    {
        $exam_type = ExamType::where('type', 'Review')->first();
        $sessions = Session::where('exam_type_id', $exam_type->id)
            ->where('status', 1)
            ->orderBy('session')
            ->orderBy('year')
            ->orderBy('semester')
            ->get();

        return view('held_on.review_session', compact('sessions'));
    }

    /**
     * Show all active Special sessions for held-on date entry.
     */
    public function specialSession()
    {
        $exam_type = ExamType::where('type', 'Special')->first();
        $sessions = Session::where('exam_type_id', $exam_type->id)
            ->where('status', 1)
            ->orderBy('session')
            ->orderBy('year')
            ->orderBy('semester')
            ->get();

        return view('held_on.special_session', compact('sessions'));
    }

    /**
     * Save held_on dates for all submitted sessions.
     */
    public function store(Request $request)
    {
        $heldOnData = $request->input('held_on', []);

        $updated = 0;
        foreach ($heldOnData as $sessionId => $date) {
            if ($date) {
                Session::where('id', $sessionId)->update(['held_on' => $date]);
                $updated++;
            }
        }

        Log::info("✅ Held On dates updated for $updated sessions.");

        return redirect()->back()->with('success', "Held On dates saved successfully for $updated session(s).");
    }
}
