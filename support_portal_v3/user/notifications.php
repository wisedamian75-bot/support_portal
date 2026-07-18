<?php
require_once "../includes/auth.php";
require_role("user");
require_once "../config/db.php";
$userId=(int)$_SESSION["user_id"];
$stmt=$conn->prepare("SELECT id,message,is_read,created_at FROM notifications WHERE user_id=? ORDER BY created_at DESC");
$stmt->bind_param("i",$userId);
$stmt->execute();
$rows=$stmt->get_result();
?>
<!DOCTYPE html>
<html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Notifications</title><link rel="stylesheet" href="../css/style.css"></head>
<body><?php include "../includes/nav.php"; ?>
<main class="container"><div class="dashboard-header"><div><h1>Notifications</h1><p>Updates about your support requests.</p></div></div>
<section class="panel">
<?php if($rows->num_rows): while($n=$rows->fetch_assoc()): ?>
<div style="padding:14px 0;border-bottom:1px solid #eee"><strong><?= e($n["message"]) ?></strong><br><small><?= e($n["created_at"]) ?></small></div>
<?php endwhile; else: ?><p>No notifications available.</p><?php endif; ?>
</section></main></body></html>
