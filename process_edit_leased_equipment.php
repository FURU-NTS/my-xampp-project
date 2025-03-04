<?php
include_once 'db_connection.php';
include_once 'config.php';

try {
    $conn = getDBConnection();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception('無効なリクエストメソッドです');
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) throw new Exception('CSRFトークンが無効です');

    $leased_equipment_id = $_POST['leased_equipment_id'] ?? '';
    $equipment_id = $_POST['equipment_id'] ?? '';
    $contract_id = $_POST['contract_id'] ?? '';
    $installation_date = $_POST['installation_date'] ?: null;
    $last_maintenance_date = $_POST['last_maintenance_date'] ?: null;

    if (empty($leased_equipment_id) || empty($equipment_id) || empty($contract_id)) throw new Exception('必須項目を入力してください');

    $stmt = $conn->prepare(
        "UPDATE leased_equipment SET equipment_id = ?, contract_id = ?, installation_date = ?, last_maintenance_date = ? 
         WHERE leased_equipment_id = ?"
    );
    $stmt->execute([$equipment_id, $contract_id, $installation_date, $last_maintenance_date, $leased_equipment_id]);

    header('Location: leased_equipment_list.php?status=success&message=リース機器が更新されました');
    exit;
} catch (Exception $e) {
    header('Location: edit_leased_equipment.php?leased_equipment_id=' . urlencode($_POST['leased_equipment_id']) . '&status=error&message=' . urlencode($e->getMessage()));
    exit;
}
?>