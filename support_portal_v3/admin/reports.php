<?php
require_once "../includes/auth.php";
require_role("admin");
require_once "../config/db.php";

$statusRows=$conn->query("SELECT status,COUNT(*) total FROM tickets GROUP BY status ORDER BY total DESC");
$priorityRows=$conn->query("SELECT priority,COUNT(*) total FROM tickets GROUP BY priority ORDER BY FIELD(priority,'Critical','High','Medium','Low')");
$categoryRows=$conn->query("SELECT COALESCE(c.name,'Uncategorised') category,COUNT(*) total FROM tickets tk LEFT JOIN categories c ON c.id=tk.category_id GROUP BY c.id,c.name ORDER BY total DESC");
?>
<!DOCTYPE html>
<html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Reports</title><link rel="stylesheet" href="../css/style.css"></head>
<body><?php include "../includes/nav.php"; ?><main class="container">
<div class="dashboard-header"><div><h1>Reports</h1><p>Ticket distribution by status, priority, and category.</p></div></div>
<div class="grid-3">
<section class="panel"><h2>By Status</h2><table><thead><tr><th>Status</th><th>Total</th></tr></thead><tbody><?php while($r=$statusRows->fetch_assoc()): ?><tr><td><?= e($r["status"]) ?></td><td><?= (int)$r["total"] ?></td></tr><?php endwhile; ?></tbody></table></section>
<section class="panel"><h2>By Priority</h2><table><thead><tr><th>Priority</th><th>Total</th></tr></thead><tbody><?php while($r=$priorityRows->fetch_assoc()): ?><tr><td><?= e($r["priority"]) ?></td><td><?= (int)$r["total"] ?></td></tr><?php endwhile; ?></tbody></table></section>
<section class="panel"><h2>By Category</h2><table><thead><tr><th>Category</th><th>Total</th></tr></thead><tbody><?php while($r=$categoryRows->fetch_assoc()): ?><tr><td><?= e($r["category"]) ?></td><td><?= (int)$r["total"] ?></td></tr><?php endwhile; ?></tbody></table></section>
</div></main></body></html>
