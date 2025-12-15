<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Controllers\ProductController;
use App\Controllers\CategoryController;

$productController = new ProductController();
$categoryController = new CategoryController();

// Get filter parameters
$selectedCategory = isset($_GET['category']) ? intval($_GET['category']) : null;
$sortBy = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';

// Get all categories for filter
$categories = $categoryController->getAllCategories();

// Pagination settings
$itemsPerPage = 9;
$currentPage = isset($_GET['pg']) ? max(1, intval($_GET['pg'])) : 1;
$offset = ($currentPage - 1) * $itemsPerPage;

// Build query based on filters
$query = "SELECT p.*, c.name as category_name FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          WHERE 1=1";
$params = [];

if ($selectedCategory) {
    $query .= " AND p.category_id = ?";
    $params[] = $selectedCategory;
}

if ($searchQuery) {
    $query .= " AND (p.title LIKE ? OR p.description LIKE ?)";
    $params[] = "%$searchQuery%";
    $params[] = "%$searchQuery%";
}

// Apply sorting
switch ($sortBy) {
    case 'price-low':
        $query .= " ORDER BY p.price ASC";
        break;
    case 'price-high':
        $query .= " ORDER BY p.price DESC";
        break;
    case 'name':
        $query .= " ORDER BY p.title ASC";
        break;
    case 'newest':
    default:
        $query .= " ORDER BY p.created_at DESC";
        break;
}

// Count total products for pagination
$countQuery = str_replace("SELECT p.*, c.name as category_name", "SELECT COUNT(*)", $query);
$countQuery = preg_replace('/ORDER BY.*$/', '', $countQuery);

// Get database connection
$database = new \App\configs\Database();
$conn = $database->connect();

$countStmt = $conn->prepare($countQuery);
$countStmt->execute($params);
$totalProducts = $countStmt->fetchColumn();
$totalPages = ceil($totalProducts / $itemsPerPage);

// Get products for current page - use direct values for LIMIT/OFFSET (they're already integers)
$query .= " LIMIT " . intval($itemsPerPage) . " OFFSET " . intval($offset);

$stmt = $conn->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

