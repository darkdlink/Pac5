<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Show the application home page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // Busca produtos em destaque (featured)
        $featuredProducts = Product::where('is_featured', true)
            ->where('is_active', true)
            ->take(4)
            ->get();

        return view('home.index', compact('featuredProducts'));
    }
}
