<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Country;
use Illuminate\Http\Request;

class FrontController extends Controller
{
    public function index()
    {
        $featuredProducts = Product::where('status', 1)
                                  ->where('is_featured', 1)
                                  ->with(['productImages'])
                                  ->take(8)
                                  ->get();

        $latestProducts = Product::where('status', 1)
                                ->with(['productImages'])
                                ->orderBy('id', 'desc')
                                ->take(8)
                                ->get();

        return view('front.home', compact('featuredProducts', 'latestProducts'));
    }

    public function shop(Request $request, $categorySlug = null, $subCategorySlug = null)
    {
        $categorySelected = '';
        $subCategorySelected = '';
        $brandsArray = [];

        $categories = Category::where('status', 1)->with('sub_category')->orderBy('name', 'asc')->get();
        $brands = Brand::where('status', 1)->orderBy('name', 'asc')->get();

        $products = Product::where('status', 1);

        // Filter by category
        if (!empty($categorySlug)) {
            $category = Category::where('slug', $categorySlug)->first();
            if ($category) {
                $products = $products->where('category_id', $category->id);
                $categorySelected = $category->id;
            }
        }

        // Filter by subcategory
        if (!empty($subCategorySlug)) {
            $subCategory = SubCategory::where('slug', $subCategorySlug)->first();
            if ($subCategory) {
                $products = $products->where('sub_category_id', $subCategory->id);
                $subCategorySelected = $subCategory->id;
            }
        }

        // Filter by brands
        if (!empty($request->get('brand'))) {
            $brandsArray = explode(',', $request->get('brand'));
            $products = $products->whereIn('brand_id', $brandsArray);
        }

        // Filter by price
        if ($request->get('price_max') != '' && $request->get('price_min') != '') {
            if ($request->get('price_max') == 1000) {
                $products = $products->whereBetween('price', [intval($request->get('price_min')), 1000000]);
            } else {
                $products = $products->whereBetween('price', [intval($request->get('price_min')), intval($request->get('price_max'))]);
            }
        }

        // Search
        if (!empty($request->get('search'))) {
            $products = $products->where('title', 'like', '%'.$request->get('search').'%');
        }

        // Sorting
        if ($request->get('sort') != '') {
            if ($request->get('sort') == 'latest') {
                $products = $products->orderBy('id', 'desc');
            } else if ($request->get('sort') == 'price_asc') {
                $products = $products->orderBy('price', 'asc');
            } else {
                $products = $products->orderBy('price', 'desc');
            }
        } else {
            $products = $products->orderBy('id', 'desc');
        }

        $products = $products->paginate(6);

        return view('front.shop', compact('categories', 'brands', 'products', 'categorySelected', 'subCategorySelected', 'brandsArray'));
    }

    public function product($slug)
    {
        $product = Product::where('slug', $slug)
                         ->with(['productImages', 'productRatings'])
                         ->first();

        if ($product == null) {
            abort(404);
        }

        $relatedProducts = [];
        if ($product->category_id != '') {
            $relatedProducts = Product::where('category_id', $product->category_id)
                                    ->where('id', '!=', $product->id)
                                    ->with('productImages')
                                    ->take(8)
                                    ->get();
        }

        return view('front.product', compact('product', 'relatedProducts'));
    }

    public function cart()
    {
        return view('front.cart');
    }

    public function checkout()
    {
        $countries = Country::orderBy('name', 'asc')->get();
        return view('front.checkout', compact('countries'));
    }

    public function thanks($orderId)
    {
        return view('front.thanks', compact('orderId'));
    }

    public function addToCart(Request $request)
    {
        $product = Product::with('productImages')->find($request->id);

        if ($product == null) {
            return response()->json([
                'status' => false,
                'message' => 'Product not found'
            ]);
        }

        if (\Cart::count() > 0) {
            $cartContent = \Cart::content();
            $productAlreadyExist = false;

            foreach ($cartContent as $item) {
                if ($item->id == $product->id) {
                    $productAlreadyExist = true;
                }
            }

            if ($productAlreadyExist == false) {
                \Cart::add($product->id, $product->title, 1, $product->price, ['productImage' => (!empty($product->productImages)) ? $product->productImages->first() : '']);
                $status = true;
                $message = '<strong>'.$product->title.'</strong> added in your cart successfully.';
                session()->flash('success', $message);
            } else {
                $status = false;
                $message = $product->title.' already added in cart';
            }
        } else {
            \Cart::add($product->id, $product->title, 1, $product->price, ['productImage' => (!empty($product->productImages)) ? $product->productImages->first() : '']);
            $status = true;
            $message = '<strong>'.$product->title.'</strong> added in your cart successfully.';
            session()->flash('success', $message);
        }

        return response()->json([
            'status' => $status,
            'message' => $message
        ]);
    }

    public function updateCart(Request $request)
    {
        $rowId = $request->rowId;
        $qty = $request->qty;

        $itemInfo = \Cart::get($rowId);

        $product = Product::find($itemInfo->id);
        if ($product->track_qty == 1) {
            if ($qty <= $product->qty) {
                \Cart::update($rowId, $qty);
                $message = 'Cart updated successfully';
                $status = true;
                session()->flash('success', $message);
            } else {
                $message = 'Requested qty('.$qty.') not available in stock.';
                $status = false;
                session()->flash('error', $message);
            }
        } else {
            \Cart::update($rowId, $qty);
            $message = 'Cart updated successfully';
            $status = true;
            session()->flash('success', $message);
        }

        return response()->json([
            'status' => $status,
            'message' => $message
        ]);
    }

    public function deleteItem(Request $request)
    {
        $itemInfo = \Cart::get($request->rowId);

        if ($itemInfo == null) {
            $errorMessage = 'Item not found in cart';
            session()->flash('error', $errorMessage);
            return response()->json([
                'status' => false,
                'message' => $errorMessage
            ]);
        }

        \Cart::remove($request->rowId);

        $message = 'Item removed from cart successfully';
        session()->flash('success', $message);

        return response()->json([
            'status' => true,
            'message' => $message
        ]);
    }
}
