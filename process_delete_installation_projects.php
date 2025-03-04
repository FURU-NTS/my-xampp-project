<?php
include_once 'db_connection.php';
include_once 'config.php';

try {
    $conn = getDBConnection();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception('無効なリクエストメソッドです');
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) throw new Exception('CSRFトークンが無効です');

    $project_id = $_POST['project_id'] ?? '';
    if (empty($project_id)) throw new Exception('プロジェクトIDが指定されていません');

    $stmt = $conn->prepare("DELETE FROM installation_projects WHERE project_id = ?");
    $stmt->execute([$project_id]);

    header('Location: installation_projects_list.php?status=success&message=工事プロジェクトが削除されました');
    exit;
} catch (Exception $e) {
    header('Location: delete_installation_projects.php?project_id=' . urlencode($_POST['project_id']) . '&status=error&message=' . urlencode($e->getMessage()));
    exit;
}
?>