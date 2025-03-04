<?php
include_once 'db_connection.php';
session_start();

try {
    $conn = getDBConnection();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("不正なリクエストです。");
    }

    // CSRFトークン検証
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        header("Location: edit_installation_tasks.php?task_id=" . urlencode($_POST['task_id']) . "&status=error&message=" . urlencode("CSRFトークンが無効です"));
        exit;
    }

    if (!isset($_POST['task_id']) || empty($_POST['task_id'])) {
        throw new Exception("タスクIDが指定されていません。");
    }

    $task_id = $_POST['task_id'];
    $project_id = $_POST['project_id'];
    $task_name = $_POST['task_name'];
    $status = $_POST['status'];
    $start_date = $_POST['start_date'] ?: null;
    $end_date = $_POST['end_date'] ?: null;
    $employee_id_1 = $_POST['employee_id_1'] ?: null;
    $employee_id_2 = $_POST['employee_id_2'] ?: null;
    $memo = $_POST['memo'] ?? null;

    $sql = "UPDATE installation_tasks 
            SET project_id = ?, task_name = ?, status = ?, start_date = ?, end_date = ?, 
                employee_id_1 = ?, employee_id_2 = ?, memo = ?
            WHERE task_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$project_id, $task_name, $status, $start_date, $end_date, $employee_id_1, $employee_id_2, $memo, $task_id]);

    header("Location: installation_tasks_list.php?status=success&message=工事タスクが更新されました");
    exit;
} catch (Exception $e) {
    error_log("Error in process_edit_installation_tasks.php: " . $e->getMessage());
    header("Location: edit_installation_tasks.php?task_id=" . urlencode($_POST['task_id']) . "&status=error&message=" . urlencode($e->getMessage()));
    exit;
}
?>