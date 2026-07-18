<?php
require_once "../includes/auth.php";
require_role("user");
require_once "../config/db.php";

$error = "";
$userId = (int)$_SESSION["user_id"];
$categories = $conn->query("SELECT id,name FROM categories WHERE is_active=1 ORDER BY name");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $categoryId = (int)($_POST["category_id"] ?? 0);
    $subject = trim($_POST["subject"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $location = trim($_POST["location"] ?? "");
    $priority = $_POST["priority"] ?? "Medium";

    $allowedPriorities = ["Low","Medium","High","Critical"];

    if ($categoryId <= 0 || $subject === "" || $description === "" || !in_array($priority, $allowedPriorities, true)) {
        $error = "Please complete all required fields.";
    } else {
        try {
            $conn->begin_transaction();

            $stmt = $conn->prepare(
                "INSERT INTO tickets
                (user_id,category_id,subject,description,location,priority,status)
                VALUES (?,?,?,?,?,?,'Open')"
            );
            $stmt->bind_param("iissss", $userId, $categoryId, $subject, $description, $location, $priority);
            $stmt->execute();

            $ticketId = (int)$conn->insert_id;
            $ticketNumber = "NW-" . date("Y") . "-" . str_pad((string)$ticketId, 6, "0", STR_PAD_LEFT);

            $update = $conn->prepare("UPDATE tickets SET ticket_number=? WHERE id=?");
            $update->bind_param("si", $ticketNumber, $ticketId);
            $update->execute();

            $comment = "Ticket created";
            $history = $conn->prepare(
                "INSERT INTO ticket_history(ticket_id,old_status,new_status,comment,changed_by)
                 VALUES (?,NULL,'Open',?,?)"
            );
            $history->bind_param("isi", $ticketId, $comment, $userId);
            $history->execute();

            $conn->commit();
            header("Location: view_ticket.php?id={$ticketId}&created=1");
            exit;
        } catch (Throwable $e) {
            $conn->rollback();
            $error = "Unable to create ticket: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Create Ticket</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include "../includes/nav.php"; ?>
<div class="form-card">
  <h1>Create Support Ticket</h1>
  <p>Describe the issue clearly so the ICT team can assign it correctly.</p>
  <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>

  <form method="post">
    <div class="form-grid">
      <div class="form-group">
        <label>Category *</label>
        <select name="category_id" required>
          <option value="">Select category</option>
          <?php while ($c = $categories->fetch_assoc()): ?>
            <option value="<?= (int)$c["id"] ?>"><?= e($c["name"]) ?></option>
          <?php endwhile; ?>
        </select>
      </div>

      <div class="form-group">
        <label>Priority *</label>
        <select name="priority" required>
          <option>Low</option>
          <option selected>Medium</option>
          <option>High</option>
          <option>Critical</option>
        </select>
      </div>

      <div class="form-group full">
        <label>Subject *</label>
        <input name="subject" maxlength="200" required>
      </div>

      <div class="form-group full">
        <label>Description *</label>
        <textarea name="description" required></textarea>
      </div>

      <div class="form-group full">
        <label>Location</label>
        <input name="location" maxlength="150" placeholder="Building, floor, or office">
      </div>
    </div>

    <button class="btn btn-primary" type="submit">Create Ticket</button>
    <a class="btn btn-outline" href="dashboard.php">Cancel</a>
  </form>
</div>
</body>
</html>
