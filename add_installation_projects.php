<?php
include_once 'db_connection.php';
$page_title = "新規工事プロジェクト追加";
include_once 'header.php';

try {
    $conn = getDBConnection();

    // 条件付きで orders から顧客名を取得
    $stmt = $conn->query("SELECT o.id, o.customer_name, o.order_date 
                          FROM orders o 
                          WHERE o.negotiation_status IN ('進行中', '与信怪しい', '書換完了') 
                          ORDER BY o.customer_name");
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<style>
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; font-weight: bold; margin-bottom: 5px; }
    .form-group input, .form-group select { width: 100%; max-width: 300px; padding: 5px; box-sizing: border-box; }
    .required::after { content: " *"; color: red; }
    .error { color: red; }
    .success { color: green; }
</style>
<form method="POST" action="process_add_installation_projects.php">
    <div class="form-group">
        <label for="order_id" class="required">顧客名（受注）:</label>
        <select id="order_id" name="order_id" required onchange="updateOrderDate(this)">
            <option value="">選択してください</option>
            <?php foreach ($orders as $order) {
                $display_text = htmlspecialchars($order['customer_name']) . " (" . htmlspecialchars($order['order_date']) . ")";
                echo "<option value='" . htmlspecialchars($order['id']) . "' data-order-date='" . htmlspecialchars($order['order_date']) . "'>" . $display_text . "</option>";
            } ?>
        </select>
    </div>
    <div class="form-group">
        <label for="order_date" class="required">受注日:</label>
        <input type="date" id="order_date" name="order_date" required readonly style="background-color: #f0f0f0;">
    </div>
    <div class="form-group">
        <label for="new_schedule_date" class="required">新規予定日:</label>
        <input type="date" id="new_schedule_date" name="new_schedule_date" required>
    </div>
    <div class="form-group">
        <label for="status" class="required">ステータス:</label>
        <select id="status" name="status" required>
            <option value="planning">段取り中</option>
            <option value="in_progress">進行中</option>
        </select>
    </div>
    <div>
        <input type="submit" value="追加">
        <input type="button" value="キャンセル" onclick="window.location.href='installation_projects_list.php';">
    </div>
</form>

<script>
function updateOrderDate(select) {
    var orderDate = select.options[select.selectedIndex].getAttribute('data-order-date');
    document.getElementById('order_date').value = orderDate;
}
</script>
</body>
</html>
<?php
} catch (Exception $e) {
    error_log("Error in add_installation_projects.php: " . $e->getMessage());
    echo '<p class="error">' . htmlspecialchars($e->getMessage()) . '</p>';
}
?>