<?php
include_once 'db_connection.php';
include_once 'config.php';

try {
    $conn = getDBConnection();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception('無効なリクエストメソッドです');
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) throw new Exception('CSRFトークンが無効です');

    $application_id = $_POST['application_id'] ?? '';
    $company_id = $_POST['company_id'] ?? '';
    $provider_id = $_POST['provider_id'] ?? '';
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';
    $monthly_fee = $_POST['monthly_fee'] ?? '';
    $total_payments = $_POST['total_payments'] ?? '';
    $status = $_POST['status'] ?? '';

    if (empty($application_id) || empty($company_id) || empty($provider_id) || empty($start_date) || empty($end_date) || empty($monthly_fee) || empty($total_payments) || empty($status)) {
        throw new Exception('必須項目を入力してください');
    }
    if (!is_numeric($monthly_fee) || $monthly_fee < 0) throw new Exception('月額 (税抜) は0以上の整数を入力してください');
    if (!is_numeric($total_payments) || $total_payments < 1) throw new Exception('回数は1以上の整数を入力してください');
    if (!in_array($status, ['contract_active', 'offsetting', 'early_termination', 'expired', 'lost_to_competitor'])) {
        throw new Exception('無効なステータスです');
    }

    // トランザクション開始
    try {
        $conn->beginTransaction();

        // lease_contracts に挿入
        $stmt = $conn->prepare(
            "INSERT INTO lease_contracts (company_id, provider_id, start_date, end_date, monthly_fee, total_payments, status) 
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([$company_id, $provider_id, $start_date, $end_date, $monthly_fee, $total_payments, $status]);
        $contract_id = $conn->lastInsertId();

        // credit_applications に contract_id を更新
        $update_stmt = $conn->prepare(
            "UPDATE credit_applications SET contract_id = ? WHERE application_id = ?"
        );
        $update_stmt->execute([$contract_id, $application_id]);

        $conn->commit();

        header('Location: credit_applications_list.php?status=success&message=リース契約が登録されました');
        exit;
    } catch (Exception $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        throw $e; // 外側の catch にエラーを伝播
    }
} catch (Exception $e) {
    header('Location: add_lease_contracts_from_credit.php?application_id=' . urlencode($_POST['application_id'] ?? '') . '&status=error&message=' . urlencode($e->getMessage()));
    exit;
}
?>