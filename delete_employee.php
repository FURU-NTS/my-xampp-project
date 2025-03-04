<?php
include_once 'db_connection.php';
$page_title = "担当者削除";
include_once 'header.php';

try {
    $conn = getDBConnection();
    $employee_id = $_GET['employee_id'] ?? '';
    if (empty($employee_id)) throw new Exception('社員IDが指定されていません');

    $stmt = $conn->prepare("SELECT * FROM employees WHERE employee_id = ?");
    $stmt->execute([$employee_id]);
    $employee = $stmt->fetch();
    if (!$employee) throw new Exception('担当者が見つかりません');
?>

<p>以下の担当者を削除してもよろしいですか？</p>
<p>担当者名: <?php echo htmlspecialchars($employee['full_name']); ?></p>
<form method="POST" action="process_delete_employee.php">
    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
    <input type="hidden" name="employee_id" value="<?php echo htmlspecialchars($employee['employee_id']); ?>">
    <input type="submit" value="削除">
    <a href="employee_list.php">キャンセル</a>
</form>
<?php
} catch (Exception $e) {
    echo '<p class="error">' . htmlspecialchars($e->getMessage()) . '</p>';
}
?>
</body>
</html>