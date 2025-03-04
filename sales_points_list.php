<?php
include_once 'db_connection.php';
$page_title = "ポイント一覧";
include_once 'header.php';

try {
    $conn = getDBConnection();
    $stmt = $conn->query("SELECT sp.*, o.customer_name, e.full_name 
                          FROM sales_points sp 
                          LEFT JOIN orders o ON sp.order_id = o.id 
                          LEFT JOIN employees e ON sp.sales_rep_id = e.employee_id");
    $items = $stmt->fetchAll();

    if (isset($_GET['status']) && isset($_GET['message'])) {
        $class = $_GET['status'] === 'success' ? 'success' : 'error';
        echo "<p class='$class'>" . htmlspecialchars($_GET['message']) . "</p>";
    }
?>
<table>
    <tr>
        <th>ID</th><th>受注顧客</th><th>担当者</th><th>ポイント</th><th>追加日</th><th>アクション</th>
    </tr>
    <?php
    if ($items) {
        foreach ($items as $row) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['point_id']) . "</td>";
            echo "<td>" . htmlspecialchars($row['customer_name']) . "</td>";
            echo "<td>" . htmlspecialchars($row['full_name']) . "</td>";
            echo "<td>" . htmlspecialchars($row['points']) . "</td>";
            echo "<td>" . htmlspecialchars($row['date_added']) . "</td>";
            echo "<td><a href='edit_sales_points.php?point_id=" . htmlspecialchars($row['point_id']) . "'>編集</a> | <a href='delete_sales_points.php?point_id=" . htmlspecialchars($row['point_id']) . "'>削除</a></td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='6'>データが見つかりません。</td></tr>";
    }
    ?>
</table>
<a href="add_sales_points.php">ポイント追加</a>
</body></html>
<?php
} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    echo "<p class='error'>エラーが発生しました。</p>";
}
?>