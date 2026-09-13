<?php

namespace App\Http\Controllers;

use App\Models\RateHead;
use Illuminate\Http\Request;

class RateHeadController extends Controller
{
    public function AllRateHead()
    {
        $rate_heads = RateHead::with('mergedWith')->orderBy('id', 'desc')->get();
        return view('system_setting.rate_head.all_rate_head', compact('rate_heads'));
    }

    public function AddRateHead()
    {
        $parent_rate_heads = RateHead::orderBy('head', 'asc')->get();
        return view('system_setting.rate_head.add_rate_head', compact('parent_rate_heads'));
    }

    public function StoreRateHead(Request $request)
    {
        $request->validate([
            'head'             => 'required|string|max:255',
            'sub_head'         => 'nullable|string|max:255',
            'order_no'         => 'required|string|max:255',
            'dist_type'        => 'required|string|max:255',
            'enable_min'       => 'nullable|boolean',
            'enable_max'       => 'nullable|boolean',
            'is_course'        => 'nullable|boolean',
            'is_student_count' => 'nullable|boolean',
            'marge_with'       => 'nullable|exists:rate_heads,id',
            'status'           => 'nullable|boolean',
        ]);

        RateHead::create([
            'head'             => $request->head,
            'sub_head'         => $request->sub_head,
            'order_no'         => $request->order_no,
            'dist_type'        => $request->dist_type,
            'enable_min'       => $request->has('enable_min') ? 1 : 0,
            'enable_max'       => $request->has('enable_max') ? 1 : 0,
            'is_course'        => $request->has('is_course') ? 1 : 0,
            'is_student_count' => $request->has('is_student_count') ? 1 : 0,
            'marge_with'       => $request->marge_with ?? null,
            'status'           => $request->has('status') ? 1 : 0,
        ]);

        $notification = [
            'message'    => 'Rate Head Added Successfully',
            'alert-type' => 'success',
        ];

        return redirect()->route('rate_head.all')->with($notification);
    }

    public function EditRateHead($id)
    {
        $rate_head = RateHead::findOrFail($id);
        $parent_rate_heads = RateHead::where('id', '!=', $id)->orderBy('head', 'asc')->get();
        return view('system_setting.rate_head.edit_rate_head', compact('rate_head', 'parent_rate_heads'));
    }

    public function UpdateRateHead(Request $request)
    {
        $id = $request->id;

        $request->validate([
            'head'             => 'required|string|max:255',
            'sub_head'         => 'nullable|string|max:255',
            'order_no'         => 'required|string|max:255',
            'dist_type'        => 'required|string|max:255',
            'enable_min'       => 'nullable|boolean',
            'enable_max'       => 'nullable|boolean',
            'is_course'        => 'nullable|boolean',
            'is_student_count' => 'nullable|boolean',
            'marge_with'       => 'nullable|exists:rate_heads,id',
            'status'           => 'nullable|boolean',
        ]);

        $rate_head = RateHead::findOrFail($id);
        $rate_head->update([
            'head'             => $request->head,
            'sub_head'         => $request->sub_head,
            'order_no'         => $request->order_no,
            'dist_type'        => $request->dist_type,
            'enable_min'       => $request->has('enable_min') ? 1 : 0,
            'enable_max'       => $request->has('enable_max') ? 1 : 0,
            'is_course'        => $request->has('is_course') ? 1 : 0,
            'is_student_count' => $request->has('is_student_count') ? 1 : 0,
            'marge_with'       => $request->marge_with ?? null,
            'status'           => $request->has('status') ? 1 : 0,
        ]);

        $notification = [
            'message'    => 'Rate Head Updated Successfully',
            'alert-type' => 'success',
        ];

        return redirect()->route('rate_head.all')->with($notification);
    }

    public function DeleteRateHead($id)
    {
        RateHead::findOrFail($id)->delete();

        $notification = [
            'message'    => 'Rate Head Deleted Successfully',
            'alert-type' => 'success',
        ];

        return redirect()->route('rate_head.all')->with($notification);
    }
}
