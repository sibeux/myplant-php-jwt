<?php
include '../config.php';

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

$method = $_SERVER['REQUEST_METHOD'] === 'POST' 
    ? ($_POST['method'] ?? '') 
    : ($_GET['method'] ?? '');

switch ($method) {
    case 'get_planting_date':
        getPlantingDate($db);
        break;

    case 'set_planting_date':
        setPlantingDate($db);
        break;

    default:
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid method or method not specified.'
        ]);
        break;
}

// Ambil tanggal tanam
function getPlantingDate($db) {
    $query = "SELECT date FROM planting_day WHERE uid = 1 LIMIT 1";

    if ($stmt = $db->prepare($query)) {
        $stmt->execute();
        $result = $stmt->get_result();

        $data = $result->fetch_assoc();

        echo json_encode([
            'status' => 'success',
            'data' => $data ?: [],
            'message' => $data ? 'Date retrieved successfully' : 'No date found'
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

// Set tanggal tanam
function setPlantingDate($db) {
    $date = $_POST['date'] ?? null;

    if (!$date) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Date is required.'
        ]);
        return;
    }

    $query = "UPDATE planting_day SET date = ? WHERE uid = 1";

    if ($stmt = $db->prepare($query)) {
        $stmt->bind_param('s', $date);

        if ($stmt->execute()) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Date updated successfully',
                'updated' => true
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to execute the update query.',
                'error' => $stmt->error
            ]);
        }

        $stmt->close();
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to prepare update statement.',
            'error' => $db->error
        ]);
    }
}

$db->close();
