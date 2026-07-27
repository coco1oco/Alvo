<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
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
     * @param  StoreCategoryRequest $request the incoming HTTP request
     * @return JsonResponse
     */
    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $data = $request->validated();

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
     * @param  UpdateCategoryRequest $request  the incoming HTTP request
     * @param  Category              $category the category to update
     * @return JsonResponse
     */
    public function update(UpdateCategoryRequest $request, Category $category): JsonResponse
    {
        abort_if($category->user_id !== $request->user()->id, 403);

        $data = $request->validated();

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
