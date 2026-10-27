<?php
echo "Running from: " . __FILE__ . "<br>";
require "db.php";
$result = $conn->query("SELECT item_name, price, available_qty FROM Food_Items");
echo "<h2>Connection OK</h2><ul>";
while ($row = $result->fetch_assoc()) {
    echo "<li>" . htmlspecialchars($row['item_name']) . " - Rs. " . $row['price']
       . " (stock: " . $row['available_qty'] . ")</li>";
}
echo "</ul>";
