<?php
include_once 'db_connection.php';
include_once 'config.php';

// 管理者モードの切り替え
if (isset($_GET['toggle_admin'])) {
    $_SESSION['is_admin'] = !$_SESSION['is_admin'];
    header('Location: equipment_master_list.php');
    exit;
}
$_SESSION['is_admin'] = isset($_SESSION['is_admin']) ? $_SESSION['is_admin'] : false;

$page_title = "機器MASTER";
include_once 'header.php';

try {
    $conn = getDBConnection();

    // CSVインポートの処理
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
        $file = $_FILES['csv_file']['tmp_name'];
        $filename = $_FILES['csv_file']['name'];
        if (($handle = fopen($file, "r")) !== FALSE) {
            $header = fgetcsv($handle, 1000, ","); // ヘッダー行をスキップ
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                if (strpos($filename, 'equipment_master_new_template') !== false) {
                    // 新規用シート (5列: 機器名, タイプ, メーカー, モデル番号, 価格)
                    if (count($data) === 5) {
                        $stmt = $conn->prepare("INSERT INTO equipment_master (equipment_name, equipment_type, manufacturer, model_number, price) VALUES (?, ?, ?, ?, ?)");
                        $stmt->execute([$data[0], $data[1], $data[2], $data[3], $data[4]]);
                    }
                } elseif (strpos($filename, 'equipment_master_update_template') !== false) {
                    // 既存変更用シート (6列: 機器ID, 機器名, タイプ, メーカー, モデル番号, 価格)
                    if (count($data) === 6 && !empty($data[0])) {
                        $stmt = $conn->prepare("INSERT INTO equipment_master (equipment_id, equipment_name, equipment_type, manufacturer, model_number, price) VALUES (?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE equipment_name = ?, equipment_type = ?, manufacturer = ?, model_number = ?, price = ?");
                        $stmt->execute([
                            $data[0], $data[1], $data[2], $data[3], $data[4], $data[5], // INSERT用
                            $data[1], $data[2], $data[3], $data[4], $data[5]           // UPDATE用
                        ]);
                    }
                }
            }
            fclose($handle);
            header('Location: equipment_master_list.php?status=success&message=CSVデータがインポートされました');
            exit;
        } else {
            throw new Exception('CSVファイルの読み込みに失敗しました');
        }
    }

    // 空のCSVテンプレートダウンロード（新規用）
    if (isset($_GET['download_new_template'])) {
        ob_end_clean();
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="equipment_master_new_template.csv"');
        $output = fopen('php://output', 'w');
        fputs($output, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel
        $headers = ['機器名', 'タイプ', 'メーカー', 'モデル番号', '価格']; // 機器IDなし
        fputcsv($output, $headers);
        fclose($output);
        exit;
    }

    // 既存データのCSVテンプレートダウンロード（既存変更用）
    if (isset($_GET['download_update_template'])) {
        ob_end_clean();
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="equipment_master_update_template.csv"');
        $output = fopen('php://output', 'w');
        fputs($output, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel
        $headers = ['機器ID', '機器名', 'タイプ', 'メーカー', 'モデル番号', '価格']; // 日本語ヘッダー
        fputcsv($output, $headers);

        // 既存データを取得して出力
        $stmt = $conn->query("SELECT equipment_id, equipment_name, equipment_type, manufacturer, model_number, price FROM equipment_master");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $data = [
                $row['equipment_id'],
                $row['equipment_name'],
                $row['equipment_type'],
                $row['manufacturer'] ?? '',
                $row['model_number'] ?? '',
                $row['price'] !== null ? $row['price'] : ''
            ];
            fputcsv($output, $data);
        }
        fclose($output);
        exit;
    }

    // 既存のリスト表示
    $stmt = $conn->query("SELECT * FROM equipment_master");
    $items = $stmt->fetchAll();

    if (isset($_GET['status']) && isset($_GET['message'])) {
        $class = $_GET['status'] === 'success' ? 'success' : 'error';
        echo "<p class='$class'>" . htmlspecialchars($_GET['message']) . "</p>";
    }
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <style>
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .success { color: green; }
        .error { color: red; }
        .table-header { margin-bottom: 10px; }
        .csv-import { margin-bottom: 20px; }
    </style>
</head>
<body>
<div style="margin-bottom: 10px;">
    <a href="equipment_master_list.php?toggle_admin=1">管理者モード: <?php echo $_SESSION['is_admin'] ? 'ON' : 'OFF'; ?> (切り替え)</a>
</div>

<!-- CSVインポートフォーム -->
<div class="csv-import">
    <form method="POST" enctype="multipart/form-data">
        <label for="csv_file">CSVファイルを選択:</label>
        <input type="file" id="csv_file" name="csv_file" accept=".csv" required>
        <input type="submit" value="インポート">
    </form>
    <a href="equipment_master_list.php?download_new_template=1">新規用CSVテンプレートをダウンロード</a>
    <br>
    <a href="equipment_master_list.php?download_update_template=1">既存変更用CSVテンプレートをダウンロード</a>
</div>

<!-- 左上に「機器マスター追加」ボタン -->
<div class="table-header">
    <a href="add_equipment_master.php">機器マスター追加</a>
</div>

<table>
    <tr>
        <th>ID</th>
        <th>機器名</th>
        <th>タイプ</th>
        <th>メーカー</th>
        <th>モデル番号</th>
        <?php if ($_SESSION['is_admin']) { echo "<th>価格</th>"; } ?>
        <th>アクション</th>
    </tr>
    <?php
    if ($items) {
        foreach ($items as $row) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['equipment_id']) . "</td>";
            echo "<td>" . htmlspecialchars($row['equipment_name']) . "</td>";
            echo "<td>" . htmlspecialchars($row['equipment_type']) . "</td>";
            echo "<td>" . htmlspecialchars($row['manufacturer']) . "</td>";
            echo "<td>" . htmlspecialchars($row['model_number']) . "</td>";
            if ($_SESSION['is_admin']) {
                echo "<td>" . number_format($row['price'], 0) . " 円</td>";
            }
            echo "<td><a href='edit_equipment_master.php?equipment_id=" . htmlspecialchars($row['equipment_id']) . "'>編集</a> | <a href='delete_equipment_master.php?equipment_id=" . htmlspecialchars($row['equipment_id']) . "'>削除</a></td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='" . ($_SESSION['is_admin'] ? 7 : 6) . "'>データが見つかりません。</td></tr>";
    }
    ?>
</table>
<a href="add_equipment_master.php">機器マスター追加</a>
</body>
</html>
<?php
} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    echo "<p class='error'>エラーが発生しました。</p>";
}
?>