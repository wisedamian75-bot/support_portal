<?php
require_once "../includes/auth.php";
require_role("admin");
require_once "../config/db.php";

$rows = $conn->query(
    "SELECT t.id,u.name,u.email,t.technician_type,t.specialization,t.availability,
            COUNT(tk.id) workload
     FROM technicians t
     JOIN users u ON u.id=t.user_id
     LEFT JOIN tickets tk ON tk.technician_id=t.id
       AND tk.status NOT IN ('Resolved','Closed','Cancelled')
     GROUP BY t.id,u.name,u.email,t.technician_type,t.specialization,t.availability
     ORDER BY u.name"
);
?>
<!DOCTYPE html>
<html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Technicians</title><link rel="stylesheet" href="../css/style.css"></head>
<body><?php include "../includes/nav.php"; ?>
<main class="container"><div class="dashboard-header"><div><h1>Technicians</h1><p>Technician profiles and current workload.</p></div></div>
<section class="panel"><div class="table-wrap"><table>
<thead><tr><th>Name</th><th>Email</th><th>Type</th><th>Specialisation</th><th>Availability</th><th>Active Tickets</th></tr></thead>
<tbody><?php while($t=$rows->fetch_assoc()): ?>
<tr><td><?= e($t["name"]) ?></td><td><?= e($t["email"]) ?></td><td><?= e($t["technician_type"]) ?></td><td><?= e($t["specialization"] ?? "-") ?></td><td><?= e($t["availability"]) ?></td><td><?= (int)$t["workload"] ?></td></tr>
<?php endwhile; ?></tbody></table></div></section>
</main></body></html>
