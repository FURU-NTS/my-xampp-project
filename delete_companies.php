<?php
include_once 'db_connection.php';
$page_title = "顧客企業削除";
include_once 'header.php';

try {
    $conn = getDBConnection();
    $company_id = $_GET['company_id'] ?? '';
    if (empty($company_id)) throw new Exception('会社IDが指定されていません');

    $stmt = $conn->prepare("SELECT company_name, business_registration_number FROM companies WHERE company_id = ?");
    $stmt->execute([$company_id]);
    $item = $stmt->fetch();
    if (!$item) throw new Exception('顧客企業が見つかりません');
?>
<style>
    table { 
        width: 50%; /* 表全体の幅を50%に制限 */
        border-collapse: collapse; 
        table-layout: fixed; /* 列幅を固定 */
        margin-bottom: 20px;
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
        width: 30%; /* 見出し幅を30%に */
    }
    td { 
        width: 70%; /* データ幅を70%に */
    }
</style>
<p>以下の顧客企業を削除してもよろしいですか？</p>
<table>
    <tr><th>会社名</th><td><?php echo htmlspecialchars($item['company_name']); ?></td></tr>
    <tr><th>登記番号</th><td><?php echo htmlspecialchars($item['business_registration_number']); ?></td></tr>
</table>
<form method="POST" action="process_delete_companies.php">
    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
    <input type="hidden" name="company_id" value="<?php echo htmlspecialchars($company_id); ?>">
    <input type="submit" value="削除" style="background-color: #ff4444; color: white; border: none; padding: 5px 10px; cursor: pointer;">
    <a href="companies_list.php" style="margin-left: 10px; text-decoration: none; color: #007BFF;">キャンセル</a>
</form>
</body>
</html>
<?php
} catch (Exception $e) {
    echo '<p class="error">' . htmlspecialchars($e->getMessage()) . '</p>';
}
?>