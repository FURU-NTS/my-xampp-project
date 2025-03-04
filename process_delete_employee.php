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
    if (empty($employee_id)) throw new Exception('社員IDが指定されていません');

    $stmt = $conn->prepare("DELETE FROM employees WHERE employee_id = ?");
    $stmt->execute([$employee_id]);

    header('Location: employee_list.php?status=success&message=担当者が削除されました');
    exit;
} catch (Exception $e) {
    header('Location: delete_employee.php?employee_id=' . urlencode($_POST['employee_id']) . '&status=error&message=' . urlencode($e->getMessage()));
    exit;
}
?>