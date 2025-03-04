<?php
include_once 'db_connection.php';
$page_title = "ポイント追加";
include_once 'header.php';

try {
    $conn = getDBConnection();
    $orders_stmt = $conn->query("SELECT id, customer_name FROM orders");
    $orders = $orders_stmt->fetchAll();
    $employees_stmt = $conn->query("SELECT employee_id, full_name FROM employees");
    $employees = $employees_stmt->fetchAll();
?>
<form method="POST" action="process_add_sales_points.php">
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
        <label for="sales_rep_id" class="required">担当者:</label>
        <select id="sales_rep_id" name="sales_rep_id" required>
            <option value="">選択してください</option>
            <?php foreach ($employees as $employee) {
                echo "<option value='" . htmlspecialchars($employee['employee_id']) . "'>" . htmlspecialchars($employee['full_name']) . "</option>";
            } ?>
        </select>
    </div>
    <div class="form-group">
        <label for="points" class="required">ポイント:</label>
        <input type="number" id="points" name="points" min="0" required>
    </div>
    <input type="submit" value="追加">
</form>
</body></html>
<?php
} catch (Exception $e) {
    echo '<p class="error">' . htmlspecialchars($e->getMessage()) . '</p>';
}
?>