<?php
include_once 'db_connection.php';
include_once 'config.php';

try {
    $conn = getDBConnection();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception('無効なリクエストメソッドです');
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) throw new Exception('CSRFトークンが無効です');

    $request_id = $_POST['request_id'] ?? '';
    if (empty($request_id)) throw new Exception('受付IDが指定されていません');

    $stmt = $conn->prepare("DELETE FROM maintenance_requests WHERE request_id = ?");
    $stmt->execute([$request_id]);

    header('Location: maintenance_requests_list.php?status=success&message=保守受付が削除されました');
    exit;
} catch (Exception $e) {
    header('Location: delete_maintenance_requests.php?request_id=' . urlencode($_POST['request_id']) . '&status=error&message=' . urlencode($e->getMessage()));
    exit;
}
?>