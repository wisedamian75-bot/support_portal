<?php
require_once "../includes/auth.php";
require_role("user");
require_once "../config/db.php";

$userId = (int)$_SESSION["user_id"];

$stmt = $conn->prepare(
    "SELECT
      COUNT(*) total,
      SUM(status='Open') open_count,
      SUM(status IN ('Assigned','In Progress','Pending')) active_count,
      SUM(status='Resolved') resolved_count,
      SUM(status='Closed') closed_count
     FROM tickets
     WHERE user_id=?"
);
$stmt->bind_param("i", $userId);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();

$stmt = $conn->prepare(
    "SELECT tk.id,tk.ticket_number,tk.subject,tk.priority,tk.status,tk.created_at,c.name category
     FROM tickets tk
     LEFT JOIN categories c ON c.id=tk.category_id
     WHERE tk.user_id=?
     ORDER BY tk.created_at DESC
     LIMIT 10"
);
$stmt->bind_param("i", $userId);
$stmt->execute();
$tickets = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>User Dashboard</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include "../includes/nav.php"; ?>
<main class="container">
  <div class="dashboard-header">
    <div><h1>User Dashboard</h1><p>Create and track your ICT support requests.</p></div>
    <a class="btn btn-primary" href="create_ticket.php">Create Ticket</a>
  </div>

  <section class="stat-grid">
    <div class="stat-card"><span>Total</span><strong><?= (int)($stats["total"] ?? 0) ?></strong></div>
    <div class="stat-card"><span>Open</span><strong><?= (int)($stats["open_count"] ?? 0) ?></strong></div>
    <div class="stat-card"><span>Active</span><strong><?= (int)($stats["active_count"] ?? 0) ?></strong></div>
    <div class="stat-card"><span>Resolved</span><strong><?= (int)($stats["resolved_count"] ?? 0) ?></strong></div>
    <div class="stat-card"><span>Closed</span><strong><?= (int)($stats["closed_count"] ?? 0) ?></strong></div>
  </section>

  <section class="panel">
    <div class="panel-header">
      <h2>Recent Tickets</h2>
      <a class="btn btn-outline" href="my_tickets.php">View All</a>
    </div>
    <div class="table-wrap">
      <table>
        <thead><tr><th>Ticket</th><th>Subject</th><th>Category</th><th>Priority</th><th>Status</th><th>Created</th><th></th></tr></thead>
        <tbody>
        <?php if ($tickets->num_rows): while ($t = $tickets->fetch_assoc()): ?>
          <tr>
            <td><?= e($t["ticket_number"] ?: "#".$t["id"]) ?></td>
            <td><?= e($t["subject"]) ?></td>
            <td><?= e($t["category"] ?? "Uncategorised") ?></td>
            <td><span class="<?= badge_class($t["priority"]) ?>"><?= e($t["priority"]) ?></span></td>
            <td><span class="<?= badge_class($t["status"]) ?>"><?= e($t["status"]) ?></span></td>
            <td><?= e($t["created_at"]) ?></td>
            <td><a class="btn btn-outline" href="view_ticket.php?id=<?= (int)$t["id"] ?>">View</a></td>
          </tr>
        <?php endwhile; else: ?>
          <tr><td colspan="7">No tickets found.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>
</main>
</body>
</html>
