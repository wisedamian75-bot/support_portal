<?php
require_once "../includes/auth.php";
require_role("admin");
require_once "../config/db.php";

$tickets = $conn->query(
    "SELECT tk.id,tk.ticket_number,tk.subject,tk.priority,tk.status,tk.created_at,
            c.name category,u.name requester,tu.name technician
     FROM tickets tk
     JOIN users u ON u.id=tk.user_id
     LEFT JOIN categories c ON c.id=tk.category_id
     LEFT JOIN technicians t ON t.id=tk.technician_id
     LEFT JOIN users tu ON tu.id=t.user_id
     ORDER BY tk.created_at DESC"
);
?>
<!DOCTYPE html>
<html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Manage Tickets</title><link rel="stylesheet" href="../css/style.css"></head>
<body><?php include "../includes/nav.php"; ?>
<main class="container">
<div class="dashboard-header"><div><h1>Manage Tickets</h1><p>Assign technicians and update ticket progress.</p></div></div>
<section class="panel"><div class="table-wrap"><table>
<thead><tr><th>Ticket</th><th>Subject</th><th>Requester</th><th>Category</th><th>Priority</th><th>Status</th><th>Technician</th><th></th></tr></thead>
<tbody>
<?php if($tickets->num_rows): while($t=$tickets->fetch_assoc()): ?>
<tr>
<td><?= e($t["ticket_number"] ?: "#".$t["id"]) ?></td><td><?= e($t["subject"]) ?></td><td><?= e($t["requester"]) ?></td>
<td><?= e($t["category"] ?? "Uncategorised") ?></td>
<td><span class="<?= badge_class($t["priority"]) ?>"><?= e($t["priority"]) ?></span></td>
<td><span class="<?= badge_class($t["status"]) ?>"><?= e($t["status"]) ?></span></td>
<td><?= e($t["technician"] ?? "Unassigned") ?></td>
<td><a class="btn btn-primary" href="ticket_view.php?id=<?= (int)$t["id"] ?>">Manage</a></td>
</tr>
<?php endwhile; else: ?><tr><td colspan="8">No tickets found.</td></tr><?php endif; ?>
</tbody></table></div></section>
</main></body></html>
