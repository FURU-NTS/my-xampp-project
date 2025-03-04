<?php
include_once 'db_connection.php';
$page_title = "工事プロジェクト削除";
include_once 'header.php';

try {
    $conn = getDBConnection();
    $project_id = $_GET['project_id'] ?? '';
    if (empty($project_id)) throw new Exception('プロジェクトIDが指定されていません');

    $stmt = $conn->prepare("SELECT ip.*, lc.company_id, c.company_name FROM installation_projects ip LEFT JOIN lease_contracts lc ON ip.contract_id = lc.contract_id LEFT JOIN companies c ON lc.company_id = c.company_id WHERE project_id = ?");
    $stmt->execute([$project_id]);
    $item = $stmt->fetch();
    if (!$item) throw new Exception('工事プロジェクトが見つかりません');
?>
<p>以下の工事プロジェクトを削除してもよろしいですか？</p>
<p>会社名: <?php echo htmlspecialchars($item['company_name'] ?? '未指定'); ?> | 契約ID: <?php echo htmlspecialchars($item['contract_id'] ?? '未指定'); ?> | ステータス: <?php echo htmlspecialchars($item['status']); ?></p>
<form method="POST" action="process_delete_installation_projects.php">
    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
    <input type="hidden" name="project_id" value="<?php echo htmlspecialchars($item['project_id']); ?>">
    <input type="submit" value="削除">
    <a href="installation_projects_list.php">キャンセル</a>
</form>
</body></html>
<?php
} catch (Exception $e) {
    echo '<p class="error">' . htmlspecialchars($e->getMessage()) . '</p>';
}
?>