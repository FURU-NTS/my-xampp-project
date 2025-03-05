<?php
include_once 'db_connection.php';
include_once 'config.php';
ob_start();

try {
    $conn = getDBConnection();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception('無効なリクエストメソッドです');
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) throw new Exception('CSRFトークンが無効です');

    $project_id = $_POST['project_id'] ?? '';
    $new_schedule_date = $_POST['new_schedule_date'] ?? '';
    $status = $_POST['status'] ?? '';
    $memo = $_POST['memo'] ?? '';

    $missing_fields = [];
    if (empty($project_id)) $missing_fields[] = 'project_id';
    if (empty($new_schedule_date)) $missing_fields[] = 'new_schedule_date';
    if (empty($status)) $missing_fields[] = 'status';

    if (!empty($missing_fields)) {
        throw new Exception('必須項目が不足しています: ' . implode(', ', $missing_fields));
    }

    // completedを除外
    if (!in_array($status, ['planning', 'in_progress'])) {
        throw new Exception('無効なステータス値です');
    }

    // 元のorder_idを取得
    $stmt = $conn->prepare("SELECT order_id FROM installation_projects WHERE project_id = ?");
    $stmt->execute([$project_id]);
    $current_project = $stmt->fetch(PDO::FETCH_ASSOC);
    $order_id = $current_project['order_id'];

    $conn->beginTransaction();

    $stmt = $conn->prepare("UPDATE installation_projects SET order_id = ?, new_schedule_date = ?, status = ?, memo = ? WHERE project_id = ?");
    $stmt->execute([$order_id, $new_schedule_date, $status, $memo, $project_id]);

    $conn->commit();
    ob_end_clean();
    echo "<script>window.location.href='installation_projects_list.php?status=success&message=" . urlencode('プロジェクトが更新されました') . "';</script>";
    exit;

} catch (Exception $e) {
    if ($conn->inTransaction()) $conn->rollBack();
    error_log("Error in process_edit_installation_projects.php: " . $e->getMessage());
    ob_end_clean();
    echo "<script>window.location.href='edit_installation_projects.php?project_id=" . urlencode($project_id) . "&status=error&message=" . urlencode($e->getMessage()) . "';</script>";
    exit;
}
?>