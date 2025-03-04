<?php
include_once 'db_connection.php';
$page_title = "新規工事タスク一覧";
include_once 'header.php';

try {
    $conn = getDBConnection();
    $stmt = $conn->query("SELECT it.*, ip.new_schedule_date, o.customer_name, o.negotiation_status, 
                          e1.full_name AS employee_1, e2.full_name AS employee_2 
                          FROM installation_tasks it 
                          LEFT JOIN installation_projects ip ON it.project_id = ip.project_id 
                          LEFT JOIN orders o ON ip.order_id = o.id 
                          LEFT JOIN employees e1 ON it.employee_id_1 = e1.employee_id 
                          LEFT JOIN employees e2 ON it.employee_id_2 = e2.employee_id 
                          WHERE o.negotiation_status IN ('進行中', '与信怪しい', '書換完了')");
    $items = $stmt->fetchAll();

    // タスクステータスの日本語変換関数
    function translateTaskStatus($status) {
        switch ($status) {
            case 'not_started': return '未開始';
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
<style>
    table { width: 100%; border-collapse: collapse; table-layout: fixed; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    th { background-color: #f2f2f2; }
    th:nth-child(1), td:nth-child(1) { width: 5%; } /* ID */
    th:nth-child(2), td:nth-child(2) { width: 15%; } /* 顧客名 */
    th:nth-child(3), td:nth-child(3) { width: 5%; } /* プロジェクトID */
    th:nth-child(4), td:nth-child(4) { width: 8%; } /* 商談ステータス */
    th:nth-child(5), td:nth-child(5) { width: 10%; } /* 新規予定日 */
    th:nth-child(6), td:nth-child(6) { width: 8%; } /* 経過日数 */
    th:nth-child(7), td:nth-child(7) { width: 12%; } /* タスク名 */
    th:nth-child(8), td:nth-child(8) { width: 15%; } /* メモ */
    th:nth-child(9), td:nth-child(9) { width: 8%; } /* 担当者１ */
    th:nth-child(10), td:nth-child(10) { width: 8%; } /* 担当者２ */
    th:nth-child(11), td:nth-child(11) { width: 10%; } /* ステータス */
    th:nth-child(12), td:nth-child(12) { width: 10%; } /* 開始日 */
    th:nth-child(13), td:nth-child(13) { width: 10%; } /* 終了日 */
    th:nth-child(14), td:nth-child(14) { width: 10%; } /* アクション */
    select { width: 100%; padding: 2px; }
</style>
<table>
    <tr>
        <th>ID</th><th>顧客名</th><th>プロジェクトID</th><th>商談ステータス</th><th>新規予定日</th><th>経過日数</th><th>タスク名</th><th>メモ</th><th>担当者１</th><th>担当者２</th><th>ステータス</th><th>開始日</th><th>終了日</th><th>アクション</th>
    </tr>
    <?php
    if ($items) {
        foreach ($items as $row) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['task_id']) . "</td>";
            echo "<td>" . htmlspecialchars($row['customer_name'] ?? '未指定') . "</td>";
            echo "<td>" . htmlspecialchars($row['project_id'] ?? '未指定') . "</td>";
            echo "<td>" . htmlspecialchars($row['negotiation_status'] ?? '未指定') . "</td>";
            echo "<td>" . htmlspecialchars($row['new_schedule_date'] ?? '未指定') . "</td>";
            echo "<td>" . htmlspecialchars(calculateElapsedDays($row['new_schedule_date'], $row['status'])) . "</td>";
            echo "<td>" . htmlspecialchars($row['task_name']) . "</td>";
            echo "<td>" . htmlspecialchars($row['memo'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($row['employee_1'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($row['employee_2'] ?? '') . "</td>";
            echo "<td>";
            echo "<form method='POST' action='update_task_status.php' style='margin: 0;'>";
            echo "<input type='hidden' name='task_id' value='" . htmlspecialchars($row['task_id']) . "'>";
            echo "<select name='status' onchange='this.form.submit()'>";
            foreach (['not_started' => '未開始', 'in_progress' => '進行中', 'completed' => '完了'] as $value => $label) {
                $selected = $row['status'] === $value ? 'selected' : '';
                echo "<option value='$value' $selected>$label</option>";
            }
            echo "</select>";
            echo "</form>";
            echo "</td>";
            echo "<td>" . htmlspecialchars($row['start_date'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($row['end_date'] ?? '') . "</td>";
            echo "<td><a href='edit_installation_tasks.php?task_id=" . htmlspecialchars($row['task_id']) . "'>編集</a> | <a href='delete_installation_tasks.php?task_id=" . htmlspecialchars($row['task_id']) . "'>削除</a></td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='14'>データが見つかりません。</td></tr>";
    }
    ?>
</table>
<a href="add_installation_tasks.php">工事タスク追加</a>
</body></html>
<?php
} catch (Exception $e) {
    error_log("Error in installation_tasks_list.php: " . $e->getMessage());
    echo "<p class='error'>エラーが発生しました: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>