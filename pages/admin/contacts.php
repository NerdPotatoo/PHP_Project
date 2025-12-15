<?php
session_start();
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Controllers\AdminAuthController;
use App\Controllers\ContactController;

// Check if admin is logged in
AdminAuthController::checkAuth();

$contactController = new ContactController();

// Handle status update
if (isset($_GET['action']) && $_GET['action'] == 'update_status' && isset($_GET['id']) && isset($_GET['status'])) {
    $result = $contactController->updateContactStatus($_GET['id'], $_GET['status']);
    header('Location: ?page=admin/contacts&msg=' . urlencode($result['message']));
    exit();
}

// Handle delete
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $result = $contactController->deleteContact($_GET['id']);
    header('Location: ?page=admin/contacts&msg=' . urlencode($result['message']));
    exit();
}

// Get specific contact for detail view
$contactDetail = null;
if (isset($_GET['view']) && is_numeric($_GET['view'])) {
    $contactDetail = $contactController->getContactById($_GET['view']);
    // Mark as read if it's new
    if ($contactDetail && $contactDetail['status'] == 'New') {
        $contactController->updateContactStatus($_GET['view'], 'Read');
        $contactDetail['status'] = 'Read'; // Update local variable
    }
}

// Get all contacts
$contacts = $contactController->getAllContacts();

