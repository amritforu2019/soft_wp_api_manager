<?php
$page_title = "Dashboard";
require_once 'config/db_connect.php';
require_once 'includes/header.php';

// Get statistics
$total_customers = $conn->query("SELECT COUNT(*) as count FROM customers")->fetch_assoc()['count'];
$active_customers = $conn->query("SELECT COUNT(*) as count FROM customers WHERE status='active'")->fetch_assoc()['count'];
$total_messages = $conn->query("SELECT COUNT(*) as count FROM messages")->fetch_assoc()['count'];
$pending_messages = $conn->query("SELECT COUNT(*) as count FROM messages WHERE status='pending'")->fetch_assoc()['count'];
$sent_messages = $conn->query("SELECT COUNT(*) as count FROM messages WHERE status='sent'")->fetch_assoc()['count'];
$failed_messages = $conn->query("SELECT COUNT(*) as count FROM messages WHERE status='failed'")->fetch_assoc()['count'];

// Get recent messages
$recent_messages = $conn->query("
    SELECT m.*, c.customer_name 
    FROM messages m 
    JOIN customers c ON m.customer_id = c.id 
    ORDER BY m.created_at DESC 
    LIMIT 10
");
?>

<div class="container-fluid">
    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted">Total Customers</h6>
                        <h2 class="mb-0"><?php echo $total_customers; ?></h2>
                        <small class="text-success"><?php echo $active_customers; ?> Active</small>
                    </div>
                    <div class="icon text-primary">
                        <i class="bi bi-people"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted">Total Messages</h6>
                        <h2 class="mb-0"><?php echo $total_messages; ?></h2>
                        <small class="text-info"><?php echo $sent_messages; ?> Sent</small>
                    </div>
                    <div class="icon text-info">
                        <i class="bi bi-envelope"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted">Pending Messages</h6>
                        <h2 class="mb-0"><?php echo $pending_messages; ?></h2>
                        <small class="text-warning">In Queue</small>
                    </div>
                    <div class="icon text-warning">
                        <i class="bi bi-clock-history"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted">Failed Messages</h6>
                        <h2 class="mb-0"><?php echo $failed_messages; ?></h2>
                        <small class="text-danger">Needs Attention</small>
                    </div>
                    <div class="icon text-danger">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Messages -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-clock-history"></i> Recent Messages
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Customer</th>
                                    <th>Mobile</th>
                                    <th>Message</th>
                                    <th>Status</th>
                                    <th>Delivery</th>
                                    <th>Created At</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($msg = $recent_messages->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $msg['id']; ?></td>
                                    <td><?php echo htmlspecialchars($msg['customer_name']); ?></td>
                                    <td><?php echo htmlspecialchars($msg['mobile_number']); ?></td>
                                    <td><?php echo htmlspecialchars(substr($msg['message_text'], 0, 50)) . '...'; ?></td>
                                    <td>
                                        <?php
                                        $badge_class = 'badge-' . $msg['status'];
                                        echo '<span class="badge ' . $badge_class . '">' . ucfirst($msg['status']) . '</span>';
                                        ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary"><?php echo ucfirst($msg['delivery_status']); ?></span>
                                    </td>
                                    <td><?php echo date('Y-m-d H:i', strtotime($msg['created_at'])); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
