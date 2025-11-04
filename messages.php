<?php
$page_title = "Messages Queue";
require_once 'config/db_connect.php';
require_once 'includes/header.php';

// Filter handling
$status_filter = isset($_GET['status']) ? sanitize_input($_GET['status']) : '';
$customer_filter = isset($_GET['customer']) ? intval($_GET['customer']) : 0;

// Build query
$query = "SELECT m.*, c.customer_name FROM messages m JOIN customers c ON m.customer_id = c.id WHERE 1=1";
$params = [];
$types = '';

if ($status_filter) {
    $query .= " AND m.status = ?";
    $params[] = $status_filter;
    $types .= 's';
}

if ($customer_filter > 0) {
    $query .= " AND m.customer_id = ?";
    $params[] = $customer_filter;
    $types .= 'i';
}

$query .= " ORDER BY m.created_at DESC";

if ($types) {
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $messages = $stmt->get_result();
} else {
    $messages = $conn->query($query);
}

// Get customers for filter
$customers = $conn->query("SELECT id, customer_name FROM customers ORDER BY customer_name");
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <i class="bi bi-envelope"></i> Message Queue
        </div>
        <div class="card-body">
            <!-- Filters -->
            <form method="GET" action="" class="row g-3 mb-4">
                <div class="col-md-4">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">All Statuses</option>
                        <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="processing" <?php echo $status_filter == 'processing' ? 'selected' : ''; ?>>Processing</option>
                        <option value="sent" <?php echo $status_filter == 'sent' ? 'selected' : ''; ?>>Sent</option>
                        <option value="failed" <?php echo $status_filter == 'failed' ? 'selected' : ''; ?>>Failed</option>
                        <option value="hold" <?php echo $status_filter == 'hold' ? 'selected' : ''; ?>>Hold</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="customer" class="form-label">Customer</label>
                    <select class="form-select" id="customer" name="customer">
                        <option value="0">All Customers</option>
                        <?php while ($cust = $customers->fetch_assoc()): ?>
                            <option value="<?php echo $cust['id']; ?>" <?php echo $customer_filter == $cust['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cust['customer_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">&nbsp;</label>
                    <div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-filter"></i> Filter
                        </button>
                        <a href="messages.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-clockwise"></i> Reset
                        </a>
                    </div>
                </div>
            </form>

            <!-- Messages Table -->
            <div class="table-responsive">
                <table class="table table-hover datatable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Mobile</th>
                            <th>Message</th>
                            <th>API Type</th>
                            <th>Status</th>
                            <th>Delivery</th>
                            <th>Priority</th>
                            <th>Retry</th>
                            <th>Created</th>
                            <th>Sent</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($msg = $messages->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $msg['id']; ?></td>
                            <td><?php echo htmlspecialchars($msg['customer_name']); ?></td>
                            <td><?php echo htmlspecialchars($msg['mobile_number']); ?></td>
                            <td>
                                <span data-bs-toggle="tooltip" title="<?php echo htmlspecialchars($msg['message_text']); ?>">
                                    <?php echo htmlspecialchars(substr($msg['message_text'], 0, 40)); ?>...
                                </span>
                            </td>
                            <td><span class="badge bg-info"><?php echo strtoupper($msg['api_type']); ?></span></td>
                            <td>
                                <?php
                                $badge_class = 'badge-' . $msg['status'];
                                echo '<span class="badge ' . $badge_class . '">' . ucfirst($msg['status']) . '</span>';
                                ?>
                            </td>
                            <td>
                                <span class="badge bg-secondary"><?php echo ucfirst($msg['delivery_status']); ?></span>
                            </td>
                            <td>
                                <?php
                                $priority_class = $msg['priority'] == 'high' ? 'bg-warning' : 'bg-secondary';
                                echo '<span class="badge ' . $priority_class . '">' . ucfirst($msg['priority']) . '</span>';
                                ?>
                            </td>
                            <td><?php echo $msg['retry_count']; ?></td>
                            <td><?php echo date('Y-m-d H:i', strtotime($msg['created_at'])); ?></td>
                            <td><?php echo $msg['sent_at'] ? date('Y-m-d H:i', strtotime($msg['sent_at'])) : '-'; ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    // Enable Bootstrap tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    })
</script>

<?php require_once 'includes/footer.php'; ?>
