<?php
include_once 'db_connection.php';
include_once 'config.php';
ob_start();

try {
    $conn = getDBConnection();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception('無効なリクエストメソッドです');
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) throw new Exception('CSRFトークンが無効です');

    $task_id = $_POST['task_id'] ?? '';
    $project_id = $_POST['project_id'] ?? '';
    $task_name = $_POST['task_name'] ?? '';
    $employee_id = $_POST['employee_id'] ?? '';
    $status = $_POST['status'] ?? '';
    $memo = $_POST['memo'] ?? '';

    $missing_fields = [];
    if (empty($task_id)) $missing_fields[] = 'task_id';
    if (empty($project_id)) $missing_fields[] = 'project_id';
    if (empty($task_name)) $missing_fields[] = 'task_name';
    if (empty($employee_id)) $missing_fields[] = 'employee_id';
    if (empty($status)) $missing_fields[] = 'status';

    if (!empty($missing_fields)) {
        throw new Exception('必須項目が不足しています: ' . implode(', ', $missing_fields));
    }

    if (!in_array($status, ['not_started', 'in_progress', 'completed'])) {
        throw new Exception('無効なステータス値です');
    }

    $conn->beginTransaction();

    $stmt = $conn->prepare(
        "UPDATE installation_tasks SET project_id = ?, task_name = ?, employee_id = ?, status = ?, memo = ? WHERE task_id = ?"
    );
    $stmt->execute([$project_id, $task_name, $employee_id, $status, $memo, $task_id]);

    // プロジェクトのステータス自動更新
    $stmt = $conn->prepare("SELECT COUNT(*) as total, SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed FROM installation_tasks WHERE project_id = ?");
    $stmt->execute([$project_id]);
    $task_status = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($task_status['total'] > 0 && $task_status['total'] == $task_status['completed']) {
        $stmt = $conn->prepare("UPDATE installation_projects SET status = 'completed' WHERE project_id = ?");
        $stmt->execute([$project_id]);
    } else {
        $stmt = $conn->prepare("UPDATE installation_projects SET status = 'in_progress' WHERE project_id = ? AND status != 'completed'");
        $stmt->execute([$project_id]);
    }

    $conn->commit();
    ob_end_clean();
    echo "<script>window.location.href='installation_tasks_list.php?status=success&message=" . urlencode('タスクが更新されました') . "';</script>";
    exit;

} catch (Exception $e) {
    if ($conn->inTransaction()) $conn->rollBack();
    error_log("Error in process_edit_installation_tasks.php: " . $e->getMessage());
    ob_end_clean();
    echo "<script>window.location.href='edit_installation_tasks.php?task_id=" . urlencode($task_id) . "&status=error&message=" . urlencode($e->getMessage()) . "';</script>";
    exit;
}
?>