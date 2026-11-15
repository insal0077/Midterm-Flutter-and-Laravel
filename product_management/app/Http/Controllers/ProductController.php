<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    private $rules = [
        'ProductName'  => 'required|string|max:100',
        'Description'  => 'nullable|string|max:255',
        'CategoryID'   => 'required|integer|exists:categories,category_id',
        'Barcode'      => 'nullable|string|max:255',
        'Expiredate'   => 'nullable|date',
        'Qty'          => 'required|integer|min:0',
        'UnitPriceIn'  => 'required|numeric|min:0',
        'UnitPriceOut' => 'required|numeric|min:0',
        'ProductImage' => 'nullable|image|max:2048', // image file (jpg, png, ...) up to 2 MB
    ];

    private $fields = [
        'ProductName', 'Description', 'CategoryID', 'Barcode', 'Expiredate',
        'Qty', 'UnitPriceIn', 'UnitPriceOut',
    ];

    // GET /api/products - display all products that are not deleted
    public function index()
    {
        $products = Product::with('category')
            ->where('isDeleted', 0)
            ->orderBy('ProductID', 'desc')
            ->get();

        return response()->json($products);
    }

    // POST /api/products - insert a new product (sent as form-data because of the image)
    public function store(Request $request)
    {
        $request->validate($this->rules);

        $data = $request->only($this->fields);

        if ($request->hasFile('ProductImage')) {
            // Saved in storage/app/public/products, path stored in the ProductImage column
            $data['ProductImage'] = $request->file('ProductImage')->store('products', 'public');
        }

        $product = Product::create($data);

        return response()->json([
            'message' => 'Product created successfully',
            'data'    => $product,
        ], 201);
    }

    // GET /api/products/{id} - display one product
    public function show($id)
    {
        $product = Product::with('category')
            ->where('isDeleted', 0)
            ->find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        return response()->json($product);
    }

    // PUT /api/products/{id} - update a product
    // With an image file, send POST + form-data with _method=PUT (PHP cannot read files on a real PUT).
    public function update(Request $request, $id)
    {
        $product = Product::where('isDeleted', 0)->find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $request->validate($this->rules);

        $data = $request->only($this->fields);

        // Only replace the image when a new file is uploaded
        if ($request->hasFile('ProductImage')) {
            if ($product->ProductImage) {
                Storage::disk('public')->delete($product->ProductImage);
            }
            $data['ProductImage'] = $request->file('ProductImage')->store('products', 'public');
        }

        $product->update($data);

        return response()->json([
            'message' => 'Product updated successfully',
            'data'    => $product,
        ]);
    }

    // DELETE /api/products/{id} - delete a product (sets isDeleted = 1)
    public function destroy($id)
    {
        $product = Product::where('isDeleted', 0)->find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $product->isDeleted = 1;
        $product->save();

        return response()->json(['message' => 'Product deleted successfully']);
    }
}
