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
        header("Location: edit_installation_projects.php?project_id=" . urlencode($_POST['project_id']) . "&status=error&message=" . urlencode("CSRFトークンが無効です"));
        exit;
    }

    if (!isset($_POST['project_id']) || empty($_POST['project_id'])) {
        throw new Exception("プロジェクトIDが指定されていません。");
    }

    $id = $_POST['project_id'];
    $new_schedule_date = $_POST['new_schedule_date'];
    $memo = $_POST['memo'];
    $status = $_POST['status'];

    // 更新SQL（編集可能項目のみ）
    $sql = "UPDATE installation_projects 
            SET new_schedule_date = :new_schedule_date, 
                memo = :memo, 
                status = :status 
            WHERE project_id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        'new_schedule_date' => $new_schedule_date,
        'memo' => $memo,
        'status' => $status,
        'id' => $id
    ]);

    header("Location: installation_projects_list.php");
    exit;
} catch (Exception $e) {
    error_log("Error in process_edit_installation_projects.php: " . $e->getMessage());
    header("Location: edit_installation_projects.php?project_id=" . urlencode($_POST['project_id']) . "&status=error&message=" . urlencode($e->getMessage()));
    exit;
}
?>