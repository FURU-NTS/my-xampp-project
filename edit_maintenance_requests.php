<?php
include_once 'db_connection.php';
$page_title = "保守受付編集";
include_once 'header.php';

try {
    $conn = getDBConnection();
    $request_id = $_GET['request_id'] ?? '';
    if (empty($request_id)) throw new Exception('受付IDが指定されていません');

    $stmt = $conn->prepare("SELECT * FROM maintenance_requests WHERE request_id = ?");
    $stmt->execute([$request_id]);
    $item = $stmt->fetch();
    if (!$item) throw new Exception('保守受付が見つかりません');

    $stmt = $conn->query("SELECT id, customer_name FROM orders");
    $orders = $stmt->fetchAll();
?>
<form method="POST" action="process_edit_maintenance_requests.php">
    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
    <input type="hidden" name="request_id" value="<?php echo htmlspecialchars($item['request_id']); ?>">
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
        <label for="status" class="required">ステータス:</label>
        <select id="status" name="status" required>
            <option value="new" <?php echo $item['status'] === 'new' ? 'selected' : ''; ?>>new</option>
            <option value="in_progress" <?php echo $item['status'] === 'in_progress' ? 'selected' : ''; ?>>in_progress</option>
            <option value="completed" <?php echo $item['status'] === 'completed' ? 'selected' : ''; ?>>completed</option>
        </select>
    </div>
    <div class="form-group">
        <label for="request_date" class="required">受付日:</label>
        <input type="date" id="request_date" name="request_date" value="<?php echo htmlspecialchars($item['request_date']); ?>" required>
    </div>
    <input type="submit" value="更新">
</form>
</body></html>
<?php
} catch (Exception $e) {
    echo '<p class="error">' . htmlspecialchars($e->getMessage()) . '</p>';
}
?>