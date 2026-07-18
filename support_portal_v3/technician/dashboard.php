<?php
require_once "../includes/auth.php";
require_role("technician");
require_once "../config/db.php";

$userId = (int)$_SESSION["user_id"];

$p = $conn->prepare("SELECT id,technician_type,specialization,availability FROM technicians WHERE user_id=?");
$p->bind_param("i",$userId);
$p->execute();
$profile = $p->get_result()->fetch_assoc();

if(!$profile){
    die("Technician profile not found.");
}

$technicianId = (int)$profile["id"];

$s = $conn->prepare(
    "SELECT
      COUNT(*) total,
      SUM(status='Assigned') assigned_count,
      SUM(status='In Progress') progress_count,
      SUM(status='Pending') pending_count,
      SUM(status='Resolved') resolved_count
     FROM tickets
     WHERE technician_id=?"
);
$s->bind_param("i",$technicianId);
$s->execute();
$stats=$s->get_result()->fetch_assoc();

$q=$conn->prepare(
    "SELECT tk.id,tk.ticket_number,tk.subject,tk.priority,tk.status,tk.location,
            c.name category,u.name requester
     FROM tickets tk
     JOIN users u ON u.id=tk.user_id
     LEFT JOIN categories c ON c.id=tk.category_id
     WHERE tk.technician_id=?
     ORDER BY FIELD(tk.priority,'Critical','High','Medium','Low'),tk.created_at DESC
     LIMIT 12"
);
$q->bind_param("i",$technicianId);
$q->execute();
$tickets=$q->get_result();
?>
<!DOCTYPE html>
<html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Technician Dashboard</title><link rel="stylesheet" href="../css/style.css"></head>
<body><?php include "../includes/nav.php"; ?><main class="container">
<div class="dashboard-header"><div><h1>Technician Dashboard</h1><p><?= e($profile["technician_type"]) ?> · <?= e($profile["availability"]) ?></p></div><a class="btn btn-primary" href="assigned_tickets.php">Assigned Tickets</a></div>

<section class="stat-grid">
<div class="stat-card"><span>Total</span><strong><?= (int)($stats["total"] ?? 0) ?></strong></div>
<div class="stat-card"><span>Assigned</span><strong><?= (int)($stats["assigned_count"] ?? 0) ?></strong></div>
<div class="stat-card"><span>In Progress</span><strong><?= (int)($stats["progress_count"] ?? 0) ?></strong></div>
<div class="stat-card"><span>Pending</span><strong><?= (int)($stats["pending_count"] ?? 0) ?></strong></div>
<div class="stat-card"><span>Resolved</span><strong><?= (int)($stats["resolved_count"] ?? 0) ?></strong></div>
</section>

<section class="panel">
<div class="panel-header"><h2>My Tickets</h2><a class="btn btn-outline" href="assigned_tickets.php">View All</a></div>
<div class="table-wrap"><table>
<thead><tr><th>Ticket</th><th>Subject</th><th>Requester</th><th>Category</th><th>Priority</th><th>Status</th><th></th></tr></thead>
<tbody>
<?php if($tickets->num_rows): while($t=$tickets->fetch_assoc()): ?>
<tr>
<td><?= e($t["ticket_number"] ?: "#".$t["id"]) ?></td><td><?= e($t["subject"]) ?></td><td><?= e($t["requester"]) ?></td>
<td><?= e($t["category"] ?? "Uncategorised") ?></td>
<td><span class="<?= badge_class($t["priority"]) ?>"><?= e($t["priority"]) ?></span></td>
<td><span class="<?= badge_class($t["status"]) ?>"><?= e($t["status"]) ?></span></td>
<td><a class="btn btn-primary" href="update_status.php?id=<?= (int)$t["id"] ?>">Update</a></td>
</tr>
<?php endwhile; else: ?><tr><td colspan="7">No tickets assigned.</td></tr><?php endif; ?>
</tbody></table></div>
</section>
</main></body></html>
