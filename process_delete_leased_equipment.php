<?php
include_once 'db_connection.php';
include_once 'config.php';

try {
    $conn = getDBConnection();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception('無効なリクエストメソッドです');
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) throw new Exception('CSRFトークンが無効です');

    $leased_equipment_id = $_POST['leased_equipment_id'] ?? '';
    if (empty($leased_equipment_id)) throw new Exception('リース機器IDが指定されていません');

    $stmt = $conn->prepare("DELETE FROM leased_equipment WHERE leased_equipment_id = ?");
    $stmt->execute([$leased_equipment_id]);

    header('Location: leased_equipment_list.php?status=success&message=リース機器が削除されました');
    exit;
} catch (Exception $e) {
    header('Location: delete_leased_equipment.php?leased_equipment_id=' . urlencode($_POST['leased_equipment_id']) . '&status=error&message=' . urlencode($e->getMessage()));
    exit;
}
?>