include "include/header.php"; 
?>

    <?php if (isset($_GET['msg'])): ?>
        <div id="successMessage" class="mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg transition-opacity duration-500">
            <?php echo htmlspecialchars($_GET['msg']); ?>
        </div>
    <?php endif; ?>

    <?php if ($contactDetail): ?>
        <!-- Contact Detail View -->
        <div class="mb-6">
            <a href="?page=admin/contacts" class="text-primary hover:text-primary-dark font-medium">
                <i class="fas fa-arrow-left mr-2"></i>Back to All Messages
            </a>
        </div>

        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="p-6 border-b border-gray-100 flex justify-between items-center">
                <div>
                    <h3 class="text-lg font-bold text-gray-800">Message Details</h3>
                    <p class="text-sm text-gray-500 mt-1">Contact ID: #<?php echo $contactDetail['id']; ?></p>
                </div>
                <div class="flex gap-2">
                    <select onchange="updateStatus(<?php echo $contactDetail['id']; ?>, this.value)" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary focus:border-primary">
                        <option value="New" <?php echo $contactDetail['status'] == 'New' ? 'selected' : ''; ?>>New</option>
                        <option value="Read" <?php echo $contactDetail['status'] == 'Read' ? 'selected' : ''; ?>>Read</option>
                        <option value="Replied" <?php echo $contactDetail['status'] == 'Replied' ? 'selected' : ''; ?>>Replied</option>
                        <option value="Closed" <?php echo $contactDetail['status'] == 'Closed' ? 'selected' : ''; ?>>Closed</option>
                    </select>
                    <button onclick="confirmDelete(<?php echo $contactDetail['id']; ?>)" class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg text-sm transition-colors">
                        <i class="fas fa-trash mr-1"></i>Delete
                    </button>
                </div>
            </div>

            <div class="p-6 space-y-6">
                <!-- Contact Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <label class="text-xs font-semibold text-gray-500 uppercase">Name</label>
                        <p class="text-gray-900 font-medium mt-1"><?php echo htmlspecialchars($contactDetail['name']); ?></p>
                    </div>

                    <div class="bg-gray-50 p-4 rounded-lg">
                        <label class="text-xs font-semibold text-gray-500 uppercase">Email</label>
                        <p class="text-gray-900 mt-1">
                            <a href="mailto:<?php echo htmlspecialchars($contactDetail['email']); ?>" class="text-primary hover:underline">
                                <?php echo htmlspecialchars($contactDetail['email']); ?>
                            </a>
                        </p>
                    </div>

                    <?php if ($contactDetail['phone']): ?>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <label class="text-xs font-semibold text-gray-500 uppercase">Phone</label>
                        <p class="text-gray-900 mt-1">
                            <a href="tel:<?php echo htmlspecialchars($contactDetail['phone']); ?>" class="text-primary hover:underline">
                                <?php echo htmlspecialchars($contactDetail['phone']); ?>
                            </a>
                        </p>
                    </div>
                    <?php endif; ?>

                    <div class="bg-gray-50 p-4 rounded-lg">
                        <label class="text-xs font-semibold text-gray-500 uppercase">Subject</label>
                        <p class="text-gray-900 font-medium mt-1"><?php echo htmlspecialchars($contactDetail['subject']); ?></p>
                    </div>

                    <div class="bg-gray-50 p-4 rounded-lg">
                        <label class="text-xs font-semibold text-gray-500 uppercase">Date Submitted</label>
                        <p class="text-gray-900 mt-1"><?php echo date('F d, Y \a\t g:i A', strtotime($contactDetail['created_at'])); ?></p>
                    </div>

                    <div class="bg-gray-50 p-4 rounded-lg">
                        <label class="text-xs font-semibold text-gray-500 uppercase">Newsletter Subscription</label>
                        <p class="text-gray-900 mt-1">
                            <?php if ($contactDetail['subscribe']): ?>
                                <span class="text-green-600"><i class="fas fa-check-circle mr-1"></i>Subscribed</span>
                            <?php else: ?>
                                <span class="text-gray-500"><i class="fas fa-times-circle mr-1"></i>Not subscribed</span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>

                <!-- Message Content -->
                <div class="bg-gray-50 p-6 rounded-lg">
                    <label class="text-xs font-semibold text-gray-500 uppercase block mb-3">Message</label>
                    <div class="text-gray-900 whitespace-pre-wrap"><?php echo htmlspecialchars($contactDetail['message']); ?></div>
                </div>

                <!-- Quick Reply -->
                <div class="border-t pt-6">
                    <h4 class="font-semibold text-gray-800 mb-3">Quick Reply</h4>
                    <a href="mailto:<?php echo htmlspecialchars($contactDetail['email']); ?>?subject=Re: <?php echo urlencode($contactDetail['subject']); ?>" 
                       class="inline-flex items-center px-6 py-3 bg-primary hover:bg-primary-dark text-white rounded-lg transition-colors">
                        <i class="fas fa-reply mr-2"></i>Reply via Email
                    </a>
                </div>
            </div>
        </div>

    <?php else: ?>
        <!-- Contact List View -->
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Contact Messages</h2>
                <p class="text-gray-600 mt-1">Manage customer inquiries and feedback</p>
            </div>
            <div class="text-right">
                <p class="text-sm text-gray-500">Total Messages</p>
                <p class="text-2xl font-bold text-gray-800"><?php echo count($contacts); ?></p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
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
                            <th class="p-4 font-semibold text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm">
                        <?php if (empty($contacts)): ?>
                            <tr>
                                <td colspan="7" class="p-8 text-center text-gray-500">
                                    <i class="fas fa-envelope text-4xl mb-2 text-gray-300"></i>
                                    <p>No contact messages found</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php 
                            $statusColors = [
                                'New' => 'bg-blue-100 text-blue-700',
                                'Read' => 'bg-gray-100 text-gray-700',
                                'Replied' => 'bg-green-100 text-green-700',
                                'Closed' => 'bg-red-100 text-red-700'
                            ];
                            foreach ($contacts as $contact): 
                                $statusColor = $statusColors[$contact['status']] ?? 'bg-gray-100 text-gray-700';
                            ?>
                            <tr class="hover:bg-gray-50 transition-colors <?php echo $contact['status'] == 'New' ? 'font-semibold' : ''; ?>">
                                <td class="p-4 text-gray-900">#<?php echo $contact['id']; ?></td>
                                <td class="p-4 text-gray-900">
                                    <?php echo htmlspecialchars($contact['name']); ?>
                                    <?php if ($contact['status'] == 'New'): ?>
                                        <span class="ml-2 inline-block w-2 h-2 bg-blue-500 rounded-full"></span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-4 text-gray-600"><?php echo htmlspecialchars($contact['email']); ?></td>
                                <td class="p-4 text-gray-900"><?php echo htmlspecialchars($contact['subject']); ?></td>
                                <td class="p-4">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full <?php echo $statusColor; ?>">
                                        <?php echo htmlspecialchars($contact['status']); ?>
                                    </span>
                                </td>
                                <td class="p-4 text-gray-600"><?php echo date('M d, Y', strtotime($contact['created_at'])); ?></td>
                                <td class="p-4 text-right space-x-2">
                                    <a href="?page=admin/contacts&view=<?php echo $contact['id']; ?>" class="text-blue-500 hover:text-blue-700 transition-colors">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <button onclick="confirmDelete(<?php echo $contact['id']; ?>)" class="text-red-500 hover:text-red-700 transition-colors">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-gray-100 flex justify-between items-center">
                <p class="text-sm text-gray-500">Showing <?php echo count($contacts); ?> message(s)</p>
            </div>
        </div>
    <?php endif; ?>

    <script>
        function updateStatus(contactId, status) {
            if (confirm('Are you sure you want to update the status of this message?')) {
                window.location.href = '?page=admin/contacts&action=update_status&id=' + contactId + '&status=' + status;
            } else {
                location.reload();
            }
        }

        function confirmDelete(id) {
            if (confirm('Are you sure you want to delete this contact message? This action cannot be undone.')) {
                window.location.href = '?page=admin/contacts&action=delete&id=' + id;
            }
        }

        // Auto-hide success message after 5 seconds
        <?php if (isset($_GET['msg'])): ?>
        setTimeout(function() {
            const message = document.getElementById('successMessage');
            if (message) {
                message.style.opacity = '0';
                setTimeout(function() {
                    message.remove();
                }, 500);
            }
        }, 5000);
        <?php endif; ?>
    </script>

<?php include "include/footer.php"; ?>