include "include/header.php"; 
?>

    <!-- Page Title -->
    <section class="bg-gray-100 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-4xl font-bold text-gray-900 mb-2">Our Products</h1>
            <p class="text-gray-600">Explore our wide range of quality products</p>
            <?php if ($searchQuery): ?>
                <p class="text-sm text-gray-500 mt-2">
                    Search results for: <strong><?php echo htmlspecialchars($searchQuery); ?></strong>
                    <a href="?page=products" class="text-primary hover:underline ml-2">Clear search</a>
                </p>
            <?php endif; ?>
        </div>
    </section>

    <!-- Products Section -->
    <section class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row gap-8">
                <!-- Sidebar Filters -->
                <div class="w-full md:w-1/4">
                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <h5 class="text-xl font-bold mb-6 text-gray-900">Filters</h5>
                        
                        <!-- Search -->
                        <div class="mb-6">
                            <form method="GET" action="">
                                <input type="hidden" name="page" value="products">
                                <input type="text" name="search" value="<?php echo htmlspecialchars($searchQuery); ?>" placeholder="Search products..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary">
                                <button type="submit" class="w-full mt-2 bg-primary hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded transition duration-300">
                                    <i class="fas fa-search mr-2"></i>Search
                                </button>
                            </form>
                        </div>

                        <!-- Category Filter -->
                        <div class="mb-6">
                            <h6 class="font-semibold text-gray-900 mb-3">Category</h6>
                            <div class="space-y-2">
                                <div class="flex items-center">
                                    <input type="radio" name="category" id="cat-all" <?php echo !$selectedCategory ? 'checked' : ''; ?> onchange="filterCategory(null)" class="h-4 w-4 text-primary focus:ring-primary border-gray-300">
                                    <label for="cat-all" class="ml-2 text-gray-700 cursor-pointer">All Categories</label>
                                </div>
                                <?php foreach ($categories as $category): ?>
                                <div class="flex items-center">
                                    <input type="radio" name="category" id="cat-<?php echo $category['id']; ?>" <?php echo $selectedCategory == $category['id'] ? 'checked' : ''; ?> onchange="filterCategory(<?php echo $category['id']; ?>)" class="h-4 w-4 text-primary focus:ring-primary border-gray-300">
                                    <label for="cat-<?php echo $category['id']; ?>" class="ml-2 text-gray-700 cursor-pointer"><?php echo htmlspecialchars($category['name']); ?></label>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <?php if ($selectedCategory || $searchQuery): ?>
                        <a href="?page=products" class="w-full inline-block text-center bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold py-2 px-4 rounded transition duration-300">
                            <i class="fas fa-times mr-2"></i>Clear Filters
                        </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Products Grid -->
                <div class="w-full md:w-3/4">
                    <!-- Sort Options & Results Count -->
                    <div class="flex justify-between items-center mb-6">
                        <p class="text-gray-600">
                            Showing <?php echo count($products); ?> of <?php echo $totalProducts; ?> products
                        </p>
                        <select class="form-select block w-48 pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm rounded-md" id="sortSelect" onchange="sortProducts(this.value)">
                            <option value="newest" <?php echo $sortBy == 'newest' ? 'selected' : ''; ?>>Newest</option>
                            <option value="price-low" <?php echo $sortBy == 'price-low' ? 'selected' : ''; ?>>Price: Low to High</option>
                            <option value="price-high" <?php echo $sortBy == 'price-high' ? 'selected' : ''; ?>>Price: High to Low</option>
                            <option value="name" <?php echo $sortBy == 'name' ? 'selected' : ''; ?>>Name A-Z</option>
                        </select>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <?php if (empty($products)): ?>
                            <div class="col-span-3 text-center py-16">
                                <i class="fas fa-box-open text-6xl text-gray-300 mb-4"></i>
                                <h3 class="text-2xl font-semibold text-gray-700 mb-2">No Products Found</h3>
                                <p class="text-gray-500 mb-4">Try adjusting your filters or search query</p>
                                <a href="?page=products" class="text-primary hover:underline">View all products</a>
                            </div>
                        <?php else: ?>
                            <?php foreach ($products as $product): ?>
                            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition duration-300">
                                <div class="relative h-48 bg-gray-100">
                                    <?php if ($product['image']): ?>
                                        <img src="<?php echo htmlspecialchars($product['image']); ?>" class="w-full h-full object-cover" alt="<?php echo htmlspecialchars($product['title']); ?>">
                                    <?php else: ?>
                                        <div class="w-full h-full flex items-center justify-center text-gray-400">
                                            <i class="fas fa-image text-4xl"></i>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($product['stock'] == 0): ?>
                                        <div class="absolute top-2 right-2 bg-red-500 text-white px-2 py-1 rounded text-xs font-semibold">
                                            Out of Stock
                                        </div>
                                    <?php elseif ($product['stock'] < 10): ?>
                                        <div class="absolute top-2 right-2 bg-yellow-500 text-white px-2 py-1 rounded text-xs font-semibold">
                                            Only <?php echo $product['stock']; ?> left
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="p-4">
                                    <div class="text-xs text-gray-500 mb-1"><?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?></div>
                                    <h5 class="text-lg font-semibold text-gray-900 mb-1"><?php echo htmlspecialchars($product['title']); ?></h5>
                                    <p class="text-gray-600 text-sm mb-3 line-clamp-2">
                                        <?php echo htmlspecialchars(substr($product['description'], 0, 80)); ?>
                                        <?php if (strlen($product['description']) > 80) echo '...'; ?>
                                    </p>
                                    <div class="flex justify-between items-center">
                                        <span class="text-xl font-bold text-primary">$<?php echo number_format($product['price'], 2); ?></span>
                                        <?php if ($product['stock'] > 0): ?>
                                            <button onclick="addToCart(<?php echo $product['id']; ?>)" class="bg-primary hover:bg-indigo-700 text-white py-1 px-3 rounded transition duration-300">
                                                <i class="fas fa-shopping-cart mr-1"></i>Add to Cart
                                            </button>
                                        <?php else: ?>
                                            <button disabled class="bg-gray-300 text-gray-500 py-1 px-3 rounded cursor-not-allowed">
                                                Out of Stock
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                    <div class="mt-8 flex justify-center">
                        <nav class="flex items-center gap-2">
                            <?php if ($currentPage > 1): ?>
                                <a href="?page=products&pg=<?php echo $currentPage - 1; ?><?php echo $selectedCategory ? '&category=' . $selectedCategory : ''; ?><?php echo $sortBy ? '&sort=' . $sortBy : ''; ?><?php echo $searchQuery ? '&search=' . urlencode($searchQuery) : ''; ?>" class="px-3 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            <?php endif; ?>

                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <?php if ($i == $currentPage): ?>
                                    <span class="px-4 py-2 bg-primary text-white rounded-md font-semibold"><?php echo $i; ?></span>
                                <?php else: ?>
                                    <a href="?page=products&pg=<?php echo $i; ?><?php echo $selectedCategory ? '&category=' . $selectedCategory : ''; ?><?php echo $sortBy ? '&sort=' . $sortBy : ''; ?><?php echo $searchQuery ? '&search=' . urlencode($searchQuery) : ''; ?>" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                                        <?php echo $i; ?>
                                    </a>
                                <?php endif; ?>
                            <?php endfor; ?>

                            <?php if ($currentPage < $totalPages): ?>
                                <a href="?page=products&pg=<?php echo $currentPage + 1; ?><?php echo $selectedCategory ? '&category=' . $selectedCategory : ''; ?><?php echo $sortBy ? '&sort=' . $sortBy : ''; ?><?php echo $searchQuery ? '&search=' . urlencode($searchQuery) : ''; ?>" class="px-3 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php endif; ?>
                        </nav>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <script>
        function filterCategory(categoryId) {
            const params = new URLSearchParams(window.location.search);
            params.set('page', 'products');
            if (categoryId) {
                params.set('category', categoryId);
            } else {
                params.delete('category');
            }
            params.delete('pg'); // Reset to page 1
            window.location.href = '?' + params.toString();
        }

        function sortProducts(sortBy) {
            const params = new URLSearchParams(window.location.search);
            params.set('page', 'products');
            params.set('sort', sortBy);
            params.delete('pg'); // Reset to page 1
            window.location.href = '?' + params.toString();
        }

        function addToCart(productId) {
            // TODO: Implement cart functionality
            alert('Product added to cart! (Cart functionality coming soon)');
            // Future implementation will use AJAX to add to session cart
        }
    </script>

    <?php include "include/footer.php"; ?>