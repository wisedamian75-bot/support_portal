<?php
require_once "../includes/auth.php";
require_role("technician");
require_once "../config/db.php";

$userId=(int)$_SESSION["user_id"];
$p=$conn->prepare("SELECT id FROM technicians WHERE user_id=?");
$p->bind_param("i",$userId);
$p->execute();
$profile=$p->get_result()->fetch_assoc();
if(!$profile) die("Technician profile not found.");

$stmt=$conn->prepare(
    "SELECT tk.id,tk.ticket_number,tk.subject,tk.priority,tk.status,tk.location,
            c.name category,u.name requester,tk.created_at
     FROM tickets tk
     JOIN users u ON u.id=tk.user_id
     LEFT JOIN categories c ON c.id=tk.category_id
     WHERE tk.technician_id=?
     ORDER BY FIELD(tk.priority,'Critical','High','Medium','Low'),tk.created_at DESC"
);
$stmt->bind_param("i",$profile["id"]);
$stmt->execute();
$rows=$stmt->get_result();
?>
<!DOCTYPE html>
<html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Assigned Tickets</title><link rel="stylesheet" href="../css/style.css"></head>
<body><?php include "../includes/nav.php"; ?><main class="container">
<div class="dashboard-header"><div><h1>Assigned Tickets</h1><p>All tickets currently assigned to you.</p></div></div>
<section class="panel"><div class="table-wrap"><table>
<thead><tr><th>Ticket</th><th>Subject</th><th>Requester</th><th>Category</th><th>Priority</th><th>Status</th><th>Created</th><th></th></tr></thead>
<tbody>
<?php if($rows->num_rows): while($r=$rows->fetch_assoc()): ?>
<tr>
<td><?= e($r["ticket_number"] ?: "#".$r["id"]) ?></td><td><?= e($r["subject"]) ?></td><td><?= e($r["requester"]) ?></td>
<td><?= e($r["category"] ?? "Uncategorised") ?></td><td><span class="<?= badge_class($r["priority"]) ?>"><?= e($r["priority"]) ?></span></td>
<td><span class="<?= badge_class($r["status"]) ?>"><?= e($r["status"]) ?></span></td><td><?= e($r["created_at"]) ?></td>
<td><a class="btn btn-primary" href="update_status.php?id=<?= (int)$r["id"] ?>">Update</a></td>
</tr>
<?php endwhile; else: ?><tr><td colspan="8">No tickets assigned.</td></tr><?php endif; ?>
</tbody></table></div></section>
</main></body></html>
