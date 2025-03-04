<?php
$page_title = "機器マスター追加";
include_once 'header.php';
?>
<form method="POST" action="process_add_equipment_master.php" onsubmit="return validateForm()">
    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
    <div class="form-group">
        <label for="equipment_name" class="required">機器名:</label>
        <input type="text" id="equipment_name" name="equipment_name" required onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="equipment_type" class="required">タイプ:</label>
        <input type="text" id="equipment_type" name="equipment_type" required onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="manufacturer">メーカー:</label>
        <input type="text" id="manufacturer" name="manufacturer" onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="model_number">モデル番号:</label>
        <input type="text" id="model_number" name="model_number" onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="price" class="required">価格 (円):</label>
        <input type="number" id="price" name="price" step="1" min="0" required onkeydown="preventEnterSubmit(event)">
    </div>
    <div style="margin-top: 10px;">
        <input type="submit" value="追加" style="margin-right: 10px;">
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