<?php
include_once 'db_connection.php';
include_once 'config.php';

try {
    $conn = getDBConnection();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('無効なリクエストメソッドです');
    }
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        throw new Exception('CSRFトークンが無効です');
    }

    $employee_id = $_POST['employee_id'] ?? '';
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone_number = trim($_POST['phone_number'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $position = trim($_POST['position'] ?? '');

    if (empty($employee_id) || empty($full_name)) throw new Exception('IDと氏名は必須です');
    if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) throw new Exception('メールアドレスが無効です');
    if ($phone_number && !preg_match('/^\d{2,4}-?\d{2,4}-?\d{3,4}$/', $phone_number)) {
        throw new Exception('電話番号が無効です');
    }

    $stmt = $conn->prepare(
        "UPDATE employees SET full_name = ?, email = ?, phone_number = ?, department = ?, position = ? 
         WHERE employee_id = ?"
    );
    $stmt->execute([$full_name, $email, $phone_number, $department, $position, $employee_id]);

    header('Location: employee_list.php?status=success&message=担当者が更新されました');
    exit;
} catch (Exception $e) {
    header('Location: edit_employee.php?employee_id=' . urlencode($_POST['employee_id']) . '&status=error&message=' . urlencode($e->getMessage()));
    exit;
}
?>