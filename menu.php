<?php
$pageTitle = "Menu";
require "header.php";

$result = $conn->query("SELECT item_id, item_name, category, price, available_qty
                        FROM Food_Items
                        ORDER BY category, item_name");

// Group the rows by category
$menu = [];
while ($row = $result->fetch_assoc()) {
    $menu[$row['category']][] = $row;
}
?>
<h1>Menu</h1>

<?php foreach ($menu as $category => $items): ?>
    <h2><?= htmlspecialchars($category) ?></h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Item</th>
            <th>Price (Rs.)</th>
            <th>Stock</th>
            <th>Status</th>
        </tr>
        <?php foreach ($items as $it): ?>
            <tr>
                <td><?= $it['item_id'] ?></td>
                <td><?= htmlspecialchars($it['item_name']) ?></td>
                <td><?= number_format($it['price'], 2) ?></td>
                <td><?= $it['available_qty'] ?></td>
                <td>
                    <?php if ($it['available_qty'] == 0): ?>
                        <span class="low">Sold out</span>
                    <?php elseif ($it['available_qty'] < 10): ?>
                        <span class="low">Low</span>
                    <?php else: ?>
                        Available
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
    <br>
<?php endforeach; ?>

<a class="btn" href="order.php">Place an Order</a>

<?php require "footer.php"; ?>