<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();
        \App\Models\User::truncate();
        \App\Models\Product::truncate();
        \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();

        // ─── Seed Users ───────────────────────────────────────────────────────
        \App\Models\User::create([
            'username'  => 'admin',
            'email'     => 'admin@zmart.id',
            'password'  => 'admin123',
            'full_name' => 'System Admin',
            'role'      => 'admin',
        ]);

        \App\Models\User::create([
            'username'  => 'user1',
            'email'     => 'user1@zmart.id',
            'password'  => 'user123',
            'full_name' => 'Regular Customer',
            'role'      => 'customer',
        ]);

        // ─── Produk Pakaian (Fashion) ─────────────────────────────────────────
        $pakaian = [
            [
                'name'        => 'Kaos Polos Cotton Combed 30s',
                'description' => 'Bahan katun premium Combed 30s yang sangat lembut, adem, dan menyerap keringat. Cocok untuk bersantai sehari-hari.',
                'price'       => 45000,
                'image'       => 'https://images.unsplash.com/photo-1521572267360-ee0c2909d518?w=600&auto=format&fit=crop&q=80',
                'stock'       => 120,
                'category'    => 'kaos',
                'is_active'   => 1,
            ],
            [
                'name'        => 'Jaket Denim Klasik Indigo',
                'description' => 'Jaket denim berkualitas tinggi dengan jahitan kuat dan detail washed retro yang modis.',
                'price'       => 185000,
                'image'       => 'https://images.unsplash.com/photo-1576995853123-5a10305d93c0?w=600&auto=format&fit=crop&q=80',
                'stock'       => 45,
                'category'    => 'jaket',
                'is_active'   => 1,
            ],
            [
                'name'        => 'Kemeja Flanel Slim Fit Red-Black',
                'description' => 'Kemeja flanel lengan panjang dengan motif kotak-kotak klasik. Sangat cocok dipadukan dengan kaos polos.',
                'price'       => 120000,
                'image'       => 'https://images.unsplash.com/photo-1598033129183-c4f50c736f10?w=600&auto=format&fit=crop&q=80',
                'stock'       => 60,
                'category'    => 'kemeja',
                'is_active'   => 1,
            ],
            [
                'name'        => 'Celana Chino Stretch Slim Beige',
                'description' => 'Celana chino kasual stretch yang elastis dan nyaman untuk bergerak bebas sepanjang hari.',
                'price'       => 150000,
                'image'       => 'https://images.unsplash.com/photo-1624378439575-d8705ad7ae80?w=600&auto=format&fit=crop&q=80',
                'stock'       => 75,
                'category'    => 'celana',
                'is_active'   => 1,
            ],
            [
                'name'        => 'Hoodie Pullover Oversized Sage Green',
                'description' => 'Hoodie rajutan premium yang tebal dan hangat namun tetap sejuk dipakai di siang hari.',
                'price'       => 195000,
                'image'       => 'https://images.unsplash.com/photo-1556821840-3a63f95609a7?w=600&auto=format&fit=crop&q=80',
                'stock'       => 35,
                'category'    => 'hoodie',
                'is_active'   => 1,
            ],
            [
                'name'        => 'Celana Cargo Corduroy Brown',
                'description' => 'Celana cargo berbahan corduroy premium dengan saku samping fungsional untuk petualangan urban Anda.',
                'price'       => 175000,
                'image'       => 'https://images.unsplash.com/photo-1517462964-21fdcec3f25b?w=600&auto=format&fit=crop&q=80',
                'stock'       => 28,
                'category'    => 'celana',
                'is_active'   => 1,
            ],
            [
                'name'        => 'Jaket Bomber Vintage Black',
                'description' => 'Jaket bomber dengan lapisan windbreaker tebal yang melindungi dari dingin malam namun tetap stylish.',
                'price'       => 210000,
                'image'       => 'https://images.unsplash.com/photo-1551028719-00167b16eac5?w=600&auto=format&fit=crop&q=80',
                'stock'       => 20,
                'category'    => 'jaket',
                'is_active'   => 1,
            ],
            [
                'name'        => 'Kaos Polo Premium Navy Blue',
                'description' => 'Kaos polo semi-formal berkancing dengan kerah rajut padat dan rajutan katun berpori yang breathable.',
                'price'       => 85000,
                'image'       => 'https://images.unsplash.com/photo-1581655353564-df123a1eb820?w=600&auto=format&fit=crop&q=80',
                'stock'       => 80,
                'category'    => 'kaos',
                'is_active'   => 1,
            ],
        ];

        // ─── Produk Kebutuhan Harian ──────────────────────────────────────────
        $harian = [
            // Makanan & Minuman
            [
                'name'        => 'Beras Premium Pandan Wangi 5kg',
                'description' => 'Beras putih pulen premium varietas Pandan Wangi pilihan petani terbaik. Aroma wangi alami dan tekstur nasi yang lembut.',
                'price'       => 68000,
                'image'       => 'https://images.unsplash.com/photo-1586201375761-83865001e31c?w=600&auto=format&fit=crop&q=80',
                'stock'       => 150,
                'category'    => 'sembako',
                'is_active'   => 1,
            ],
            [
                'name'        => 'Minyak Goreng Kemasan 1L',
                'description' => 'Minyak goreng sawit berkualitas tinggi, jernih, bebas kolesterol jahat. Cocok untuk menggoreng, menumis, dan memanggang.',
                'price'       => 22000,
                'image'       => 'https://images.unsplash.com/photo-1620706857370-e1b9770e8bb1?w=600&auto=format&fit=crop&q=80',
                'stock'       => 200,
                'category'    => 'sembako',
                'is_active'   => 1,
            ],
            [
                'name'        => 'Gula Pasir Rafinasi 1kg',
                'description' => 'Gula pasir putih halus berkualitas tinggi, bersih tanpa kotoran. Ideal untuk memasak, kue, dan minuman.',
                'price'       => 16500,
                'image'       => 'https://images.unsplash.com/photo-1571506165871-ee72a35bc9d4?w=600&auto=format&fit=crop&q=80',
                'stock'       => 180,
                'category'    => 'sembako',
                'is_active'   => 1,
            ],
            [
                'name'        => 'Telur Ayam Negeri Segar 1 Kg',
                'description' => 'Telur ayam negeri segar langsung dari peternak, protein tinggi untuk kebutuhan gizi keluarga sehari-hari.',
                'price'       => 28000,
                'image'       => 'https://images.unsplash.com/photo-1506976785307-8732e854ad03?w=600&auto=format&fit=crop&q=80',
                'stock'       => 100,
                'category'    => 'sembako',
                'is_active'   => 1,
            ],
            [
                'name'        => 'Mie Instan Rasa Ayam Bawang (Box)',
                'description' => 'Isi 40 bungkus per karton. Mie instan favorit keluarga dengan bumbu khas ayam bawang yang gurih.',
                'price'       => 95000,
                'image'       => 'https://images.unsplash.com/photo-1569718212165-3a8278d5f624?w=600&auto=format&fit=crop&q=80',
                'stock'       => 80,
                'category'    => 'makanan',
                'is_active'   => 1,
            ],
            [
                'name'        => 'Susu UHT Full Cream 1L',
                'description' => 'Susu sapi segar Ultra High Temperature dengan lemak penuh. Sumber kalsium dan protein untuk pertumbuhan.',
                'price'       => 18500,
                'image'       => 'https://images.unsplash.com/photo-1550583724-b2692b85b150?w=600&auto=format&fit=crop&q=80',
                'stock'       => 120,
                'category'    => 'minuman',
                'is_active'   => 1,
            ],
            [
                'name'        => 'Air Mineral Botol 600ml (1 Krat)',
                'description' => 'Air mineral segar tersaring 100% alami dalam kemasan botol praktis. Segar, bersih, bebas kuman.',
                'price'       => 24000,
                'image'       => 'https://images.unsplash.com/photo-1548839140-29a749e1cf4d?w=600&auto=format&fit=crop&q=80',
                'stock'       => 60,
                'category'    => 'minuman',
                'is_active'   => 1,
            ],
            // Kebersihan Rumah
            [
                'name'        => 'Sabun Cuci Piring Busa Aktif 800ml',
                'description' => 'Formula busa tebal dengan aroma jeruk segar. Efektif membersihkan lemak membandel tanpa merusak kulit tangan.',
                'price'       => 15000,
                'image'       => 'https://images.unsplash.com/photo-1583947215259-38e31be8751f?w=600&auto=format&fit=crop&q=80',
                'stock'       => 200,
                'category'    => 'kebersihan',
                'is_active'   => 1,
            ],
            [
                'name'        => 'Deterjen Cair Konsentrat 800ml',
                'description' => 'Deterjen cair konsentrat yang efisien. Formula Enzyme Power mengangkat noda membandel pada baju kesayangan Anda.',
                'price'       => 32000,
                'image'       => 'https://images.unsplash.com/photo-1585670083947-7c3a5e2ebe02?w=600&auto=format&fit=crop&q=80',
                'stock'       => 150,
                'category'    => 'kebersihan',
                'is_active'   => 1,
            ],
            [
                'name'        => 'Pembersih Lantai Anti-Kuman 1.8L',
                'description' => 'Formula anti-kuman 99.9% dengan wangi lavender tahan lama. Menjaga lantai bersih, wangi, dan bebas bakteri.',
                'price'       => 28500,
                'image'       => 'https://images.unsplash.com/photo-1563453392212-326f5e854473?w=600&auto=format&fit=crop&q=80',
                'stock'       => 90,
                'category'    => 'kebersihan',
                'is_active'   => 1,
            ],
            [
                'name'        => 'Tisu Wajah Lembut 200 Lembar',
                'description' => 'Tisu wajah lembut ekstra soft berbahan pulp premium. Tidak berbulu, aman untuk kulit wajah sensitif.',
                'price'       => 18000,
                'image'       => 'https://images.unsplash.com/photo-1616628188859-7a11abb6fcc9?w=600&auto=format&fit=crop&q=80',
                'stock'       => 300,
                'category'    => 'kebersihan',
                'is_active'   => 1,
            ],
            // Perawatan Diri
            [
                'name'        => 'Shampo Perawatan Rambut 340ml',
                'description' => 'Formula keratin dan argan oil yang menutrisi rambut dari akar hingga ujung. Membuat rambut halus, berkilau, dan bebas kusut.',
                'price'       => 34000,
                'image'       => 'https://images.unsplash.com/photo-1585751119414-ef2636f8aede?w=600&auto=format&fit=crop&q=80',
                'stock'       => 110,
                'category'    => 'perawatan',
                'is_active'   => 1,
            ],
            [
                'name'        => 'Sabun Mandi Cair Moisturizing 400ml',
                'description' => 'Sabun mandi cair dengan pelembab shea butter dan vitamin E. Menjaga kelembaban kulit hingga 24 jam setelah mandi.',
                'price'       => 28000,
                'image'       => 'https://images.unsplash.com/photo-1631729371254-42c2892f0e6e?w=600&auto=format&fit=crop&q=80',
                'stock'       => 250,
                'category'    => 'perawatan',
                'is_active'   => 1,
            ],
            [
                'name'        => 'Pasta Gigi Whitening Charcoal 120g',
                'description' => 'Formula charcoal aktif yang memutihkan gigi secara alami sekaligus melawan bakteri penyebab bau mulut.',
                'price'       => 18000,
                'image'       => 'https://images.unsplash.com/photo-1559591937-abc0d0c77c58?w=600&auto=format&fit=crop&q=80',
                'stock'       => 175,
                'category'    => 'perawatan',
                'is_active'   => 1,
            ],
            [
                'name'        => 'Pelembab Wajah SPF 30 50ml',
                'description' => 'Pelembab wajah ringan dengan perlindungan UV SPF 30. Cocok untuk pemakaian sehari-hari di luar dan dalam ruangan.',
                'price'       => 45000,
                'image'       => 'https://images.unsplash.com/photo-1620916566398-39f1143ab7be?w=600&auto=format&fit=crop&q=80',
                'stock'       => 85,
                'category'    => 'perawatan',
                'is_active'   => 1,
            ],
        ];

        foreach (array_merge($pakaian, $harian) as $product) {
            \App\Models\Product::create(array_merge(['external_link' => ''], $product));
        }
    }
}
