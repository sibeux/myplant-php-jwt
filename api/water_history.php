<?php
include '../config.php';

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

$method = $_SERVER['REQUEST_METHOD'] === 'POST' 
    ? ($_POST['method'] ?? '') 
    : ($_GET['method'] ?? '');

switch ($method) {
    case 'get_water_history':
        getPlantingDate($db);
        break;

    case 'set_water_history':
        setPlantingDate($db);
        break;

    default:
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid method or method not specified.'
        ]);
        break;
}

function getWaterHistory($db) {
    $query = "SELECT * FROM water_history";

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
function setWaterHistory($db) {
    $time = $_POST['time'] ?? null;
    $duration = $_POST['duration'] ?? null;
    $type = $_POST['type'] ?? null;

    if (!$time) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Date is required.'
        ]);
        return;
    }

    $query = "INSERT INTO water_history (`uid`, `time`, `duration`, `type`) VALUES (NULL, ?, ?, ?);";

    if ($stmt = $db->prepare($query)) {
        $stmt->bind_param('sis', $time, $duration, $type);

        if ($stmt->execute()) {
            echo json_encode([
                'status' => 'success',
                'message' => 'History inserted successfully',
                'inserted' => true
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to execute the insert query.',
                'error' => $stmt->error
            ]);
        }

        $stmt->close();
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to prepare insert statement.',
            'error' => $db->error
        ]);
    }
}

$db->close();
