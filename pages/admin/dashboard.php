<?php
session_start();
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Controllers\AdminAuthController;
use App\configs\Database;

// Check if admin is logged in
AdminAuthController::checkAuth();

// Get database connection
$database = new Database();
$conn = $database->connect();

// Fetch stats
$totalOrders = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch()['count'] ?? 0;
$totalProducts = $conn->query("SELECT COUNT(*) as count FROM products")->fetch()['count'] ?? 0;
$totalCustomers = $conn->query("SELECT COUNT(*) as count FROM users")->fetch()['count'] ?? 0;
$totalSales = $conn->query("SELECT COALESCE(SUM(total_amount), 0) as total FROM orders WHERE status = 'Completed'")->fetch()['total'] ?? 0;

// Fetch recent orders
$recentOrdersQuery = "SELECT o.id, o.total_amount, o.status, o.created_at, u.name as customer_name 
                      FROM orders o 
                      LEFT JOIN users u ON o.user_id = u.id 
                      ORDER BY o.created_at DESC 
                      LIMIT 5";
$recentOrders = $conn->query($recentOrdersQuery)->fetchAll(PDO::FETCH_ASSOC);

// Fetch top products
$topProductsQuery = "SELECT p.title, p.image, COUNT(oi.id) as sales_count, SUM(oi.price * oi.quantity) as revenue
                     FROM products p
                     LEFT JOIN order_items oi ON p.id = oi.product_id
                     GROUP BY p.id
                     ORDER BY sales_count DESC
                     LIMIT 5";
$topProducts = $conn->query($topProductsQuery)->fetchAll(PDO::FETCH_ASSOC);

// Fetch pending orders count
$pendingOrders = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'Pending'")->fetch()['count'] ?? 0;

// Fetch new contact messages
$newContacts = $conn->query("SELECT COUNT(*) as count FROM contacts WHERE status = 'New'")->fetch()['count'] ?? 0;

// Fetch recent contacts
$recentContactsQuery = "SELECT id, name, email, subject, status, created_at 
                        FROM contacts 
                        ORDER BY created_at DESC 
                        LIMIT 5";
$recentContacts = $conn->query($recentContactsQuery)->fetchAll(PDO::FETCH_ASSOC);

// Fetch low stock products
$lowStockQuery = "SELECT id, title, stock FROM products WHERE stock < 10 AND stock > 0 ORDER BY stock ASC LIMIT 5";
$lowStockProducts = $conn->query($lowStockQuery)->fetchAll(PDO::FETCH_ASSOC);

// Fetch out of stock count
$outOfStock = $conn->query("SELECT COUNT(*) as count FROM products WHERE stock = 0")->fetch()['count'] ?? 0;

