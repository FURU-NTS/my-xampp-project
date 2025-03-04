<?php
include_once 'db_connection.php';
include_once 'config.php';
ob_start();

try {
    $conn = getDBConnection();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception('無効なリクエストメソッドです');
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) throw new Exception('CSRFトークンが無効です');

    $contract_id = $_POST['contract_id'] ?? '';
    if (empty($contract_id)) throw new Exception('契約IDが指定されていません');

    $conn->beginTransaction();

    $stmt = $conn->prepare("DELETE FROM leased_equipment WHERE contract_id = ?");
    $stmt->execute([$contract_id]);

    $stmt = $conn->prepare("DELETE FROM lease_contracts WHERE contract_id = ?");
    $stmt->execute([$contract_id]);

    $conn->commit();
    error_log("Lease contract deleted successfully: ID = $contract_id");
    ob_end_clean();
    echo "<script>window.location.href='lease_contracts_list.php?status=success&message=" . urlencode('リース契約が削除されました') . "';</script>";
    exit;
} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    error_log("Error in process_delete_lease_contracts.php: " . $e->getMessage());
    ob_end_clean();
    echo "<script>window.location.href='delete_lease_contracts.php?contract_id=" . urlencode($_POST['contract_id'] ?? '') . "&status=error&message=" . urlencode($e->getMessage()) . "';</script>";
    exit;
}
?>