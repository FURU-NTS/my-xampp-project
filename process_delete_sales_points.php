<?php
include_once 'db_connection.php';
include_once 'config.php';

try {
    $conn = getDBConnection();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception('無効なリクエストメソッドです');
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) throw new Exception('CSRFトークンが無効です');

    $order_id = $_POST['order_id'] ?? '';

    if (empty($order_id)) throw new Exception('受注IDが指定されていません');

    $stmt = $conn->prepare("DELETE FROM sales_points WHERE order_id = ?");
    $stmt->execute([$order_id]);

    header('Location: sales_points_list.php?status=success&message=ポイントが削除されました');
    exit;
} catch (Exception $e) {
    header('Location: delete_sales_points.php?order_id=' . urlencode($order_id) . '&status=error&message=' . urlencode($e->getMessage()));
    exit;
}
?>