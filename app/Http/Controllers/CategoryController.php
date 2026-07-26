<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends AbstractController
{
    /**
     * List all categories for the authenticated user.
     *
     * @param  Request $request the incoming HTTP request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $categories = $request->user()
            ->categories()
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        return response()->json($categories);
    }

    /**
     * Create a new category for the authenticated user.
     *
     * @param  Request $request the incoming HTTP request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'  => 'required|string|max:100',
            'type'  => 'required|in:income,expense',
            'color' => 'nullable|string|max:7',
            'icon'  => 'nullable|string|max:50',
        ]);

        $category = $request->user()->categories()->create([
            'name'  => $data['name'],
            'type'  => $data['type'],
            'color' => $data['color'] ?? '#6366f1',
            'icon'  => $data['icon'] ?? 'tag',
        ]);

        return response()->json($category, 201);
    }

    /**
     * Update an existing category.
     *
     * @param  Request  $request  the incoming HTTP request
     * @param  Category $category the category to update
     * @return JsonResponse
     */
    public function update(Request $request, Category $category): JsonResponse
    {
        abort_if($category->user_id !== $request->user()->id, 403);

        $data = $request->validate([
            'name'  => 'sometimes|required|string|max:100',
            'type'  => 'sometimes|required|in:income,expense',
            'color' => 'sometimes|nullable|string|max:7',
            'icon'  => 'sometimes|nullable|string|max:50',
        ]);

        $category->update($data);

        return response()->json($category);
    }

    /**
     * Delete a category.
     *
     * @param  Request  $request  the incoming HTTP request
     * @param  Category $category the category to delete
     * @return JsonResponse
     */
    public function destroy(Request $request, Category $category): JsonResponse
    {
        abort_if($category->user_id !== $request->user()->id, 403);

        $category->delete();

        return response()->json(['message' => 'Category deleted']);
    }
}
