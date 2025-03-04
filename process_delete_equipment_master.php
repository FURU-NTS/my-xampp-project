<?php
include_once 'db_connection.php';
include_once 'config.php';

try {
    $conn = getDBConnection();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception('無効なリクエストメソッドです');
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) throw new Exception('CSRFトークンが無効です');

    $equipment_id = $_POST['equipment_id'] ?? '';
    if (empty($equipment_id)) throw new Exception('機器IDが指定されていません');

    $stmt = $conn->prepare("DELETE FROM equipment_master WHERE equipment_id = ?");
    $stmt->execute([$equipment_id]);

    header('Location: equipment_master_list.php?status=success&message=機器マスターが削除されました');
    exit;
} catch (Exception $e) {
    header('Location: delete_equipment_master.php?equipment_id=' . urlencode($_POST['equipment_id']) . '&status=error&message=' . urlencode($e->getMessage()));
    exit;
}
?>