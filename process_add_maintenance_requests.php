<?php
include_once 'db_connection.php';
include_once 'config.php';

try {
    $conn = getDBConnection();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception('無効なリクエストメソッドです');
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) throw new Exception('CSRFトークンが無効です');

    $order_id = $_POST['order_id'] ?? '';
    $status = $_POST['status'] ?? '';
    $request_date = $_POST['request_date'] ?? '';

    if (empty($order_id) || empty($status) || empty($request_date)) throw new Exception('受注、ステータス、受付日は必須です');

    $stmt = $conn->prepare(
        "INSERT INTO maintenance_requests (order_id, status, request_date) 
         VALUES (?, ?, ?)"
    );
    $stmt->execute([$order_id, $status, $request_date]);

    header('Location: maintenance_requests_list.php?status=success&message=保守受付が追加されました');
    exit;
} catch (Exception $e) {
    header('Location: add_maintenance_requests.php?status=error&message=' . urlencode($e->getMessage()));
    exit;
}
?>