<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $fashionCategories = ['kaos', 'jaket', 'kemeja', 'celana', 'hoodie'];
        $dailyCategories   = ['sembako', 'makanan', 'minuman', 'kebersihan', 'perawatan'];

        // Kirim semua produk aktif — filtering dilakukan oleh JavaScript di frontend
        $fashionProducts   = Product::where('is_active', 1)
            ->whereIn('category', $fashionCategories)
            ->orderBy('category')
            ->get();

        $dailyProducts     = Product::where('is_active', 1)
            ->whereIn('category', $dailyCategories)
            ->orderBy('category')
            ->get();

        $fashionCategoryList = $fashionProducts->pluck('category')->unique()->values();
        $dailyCategoryList   = $dailyProducts->pluck('category')->unique()->values();

        // Backward compat
        $products   = $fashionProducts;
        $categories = $fashionCategoryList;

        return view('landing', compact(
            'fashionProducts', 'fashionCategoryList',
            'dailyProducts',   'dailyCategoryList',
            'products',        'categories'
        ));
    }
}
