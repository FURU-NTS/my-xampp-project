<?php
include_once 'db_connection.php';
$page_title = "リース機器編集";
include_once 'header.php';

try {
    $conn = getDBConnection();
    $leased_equipment_id = $_GET['leased_equipment_id'] ?? '';
    if (empty($leased_equipment_id)) throw new Exception('リース機器IDが指定されていません');

    $stmt = $conn->prepare("SELECT * FROM leased_equipment WHERE leased_equipment_id = ?");
    $stmt->execute([$leased_equipment_id]);
    $item = $stmt->fetch();
    if (!$item) throw new Exception('リース機器が見つかりません');

    $equipment_stmt = $conn->query("SELECT equipment_id, equipment_name FROM equipment_master");
    $equipment = $equipment_stmt->fetchAll();
    $contracts_stmt = $conn->query("SELECT contract_id, start_date FROM lease_contracts");
    $contracts = $contracts_stmt->fetchAll();
?>
<form method="POST" action="process_edit_leased_equipment.php">
    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
    <input type="hidden" name="leased_equipment_id" value="<?php echo htmlspecialchars($item['leased_equipment_id']); ?>">
    <div class="form-group">
        <label for="equipment_id" class="required">機器:</label>
        <select id="equipment_id" name="equipment_id" required>
            <option value="">選択してください</option>
            <?php foreach ($equipment as $eq) {
                $selected = $eq['equipment_id'] == $item['equipment_id'] ? 'selected' : '';
                echo "<option value='" . htmlspecialchars($eq['equipment_id']) . "' $selected>" . htmlspecialchars($eq['equipment_name']) . "</option>";
            } ?>
        </select>
    </div>
    <div class="form-group">
        <label for="contract_id" class="required">契約:</label>
        <select id="contract_id" name="contract_id" required>
            <option value="">選択してください</option>
            <?php foreach ($contracts as $contract) {
                $selected = $contract['contract_id'] == $item['contract_id'] ? 'selected' : '';
                echo "<option value='" . htmlspecialchars($contract['contract_id']) . "' $selected>" . htmlspecialchars($contract['start_date']) . "</option>";
            } ?>
        </select>
    </div>
    <div class="form-group">
        <label for="installation_date">設置日:</label>
        <input type="date" id="installation_date" name="installation_date" value="<?php echo htmlspecialchars($item['installation_date']); ?>">
    </div>
    <div class="form-group">
        <label for="last_maintenance_date">最終保守日:</label>
        <input type="date" id="last_maintenance_date" name="last_maintenance_date" value="<?php echo htmlspecialchars($item['last_maintenance_date']); ?>">
    </div>
    <input type="submit" value="更新">
</form>
</body></html>
<?php
} catch (Exception $e) {
    echo '<p class="error">' . htmlspecialchars($e->getMessage()) . '</p>';
}
?>