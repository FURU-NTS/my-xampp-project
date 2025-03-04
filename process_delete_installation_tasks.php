<?php
include_once 'db_connection.php';
session_start();

try {
    $conn = getDBConnection();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("不正なリクエストです。");
    }

    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        header("Location: delete_installation_tasks.php?task_id=" . urlencode($_POST['task_id']) . "&status=error&message=" . urlencode("CSRFトークンが無効です"));
        exit;
    }

    if (!isset($_POST['task_id']) || empty($_POST['task_id'])) {
        throw new Exception("タスクIDが指定されていません。");
    }

    $id = $_POST['task_id'];
    $sql = "DELETE FROM installation_tasks WHERE task_id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['id' => $id]);

    header("Location: installation_tasks_list.php?status=success&message=工事タスクが削除されました");
    exit;
} catch (Exception $e) {
    error_log("Error in process_delete_installation_tasks.php: " . $e->getMessage());
    header("Location: delete_installation_tasks.php?task_id=" . urlencode($_POST['task_id']) . "&status=error&message=" . urlencode($e->getMessage()));
    exit;
}
?>