<?php
$pageTitle = "Home";
require "header.php";

$items  = $conn->query("SELECT COUNT(*) AS c FROM Food_Items")->fetch_assoc()['c'];
$today  = $conn->query("SELECT COUNT(*) AS c, COALESCE(SUM(total_amount),0) AS r
                        FROM Orders
                        WHERE order_date = CURDATE() AND status <> 'Cancelled'")->fetch_assoc();
?>
<h1>Welcome to the College Canteen</h1>

<div class="cards">
    <div class="card"><div class="num"><?= $items ?></div><div class="label">Items on the menu</div></div>
    <div class="card"><div class="num"><?= $today['c'] ?></div><div class="label">Orders today</div></div>
    <div class="card"><div class="num">Rs. <?= number_format($today['r'], 2) ?></div><div class="label">Revenue today</div></div>
</div>

<a class="btn" href="order.php">Place an Order</a>
<a class="btn green" href="menu.php">View Menu</a>
<a class="btn grey" href="orders.php">Today's Orders</a>

<?php require "footer.php"; ?>