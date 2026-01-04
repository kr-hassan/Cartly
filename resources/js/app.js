import './bootstrap';
import Alpine from 'alpinejs';

// Make Alpine available globally
window.Alpine = Alpine;

// Cart functionality with Alpine.js
Alpine.data('cart', () => ({
    cartCount: 0,
    loading: false,
    notifications: [],
    cartCountLoaded: false,

    init() {
        // Defer cart count loading to improve initial page load
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                this.updateCartCount();
            });
        } else {
            // Use requestIdleCallback if available, otherwise setTimeout
            if ('requestIdleCallback' in window) {
                requestIdleCallback(() => this.updateCartCount(), { timeout: 2000 });
            } else {
                setTimeout(() => this.updateCartCount(), 100);
            }
        }
    },

    async updateCartCount(force = false) {
        // Only skip if already loaded and not forcing an update
        if (this.cartCountLoaded && !force) return;
        
        try {
            const response = await fetch('/cart/count', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const data = await response.json();
            this.cartCount = data.count || 0;
            this.cartCountLoaded = true;
        } catch (error) {
            console.error('Error updating cart count:', error);
            this.cartCountLoaded = true; // Mark as loaded even on error to prevent retries
        }
    },

    showNotification(message, type = 'success') {
        const notification = {
            id: Date.now(),
            message,
            type,
            visible: true
        };
        
        this.notifications.push(notification);
        
        // Auto remove after 3 seconds
        setTimeout(() => {
            this.removeNotification(notification.id);
        }, 3000);
    },

    removeNotification(id) {
        this.notifications = this.notifications.filter(n => n.id !== id);
    },

    async addToCart(productId, quantity = 1, event = null) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }

        if (this.loading) return; // Prevent multiple simultaneous requests

        this.loading = true;

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            if (!csrfToken) {
                throw new Error('CSRF token not found');
            }

            const formData = new FormData();
            formData.append('product_id', productId);
            formData.append('quantity', quantity);
            formData.append('_token', csrfToken);

            const response = await fetch('/cart', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken
                }
            });

            const data = await response.json();

            if (data.success) {
                // Update cart count from response if available, otherwise fetch it
                if (data.cart_count !== undefined) {
                    this.cartCount = data.cart_count;
                } else {
                    // Force update cart count
                    this.updateCartCount(true);
                }
                this.showNotification(data.message || 'Product added to cart successfully!', 'success');
            } else {
                this.showNotification(data.message || 'Failed to add product to cart.', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            this.showNotification('An error occurred. Please try again.', 'error');
        } finally {
            this.loading = false;
        }
    }
}));

// Checkout Coupon functionality
Alpine.data('checkoutCoupon', (subtotal, validateUrl, shippingUrl) => ({
    couponCode: '',
    discount: 0,
    couponApplied: false,
    loading: false,
    error: '',
    subtotal: subtotal,
    shippingCost: 0,
    shippingCalculated: false,
    
    init() {
        // Calculate shipping if district is already selected
        this.$nextTick(() => {
            const districtSelect = document.getElementById('shipping_district');
            if (districtSelect && districtSelect.value) {
                this.calculateShippingCost();
            }
        });
    },
    
    async calculateShippingCost() {
        const districtSelect = document.getElementById('shipping_district');
        if (!districtSelect || !districtSelect.value) {
            this.shippingCost = 0;
            this.shippingCalculated = false;
            return;
        }
        
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (!csrfToken) return;
            
            const response = await fetch(shippingUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken.content,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ district: districtSelect.value })
            });
            
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            
            const data = await response.json();
            if (data.success) {
                this.shippingCost = parseFloat(data.shipping_cost) || 0;
                this.shippingCalculated = true;
            }
        } catch (error) {
            console.error('Shipping calculation error:', error);
            this.shippingCost = 0;
            this.shippingCalculated = false;
        }
    },
    
    async applyCoupon() {
        if (!this.couponCode.trim()) {
            this.error = 'Please enter a coupon code';
            return;
        }
        this.loading = true;
        this.error = '';
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (!csrfToken) {
                this.error = 'CSRF token not found. Please refresh the page.';
                this.loading = false;
                return;
            }
            
            const response = await fetch(validateUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken.content,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ code: this.couponCode.trim() })
            });
            
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            
            const data = await response.json();
            if (data.valid && data.discount) {
                this.discount = parseFloat(data.discount);
                if (isNaN(this.discount)) {
                    this.discount = 0;
                }
                this.couponApplied = true;
                this.error = '';
                console.log('Coupon applied successfully. Discount:', this.discount);
            } else {
                this.error = data.message || 'Invalid coupon code';
                this.couponApplied = false;
                this.discount = 0;
            }
        } catch (error) {
            console.error('Coupon validation error:', error);
            this.error = 'Failed to validate coupon. Please try again.';
            this.couponApplied = false;
            this.discount = 0;
        } finally {
            this.loading = false;
        }
    },
    
    removeCoupon() {
        this.couponCode = '';
        this.discount = 0;
        this.couponApplied = false;
        this.error = '';
    },
    
    getTotal() {
        const discountAmount = this.couponApplied ? parseFloat(this.discount) : 0;
        const shipping = parseFloat(this.shippingCost) || 0;
        const subtotalAmount = parseFloat(this.subtotal);
        const tax = (subtotalAmount - discountAmount + shipping) * 0.1; // 10% tax
        const total = subtotalAmount - discountAmount + shipping + tax;
        return total.toFixed(2);
    },
    
    getShippingCost() {
        return parseFloat(this.shippingCost) || 0;
    },
    
    getTax() {
        const discountAmount = this.couponApplied ? parseFloat(this.discount) : 0;
        const shipping = parseFloat(this.shippingCost) || 0;
        const subtotalAmount = parseFloat(this.subtotal);
        return ((subtotalAmount - discountAmount + shipping) * 0.1).toFixed(2);
    }
}));

// Banner Slider functionality
Alpine.data('bannerSlider', (totalSlides) => ({
    currentSlide: 0,
    totalSlides: totalSlides,
    autoplayInterval: null,

    init() {
        // Auto-play slider (change slide every 5 seconds)
        if (this.totalSlides > 1) {
            this.startAutoplay();
        }
    },

    startAutoplay() {
        this.autoplayInterval = setInterval(() => {
            this.nextSlide();
        }, 5000);
    },

    stopAutoplay() {
        if (this.autoplayInterval) {
            clearInterval(this.autoplayInterval);
            this.autoplayInterval = null;
        }
    },

    nextSlide() {
        this.currentSlide = (this.currentSlide + 1) % this.totalSlides;
        this.restartAutoplay();
    },

    previousSlide() {
        this.currentSlide = (this.currentSlide - 1 + this.totalSlides) % this.totalSlides;
        this.restartAutoplay();
    },

    goToSlide(index) {
        this.currentSlide = index;
        this.restartAutoplay();
    },

    restartAutoplay() {
        this.stopAutoplay();
        if (this.totalSlides > 1) {
            this.startAutoplay();
        }
    }
}));

// Start Alpine immediately for cart functionality
// Cart updates need to work immediately, so we can't defer too much
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        Alpine.start();
    });
} else {
    // Start immediately if DOM is already loaded
    Alpine.start();
}
