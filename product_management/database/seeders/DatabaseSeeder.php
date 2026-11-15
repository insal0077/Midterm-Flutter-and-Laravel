<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Insert sample data: 3 categories and 3 products.
     * Run with: php artisan db:seed
     */
    public function run(): void
    {
        // ---- Categories ----
        $drinks = Category::create([
            'category_name' => 'Drinks',
            'description'   => 'Soft drinks and water',
            'image'         => 'drinks.png',
        ]);

        $snacks = Category::create([
            'category_name' => 'Snacks',
            'description'   => 'Chips and biscuits',
            'image'         => 'snacks.png',
        ]);

        $dairy = Category::create([
            'category_name' => 'Dairy',
            'description'   => 'Milk and yogurt',
            'image'         => 'dairy.png',
        ]);

        // ---- Products ----
        Product::create([
            'ProductName'  => 'Coca Cola 330ml',
            'Description'  => 'Can of Coca Cola',
            'CategoryID'   => $drinks->category_id,
            'Barcode'      => '8851959132012',
            'Expiredate'   => '2027-06-30',
            'Qty'          => 50,
            'UnitPriceIn'  => 0.40,
            'UnitPriceOut' => 0.75,
            'ProductImage' => null, // upload a picture from the app or web page
        ]);

        Product::create([
            'ProductName'  => 'Lays Original 50g',
            'Description'  => 'Potato chips',
            'CategoryID'   => $snacks->category_id,
            'Barcode'      => '8850718801015',
            'Expiredate'   => '2027-03-15',
            'Qty'          => 30,
            'UnitPriceIn'  => 0.60,
            'UnitPriceOut' => 1.00,
            'ProductImage' => null, // upload a picture from the app or web page
        ]);

        Product::create([
            'ProductName'  => 'Fresh Milk 1L',
            'Description'  => 'Full cream milk',
            'CategoryID'   => $dairy->category_id,
            'Barcode'      => '8851717030017',
            'Expiredate'   => '2026-12-31',
            'Qty'          => 20,
            'UnitPriceIn'  => 1.20,
            'UnitPriceOut' => 1.80,
            'ProductImage' => null, // upload a picture from the app or web page
        ]);
    }
}
