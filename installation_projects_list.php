<?php
include_once 'db_connection.php';
$page_title = "新規工事プロジェクト一覧";
include_once 'header.php';

try {
    $conn = getDBConnection();
    $stmt = $conn->query("SELECT ip.*, o.customer_name, o.negotiation_status, c.address 
                          FROM installation_projects ip 
                          LEFT JOIN orders o ON ip.order_id = o.id 
                          LEFT JOIN companies c ON o.company_id = c.company_id 
                          WHERE o.negotiation_status IN ('進行中', '与信怪しい', '書換完了')");
    $items = $stmt->fetchAll();

    // 工事ステータスの日本語変換関数
    function translateProjectStatus($status) {
        switch ($status) {
            case 'planning': return '段取り中';
            case 'in_progress': return '進行中';
            case 'completed': return '完了';
            default: return $status;
        }
    }

    // 経過日数または状態を表示する関数
    function calculateElapsedDays($new_schedule_date, $status) {
        $current_date = new DateTime('2025-03-03'); // 固定日付（実運用では new DateTime() に変更）
        $schedule_date = new DateTime($new_schedule_date);
        $interval = $schedule_date->diff($current_date);
        $days = $interval->days;

        if ($status === 'completed') {
            return '完了';
        } elseif ($schedule_date > $current_date) {
            return $days . '日後';
        } else {
            return $days . '日';
        }
    }

    if (isset($_GET['status']) && isset($_GET['message'])) {
        $class = $_GET['status'] === 'success' ? 'success' : 'error';
        echo "<p class='$class'>" . htmlspecialchars($_GET['message']) . "</p>";
    }
?>
<table>
    <tr>
        <th>ID</th><th>顧客名</th><th>住所</th><th>受注日</th><th>新規予定日</th><th>経過日数</th><th>メモ</th><th>ステータス</th><th>商談ステータス</th><th>アクション</th>
    </tr>
    <?php
    if ($items) {
        foreach ($items as $row) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['project_id']) . "</td>";
            echo "<td>" . htmlspecialchars($row['customer_name'] ?? '未指定') . "</td>";
            echo "<td>" . htmlspecialchars($row['address'] ?? '未指定') . "</td>";
            echo "<td>" . htmlspecialchars($row['order_date']) . "</td>";
            echo "<td>" . htmlspecialchars($row['new_schedule_date']) . "</td>";
            echo "<td>" . htmlspecialchars(calculateElapsedDays($row['new_schedule_date'], $row['status'])) . "</td>";
            echo "<td>" . htmlspecialchars($row['memo'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars(translateProjectStatus($row['status'])) . "</td>";
            echo "<td>" . htmlspecialchars($row['negotiation_status'] ?? '未指定') . "</td>";
            echo "<td><a href='edit_installation_projects.php?project_id=" . htmlspecialchars($row['project_id']) . "'>編集</a> | <a href='delete_installation_projects.php?project_id=" . htmlspecialchars($row['project_id']) . "'>削除</a></td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='10'>データが見つかりません。</td></tr>";
    }
    ?>
</table>
<a href="add_installation_projects.php">新規工事プロジェクト追加</a>
</body></html>
<?php
} catch (Exception $e) {
    error_log("Error in installation_projects_list.php: " . $e->getMessage());
    echo "<p class='error'>エラーが発生しました: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>