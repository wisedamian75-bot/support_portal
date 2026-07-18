<?php
require_once "../includes/auth.php";
require_role("admin");
require_once "../config/db.php";

$message="";
if($_SERVER["REQUEST_METHOD"]==="POST"){
    $name=trim($_POST["name"]??"");
    $description=trim($_POST["description"]??"");
    if($name!==""){
        try{
            $stmt=$conn->prepare("INSERT INTO categories(name,description) VALUES(?,?)");
            $stmt->bind_param("ss",$name,$description);
            $stmt->execute();
            $message="Category added.";
        }catch(Throwable $e){$message="Could not add category: ".$e->getMessage();}
    }
}
$rows=$conn->query("SELECT * FROM categories ORDER BY name");
?>
<!DOCTYPE html>
<html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Categories</title><link rel="stylesheet" href="../css/style.css"></head>
<body><?php include "../includes/nav.php"; ?><main class="container">
<div class="dashboard-header"><div><h1>Categories</h1><p>Manage ticket categories.</p></div></div>
<?php if($message): ?><div class="alert alert-success"><?= e($message) ?></div><?php endif; ?>
<div class="grid-3">
<section class="panel"><h2>Add Category</h2><form method="post"><div class="form-group"><label>Name</label><input name="name" required></div><div class="form-group"><label>Description</label><textarea name="description"></textarea></div><button class="btn btn-primary">Add Category</button></form></section>
<section class="panel" style="grid-column:span 2"><div class="table-wrap"><table><thead><tr><th>Name</th><th>Description</th><th>Active</th></tr></thead><tbody><?php while($r=$rows->fetch_assoc()): ?><tr><td><?= e($r["name"]) ?></td><td><?= e($r["description"] ?? "-") ?></td><td><?= $r["is_active"] ? "Yes" : "No" ?></td></tr><?php endwhile; ?></tbody></table></div></section>
</div></main></body></html>
