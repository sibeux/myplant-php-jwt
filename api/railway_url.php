<?php
include '../config.php';

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

$method = $_SERVER['REQUEST_METHOD'] === 'POST' 
    ? ($_POST['method'] ?? '') 
    : ($_GET['method'] ?? '');

switch ($method) {
    case 'get_railway_url':
        getRailwayUrl($db);
        break;

    default:
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid method or method not specified.'
        ]);
        break;
}

function getRailwayUrl($db) {
    $query = "SELECT url FROM railway_url";

    if ($stmt = $db->prepare($query)) {
        $stmt->execute();
        $result = $stmt->get_result();

        $data = $result->fetch_assoc();

        echo json_encode([
            'status' => 'success',
            'data' => $data ?: [],
            'message' => $data ? 'data retrieved successfully' : 'No data found'
        ]);

        $stmt->close();
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to prepare statement.',
            'error' => $db->error
        ]);
    }
}

$db->close();
