<?php
include_once 'db_connection.php';
$page_title = "リース機器一覧";
include_once 'header.php';

try {
    $conn = getDBConnection();
    $stmt = $conn->query("SELECT le.*, em.equipment_name, lc.start_date 
                          FROM leased_equipment le 
                          LEFT JOIN equipment_master em ON le.equipment_id = em.equipment_id 
                          LEFT JOIN lease_contracts lc ON le.contract_id = lc.contract_id");
    $items = $stmt->fetchAll();

    if (isset($_GET['status']) && isset($_GET['message'])) {
        $class = $_GET['status'] === 'success' ? 'success' : 'error';
        echo "<p class='$class'>" . htmlspecialchars($_GET['message']) . "</p>";
    }
?>
<table>
    <tr>
        <th>ID</th><th>機器名</th><th>契約開始日</th><th>シリアル番号</th><th>設置日</th><th>最終保守日</th><th>アクション</th>
    </tr>
    <?php
    if ($items) {
        foreach ($items as $row) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['leased_equipment_id']) . "</td>";
            echo "<td>" . htmlspecialchars($row['equipment_name']) . "</td>";
            echo "<td>" . htmlspecialchars($row['start_date']) . "</td>";
            echo "<td>" . htmlspecialchars($row['serial_number']) . "</td>";
            echo "<td>" . htmlspecialchars($row['installation_date']) . "</td>";
            echo "<td>" . htmlspecialchars($row['last_maintenance_date']) . "</td>";
            echo "<td><a href='edit_leased_equipment.php?leased_equipment_id=" . htmlspecialchars($row['leased_equipment_id']) . "'>編集</a> | <a href='delete_leased_equipment.php?leased_equipment_id=" . htmlspecialchars($row['leased_equipment_id']) . "'>削除</a></td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='7'>データが見つかりません。</td></tr>";
    }
    ?>
</table>
<a href="add_leased_equipment.php">リース機器追加</a>
</body></html>
<?php
} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    echo "<p class='error'>エラーが発生しました。</p>";
}
?>