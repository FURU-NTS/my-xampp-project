<?php
include_once 'db_connection.php';
$page_title = "営業ポイント削除";
include_once 'header.php';

try {
    $conn = getDBConnection();
    $order_id = $_GET['order_id'] ?? '';

    if (empty($order_id)) throw new Exception('受注IDが指定されていません');

    $stmt = $conn->prepare("SELECT o.customer_name, o.order_date 
                            FROM orders o 
                            WHERE o.id = ? AND o.construction_status IN ('残あり', '完了', '回収待ち', '回収完了')");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();
    if (!$order) throw new Exception('指定された受注が見つかりません、または対象外です');

    $points_stmt = $conn->prepare("SELECT sp.*, e.full_name AS employee_name 
                                   FROM sales_points sp 
                                   JOIN employees e ON sp.employee_id = e.employee_id 
                                   WHERE sp.order_id = ?");
    $points_stmt->execute([$order_id]);
    $points = $points_stmt->fetchAll();
?>
<style>
    .delete-container {
        max-width: 1000px;
        margin: 20px auto;
    }
    .delete-item {
        margin: 10px 0;
    }
    .delete-item label {
        font-weight: bold;
    }
    .points-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }
    .points-table th, .points-table td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
    }
    .points-table th {
        background-color: #f2f2f2;
    }
    .button-group {
        text-align: center;
        margin-top: 20px;
    }
    .button-group input, .button-group a {
        margin: 0 10px;
    }
</style>
<div class="delete-container">
    <p>以下の営業ポイントを削除してもよろしいですか？</p>
    <div class="delete-item">
        <label>顧客名:</label> <?php echo htmlspecialchars($order['customer_name'] ?? '未設定'); ?>
    </div>
    <div class="delete-item">
        <label>受注日:</label> <?php echo htmlspecialchars($order['order_date'] ?? '未設定'); ?>
    </div>
    <?php if ($points) { ?>
        <table class="points-table">
            <tr>
                <th>担当者</th>
                <th>ポイント</th>
                <th>紹介ポイント</th>
                <th>車輛ポイント</th>
                <th>書き換え日</th>
                <th>撤去ポイント</th> <!-- 追加 -->
                <th>ポイント修正</th> <!-- 追加 -->
                <th>報奨金修正</th> <!-- 追加 -->
                <th>ポイント付与月</th>
                <th>メモ</th>
            </tr>
            <?php foreach ($points as $point) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($point['employee_name'] ?? '未設定'); ?></td>
                    <td><?php echo htmlspecialchars($point['points'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($point['referral_points'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($point['vehicle_points'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($point['rewrite_date'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($point['removal_points'] ?? ''); ?></td> <!-- 追加 -->
                    <td><?php echo htmlspecialchars($point['points_revision'] ?? ''); ?></td> <!-- 追加 -->
                    <td><?php echo htmlspecialchars($point['bonus_revision'] ?? ''); ?></td> <!-- 追加 -->
                    <td><?php echo htmlspecialchars($point['points_granted_month'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($point['memo'] ?? ''); ?></td>
                </tr>
            <?php } ?>
        </table>
    <?php } else { ?>
        <p>ポイントデータがありません。</p>
    <?php } ?>
    <div class="button-group">
        <form method="POST" action="process_delete_sales_points.php">
            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
            <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order_id); ?>">
            <input type="submit" value="削除" onkeydown="preventEnterSubmit(event)">
            <a href="sales_points_list.php" onkeydown="preventEnterSubmit(event)">キャンセル</a>
        </form>
    </div>
</div>
<script>
function preventEnterSubmit(event) {
    if (event.key === 'Enter') {
        event.preventDefault();
    }
}
</script>
</body></html>
<?php
} catch (Exception $e) {
    echo '<p class="error">' . htmlspecialchars($e->getMessage()) . '</p>';
}
?>