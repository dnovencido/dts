<?php

require "db/db.php";

/*
|--------------------------------------------------------------------------
| Initialize Logs for Legacy Documents
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT d.id, d.user_id, d.status
    FROM documents d
    LEFT JOIN document_logs dl 
        ON dl.document_id = d.id
    WHERE dl.document_id IS NULL
";

$result = $conn->query($sql);

if (!$result) {
    die("Query failed: " . $conn->error);
}

$total = 0;

while ($row = $result->fetch_assoc()) {

    $document_id = (int) $row['id'];
    $user_id     = (int) $row['user_id'];
    $status      = $row['status'] ?: 'pending';

    /*
    ----------------------------------------
    1. Insert INITIAL_STATUS → pending
    ----------------------------------------
    */

    $stmt1 = $conn->prepare("
        INSERT INTO document_logs
        (document_id, user_id, action, status, created_at)
        VALUES (?, ?, 'INITIAL_STATUS', 'pending', NOW())
    ");

    $stmt1->bind_param("ii", $document_id, $user_id);
    $stmt1->execute();
    $stmt1->close();


    /*
    ----------------------------------------
    2. Insert CURRENT STATUS (if not pending)
    ----------------------------------------
    */

    if ($status !== 'pending') {

        $stmt2 = $conn->prepare("
            INSERT INTO document_logs
            (document_id, user_id, action, status, created_at)
            VALUES (?, ?, 'STATUS_CHANGED', ?, NOW())
        ");

        $stmt2->bind_param("iis", $document_id, $user_id, $status);
        $stmt2->execute();
        $stmt2->close();
    }

    $total++;
}

echo "Initialized {$total} legacy document(s).";