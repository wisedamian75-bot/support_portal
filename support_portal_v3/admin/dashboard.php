<?php
require_once "../includes/auth.php";
require_role("admin");
require_once "../config/db.php";

$stats = $conn->query(
    "SELECT
      COUNT(*) total,
      SUM(status='Open') open_count,
      SUM(status IN ('Assigned','In Progress','Pending')) active_count,
      SUM(status='Resolved') resolved_count,
      SUM(status='Closed') closed_count
     FROM tickets"
)->fetch_assoc();

$tickets = $conn->query(
    "SELECT tk.id,tk.ticket_number,tk.subject,tk.priority,tk.status,tk.created_at,
            c.name category,u.name requester,tu.name technician
     FROM tickets tk
     JOIN users u ON u.id=tk.user_id
     LEFT JOIN categories c ON c.id=tk.category_id
     LEFT JOIN technicians t ON t.id=tk.technician_id
     LEFT JOIN users tu ON tu.id=t.user_id
     ORDER BY tk.created_at DESC
     LIMIT 12"
);

$workload = $conn->query(
    "SELECT u.name,t.technician_type,COUNT(tk.id) active_tickets
     FROM technicians t
     JOIN users u ON u.id=t.user_id
     LEFT JOIN tickets tk ON tk.technician_id=t.id
       AND tk.status NOT IN ('Resolved','Closed','Cancelled')
     GROUP BY t.id,u.name,t.technician_type
     ORDER BY active_tickets DESC,u.name"
);
?>
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Admin Dashboard</title><link rel="stylesheet" href="../css/style.css"></head>
<body>
<?php include "../includes/nav.php"; ?>
<main class="container">
<div class="dashboard-header"><div><h1>Admin Dashboard</h1><p>Monitor tickets, users, and technician workloads.</p></div><a class="btn btn-primary" href="tickets.php">Manage Tickets</a></div>

<section class="stat-grid">
<div class="stat-card"><span>Total</span><strong><?= (int)($stats["total"] ?? 0) ?></strong></div>
<div class="stat-card"><span>Open</span><strong><?= (int)($stats["open_count"] ?? 0) ?></strong></div>
<div class="stat-card"><span>Active</span><strong><?= (int)($stats["active_count"] ?? 0) ?></strong></div>
<div class="stat-card"><span>Resolved</span><strong><?= (int)($stats["resolved_count"] ?? 0) ?></strong></div>
<div class="stat-card"><span>Closed</span><strong><?= (int)($stats["closed_count"] ?? 0) ?></strong></div>
</section>

<section class="panel">
<div class="panel-header"><h2>Recent Tickets</h2><a class="btn btn-outline" href="tickets.php">View All</a></div>
<div class="table-wrap"><table>
<thead><tr><th>Ticket</th><th>Subject</th><th>Requester</th><th>Category</th><th>Priority</th><th>Status</th><th>Technician</th><th></th></tr></thead>
<tbody>
<?php if($tickets->num_rows): while($t=$tickets->fetch_assoc()): ?>
<tr>
<td><?= e($t["ticket_number"] ?: "#".$t["id"]) ?></td><td><?= e($t["subject"]) ?></td><td><?= e($t["requester"]) ?></td>
<td><?= e($t["category"] ?? "Uncategorised") ?></td>
<td><span class="<?= badge_class($t["priority"]) ?>"><?= e($t["priority"]) ?></span></td>
<td><span class="<?= badge_class($t["status"]) ?>"><?= e($t["status"]) ?></span></td>
<td><?= e($t["technician"] ?? "Unassigned") ?></td>
<td><a class="btn btn-outline" href="ticket_view.php?id=<?= (int)$t["id"] ?>">Manage</a></td>
</tr>
<?php endwhile; else: ?><tr><td colspan="8">No tickets found.</td></tr><?php endif; ?>
</tbody></table></div>
</section>

<section class="panel">
<div class="panel-header"><h2>Technician Workload</h2><a class="btn btn-outline" href="technicians.php">Technicians</a></div>
<div class="table-wrap"><table>
<thead><tr><th>Name</th><th>Type</th><th>Active Tickets</th></tr></thead>
<tbody><?php while($w=$workload->fetch_assoc()): ?><tr><td><?= e($w["name"]) ?></td><td><?= e($w["technician_type"]) ?></td><td><?= (int)$w["active_tickets"] ?></td></tr><?php endwhile; ?></tbody>
</table></div>
</section>
</main>
</body>
</html>
