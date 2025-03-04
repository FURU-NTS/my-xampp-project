<?php
include_once 'db_connection.php';
$page_title = "担当者MASTER";
include_once 'header.php';

try {
    $conn = getDBConnection();
    $stmt = $conn->query("SELECT * FROM employees");
    $employees = $stmt->fetchAll();

    if (isset($_GET['status']) && isset($_GET['message'])) {
        $class = $_GET['status'] === 'success' ? 'success' : 'error';
        echo "<p class='$class'>" . htmlspecialchars($_GET['message']) . "</p>";
    }
?>
<style>
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
    .success { color: green; }
    .error { color: red; }
    .table-header { margin-bottom: 10px; }
</style>

<!-- 左上に「担当者追加」ボタンを追加 -->
<div class="table-header">
    <a href="add_employee.php">担当者追加</a>
</div>

<table>
    <tr>
        <th>ID</th>
        <th>氏名</th>
        <th>メールアドレス</th>
        <th>電話番号</th>
        <th>部門</th>
        <th>職位</th>
        <th>作成日</th>
        <th>更新日</th>
        <th>アクション</th>
    </tr>
    <?php
    if ($employees) {
        foreach ($employees as $row) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['employee_id']) . "</td>";
            echo "<td>" . htmlspecialchars($row['full_name']) . "</td>";
            echo "<td>" . htmlspecialchars($row['email']) . "</td>";
            echo "<td>" . htmlspecialchars($row['phone_number']) . "</td>";
            echo "<td>" . htmlspecialchars($row['department']) . "</td>";
            echo "<td>" . htmlspecialchars($row['position']) . "</td>";
            echo "<td>" . htmlspecialchars($row['created_at']) . "</td>";
            echo "<td>" . htmlspecialchars($row['updated_at']) . "</td>";
            echo "<td><a href='edit_employee.php?employee_id=" . htmlspecialchars($row['employee_id']) . "'>編集</a> | <a href='delete_employee.php?employee_id=" . htmlspecialchars($row['employee_id']) . "'>削除</a></td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='9'>担当者情報が見つかりません。</td></tr>";
    }
    ?>
</table>
<a href="add_employee.php">担当者追加</a>
</body>
</html>
<?php
} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    echo "<p class='error'>エラーが発生しました。</p>";
}
?>