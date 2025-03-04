<?php
include_once 'db_connection.php';
include_once 'config.php';

try {
    $conn = getDBConnection();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception('無効なリクエストメソッドです');
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) throw new Exception('CSRFトークンが無効です');

    $maintenance_id = $_POST['maintenance_id'] ?? '';
    $lease_device_id = $_POST['lease_device_id'] ?? '';
    $maintenance_date = $_POST['maintenance_date'] ?? '';
    $maintenance_type = $_POST['maintenance_type'] ?? '';
    $technician_name = trim($_POST['technician_name'] ?? '');
    $maintenance_details = trim($_POST['maintenance_details'] ?? '');
    $next_maintenance_date = $_POST['next_maintenance_date'] ?: null;

    if (empty($maintenance_id) || empty($lease_device_id) || empty($maintenance_date) || empty($maintenance_type)) throw new Exception('必須項目を入力してください');
    if (!in_array($maintenance_type, ['regular', 'emergency', 'installation', 'removal'])) throw new Exception('無効な保守タイプです');

    $stmt = $conn->prepare(
        "UPDATE maintenance_records SET lease_device_id = ?, maintenance_date = ?, maintenance_type = ?, technician_name = ?, maintenance_details = ?, next_maintenance_date = ? 
         WHERE maintenance_id = ?"
    );
    $stmt->execute([$lease_device_id, $maintenance_date, $maintenance_type, $technician_name, $maintenance_details, $next_maintenance_date, $maintenance_id]);

    header('Location: maintenance_records_list.php?status=success&message=保守記録が更新されました');
    exit;
} catch (Exception $e) {
    header('Location: edit_maintenance_records.php?maintenance_id=' . urlencode($_POST['maintenance_id']) . '&status=error&message=' . urlencode($e->getMessage()));
    exit;
}
?>