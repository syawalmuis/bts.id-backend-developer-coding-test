<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ChecklistItemRequest;
use App\Http\Resources\ChecklistItemResource;
use App\Models\ChecklistItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChecklistItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(string $checklistId)
    {
        try {
            $checklistItems = ChecklistItem::with('children')->where('checklist_id', $checklistId)->whereNull('parent_id')->get();
            return response()->json([
                'data' => $checklistItems,
                'message' => 'Total checklist items: ' . $checklistItems->count(),
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Checklist items not found',
            ], 404);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ChecklistItemRequest $request, string $checklistId)
    {
        $validated = $request->validated();
        $validated['checklist_id'] = $checklistId;

        DB::beginTransaction();
        try {
            $checklistItem = ChecklistItem::create($validated);
            DB::commit();
            return response()->json([
                'data' => $checklistItem,
                'message' => 'Checklist item created successfully',
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => 'Checklist item not created',
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $checklistItem = ChecklistItem::with('parent', 'children', 'checklist')->findOrFail($id);
            return response()->json([
                'data' => $checklistItem,
                'message' => 'Checklist item found',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Checklist item not found',
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ChecklistItemRequest $request, string $checklistId, string $id)
    {
        $validated = $request->validated();

        DB::beginTransaction();
        try {
            $checklistItem = ChecklistItem::findOrFail($id);
            $checklistItem->update($validated);
            DB::commit();

            return response()->json([
                'data' => $checklistItem,
                'message' => 'Checklist item updated successfully',
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => 'Checklist item not updated',
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
            $checklistItem = ChecklistItem::findOrFail($id);
            $parent = $checklistItem->parent;
            $checklistItem->delete();

            if ($parent) {
                $parent->is_completed = $parent->children->every(function ($item) {
                    return $item->is_completed;
                });
                $parent->save();
            }

            DB::commit();
            return response()->json([
                'message' => 'Checklist item deleted successfully',
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => 'Checklist item not deleted',
            ], 500);
        }
    }

    /**
     * toggle comple specific resource.
     */

    public function toggleComplete(string $id)
    {
        DB::beginTransaction();
        try {
            $checklistItem = ChecklistItem::findOrFail($id);
            $checklistItem->is_completed = !$checklistItem->is_completed;
            $checklistItem->save();

            // update children
            if ($checklistItem->children->count() > 0) {
                $checklistItem->children()->update(['is_completed' => $checklistItem->is_completed]);
            }

            if ($checklistItem->parent) {
                $checklistItem->parent->is_completed = $checklistItem->parent->children->every(function ($item) {
                    return $item->is_completed;
                });
                $checklistItem->parent->save();
            }


            DB::commit();
            return response()->json([
                'message' => $checklistItem->is_completed ? 'Checklist item completed' : 'Checklist item not completed',
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => 'Checklist item not toggled',
            ], 500);
        }
    }
}
