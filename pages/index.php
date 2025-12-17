
<?php 
require_once __DIR__ . '/../vendor/autoload.php';

use App\Controllers\ProductController;

include "include/header.php"; 

// Fetch featured products (latest 6 products)
$productController = new ProductController();
$featuredProducts = $productController->getFeaturedProducts(6);
?>

    <!-- Hero Section -->
    <section class="relative bg-gradient-to-br from-indigo-50 via-white to-purple-50 py-20 overflow-hidden">
        <!-- Background decorative elements -->
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute -top-40 -right-40 w-80 h-80 bg-primary opacity-10 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-purple-400 opacity-10 rounded-full blur-3xl"></div>
        </div>
        
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
            <div class="flex flex-col md:flex-row items-center gap-12">
                <div class="md:w-1/2 mb-10 md:mb-0 z-10">
                    <div class="inline-block bg-primary/10 text-primary px-4 py-2 rounded-full text-sm font-semibold mb-4">
                        <i class="fas fa-star mr-2"></i>Trusted by 10,000+ Customers
                    </div>
                    <h1 class="text-5xl md:text-7xl font-extrabold text-gray-900 mb-6 leading-tight">
                        Shop Smart,<br>
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-primary to-purple-600">Live Better</span>
                    </h1>
                    <p class="text-xl text-gray-600 mb-8 leading-relaxed">Discover premium products at unbeatable prices. Fast delivery, secure payments, and 30-day money-back guarantee.</p>
                    
                    <div class="flex flex-wrap gap-4 mb-8">
                        <a href="?page=products" class="inline-flex items-center bg-primary hover:bg-indigo-700 text-white font-bold py-4 px-8 rounded-full transition duration-300 shadow-lg transform hover:-translate-y-1 hover:shadow-xl">
                            <i class="fas fa-shopping-bag mr-2"></i>
                            Start Shopping
                        </a>
                        <a href="?page=about" class="inline-flex items-center bg-white hover:bg-gray-50 text-gray-900 font-bold py-4 px-8 rounded-full transition duration-300 shadow-md border-2 border-gray-200">
                            <i class="fas fa-info-circle mr-2"></i>
                            Learn More
                        </a>
                    </div>
                    
                    <!-- Stats -->
                    <div class="grid grid-cols-3 gap-6 pt-8 border-t border-gray-200">
                        <?php
                        // Fetch quick stats
                        $stats = $productController->getHeroStats();
                        ?>
                        <div>
                            <div class="text-3xl font-bold text-primary"><?php echo number_format($stats['products_count'] ?? 0); ?>+</div>
                            <div class="text-sm text-gray-600">Products</div>
                        </div>
                        <div>
                            <div class="text-3xl font-bold text-primary"><?php echo number_format($stats['customers_count'] ?? 0); ?>+</div>
                            <div class="text-sm text-gray-600">Customers</div>
                        </div>
                        <div>
                            <div class="text-3xl font-bold text-primary"><?php echo number_format($stats['orders_count'] ?? 0); ?>+</div>
                            <div class="text-sm text-gray-600">Orders</div>
                        </div>
                    </div>
                </div>
                
                <div class="md:w-1/2 relative z-10">
                    <!-- Product showcase grid -->
                    <div class="grid grid-cols-2 gap-4">
                        <?php 
                        $heroProducts = array_slice($featuredProducts, 0, 4);
                        if (!empty($heroProducts)):
                            foreach ($heroProducts as $index => $product): 
                                $imagePath = !empty($product['image']) ? 'uploads/products/' . $product['image'] : 'https://via.placeholder.com/300x300?text=No+Image';
                        ?>
                            <div class="<?php echo $index === 0 ? 'col-span-2' : ''; ?> bg-white rounded-2xl shadow-xl overflow-hidden transform hover:scale-105 transition duration-300 hover:shadow-2xl">
                                <img src="<?php echo htmlspecialchars($imagePath); ?>" 
                                     alt="<?php echo htmlspecialchars($product['title']); ?>" 
                                     class="w-full <?php echo $index === 0 ? 'h-64' : 'h-40'; ?> object-cover">
                                <div class="p-4">
                                    <h6 class="font-bold text-gray-900 mb-1 truncate"><?php echo htmlspecialchars($product['title']); ?></h6>
                                    <div class="flex justify-between items-center">
                                        <span class="text-primary font-bold text-lg">$<?php echo number_format($product['price'], 2); ?></span>
                                        <?php if ($product['stock'] > 0): ?>
                                            <span class="text-xs text-green-600 font-semibold"><i class="fas fa-check-circle"></i> In Stock</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php 
                            endforeach;
                        else:
                        ?>
                            <div class="col-span-2 bg-gradient-to-br from-primary to-purple-600 rounded-2xl shadow-xl p-12 text-center text-white">
                                <i class="fas fa-shopping-cart text-6xl mb-4 opacity-80"></i>
                                <h3 class="text-2xl font-bold mb-2">Coming Soon!</h3>
                                <p class="text-indigo-100">Amazing products will be available shortly</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Floating badges -->
                    <div class="absolute -top-4 -right-4 bg-yellow-400 text-yellow-900 px-6 py-3 rounded-full font-bold shadow-lg transform rotate-12 animate-pulse">
                        <i class="fas fa-bolt mr-1"></i> Hot Deals!
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Products -->
    <section class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold text-center text-gray-900 mb-12">Featured Products</h2>
            
            <?php if (!empty($featuredProducts)): ?>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <?php foreach ($featuredProducts as $product): ?>
                        <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition duration-300 group">
                            <div class="relative overflow-hidden">
                                <?php 
                                $imagePath = !empty($product['image']) ? 'uploads/products/' . $product['image'] : 'https://via.placeholder.com/400x300?text=No+Image';
                                ?>
                                <img src="<?php echo htmlspecialchars($imagePath); ?>" class="w-full h-56 object-cover transform group-hover:scale-110 transition duration-500" alt="<?php echo htmlspecialchars($product['title']); ?>">
                                <div class="absolute inset-0 bg-black bg-opacity-20 opacity-0 group-hover:opacity-100 transition duration-300 flex items-center justify-center">
                                    <a href="?page=products" class="bg-white text-gray-900 py-2 px-4 rounded-full font-bold hover:bg-primary hover:text-white transition duration-300">Quick View</a>
                                </div>
                                <?php if ($product['stock'] <= 0): ?>
                                    <div class="absolute top-2 right-2 bg-red-500 text-white px-3 py-1 rounded-full text-xs font-bold">Out of Stock</div>
                                <?php elseif ($product['stock'] <= 5): ?>
                                    <div class="absolute top-2 right-2 bg-yellow-500 text-white px-3 py-1 rounded-full text-xs font-bold">Low Stock</div>
                                <?php endif; ?>
                            </div>
                            <div class="p-6">
                                <h5 class="text-xl font-semibold text-gray-900 mb-2"><?php echo htmlspecialchars($product['title']); ?></h5>
                                <p class="text-gray-600 mb-4 text-sm line-clamp-2"><?php echo htmlspecialchars(substr($product['description'], 0, 60)) . '...'; ?></p>
                                <div class="flex justify-between items-center">
                                    <span class="text-2xl font-bold text-primary">$<?php echo number_format($product['price'], 2); ?></span>
                                    <?php if ($product['stock'] > 0): ?>
                                        <button onclick="addToCart(<?php echo $product['id']; ?>)" class="bg-primary hover:bg-indigo-700 text-white py-2 px-4 rounded-lg transition duration-300 flex items-center">
                                            <i class="fas fa-cart-plus mr-2"></i> Add
                                        </button>
                                    <?php else: ?>
                                        <button disabled class="bg-gray-400 text-white py-2 px-4 rounded-lg cursor-not-allowed flex items-center">
                                            <i class="fas fa-ban mr-2"></i> Unavailable
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-12">
                    <i class="fas fa-box-open text-6xl text-gray-300 mb-4"></i>
                    <p class="text-gray-500 text-lg">No products available at the moment.</p>
                    <p class="text-gray-400 text-sm mt-2">Check back soon for new arrivals!</p>
                </div>
            <?php endif; ?>
            
            <div class="text-center mt-12">
                <a href="?page=products" class="inline-block border-2 border-primary text-primary hover:bg-primary hover:text-white font-bold py-3 px-8 rounded-full transition duration-300">View All Products</a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-center">
                <div class="p-6 rounded-lg hover:bg-gray-50 transition duration-300">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-indigo-100 text-primary mb-6">
                        <i class="fas fa-shipping-fast text-3xl"></i>
                    </div>
                    <h5 class="text-xl font-bold text-gray-900 mb-2">Fast Shipping</h5>
                    <p class="text-gray-600">Get your orders delivered quickly to your doorstep with our express delivery partners.</p>
                </div>
                <div class="p-6 rounded-lg hover:bg-gray-50 transition duration-300">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-green-100 text-green-600 mb-6">
                        <i class="fas fa-shield-alt text-3xl"></i>
                    </div>
                    <h5 class="text-xl font-bold text-gray-900 mb-2">Secure Payment</h5>
                    <p class="text-gray-600">Safe and encrypted payment methods. We ensure your data is always protected.</p>
                </div>
                <div class="p-6 rounded-lg hover:bg-gray-50 transition duration-300">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-yellow-100 text-yellow-600 mb-6">
                        <i class="fas fa-undo text-3xl"></i>
                    </div>
                    <h5 class="text-xl font-bold text-gray-900 mb-2">Easy Returns</h5>
                    <p class="text-gray-600">30-day money-back guarantee. Not satisfied? Return it hassle-free.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
   <?php include "include/footer.php"; ?>