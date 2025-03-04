<?php
include_once 'db_connection.php';
include_once 'config.php';

try {
    $conn = getDBConnection();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception('無効なリクエストメソッドです');
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) throw new Exception('CSRFトークンが無効です');

    $id = $_POST['id'] ?? '';
    if (empty($id)) throw new Exception('受注詳細IDが指定されていません');

    $stmt = $conn->prepare("DELETE FROM order_details WHERE id = ?");
    $stmt->execute([$id]);

    header('Location: order_details_list.php?status=success&message=受注詳細が削除されました');
    exit;
} catch (Exception $e) {
    header('Location: delete_order_details.php?id=' . urlencode($_POST['id']) . '&status=error&message=' . urlencode($e->getMessage()));
    exit;
}
?>