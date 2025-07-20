<?php
require_once '../includes_files/auth_check.php';
requireLogin();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include '../includes_files/connection.php';
if (isset($_POST['itemName']) && isset($_POST['itemQty'])) {
    $itemName = trim($_POST['itemName']);
    $itemQty = intval($_POST['itemQty']);
    $itemPrice = isset($_POST['itemPrice']) ? floatval($_POST['itemPrice']) : 0.00;
    $itemDesc = isset($_POST['itemDesc']) ? trim($_POST['itemDesc']) : '';
    $hasImages = isset($_FILES['itemImage']) && isset($_FILES['itemImage']['name']) && count(array_filter($_FILES['itemImage']['name'])) > 0;
    $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    $uploadDir = __DIR__ . '/uploads/';
    $error = '';
    if (!$hasImages) {
        header('Location: inventory_list_form.php?error=1');
        exit;
    } else {
        foreach ($_FILES['itemImage']['name'] as $idx => $name) {
            $fileExt = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            $size = $_FILES['itemImage']['size'][$idx];
            $err = $_FILES['itemImage']['error'][$idx];
            if ($err !== UPLOAD_ERR_OK || !in_array($fileExt, $allowedExts) || $size > $maxSize) {
                header('Location: inventory_list_form.php?error=1');
                exit;
            }
        }
    }
    if ($error === '' && $itemName !== '' && $itemQty >= 0) {
        $stmt = $conn->prepare('INSERT INTO inventory_items (name, quantity, price, description) VALUES (?, ?, ?, ?)');
        if (!$stmt) {
            header('Location: inventory_list_form.php?error=1');
            exit;
        }
        if (!$stmt->bind_param('sids', $itemName, $itemQty, $itemPrice, $itemDesc)) {
            header('Location: inventory_list_form.php?error=1');
            exit;
        }
        if (!$stmt->execute()) {
            header('Location: inventory_list_form.php?error=1');
            exit;
        }
        $inventoryId = $stmt->insert_id;
        $stmt->close();
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0777, true)) {
                header('Location: inventory_list_form.php?error=1');
                exit;
            }
        }
        foreach ($_FILES['itemImage']['name'] as $idx => $name) {
            $fileTmp = $_FILES['itemImage']['tmp_name'][$idx];
            $fileExt = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            $newFileName = uniqid('inv_', true) . '.' . $fileExt;
            $targetPath = $uploadDir . $newFileName;
            if (!move_uploaded_file($fileTmp, $targetPath)) {
                header('Location: inventory_list_form.php?error=1');
                exit;
            }
            $imagePath = 'uploads/' . $newFileName;
            $stmtImg = $conn->prepare('INSERT INTO inventory_images (inventory_id, image_path) VALUES (?, ?)');
            if (!$stmtImg) {
                header('Location: inventory_list_form.php?error=1');
                exit;
            }
            if (!$stmtImg->bind_param('is', $inventoryId, $imagePath)) {
                header('Location: inventory_list_form.php?error=1');
                exit;
            }
            if (!$stmtImg->execute()) {
                header('Location: inventory_list_form.php?error=1');
                exit;
            }
            $stmtImg->close();
        }
        header('Location: inventory_list_form.php?success=1');
        exit;
    } else {
        header('Location: inventory_list_form.php?error=1');
        exit;
    }
} else {
    header('Location: inventory_list_form.php');
    exit;
} 