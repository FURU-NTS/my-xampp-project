<?php
include_once 'db_connection.php';
$page_title = "保守記録一覧";
include_once 'header.php';

try {
    $conn = getDBConnection();
    $stmt = $conn->query("SELECT mr.*, le.serial_number, em.equipment_name 
                          FROM maintenance_records mr 
                          LEFT JOIN leased_equipment le ON mr.lease_device_id = le.leased_equipment_id 
                          LEFT JOIN equipment_master em ON le.equipment_id = em.equipment_id");
    $items = $stmt->fetchAll();

    if (isset($_GET['status']) && isset($_GET['message'])) {
        $class = $_GET['status'] === 'success' ? 'success' : 'error';
        echo "<p class='$class'>" . htmlspecialchars($_GET['message']) . "</p>";
    }
?>
<table>
    <tr>
        <th>ID</th><th>機器名</th><th>シリアル番号</th><th>保守日</th><th>保守タイプ</th><th>技術者</th><th>次回保守日</th><th>アクション</th>
    </tr>
    <?php
    if ($items) {
        foreach ($items as $row) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['maintenance_id']) . "</td>";
            echo "<td>" . htmlspecialchars($row['equipment_name']) . "</td>";
            echo "<td>" . htmlspecialchars($row['serial_number']) . "</td>";
            echo "<td>" . htmlspecialchars($row['maintenance_date']) . "</td>";
            echo "<td>" . htmlspecialchars($row['maintenance_type']) . "</td>";
            echo "<td>" . htmlspecialchars($row['technician_name']) . "</td>";
            echo "<td>" . htmlspecialchars($row['next_maintenance_date']) . "</td>";
            echo "<td><a href='edit_maintenance_records.php?maintenance_id=" . htmlspecialchars($row['maintenance_id']) . "'>編集</a> | <a href='delete_maintenance_records.php?maintenance_id=" . htmlspecialchars($row['maintenance_id']) . "'>削除</a></td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='8'>データが見つかりません。</td></tr>";
    }
    ?>
</table>
<a href="add_maintenance_records.php">保守記録追加</a>
</body></html>
<?php
} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    echo "<p class='error'>エラーが発生しました。</p>";
}
?>