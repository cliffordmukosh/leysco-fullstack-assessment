<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/data/products.json');

        if (!file_exists($path)) {
            $this->command->error('products.json not found!');
            return;
        }

        $products = json_decode(file_get_contents($path), true);

        foreach ($products as $productData) {
            // Create or get category (normalize from string)
            $category = Category::firstOrCreate(
                ['name' => $productData['category']],
                ['slug' => str($productData['category'])->slug()]  // ← Perfect, no import needed
            );

            Product::create([
                'sku' => $productData['sku'],
                'name' => $productData['name'],
                'category_id' => $category->id,
                'subcategory' => $productData['subcategory'],
                'description' => $productData['description'],
                'price' => $productData['price'],
                'tax_rate' => $productData['tax_rate'],
                'unit' => $productData['unit'],
                'packaging' => $productData['packaging'],
                'min_order_quantity' => $productData['min_order_quantity'],
                'reorder_level' => $productData['reorder_level'],
            ]);
        }

        $this->command->info('Products seeded successfully!');
    }
}