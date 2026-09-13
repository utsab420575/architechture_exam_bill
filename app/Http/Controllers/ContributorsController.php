<?php

namespace App\Http\Controllers;

use App\Models\Contributor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Illuminate\Support\Facades\DB;

class ContributorsController extends Controller
{
    //showing contributors in blade
    public function contributors_view()
    {
        $contributors = Contributor::with('user')
            ->orderBy('order')        // lowest order first
            ->orderBy('sequence')     // then by sequence
            ->get();

        //return $contributors;

        return view('contributors.contributors_view', compact('contributors'));
    }







    // List all contributors (table like your sessions list)
    public function index()
    {
        $contributors = Contributor::with('user')->orderByDesc('id')->get();
        return view('contributors.contributors_index', compact('contributors'));
    }

    // Show add form
    public function create()
    {
        return view('contributors.contributors_create');
    }

    // Store new contributor
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name'        => ['required', 'string', 'max:255'],
                'designation' => ['required', 'string', 'max:255'],
                'profile'     => ['nullable', 'string', 'max:1000'],
                'speech'      => ['nullable', 'string', 'max:2000'],
                'photo'       => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
                'order'    => ['required', 'integer', 'min:0'],
                'sequence' => ['required', 'integer', 'min:0'],
            ]);

            // Upload photo (if any)
            $photoPath = null;

            if ($request->file('photo')) {
                $recive_image = $request->file('photo');
                $name_gen  = 'contrib_' . Str::random(8) . '_' . time() . '.' . $recive_image->getClientOriginalExtension();

                $manager = new ImageManager(new Driver());
                $image = $manager->read($recive_image)->resize(300, 300);
                $path ='upload/contributors/';

                if (!file_exists($path)) {
                    mkdir($path, 0777, true);
                }
                $image->toJpeg()->save($path . $name_gen);
                $photoPath = $path . $name_gen;
            }

            DB::beginTransaction(); // <-- added
            Contributor::create([
                'name'        => $validated['name'],
                'designation' => $validated['designation'],
                'profile'     => $validated['profile'] ?? null,
                'speech'      => $validated['speech'] ?? null,
                'order'    => $validated['order'] ?? null,
                'sequence' => $validated['sequence'] ?? null,
                'photo'       => $photoPath,
                /*'user_id'     => Auth::id(),*/
            ]);
            DB::commit(); // <-- added

            return redirect()->route('contributors.all')->with([
                'message' => 'Contributor added successfully!',
                'alert-type' => 'success'
            ]);
        } catch (\Exception $e) {
            DB::rollBack(); // <-- added
            return back()->withInput()->with([
                'message' => 'Error adding contributor: ' . $e->getMessage(),
                'alert-type' => 'error'
            ]);
        }
    }

    // Show edit form
    public function edit($id)
    {
        $contributor = Contributor::findOrFail($id);
        return view('contributors.contributors_edit', compact('contributor'));
    }

    // Update contributor
    public function update(Request $request)
    {
        try {
            $validated = $request->validate([
                'id'          => ['required', 'integer', 'exists:contributors,id'],
                'name'        => ['required', 'string', 'max:255'],
                'designation' => ['required', 'string', 'max:255'],
                'profile'     => ['nullable', 'string', 'max:1000'],
                'speech'      => ['nullable', 'string', 'max:2000'],
                'photo'       => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
                'order'    => ['required', 'integer', 'min:0'],
                'sequence' => ['required', 'integer', 'min:0'],
            ]);

            $single_contributor = Contributor::findOrFail($validated['id']);

            // Replace photo if a new one uploaded
            if ($request->file('photo')) {
                // delete old if exists
                if (!empty($single_contributor->photo) && file_exists(public_path($single_contributor->photo))) {
                    @unlink(public_path($single_contributor->photo));
                }
                $recive_image     = $request->file('photo');
                $name_gen  = 'contrib_' . Str::random(8) . '_' . time() . '.' . $recive_image->getClientOriginalExtension();

                $manager = new ImageManager(new Driver());
                $image = $manager->read($recive_image);
                $image->resize(300, 300);

                $path      = 'upload/contributors/';
                if (!file_exists($path)) {
                    mkdir($path, 0777, true);
                }

                $image->toJpeg()->save($path . $name_gen);
                $single_contributor->photo = $path . $name_gen;
            }

            DB::beginTransaction(); // <-- added
            $single_contributor->name        = $validated['name'];
            $single_contributor->designation = $validated['designation'];
            $single_contributor->profile     = $validated['profile'] ?? null;
            $single_contributor->speech      = $validated['speech'] ?? null;
            $single_contributor->user_id     = Auth::id();
            $single_contributor->order     =$validated['order'] ?? null;
            $single_contributor->sequence     = $validated['sequence'] ?? null;

            $single_contributor->save();
            DB::commit(); // <-- added

            return redirect()->route('contributors.all')->with([
                'message' => 'Contributor updated successfully!',
                'alert-type' => 'success'
            ]);
        } catch (\Exception $e) {
            DB::rollBack(); // <-- added
            return back()->withInput()->with([
                'message' => 'Error updating contributor: ' . $e->getMessage(),
                'alert-type' => 'error'
            ]);
        }
    }

    // Delete contributor
    public function destroy($id)
    {
        try {
            $row = Contributor::findOrFail($id);
            if (!empty($row->photo) && file_exists(public_path($row->photo))) {
                @unlink(public_path($row->photo));
            }

            DB::beginTransaction(); // <-- added
            $row->delete();
            DB::commit(); // <-- added

            return redirect()->route('contributors.all')->with([
                'message' => 'Contributor deleted successfully!',
                'alert-type' => 'success'
            ]);
        } catch (\Exception $e) {
            DB::rollBack(); // <-- added
            return back()->with([
                'message' => 'Error deleting contributor: ' . $e->getMessage(),
                'alert-type' => 'error'
            ]);
        }
    }
}
