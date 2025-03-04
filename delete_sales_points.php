<?php
include_once 'db_connection.php';
$page_title = "ポイント削除";
include_once 'header.php';

try {
    $conn = getDBConnection();
    $point_id = $_GET['point_id'] ?? '';
    if (empty($point_id)) throw new Exception('ポイントIDが指定されていません');

    $stmt = $conn->prepare("SELECT sp.*, o.customer_name, e.full_name 
                            FROM sales_points sp 
                            LEFT JOIN orders o ON sp.order_id = o.id 
                            LEFT JOIN employees e ON sp.sales_rep_id = e.employee_id 
                            WHERE sp.point_id = ?");
    $stmt->execute([$point_id]);
    $item = $stmt->fetch();
    if (!$item) throw new Exception('ポイントが見つかりません');
?>
<p>以下のポイントを削除してもよろしいですか？</p>
<p>受注: <?php echo htmlspecialchars($item['customer_name']); ?></p>
<p>担当者: <?php echo htmlspecialchars($item['full_name']); ?></p>
<p>ポイント: <?php echo htmlspecialchars($item['points']); ?></p>
<form method="POST" action="process_delete_sales_points.php">
    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
    <input type="hidden" name="point_id" value="<?php echo htmlspecialchars($item['point_id']); ?>">
    <input type="submit" value="削除">
    <a href="sales_points_list.php">キャンセル</a>
</form>
</body></html>
<?php
} catch (Exception $e) {
    echo '<p class="error">' . htmlspecialchars($e->getMessage()) . '</p>';
}
?>