<?php
include_once 'db_connection.php';
include_once 'config.php';

try {
    $conn = getDBConnection();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception('無効なリクエストメソッドです');
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) throw new Exception('CSRFトークンが無効です');

    $company_id = $_POST['company_id'] ?? '';
    $customer_name = $_POST['customer_name'] ?? '';
    $customer_type = $_POST['customer_type'] ?? '';
    $order_date = $_POST['order_date'] ?? '';
    $monthly_fee = $_POST['monthly_fee'] ?? '';
    $total_payments = $_POST['total_payments'] ?? '';
    $negotiation_status = $_POST['negotiation_status'] ?? '';
    $construction_status = $_POST['construction_status'] ?? '';
    $credit_status = $_POST['credit_status'] ?? '';
    $document_status = $_POST['document_status'] ?? '';
    $rewrite_status = $_POST['rewrite_status'] ?? '';
    $seal_certificate_status = $_POST['seal_certificate_status'] ?? '';
    $shipping_status = $_POST['shipping_status'] ?? '';
    $sales_rep_id = $_POST['sales_rep_id'] ?? '';
    $sales_rep_id_2 = $_POST['sales_rep_id_2'] ?? '';
    $sales_rep_id_3 = $_POST['sales_rep_id_3'] ?? '';
    $sales_rep_id_4 = $_POST['sales_rep_id_4'] ?? '';
    $appointment_rep_id_1 = $_POST['appointment_rep_id_1'] ?? '';
    $appointment_rep_id_2 = $_POST['appointment_rep_id_2'] ?? '';
    $rewriting_person_id = $_POST['rewriting_person_id'] ?? '';

    file_put_contents('C:\xampp_new\htdocs\LeaseAndMaintenanceDB\debug.log', 
        "Received negotiation_status: " . ($negotiation_status ?: 'EMPTY') . "\n", FILE_APPEND);

    if (empty($customer_name) || empty($customer_type) || empty($order_date) || empty($monthly_fee) || empty($total_payments)) {
        throw new Exception('必須項目を入力してください');
    }
    if (!is_numeric($monthly_fee) || $monthly_fee < 0) throw new Exception('月額 (税抜) は0以上の整数を入力してください');
    if (!is_numeric($total_payments) || $total_payments < 1) throw new Exception('回数は1以上の整数を入力してください');
    if (!in_array($customer_type, ['新規', '既存', '旧顧客'])) throw new Exception('無効な客層です');
    if ($negotiation_status && !in_array($negotiation_status, ['未設定', '進行中', '与信怪しい', '工事前再説', '工事後再説', '工事前キャンセル', '工事後キャンセル', '書換完了', '承認完了', '承認後キャンセル'])) {
        throw new Exception('無効な商談ステータスです: ' . $negotiation_status);
    }
    if ($construction_status && !in_array($construction_status, ['待ち', '与信待ち', '残あり', '完了', '回収待ち', '回収完了'])) {
        throw new Exception('無効な工事ステータスです: ' . $construction_status);
    }
    if ($credit_status && !in_array($credit_status, ['待ち', '与信中', '再与信中', '与信OK', '与信NG'])) throw new Exception('無効な与信ステータスです');
    if ($document_status && !in_array($document_status, ['待ち', '準備中', '変更中', '発送済', '受取済'])) throw new Exception('無効な書類ステータスです');
    if ($rewrite_status && !in_array($rewrite_status, ['待ち', '準備中', 'アポOK', '残あり', '完了'])) throw new Exception('無効な書換ステータスです');
    if ($seal_certificate_status && !in_array($seal_certificate_status, ['不要', '取得待', '回収待', '完了'])) throw new Exception('無効な印鑑証明ステータスです');
    if ($shipping_status && !in_array($shipping_status, ['準備中', '発送済'])) throw new Exception('無効な発送ステータスです');

    file_put_contents('C:\xampp_new\htdocs\LeaseAndMaintenanceDB\debug.log', 
        "Negotiation Status before insert: " . ($negotiation_status ?: 'NULL') . "\n", FILE_APPEND);

    $stmt = $conn->prepare(
        "INSERT INTO orders (
            company_id, customer_name, customer_type, order_date, monthly_fee, total_payments, 
            negotiation_status, construction_status, credit_status, document_status, rewrite_status, 
            seal_certificate_status, shipping_status, sales_rep_id, sales_rep_id_2, sales_rep_id_3, 
            sales_rep_id_4, appointment_rep_id_1, appointment_rep_id_2, rewriting_person_id
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->execute([
        $company_id ?: null, $customer_name, $customer_type, $order_date, $monthly_fee, $total_payments,
        $negotiation_status ?: null, $construction_status ?: null, $credit_status ?: null,
        $document_status ?: null, $rewrite_status ?: null, $seal_certificate_status ?: null,
        $shipping_status ?: null,
        $sales_rep_id ?: null, $sales_rep_id_2 ?: null, $sales_rep_id_3 ?: null, $sales_rep_id_4 ?: null,
        $appointment_rep_id_1 ?: null, $appointment_rep_id_2 ?: null, $rewriting_person_id ?: null
    ]);

    $stmt = $conn->prepare("SELECT negotiation_status FROM orders WHERE id = LAST_INSERT_ID()");
    $stmt->execute();
    $inserted = $stmt->fetch();
    file_put_contents('C:\xampp_new\htdocs\LeaseAndMaintenanceDB\debug.log', 
        "Negotiation Status after insert: " . ($inserted['negotiation_status'] ?? 'NULL') . "\n", FILE_APPEND);

    header('Location: orders_list.php?status=success&message=受注が追加されました');
    exit;
} catch (Exception $e) {
    file_put_contents('C:\xampp_new\htdocs\LeaseAndMaintenanceDB\debug.log', 
        "Error: " . $e->getMessage() . "\n", FILE_APPEND);
    header('Location: add_orders.php?status=error&message=' . urlencode($e->getMessage()));
    exit;
}
?>