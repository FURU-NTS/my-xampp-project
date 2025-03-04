<?php
include_once 'db_connection.php';
$page_title = "保守記録編集";
include_once 'header.php';

try {
    $conn = getDBConnection();
    $maintenance_id = $_GET['maintenance_id'] ?? '';
    if (empty($maintenance_id)) throw new Exception('保守記録IDが指定されていません');

    $stmt = $conn->prepare("SELECT * FROM maintenance_records WHERE maintenance_id = ?");
    $stmt->execute([$maintenance_id]);
    $item = $stmt->fetch();
    if (!$item) throw new Exception('保守記録が見つかりません');

    $equipment_stmt = $conn->query("SELECT le.leased_equipment_id, le.serial_number, em.equipment_name 
                                    FROM leased_equipment le 
                                    LEFT JOIN equipment_master em ON le.equipment_id = em.equipment_id");
    $equipment = $equipment_stmt->fetchAll();
?>
<form method="POST" action="process_edit_maintenance_records.php">
    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
    <input type="hidden" name="maintenance_id" value="<?php echo htmlspecialchars($item['maintenance_id']); ?>">
    <div class="form-group">
        <label for="lease_device_id" class="required">リース機器:</label>
        <select id="lease_device_id" name="lease_device_id" required>
            <option value="">選択してください</option>
            <?php foreach ($equipment as $eq) {
                $selected = $eq['leased_equipment_id'] == $item['lease_device_id'] ? 'selected' : '';
                echo "<option value='" . htmlspecialchars($eq['leased_equipment_id']) . "' $selected>" . htmlspecialchars($eq['equipment_name'] . " - " . $eq['serial_number']) . "</option>";
            } ?>
        </select>
    </div>
    <div class="form-group">
        <label for="maintenance_date" class="required">保守日:</label>
        <input type="date" id="maintenance_date" name="maintenance_date" value="<?php echo htmlspecialchars($item['maintenance_date']); ?>" required>
    </div>
    <div class="form-group">
        <label for="maintenance_type" class="required">保守タイプ:</label>
        <select id="maintenance_type" name="maintenance_type" required>
            <option value="">選択してください</option>
            <option value="regular" <?php echo $item['maintenance_type'] === 'regular' ? 'selected' : ''; ?>>定期</option>
            <option value="emergency" <?php echo $item['maintenance_type'] === 'emergency' ? 'selected' : ''; ?>>緊急</option>
            <option value="installation" <?php echo $item['maintenance_type'] === 'installation' ? 'selected' : ''; ?>>設置</option>
            <option value="removal" <?php echo $item['maintenance_type'] === 'removal' ? 'selected' : ''; ?>>撤去</option>
        </select>
    </div>
    <div class="form-group">
        <label for="technician_name">技術者名:</label>
        <input type="text" id="technician_name" name="technician_name" value="<?php echo htmlspecialchars($item['technician_name']); ?>">
    </div>
    <div class="form-group">
        <label for="maintenance_details">詳細:</label>
        <textarea id="maintenance_details" name="maintenance_details"><?php echo htmlspecialchars($item['maintenance_details']); ?></textarea>
    </div>
    <div class="form-group">
        <label for="next_maintenance_date">次回保守日:</label>
        <input type="date" id="next_maintenance_date" name="next_maintenance_date" value="<?php echo htmlspecialchars($item['next_maintenance_date']); ?>">
    </div>
    <input type="submit" value="更新">
</form>
</body></html>
<?php
} catch (Exception $e) {
    echo '<p class="error">' . htmlspecialchars($e->getMessage()) . '</p>';
}
?>