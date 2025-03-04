<?php
try {
    $pdo = new PDO("mysql:host=localhost;dbname=leaseandmaintenancedb", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['project_id']) && !empty($_GET['project_id'])) {
        $id = $_GET['project_id'];
        $sql = "SELECT ip.project_id, c.company_name AS customer_name, c.address, 
                       o.order_date, ip.new_schedule_date, 
                       DATEDIFF(CURDATE(), o.order_date) AS days_elapsed, 
                       ip.memo, ip.status, o.negotiation_status AS order_status, ip.contract_id
                FROM installation_projects ip
                LEFT JOIN orders o ON ip.order_id = o.id
                LEFT JOIN companies c ON o.company_id = c.company_id
                LEFT JOIN lease_contracts lc ON ip.contract_id = lc.contract_id
                WHERE ip.project_id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        $project = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($project) {
            $status_jp = [
                'planning' => '段取り中',
                'in_progress' => '進行中',
                'completed' => '完了'
            ];
            $status_display = $status_jp[$project['status']] ?? $project['status'];
            ?>
            <!DOCTYPE html>
            <html>
            <head>
                <title>工事プロジェクト削除確認</title>
                <style>
                    table { border-collapse: collapse; width: 80%; margin: 20px auto; }
                    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                    th { background-color: #f2f2f2; width: 120px; }
                    td { width: auto; }
                </style>
            </head>
            <body>
                <h2>以下の工事プロジェクトを削除しますか？</h2>
                <table>
                    <tr><th>ID</th><td><?php echo htmlspecialchars($project['project_id']); ?></td></tr>
                    <tr><th>顧客名</th><td><?php echo htmlspecialchars($project['customer_name'] ?: 'なし'); ?></td></tr>
                    <tr><th>住所</th><td><?php echo htmlspecialchars($project['address'] ?: 'なし'); ?></td></tr>
                    <tr><th>受注日</th><td><?php echo htmlspecialchars($project['order_date'] ?: '未定'); ?></td></tr>
                    <tr><th>新規予定日</th><td><?php echo htmlspecialchars($project['new_schedule_date'] ?: '未定'); ?></td></tr>
                    <tr><th>経過日数</th><td><?php echo htmlspecialchars($project['days_elapsed'] ?: '0'); ?></td></tr>
                    <tr><th>メモ</th><td><?php echo htmlspecialchars($project['memo'] ?: 'なし'); ?></td></tr>
                    <tr><th>ステータス</th><td><?php echo htmlspecialchars($status_display); ?></td></tr>
                    <tr><th>商談ステータス</th><td><?php echo htmlspecialchars($project['order_status'] ?: 'なし'); ?></td></tr>
                </table>
                <form method="POST" action="process_delete_installation_projects.php">
                    <input type="hidden" name="project_id" value="<?php echo $project['project_id']; ?>">
                    <input type="submit" value="削除する">
                    <a href="installation_projects_list.php">キャンセル</a>
                </form>
            </body>
            </html>
            <?php
        } else {
            echo "プロジェクトが見つかりません（ID: " . htmlspecialchars($id) . "）。";
            echo '<br><a href="installation_projects_list.php">一覧に戻る</a>';
        }
    } else {
        echo "削除するプロジェクトが指定されていません。";
        echo '<br><a href="installation_projects_list.php">一覧に戻る</a>';
    }
} catch (PDOException $e) {
    echo "エラー: " . $e->getMessage();
}
?>