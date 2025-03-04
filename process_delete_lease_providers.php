<?php
include_once 'db_connection.php';
include_once 'config.php';

try {
    $conn = getDBConnection();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception('無効なリクエストメソッドです');
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) throw new Exception('CSRFトークンが無効です');

    $provider_id = $_POST['provider_id'] ?? '';
    if (empty($provider_id)) throw new Exception('リース会社IDが指定されていません');

    $stmt = $conn->prepare("DELETE FROM lease_providers WHERE provider_id = ?");
    $stmt->execute([$provider_id]);

    header('Location: lease_providers_list.php?status=success&message=リース会社が削除されました');
    exit;
} catch (Exception $e) {
    header('Location: delete_lease_providers.php?provider_id=' . urlencode($_POST['provider_id']) . '&status=error&message=' . urlencode($e->getMessage()));
    exit;
}
?>