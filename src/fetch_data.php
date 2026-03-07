<?php

include "session.php";
include "models/document.php";
include "models/user_role.php";
include "models/assigned_office.php";

/* =========================
   Check Session
========================= */

if (!isset($_SESSION['id'])) {

    header('Content-Type: application/json');
    http_response_code(401);

    echo json_encode([
        'status'  => 'error',
        'message' => 'Unauthorized'
    ]);

    exit;
}


/* =========================
   Initialize Filter
========================= */

$filter = [];


/* =========================
   Role-based Filtering
========================= */

$roles = get_user_roles($_SESSION['id'], 'names');

if (in_array('employee', $roles)) {

    $current_office = get_assigned_office($_SESSION['id']);

    if (!empty($current_office)) {

        $filter['item'][] = [
            'column' => 'receiving_office',
            'value'  => $current_office
        ];
    }
}


/* =========================
   Request Parameters
========================= */

$chart = $_GET['chart'] ?? null;
$range = $_GET['range'] ?? 6;
$year  = $_GET['year'] ?? date('Y');
$category = $_GET['category'] ?? null;


/* =========================
   Output JSON
========================= */

header('Content-Type: application/json');


switch ($chart) {

    case 1:

        echo json_encode(
            count_documents($range, $filter)
        );

        break;


    case 2:

        echo json_encode(
            count_documents_per_type(
                $year,
                $category,
                $filter
            )
        );

        break;


    default:

        http_response_code(400);

        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid chart request'
        ]);
}