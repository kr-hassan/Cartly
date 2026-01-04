# Cartly E-commerce Setup Guide

## Overview
Cartly is a complete, production-ready e-commerce web application built with Laravel 12. It features a full customer-facing store, admin panel, cart management (guest & authenticated), order processing, coupon system, and more.

## Features

### Customer Side
- ✅ Product listing with search, filter, and sorting
- ✅ Product details pages
- ✅ Category & sub-category browsing
- ✅ Shopping cart (works for both guest and logged-in users)
- ✅ Cart persistence (session for guests, database for users)
- ✅ Guest checkout (no registration required)
- ✅ Registered checkout
- ✅ Order tracking
- ✅ Email order confirmations

### Admin Panel
- ✅ Dashboard with statistics
- ✅ Product management (CRUD)
- ✅ Category management (hierarchical)
- ✅ Order management with status updates
- ✅ Coupon/discount system
- ✅ Customer management
- ✅ Inventory/stock management

### Technical Features
- ✅ RESTful API design
- ✅ MVC architecture
- ✅ Service layer for business logic
- ✅ Form Request validation
- ✅ Role-based access control (Admin/Customer)
- ✅ SEO-friendly URLs
- ✅ Mobile-responsive UI (Tailwind CSS)
- ✅ Secure against SQL injection & XSS

## Installation

### Prerequisites
- PHP 8.2 or higher
- MySQL 5.7+ or MariaDB 10.3+
- Composer
- Node.js & npm

### Steps

1. **Clone/Navigate to the project**
   ```bash
   cd /path/to/Cartly
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install Node dependencies**
   ```bash
   npm install
   ```

4. **Environment Configuration**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Update .env file**
   Set your database credentials:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=cartly
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

   Configure mail settings (for order emails):
   ```env
   MAIL_MAILER=smtp
   MAIL_HOST=your_smtp_host
   MAIL_PORT=587
   MAIL_USERNAME=your_email
   MAIL_PASSWORD=your_password
   MAIL_FROM_ADDRESS=noreply@cartly.com
   MAIL_FROM_NAME="Cartly"
   ```

6. **Create database**
   ```bash
   mysql -u root -p
   CREATE DATABASE cartly;
   ```

7. **Run migrations**
   ```bash
   php artisan migrate
   ```

8. **Seed database (optional but recommended for demo)**
   ```bash
   php artisan db:seed
   ```

   This will create:
   - Admin user: `admin@cartly.com` / `password`
   - Customer user: `customer@example.com` / `password`
   - Sample categories, products, and coupons

9. **Create storage link**
   ```bash
   php artisan storage:link
   ```

10. **Build assets**
    ```bash
    npm run build
    ```

11. **Start development server**
    ```bash
    php artisan serve
    ```

    The application will be available at `http://localhost:8000`

## Usage

### Default Login Credentials

**Admin Panel:**
- Email: `admin@cartly.com`
- Password: `password`
- URL: `/admin/dashboard`

**Customer Account:**
- Email: `customer@example.com`
- Password: `password`

### Key Routes

**Frontend:**
- `/` - Home (redirects to products)
- `/products` - Product listing
- `/products/{slug}` - Product details
- `/cart` - Shopping cart
- `/checkout` - Checkout page
- `/orders` - User orders (requires login)
- `/orders/{orderNumber}` - Order details

**Admin:**
- `/admin/dashboard` - Admin dashboard
- `/admin/products` - Product management
- `/admin/categories` - Category management
- `/admin/orders` - Order management
- `/admin/coupons` - Coupon management

**Authentication:**
- `/login` - Login page
- `/register` - Registration page
- `/forgot-password` - Password reset request
- `/reset-password/{token}` - Password reset

## Database Schema

### Main Tables
- `users` - User accounts (customers & admins)
- `categories` - Product categories (hierarchical)
- `products` - Products with images, pricing, stock
- `carts` - Shopping cart items (guest session or user)
- `orders` - Orders (supports guest checkout)
- `order_items` - Order line items
- `order_status_history` - Order status change history
- `coupons` - Discount coupons

## Cart Logic Explanation

### Guest Carts
- Stored in `carts` table with `session_id`
- Session ID is stored in Laravel session
- Cart persists across browser sessions
- Merged with user cart upon login

