<?php
header('Content-Type: application/json');

session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if (!isset($_FILES['image'])) {
    http_response_code(400);
    echo json_encode(['error' => 'No file uploaded']);
    exit();
}

$file = $_FILES['image'];
$folder = $_POST['folder'] ?? 'general';

if ($file['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['error' => 'Upload error: ' . $file['error']]);
    exit();
}

$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
if (!in_array($file['type'], $allowedTypes)) {
    http_response_code(400);
    echo json_encode(['error' => 'Only JPEG, PNG, GIF and WebP files are allowed']);
    exit();
}

$maxSize = 2 * 1024 * 1024;
if ($file['size'] > $maxSize) {
    http_response_code(400);
    echo json_encode(['error' => 'File size must be less than 2MB']);
    exit();
}

// Kök dizine göre uploads klasörü
$uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/' . $folder . '/images/';
$yearMonth = date('Y/m');
$uploadDir .= $yearMonth . '/';

if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$fileName = uniqid() . '_' . time() . '.' . $extension;
$filePath = $uploadDir . $fileName;

if (move_uploaded_file($file['tmp_name'], $filePath)) {
    $webPath = '/uploads/' . $folder . '/images/' . $yearMonth . '/' . $fileName;
    
    echo json_encode([
        'success' => true,
        'file_path' => $webPath,
        'file_name' => $fileName,
        'file_size' => $file['size'],
        'file_type' => $file['type']
    ]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to save file']);
}
?>