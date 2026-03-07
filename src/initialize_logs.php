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

$total_inserted = 0;

while ($row = $result->fetch_assoc()) {

    $document_id = (int) $row['id'];
    $user_id     = (int) $row['user_id'];
    $status      = $row['status'] ?: 'pending'; // default to pending

    $insert = "
        INSERT INTO document_logs
        (document_id, user_id, action, status, created_at)
        VALUES (?, ?, 'INITIAL_STATUS', ?, NOW())
    ";

    $stmt = $conn->prepare($insert);

    if (!$stmt) {
        die($conn->error);
    }

    $stmt->bind_param("iis", $document_id, $user_id, $status);

    if ($stmt->execute()) {
        $total_inserted++;
    }

    $stmt->close();
}

echo "Initialized logs for {$total_inserted} document(s).\n";