### Authenticated User Carts
- Stored in `carts` table with `user_id`
- Persists across devices (if same account)
- Automatically synced when user logs in

### Cart Merging on Login
When a guest user logs in:
1. Guest cart items are retrieved by `session_id`
2. Each item is checked against user's existing cart
3. If product exists, quantities are merged (respecting stock limits)
4. If product is new, `session_id` is replaced with `user_id`
5. Session ID is cleared after merge

## Checkout Flow

1. **Cart Review** - User reviews cart items
2. **Checkout Form** - User enters shipping information
   - Name (required)
   - Phone (required)
   - Email (optional for guest)
   - Shipping address (required)
   - Payment method (COD or Online)
   - Optional coupon code
3. **Order Creation**
   - Cart items converted to order items
   - Stock is decremented
   - Order status set to "pending"
   - Status history entry created
   - Cart is cleared
4. **Payment Processing**
   - COD: Payment status set to "pending"
   - Online: Payment gateway integration (abstract layer provided)
5. **Email Notification** - Order confirmation email sent
6. **Order Confirmation Page** - User sees order details

## Payment Integration

The application includes an abstract `PaymentService` class that can be extended:

```php
// app/Services/StripePaymentService.php
class StripePaymentService extends PaymentService
{
    public function processPayment(Order $order, array $paymentData = []): array
    {
        // Stripe integration logic
    }
}
```

Currently, COD (Cash on Delivery) is fully implemented. Online payment integration requires extending the `PaymentService` class.

## File Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── ProductController.php (Frontend)
│   │   ├── CartController.php
│   │   ├── CheckoutController.php
│   │   ├── OrderController.php
│   │   └── Admin/
│   │       ├── DashboardController.php
│   │       ├── ProductController.php
│   │       ├── CategoryController.php
│   │       ├── OrderController.php
│   │       └── CouponController.php
│   ├── Middleware/
│   │   └── EnsureUserIsAdmin.php
│   └── Requests/
│       └── (Form Request validators)
├── Models/
│   ├── User.php (extended with role)
│   ├── Category.php
│   ├── Product.php
│   ├── Cart.php
│   ├── Order.php
│   ├── OrderItem.php
│   ├── OrderStatusHistory.php
│   └── Coupon.php
└── Services/
    ├── CartService.php
    ├── OrderService.php
    └── PaymentService.php (abstract)
```

## Security Features

- ✅ CSRF protection on all forms
- ✅ SQL injection prevention (Eloquent ORM)
- ✅ XSS protection (Blade templating)
- ✅ Password hashing (bcrypt)
- ✅ Role-based access control
- ✅ Admin middleware protection
- ✅ Input validation (Form Requests)
- ✅ Secure file uploads

## Customization

### Adding Payment Gateway

1. Extend `PaymentService`:
```php
class StripePaymentService extends PaymentService
{
    public function processPayment(Order $order, array $paymentData = []): array
    {
        // Implementation
    }
}
```

2. Update `CheckoutController` to use the new service

### Adding Product Attributes

1. Create migration for new fields
2. Update `Product` model fillable/casts
3. Update `StoreProductRequest` and `UpdateProductRequest`
4. Update admin product forms
5. Update product views

## Production Deployment

### Before deploying:

1. Set `APP_ENV=production` in `.env`
2. Set `APP_DEBUG=false`
3. Run `php artisan config:cache`
4. Run `php artisan route:cache`
5. Run `php artisan view:cache`
6. Set up proper queue worker for emails
7. Configure proper storage (S3, etc. for images)
8. Set up SSL certificate
9. Configure proper mail settings
10. Set up database backups

### Queue Configuration

For order confirmation emails, configure a queue:
```env
QUEUE_CONNECTION=database
```

Then run:
```bash
php artisan queue:work
```

## Troubleshooting

### Images not displaying
- Run `php artisan storage:link`
- Check file permissions on `storage/app/public`

### Cart not persisting
- Check session driver in `.env`
- Ensure database sessions table exists if using database driver

### Email not sending
- Check mail configuration in `.env`
- Test with `php artisan tinker`:
  ```php
  Mail::raw('Test', function($msg) { $msg->to('test@example.com')->subject('Test'); });
  ```

## Support

For issues or questions, please refer to the Laravel documentation or create an issue in the repository.

## License

This project is open-sourced software licensed under the MIT license.

