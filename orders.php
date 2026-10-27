<?php
$pageTitle = "Today's Orders";
require "header.php";
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$flow = ["Pending" => "Preparing", "Preparing" => "Ready", "Ready" => "Collected"];
$message = "";
$type = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $oid = (int)($_POST['order_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    try {
        $conn->begin_transaction();

        // Lock the row and read its current status
        $stmt = $conn->prepare("SELECT status FROM Orders
                                WHERE order_id = ? AND order_date = CURDATE() FOR UPDATE");
        $stmt->bind_param("i", $oid);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        if (!$row) {
            throw new Exception("Order not found for today.");
        }
        $status = $row['status'];

        if ($action === 'next' && isset($flow[$status])) {
            $new = $flow[$status];
            $up = $conn->prepare("UPDATE Orders SET status = ? WHERE order_id = ?");
            $up->bind_param("si", $new, $oid);
            $up->execute();
            $message = "Order moved to $new.";
            $type = "ok";
        } elseif ($action === 'cancel' && in_array($status, ['Pending', 'Preparing'])) {
            $up = $conn->prepare("UPDATE Orders SET status = 'Cancelled' WHERE order_id = ?");
            $up->bind_param("i", $oid);
            $up->execute();
            // Return the stock of every item in this order
            $rs = $conn->prepare("UPDATE Food_Items f
                                  JOIN Order_Items oi ON f.item_id = oi.item_id
                                  SET f.available_qty = f.available_qty + oi.quantity
                                  WHERE oi.order_id = ?");
            $rs->bind_param("i", $oid);
            $rs->execute();
            $message = "Order cancelled and stock restored.";
            $type = "ok";
        } else {
            throw new Exception("This order is already $status and cannot be changed that way.");
        }

        $conn->commit();
    } catch (Exception $e) {
        $conn->rollback();
        $message = htmlspecialchars($e->getMessage());
        $type = "err";
    }
}

$orders = $conn->query("
    SELECT o.order_id, o.token_no, s.name, o.status, o.total_amount,
           GROUP_CONCAT(CONCAT(f.item_name, ' x', oi.quantity) SEPARATOR ', ') AS items
      FROM Orders o
      JOIN Students s     ON o.student_id = s.student_id
      JOIN Order_Items oi ON o.order_id   = oi.order_id
      JOIN Food_Items f   ON oi.item_id   = f.item_id
     WHERE o.order_date = CURDATE()
     GROUP BY o.order_id, o.token_no, s.name, o.status, o.total_amount
     ORDER BY o.token_no");
?>
<h1>Today's Orders</h1>

<?php if ($message): ?>
    <div class="msg <?= $type ?>"><?= $message ?></div>
<?php endif; ?>

<table>
    <tr>
        <th>Token</th>
        <th>Student</th>
        <th>Items</th>
        <th>Amount (Rs.)</th>
        <th>Status</th>
        <th>Action</th>
    </tr>
    <?php while ($o = $orders->fetch_assoc()): ?>
        <tr>
            <td><strong><?= $o['token_no'] ?></strong></td>
            <td><?= htmlspecialchars($o['name']) ?></td>
            <td><?= htmlspecialchars($o['items']) ?></td>
            <td><?= number_format($o['total_amount'], 2) ?></td>
            <td><span class="badge <?= $o['status'] ?>"><?= $o['status'] ?></span></td>
            <td>
                <?php if (isset($flow[$o['status']])): ?>
                    <form method="post" class="inline">
                        <input type="hidden" name="order_id" value="<?= $o['order_id'] ?>">
                        <input type="hidden" name="action" value="next">
                        <button class="btn green small" type="submit">
                            Mark <?= $flow[$o['status']] ?>
                        </button>
                    </form>
                <?php endif; ?>
                <?php if (in_array($o['status'], ['Pending', 'Preparing'])): ?>
                    <form method="post" class="inline"
                          onsubmit="return confirm('Cancel this order?');">
                        <input type="hidden" name="order_id" value="<?= $o['order_id'] ?>">
                        <input type="hidden" name="action" value="cancel">
                        <button class="btn small" type="submit">Cancel</button>
                    </form>
                <?php endif; ?>
            </td>
        </tr>
    <?php endwhile; ?>
</table>

<?php require "footer.php"; ?>