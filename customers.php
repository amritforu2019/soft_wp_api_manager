<?php
$page_title = "Customers Management";
require_once 'config/db_connect.php';
require_once 'includes/header.php';

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM customers WHERE id = $id");
    echo '<div class="alert alert-success">Customer deleted successfully!</div>';
}

// Get all customers
$customers = $conn->query("SELECT * FROM customers ORDER BY created_at DESC");
?>

<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <a href="add_customer.php" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Add New Customer
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <i class="bi bi-people"></i> All Customers
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover datatable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Mobile</th>
                            <th>API Key</th>
                            <th>API Type</th>
                            <th>Own API</th>
                            <th>Status</th>
                            <th>Credits</th>
                            <th>Priority</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($customer = $customers->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $customer['id']; ?></td>
                            <td><?php echo htmlspecialchars($customer['customer_name']); ?></td>
                            <td><?php echo htmlspecialchars($customer['email']); ?></td>
                            <td><?php echo htmlspecialchars($customer['mobile']); ?></td>
                            <td>
                                <code><?php echo substr($customer['api_key'], 0, 20) . '...'; ?></code>
                            </td>
                            <td><span class="badge bg-info"><?php echo strtoupper($customer['api_type']); ?></span></td>
                            <td>
                                <?php 
                                if (isset($customer['use_own_api']) && $customer['use_own_api'] == 1): 
                                ?>
                                    <span class="badge bg-success" title="Using customer's own API key">
                                        <i class="bi bi-check-circle"></i> Yes
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-secondary" title="Using system default API">
                                        <i class="bi bi-x-circle"></i> No
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $status_class = 'badge-status-' . $customer['status'];
                                echo '<span class="badge ' . $status_class . '">' . ucfirst($customer['status']) . '</span>';
                                ?>
                            </td>
                            <td>
                                <?php echo $customer['used_credits']; ?> / <?php echo $customer['credit_limit']; ?>
                                <div class="progress" style="height: 5px;">
                                    <?php 
                                    $percentage = ($customer['credit_limit'] > 0) ? ($customer['used_credits'] / $customer['credit_limit'] * 100) : 0;
                                    ?>
                                    <div class="progress-bar" role="progressbar" style="width: <?php echo $percentage; ?>%"></div>
                                </div>
                            </td>
                            <td>
                                <?php echo $customer['priority_level'] == 2 ? '<span class="badge bg-warning">High</span>' : '<span class="badge bg-secondary">Normal</span>'; ?>
                            </td>
                            <td>
                                <a href="edit_customer.php?id=<?php echo $customer['id']; ?>" class="btn btn-sm btn-warning">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="?delete=<?php echo $customer['id']; ?>" class="btn btn-sm btn-danger" 
                                   onclick="return confirmDelete('<?php echo htmlspecialchars($customer['customer_name']); ?>')">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
