<?php
require_once "../includes/auth.php";
require_role("admin");
require_once "../config/db.php";

$id = (int)($_GET["id"] ?? 0);
$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $technicianId = (int)($_POST["technician_id"] ?? 0);
    $status = $_POST["status"] ?? "Open";
    $comment = trim($_POST["comment"] ?? "");
    $allowedStatuses = ["Open","Assigned","In Progress","Pending","Resolved","Closed","Cancelled"];

    if (!in_array($status, $allowedStatuses, true)) {
        $error = "Invalid status.";
    } else {
        try {
            $conn->begin_transaction();

            $oldStmt = $conn->prepare("SELECT status,user_id FROM tickets WHERE id=? FOR UPDATE");
            $oldStmt->bind_param("i",$id);
            $oldStmt->execute();
            $old = $oldStmt->get_result()->fetch_assoc();
            if (!$old) {
                throw new RuntimeException("Ticket not found.");
            }

            $stmt = $conn->prepare(
                "UPDATE tickets
                 SET technician_id=NULLIF(?,0),
                     status=?,
                     assigned_at=CASE WHEN ? > 0 AND assigned_at IS NULL THEN NOW() ELSE assigned_at END,
                     resolved_at=CASE WHEN ?='Resolved' THEN NOW() ELSE resolved_at END,
                     closed_at=CASE WHEN ?='Closed' THEN NOW() ELSE closed_at END
                 WHERE id=?"
            );
            $stmt->bind_param("isissi", $technicianId, $status, $technicianId, $status, $status, $id);
            $stmt->execute();

            $changedBy = (int)$_SESSION["user_id"];
            $history = $conn->prepare(
                "INSERT INTO ticket_history(ticket_id,old_status,new_status,comment,changed_by)
                 VALUES (?,?,?,?,?)"
            );
            $history->bind_param("isssi", $id, $old["status"], $status, $comment, $changedBy);
            $history->execute();

            $notificationMessage = "Ticket #{$id} status changed to {$status}.";
            $notification = $conn->prepare(
                "INSERT INTO notifications(user_id,ticket_id,message) VALUES (?,?,?)"
            );
            $notification->bind_param("iis", $old["user_id"], $id, $notificationMessage);
            $notification->execute();

            $conn->commit();
            $message = "Ticket updated successfully.";
        } catch (Throwable $e) {
            $conn->rollback();
            $error = $e->getMessage();
        }
    }
}

$stmt = $conn->prepare(
    "SELECT tk.*,c.name category,u.name requester,tu.name technician
     FROM tickets tk
     JOIN users u ON u.id=tk.user_id
     LEFT JOIN categories c ON c.id=tk.category_id
     LEFT JOIN technicians t ON t.id=tk.technician_id
     LEFT JOIN users tu ON tu.id=t.user_id
     WHERE tk.id=?"
);
$stmt->bind_param("i",$id);
$stmt->execute();
$ticket = $stmt->get_result()->fetch_assoc();

if (!$ticket) {
    die("Ticket not found.");
}

$technicians = $conn->query(
    "SELECT t.id,u.name,t.technician_type
     FROM technicians t
     JOIN users u ON u.id=t.user_id
     WHERE u.account_status='Active'
     ORDER BY u.name"
);
?>
<!DOCTYPE html>
<html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Manage Ticket</title><link rel="stylesheet" href="../css/style.css"></head>
<body><?php include "../includes/nav.php"; ?>
<main class="container">
<div class="dashboard-header"><div><h1><?= e($ticket["ticket_number"]) ?></h1><p><?= e($ticket["subject"]) ?></p></div><a class="btn btn-outline" href="tickets.php">Back</a></div>
<?php if($message): ?><div class="alert alert-success"><?= e($message) ?></div><?php endif; ?>
<?php if($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>

<div class="grid-3">
<section class="panel" style="grid-column:span 2">
<div class="kv">
<strong>Requester</strong><span><?= e($ticket["requester"]) ?></span>
<strong>Category</strong><span><?= e($ticket["category"] ?? "Uncategorised") ?></span>
<strong>Priority</strong><span><?= e($ticket["priority"]) ?></span>
<strong>Status</strong><span><?= e($ticket["status"]) ?></span>
<strong>Technician</strong><span><?= e($ticket["technician"] ?? "Unassigned") ?></span>
<strong>Location</strong><span><?= e($ticket["location"] ?? "-") ?></span>
</div>
<hr style="margin:20px 0;border:0;border-top:1px solid #eee">
<p><?= nl2br(e($ticket["description"])) ?></p>
</section>

<section class="panel">
<h2>Assign / Update</h2>
<form method="post">
<div class="form-group"><label>Technician</label>
<select name="technician_id"><option value="0">Unassigned</option>
<?php while($t=$technicians->fetch_assoc()): ?>
<option value="<?= (int)$t["id"] ?>" <?= ((int)$ticket["technician_id"] === (int)$t["id"]) ? "selected" : "" ?>>
<?= e($t["name"]." — ".$t["technician_type"]) ?>
</option>
<?php endwhile; ?>
</select></div>

<div class="form-group"><label>Status</label>
<select name="status">
<?php foreach(["Open","Assigned","In Progress","Pending","Resolved","Closed","Cancelled"] as $s): ?>
<option <?= $ticket["status"]===$s ? "selected" : "" ?>><?= e($s) ?></option>
<?php endforeach; ?>
</select></div>

<div class="form-group"><label>Comment</label><textarea name="comment"></textarea></div>
<button class="btn btn-primary" type="submit">Save Changes</button>
</form>
</section>
</div>
</main>
</body>
</html>
