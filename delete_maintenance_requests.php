<?php
include_once 'db_connection.php';
$page_title = "保守受付削除";
include_once 'header.php';

try {
    $conn = getDBConnection();
    $request_id = $_GET['request_id'] ?? '';
    if (empty($request_id)) throw new Exception('受付IDが指定されていません');

    $stmt = $conn->prepare("SELECT mr.*, o.customer_name FROM maintenance_requests mr LEFT JOIN orders o ON mr.order_id = o.id WHERE request_id = ?");
    $stmt->execute([$request_id]);
    $item = $stmt->fetch();
    if (!$item) throw new Exception('保守受付が見つかりません');
?>
<p>以下の保守受付を削除してもよろしいですか？</p>
<p>受注顧客名: <?php echo htmlspecialchars($item['customer_name'] ?? '未指定'); ?> | ステータス: <?php echo htmlspecialchars($item['status']); ?> | 受付日: <?php echo htmlspecialchars($item['request_date']); ?></p>
<form method="POST" action="process_delete_maintenance_requests.php">
    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
    <input type="hidden" name="request_id" value="<?php echo htmlspecialchars($item['request_id']); ?>">
    <input type="submit" value="削除">
    <a href="maintenance_requests_list.php">キャンセル</a>
</form>
</body></html>
<?php
} catch (Exception $e) {
    echo '<p class="error">' . htmlspecialchars($e->getMessage()) . '</p>';
}
?>