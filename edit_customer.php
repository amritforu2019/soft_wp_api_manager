<?php
$page_title = "Edit Customer";
require_once 'config/db_connect.php';
require_once 'includes/header.php';

$success = '';
$error = '';
$customer_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($customer_id == 0) {
    header('Location: customers.php');
    exit();
}

// Fetch customer data
$stmt = $conn->prepare("SELECT * FROM customers WHERE id = ?");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();
$customer = $result->fetch_assoc();
$stmt->close();

if (!$customer) {
    header('Location: customers.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $customer_name = sanitize_input($_POST['customer_name']);
    $email = sanitize_input($_POST['email']);
    $mobile = sanitize_input($_POST['mobile']);
    $api_type = sanitize_input($_POST['api_type']);
    $customer_api_key = sanitize_input($_POST['customer_api_key']);
    $use_own_api = isset($_POST['use_own_api']) ? 1 : 0;
    $status = sanitize_input($_POST['status']);
    $credit_limit = intval($_POST['credit_limit']);
    $priority_level = intval($_POST['priority_level']);
    
    $stmt = $conn->prepare("UPDATE customers SET customer_name=?, email=?, mobile=?, api_type=?, customer_api_key=?, use_own_api=?, status=?, credit_limit=?, priority_level=? WHERE id=?");
    $stmt->bind_param("sssssisiii", $customer_name, $email, $mobile, $api_type, $customer_api_key, $use_own_api, $status, $credit_limit, $priority_level, $customer_id);
    
    if ($stmt->execute()) {
        $success = "Customer updated successfully!";
        // Refresh customer data
        $stmt2 = $conn->prepare("SELECT * FROM customers WHERE id = ?");
        $stmt2->bind_param("i", $customer_id);
        $stmt2->execute();
        $result2 = $stmt2->get_result();
        $customer = $result2->fetch_assoc();
        $stmt2->close();
    } else {
        $error = "Error: " . $stmt->error;
    }
    $stmt->close();
}
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-pencil"></i> Edit Customer
                </div>
                <div class="card-body">
                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle"></i> <?php echo $success; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle"></i> <?php echo $error; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="customer_name" class="form-label">Customer Name *</label>
                                <input type="text" class="form-control" id="customer_name" name="customer_name" 
                                       value="<?php echo htmlspecialchars($customer['customer_name']); ?>" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($customer['email']); ?>">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="mobile" class="form-label">Mobile</label>
                                <input type="text" class="form-control" id="mobile" name="mobile" 
                                       value="<?php echo htmlspecialchars($customer['mobile']); ?>">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="api_type" class="form-label">API Type *</label>
                                <select class="form-select" id="api_type" name="api_type" required>
                                    <option value="type1" <?php echo $customer['api_type'] == 'type1' ? 'selected' : ''; ?>>Type 1</option>
                                    <option value="type2" <?php echo $customer['api_type'] == 'type2' ? 'selected' : ''; ?>>Type 2</option>
                                    <option value="type3" <?php echo $customer['api_type'] == 'type3' ? 'selected' : ''; ?>>Type 3</option>
                                </select>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <h6 class="mb-3">
                            <i class="bi bi-key"></i> Customer's Own API Configuration (Optional)
                        </h6>
                        
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="use_own_api" name="use_own_api" 
                                       <?php echo isset($customer['use_own_api']) && $customer['use_own_api'] == 1 ? 'checked' : ''; ?> 
                                       onchange="toggleCustomerApi()">
                                <label class="form-check-label" for="use_own_api">
                                    <strong>Customer has their own API key</strong>
                                </label>
                            </div>
                            <small class="text-muted">Enable this if customer wants to use their own WhatsApp/SMS API account</small>
                        </div>
                        
                        <div id="customer_api_section" style="display: <?php echo (isset($customer['use_own_api']) && $customer['use_own_api'] == 1) ? 'block' : 'none'; ?>;">
                            <div class="mb-3">
                                <label for="customer_api_key" class="form-label">Customer's API Key</label>
                                <input type="text" class="form-control" id="customer_api_key" name="customer_api_key" 
                                       value="<?php echo htmlspecialchars($customer['customer_api_key'] ?? ''); ?>"
                                       placeholder="Enter customer's WhatsApp/SMS API key">
                                <small class="text-muted">This key will be used instead of system default API</small>
                            </div>
                            
                            <div class="alert alert-info">
                                <small>
                                    <i class="bi bi-info-circle"></i>
                                    When enabled, customer's own API key will be used for sending messages. 
                                    If disabled or empty, system default API (<?php echo strtoupper($customer['api_type']); ?>) will be used.
                                </small>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="status" class="form-label">Status *</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="active" <?php echo $customer['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="suspended" <?php echo $customer['status'] == 'suspended' ? 'selected' : ''; ?>>Suspended</option>
                                    <option value="expired" <?php echo $customer['status'] == 'expired' ? 'selected' : ''; ?>>Expired</option>
                                </select>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="credit_limit" class="form-label">Credit Limit *</label>
                                <input type="number" class="form-control" id="credit_limit" name="credit_limit" 
                                       value="<?php echo $customer['credit_limit']; ?>" required>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="priority_level" class="form-label">Priority Level *</label>
                                <select class="form-select" id="priority_level" name="priority_level" required>
                                    <option value="1" <?php echo $customer['priority_level'] == 1 ? 'selected' : ''; ?>>Normal</option>
                                    <option value="2" <?php echo $customer['priority_level'] == 2 ? 'selected' : ''; ?>>High</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="api_key" class="form-label">API Key (Read-only)</label>
                            <input type="text" class="form-control" id="api_key" 
                                   value="<?php echo $customer['api_key']; ?>" readonly>
                        </div>
                        
                        <script>
                        function toggleCustomerApi() {
                            var checkbox = document.getElementById('use_own_api');
                            var section = document.getElementById('customer_api_section');
                            section.style.display = checkbox.checked ? 'block' : 'none';
                        }
                        </script>
                        
                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Update Customer
                            </button>
                            <a href="customers.php" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-bar-chart"></i> Usage Statistics
                </div>
                <div class="card-body">
                    <h6>Credit Usage</h6>
                    <div class="progress mb-2" style="height: 20px;">
                        <?php 
                        $percentage = ($customer['credit_limit'] > 0) ? ($customer['used_credits'] / $customer['credit_limit'] * 100) : 0;
                        ?>
                        <div class="progress-bar bg-info" role="progressbar" style="width: <?php echo $percentage; ?>%">
                            <?php echo round($percentage, 1); ?>%
                        </div>
                    </div>
                    <p><strong>Used:</strong> <?php echo $customer['used_credits']; ?> / <?php echo $customer['credit_limit']; ?></p>
                    
                    <hr>
                    
                    <h6>Account Info</h6>
                    <p><strong>Created:</strong> <?php echo date('Y-m-d H:i', strtotime($customer['created_at'])); ?></p>
                    <p><strong>Updated:</strong> <?php echo date('Y-m-d H:i', strtotime($customer['updated_at'])); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
