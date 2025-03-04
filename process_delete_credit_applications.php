<?php
include_once 'db_connection.php';
include_once 'config.php';
ob_start();

try {
    $conn = getDBConnection();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception('無効なリクエストメソッドです');
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) throw new Exception('CSRFトークンが無効です');

    $application_id = $_POST['application_id'] ?? '';
    if (empty($application_id)) throw new Exception('申請IDが指定されていません');

    $stmt = $conn->prepare("DELETE FROM credit_applications WHERE application_id = :application_id");
    $stmt->execute([':application_id' => $application_id]);

    error_log("Data deleted: application_id=$application_id");

    ob_end_clean();
    // JavaScriptでリダイレクト
    echo "<script>window.location.href='credit_applications_list.php?status=success&message=" . urlencode('リース審査が削除されました') . "';</script>";
    exit;
} catch (Exception $e) {
    ob_end_clean();
    error_log("Error in process_delete_credit_applications.php: " . $e->getMessage());
    echo "<script>window.location.href='delete_credit_applications.php?application_id=" . urlencode($_POST['application_id']) . "&error=" . urlencode($e->getMessage()) . "';</script>";
    exit;
}
?>