include "include/header.php"; 
?>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Card 1 -->
        <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-primary">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 mb-1">Total Sales</p>
                    <h3 class="text-2xl font-bold text-gray-800">$<?php echo number_format($totalSales, 2); ?></h3>
                </div>
                <div class="p-3 bg-primary/10 rounded-full text-primary">
                    <i class="fas fa-dollar-sign text-xl"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <span class="text-gray-400">Completed orders only</span>
            </div>
        </div>

        <!-- Card 2 -->
        <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-secondary">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 mb-1">Total Orders</p>
                    <h3 class="text-2xl font-bold text-gray-800"><?php echo $totalOrders; ?></h3>
                </div>
                <div class="p-3 bg-secondary/10 rounded-full text-secondary">
                    <i class="fas fa-shopping-bag text-xl"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <span class="text-gray-400">All time orders</span>
            </div>
        </div>

        <!-- Card 3 -->
        <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 mb-1">Total Products</p>
                    <h3 class="text-2xl font-bold text-gray-800"><?php echo $totalProducts; ?></h3>
                </div>
                <div class="p-3 bg-blue-100 rounded-full text-blue-500">
                    <i class="fas fa-box text-xl"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <span class="text-gray-400">In catalog</span>
            </div>
        </div>

        <!-- Card 4 -->
        <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-orange-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 mb-1">Total Customers</p>
                    <h3 class="text-2xl font-bold text-gray-800"><?php echo $totalCustomers; ?></h3>
                </div>
                <div class="p-3 bg-orange-100 rounded-full text-orange-500">
                    <i class="fas fa-users text-xl"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <span class="text-gray-400">Registered users</span>
            </div>
        </div>
    </div>

    <!-- Quick Stats Row -->
    <?php if ($pendingOrders > 0 || $outOfStock > 0 || $newContacts > 0): ?>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <?php if ($pendingOrders > 0): ?>
        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-yellow-700 mb-1">Pending Orders</p>
                    <h3 class="text-2xl font-bold text-yellow-800"><?php echo $pendingOrders; ?></h3>
                    <p class="text-sm text-yellow-600 mt-2">Require attention</p>
                </div>
                <div class="p-3 bg-yellow-100 rounded-full text-yellow-600">
                    <i class="fas fa-clock text-2xl"></i>
                </div>
            </div>
            <div class="mt-4">
                <a href="?page=admin/orders" class="text-yellow-700 text-sm font-medium hover:underline">View pending orders →</a>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($outOfStock > 0): ?>
        <div class="bg-red-50 border border-red-200 rounded-xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-red-700 mb-1">Out of Stock</p>
                    <h3 class="text-2xl font-bold text-red-800"><?php echo $outOfStock; ?></h3>
                    <p class="text-sm text-red-600 mt-2">Products need restocking</p>
                </div>
                <div class="p-3 bg-red-100 rounded-full text-red-600">
                    <i class="fas fa-exclamation-triangle text-2xl"></i>
                </div>
            </div>
            <div class="mt-4">
                <a href="?page=admin/products" class="text-red-700 text-sm font-medium hover:underline">View products →</a>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($newContacts > 0): ?>
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-blue-700 mb-1">New Messages</p>
                    <h3 class="text-2xl font-bold text-blue-800"><?php echo $newContacts; ?></h3>
                    <p class="text-sm text-blue-600 mt-2">Unread contact messages</p>
                </div>
                <div class="p-3 bg-blue-100 rounded-full text-blue-600">
                    <i class="fas fa-envelope text-2xl"></i>
                </div>
            </div>
            <div class="mt-4">
                <a href="?page=admin/contacts" class="text-blue-700 text-sm font-medium hover:underline">View messages →</a>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Recent Orders & Top Products -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Recent Orders -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="p-6 border-b border-gray-100 flex justify-between items-center">
                <h3 class="text-lg font-bold text-gray-800">Recent Orders</h3>
                <a href="?page=admin/orders" class="text-primary hover:text-primary-dark text-sm font-medium">View All</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 text-gray-600 text-xs uppercase tracking-wider">
                            <th class="p-4 font-semibold">Order ID</th>
                            <th class="p-4 font-semibold">Customer</th>
                            <th class="p-4 font-semibold">Product</th>
                            <th class="p-4 font-semibold">Amount</th>
                            <th class="p-4 font-semibold">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm">
                        <?php if (empty($recentOrders)): ?>
                            <tr>
                                <td colspan="5" class="p-8 text-center text-gray-500">
                                    <i class="fas fa-shopping-cart text-4xl mb-2 text-gray-300"></i>
                                    <p>No recent orders</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php 
                            $statusColors = [
                                'Pending' => 'bg-yellow-100 text-yellow-700',
                                'Processing' => 'bg-blue-100 text-blue-700',
                                'Completed' => 'bg-green-100 text-green-700',
                                'Cancelled' => 'bg-red-100 text-red-700'
                            ];
                            foreach ($recentOrders as $order): 
                                $statusColor = $statusColors[$order['status']] ?? 'bg-gray-100 text-gray-700';
                                
                                // Get order items for this order
                                $itemsQuery = "SELECT p.title FROM order_items oi 
                                             LEFT JOIN products p ON oi.product_id = p.id 
                                             WHERE oi.order_id = ? LIMIT 1";
                                $itemsStmt = $conn->prepare($itemsQuery);
                                $itemsStmt->execute([$order['id']]);
                                $firstItem = $itemsStmt->fetch(PDO::FETCH_ASSOC);
                                
                                // Count total items
                                $countQuery = "SELECT COUNT(*) as count FROM order_items WHERE order_id = ?";
                                $countStmt = $conn->prepare($countQuery);
                                $countStmt->execute([$order['id']]);
                                $itemCount = $countStmt->fetch()['count'];
                            ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="p-4 font-medium text-primary">#<?php echo $order['id']; ?></td>
                                <td class="p-4 text-gray-600"><?php echo htmlspecialchars($order['customer_name'] ?? 'Unknown'); ?></td>
                                <td class="p-4 text-gray-600">
                                    <?php 
                                    if ($firstItem) {
                                        echo htmlspecialchars($firstItem['title']);
                                        if ($itemCount > 1) {
                                            echo ' <span class="text-xs text-gray-400">(+' . ($itemCount - 1) . ' more)</span>';
                                        }
                                    } else {
                                        echo 'No items';
                                    }
                                    ?>
                                </td>
                                <td class="p-4 font-medium text-gray-900">$<?php echo number_format($order['total_amount'], 2); ?></td>
                                <td class="p-4">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full <?php echo $statusColor; ?>">
                                        <?php echo htmlspecialchars($order['status']); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Top Products -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="p-6 border-b border-gray-100">
                <h3 class="text-lg font-bold text-gray-800">Top Selling Products</h3>
            </div>
            <div class="p-6 space-y-6">
                <?php if (empty($topProducts)): ?>
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-box-open text-4xl mb-2 text-gray-300"></i>
                        <p>No product sales yet</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($topProducts as $product): ?>
                        <div class="flex items-center">
                            <div class="w-12 h-12 rounded-lg bg-gray-100 flex-shrink-0 overflow-hidden">
                                <?php if ($product['image']): ?>
                                    <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['title']); ?>" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <div class="w-full h-full flex items-center justify-center text-gray-400">
                                        <i class="fas fa-image"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="ml-4 flex-1">
                                <h4 class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($product['title']); ?></h4>
                                <p class="text-xs text-gray-500"><?php echo $product['sales_count'] > 0 ? $product['sales_count'] . ' sales' : 'No sales'; ?></p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-bold text-gray-900">$<?php echo number_format($product['revenue'] ?? 0, 2); ?></p>
                                <?php if ($product['sales_count'] > 0): ?>
                                    <p class="text-xs text-green-500"><?php echo $product['sales_count']; ?> sold</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <div class="p-4 border-t border-gray-100 text-center">
                <a href="?page=admin/products" class="text-primary text-sm font-medium hover:underline">View All Products</a>
            </div>
        </div>
    </div>

    <!-- Low Stock Alert -->
    <?php if (!empty($lowStockProducts)): ?>
    <div class="mt-8 bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex justify-between items-center">
            <div>
                <h3 class="text-lg font-bold text-gray-800">Low Stock Alert</h3>
                <p class="text-sm text-gray-500 mt-1">Products with less than 10 items in stock</p>
            </div>
            <a href="?page=admin/products" class="text-primary hover:text-primary-dark text-sm font-medium">View All Products</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-gray-600 text-xs uppercase tracking-wider">
                        <th class="p-4 font-semibold">Product ID</th>
                        <th class="p-4 font-semibold">Product Name</th>
                        <th class="p-4 font-semibold">Stock Level</th>
                        <th class="p-4 font-semibold">Status</th>
                        <th class="p-4 font-semibold text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    <?php foreach ($lowStockProducts as $product): ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="p-4 font-medium text-gray-900">#<?php echo $product['id']; ?></td>
                        <td class="p-4 text-gray-900"><?php echo htmlspecialchars($product['title']); ?></td>
                        <td class="p-4">
                            <div class="flex items-center">
                                <div class="w-24 bg-gray-200 rounded-full h-2 mr-2">
                                    <div class="bg-<?php echo $product['stock'] < 5 ? 'red' : 'yellow'; ?>-500 h-2 rounded-full" style="width: <?php echo min(100, $product['stock'] * 10); ?>%"></div>
                                </div>
                                <span class="font-medium <?php echo $product['stock'] < 5 ? 'text-red-600' : 'text-yellow-600'; ?>">
                                    <?php echo $product['stock']; ?> units
                                </span>
                            </div>
                        </td>
                        <td class="p-4">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full <?php echo $product['stock'] < 5 ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700'; ?>">
                                <?php echo $product['stock'] < 5 ? 'Critical' : 'Low Stock'; ?>
                            </span>
                        </td>
                        <td class="p-4 text-right">
                            <a href="?page=admin/add-product&id=<?php echo $product['id']; ?>" class="text-primary hover:text-primary-dark font-medium text-sm">
                                <i class="fas fa-edit mr-1"></i>Restock
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- Recent Contact Messages -->
    <?php if (!empty($recentContacts)): ?>
    <div id="contactSection" class="mt-8 bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex justify-between items-center">
            <div>
                <h3 class="text-lg font-bold text-gray-800">Recent Contact Messages</h3>
                <p class="text-sm text-gray-500 mt-1">Latest messages from customers</p>
            </div>
            <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm font-semibold">
                <?php echo $newContacts; ?> New
            </span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-gray-600 text-xs uppercase tracking-wider">
                        <th class="p-4 font-semibold">ID</th>
                        <th class="p-4 font-semibold">Name</th>
                        <th class="p-4 font-semibold">Email</th>
                        <th class="p-4 font-semibold">Subject</th>
                        <th class="p-4 font-semibold">Status</th>
                        <th class="p-4 font-semibold">Date</th>
                        <th class="p-4 font-semibold text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    <?php 
                    $statusColors = [
                        'New' => 'bg-blue-100 text-blue-700',
                        'Read' => 'bg-gray-100 text-gray-700',
                        'Replied' => 'bg-green-100 text-green-700',
                        'Closed' => 'bg-red-100 text-red-700'
                    ];
                    foreach ($recentContacts as $contact): 
                        $statusColor = $statusColors[$contact['status']] ?? 'bg-gray-100 text-gray-700';
                    ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="p-4 font-medium text-gray-900">#<?php echo $contact['id']; ?></td>
                        <td class="p-4 text-gray-900"><?php echo htmlspecialchars($contact['name']); ?></td>
                        <td class="p-4 text-gray-600"><?php echo htmlspecialchars($contact['email']); ?></td>
                        <td class="p-4 text-gray-900"><?php echo htmlspecialchars($contact['subject']); ?></td>
                        <td class="p-4">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full <?php echo $statusColor; ?>">
                                <?php echo htmlspecialchars($contact['status']); ?>
                            </span>
                        </td>
                        <td class="p-4 text-gray-600"><?php echo date('M d, Y', strtotime($contact['created_at'])); ?></td>
                        <td class="p-4 text-right">
                            <a href="?page=admin/contacts&view=<?php echo $contact['id']; ?>" class="text-blue-500 hover:text-blue-700 font-medium text-sm">
                                <i class="fas fa-eye mr-1"></i>View
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-gray-100 text-center">
            <p class="text-sm text-gray-500">Contact management page coming soon</p>
        </div>
    </div>
    <?php endif; ?>

    <script>
        function viewContact(id) {
            // TODO: Implement contact detail view
            alert('Contact detail view will be implemented in admin panel. Contact ID: ' + id);
        }
    </script>

<?php include "include/footer.php"; ?>
