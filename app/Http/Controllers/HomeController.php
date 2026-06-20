<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::where('is_active', 1);

        if ($request->has('category') && $request->category != '') {
            $query->where('category', $request->category);
        }

        $products = $query->get();
        $categories = Product::where('is_active', 1)->select('category')->distinct()->pluck('category');

        return view('landing', compact('products', 'categories'));
    }
}
