<?php
include_once 'db_connection.php';
include_once 'config.php';
ob_start();

try {
    $conn = getDBConnection();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception('無効なリクエストメソッドです');
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) throw new Exception('CSRFトークンが無効です');

    $credit_application_id = $_POST['credit_application_id'] ?? '';
    $company_id = $_POST['company_id'] ?? '';
    $provider_id = $_POST['provider_id'] ?? '';
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';
    $monthly_fee = $_POST['monthly_fee'] ?? '';
    $total_payments = $_POST['total_payments'] ?? '';
    $special_case = $_POST['special_case'] ?? '';
    $status = $_POST['status'] ?? '';
    $equipment_ids = $_POST['equipment_ids'] ?? [];

    $missing_fields = [];
    if (empty($company_id)) $missing_fields[] = 'company_id';
    if (empty($provider_id)) $missing_fields[] = 'provider_id';
    if (empty($start_date)) $missing_fields[] = 'start_date';
    if (empty($end_date)) $missing_fields[] = 'end_date';
    if (empty($monthly_fee)) $missing_fields[] = 'monthly_fee';
    if (empty($total_payments)) $missing_fields[] = 'total_payments';
    if ($special_case === '' && !empty($credit_application_id)) $missing_fields[] = 'special_case';
    if (empty($status)) $missing_fields[] = 'status';

    if (!empty($missing_fields)) {
        error_log("Missing required fields: " . implode(', ', $missing_fields));
        throw new Exception('必須項目を入力してください: ' . implode(', ', $missing_fields));
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
        "INSERT INTO lease_contracts (credit_application_id, company_id, provider_id, start_date, end_date, monthly_fee, total_payments, payments_made, special_case, status) 
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->execute([$credit_application_id, $company_id, $provider_id, $start_date, $end_date, $monthly_fee, $total_payments, $payments_made, $special_case, $status]);
    $contract_id = $conn->lastInsertId();

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
    error_log("Lease contract added successfully: ID = $contract_id, payments_made = $payments_made");
    ob_end_clean();
    echo "<script>window.location.href='lease_contracts_list.php?status=success&message=" . urlencode('リース契約が追加されました') . "';</script>";
    exit;
} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    error_log("Error in process_add_lease_contracts.php: " . $e->getMessage());
    ob_end_clean();
    echo "<script>window.location.href='add_lease_contracts.php?status=error&message=" . urlencode($e->getMessage()) . "';</script>";
    exit;
}
?>