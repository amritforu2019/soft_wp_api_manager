<?php
$page_title = "API Logs";
require_once 'config/db_connect.php';
require_once 'includes/header.php';

// Pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$per_page = 50;
$offset = ($page - 1) * $per_page;

// Get total count
$total = $conn->query("SELECT COUNT(*) as count FROM api_logs")->fetch_assoc()['count'];
$total_pages = ceil($total / $per_page);

// Get logs
$logs = $conn->query("
    SELECT l.*, m.mobile_number, m.customer_id, c.customer_name 
    FROM api_logs l 
    LEFT JOIN messages m ON l.message_id = m.id 
    LEFT JOIN customers c ON m.customer_id = c.id 
    ORDER BY l.created_at DESC 
    LIMIT $per_page OFFSET $offset
");
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <i class="bi bi-journal-text"></i> API Logs
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-sm">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Message ID</th>
                            <th>Customer</th>
                            <th>Mobile</th>
                            <th>API Type</th>
                            <th>Status Code</th>
                            <th>Request URL</th>
                            <th>Response</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($log = $logs->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $log['id']; ?></td>
                            <td><?php echo $log['message_id']; ?></td>
                            <td><?php echo htmlspecialchars($log['customer_name'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($log['mobile_number'] ?? 'N/A'); ?></td>
                            <td><span class="badge bg-info"><?php echo strtoupper($log['api_type']); ?></span></td>
                            <td>
                                <?php
                                $code = intval($log['status_code']);
                                $badge_class = ($code >= 200 && $code < 300) ? 'bg-success' : 'bg-danger';
                                echo '<span class="badge ' . $badge_class . '">' . $code . '</span>';
                                ?>
                            </td>
                            <td>
                                <small><?php echo htmlspecialchars(substr($log['request_url'], 0, 50)); ?>...</small>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" 
                                        data-bs-target="#responseModal<?php echo $log['id']; ?>">
                                    <i class="bi bi-eye"></i> View
                                </button>
                                
                                <!-- Modal for response -->
                                <div class="modal fade" id="responseModal<?php echo $log['id']; ?>" tabindex="-1">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">API Log #<?php echo $log['id']; ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <h6>Request Data:</h6>
                                                <pre class="bg-light p-2"><?php echo htmlspecialchars($log['request_data']); ?></pre>
                                                
                                                <h6 class="mt-3">Response Data:</h6>
                                                <pre class="bg-light p-2"><?php echo htmlspecialchars($log['response_data']); ?></pre>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td><?php echo date('Y-m-d H:i:s', strtotime($log['created_at'])); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <nav aria-label="Page navigation" class="mt-3">
                <ul class="pagination">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
