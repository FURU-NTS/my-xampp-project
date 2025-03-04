<?php
include_once 'db_connection.php';
$page_title = "リース会社削除";
include_once 'header.php';

try {
    $conn = getDBConnection();
    $provider_id = $_GET['provider_id'] ?? '';
    if (empty($provider_id)) throw new Exception('リース会社IDが指定されていません');

    $stmt = $conn->prepare("SELECT * FROM lease_providers WHERE provider_id = ?");
    $stmt->execute([$provider_id]);
    $item = $stmt->fetch();
    if (!$item) throw new Exception('リース会社が見つかりません');
?>
<p>以下のリース会社を削除してもよろしいですか？</p>
<p>会社名: <?php echo htmlspecialchars($item['provider_name']); ?></p>
<form method="POST" action="process_delete_lease_providers.php">
    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
    <input type="hidden" name="provider_id" value="<?php echo htmlspecialchars($item['provider_id']); ?>">
    <input type="submit" value="削除">
    <a href="lease_providers_list.php">キャンセル</a>
</form>
</body></html>
<?php
} catch (Exception $e) {
    echo '<p class="error">' . htmlspecialchars($e->getMessage()) . '</p>';
}
?>