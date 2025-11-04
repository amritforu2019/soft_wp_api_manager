<?php
$page_title = "Add Customer";
require_once 'config/db_connect.php';
require_once 'includes/header.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $customer_name = sanitize_input($_POST['customer_name']);
    $email = sanitize_input($_POST['email']);
    $mobile = sanitize_input($_POST['mobile']);
    $api_key = generate_api_key();
    $api_type = sanitize_input($_POST['api_type']);
    $customer_api_key = sanitize_input($_POST['customer_api_key']);
    $use_own_api = isset($_POST['use_own_api']) ? 1 : 0;
    $status = sanitize_input($_POST['status']);
    $credit_limit = intval($_POST['credit_limit']);
    $priority_level = intval($_POST['priority_level']);
    
    $stmt = $conn->prepare("INSERT INTO customers (customer_name, email, mobile, api_key, api_type, customer_api_key, use_own_api, status, credit_limit, priority_level) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssisii", $customer_name, $email, $mobile, $api_key, $api_type, $customer_api_key, $use_own_api, $status, $credit_limit, $priority_level);
    
    if ($stmt->execute()) {
        $success = "Customer added successfully! API Key: " . $api_key;
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
                    <i class="bi bi-person-plus"></i> Add New Customer
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
                                <input type="text" class="form-control" id="customer_name" name="customer_name" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="mobile" class="form-label">Mobile</label>
                                <input type="text" class="form-control" id="mobile" name="mobile">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="api_type" class="form-label">API Type *</label>
                                <select class="form-select" id="api_type" name="api_type" required>
                                    <option value="type1">Type 1</option>
                                    <option value="type2">Type 2</option>
                                    <option value="type3">Type 3</option>
                                </select>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <h6 class="mb-3">
                            <i class="bi bi-key"></i> Customer's Own API Configuration (Optional)
                        </h6>
                        
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="use_own_api" name="use_own_api" onchange="toggleCustomerApi()">
                                <label class="form-check-label" for="use_own_api">
                                    <strong>Customer has their own API key</strong>
                                </label>
                            </div>
                            <small class="text-muted">Enable this if customer wants to use their own WhatsApp/SMS API account</small>
                        </div>
                        
                        <div id="customer_api_section" style="display: none;">
                            <div class="mb-3">
                                <label for="customer_api_key" class="form-label">Customer's API Key</label>
                                <input type="text" class="form-control" id="customer_api_key" name="customer_api_key" 
                                       placeholder="Enter customer's WhatsApp/SMS API key">
                                <small class="text-muted">This key will be used instead of system default API</small>
                            </div>
                            
                            <div class="alert alert-info">
                                <small>
                                    <i class="bi bi-info-circle"></i>
                                    When enabled, customer's own API key will be used for sending messages. 
                                    If disabled or empty, system default API will be used.
                                </small>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="status" class="form-label">Status *</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="active">Active</option>
                                    <option value="suspended">Suspended</option>
                                    <option value="expired">Expired</option>
                                </select>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="credit_limit" class="form-label">Credit Limit *</label>
                                <input type="number" class="form-control" id="credit_limit" name="credit_limit" value="1000" required>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="priority_level" class="form-label">Priority Level *</label>
                                <select class="form-select" id="priority_level" name="priority_level" required>
                                    <option value="1">Normal</option>
                                    <option value="2">High</option>
                                </select>
                            </div>
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
                                <i class="bi bi-save"></i> Save Customer
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
                    <i class="bi bi-info-circle"></i> Information
                </div>
                <div class="card-body">
                    <h6>API Types</h6>
                    <ul>
                        <li><strong>Type 1:</strong> Primary API provider</li>
                        <li><strong>Type 2:</strong> Secondary API provider</li>
                        <li><strong>Type 3:</strong> Backup API provider</li>
                    </ul>
                    
                    <h6 class="mt-3">Status</h6>
                    <ul>
                        <li><strong>Active:</strong> Can send messages</li>
                        <li><strong>Suspended:</strong> Temporarily blocked</li>
                        <li><strong>Expired:</strong> API key expired</li>
                    </ul>
                    
                    <div class="alert alert-info mt-3">
                        <small>API Key will be generated automatically</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
