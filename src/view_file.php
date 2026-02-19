<?php

require "db/db.php";

if (!isset($_GET['id'])) {
    exit("No ID");
}

$id = (int) $_GET['id'];

// Get blob + filename
$sql = "SELECT file, file_name FROM documents WHERE id=?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();

$stmt->bind_result($blob, $fileName);
$stmt->fetch();

$stmt->close();
$conn->close();

if (!$blob) {
    exit("File not found");
}

// Detect MIME type from blob
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime  = $finfo->buffer($blob);

// Fallback filename
if (empty($fileName)) {

    $ext = explode('/', $mime)[1] ?? 'bin';
    $fileName = "document_$id.$ext";
}

// Headers
header("Content-Type: $mime");

// Preview images + PDF, download others
if (str_starts_with($mime, 'image/') || $mime === 'application/pdf') {

    header("Content-Disposition: inline; filename=\"$fileName\"");

} else {

    header("Content-Disposition: attachment; filename=\"$fileName\"");
}

header("Content-Length: " . strlen($blob));
header("Accept-Ranges: bytes");

// Output file
echo $blob;
exit;
