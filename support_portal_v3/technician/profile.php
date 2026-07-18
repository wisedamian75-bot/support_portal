<?php
require_once "../includes/auth.php";
require_role("technician");
require_once "../config/db.php";

$userId=(int)$_SESSION["user_id"];
$stmt=$conn->prepare(
    "SELECT u.name,u.email,t.technician_type,t.specialization,t.availability,t.created_at
     FROM technicians t
     JOIN users u ON u.id=t.user_id
     WHERE t.user_id=?"
);
$stmt->bind_param("i",$userId);
$stmt->execute();
$profile=$stmt->get_result()->fetch_assoc();
if(!$profile) die("Technician profile not found.");
?>
<!DOCTYPE html>
<html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Technician Profile</title><link rel="stylesheet" href="../css/style.css"></head>
<body><?php include "../includes/nav.php"; ?><main class="container">
<div class="dashboard-header"><div><h1>Technician Profile</h1><p>Your support profile details.</p></div></div>
<section class="panel"><div class="kv">
<strong>Name</strong><span><?= e($profile["name"]) ?></span>
<strong>Email</strong><span><?= e($profile["email"]) ?></span>
<strong>Type</strong><span><?= e($profile["technician_type"]) ?></span>
<strong>Specialisation</strong><span><?= e($profile["specialization"] ?? "-") ?></span>
<strong>Availability</strong><span><?= e($profile["availability"]) ?></span>
<strong>Created</strong><span><?= e($profile["created_at"]) ?></span>
</div></section></main></body></html>
