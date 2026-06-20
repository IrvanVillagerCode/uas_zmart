<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Disable foreign key checks
        \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();

        // Truncate users and products
        \App\Models\User::truncate();
        \App\Models\Product::truncate();

        // Re-enable foreign key checks
        \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();

        // Seed default users
        \App\Models\User::create([
            'username' => 'admin',
            'email' => 'admin@zmart.id',
            'password' => 'admin123',
            'full_name' => 'System Admin',
            'role' => 'admin',
        ]);

        \App\Models\User::create([
            'username' => 'user1',
            'email' => 'user1@zmart.id',
            'password' => 'user123',
            'full_name' => 'Regular Customer',
            'role' => 'customer',
        ]);

        // Seed clothing products
        $products = [
            [
                'name' => 'Kaos Polos Cotton Combed 30s',
                'description' => 'Bahan katun premium Combed 30s yang sangat lembut, adem, dan menyerap keringat. Cocok untuk bersantai sehari-hari.',
                'price' => 45000,
                'image' => 'https://images.unsplash.com/photo-1521572267360-ee0c2909d518?w=600&auto=format&fit=crop&q=80',
                'external_link' => '',
                'stock' => 120,
                'category' => 'kaos',
                'is_active' => 1
            ],
            [
                'name' => 'Jaket Denim Klasik Indigo',
                'description' => 'Jaket denim berkualitas tinggi dengan jahitan kuat dan detail washed retro yang modis.',
                'price' => 185000,
                'image' => 'https://images.unsplash.com/photo-1576995853123-5a10305d93c0?w=600&auto=format&fit=crop&q=80',
                'external_link' => '',
                'stock' => 45,
                'category' => 'jaket',
                'is_active' => 1
            ],
            [
                'name' => 'Kemeja Flanel Slim Fit Red-Black',
                'description' => 'Kemeja flanel lengan panjang dengan motif kotak-kotak klasik. Sangat cocok dipadukan dengan kaos polos.',
                'price' => 120000,
                'image' => 'https://images.unsplash.com/photo-1598033129183-c4f50c736f10?w=600&auto=format&fit=crop&q=80',
                'external_link' => '',
                'stock' => 60,
                'category' => 'kemeja',
                'is_active' => 1
            ],
            [
                'name' => 'Celana Chino Stretch Slim Beige',
                'description' => 'Celana chino kasual stretch yang elastis dan nyaman untuk bergerak bebas sepanjang hari.',
                'price' => 150000,
                'image' => 'https://images.unsplash.com/photo-1624378439575-d8705ad7ae80?w=600&auto=format&fit=crop&q=80',
                'external_link' => '',
                'stock' => 75,
                'category' => 'celana',
                'is_active' => 1
            ],
            [
                'name' => 'Hoodie Pullover Oversized Sage Green',
                'description' => 'Hoodie rajutan premium yang tebal dan hangat namun tetap sejuk dipakai di siang hari.',
                'price' => 195000,
                'image' => 'https://images.unsplash.com/photo-1556821840-3a63f95609a7?w=600&auto=format&fit=crop&q=80',
                'external_link' => '',
                'stock' => 35,
                'category' => 'hoodie',
                'is_active' => 1
            ],
            [
                'name' => 'Celana Cargo Corduroy Brown',
                'price' => 175000,
                'description' => 'Celana cargo berbahan corduroy premium dengan saku samping fungsional untuk petualangan urban Anda.',
                'image' => 'https://images.unsplash.com/photo-1517462964-21fdcec3f25b?w=600&auto=format&fit=crop&q=80',
                'external_link' => '',
                'stock' => 28,
                'category' => 'celana',
                'is_active' => 1
            ],
            [
                'name' => 'Jaket Bomber Vintage Black',
                'description' => 'Jaket bomber dengan lapisan windbreaker tebal yang melindungi dari dingin malam namun tetap stylish.',
                'price' => 210000,
                'image' => 'https://images.unsplash.com/photo-1551028719-00167b16eac5?w=600&auto=format&fit=crop&q=80',
                'external_link' => '',
                'stock' => 20,
                'category' => 'jaket',
                'is_active' => 1
            ],
            [
                'name' => 'Kaos Polo Premium Navy Blue',
                'description' => 'Kaos polo semi-formal berkancing dengan kerah rajut padat dan rajutan katun berpori yang breathable.',
                'price' => 85000,
                'image' => 'https://images.unsplash.com/photo-1581655353564-df123a1eb820?w=600&auto=format&fit=crop&q=80',
                'external_link' => '',
                'stock' => 80,
                'category' => 'kaos',
                'is_active' => 1
            ]
        ];

        foreach ($products as $product) {
            \App\Models\Product::create($product);
        }
    }
}
