<?php
include_once 'db_connection.php';
include_once 'config.php';

try {
    $conn = getDBConnection();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception('無効なリクエストメソッドです');
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) throw new Exception('CSRFトークンが無効です');

    $request_id = $_POST['request_id'] ?? '';
    $order_id = $_POST['order_id'] ?? '';
    $status = $_POST['status'] ?? '';
    $request_date = $_POST['request_date'] ?? '';

    if (empty($request_id) || empty($order_id) || empty($status) || empty($request_date)) throw new Exception('ID、受注、ステータス、受付日は必須です');

    $stmt = $conn->prepare(
        "UPDATE maintenance_requests SET order_id = ?, status = ?, request_date = ? 
         WHERE request_id = ?"
    );
    $stmt->execute([$order_id, $status, $request_date, $request_id]);

    header('Location: maintenance_requests_list.php?status=success&message=保守受付が更新されました');
    exit;
} catch (Exception $e) {
    header('Location: edit_maintenance_requests.php?request_id=' . urlencode($_POST['request_id']) . '&status=error&message=' . urlencode($e->getMessage()));
    exit;
}
?>