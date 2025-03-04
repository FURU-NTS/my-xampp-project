<?php
try {
    $pdo = new PDO("mysql:host=localhost;dbname=leaseandmaintenancedb", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['project_id']) && !empty($_POST['project_id'])) {
        $id = $_POST['project_id'];

        // 関連するinstallation_tasksを先に削除
        $sql_tasks = "DELETE FROM installation_tasks WHERE project_id = :id";
        $stmt_tasks = $pdo->prepare($sql_tasks);
        $stmt_tasks->execute(['id' => $id]);

        // installation_projectsを削除
        $sql = "DELETE FROM installation_projects WHERE project_id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $id]);

        header("Location: installation_projects_list.php");
        exit;
    } else {
        echo "削除するプロジェクトが指定されていません。";
        echo '<br><a href="installation_projects_list.php">一覧に戻る</a>';
    }
} catch (PDOException $e) {
    echo "エラー: " . $e->getMessage();
}
?>