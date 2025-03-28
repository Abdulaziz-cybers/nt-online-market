<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Image;
use App\Models\Product;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Volume;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

class ProductsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('user.index', [
            'users' => DB::table('users')->paginate(15)
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */

    public function show(Request $request)
    {
        $categories = $request->input('categories');
        $weights = $request->input('weights');
        $startPrice = $request->input('startPrice');
        $endPrice = $request->input('endPrice');

        $selectedCategories = Category::whereIn('name', (array) $categories)->pluck('id')->toArray();

        $categoryIds = Category::whereIn('parent_id', $selectedCategories)
            ->orWhereIn('id', $selectedCategories)
            ->pluck('id')
            ->toArray();

        $parentCategories = Category::whereNull('parent_id')
            ->orderBy('id', 'desc')
            ->limit(4)
            ->with('categories')
            ->get();

        $productsMenu = Category::whereNull('parent_id')
            ->orderBy('id', 'desc')
            ->with('categories')
            ->get();

        $products = Product::query()
            ->when(!empty($categoryIds), function ($query) use ($categoryIds) {
                return $query->whereIn('category_id', $categoryIds);
            })
            ->when($weights, function ($query) use ($weights) {
                $productVolumes = Volume::whereIn('name', $weights)->pluck('id');
                return $query->whereIn('volume_id', $productVolumes);
            })
            ->when(isset($startPrice) && isset($endPrice), function ($query) use ($startPrice, $endPrice) {
                return $query->whereBetween('price', [$startPrice, $endPrice]);
            })
            ->orderBy('id', 'desc')
            ->with('images')
            ->paginate(10);

        $categories = Category::all();

        $images = Image::paginate(1);
        $weights = Volume::all(); // **Shu qatorni qo‘sh!**

        return view('product-filter', [
            'products' => $products,
            'parentCategories' => $parentCategories,
            'productsMenu' => $productsMenu,
            'categories' => $categories,
            'images'=>$images,
            'weights' => $weights
        ]);
    }



    public function likeProduct($productId)
    {
        if (Auth::check()) {
            $user = Auth::user();
            $user->likedProducts()->attach($productId);
        } else {
            $likedProducts = Session::get('liked_products', []);
            if (!in_array($productId, $likedProducts)) {
                $likedProducts[] = $productId;
                Session::put('liked_products', $likedProducts);
            }
        }
        return response()->json(['success' => true]); // JavaScript uchun javob
    }

}
