<?php
include_once 'db_connection.php';
include_once 'config.php';

try {
    $conn = getDBConnection();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception('無効なリクエストメソッドです');
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) throw new Exception('CSRFトークンが無効です');

    $point_id = $_POST['point_id'] ?? '';
    $order_id = $_POST['order_id'] ?? '';
    $sales_rep_id = $_POST['sales_rep_id'] ?? '';
    $points = $_POST['points'] ?? '';

    if (empty($point_id) || empty($order_id) || empty($sales_rep_id) || empty($points)) throw new Exception('すべての必須項目を入力してください');
    if (!is_numeric($points) || $points < 0) throw new Exception('ポイントは0以上の数値を入力してください');

    $stmt = $conn->prepare(
        "UPDATE sales_points SET order_id = ?, sales_rep_id = ?, points = ? 
         WHERE point_id = ?"
    );
    $stmt->execute([$order_id, $sales_rep_id, $points, $point_id]);

    header('Location: sales_points_list.php?status=success&message=ポイントが更新されました');
    exit;
} catch (Exception $e) {
    header('Location: edit_sales_points.php?point_id=' . urlencode($_POST['point_id']) . '&status=error&message=' . urlencode($e->getMessage()));
    exit;
}
?>