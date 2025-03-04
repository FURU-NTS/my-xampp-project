<?php
include_once 'db_connection.php';
$page_title = "見直し詳細";
include_once 'header.php';

try {
    $conn = getDBConnection();
    $stmt = $conn->query("SELECT od.*, o.customer_name, o.order_date, e.full_name AS sales_rep_name,
                          (SELECT COALESCE(SUM(COALESCE(od2.mobile_revision, 0) + COALESCE(od2.monitor_total, 0) + COALESCE(od2.service_total, 0)), 0)
                           FROM order_details od2 WHERE od2.order_id = o.id) AS revision_total_calc
                          FROM order_details od 
                          LEFT JOIN orders o ON od.order_id = o.id 
                          LEFT JOIN employees e ON od.sales_rep_id = e.employee_id");
    $items = $stmt->fetchAll();

    if (isset($_GET['status']) && isset($_GET['message'])) {
        $class = $_GET['status'] === 'success' ? 'success' : 'error';
        echo "<p class='$class'>" . htmlspecialchars($_GET['message']) . "</p>";
    }
?>
<style>
    table { 
        width: 100%; 
        border-collapse: collapse; 
        table-layout: fixed; /* 列幅を固定 */
    }
    th, td { 
        border: 1px solid #ddd; 
        padding: 8px; 
        text-align: left; 
        white-space: normal; /* 折り返しを許可 */
        overflow: hidden; 
        text-overflow: ellipsis; /* 長いテキストは省略 */
    }
    th { 
        background-color: #f2f2f2; 
        font-size: 0.9em; /* 見出しの文字サイズを少し小さく */
    }
    /* 列幅の調整 - 見直し合計を優先 */
    th:nth-child(1), td:nth-child(1) { width: 7%; }  /* 受注日 */
    th:nth-child(2), td:nth-child(2) { width: 8%; }  /* 顧客名 */
    th:nth-child(3), td:nth-child(3) { width: 7%; }  /* 担当者1 */
    th:nth-child(4), td:nth-child(4) { width: 10%; } /* 見直し合計 (税込) - 幅を広く */
    th:nth-child(5), td:nth-child(5) { width: 5%; }  /* 携帯見直し */
    th:nth-child(6), td:nth-child(6) { width: 8%; }  /* 携帯内容 */
    th:nth-child(7), td:nth-child(7) { width: 5%; }  /* モニター費A */
    th:nth-child(8), td:nth-child(8) { width: 8%; }  /* A内容 */
    th:nth-child(9), td:nth-child(9) { width: 5%; }  /* モニター費B */
    th:nth-child(10), td:nth-child(10) { width: 8%; } /* B内容 */
    th:nth-child(11), td:nth-child(11) { width: 5%; } /* モニター費C */
    th:nth-child(12), td:nth-child(12) { width: 8%; } /* C内容 */
    th:nth-child(13), td:nth-child(13) { width: 5%; } /* モニター合計 */
    th:nth-child(14), td:nth-child(14) { width: 5%; } /* サービス品1 */
    th:nth-child(15), td:nth-child(15) { width: 8%; } /* 1内容 */
    th:nth-child(16), td:nth-child(16) { width: 5%; } /* サービス品2 */
    th:nth-child(17), td:nth-child(17) { width: 8%; } /* 2内容 */
    th:nth-child(18), td:nth-child(18) { width: 5%; } /* サービス品3 */
    th:nth-child(19), td:nth-child(19) { width: 8%; } /* 3内容 */
    th:nth-child(20), td:nth-child(20) { width: 5%; } /* サービス合計 */
    th:nth-child(21), td:nth-child(21) { width: 8%; } /* その他 */
    th:nth-child(22), td:nth-child(22) { width: 9%; } /* アクション */
</style>
<table>
    <tr>
        <th>受注日</th>
        <th>受注顧客名</th>
        <th>担当者1</th>
        <th>見直し合計 (税込)</th>
        <th>携帯見直し (税込)</th>
        <th>携帯内容</th>
        <th>モニター費A (税込)</th>
        <th>A内容</th>
        <th>モニター費B (税込)</th>
        <th>B内容</th>
        <th>モニター費C (税込)</th>
        <th>C内容</th>
        <th>モニター合計 (税込)</th>
        <th>サービス品1 (税込)</th>
        <th>1内容</th>
        <th>サービス品2 (税込)</th>
        <th>2内容</th>
        <th>サービス品3 (税込)</th>
        <th>3内容</th>
        <th>サービス合計 (税込)</th>
        <th>その他</th>
        <th>アクション</th>
    </tr>
    <?php
    if ($items) {
        foreach ($items as $row) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['order_date'] ?? '未設定') . "</td>";
            echo "<td>" . htmlspecialchars($row['customer_name'] ?? '未指定') . "</td>";
            echo "<td>" . htmlspecialchars($row['sales_rep_name'] ?? '未設定') . "</td>";
            echo "<td>" . number_format($row['revision_total_calc'], 0) . " 円</td>";
            echo "<td>" . number_format($row['mobile_revision'] ?? 0, 0) . " 円</td>";
            echo "<td>" . htmlspecialchars($row['mobile_content'] ?? '未設定') . "</td>";
            echo "<td>" . number_format($row['mobile_monitor_fee_a'] ?? 0, 0) . " 円</td>";
            echo "<td>" . htmlspecialchars($row['monitor_content_a'] ?? '未設定') . "</td>";
            echo "<td>" . number_format($row['monitor_fee_b'] ?? 0, 0) . " 円</td>";
            echo "<td>" . htmlspecialchars($row['monitor_content_b'] ?? '未設定') . "</td>";
            echo "<td>" . number_format($row['monitor_fee_c'] ?? 0, 0) . " 円</td>";
            echo "<td>" . htmlspecialchars($row['monitor_content_c'] ?? '未設定') . "</td>";
            echo "<td>" . number_format($row['monitor_total'] ?? 0, 0) . " 円</td>";
            echo "<td>" . number_format($row['service_item_1'] ?? 0, 0) . " 円</td>";
            echo "<td>" . htmlspecialchars($row['service_content_1'] ?? '未設定') . "</td>";
            echo "<td>" . number_format($row['service_item_2'] ?? 0, 0) . " 円</td>";
            echo "<td>" . htmlspecialchars($row['service_content_2'] ?? '未設定') . "</td>";
            echo "<td>" . number_format($row['service_item_3'] ?? 0, 0) . " 円</td>";
            echo "<td>" . htmlspecialchars($row['service_content_3'] ?? '未設定') . "</td>";
            echo "<td>" . number_format($row['service_total'] ?? 0, 0) . " 円</td>";
            echo "<td>" . htmlspecialchars($row['others'] ?? '未設定') . "</td>";
            echo "<td><a href='edit_order_details.php?id=" . htmlspecialchars($row['id']) . "'>編集</a> | <a href='delete_order_details.php?id=" . htmlspecialchars($row['id']) . "'>削除</a></td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='22'>データが見つかりません。</td></tr>";
    }
    ?>
</table>
</body>
</html>
<?php
} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    echo "<p class='error'>エラーが発生しました。</p>";
}
?>