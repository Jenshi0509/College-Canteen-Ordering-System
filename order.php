<?php
$pageTitle = "Place Order";
require "header.php";
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$message = "";
$type = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = (int)($_POST['student_id'] ?? 0);

    // Collect only the items with quantity > 0
    $cart = [];
    foreach (($_POST['qty'] ?? []) as $item_id => $q) {
        $q = (int)$q;
        if ($q > 0) {
            $cart[(int)$item_id] = $q;
        }
    }

    if ($student_id <= 0) {
        $message = "Please select a student.";
        $type = "err";
    } elseif (empty($cart)) {
        $message = "Please choose at least one item.";
        $type = "err";
    } else {
        try {
            $conn->begin_transaction();

            // 1. Next token for today (uses the stored function)
            $token = (int)$conn->query("SELECT get_next_token(CURDATE()) AS t")->fetch_assoc()['t'];

            // 2. Create the order
            $stmt = $conn->prepare("INSERT INTO Orders (student_id, order_date, token_no)
                                    VALUES (?, CURDATE(), ?)");
            $stmt->bind_param("ii", $student_id, $token);
            $stmt->execute();
            $order_id = $conn->insert_id;

            // 3. Add each item. The price is read from the database, never from the form.
            $priceStmt = $conn->prepare("SELECT price FROM Food_Items WHERE item_id = ?");
            $insStmt = $conn->prepare("INSERT INTO Order_Items (order_id, item_id, quantity, unit_price)
                                       VALUES (?, ?, ?, ?)");
            foreach ($cart as $item_id => $q) {
                $priceStmt->bind_param("i", $item_id);
                $priceStmt->execute();
                $row = $priceStmt->get_result()->fetch_assoc();
                if (!$row) {
                    throw new Exception("Invalid item selected.");
                }
                $price = $row['price'];
                // The trigger deducts stock and updates the order total
                $insStmt->bind_param("iiid", $order_id, $item_id, $q, $price);
                $insStmt->execute();
            }

            $conn->commit();

            $total = $conn->query("SELECT total_amount FROM Orders WHERE order_id = $order_id")
                          ->fetch_assoc()['total_amount'];
            $message = "Order placed successfully. YOUR TOKEN NUMBER: <strong>$token</strong>"
                     . " | Total: Rs. " . number_format($total, 2);
            $type = "ok";
        } catch (Exception $e) {
            $conn->rollback();
            $message = "Order failed, nothing was saved. Reason: " . htmlspecialchars($e->getMessage())
                     . " (usually this means there is not enough stock).";
            $type = "err";
        }
    }
}

$students = $conn->query("SELECT student_id, name, enrollment_no FROM Students ORDER BY name");
$items    = $conn->query("SELECT item_id, item_name, category, price, available_qty
                          FROM Food_Items ORDER BY category, item_name");
?>
<h1>Place an Order</h1>

<?php if ($message): ?>
    <div class="msg <?= $type ?>"><?= $message ?></div>
<?php endif; ?>

<form method="post" action="order.php">
    <p>
        <label><strong>Student:</strong></label>
        <select name="student_id" required>
            <option value="">-- select student --</option>
            <?php while ($s = $students->fetch_assoc()): ?>
                <option value="<?= $s['student_id'] ?>">
                    <?= htmlspecialchars($s['name']) ?> (<?= htmlspecialchars($s['enrollment_no']) ?>)
                </option>
            <?php endwhile; ?>
        </select>
    </p>

    <table>
        <tr>
            <th>Item</th>
            <th>Category</th>
            <th>Price (Rs.)</th>
            <th>In stock</th>
            <th>Quantity</th>
        </tr>
        <?php while ($it = $items->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($it['item_name']) ?></td>
                <td><?= htmlspecialchars($it['category']) ?></td>
                <td><?= number_format($it['price'], 2) ?></td>
                <td><?= $it['available_qty'] ?></td>
                <td>
                    <?php if ($it['available_qty'] > 0): ?>
                        <input class="qty" type="number" name="qty[<?= $it['item_id'] ?>]"
                               value="0" min="0" max="<?= $it['available_qty'] ?>"
                               data-price="<?= $it['price'] ?>">
                    <?php else: ?>
                        <span class="low">Sold out</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>

    <p><strong>Bill total: Rs. <span id="total">0.00</span></strong></p>
    <button class="btn green" type="submit">Place Order</button>
</form>

<script>
// Live bill total (display only; the real total is calculated by the database)
const boxes = document.querySelectorAll('.qty');
function updateTotal() {
    let t = 0;
    boxes.forEach(b => { t += (parseInt(b.value) || 0) * parseFloat(b.dataset.price); });
    document.getElementById('total').textContent = t.toFixed(2);
}
boxes.forEach(b => b.addEventListener('input', updateTotal));
</script>

<?php require "footer.php"; ?>