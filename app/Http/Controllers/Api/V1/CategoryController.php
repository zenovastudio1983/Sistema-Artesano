<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\Products\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class CategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $categories = Category::with('children')
            ->roots()
            ->active()
            ->ordered()
            ->get();

        return response()->json(['data' => $categories]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create categories');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'parent_id' => ['nullable', 'exists:categories,id'],
            'description' => ['nullable', 'string'],
            'color' => ['nullable', 'string', 'regex:/^#[0-9A-F]{6}$/i'],
            'icon' => ['nullable', 'string', 'max:50'],
        ]);

        $category = Category::create($validated);

        return response()->json([
            'message' => 'Categoría creada.',
            'data' => $category,
        ], 201);
    }

    public function show(Category $category): JsonResponse
    {
        $category->load(['parent', 'children', 'products' => fn($q) => $q->limit(20)]);

        return response()->json(['data' => $category]);
    }

    public function update(Request $request, Category $category): JsonResponse
    {
        $this->authorize('edit categories');

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'color' => ['nullable', 'string', 'regex:/^#[0-9A-F]{6}$/i'],
            'is_active' => ['boolean'],
        ]);

        $category->update($validated);

        return response()->json(['message' => 'Categoría actualizada.', 'data' => $category->fresh()]);
    }

    public function destroy(Category $category): JsonResponse
    {
        $this->authorize('delete categories');

        if ($category->products()->count() > 0) {
            return response()->json([
                'message' => 'No se puede eliminar una categoría con productos asignados.',
            ], 422);
        }

        $category->delete();

        return response()->json(['message' => 'Categoría eliminada.']);
    }
}
