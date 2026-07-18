<?php
$role = $_SESSION["role"] ?? null;
?>
<header class="topbar">
  <a class="brand" href="/support_portal_v3/index.php">
    <span class="brand-mark">NW</span>
    <span>Nairobi Water <small>ICT Support Portal</small></span>
  </a>

  <nav class="main-nav">
    <a href="/support_portal_v3/index.php">Home</a>

    <?php if ($role === "user"): ?>
      <a href="/support_portal_v3/user/dashboard.php">Dashboard</a>
      <a href="/support_portal_v3/user/create_ticket.php">Create Ticket</a>
      <a href="/support_portal_v3/user/my_tickets.php">My Tickets</a>
      <a href="/support_portal_v3/user/notifications.php">Notifications</a>
    <?php elseif ($role === "technician"): ?>
      <a href="/support_portal_v3/technician/dashboard.php">Dashboard</a>
      <a href="/support_portal_v3/technician/assigned_tickets.php">Assigned Tickets</a>
      <a href="/support_portal_v3/technician/profile.php">Profile</a>
    <?php elseif ($role === "admin"): ?>
      <a href="/support_portal_v3/admin/dashboard.php">Dashboard</a>
      <a href="/support_portal_v3/admin/tickets.php">Tickets</a>
      <a href="/support_portal_v3/admin/users.php">Users</a>
      <a href="/support_portal_v3/admin/technicians.php">Technicians</a>
      <a href="/support_portal_v3/admin/categories.php">Categories</a>
      <a href="/support_portal_v3/admin/reports.php">Reports</a>
    <?php endif; ?>

    <a href="/support_portal_v3/knowledge/index.php">Knowledge Base</a>
  </nav>

  <div class="auth-actions">
    <?php if ($role): ?>
      <span class="user-chip"><?= e($_SESSION["name"] ?? "") ?></span>
      <a class="btn btn-primary" href="/support_portal_v3/logout.php">Logout</a>
    <?php else: ?>
      <a class="btn btn-outline" href="/support_portal_v3/login.php">Login</a>
      <a class="btn btn-primary" href="/support_portal_v3/register.php">Register</a>
    <?php endif; ?>
  </div>
</header>
