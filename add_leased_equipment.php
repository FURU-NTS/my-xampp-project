<?php
include_once 'db_connection.php';
$page_title = "リース機器追加";
include_once 'header.php';

try {
    $conn = getDBConnection();
    $equipment_stmt = $conn->query("SELECT equipment_id, equipment_name FROM equipment_master");
    $equipment = $equipment_stmt->fetchAll();
    $contracts_stmt = $conn->query("SELECT contract_id, start_date FROM lease_contracts");
    $contracts = $contracts_stmt->fetchAll();
?>
<form method="POST" action="process_add_leased_equipment.php">
    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
    <div class="form-group">
        <label for="equipment_id" class="required">機器:</label>
        <select id="equipment_id" name="equipment_id" required>
            <option value="">選択してください</option>
            <?php foreach ($equipment as $item) {
                echo "<option value='" . htmlspecialchars($item['equipment_id']) . "'>" . htmlspecialchars($item['equipment_name']) . "</option>";
            } ?>
        </select>
    </div>
    <div class="form-group">
        <label for="contract_id" class="required">契約:</label>
        <select id="contract_id" name="contract_id" required>
            <option value="">選択してください</option>
            <?php foreach ($contracts as $contract) {
                echo "<option value='" . htmlspecialchars($contract['contract_id']) . "'>" . htmlspecialchars($contract['start_date']) . "</option>";
            } ?>
        </select>
    </div>
    <div class="form-group">
        <label for="serial_number" class="required">シリアル番号:</label>
        <input type="text" id="serial_number" name="serial_number" required>
    </div>
    <div class="form-group">
        <label for="installation_date">設置日:</label>
        <input type="date" id="installation_date" name="installation_date">
    </div>
    <div class="form-group">
        <label for="last_maintenance_date">最終保守日:</label>
        <input type="date" id="last_maintenance_date" name="last_maintenance_date">
    </div>
    <input type="submit" value="追加">
</form>
</body></html>
<?php
} catch (Exception $e) {
    echo '<p class="error">' . htmlspecialchars($e->getMessage()) . '</p>';
}
?>