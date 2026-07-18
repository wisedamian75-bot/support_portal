<?php
session_start();
require_once "includes/auth.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Nairobi Water ICT Support Portal</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include "includes/nav.php"; ?>
<section class="hero">
  <div class="hero-inner">
    <h1>Nairobi Water ICT support that keeps every department moving.</h1>
    <p>Report technical issues, track progress, connect with the right technician, and keep every department moving through one secure support portal.</p>
    <div class="hero-actions">
      <a class="btn btn-primary" href="<?= isset($_SESSION["user_id"]) ? 'dashboard.php' : 'login.php' ?>">Open Support Portal</a>
      <a class="btn btn-outline" style="background:#fff" href="knowledge/index.php">Knowledge Base</a>
    </div>
  </div>
</section>
<section class="section">
  <div class="container">
    <h2 class="section-title">Everything in one system</h2>
    <p class="section-subtitle">Built for users, technicians, and ICT administrators.</p>
    <div class="grid-3">
      <div class="card"><h3>Ticket Management</h3><p>Create, assign, update, resolve, and close ICT support requests.</p></div>
      <div class="card"><h3>Role Dashboards</h3><p>Separate dashboards for users, technicians, and administrators.</p></div>
      <div class="card"><h3>Reports and History</h3><p>Monitor ticket status, technician workload, and changes over time.</p></div>
    </div>
  </div>
</section>
<footer><strong>Nairobi Water ICT Support Portal</strong><br><small>PHP, MySQL, XAMPP</small></footer>
</body>
</html>
