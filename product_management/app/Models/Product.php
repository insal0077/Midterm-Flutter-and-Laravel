<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'products';

    protected $primaryKey = 'ProductID';

    // The products table has no created_at / updated_at columns
    public $timestamps = false;

    protected $fillable = [
        'ProductName',
        'Description',
        'CategoryID',
        'Barcode',
        'Expiredate',
        'Qty',
        'UnitPriceIn',
        'UnitPriceOut',
        'ProductImage',
        'isDeleted',
    ];

    // Add the full image link to every JSON response
    protected $appends = ['image_url'];

    // ProductImage stores the path, e.g. "products/abc.jpg"
    public function getImageUrlAttribute()
    {
        return $this->ProductImage ? asset('storage/' . $this->ProductImage) : null;
    }

    // Each product belongs to one category
    public function category()
    {
        return $this->belongsTo(Category::class, 'CategoryID', 'category_id');
    }
}
