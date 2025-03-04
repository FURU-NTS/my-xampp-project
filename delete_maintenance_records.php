<?php
include_once 'db_connection.php';
$page_title = "保守記録削除";
include_once 'header.php';

try {
    $conn = getDBConnection();
    $maintenance_id = $_GET['maintenance_id'] ?? '';
    if (empty($maintenance_id)) throw new Exception('保守記録IDが指定されていません');

    $stmt = $conn->prepare("SELECT mr.*, le.serial_number, em.equipment_name 
                            FROM maintenance_records mr 
                            LEFT JOIN leased_equipment le ON mr.lease_device_id = le.leased_equipment_id 
                            LEFT JOIN equipment_master em ON le.equipment_id = em.equipment_id 
                            WHERE mr.maintenance_id = ?");
    $stmt->execute([$maintenance_id]);
    $item = $stmt->fetch();
    if (!$item) throw new Exception('保守記録が見つかりません');
?>
<p>以下の保守記録を削除してもよろしいですか？</p>
<p>機器名: <?php echo htmlspecialchars($item['equipment_name']); ?></p>
<p>シリアル番号: <?php echo htmlspecialchars($item['serial_number']); ?></p>
<p>保守日: <?php echo htmlspecialchars($item['maintenance_date']); ?></p>
<p>保守タイプ: <?php echo htmlspecialchars($item['maintenance_type']); ?></p>
<form method="POST" action="process_delete_maintenance_records.php">
    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
    <input type="hidden" name="maintenance_id" value="<?php echo htmlspecialchars($item['maintenance_id']); ?>">
    <input type="submit" value="削除">
    <a href="maintenance_records_list.php">キャンセル</a>
</form>
</body></html>
<?php
} catch (Exception $e) {
    echo '<p class="error">' . htmlspecialchars($e->getMessage()) . '</p>';
}
?>