<?php
include '../config.php';

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

$input = json_decode(file_get_contents("php://input"), true); 
$method = $input['method'] ?? ($_POST['method'] ?? ($_GET['method'] ?? ''));

$method = $_SERVER['REQUEST_METHOD'] === 'POST' 
    ? ($input['method'] ?? '') 
    : ($_GET['method'] ?? '');

switch ($method) {
    case 'get_water_history':
        getWaterHistory($db);
        break;

    case 'set_water_history':
        setWaterHistory($db);
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

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        echo json_encode([
            'status' => 'success',
            'data' => $data,
            'message' => $data ? 'Data retrieved successfully' : 'No data found'
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


function setWaterHistory($db) {
    global $input;

    $time = $input['time'] ?? $_POST['time'];
    $duration = $input['duration'] ?? $_POST['duration'];
    $type = $input['type'] ?? $_POST['type'];

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
