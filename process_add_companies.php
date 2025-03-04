<?php
include_once 'db_connection.php';
include_once 'config.php';

try {
    $conn = getDBConnection();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception('無効なリクエストメソッドです');
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) throw new Exception('CSRFトークンが無効です');

    $company_name = $_POST['company_name'] ?? '';
    $business_registration_number = $_POST['business_registration_number'] ?? '';
    $industry_type = $_POST['industry_type'] ?? '';
    $address = $_POST['address'] ?? '';
    $postal_code = $_POST['postal_code'] ?? '';
    $phone_number = $_POST['phone_number'] ?? '';
    $email = $_POST['email'] ?? '';
    $representative_name = $_POST['representative_name'] ?? '';
    $decision_maker_1 = $_POST['decision_maker_1'] ?? '';
    $decision_maker_2 = $_POST['decision_maker_2'] ?? '';
    $representative_income = $_POST['representative_income'] ?? '';
    $representative_contact = $_POST['representative_contact'] ?? '';
    $employee_count = $_POST['employee_count'] ?? '';
    $capital = $_POST['capital'] ?? '';
    $revenue = $_POST['revenue'] ?? '';
    $teikoku = $_POST['teikoku'] ?? '';
    $tosho = $_POST['tosho'] ?? '';
    $memo = $_POST['memo'] ?? '';

    if (empty($company_name) || empty($address)) {
        throw new Exception('必須項目を入力してください');
    }

    $stmt = $conn->prepare(
        "INSERT INTO companies (
            company_name, business_registration_number, industry_type, address, postal_code, 
            phone_number, email, representative_name, decision_maker_1, decision_maker_2, 
            representative_income, representative_contact, employee_count, capital, revenue, 
            teikoku, tosho, memo
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->execute([
        $company_name, $business_registration_number, $industry_type, $address, $postal_code,
        $phone_number, $email, $representative_name, $decision_maker_1, $decision_maker_2,
        $representative_income, $representative_contact, $employee_count, $capital, $revenue,
        $teikoku, $tosho, $memo
    ]);

    header('Location: companies_list.php?status=success&message=顧客企業が追加されました');
    exit;
} catch (Exception $e) {
    header('Location: add_companies.php?status=error&message=' . urlencode($e->getMessage()));
    exit;
}
?>