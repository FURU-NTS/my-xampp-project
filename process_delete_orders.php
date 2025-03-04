<?php
include_once 'db_connection.php';
ob_start();

try {
    $conn = getDBConnection();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception('不正なリクエストです');
    if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        throw new Exception('CSRFトークンが無効です');
    }

    $id = $_POST['id'] ?? '';
    if (empty($id)) throw new Exception('受注IDが指定されていません');

    // order_details の関連データを削除（外部キー制約がある場合を考慮）
    $stmt = $conn->prepare("DELETE FROM order_details WHERE order_id = ?");
    $stmt->execute([$id]);

    // orders の削除
    $stmt = $conn->prepare("DELETE FROM orders WHERE id = ?");
    $stmt->execute([$id]);

    error_log("Order deleted successfully: ID = $id");
    ob_end_clean();
    header("Cache-Control: no-cache, no-store, must-revalidate");
    header("Pragma: no-cache");
    header("Expires: 0");
    header("Location: orders_list.php?status=success&message=受注が削除されました");
    exit;
} catch (Exception $e) {
    ob_end_clean();
    error_log("Error in process_delete_orders.php: " . $e->getMessage());
    header("Cache-Control: no-cache, no-store, must-revalidate");
    header("Pragma: no-cache");
    header("Expires: 0");
    header("Location: delete_orders.php?id=" . urlencode($id) . "&status=error&message=" . urlencode($e->getMessage()));
    exit;
}
?>