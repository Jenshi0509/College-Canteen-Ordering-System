<?php
require_once "db.php";
$current = basename($_SERVER['PHP_SELF']);
function navLink($file, $label, $current) {
    $cls = ($file === $current) ? "active" : "";
    echo "<a class='$cls' href='$file'>$label</a>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($pageTitle ?? "Home") ?> | College Canteen</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="topbar">
    <div class="brand">College Canteen</div>
    <nav>
        <?php
        navLink("index.php", "Home", $current);
        navLink("menu.php", "Menu", $current);
        navLink("order.php", "Place Order", $current);
        navLink("orders.php", "Today's Orders", $current);
        navLink("report.php", "Sales Report", $current);
        ?>
    </nav>
</header>
<main class="container">