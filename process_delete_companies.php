<?php
include_once 'db_connection.php';
include_once 'config.php';

ob_start(); // 出力バッファリング開始

try {
    $conn = getDBConnection();
    if (!$conn) {
        throw new Exception('データベース接続に失敗しました');
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('無効なリクエストメソッドです');
    }

    $csrf_token = $_POST['csrf_token'] ?? null;
    if (!isset($csrf_token) || !validateCsrfToken($csrf_token)) {
        throw new Exception('CSRFトークンが無効です');
    }

    $company_id = $_POST['company_id'] ?? '';
    if (empty($company_id)) {
        throw new Exception('会社IDが指定されていません');
    }

    // トランザクション開始
    $conn->beginTransaction();

    // order_details の関連データを削除
    $stmt = $conn->prepare("DELETE FROM order_details WHERE order_id IN (SELECT id FROM orders WHERE company_id = ?)");
    if (!$stmt) {
        throw new Exception('order_details削除準備に失敗しました: ' . $conn->errorInfo()[2]);
    }
    $result = $stmt->execute([$company_id]);
    if (!$result) {
        throw new Exception('order_details削除に失敗しました: ' . $stmt->errorInfo()[2]);
    }

    // credit_applications の関連データを削除
    $stmt = $conn->prepare("DELETE FROM credit_applications WHERE company_id = ? OR order_id IN (SELECT id FROM orders WHERE company_id = ?)");
    if (!$stmt) {
        throw new Exception('credit_applications削除準備に失敗しました: ' . $conn->errorInfo()[2]);
    }
    $result = $stmt->execute([$company_id, $company_id]);
    if (!$result) {
        throw new Exception('credit_applications削除に失敗しました: ' . $stmt->errorInfo()[2]);
    }

    // orders の関連データを削除
    $stmt = $conn->prepare("DELETE FROM orders WHERE company_id = ?");
    if (!$stmt) {
        throw new Exception('orders削除準備に失敗しました: ' . $conn->errorInfo()[2]);
    }
    $result = $stmt->execute([$company_id]);
    if (!$result) {
        throw new Exception('orders削除に失敗しました: ' . $stmt->errorInfo()[2]);
    }

    // lease_contracts の関連データを削除
    $stmt = $conn->prepare("DELETE FROM lease_contracts WHERE company_id = ?");
    if (!$stmt) {
        throw new Exception('lease_contracts削除準備に失敗しました: ' . $conn->errorInfo()[2]);
    }
    $result = $stmt->execute([$company_id]);
    if (!$result) {
        throw new Exception('lease_contracts削除に失敗しました: ' . $stmt->errorInfo()[2]);
    }

    // companies のレコードを削除
    $stmt = $conn->prepare("DELETE FROM companies WHERE company_id = ?");
    if (!$stmt) {
        throw new Exception('companies削除準備に失敗しました: ' . $conn->errorInfo()[2]);
    }
    $result = $stmt->execute([$company_id]);
    if (!$result) {
        throw new Exception('companies削除に失敗しました: ' . $stmt->errorInfo()[2]);
    }

    // トランザクションコミット
    $conn->commit();

    // 成功時の遷移
    ob_end_clean();
    header('Location: companies_list.php?status=success&message=顧客企業が削除されました');
    exit;
} catch (Exception $e) {
    if ($conn && $conn->inTransaction()) {
        $conn->rollBack();
    }
    ob_end_clean();
    $company_id = isset($_POST['company_id']) ? urlencode($_POST['company_id']) : '';
    header('Location: delete_companies.php?company_id=' . $company_id . '&status=error&message=' . urlencode('削除に失敗しました。関連データが存在するため、削除できません: ' . $e->getMessage()));
    exit;
}
?>