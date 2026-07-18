<?php
session_start();
require_once "config/db.php";
require_once "includes/auth.php";

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $departmentId = (int)($_POST["department_id"] ?? 0);
    $password = $_POST["password"] ?? "";

    if ($name === "" || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 6 || $departmentId <= 0) {
        $error = "Enter a valid name, department, email, and password of at least 6 characters.";
    } else {
        try {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare(
                "INSERT INTO users (name,email,department_id,password,role)
                 VALUES (?,?,?,?, 'user')"
            );
            $stmt->bind_param("ssis", $name, $email, $departmentId, $hash);
            $stmt->execute();
            $success = "Registration successful. You can now log in.";
        } catch (mysqli_sql_exception $e) {
            $error = "That email is already registered.";
        }
    }
}

$departments = $conn->query("SELECT id,name FROM departments ORDER BY name");
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Register</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include "includes/nav.php"; ?>
<div class="form-card">
  <h1>Create an account</h1>
  <p>Register as a Nairobi Water support portal user.</p>
  <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
  <?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>

  <form method="post">
    <div class="form-group"><label>Full name</label><input name="name" required></div>
    <div class="form-group"><label>Email</label><input type="email" name="email" required></div>
    <div class="form-group">
      <label>Department</label>
      <select name="department_id" required>
        <option value="">Select department</option>
        <?php while ($d = $departments->fetch_assoc()): ?>
          <option value="<?= (int)$d["id"] ?>"><?= e($d["name"]) ?></option>
        <?php endwhile; ?>
      </select>
    </div>
    <div class="form-group"><label>Password</label><input type="password" name="password" minlength="6" required></div>
    <button class="btn btn-primary" type="submit">Register</button>
  </form>
</div>
</body>
</html>
