<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function e($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

function require_login(): void {
    if (!isset($_SESSION["user_id"])) {
        header("Location: /support_portal_v3/login.php");
        exit;
    }
}

function require_role(string $role): void {
    require_login();
    if (($_SESSION["role"] ?? "") !== $role) {
        header("Location: /support_portal_v3/dashboard.php");
        exit;
    }
}

function redirect_by_role(): void {
    $role = $_SESSION["role"] ?? "";
    $map = [
        "admin" => "/support_portal_v3/admin/dashboard.php",
        "technician" => "/support_portal_v3/technician/dashboard.php",
        "user" => "/support_portal_v3/user/dashboard.php",
    ];
    header("Location: " . ($map[$role] ?? "/support_portal_v3/login.php"));
    exit;
}

function badge_class(string $value): string {
    $slug = strtolower(str_replace(" ", "-", $value));
    return "badge badge-" . preg_replace("/[^a-z0-9\-]/", "", $slug);
}
?>