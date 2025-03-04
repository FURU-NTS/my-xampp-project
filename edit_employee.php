<?php
include_once 'db_connection.php';
$page_title = "担当者編集"; // 名称を「従業員編集」から「担当者編集」に変更
include_once 'header.php';

try {
    $conn = getDBConnection();
    $employee_id = $_GET['employee_id'] ?? '';
    if (empty($employee_id)) throw new Exception('担当者IDが指定されていません');

    $stmt = $conn->prepare("SELECT * FROM employees WHERE employee_id = ?");
    $stmt->execute([$employee_id]);
    $item = $stmt->fetch();
    if (!$item) throw new Exception('担当者が見つかりません');
?>
<style>
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; font-weight: bold; margin-bottom: 5px; }
    .form-group input, .form-group select { width: 100%; max-width: 300px; padding: 5px; box-sizing: border-box; }
    .form-group input[readonly] { background-color: #f0f0f0; border: 1px solid #ccc; }
</style>
<form method="POST" action="process_edit_employee.php">
    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
    <input type="hidden" name="employee_id" value="<?php echo htmlspecialchars($item['employee_id']); ?>">
    <div class="form-group">
        <label for="employee_id">ID:</label>
        <input type="text" id="employee_id" value="<?php echo htmlspecialchars($item['employee_id']); ?>" readonly>
    </div>
    <div class="form-group">
        <label for="full_name" class="required">氏名:</label>
        <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($item['full_name']); ?>" required onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="email">メールアドレス:</label> <!-- 「メール」から変更 -->
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($item['email']); ?>" onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="phone_number">電話番号:</label>
        <input type="text" id="phone_number" name="phone_number" value="<?php echo htmlspecialchars($item['phone_number']); ?>" onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="department">部門:</label> <!-- 「部署」から変更 -->
        <input type="text" id="department" name="department" value="<?php echo htmlspecialchars($item['department']); ?>" onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="position">職位:</label> <!-- 「役職」から変更 -->
        <input type="text" id="position" name="position" value="<?php echo htmlspecialchars($item['position']); ?>" onkeydown="preventEnterSubmit(event)">
    </div>
    <div class="form-group">
        <label for="created_at">作成日:</label>
        <input type="text" id="created_at" value="<?php echo htmlspecialchars($item['created_at']); ?>" readonly>
    </div>
    <div class="form-group">
        <label for="updated_at">更新日:</label>
        <input type="text" id="updated_at" value="<?php echo htmlspecialchars($item['updated_at']); ?>" readonly>
    </div>
    <div style="margin-top: 10px;">
        <input type="submit" value="更新" style="margin-right: 10px;">
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