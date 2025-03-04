<?php
include_once 'db_connection.php';

try {
    $conn = getDBConnection();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('無効なリクエストです');
    }

    $project_id = $_POST['project_id'] ?? '';
    $task_names = $_POST['task_names'] ?? [];
    $employee_id_1 = $_POST['employee_id_1'] ?? NULL;
    $employee_id_2 = $_POST['employee_id_2'] ?? NULL;
    $memo = $_POST['memo'] ?? '';

    if (empty($project_id) || empty($task_names)) {
        throw new Exception('必須項目が入力されていません');
    }

    // project_id の存在確認
    $stmt = $conn->prepare("SELECT project_id FROM installation_projects WHERE project_id = ?");
    $stmt->execute([$project_id]);
    if (!$stmt->fetch()) {
        throw new Exception('指定されたプロジェクトが見つかりません');
    }

    // 複数タスクを1つずつ登録（ステータスはデフォルトで 'not_started'）
    $stmt = $conn->prepare("INSERT INTO installation_tasks (project_id, task_name, employee_id_1, employee_id_2, memo) 
                            VALUES (?, ?, ?, ?, ?)");
    foreach ($task_names as $task_name) {
        $stmt->execute([$project_id, $task_name, $employee_id_1 ?: NULL, $employee_id_2 ?: NULL, $memo]);
    }

    header("Location: installation_tasks_list.php?status=success&message=工事タスクが追加されました");
    exit;
} catch (Exception $e) {
    header("Location: installation_tasks_list.php?status=error&message=" . urlencode($e->getMessage()));
    exit;
}
?>