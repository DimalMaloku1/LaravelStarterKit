<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Brand;
use App\Models\ProductImage;
use App\Models\TempImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $products = Product::with(['category', 'subCategory', 'brand'])->latest();

        if (!empty($request->get('keyword'))) {
            $products = $products->where('title','like','%'.$request->get('keyword').'%');
        }
        
        $products = $products->paginate(10);
        
        return view('admin.products.list', compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::orderBy('name', 'asc')->get();
        $brands = Brand::orderBy('name', 'asc')->get();
        
        return view('admin.products.create', compact('categories', 'brands'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'slug' => 'required|unique:products',
            'description' => 'required',
            'price' => 'required|numeric',
            'sku' => 'required|unique:products',
            'track_qty' => 'required|in:Yes,No',
            'category_id' => 'required|integer',
            'is_featured' => 'required|in:Yes,No',
        ]);

        if ($request->track_qty == 'Yes') {
            $validator->after(function ($validator) use ($request) {
                if (empty($request->qty)) {
                    $validator->errors()->add('qty', 'Qty is required when track quantity is Yes.');
                }
            });
        }

        if ($validator->passes()) {
            $product = new Product();
            $product->title = $request->title;
            $product->slug = $request->slug;
            $product->description = $request->description;
            $product->short_description = $request->short_description;
            $product->shipping_returns = $request->shipping_returns;
            $product->price = $request->price;
            $product->compare_price = $request->compare_price;
            $product->sku = $request->sku;
            $product->barcode = $request->barcode;
            $product->track_qty = ($request->track_qty == 'Yes') ? 1 : 0;
            $product->qty = $request->qty;
            $product->status = ($request->status == 1) ? 1 : 0;
            $product->category_id = $request->category_id;
            $product->sub_category_id = $request->sub_category_id;
            $product->brand_id = $request->brand_id;
            $product->is_featured = ($request->is_featured == 'Yes') ? 1 : 0;
            $product->save();

            // Save product images
            if (!empty($request->image_array)) {
                foreach ($request->image_array as $temp_image_id) {
                    
                    $tempImageInfo = TempImage::find($temp_image_id);
                    $extArray = explode('.', $tempImageInfo->name);
                    $ext = last($extArray);

                    $productImage = new ProductImage();
                    $productImage->product_id = $product->id;
                    $productImage->image = 'NULL';
                    $productImage->save();

                    $imageName = $product->id.'-'.$productImage->id.'-'.time().'.'.$ext;
                    $productImage->image = $imageName;
                    $productImage->save();

                    // Generate Product Images

                    // Large Image
                    $sourcePath = public_path().'/temp/'.$tempImageInfo->name;
                    $destPath = public_path().'/uploads/product/large/'.$imageName;
                    $image = Image::make($sourcePath);
                    $image->resize(1400, null, function ($constraint) {
                        $constraint->aspectRatio();
                    });
                    $image->save($destPath);

                    // Small Image
                    $destPath = public_path().'/uploads/product/small/'.$imageName;
                    $image = Image::make($sourcePath);
                    $image->fit(300, 300);
                    $image->save($destPath);
                }
            }

            session()->flash('success', 'Product added successfully');

            return response()->json([
                'status' => true,
                'message' => 'Product added successfully'
            ]);

        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($productId, Request $request)
    {
        $product = Product::find($productId);
        
        if (empty($product)) {
            return redirect()->route('products.index')->with('error', 'Product not found');
        }

        $productImages = ProductImage::where('product_id', $product->id)->get();
        $subCategories = SubCategory::where('category_id', $product->category_id)->get();
        $categories = Category::orderBy('name', 'asc')->get();
        $brands = Brand::orderBy('name', 'asc')->get();

        return view('admin.products.edit', compact('product', 'categories', 'brands', 'subCategories', 'productImages'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update($productId, Request $request)
    {
        $product = Product::find($productId);

        if (empty($product)) {
            session()->flash('error', 'Product not found');
            return response()->json([
                'status' => false,
                'notFound' => true,
                'message' => 'Product not found'
            ]);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'slug' => 'required|unique:products,slug,'.$product->id,
            'description' => 'required',
            'price' => 'required|numeric',
            'sku' => 'required|unique:products,sku,'.$product->id,
            'track_qty' => 'required|in:Yes,No',
            'category_id' => 'required|integer',
            'is_featured' => 'required|in:Yes,No',
        ]);

        if ($request->track_qty == 'Yes') {
            $validator->after(function ($validator) use ($request) {
                if (empty($request->qty)) {
                    $validator->errors()->add('qty', 'Qty is required when track quantity is Yes.');
                }
            });
        }

        if ($validator->passes()) {
            $product->title = $request->title;
            $product->slug = $request->slug;
            $product->description = $request->description;
            $product->short_description = $request->short_description;
            $product->shipping_returns = $request->shipping_returns;
            $product->price = $request->price;
            $product->compare_price = $request->compare_price;
            $product->sku = $request->sku;
            $product->barcode = $request->barcode;
            $product->track_qty = ($request->track_qty == 'Yes') ? 1 : 0;
            $product->qty = $request->qty;
            $product->status = ($request->status == 1) ? 1 : 0;
            $product->category_id = $request->category_id;
            $product->sub_category_id = $request->sub_category_id;
            $product->brand_id = $request->brand_id;
            $product->is_featured = ($request->is_featured == 'Yes') ? 1 : 0;
            $product->save();

            session()->flash('success', 'Product updated successfully');

            return response()->json([
                'status' => true,
                'message' => 'Product updated successfully'
            ]);

        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($productId, Request $request)
    {
        $product = Product::find($productId);

        if (empty($product)) {
            session()->flash('error', 'Product not found');
            return response()->json([
                'status' => false,
                'message' => 'Product not found'
            ]);
        }

        $productImages = ProductImage::where('product_id', $productId)->get();

        if (!empty($productImages)) {
            foreach ($productImages as $productImage) {
                File::delete(public_path().'/uploads/product/large/'.$productImage->image);
                File::delete(public_path().'/uploads/product/small/'.$productImage->image);
            }

            ProductImage::where('product_id', $productId)->delete();
        }

        $product->delete();

        session()->flash('success', 'Product deleted successfully');

        return response()->json([
            'status' => true,
            'message' => 'Product deleted successfully'
        ]);
    }

    public function getProducts(Request $request)
    {
        $tempProduct = [];
        if ($request->term != "") {
            $products = Product::where('title', 'like', '%' . $request->term . '%')->get();

            if ($products != null) {
                foreach ($products as $product) {
                    $tempProduct[] = array('id' => $product->id, 'text' => $product->title);
                }
            }
        }

        return response()->json([
            'tags' => $tempProduct,
            'status' => true
        ]);
    }
}
