<?php
include_once 'db_connection.php';
$page_title = "保守受付一覧";
include_once 'header.php';

try {
    $conn = getDBConnection();
    $stmt = $conn->query("SELECT mr.*, o.customer_name FROM maintenance_requests mr LEFT JOIN orders o ON mr.order_id = o.id");
    $items = $stmt->fetchAll();

    if (isset($_GET['status']) && isset($_GET['message'])) {
        $class = $_GET['status'] === 'success' ? 'success' : 'error';
        echo "<p class='$class'>" . htmlspecialchars($_GET['message']) . "</p>";
    }
?>
<table>
    <tr>
        <th>ID</th><th>受注顧客名</th><th>ステータス</th><th>受付日</th><th>アクション</th>
    </tr>
    <?php
    if ($items) {
        foreach ($items as $row) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['request_id']) . "</td>";
            echo "<td>" . htmlspecialchars($row['customer_name'] ?? '未指定') . "</td>";
            echo "<td>" . htmlspecialchars($row['status']) . "</td>";
            echo "<td>" . htmlspecialchars($row['request_date']) . "</td>";
            echo "<td><a href='edit_maintenance_requests.php?request_id=" . htmlspecialchars($row['request_id']) . "'>編集</a> | <a href='delete_maintenance_requests.php?request_id=" . htmlspecialchars($row['request_id']) . "'>削除</a></td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='5'>データが見つかりません。</td></tr>";
    }
    ?>
</table>
<a href="add_maintenance_requests.php">保守受付追加</a>
</body></html>
<?php
} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    echo "<p class='error'>エラーが発生しました。</p>";
}
?>