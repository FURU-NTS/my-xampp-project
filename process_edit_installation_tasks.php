<?php
include_once 'db_connection.php';

try {
    $conn = getDBConnection();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('無効なリクエストです');
    }

    $task_id = $_POST['task_id'] ?? '';
    $project_id = $_POST['project_id'] ?? '';
    $task_name = $_POST['task_name'] ?? '';
    $status = $_POST['status'] ?? '';
    $start_date = $_POST['start_date'] ?? NULL;
    $end_date = $_POST['end_date'] ?? NULL;
    $employee_id_1 = $_POST['employee_id_1'] ?? NULL;
    $employee_id_2 = $_POST['employee_id_2'] ?? NULL;
    $memo = $_POST['memo'] ?? '';

    if (empty($task_id) || empty($project_id) || empty($task_name) || empty($status)) {
        throw new Exception('必須項目が入力されていません');
    }

    // project_id の存在確認
    $stmt = $conn->prepare("SELECT project_id FROM installation_projects WHERE project_id = ?");
    $stmt->execute([$project_id]);
    if (!$stmt->fetch()) {
        throw new Exception('指定されたプロジェクトが見つかりません');
    }

    $stmt = $conn->prepare("UPDATE installation_tasks 
                            SET project_id = ?, task_name = ?, status = ?, start_date = ?, end_date = ?, 
                                employee_id_1 = ?, employee_id_2 = ?, memo = ?
                            WHERE task_id = ?");
    $stmt->execute([$project_id, $task_name, $status, $start_date ?: NULL, $end_date ?: NULL, 
                    $employee_id_1 ?: NULL, $employee_id_2 ?: NULL, $memo, $task_id]);

    header("Location: installation_tasks_list.php?status=success&message=工事タスクが更新されました");
    exit;
} catch (Exception $e) {
    header("Location: installation_tasks_list.php?status=error&message=" . urlencode($e->getMessage()));
    exit;
}
?>