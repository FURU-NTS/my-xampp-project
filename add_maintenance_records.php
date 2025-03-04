<?php
include_once 'db_connection.php';
$page_title = "保守記録追加";
include_once 'header.php';

try {
    $conn = getDBConnection();
    $equipment_stmt = $conn->query("SELECT le.leased_equipment_id, le.serial_number, em.equipment_name 
                                    FROM leased_equipment le 
                                    LEFT JOIN equipment_master em ON le.equipment_id = em.equipment_id");
    $equipment = $equipment_stmt->fetchAll();
?>
<form method="POST" action="process_add_maintenance_records.php">
    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
    <div class="form-group">
        <label for="lease_device_id" class="required">リース機器:</label>
        <select id="lease_device_id" name="lease_device_id" required>
            <option value="">選択してください</option>
            <?php foreach ($equipment as $item) {
                echo "<option value='" . htmlspecialchars($item['leased_equipment_id']) . "'>" . htmlspecialchars($item['equipment_name'] . " - " . $item['serial_number']) . "</option>";
            } ?>
        </select>
    </div>
    <div class="form-group">
        <label for="maintenance_date" class="required">保守日:</label>
        <input type="date" id="maintenance_date" name="maintenance_date" required>
    </div>
    <div class="form-group">
        <label for="maintenance_type" class="required">保守タイプ:</label>
        <select id="maintenance_type" name="maintenance_type" required>
            <option value="">選択してください</option>
            <option value="regular">定期</option>
            <option value="emergency">緊急</option>
            <option value="installation">設置</option>
            <option value="removal">撤去</option>
        </select>
    </div>
    <div class="form-group">
        <label for="technician_name">技術者名:</label>
        <input type="text" id="technician_name" name="technician_name">
    </div>
    <div class="form-group">
        <label for="maintenance_details">詳細:</label>
        <textarea id="maintenance_details" name="maintenance_details"></textarea>
    </div>
    <div class="form-group">
        <label for="next_maintenance_date">次回保守日:</label>
        <input type="date" id="next_maintenance_date" name="next_maintenance_date">
    </div>
    <input type="submit" value="追加">
</form>
</body></html>
<?php
} catch (Exception $e) {
    echo '<p class="error">' . htmlspecialchars($e->getMessage()) . '</p>';
}
?>