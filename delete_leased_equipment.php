<?php
include_once 'db_connection.php';
$page_title = "リース機器削除";
include_once 'header.php';

try {
    $conn = getDBConnection();
    $leased_equipment_id = $_GET['leased_equipment_id'] ?? '';
    if (empty($leased_equipment_id)) throw new Exception('リース機器IDが指定されていません');

    $stmt = $conn->prepare("SELECT le.*, em.equipment_name, lc.start_date 
                            FROM leased_equipment le 
                            LEFT JOIN equipment_master em ON le.equipment_id = em.equipment_id 
                            LEFT JOIN lease_contracts lc ON le.contract_id = lc.contract_id 
                            WHERE le.leased_equipment_id = ?");
    $stmt->execute([$leased_equipment_id]);
    $item = $stmt->fetch();
    if (!$item) throw new Exception('リース機器が見つかりません');
?>
<p>以下のリース機器を削除してもよろしいですか？</p>
<p>機器名: <?php echo htmlspecialchars($item['equipment_name']); ?></p>
<p>契約開始日: <?php echo htmlspecialchars($item['start_date']); ?></p>
<p>シリアル番号: <?php echo htmlspecialchars($item['serial_number']); ?></p>
<form method="POST" action="process_delete_leased_equipment.php">
    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
    <input type="hidden" name="leased_equipment_id" value="<?php echo htmlspecialchars($item['leased_equipment_id']); ?>">
    <input type="submit" value="削除">
    <a href="leased_equipment_list.php">キャンセル</a>
</form>
</body></html>
<?php
} catch (Exception $e) {
    echo '<p class="error">' . htmlspecialchars($e->getMessage()) . '</p>';
}
?>