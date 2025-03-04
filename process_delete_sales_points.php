<?php
include_once 'db_connection.php';
include_once 'config.php';

try {
    $conn = getDBConnection();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception('無効なリクエストメソッドです');
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) throw new Exception('CSRFトークンが無効です');

    $point_id = $_POST['point_id'] ?? '';
    if (empty($point_id)) throw new Exception('ポイントIDが指定されていません');

    $stmt = $conn->prepare("DELETE FROM sales_points WHERE point_id = ?");
    $stmt->execute([$point_id]);

    header('Location: sales_points_list.php?status=success&message=ポイントが削除されました');
    exit;
} catch (Exception $e) {
    header('Location: delete_sales_points.php?point_id=' . urlencode($_POST['point_id']) . '&status=error&message=' . urlencode($e->getMessage()));
    exit;
}
?>