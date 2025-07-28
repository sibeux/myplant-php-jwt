<?php
include '../config.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

try {
    // Query data
    $sql = "SELECT date FROM planting_day";
    $stmt = $db->prepare($sql);

    if (!$stmt) {
        throw new Exception("Failed to prepare statement: " . $db->error);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    // Ambil hasil sebagai array asosiatif
    $days = $result->fetch_all(MYSQLI_ASSOC);

    echo json_encode($days);

    $stmt->close();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
} finally {
    $db->close();
}
