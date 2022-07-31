<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProductController extends Controller
{
    public function all(Request $request)
    {
        $id = $request->input('id');
        $limit = $request->input('limit');
        $name = $request->input('name');
        $description = $request->input('description');
        $tags = $request->input('tags');
        $categories = $request->input('categories');
        $price_from = $request->input('price_from');
        $price_to = $request->input('price_to');

        if ($id) {
            $product = Product::with(['category', 'galleries'])->find($id);

            if ($product) {
                return ResponseFormatter::success($product, 'Get product success');
            } else {
                return ResponseFormatter::error(null, 'Get product is empty', 404);
            }
        }

        $products = Product::with(['category', 'galleries']);

        if ($name) {
            $products->where('name', 'like', '%' . $name . '%');
        }

        if ($description) {
            $products->where('description', 'like', '%' . $description . '%');
        }

        if ($tags) {
            $products->where('tags', 'like', '%' . $tags . '%');
        }

        if ($price_from) {
            $products->where('price', '>=', $price_from);
        }

        if ($price_to) {
            $products->where('price', '<=', $price_to);
        }

        if ($categories) {
            $products->where('categories', $categories);
        }

        return ResponseFormatter::success($products->paginate($limit), 'Get list products success');
    }
}
