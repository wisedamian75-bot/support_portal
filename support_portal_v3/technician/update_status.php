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

$technicianId=(int)$profile["id"];
$id=(int)($_GET["id"]??0);
$message="";
$error="";

if($_SERVER["REQUEST_METHOD"]==="POST"){
    $status=$_POST["status"]??"Assigned";
    $notes=trim($_POST["notes"]??"");
    $allowed=["Assigned","In Progress","Pending","Resolved","Closed"];

    if(!in_array($status,$allowed,true)){
        $error="Invalid status.";
    }else{
        try{
            $conn->begin_transaction();

            $oldStmt=$conn->prepare("SELECT status,user_id FROM tickets WHERE id=? AND technician_id=? FOR UPDATE");
            $oldStmt->bind_param("ii",$id,$technicianId);
            $oldStmt->execute();
            $old=$oldStmt->get_result()->fetch_assoc();
            if(!$old) throw new RuntimeException("Ticket not found or not assigned to you.");

            $u=$conn->prepare(
                "UPDATE tickets
                 SET status=?,
                     resolution_notes=CASE WHEN ?='Resolved' THEN ? ELSE resolution_notes END,
                     resolved_at=CASE WHEN ?='Resolved' THEN NOW() ELSE resolved_at END,
                     closed_at=CASE WHEN ?='Closed' THEN NOW() ELSE closed_at END
                 WHERE id=? AND technician_id=?"
            );
            $u->bind_param("ssssiii",$status,$status,$notes,$status,$status,$id,$technicianId);
            $u->execute();

            $h=$conn->prepare(
                "INSERT INTO ticket_history(ticket_id,old_status,new_status,comment,changed_by)
                 VALUES(?,?,?,?,?)"
            );
            $h->bind_param("isssi",$id,$old["status"],$status,$notes,$userId);
            $h->execute();

            $notificationMessage="Your ticket #{$id} status changed to {$status}.";
            $n=$conn->prepare("INSERT INTO notifications(user_id,ticket_id,message) VALUES(?,?,?)");
            $n->bind_param("iis",$old["user_id"],$id,$notificationMessage);
            $n->execute();

            $conn->commit();
            $message="Ticket updated successfully.";
        }catch(Throwable $e){
            $conn->rollback();
            $error=$e->getMessage();
        }
    }
}

$stmt=$conn->prepare(
    "SELECT tk.*,c.name category,u.name requester
     FROM tickets tk
     JOIN users u ON u.id=tk.user_id
     LEFT JOIN categories c ON c.id=tk.category_id
     WHERE tk.id=? AND tk.technician_id=?"
);
$stmt->bind_param("ii",$id,$technicianId);
$stmt->execute();
$ticket=$stmt->get_result()->fetch_assoc();
if(!$ticket) die("Ticket not found or not assigned to you.");
?>
<!DOCTYPE html>
<html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Update Ticket</title><link rel="stylesheet" href="../css/style.css"></head>
<body><?php include "../includes/nav.php"; ?><main class="container">
<div class="dashboard-header"><div><h1><?= e($ticket["ticket_number"]) ?></h1><p><?= e($ticket["subject"]) ?></p></div><a class="btn btn-outline" href="dashboard.php">Back</a></div>
<?php if($message): ?><div class="alert alert-success"><?= e($message) ?></div><?php endif; ?>
<?php if($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>

<div class="grid-3">
<section class="panel" style="grid-column:span 2">
<div class="kv">
<strong>Requester</strong><span><?= e($ticket["requester"]) ?></span>
<strong>Category</strong><span><?= e($ticket["category"] ?? "Uncategorised") ?></span>
<strong>Priority</strong><span><?= e($ticket["priority"]) ?></span>
<strong>Status</strong><span><?= e($ticket["status"]) ?></span>
<strong>Location</strong><span><?= e($ticket["location"] ?? "-") ?></span>
</div>
<hr style="margin:20px 0;border:0;border-top:1px solid #eee">
<p><?= nl2br(e($ticket["description"])) ?></p>
</section>

<section class="panel">
<h2>Update Progress</h2>
<form method="post">
<div class="form-group"><label>Status</label><select name="status">
<?php foreach(["Assigned","In Progress","Pending","Resolved","Closed"] as $s): ?>
<option <?= $ticket["status"]===$s ? "selected" : "" ?>><?= e($s) ?></option>
<?php endforeach; ?>
</select></div>
<div class="form-group"><label>Work notes</label><textarea name="notes"></textarea></div>
<button class="btn btn-primary" type="submit">Save Update</button>
</form>
</section>
</div>
</main></body></html>
