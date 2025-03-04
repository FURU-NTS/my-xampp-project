<?php
include_once 'db_connection.php';
$page_title = "機器マスター編集";
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
<form method="POST" action="process_edit_equipment_master.php" onsubmit="return validateForm()">
    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
    <input type="hidden" name="equipment_id" value="<?php echo htmlspecialchars($item['equipment_id']); ?>">
    <div class="form-group">
        <label for="equipment_name" class="required">機器名:</label>
        <input type="text" id="equipment_name" name="equipment_name" value="<?php echo htmlspecialchars($item['equipment_name']); ?>" required onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="equipment_type" class="required">タイプ:</label>
        <input type="text" id="equipment_type" name="equipment_type" value="<?php echo htmlspecialchars($item['equipment_type']); ?>" required onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="manufacturer">メーカー:</label>
        <input type="text" id="manufacturer" name="manufacturer" value="<?php echo htmlspecialchars($item['manufacturer'] ?? ''); ?>" onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="model_number">モデル番号:</label>
        <input type="text" id="model_number" name="model_number" value="<?php echo htmlspecialchars($item['model_number'] ?? ''); ?>" onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="price" class="required">価格 (円):</label>
        <input type="number" id="price" name="price" step="1" min="0" value="<?php echo number_format((float)$item['price'], 0, '', ''); ?>" required onkeydown="preventEnterSubmit(event)">
    </div>
    <div style="margin-top: 10px;">
        <input type="submit" value="更新" style="margin-right: 10px;">
        <input type="button" value="キャンセル" onclick="window.location.href='equipment_master_list.php';">
    </div>
</form>

<script>
function preventEnterSubmit(event) {
    if (event.key === 'Enter') {
        event.preventDefault();
    }
}

function validateForm() {
    // 必要に応じて追加のバリデーションをここに
    return true;
}
</script>
</body></html>
<?php
} catch (Exception $e) {
    echo '<p class="error">' . htmlspecialchars($e->getMessage()) . '</p>';
}
?>