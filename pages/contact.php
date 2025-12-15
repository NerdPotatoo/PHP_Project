<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Controllers\ContactController;

$contactController = new ContactController();
$success = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $subscribe = isset($_POST['subscribe']) ? 1 : 0;

    // Validation
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = 'Please fill in all required fields';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } else {
        $data = [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'subject' => $subject,
            'message' => $message,
            'subscribe' => $subscribe
        ];

        $result = $contactController->createContact($data);
        if ($result['success']) {
            $success = $result['message'];
            // Clear form
            $_POST = [];
        } else {
            $error = $result['message'];
        }
    }
}

include "include/header.php"; 
?>

    <!-- Page Title -->
    <section class="bg-gray-100 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-4xl font-bold text-gray-900 mb-2">Contact Us</h1>
            <p class="text-gray-600">We'd love to hear from you. Send us a message!</p>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-16">
                <!-- Contact Information -->
                <div class="bg-white p-8 rounded-lg shadow-md text-center hover:shadow-lg transition duration-300">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-indigo-100 text-primary mb-6">
                        <i class="fas fa-map-marker-alt text-3xl"></i>
                    </div>
                    <h5 class="text-xl font-bold text-gray-900 mb-3">Address</h5>
                    <p class="text-gray-600">
                        123 Shopping Street<br>
                        Commerce City, CC 12345<br>
                        United States
                    </p>
                </div>

                <div class="bg-white p-8 rounded-lg shadow-md text-center hover:shadow-lg transition duration-300">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-indigo-100 text-primary mb-6">
                        <i class="fas fa-phone text-3xl"></i>
                    </div>
                    <h5 class="text-xl font-bold text-gray-900 mb-3">Phone</h5>
                    <p class="text-gray-600">
                        +1 (555) 123-4567<br>
                        Mon-Fri: 9AM - 6PM<br>
                        Sat-Sun: 10AM - 4PM
                    </p>
                </div>

                <div class="bg-white p-8 rounded-lg shadow-md text-center hover:shadow-lg transition duration-300">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-indigo-100 text-primary mb-6">
                        <i class="fas fa-envelope text-3xl"></i>
                    </div>
                    <h5 class="text-xl font-bold text-gray-900 mb-3">Email</h5>
                    <p class="text-gray-600">
                        support@eshop.com<br>
                        sales@eshop.com<br>
                        info@eshop.com
                    </p>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="max-w-3xl mx-auto">
                <h2 class="text-3xl font-bold text-center text-gray-900 mb-8">Send Us a Message</h2>
                
                <?php if ($success): ?>
                    <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg">
                        <i class="fas fa-check-circle mr-2"></i><?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg">
                        <i class="fas fa-exclamation-circle mr-2"></i><?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" class="bg-white p-8 rounded-lg shadow-lg">
                    <div class="mb-6">
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Full Name *</label>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" id="name" placeholder="Your Name" required>
                    </div>

                    <div class="mb-6">
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address *</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" id="email" placeholder="your@email.com" required>
                    </div>

                    <div class="mb-6">
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Phone Number (Optional)</label>
                        <input type="tel" name="phone" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" id="phone" placeholder="(555) 123-4567">
                    </div>

                    <div class="mb-6">
                        <label for="subject" class="block text-sm font-medium text-gray-700 mb-2">Subject *</label>
                        <select name="subject" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" id="subject" required>
                            <option value="">Select a subject</option>
                            <option value="Order Inquiry" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'Order Inquiry') ? 'selected' : ''; ?>>Order Inquiry</option>
                            <option value="Product Question" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'Product Question') ? 'selected' : ''; ?>>Product Question</option>
                            <option value="Return/Refund" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'Return/Refund') ? 'selected' : ''; ?>>Return/Refund</option>
                            <option value="Feedback" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'Feedback') ? 'selected' : ''; ?>>Feedback</option>
                            <option value="Complaint" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'Complaint') ? 'selected' : ''; ?>>Complaint</option>
                            <option value="Other" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>

                    <div class="mb-6">
                        <label for="message" class="block text-sm font-medium text-gray-700 mb-2">Message *</label>
                        <textarea name="message" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" id="message" rows="5" placeholder="Your message here..." required><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                    </div>

                    <div class="mb-6 flex items-center">
                        <input type="checkbox" name="subscribe" class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded" id="subscribe" <?php echo (isset($_POST['subscribe']) || !isset($_POST['name'])) ? 'checked' : ''; ?>>
                        <label class="ml-2 block text-sm text-gray-700" for="subscribe">
                            Subscribe to our newsletter for updates and offers
                        </label>
                    </div>

                    <button type="submit" class="w-full bg-primary hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-md transition duration-300 shadow-md">
                        <i class="fas fa-paper-plane mr-2"></i>Send Message
                    </button>
                </form>
            </div>
        </div>
    </section>

    <?php include "include/footer.php"; ?>
