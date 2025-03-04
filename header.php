<?php 
include_once 'config.php'; // session_start() は config.php で呼び出し済み
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($page_title ?? ''); ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        nav { margin-bottom: 20px; }
        nav a { margin-right: 10px; text-decoration: none; color: #007BFF; }
        nav a:hover { text-decoration: underline; }
        .error { color: red; font-size: 0.9em; margin-top: 5px; }
        .success { color: green; font-size: 0.9em; margin-top: 5px; }
        .form-group { margin-bottom: 15px; }
        .required::after { content: "*"; color: red; margin-left: 3px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f5f5f5; }
        button, input[type="submit"] { padding: 5px 10px; }
    </style>
</head>
<body>
    <nav>
        <a href="lease_providers_list.php">リース会社MA</a> |
        <a href="employee_list.php">担当者MA</a> 
        <a href="equipment_master_list.php">機器MA</a> |
        <a href="companies_list.php">⓵顧客企業MA</a> |　
        <a href="orders_list.php">⓶受注管理</a> |
        <a href="order_details_list.php">⓷見直し詳細</a> |
        <a href="credit_applications_list.php">⓸与信管理</a> |
        <a href="lease_contracts_list.php">リース記録</a> |
        <a href="installation_projects_list.php">Ⓐ新規工事プロジェクト</a> |
        <a href="installation_tasks_list.php">Ⓑ新規工事タスク</a> |
        <a href="maintenance_requests_list.php">保守受付</a> |
        <a href="maintenance_records_list.php">保守記録</a> |
        <a href="sales_points_list.php">⓹ポイント</a> |
        <a href="leased_equipment_list.php">リース機器</a>
    </nav>
    <h1><?php echo htmlspecialchars($page_title ?? ''); ?></h1>