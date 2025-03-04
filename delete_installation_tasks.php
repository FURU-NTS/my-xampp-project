<?php
include_once 'db_connection.php';
$page_title = "工事タスク削除";
include_once 'header.php';

try {
    $conn = getDBConnection();
    $task_id = $_GET['task_id'] ?? '';
    if (empty($task_id)) throw new Exception('タスクIDが指定されていません');

    $stmt = $conn->prepare("SELECT it.*, ip.contract_id, c.company_name FROM installation_tasks it LEFT JOIN installation_projects ip ON it.project_id = ip.project_id LEFT JOIN lease_contracts lc ON ip.contract_id = lc.contract_id LEFT JOIN companies c ON lc.company_id = c.company_id WHERE task_id = ?");
    $stmt->execute([$task_id]);
    $item = $stmt->fetch();
    if (!$item) throw new Exception('工事タスクが見つかりません');
?>
<p>以下の工事タスクを削除してもよろしいですか？</p>
<p>会社名: <?php echo htmlspecialchars($item['company_name'] ?? '未指定'); ?> | プロジェクトID: <?php echo htmlspecialchars($item['project_id'] ?? '未指定'); ?> | タスク名: <?php echo htmlspecialchars($item['task_name']); ?> | ステータス: <?php echo htmlspecialchars($item['status']); ?></p>
<form method="POST" action="process_delete_installation_tasks.php">
    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
    <input type="hidden" name="task_id" value="<?php echo htmlspecialchars($item['task_id']); ?>">
    <input type="submit" value="削除">
    <a href="installation_tasks_list.php">キャンセル</a>
</form>
</body></html>
<?php
} catch (Exception $e) {
    echo '<p class="error">' . htmlspecialchars($e->getMessage()) . '</p>';
}
?>