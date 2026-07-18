<?php
require_once "../includes/auth.php";
require_role("user");
require_once "../config/db.php";

$id = (int)($_GET["id"] ?? 0);
$userId = (int)$_SESSION["user_id"];

$stmt = $conn->prepare(
    "SELECT tk.*,c.name category,tu.name technician_name
     FROM tickets tk
     LEFT JOIN categories c ON c.id=tk.category_id
     LEFT JOIN technicians t ON t.id=tk.technician_id
     LEFT JOIN users tu ON tu.id=t.user_id
     WHERE tk.id=? AND tk.user_id=?"
);
$stmt->bind_param("ii", $id, $userId);
$stmt->execute();
$ticket = $stmt->get_result()->fetch_assoc();

if (!$ticket) {
    die("Ticket not found.");
}

$h = $conn->prepare(
    "SELECT th.*,u.name changed_by_name
     FROM ticket_history th
     LEFT JOIN users u ON u.id=th.changed_by
     WHERE th.ticket_id=?
     ORDER BY th.created_at DESC"
);
$h->bind_param("i", $id);
$h->execute();
$history = $h->get_result();
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>View Ticket</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include "../includes/nav.php"; ?>
<main class="container">
<?php if (isset($_GET["created"])): ?><div class="alert alert-success">Ticket created successfully.</div><?php endif; ?>
<div class="dashboard-header"><div><h1><?= e($ticket["ticket_number"]) ?></h1><p><?= e($ticket["subject"]) ?></p></div><a class="btn btn-outline" href="my_tickets.php">Back</a></div>

<section class="panel">
<div class="kv">
<strong>Category</strong><span><?= e($ticket["category"] ?? "Uncategorised") ?></span>
<strong>Priority</strong><span><?= e($ticket["priority"]) ?></span>
<strong>Status</strong><span><?= e($ticket["status"]) ?></span>
<strong>Technician</strong><span><?= e($ticket["technician_name"] ?? "Unassigned") ?></span>
<strong>Location</strong><span><?= e($ticket["location"] ?? "-") ?></span>
<strong>Created</strong><span><?= e($ticket["created_at"]) ?></span>
</div>
<hr style="margin:20px 0;border:0;border-top:1px solid #eee">
<p><?= nl2br(e($ticket["description"])) ?></p>
</section>

<section class="panel">
<h2>Ticket History</h2>
<?php if ($history->num_rows): while ($x=$history->fetch_assoc()): ?>
<div style="padding:13px 0;border-bottom:1px solid #eee">
<strong><?= e($x["new_status"]) ?></strong> — <?= e($x["comment"] ?? "") ?><br>
<small><?= e($x["changed_by_name"] ?? "System") ?> · <?= e($x["created_at"]) ?></small>
</div>
<?php endwhile; else: ?><p>No history yet.</p><?php endif; ?>
</section>
</main>
</body>
</html>
