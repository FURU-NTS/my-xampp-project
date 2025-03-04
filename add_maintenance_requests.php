<?php
$page_title = "保守受付追加";
include_once 'header.php';
include_once 'db_connection.php';

try {
    $conn = getDBConnection();
    $stmt = $conn->query("SELECT id, customer_name FROM orders");
    $orders = $stmt->fetchAll();
?>
<form method="POST" action="process_add_maintenance_requests.php">
    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
    <div class="form-group">
        <label for="order_id" class="required">受注:</label>
        <select id="order_id" name="order_id" required>
            <option value="">選択してください</option>
            <?php foreach ($orders as $order) {
                echo "<option value='" . htmlspecialchars($order['id']) . "'>" . htmlspecialchars($order['customer_name']) . "</option>";
            } ?>
        </select>
    </div>
    <div class="form-group">
        <label for="status" class="required">ステータス:</label>
        <select id="status" name="status" required>
            <option value="new">new</option>
            <option value="in_progress">in_progress</option>
            <option value="completed">completed</option>
        </select>
    </div>
    <div class="form-group">
        <label for="request_date" class="required">受付日:</label>
        <input type="date" id="request_date" name="request_date" required>
    </div>
    <input type="submit" value="追加">
</form>
</body></html>
<?php
} catch (Exception $e) {
    echo '<p class="error">' . htmlspecialchars($e->getMessage()) . '</p>';
}
?>