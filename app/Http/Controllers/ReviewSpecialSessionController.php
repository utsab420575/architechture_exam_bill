<?php

namespace App\Http\Controllers;

use App\Models\Session;
use Illuminate\Http\Request;

class ReviewSpecialSessionController extends Controller
{
    // Show the create form
    public function create()
    {
        $examTypes = [
            1 => 'Regular',
            2 => 'Review',
            3 => 'Special',
        ];

        return view('review_special.session_create', compact('examTypes'));
    }

    // Store the form
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'session'      => ['required', 'string', 'max:255'],
                'year'         => ['required', 'string', 'max:255'],
                'semester'     => ['required', 'string', 'max:255'],
                'exam_type_id' => ['required', 'integer', 'in:1,2,3'],
            ]);

            // check if already exists
            $exists = Session::where('session', $validated['session'])
                ->where('year', $validated['year'])
                ->where('semester', $validated['semester'])
                ->where('exam_type_id', $validated['exam_type_id'])
                ->where('status',1)
                ->exists();

            if ($exists) {
                $notification = [
                    'message' => 'This Session already exists (session + year + semester + exam type).',
                    'alert-type' => 'error'
                ];
                return redirect()->back()->withInput()->with($notification);
            }

            $validated['ugr_id'] = null;
            $validated['status'] = 1;

            Session::create($validated);

            $notification = [
                'message' => 'Session created successfully!',
                'alert-type' => 'success'
            ];

            return redirect()->route('session.add')->with($notification);

        } catch (\Exception $e) {
            $notification = [
                'message' => 'Error Creating Session: ' . $e->getMessage(),
                'alert-type' => 'error'
            ];
            return redirect()->back()->withInput()->with($notification);
        }
    }


    // List all sessions
    public function index()
    {
        // latest first
        $sessions = Session::orderByDesc('status')  // active (1) first
        ->orderByDesc('id')       // then latest first
        ->get();



        // map exam types same as your create()
        $examTypes = [
            1 => 'Regular',
            2 => 'Review',
            3 => 'Special',
        ];

        return view('review_special.session_index', compact('sessions', 'examTypes'));
    }

    // Edit form
    public function edit($id)
    {
        $session = Session::findOrFail($id);

        $examTypes = [
            1 => 'Regular',
            2 => 'Review',
            3 => 'Special',
        ];

        return view('review_special.session_edit', compact('session', 'examTypes'));
    }

    // Update
    public function update(Request $request)
    {
        try {
            $validated = $request->validate([
                'id'           => ['required', 'integer', 'exists:sessions,id'],
                'session'      => ['required', 'string', 'max:255'],
                'year'         => ['required', 'string', 'max:255'],
                'semester'     => ['required', 'string', 'max:255'],
                'exam_type_id' => ['required', 'integer', 'in:1,2,3'],
                'status' => ['required','integer','in:0,1'],
            ]);

            $row = Session::findOrFail($validated['id']);

            // uniqueness check excluding current row
            $exists = Session::where('session', $validated['session'])
                ->where('year', $validated['year'])
                ->where('semester', $validated['semester'])
                ->where('exam_type_id', $validated['exam_type_id'])
                ->whereNull('ugr_id')
                ->where('id', '<>', $row->id)
                ->exists();

            if ($exists) {
                $notification = [
                    'message' => 'This Session already exists (session + year + semester + exam type).',
                    'alert-type' => 'error'
                ];
                return redirect()->back()->withInput()->with($notification);
            }

            $row->update([
                'session'      => $validated['session'],
                'year'         => $validated['year'],
                'semester'     => $validated['semester'],
                'exam_type_id' => $validated['exam_type_id'],
                'status' => (int)$validated['status'],
            ]);

            $notification = [
                'message' => 'Session updated successfully!',
                'alert-type' => 'success'
            ];

            return redirect()->route('session.all')->with($notification);

        } catch (\Exception $e) {
            $notification = [
                'message' => 'Error Updating Session: ' . $e->getMessage(),
                'alert-type' => 'error'
            ];
            return redirect()->back()->withInput()->with($notification);
        }
    }

    // Delete
    public function destroy($id)
    {
        try {
            $row = Session::findOrFail($id);
            $row->delete();

            $notification = [
                'message' => 'Session deleted successfully!',
                'alert-type' => 'success'
            ];
            return redirect()->route('session.all')->with($notification);

        } catch (\Exception $e) {
            $notification = [
                'message' => 'Error Deleting Session: ' . $e->getMessage(),
                'alert-type' => 'error'
            ];
            return redirect()->back()->with($notification);
        }
    }

}
