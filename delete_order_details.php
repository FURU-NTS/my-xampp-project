<?php
include_once 'db_connection.php';
$page_title = "受注詳細削除";
include_once 'header.php';

try {
    $conn = getDBConnection();
    $id = $_GET['id'] ?? '';
    if (empty($id)) throw new Exception('受注詳細IDが指定されていません');

    $stmt = $conn->prepare("SELECT od.*, o.customer_name, o.order_date 
                            FROM order_details od 
                            LEFT JOIN orders o ON od.order_id = o.id 
                            WHERE od.id = ?");
    $stmt->execute([$id]);
    $item = $stmt->fetch();
    if (!$item) throw new Exception('受注詳細が見つかりません');
?>
<style>
    table { 
        width: 100%; 
        border-collapse: collapse; 
        table-layout: fixed; /* 列幅を固定 */
        margin-bottom: 20px;
    }
    th, td { 
        border: 1px solid #ddd; 
        padding: 8px; 
        text-align: left; 
        white-space: normal; /* 折り返しを許可 */
        overflow: hidden; 
        text-overflow: ellipsis; /* 長いテキストは省略 */
    }
    th { 
        background-color: #f2f2f2; 
        font-size: 0.9em; /* 見出しの文字サイズを少し小さく */
        width: 20%; /* 見出し幅を固定 */
    }
    td { 
        width: 80%; /* データ幅を固定 */
    }
</style>
<p>以下の受注詳細を削除してもよろしいですか？</p>
<table>
    <tr><th>受注日</th><td><?php echo htmlspecialchars($item['order_date'] ?? '未設定'); ?></td></tr>
    <tr><th>受注顧客名</th><td><?php echo htmlspecialchars($item['customer_name'] ?? '未指定'); ?></td></tr>
    <tr><th>担当者名</th><td><?php echo htmlspecialchars($item['sales_rep'] ?? '未設定'); ?></td></tr>
    <tr><th>携帯見直し (税込)</th><td><?php echo number_format($item['mobile_revision'] ?? 0, 0) . " 円"; ?></td></tr>
    <tr><th>携帯内容</th><td><?php echo htmlspecialchars($item['mobile_content'] ?? '未設定'); ?></td></tr>
    <tr><th>モニター費A (税込)</th><td><?php echo number_format($item['mobile_monitor_fee_a'] ?? 0, 0) . " 円"; ?></td></tr>
    <tr><th>A内容</th><td><?php echo htmlspecialchars($item['monitor_content_a'] ?? '未設定'); ?></td></tr>
    <tr><th>モニター費B (税込)</th><td><?php echo number_format($item['monitor_fee_b'] ?? 0, 0) . " 円"; ?></td></tr>
    <tr><th>B内容</th><td><?php echo htmlspecialchars($item['monitor_content_b'] ?? '未設定'); ?></td></tr>
    <tr><th>モニター費C (税込)</th><td><?php echo number_format($item['monitor_fee_c'] ?? 0, 0) . " 円"; ?></td></tr>
    <tr><th>C内容</th><td><?php echo htmlspecialchars($item['monitor_content_c'] ?? '未設定'); ?></td></tr>
    <tr><th>モニター合計 (税込)</th><td><?php echo number_format($item['monitor_total'] ?? 0, 0) . " 円"; ?></td></tr>
    <tr><th>サービス品1 (税込)</th><td><?php echo number_format($item['service_item_1'] ?? 0, 0) . " 円"; ?></td></tr>
    <tr><th>1内容</th><td><?php echo htmlspecialchars($item['service_content_1'] ?? '未設定'); ?></td></tr>
    <tr><th>サービス品2 (税込)</th><td><?php echo number_format($item['service_item_2'] ?? 0, 0) . " 円"; ?></td></tr>
    <tr><th>2内容</th><td><?php echo htmlspecialchars($item['service_content_2'] ?? '未設定'); ?></td></tr>
    <tr><th>サービス品3 (税込)</th><td><?php echo number_format($item['service_item_3'] ?? 0, 0) . " 円"; ?></td></tr>
    <tr><th>3内容</th><td><?php echo htmlspecialchars($item['service_content_3'] ?? '未設定'); ?></td></tr>
    <tr><th>サービス合計 (税込)</th><td><?php echo number_format($item['service_total'] ?? 0, 0) . " 円"; ?></td></tr>
    <tr><th>その他</th><td><?php echo htmlspecialchars($item['others'] ?? '未設定'); ?></td></tr>
</table>
<form method="POST" action="process_delete_order_details.php">
    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
    <input type="hidden" name="id" value="<?php echo htmlspecialchars($item['id']); ?>">
    <input type="submit" value="削除" style="background-color: #ff4444; color: white; border: none; padding: 5px 10px; cursor: pointer;">
    <a href="order_details_list.php" style="margin-left: 10px; text-decoration: none; color: #007BFF;">キャンセル</a>
</form>
</body>
</html>
<?php
} catch (Exception $e) {
    echo '<p class="error">' . htmlspecialchars($e->getMessage()) . '</p>';
}
?>