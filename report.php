<?php
$pageTitle = "Sales Report";
require "header.php";
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Date from the form, default today. Validate the format before using it.
$date = $_GET['date'] ?? date('Y-m-d');
$d = DateTime::createFromFormat('Y-m-d', $date);
if (!$d || $d->format('Y-m-d') !== $date) {
    $date = date('Y-m-d');
}

// 1. Run the stored procedure to build / refresh the Daily_Sales row
$stmt = $conn->prepare("CALL generate_daily_sales(?)");
$stmt->bind_param("s", $date);
$stmt->execute();
$stmt->close();

// 2. Read the summary row
$stmt = $conn->prepare("SELECT total_orders, total_revenue FROM Daily_Sales WHERE sale_date = ?");
$stmt->bind_param("s", $date);
$stmt->execute();
$sum = $stmt->get_result()->fetch_assoc();
$stmt->close();

// 3. Best-selling items for that date
$stmt = $conn->prepare("SELECT f.item_name, SUM(oi.quantity) AS total_sold
                          FROM Order_Items oi
                          JOIN Food_Items f ON oi.item_id = f.item_id
                          JOIN Orders o     ON oi.order_id = o.order_id
                         WHERE o.order_date = ? AND o.status <> 'Cancelled'
                         GROUP BY f.item_name
                         ORDER BY total_sold DESC, f.item_name
                         LIMIT 5");
$stmt->bind_param("s", $date);
$stmt->execute();
$top = $stmt->get_result();
$stmt->close();

// 4. Revenue per category for that date
$stmt = $conn->prepare("SELECT f.category, SUM(oi.quantity) AS units_sold,
                               SUM(oi.quantity * oi.unit_price) AS revenue
                          FROM Order_Items oi
                          JOIN Food_Items f ON oi.item_id = f.item_id
                          JOIN Orders o     ON oi.order_id = o.order_id
                         WHERE o.order_date = ? AND o.status <> 'Cancelled'
                         GROUP BY f.category
                         ORDER BY revenue DESC");
$stmt->bind_param("s", $date);
$stmt->execute();
$cat = $stmt->get_result();
$stmt->close();

// 5. Low stock (uses the view) and recent history (uses Daily_Sales)
$low  = $conn->query("SELECT item_name, available_qty FROM v_low_stock_items ORDER BY available_qty");
$hist = $conn->query("SELECT sale_date, total_orders, total_revenue
                        FROM Daily_Sales ORDER BY sale_date DESC LIMIT 7");
?>
<h1>End-of-Day Sales Report</h1>

<form method="get" action="report.php" style="margin-bottom:20px;">
    <label><strong>Date:</strong></label>
    <input type="date" name="date" value="<?= htmlspecialchars($date) ?>">
    <button class="btn" type="submit">Show report</button>
</form>

<div class="cards">
    <div class="card">
        <div class="num"><?= (int)$sum['total_orders'] ?></div>
        <div class="label">Orders on <?= htmlspecialchars($date) ?></div>
    </div>
    <div class="card">
        <div class="num">Rs. <?= number_format($sum['total_revenue'], 2) ?></div>
        <div class="label">Revenue (cancelled orders excluded)</div>
    </div>
</div>

<h2>Best-selling items</h2>
<table>
    <tr><th>Item</th><th>Units sold</th></tr>
    <?php if ($top->num_rows === 0): ?>
        <tr><td colspan="2">No sales on this date.</td></tr>
    <?php endif; ?>
    <?php while ($r = $top->fetch_assoc()): ?>
        <tr><td><?= htmlspecialchars($r['item_name']) ?></td><td><?= $r['total_sold'] ?></td></tr>
    <?php endwhile; ?>
</table>

<h2>Revenue per category</h2>
<table>
    <tr><th>Category</th><th>Units sold</th><th>Revenue (Rs.)</th></tr>
    <?php if ($cat->num_rows === 0): ?>
        <tr><td colspan="3">No sales on this date.</td></tr>
    <?php endif; ?>
    <?php while ($r = $cat->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($r['category']) ?></td>
            <td><?= $r['units_sold'] ?></td>
            <td><?= number_format($r['revenue'], 2) ?></td>
        </tr>
    <?php endwhile; ?>
</table>

<h2>Low-stock alert</h2>
<table>
    <tr><th>Item</th><th>Left</th></tr>
    <?php if ($low->num_rows === 0): ?>
        <tr><td colspan="2">All items have enough stock.</td></tr>
    <?php endif; ?>
    <?php while ($r = $low->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($r['item_name']) ?></td>
            <td class="low"><?= $r['available_qty'] ?></td>
        </tr>
    <?php endwhile; ?>
</table>

<h2>Recent daily sales (from the Daily_Sales table)</h2>
<table>
    <tr><th>Date</th><th>Orders</th><th>Revenue (Rs.)</th></tr>
    <?php while ($r = $hist->fetch_assoc()): ?>
        <tr>
            <td><?= $r['sale_date'] ?></td>
            <td><?= $r['total_orders'] ?></td>
            <td><?= number_format($r['total_revenue'], 2) ?></td>
        </tr>
    <?php endwhile; ?>
</table>

<?php require "footer.php"; ?>