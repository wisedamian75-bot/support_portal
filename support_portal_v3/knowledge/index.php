<?php
session_start();
require_once "../includes/auth.php";
?>
<!DOCTYPE html>
<html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Knowledge Base</title><link rel="stylesheet" href="../css/style.css"></head>
<body><?php include "../includes/nav.php"; ?><main class="container">
<div class="dashboard-header"><div><h1>Knowledge Base</h1><p>Quick solutions for common ICT issues.</p></div></div>
<div class="grid-3">
<div class="card"><h3>Internet not working</h3><p>Check cables, restart the network adapter, reconnect to Wi-Fi, then restart the router if permitted.</p></div>
<div class="card"><h3>Printer offline</h3><p>Confirm the printer has power and network access, clear the print queue, then restart it.</p></div>
<div class="card"><h3>Password issues</h3><p>Check Caps Lock and the correct email address. Contact ICT for a secure password reset.</p></div>
</div>
</main></body></html>
