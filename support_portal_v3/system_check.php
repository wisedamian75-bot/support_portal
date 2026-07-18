<?php
require_once "config/db.php";

$requiredTables=["departments","users","admins","technicians","categories","tickets","ticket_history","notifications"];
$results=[];
foreach($requiredTables as $table){
    $safe=$conn->real_escape_string($table);
    $q=$conn->query("SHOW TABLES LIKE '{$safe}'");
    $results[$table]=$q->num_rows===1;
}

$requiredFiles=[
"admin/dashboard.php","technician/dashboard.php","user/create_ticket.php",
"admin/tickets.php","technician/update_status.php","sql/support_portal_v3.sql"
];
?>
<!DOCTYPE html>
<html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>System Check</title><link rel="stylesheet" href="css/style.css"></head>
<body><main class="container">
<div class="dashboard-header"><div><h1>System Check</h1><p>Checks that the new project files and database tables exist.</p></div><a class="btn btn-outline" href="index.php">Home</a></div>

<section class="panel"><h2>Database Tables</h2><table><thead><tr><th>Table</th><th>Status</th></tr></thead><tbody>
<?php foreach($results as $table=>$ok): ?><tr><td><?= e($table) ?></td><td><?= $ok ? "OK" : "MISSING" ?></td></tr><?php endforeach; ?>
</tbody></table></section>

<section class="panel"><h2>Required Files</h2><table><thead><tr><th>File</th><th>Status</th></tr></thead><tbody>
<?php foreach($requiredFiles as $file): ?><tr><td><?= e($file) ?></td><td><?= file_exists(__DIR__."/".$file) ? "OK" : "MISSING" ?></td></tr><?php endforeach; ?>
</tbody></table></section>
</main></body></html>
