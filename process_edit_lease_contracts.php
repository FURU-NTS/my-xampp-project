<?php
include_once 'db_connection.php';
include_once 'config.php';

try {
    $conn = getDBConnection();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception('無効なリクエストメソッドです');
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) throw new Exception('CSRFトークンが無効です');

    $contract_id = $_POST['contract_id'] ?? '';
    $company_id = $_POST['company_id'] ?? '';
    $provider_id = $_POST['provider_id'] ?? '';
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';
    $monthly_fee = $_POST['monthly_fee'] ?? '';
    $total_payments = $_POST['total_payments'] ?? '';
    $special_case = $_POST['special_case'] ?? '';
    $status = $_POST['status'] ?? '';
    $equipment_ids = $_POST['equipment_ids'] ?? [];

    if (empty($contract_id) || empty($company_id) || empty($provider_id) || empty($start_date) || empty($end_date) || empty($monthly_fee) || empty($total_payments) || empty($status)) {
        throw new Exception('必須項目を入力してください');
    }
    if (!is_numeric($monthly_fee) || $monthly_fee < 0) throw new Exception('リース月額は0以上の数値を入力してください');
    if (!is_numeric($total_payments) || $total_payments < 1) throw new Exception('回数は1以上の整数を入力してください');
    if (!in_array($status, ['contract_active', 'offsetting', 'early_termination', 'expired', 'lost_to_competitor'])) throw new Exception('無効なステータスです');
    if (!in_array($special_case, ['', '補償'])) throw new Exception('無効な特案値です');

    // payments_made を自動計算
    $current_date = new DateTime();
    $start_date_obj = new DateTime($start_date);
    $interval = $start_date_obj->diff($current_date);
    $payments_made = max(0, min(($interval->y * 12) + $interval->m, $total_payments));

    $conn->beginTransaction();

    $stmt = $conn->prepare(
        "UPDATE lease_contracts SET company_id = ?, provider_id = ?, start_date = ?, end_date = ?, monthly_fee = ?, total_payments = ?, payments_made = ?, special_case = ?, status = ? 
         WHERE contract_id = ?"
    );
    $stmt->execute([$company_id, $provider_id, $start_date, $end_date, $monthly_fee, $total_payments, $payments_made, $special_case, $status, $contract_id]);

    $current_equipment_stmt = $conn->prepare("SELECT leased_equipment_id FROM leased_equipment WHERE contract_id = ?");
    $current_equipment_stmt->execute([$contract_id]);
    $current_equipment_ids = $current_equipment_stmt->fetchAll(PDO::FETCH_COLUMN);

    if (!empty($current_equipment_ids)) {
        $placeholders = implode(',', array_fill(0, count($current_equipment_ids), '?'));
        $delete_maintenance_stmt = $conn->prepare("DELETE FROM maintenance_records WHERE lease_device_id IN ($placeholders)");
        $delete_maintenance_stmt->execute($current_equipment_ids);
    }

    $delete_stmt = $conn->prepare("DELETE FROM leased_equipment WHERE contract_id = ?");
    $delete_stmt->execute([$contract_id]);

    if (!empty($equipment_ids)) {
        $equipment_stmt = $conn->prepare(
            "INSERT INTO leased_equipment (equipment_id, contract_id) 
             VALUES (?, ?)"
        );
        foreach ($equipment_ids as $equipment_id) {
            $equipment_stmt->execute([$equipment_id, $contract_id]);
        }
    }

    $conn->commit();
    error_log("Lease contract updated successfully: ID = $contract_id, payments_made = $payments_made");
    ob_end_clean();
    echo "<script>window.location.href='lease_contracts_list.php?status=success&message=" . urlencode('リース契約が更新されました') . "';</script>";
    exit;
} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    error_log("Error in process_edit_lease_contracts.php: " . $e->getMessage());
    ob_end_clean();
    echo "<script>window.location.href='edit_lease_contracts.php?contract_id=" . urlencode($_POST['contract_id']) . "&status=error&message=" . urlencode($e->getMessage()) . "';</script>";
    exit;
}
?>