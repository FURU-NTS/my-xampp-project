<?php
include_once 'db_connection.php';
$page_title = "新規工事プロジェクト編集";
include_once 'header.php'; // ここでsession_start()が呼ばれてる

try {
    $conn = getDBConnection();

    if (!isset($_GET['project_id']) || empty($_GET['project_id'])) {
        echo "プロジェクトが指定されていません。";
        echo '<br><a href="installation_projects_list.php">一覧に戻る</a>';
        exit;
    }

    $id = $_GET['project_id'];
    $sql = "SELECT ip.project_id, c.company_name, o.order_date, 
                   ip.new_schedule_date, ip.memo, ip.status, o.negotiation_status, ip.order_id
            FROM installation_projects ip
            LEFT JOIN orders o ON ip.order_id = o.id
            LEFT JOIN companies c ON o.company_id = c.company_id
            WHERE ip.project_id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['id' => $id]);
    $project = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$project) {
        echo "プロジェクトが見つかりません（ID: " . htmlspecialchars($id) . "）。";
        echo '<br><a href="installation_projects_list.php">一覧に戻る</a>';
        exit;
    }

    // ordersから顧客名と受注日を取得
    $stmt_orders = $conn->query("SELECT id, customer_name, order_date 
                                 FROM orders 
                                 WHERE negotiation_status IN ('進行中', '与信怪しい', '書換完了') 
                                 ORDER BY customer_name");
    $orders = $stmt_orders->fetchAll(PDO::FETCH_ASSOC);

    // ステータス翻訳（completedを除外）
    $statusTranslations = [
        'planning' => '段取り中',
        'in_progress' => '進行中'
    ];

    // CSRFトークン生成
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    $csrf_token = $_SESSION['csrf_token'];
?>
<style>
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; font-weight: bold; margin-bottom: 5px; }
    .form-group input, .form-group select, .form-group textarea { 
        width: 100%; max-width: 300px; padding: 5px; box-sizing: border-box; 
    }
    .required::after { content: " *"; color: red; }
    .readonly { background-color: #f0f0f0; }
</style>
<form method="POST" action="process_edit_installation_projects.php">
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
    <div class="form-group">
        <label>ID:</label>
        <input type="text" value="<?php echo htmlspecialchars($project['project_id']); ?>" readonly class="readonly">
        <input type="hidden" name="project_id" value="<?php echo htmlspecialchars($project['project_id']); ?>">
    </div>
    <div class="form-group">
        <label for="order_id">顧客名（受注）:</label>
        <select id="order_id" name="order_id" readonly class="readonly" onchange="updateOrderDate(this)" disabled>
            <option value="">選択してください</option>
            <?php foreach ($orders as $order) {
                $selected = $project['order_id'] == $order['id'] ? 'selected' : '';
                echo "<option value='" . htmlspecialchars($order['id']) . "' data-order-date='" . htmlspecialchars($order['order_date']) . "' $selected>" . htmlspecialchars($order['customer_name']) . "</option>";
            } ?>
        </select>
    </div>
    <div class="form-group">
        <label for="order_date">受注日:</label>
        <input type="date" id="order_date" name="order_date" value="<?php echo htmlspecialchars($project['order_date']); ?>" readonly class="readonly">
    </div>
    <div class="form-group">
        <label for="new_schedule_date" class="required">新規予定日:</label>
        <input type="date" id="new_schedule_date" name="new_schedule_date" value="<?php echo htmlspecialchars($project['new_schedule_date']); ?>" required>
    </div>
    <div class="form-group">
        <label for="memo">メモ:</label>
        <textarea id="memo" name="memo"><?php echo htmlspecialchars($project['memo'] ?? ''); ?></textarea>
    </div>
    <div class="form-group">
        <label for="status" class="required">ステータス:</label>
        <select id="status" name="status" required>
            <?php foreach ($statusTranslations as $key => $value) {
                $selected = $project['status'] == $key ? 'selected' : '';
                echo "<option value='$key' $selected>$value</option>";
            } ?>
        </select>
    </div>
    <div class="form-group">
        <label for="negotiation_status">商談ステータス:</label>
        <input type="text" id="negotiation_status" value="<?php echo htmlspecialchars($project['negotiation_status'] ?? ''); ?>" readonly class="readonly">
    </div>
    <div>
        <input type="submit" value="更新">
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
    error_log("Error in edit_installation_projects.php: " . $e->getMessage());
    echo '<p class="error">' . htmlspecialchars($e->getMessage()) . '</p>';
}
?>