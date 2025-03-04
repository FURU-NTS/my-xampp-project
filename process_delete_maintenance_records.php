<?php
include_once 'db_connection.php';
include_once 'config.php';

try {
    $conn = getDBConnection();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception('無効なリクエストメソッドです');
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) throw new Exception('CSRFトークンが無効です');

    $maintenance_id = $_POST['maintenance_id'] ?? '';
    if (empty($maintenance_id)) throw new Exception('保守記録IDが指定されていません');

    $stmt = $conn->prepare("DELETE FROM maintenance_records WHERE maintenance_id = ?");
    $stmt->execute([$maintenance_id]);

    header('Location: maintenance_records_list.php?status=success&message=保守記録が削除されました');
    exit;
} catch (Exception $e) {
    header('Location: delete_maintenance_records.php?maintenance_id=' . urlencode($_POST['maintenance_id']) . '&status=error&message=' . urlencode($e->getMessage()));
    exit;
}
?>