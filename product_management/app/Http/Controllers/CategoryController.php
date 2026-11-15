<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    // GET /api/categories - display all categories
    public function index()
    {
        $categories = Category::all();

        return response()->json($categories);
    }

    // POST /api/categories - insert a new category
    public function store(Request $request)
    {
        $request->validate([
            'category_name' => 'required|string|max:50',
            'description'   => 'nullable|string|max:255',
            'image'         => 'required|string|max:255',
        ]);

        $category = Category::create($request->only(['category_name', 'description', 'image']));

        return response()->json([
            'message' => 'Category created successfully',
            'data'    => $category,
        ], 201);
    }

    // GET /api/categories/{id} - display one category
    public function show($id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        return response()->json($category);
    }

    // PUT /api/categories/{id} - update a category
    public function update(Request $request, $id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        $request->validate([
            'category_name' => 'required|string|max:50',
            'description'   => 'nullable|string|max:255',
            'image'         => 'required|string|max:255',
        ]);

        $category->update($request->only(['category_name', 'description', 'image']));

        return response()->json([
            'message' => 'Category updated successfully',
            'data'    => $category,
        ]);
    }

    // DELETE /api/categories/{id} - delete a category
    public function destroy($id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        if ($category->products()->exists()) {
            return response()->json(['message' => 'Cannot delete: this category still has products'], 400);
        }

        $category->delete();

        return response()->json(['message' => 'Category deleted successfully']);
    }
}
