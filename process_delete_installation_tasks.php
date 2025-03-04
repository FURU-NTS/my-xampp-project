<?php
include_once 'db_connection.php';
include_once 'config.php';

try {
    $conn = getDBConnection();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception('無効なリクエストメソッドです');
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) throw new Exception('CSRFトークンが無効です');

    $task_id = $_POST['task_id'] ?? '';
    if (empty($task_id)) throw new Exception('タスクIDが指定されていません');

    $stmt = $conn->prepare("DELETE FROM installation_tasks WHERE task_id = ?");
    $stmt->execute([$task_id]);

    header('Location: installation_tasks_list.php?status=success&message=工事タスクが削除されました');
    exit;
} catch (Exception $e) {
    header('Location: delete_installation_tasks.php?task_id=' . urlencode($_POST['task_id']) . '&status=error&message=' . urlencode($e->getMessage()));
    exit;
}
?>