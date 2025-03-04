<?php
include_once 'db_connection.php';
include_once 'config.php';

try {
    $conn = getDBConnection();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception('無効なリクエストメソッドです');
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) throw new Exception('CSRFトークンが無効です');

    $equipment_id = $_POST['equipment_id'] ?? '';
    $contract_id = $_POST['contract_id'] ?? '';
    $serial_number = trim($_POST['serial_number'] ?? '');
    $installation_date = $_POST['installation_date'] ?: null;
    $last_maintenance_date = $_POST['last_maintenance_date'] ?: null;

    if (empty($equipment_id) || empty($contract_id) || empty($serial_number)) throw new Exception('必須項目を入力してください');

    $stmt = $conn->prepare(
        "INSERT INTO leased_equipment (equipment_id, contract_id, serial_number, installation_date, last_maintenance_date) 
         VALUES (?, ?, ?, ?, ?)"
    );
    $stmt->execute([$equipment_id, $contract_id, $serial_number, $installation_date, $last_maintenance_date]);

    header('Location: leased_equipment_list.php?status=success&message=リース機器が追加されました');
    exit;
} catch (Exception $e) {
    header('Location: add_leased_equipment.php?status=error&message=' . urlencode($e->getMessage()));
    exit;
}
?>