<?php
include_once 'db_connection.php';

try {
    $conn = getDBConnection();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('無効なリクエストです');
    }

    $task_id = $_POST['task_id'] ?? '';
    $new_status = $_POST['status'] ?? '';

    if (empty($task_id) || empty($new_status)) {
        throw new Exception('必須項目が入力されていません');
    }

    // 現在のタスク情報を取得
    $stmt = $conn->prepare("SELECT status, start_date, end_date FROM installation_tasks WHERE task_id = ?");
    $stmt->execute([$task_id]);
    $task = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$task) {
        throw new Exception('タスクが見つかりません');
    }

    $current_date = '2025-03-03'; // 固定日付（実運用では date('Y-m-d') に変更）

    // 開始日と終了日の更新条件
    $start_date = $task['start_date'];
    $end_date = $task['end_date'];

    if ($new_status === 'in_progress' && $task['status'] === 'not_started' && !$start_date) {
        $start_date = $current_date; // 進行中に変更時、開始日を設定
    }
    if ($new_status === 'completed' && ($task['status'] === 'not_started' || $task['status'] === 'in_progress') && !$end_date) {
        $end_date = $current_date; // 完了に変更時、終了日を設定
        if (!$start_date) {
            $start_date = $current_date; // 未開始から完了への場合、開始日も設定
        }
    }

    // タスクを更新
    $stmt = $conn->prepare("UPDATE installation_tasks 
                            SET status = ?, start_date = ?, end_date = ?
                            WHERE task_id = ?");
    $stmt->execute([$new_status, $start_date, $end_date, $task_id]);

    header("Location: installation_tasks_list.php?status=success&message=ステータスが更新されました");
    exit;
} catch (Exception $e) {
    header("Location: installation_tasks_list.php?status=error&message=" . urlencode($e->getMessage()));
    exit;
}
?>