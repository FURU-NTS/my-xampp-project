<?php
include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $application_id = $_POST["application_id"];
    $company_id = $_POST["company_id"];
    $contract_id = $_POST["contract_id"];
    $application_date = $_POST["application_date"];
    $status = $_POST["status"];

    $sql = "UPDATE credit_applications SET company_id = ?, contract_id = ?, application_date = ?, status = ? WHERE application_id = ?";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("iissi", $company_id, $contract_id, $application_date, $status, $application_id);

        if ($stmt->execute()) {
            echo "リース審査情報が正常に更新されました。";
        } else {
            echo "エラー: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "エラー: " . $conn->error;
    }

    $conn->close();
}
?>
