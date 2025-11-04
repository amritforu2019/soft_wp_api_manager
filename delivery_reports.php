<?php
$page_title = "Delivery Reports";
require_once 'config/db_connect.php';
require_once 'includes/header.php';

// Get delivery reports
$reports = $conn->query("
    SELECT dr.*, m.mobile_number, m.message_text, m.customer_id, c.customer_name 
    FROM delivery_reports dr 
    JOIN messages m ON dr.message_id = m.id 
    JOIN customers c ON m.customer_id = c.id 
    ORDER BY dr.report_time DESC 
    LIMIT 100
");
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <i class="bi bi-check2-circle"></i> Delivery Reports
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover datatable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Message ID</th>
                            <th>Customer</th>
                            <th>Mobile</th>
                            <th>Message</th>
                            <th>Delivery Status</th>
                            <th>API Message ID</th>
                            <th>Report Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($report = $reports->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $report['id']; ?></td>
                            <td><?php echo $report['message_id']; ?></td>
                            <td><?php echo htmlspecialchars($report['customer_name']); ?></td>
                            <td><?php echo htmlspecialchars($report['mobile_number']); ?></td>
                            <td><?php echo htmlspecialchars(substr($report['message_text'], 0, 40)); ?>...</td>
                            <td>
                                <?php
                                $status = $report['delivery_status'];
                                $badge_class = 'bg-secondary';
                                if ($status == 'delivered') $badge_class = 'bg-success';
                                if ($status == 'failed') $badge_class = 'bg-danger';
                                if ($status == 'waiting') $badge_class = 'bg-warning';
                                echo '<span class="badge ' . $badge_class . '">' . ucfirst($status) . '</span>';
                                ?>
                            </td>
                            <td><?php echo htmlspecialchars($report['api_message_id'] ?? 'N/A'); ?></td>
                            <td><?php echo date('Y-m-d H:i:s', strtotime($report['report_time'])); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
