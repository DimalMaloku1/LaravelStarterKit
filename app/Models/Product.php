<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'short_description',
        'sku',
        'barcode',
        'track_qty',
        'qty',
        'status',
        'category_id',
        'sub_category_id',
        'brand_id',
        'price',
        'compare_price',
        'is_featured',
        'shipping_returns'
    ];

    protected $casts = [
        'track_qty' => 'boolean',
        'status' => 'boolean',
        'is_featured' => 'boolean',
        'price' => 'decimal:2',
        'compare_price' => 'decimal:2',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function subCategory()
    {
        return $this->belongsTo(SubCategory::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function productImages()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function productRatings()
    {
        return $this->hasMany(ProductRating::class);
    }

    public function getProductImageAttribute()
    {
        $productImage = $this->productImages()->first();
        if (!empty($productImage)) {
            return asset('uploads/product/small/' . $productImage->image);
        }
        return asset('admin-assets/img/default-150x150.png');
    }
}