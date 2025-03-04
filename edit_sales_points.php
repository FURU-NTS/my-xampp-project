<?php
include_once 'db_connection.php';
$page_title = "ポイント編集";
include_once 'header.php';

try {
    $conn = getDBConnection();
    $point_id = $_GET['point_id'] ?? '';
    if (empty($point_id)) throw new Exception('ポイントIDが指定されていません');

    $stmt = $conn->prepare("SELECT * FROM sales_points WHERE point_id = ?");
    $stmt->execute([$point_id]);
    $item = $stmt->fetch();
    if (!$item) throw new Exception('ポイントが見つかりません');

    $orders_stmt = $conn->query("SELECT id, customer_name FROM orders");
    $orders = $orders_stmt->fetchAll();
    $employees_stmt = $conn->query("SELECT employee_id, full_name FROM employees");
    $employees = $employees_stmt->fetchAll();
?>
<form method="POST" action="process_edit_sales_points.php">
    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
    <input type="hidden" name="point_id" value="<?php echo htmlspecialchars($item['point_id']); ?>">
    <div class="form-group">
        <label for="order_id" class="required">受注:</label>
        <select id="order_id" name="order_id" required>
            <option value="">選択してください</option>
            <?php foreach ($orders as $order) {
                $selected = $order['id'] == $item['order_id'] ? 'selected' : '';
                echo "<option value='" . htmlspecialchars($order['id']) . "' $selected>" . htmlspecialchars($order['customer_name']) . "</option>";
            } ?>
        </select>
    </div>
    <div class="form-group">
        <label for="sales_rep_id" class="required">担当者:</label>
        <select id="sales_rep_id" name="sales_rep_id" required>
            <option value="">選択してください</option>
            <?php foreach ($employees as $employee) {
                $selected = $employee['employee_id'] == $item['sales_rep_id'] ? 'selected' : '';
                echo "<option value='" . htmlspecialchars($employee['employee_id']) . "' $selected>" . htmlspecialchars($employee['full_name']) . "</option>";
            } ?>
        </select>
    </div>
    <div class="form-group">
        <label for="points" class="required">ポイント:</label>
        <input type="number" id="points" name="points" min="0" value="<?php echo htmlspecialchars($item['points']); ?>" required>
    </div>
    <input type="submit" value="更新">
</form>
</body></html>
<?php
} catch (Exception $e) {
    echo '<p class="error">' . htmlspecialchars($e->getMessage()) . '</p>';
}
?>