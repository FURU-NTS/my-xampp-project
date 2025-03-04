<?php
include_once 'db_connection.php';
include_once 'config.php';

try {
    $conn = getDBConnection();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception('無効なリクエストメソッドです');
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) throw new Exception('CSRFトークンが無効です');

    $application_id = $_POST['application_id'] ?? '';
    $company_id = $_POST['company_id'] ?? '';
    $order_id = $_POST['order_id'] ?? '';
    $provider_id = $_POST['provider_id'] ?? '';
    $application_date = $_POST['application_date'] ?? '';
    $monthly_fee = $_POST['monthly_fee'] ?? '';
    $total_payments = $_POST['total_payments'] ?? '';
    $expected_payment = $_POST['expected_payment'] ?? '';
    $expected_payment_date = $_POST['expected_payment_date'] ?? '';
    $status = $_POST['status'] ?? '';
    $special_case = $_POST['special_case'] ?? '';
    $memo = $_POST['memo'] ?? '';

    if (empty($application_id) || empty($company_id) || empty($order_id) || empty($provider_id) || empty($application_date) || empty($monthly_fee) || empty($total_payments) || empty($status)) {
        throw new Exception('必須項目を入力してください');
    }
    if (!is_numeric($monthly_fee) || $monthly_fee < 0) throw new Exception('月額は0以上の数値を入力してください');
    if (!is_numeric($total_payments) || $total_payments < 1) throw new Exception('回数は1以上の整数を入力してください');
    if ($_SESSION['is_admin'] && !is_numeric($expected_payment)) throw new Exception('見積金額は数値を入力してください');
    if (!in_array($status, ['準備中', '与信中', '条件あり', '与信OK', '特案OK', '与信NG', '手続き待ち', '手続きOK', '承認待ち', '承認完了', '証明書待ち', '入金待ち', '入金完了', '商談保留', '商談キャンセル', '承認後キャンセル'])) {
        throw new Exception('無効なステータスです');
    }
    if (!in_array($special_case, ['', '補償'])) throw new Exception('無効な特案値です');

    $conn->beginTransaction();

    if ($_SESSION['is_admin']) {
        $stmt = $conn->prepare(
            "UPDATE credit_applications 
             SET company_id = ?, order_id = ?, provider_id = ?, application_date = ?, monthly_fee = ?, total_payments = ?, 
                 expected_payment = ?, expected_payment_date = ?, status = ?, special_case = ?, memo = ? 
             WHERE application_id = ?"
        );
        $stmt->execute([$company_id, $order_id, $provider_id, $application_date, $monthly_fee, $total_payments, 
                        $expected_payment, $expected_payment_date, $status, $special_case, $memo, $application_id]);
    } else {
        $stmt = $conn->prepare(
            "UPDATE credit_applications 
             SET company_id = ?, order_id = ?, provider_id = ?, application_date = ?, monthly_fee = ?, total_payments = ?, 
                 status = ?, special_case = ?, memo = ? 
             WHERE application_id = ?"
        );
        $stmt->execute([$company_id, $order_id, $provider_id, $application_date, $monthly_fee, $total_payments, 
                        $status, $special_case, $memo, $application_id]);
    }

    $conn->commit();
    error_log("Credit application updated successfully: ID = $application_id");
    header('Location: credit_applications_list.php?status=success&message=リース審査が更新されました');
    exit;
} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    error_log("Error in process_edit_credit_applications.php: " . $e->getMessage());
    header('Location: edit_credit_applications.php?application_id=' . urlencode($_POST['application_id']) . '&status=error&message=' . urlencode($e->getMessage()));
    exit;
}
?>