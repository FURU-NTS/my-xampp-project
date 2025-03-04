<?php
include_once 'db_connection.php';
$page_title = "リース会社MASTER";
include_once 'header.php';

try {
    $conn = getDBConnection();
    $stmt = $conn->query("SELECT * FROM lease_providers");
    $items = $stmt->fetchAll();

    if (isset($_GET['status']) && isset($_GET['message'])) {
        $class = $_GET['status'] === 'success' ? 'success' : 'error';
        echo "<p class='$class'>" . htmlspecialchars($_GET['message']) . "</p>";
    }
?>
<table>
    <tr>
        <th>ID</th><th>会社名</th><th>登記番号</th><th>業種</th><th>住所</th><th>郵便番号</th><th>電話番号</th><th>メール</th><th>作成日</th><th>更新日</th><th>アクション</th>
    </tr>
    <?php
    if ($items) {
        foreach ($items as $row) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['provider_id']) . "</td>";
            echo "<td>" . htmlspecialchars($row['provider_name']) . "</td>";
            echo "<td>" . htmlspecialchars($row['business_registration_number']) . "</td>";
            echo "<td>" . htmlspecialchars($row['industry_type']) . "</td>";
            echo "<td>" . htmlspecialchars($row['address']) . "</td>";
            echo "<td>" . htmlspecialchars($row['postal_code']) . "</td>";
            echo "<td>" . htmlspecialchars($row['phone_number']) . "</td>";
            echo "<td>" . htmlspecialchars($row['email']) . "</td>";
            echo "<td>" . htmlspecialchars($row['created_at']) . "</td>";
            echo "<td>" . htmlspecialchars($row['updated_at']) . "</td>";
            echo "<td><a href='edit_lease_providers.php?provider_id=" . htmlspecialchars($row['provider_id']) . "'>編集</a> | <a href='delete_lease_providers.php?provider_id=" . htmlspecialchars($row['provider_id']) . "'>削除</a></td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='11'>リース会社データが見つかりません。</td></tr>";
    }
    ?>
</table>
<a href="add_lease_providers.php">リース会社追加</a>
</body></html>
<?php
} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    echo "<p class='error'>エラーが発生しました。</p>";
}
?>