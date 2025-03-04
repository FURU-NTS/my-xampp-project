<?php
include_once 'db_connection.php';
include_once 'config.php';

try {
    $conn = getDBConnection();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception('無効なリクエストメソッドです');
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) throw new Exception('CSRFトークンが無効です');

    $provider_id = $_POST['provider_id'] ?? '';
    $provider_name = trim($_POST['provider_name'] ?? '');
    $business_registration_number = trim($_POST['business_registration_number'] ?? '');
    $industry_type = trim($_POST['industry_type'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $postal_code = trim($_POST['postal_code'] ?? '');
    $phone_number = trim($_POST['phone_number'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if (empty($provider_id) || empty($provider_name) || empty($address)) throw new Exception('ID、会社名、住所は必須です');
    if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) throw new Exception('メールアドレスが無効です');
    if ($phone_number && !preg_match('/^\d{2,4}-?\d{2,4}-?\d{3,4}$/', $phone_number)) throw new Exception('電話番号が無効です');
    if ($postal_code && !preg_match('/^\d{3}-?\d{4}$/', $postal_code)) throw new Exception('郵便番号が無効です');

    $stmt = $conn->prepare(
        "UPDATE lease_providers SET provider_name = ?, business_registration_number = ?, industry_type = ?, address = ?, postal_code = ?, phone_number = ?, email = ? 
         WHERE provider_id = ?"
    );
    $stmt->execute([$provider_name, $business_registration_number, $industry_type, $address, $postal_code, $phone_number, $email, $provider_id]);

    header('Location: lease_providers_list.php?status=success&message=リース会社が更新されました');
    exit;
} catch (Exception $e) {
    header('Location: edit_lease_providers.php?provider_id=' . urlencode($_POST['provider_id']) . '&status=error&message=' . urlencode($e->getMessage()));
    exit;
}
?>