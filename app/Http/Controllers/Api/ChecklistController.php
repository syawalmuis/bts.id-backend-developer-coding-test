<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ChecklistRequest;
use App\Models\Checklist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Http\Middleware\CheckAbilities;

class ChecklistController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $checklists = Checklist::with('items.childrens')->where('user_id', Auth::user()->id)->get();
            return response()->json([
                'data' => $checklists,
                'message' => 'Total checklists: ' . $checklists->count(),
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Checklists not found',
            ], 404);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ChecklistRequest $request)
    {
        $validated = $request->safe()->only('name', 'description');
        $validated['user_id'] = Auth::user()->id;

        DB::beginTransaction();
        try {
            if ($request->hasFile('cover')) {
                $validated['cover'] = $request->file('cover')->store('checklists');
            }
            $checklist = Checklist::create($validated);
            DB::commit();

            return response()->json([
                'data' => $checklist,
                'message' => 'Checklist created successfully',
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
            return response()->json([
                'message' => 'Checklist not created',
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $checklist = Checklist::with('items.childrens')->where('user_id', Auth::user()->id)->findOrFail($id);
            return response()->json([
                'data' => $checklist,
                'message' => 'Checklist found',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Checklist not found',
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ChecklistRequest $request, string $id)
    {
        $validated = $request->safe()->only('name', 'description');
        DB::beginTransaction();
        try {
            $checklist = Checklist::where('user_id', Auth::user()->id)->findOrFail($id);

            if ($request->hasFile('cover')) {
                if ($checklist->cover) {
                    Storage::delete($checklist->cover);
                }
                $validated['cover'] = $request->file('cover')->store('checklists');
            }
            $checklist->update($validated);
            DB::commit();
            return response()->json([
                'data' => $checklist,
                'message' => 'Checklist updated successfully',
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => 'Checklist not updated',
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        DB::beginTransaction();
        try {
            $checklist = Checklist::where('user_id', Auth::user()->id)->findOrFail($id);
            if ($checklist->cover) {
                Storage::delete($checklist->cover);
            }
            $checklist->delete();
            DB::commit();
            return response()->json([
                'message' => 'Checklist deleted successfully',
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => 'Checklist not deleted',
            ], 500);
        }
    }
}
