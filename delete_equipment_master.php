<?php
include_once 'db_connection.php';
$page_title = "機器マスター削除";
include_once 'header.php';

try {
    $conn = getDBConnection();
    $equipment_id = $_GET['equipment_id'] ?? '';
    if (empty($equipment_id)) throw new Exception('機器IDが指定されていません');

    $stmt = $conn->prepare("SELECT * FROM equipment_master WHERE equipment_id = ?");
    $stmt->execute([$equipment_id]);
    $item = $stmt->fetch();
    if (!$item) throw new Exception('機器が見つかりません');
?>
<p>以下の機器マスターを削除してもよろしいですか？</p>
<p>機器名: <?php echo htmlspecialchars($item['equipment_name']); ?></p>
<p>タイプ: <?php echo htmlspecialchars($item['equipment_type']); ?></p>
<p>価格: <?php echo number_format($item['price'], 2); ?> 円</p>
<form method="POST" action="process_delete_equipment_master.php">
    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
    <input type="hidden" name="equipment_id" value="<?php echo htmlspecialchars($item['equipment_id']); ?>">
    <input type="submit" value="削除">
    <a href="equipment_master_list.php">キャンセル</a>
</form>
</body></html>
<?php
} catch (Exception $e) {
    echo '<p class="error">' . htmlspecialchars($e->getMessage()) . '</p>';
}
?>