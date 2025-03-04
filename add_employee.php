<?php
include_once 'db_connection.php';
$page_title = "従業員追加";
include_once 'header.php';

try {
    $conn = getDBConnection();
?>
<style>
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; font-weight: bold; margin-bottom: 5px; }
    .form-group input, .form-group select { width: 100%; max-width: 300px; padding: 5px; box-sizing: border-box; }
</style>
<form method="POST" action="process_add_employee.php">
    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
    <div class="form-group">
        <label for="full_name" class="required">氏名:</label>
        <input type="text" id="full_name" name="full_name" required onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="email">メール:</label>
        <input type="email" id="email" name="email" onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="phone_number">電話番号:</label>
        <input type="text" id="phone_number" name="phone_number" onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="position">役職:</label>
        <input type="text" id="position" name="position" onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="department">部署:</label>
        <input type="text" id="department" name="department" onkeydown="preventEnterSubmit(event)">
    </div>
    <div style="margin-top: 10px;">
        <input type="submit" value="追加" style="margin-right: 10px;">
        <input type="button" value="キャンセル" onclick="window.location.href='employee_list.php';" style="background-color: #ccc; border: none; padding: 5px 10px; cursor: pointer;">
    </div>
</form>

<script>
function preventEnterSubmit(event) {
    if (event.key === 'Enter') {
        event.preventDefault();
    }
}
</script>
</body>
</html>
<?php
} catch (Exception $e) {
    echo '<p class="error">' . htmlspecialchars($e->getMessage()) . '</p>';
}
?>