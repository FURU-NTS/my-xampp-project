<?php
include_once 'db_connection.php';
include_once 'config.php';

ob_start();

try {
    $conn = getDBConnection();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception('無効なリクエストメソッドです');
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) throw new Exception('CSRFトークンが無効です');

    $company_id = $_POST['company_id'] ?? '';
    $order_id = $_POST['order_id'] ?? '';
    $provider_id = $_POST['provider_id'] ?? '';
    $application_date = $_POST['application_date'] ?? '';
    $monthly_fee = $_POST['monthly_fee'] ?? '';
    $total_payments = $_POST['total_payments'] ?? '';
    $memo = $_POST['memo'] ?? '';
    $expected_payment = $_POST['expected_payment'] ?? '';
    $status = $_POST['status'] ?? '';
    $special_case = $_POST['special_case'] ?? '';

    if (empty($company_id) || empty($order_id) || empty($provider_id) || empty($application_date) || empty($monthly_fee) || empty($total_payments) || empty($expected_payment) || empty($status)) {
        throw new Exception('必須項目を入力してください');
    }
    if (!is_numeric($monthly_fee) || $monthly_fee < 0) throw new Exception('月額 (税抜) は0以上の整数を入力してください');
    if (!is_numeric($total_payments) || $total_payments < 1) throw new Exception('回数は1以上の整数を入力してください');
    if (!is_numeric($expected_payment) || $expected_payment < 0) throw new Exception('見積金額 (税込) は0以上の整数を入力してください');
    if (!in_array($status, ['準備中', '与信中', '条件あり', '与信OK', '特案OK', '与信NG', '手続き待ち', '手続きOK', '承認待ち', '承認完了', '証明書待ち', '入金待ち', '入金完了', '商談保留', '商談キャンセル', '承認後キャンセル'])) {
        throw new Exception('無効なステータスです');
    }
    if (!in_array($special_case, ['', '補償'])) throw new Exception('無効な特案値です');

    $stmt = $conn->prepare(
        "INSERT INTO credit_applications (company_id, order_id, provider_id, application_date, monthly_fee, total_payments, memo, expected_payment, status, special_case) 
         VALUES (:company_id, :order_id, :provider_id, :application_date, :monthly_fee, :total_payments, :memo, :expected_payment, :status, :special_case)"
    );
    $stmt->execute([
        ':company_id' => $company_id,
        ':order_id' => $order_id,
        ':provider_id' => $provider_id,
        ':application_date' => $application_date,
        ':monthly_fee' => $monthly_fee,
        ':total_payments' => $total_payments,
        ':memo' => $memo,
        ':expected_payment' => $expected_payment,
        ':status' => $status,
        ':special_case' => $special_case
    ]);

    $last_id = $conn->lastInsertId();
    error_log("Data inserted: application_id=$last_id, company_id=$company_id, status=$status");

    ob_end_clean();
    // JavaScriptでリダイレクト（キャンセルと同じ方法）
    echo "<script>window.location.href='credit_applications_list.php?status=success&message=" . urlencode('リース審査が追加されました') . "';</script>";
    exit;
} catch (Exception $e) {
    ob_end_clean();
    error_log("Error in process_add_credit_applications.php: " . $e->getMessage());
    echo "<script>window.location.href='add_credit_applications.php?error=" . urlencode($e->getMessage()) . "';</script>";
    exit;
}
?>