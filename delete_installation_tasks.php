<?php
include_once 'db_connection.php';
$page_title = "工事タスク削除確認";
include_once 'header.php';

try {
    $conn = getDBConnection();

    if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['task_id']) && !empty($_GET['task_id'])) {
        $id = $_GET['task_id'];
        $sql = "SELECT it.task_id, it.project_id, it.task_name, it.status, it.memo, 
                       it.start_date, it.end_date, e1.full_name AS employee_1, e2.full_name AS employee_2,
                       o.customer_name, ip.new_schedule_date
                FROM installation_tasks it
                LEFT JOIN installation_projects ip ON it.project_id = ip.project_id
                LEFT JOIN orders o ON ip.order_id = o.id
                LEFT JOIN employees e1 ON it.employee_id_1 = e1.employee_id
                LEFT JOIN employees e2 ON it.employee_id_2 = e2.employee_id
                WHERE it.task_id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['id' => $id]);
        $task = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($task) {
            // ステータス翻訳
            $statusTranslations = [
                'not_started' => '未開始',
                'in_progress' => '進行中',
                'completed' => '完了'
            ];
            $status_display = $statusTranslations[$task['status']] ?? $task['status'];

            if (empty($_SESSION['csrf_token'])) {
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            }
            $csrf_token = $_SESSION['csrf_token'];
            ?>
            <!DOCTYPE html>
            <html>
            <head>
                <title>工事タスク削除確認</title>
                <style>
                    table { border-collapse: collapse; width: 80%; margin: 20px auto; }
                    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                    th { background-color: #f2f2f2; width: 120px; }
                    td { width: auto; }
                </style>
            </head>
            <body>
                <h2>以下の工事タスクを削除しますか？</h2>
                <table>
                    <tr><th>ID</th><td><?php echo htmlspecialchars($task['task_id']); ?></td></tr>
                    <tr><th>プロジェクト</th><td><?php echo htmlspecialchars($task['customer_name'] . ' - ' . $task['new_schedule_date']); ?></td></tr>
                    <tr><th>タスク名</th><td><?php echo htmlspecialchars($task['task_name']); ?></td></tr>
                    <tr><th>ステータス</th><td><?php echo htmlspecialchars($status_display); ?></td></tr>
                    <tr><th>メモ</th><td><?php echo htmlspecialchars($task['memo'] ?? 'なし'); ?></td></tr>
                    <tr><th>開始日</th><td><?php echo htmlspecialchars($task['start_date'] ?? '未定'); ?></td></tr>
                    <tr><th>終了日</th><td><?php echo htmlspecialchars($task['end_date'] ?? '未定'); ?></td></tr>
                    <tr><th>担当者1</th><td><?php echo htmlspecialchars($task['employee_1'] ?? 'なし'); ?></td></tr>
                    <tr><th>担当者2</th><td><?php echo htmlspecialchars($task['employee_2'] ?? 'なし'); ?></td></tr>
                </table>
                <form method="POST" action="process_delete_installation_tasks.php">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                    <input type="hidden" name="task_id" value="<?php echo $task['task_id']; ?>">
                    <input type="submit" value="削除する">
                    <a href="installation_tasks_list.php">キャンセル</a>
                </form>
            </body>
            </html>
            <?php
        } else {
            echo "タスクが見つかりません（ID: " . htmlspecialchars($id) . "）。";
            echo '<br><a href="installation_tasks_list.php">一覧に戻る</a>';
        }
    } else {
        echo "削除するタスクが指定されていません。";
        echo '<br><a href="installation_tasks_list.php">一覧に戻る</a>';
    }
} catch (PDOException $e) {
    echo "エラー: " . $e->getMessage();
}
?>