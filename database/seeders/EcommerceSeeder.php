<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use App\Models\Coupon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EcommerceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Admin User
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@cartly.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'phone' => '+1234567890',
        ]);

        // Create Customer Users
        $customer1 = User::create([
            'name' => 'John Doe',
            'email' => 'customer@example.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
            'phone' => '+1234567891',
        ]);

        // Create Categories
        $electronics = Category::create([
            'name' => 'Electronics',
            'slug' => 'electronics',
            'description' => 'Electronic devices and gadgets',
            'is_active' => true,
        ]);

        $clothing = Category::create([
            'name' => 'Clothing',
            'slug' => 'clothing',
            'description' => 'Fashion and apparel',
            'is_active' => true,
        ]);

        $books = Category::create([
            'name' => 'Books',
            'slug' => 'books',
            'description' => 'Books and literature',
            'is_active' => true,
        ]);

        $phones = Category::create([
            'name' => 'Smartphones',
            'slug' => 'smartphones',
            'parent_id' => $electronics->id,
            'description' => 'Mobile phones and accessories',
            'is_active' => true,
        ]);

        $laptops = Category::create([
            'name' => 'Laptops',
            'slug' => 'laptops',
            'parent_id' => $electronics->id,
            'description' => 'Laptops and notebooks',
            'is_active' => true,
        ]);

        // Create Products
        $products = [
            [
                'name' => 'iPhone 15 Pro',
                'slug' => 'iphone-15-pro',
                'description' => 'Latest iPhone with advanced features and camera system.',
                'short_description' => 'Premium smartphone with A17 Pro chip',
                'category_id' => $phones->id,
                'price' => 999.99,
                'discount_price' => 899.99,
                'stock_quantity' => 50,
                'sku' => 'IPH-15-PRO-001',
                'is_active' => true,
                'is_featured' => true,
            ],
            [
                'name' => 'Samsung Galaxy S24',
                'slug' => 'samsung-galaxy-s24',
                'description' => 'Powerful Android smartphone with excellent camera.',
                'short_description' => 'Flagship Android device',
                'category_id' => $phones->id,
                'price' => 899.99,
                'discount_price' => null,
                'stock_quantity' => 30,
                'sku' => 'SGS24-001',
                'is_active' => true,
                'is_featured' => true,
            ],
            [
                'name' => 'MacBook Pro 16"',
                'slug' => 'macbook-pro-16',
                'description' => 'Powerful laptop for professionals with M3 chip.',
                'short_description' => 'Professional laptop with M3 chip',
                'category_id' => $laptops->id,
                'price' => 2499.99,
                'discount_price' => 2299.99,
                'stock_quantity' => 20,
                'sku' => 'MBP-16-001',
                'is_active' => true,
                'is_featured' => true,
            ],
            [
                'name' => 'Dell XPS 15',
                'slug' => 'dell-xps-15',
                'description' => 'High-performance laptop with stunning display.',
                'short_description' => 'Premium Windows laptop',
                'category_id' => $laptops->id,
                'price' => 1599.99,
                'discount_price' => null,
                'stock_quantity' => 25,
                'sku' => 'DXPS15-001',
                'is_active' => true,
                'is_featured' => false,
            ],
            [
                'name' => 'Wireless Headphones',
                'slug' => 'wireless-headphones',
                'description' => 'Premium wireless headphones with noise cancellation.',
                'short_description' => 'Noise cancelling headphones',
                'category_id' => $electronics->id,
                'price' => 299.99,
                'discount_price' => 249.99,
                'stock_quantity' => 100,
                'sku' => 'WH-001',
                'is_active' => true,
                'is_featured' => false,
            ],
            [
                'name' => 'Cotton T-Shirt',
                'slug' => 'cotton-t-shirt',
                'description' => 'Comfortable cotton t-shirt in various colors.',
                'short_description' => 'Comfortable everyday t-shirt',
                'category_id' => $clothing->id,
                'price' => 29.99,
                'discount_price' => 19.99,
                'stock_quantity' => 200,
                'sku' => 'TSH-001',
                'is_active' => true,
                'is_featured' => false,
            ],
            [
                'name' => 'Programming Book: Laravel',
                'slug' => 'programming-book-laravel',
                'description' => 'Comprehensive guide to Laravel framework.',
                'short_description' => 'Learn Laravel development',
                'category_id' => $books->id,
                'price' => 49.99,
                'discount_price' => null,
                'stock_quantity' => 75,
                'sku' => 'BK-LAR-001',
                'is_active' => true,
                'is_featured' => false,
            ],
            [
                'name' => 'JavaScript Mastery',
                'slug' => 'javascript-mastery',
                'description' => 'Advanced JavaScript programming techniques.',
                'short_description' => 'Master JavaScript',
                'category_id' => $books->id,
                'price' => 39.99,
                'discount_price' => 29.99,
                'stock_quantity' => 60,
                'sku' => 'BK-JS-001',
                'is_active' => true,
                'is_featured' => false,
            ],
        ];

        foreach ($products as $productData) {
            Product::create($productData);
        }

        // Create Coupons
        Coupon::create([
            'code' => 'WELCOME10',
            'name' => 'Welcome Discount',
            'description' => '10% off on your first order',
            'type' => 'percentage',
            'value' => 10,
            'min_purchase' => 50,
            'usage_limit' => 100,
            'valid_from' => now(),
            'valid_until' => now()->addMonths(3),
            'is_active' => true,
        ]);

        Coupon::create([
            'code' => 'SAVE50',
            'name' => 'Save $50',
            'description' => 'Get $50 off on orders above $200',
            'type' => 'fixed',
            'value' => 50,
            'min_purchase' => 200,
            'max_discount' => 50,
            'usage_limit' => 50,
            'valid_from' => now(),
            'valid_until' => now()->addMonths(2),
            'is_active' => true,
        ]);

        $this->command->info('E-commerce data seeded successfully!');
        $this->command->info('Admin Login: admin@cartly.com / password');
        $this->command->info('Customer Login: customer@example.com / password');
    }
}
