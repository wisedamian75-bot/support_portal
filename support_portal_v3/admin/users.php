<?php
require_once "../includes/auth.php";
require_role("admin");
require_once "../config/db.php";

$users = $conn->query(
    "SELECT u.id,u.name,u.email,u.role,u.account_status,d.name department,u.created_at
     FROM users u
     LEFT JOIN departments d ON d.id=u.department_id
     ORDER BY u.created_at DESC"
);
?>
<!DOCTYPE html>
<html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Users</title><link rel="stylesheet" href="../css/style.css"></head>
<body><?php include "../includes/nav.php"; ?>
<main class="container"><div class="dashboard-header"><div><h1>Users</h1><p>Registered portal accounts.</p></div></div>
<section class="panel"><div class="table-wrap"><table>
<thead><tr><th>Name</th><th>Email</th><th>Department</th><th>Role</th><th>Status</th><th>Created</th></tr></thead>
<tbody><?php while($u=$users->fetch_assoc()): ?>
<tr><td><?= e($u["name"]) ?></td><td><?= e($u["email"]) ?></td><td><?= e($u["department"] ?? "-") ?></td><td><?= e($u["role"]) ?></td><td><?= e($u["account_status"]) ?></td><td><?= e($u["created_at"]) ?></td></tr>
<?php endwhile; ?></tbody></table></div></section>
</main></body></html>
