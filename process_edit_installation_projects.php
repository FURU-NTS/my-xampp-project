<?php
include_once 'db_connection.php';
include_once 'config.php';

try {
    $conn = getDBConnection();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception('無効なリクエストメソッドです');
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) throw new Exception('CSRFトークンが無効です');

    $project_id = $_POST['project_id'] ?? '';
    $contract_id = $_POST['contract_id'] ?? '';
    $status = $_POST['status'] ?? '';
    $start_date = $_POST['start_date'] ?? null;
    $end_date = $_POST['end_date'] ?? null;

    if (empty($project_id) || empty($contract_id) || empty($status)) throw new Exception('ID、契約、ステータスは必須です');
    if ($start_date && $end_date && $start_date > $end_date) throw new Exception('開始日は終了日より前でなければなりません');

    $stmt = $conn->prepare(
        "UPDATE installation_projects SET contract_id = ?, status = ?, start_date = ?, end_date = ? 
         WHERE project_id = ?"
    );
    $stmt->execute([$contract_id, $status, $start_date ?: null, $end_date ?: null, $project_id]);

    header('Location: installation_projects_list.php?status=success&message=工事プロジェクトが更新されました');
    exit;
} catch (Exception $e) {
    header('Location: edit_installation_projects.php?project_id=' . urlencode($_POST['project_id']) . '&status=error&message=' . urlencode($e->getMessage()));
    exit;
}
?>