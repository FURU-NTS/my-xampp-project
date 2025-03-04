<?php
include_once 'db_connection.php';
$page_title = "工事プロジェクト編集";
include_once 'header.php';

try {
    $conn = getDBConnection();
    $project_id = $_GET['project_id'] ?? '';
    if (empty($project_id)) throw new Exception('プロジェクトIDが指定されていません');

    $stmt = $conn->prepare("SELECT * FROM installation_projects WHERE project_id = ?");
    $stmt->execute([$project_id]);
    $item = $stmt->fetch();
    if (!$item) throw new Exception('工事プロジェクトが見つかりません');

    $stmt = $conn->query("SELECT lc.contract_id, c.company_name FROM lease_contracts lc LEFT JOIN companies c ON lc.company_id = c.company_id");
    $contracts = $stmt->fetchAll();
?>
<form method="POST" action="process_edit_installation_projects.php">
    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
    <input type="hidden" name="project_id" value="<?php echo htmlspecialchars($item['project_id']); ?>">
    <div class="form-group">
        <label for="contract_id" class="required">契約:</label>
        <select id="contract_id" name="contract_id" required>
            <option value="">選択してください</option>
            <?php foreach ($contracts as $contract) {
                $selected = $contract['contract_id'] == $item['contract_id'] ? 'selected' : '';
                echo "<option value='" . htmlspecialchars($contract['contract_id']) . "' $selected>" . htmlspecialchars($contract['company_name'] . " (ID: " . $contract['contract_id'] . ")") . "</option>";
            } ?>
        </select>
    </div>
    <div class="form-group">
        <label for="status" class="required">ステータス:</label>
        <select id="status" name="status" required>
            <option value="planning" <?php echo $item['status'] === 'planning' ? 'selected' : ''; ?>>planning</option>
            <option value="in_progress" <?php echo $item['status'] === 'in_progress' ? 'selected' : ''; ?>>in_progress</option>
            <option value="completed" <?php echo $item['status'] === 'completed' ? 'selected' : ''; ?>>completed</option>
        </select>
    </div>
    <div class="form-group">
        <label for="start_date">開始日:</label>
        <input type="date" id="start_date" name="start_date" value="<?php echo htmlspecialchars($item['start_date'] ?? ''); ?>">
    </div>
    <div class="form-group">
        <label for="end_date">終了日:</label>
        <input type="date" id="end_date" name="end_date" value="<?php echo htmlspecialchars($item['end_date'] ?? ''); ?>">
    </div>
    <input type="submit" value="更新">
</form>
</body></html>
<?php
} catch (Exception $e) {
    echo '<p class="error">' . htmlspecialchars($e->getMessage()) . '</p>';
}
?>