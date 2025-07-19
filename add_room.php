<?php
// add_room.php - Handles POST for adding a room/facility with image upload
session_start();
include 'connnection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_room_modal'])) {
    $room_number = trim($_POST['room_number']);
    $room_type = trim($_POST['room_type']);
    $description = trim($_POST['description']);
    $facility = trim($_POST['facility']);
    $price = trim($_POST['price']); // now a string
    $status = 'available'; // Always set to available for new rooms
    $error = '';
    // Validate required fields
    if ($room_number === '' || $room_type === '' || $price === '') {
        $error = 'Room number, type, and price are required.';
    } elseif (isset($_FILES['room_images']) && count(array_filter($_FILES['room_images']['name'])) > 10) {
        $error = 'You can upload up to 10 images per room.';
    } else {
        // Check for duplicate room number
        $stmt = $conn->prepare('SELECT room_id FROM rooms WHERE room_number = ?');
        $stmt->bind_param('s', $room_number);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $error = 'Room number already exists.';
        } else {
            $stmt->close();
            $stmt = $conn->prepare('INSERT INTO rooms (room_number, room_type, description, facility, price, status) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->bind_param('ssssss', $room_number, $room_type, $description, $facility, $price, $status);
            if ($stmt->execute()) {
                $room_id = $stmt->insert_id;
                $stmt->close();
                // Handle image uploads
                if (isset($_FILES['room_images']) && count(array_filter($_FILES['room_images']['name'])) > 0) {
                    $upload_dir = 'uploads/rooms/';
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                    $uploaded = 0;
                    foreach ($_FILES['room_images']['tmp_name'] as $idx => $tmp_name) {
                        $name = $_FILES['room_images']['name'][$idx];
                        $type = $_FILES['room_images']['type'][$idx];
                        $error_img = $_FILES['room_images']['error'][$idx];
                        $size = $_FILES['room_images']['size'][$idx];
                        if ($error_img === UPLOAD_ERR_OK && in_array($type, $allowed_types) && $size <= 5*1024*1024) {
                            $ext = pathinfo($name, PATHINFO_EXTENSION);
                            $new_name = uniqid('roomimg_', true) . '.' . $ext;
                            $target = $upload_dir . $new_name;
                            if (move_uploaded_file($tmp_name, $target)) {
                                // Insert image path into room_images table
                                $stmt_img = $conn->prepare('INSERT INTO room_images (room_id, image_path) VALUES (?, ?)');
                                $stmt_img->bind_param('is', $room_id, $target);
                                $stmt_img->execute();
                                $stmt_img->close();
                                $uploaded++;
                            }
                        }
                    }
                }
                header('Location: room_management.php');
                exit();
            } else {
                $error = 'Failed to add room. Please try again.';
            }
        }
        $stmt->close();
    }
    // On error, preserve form values and show modal
    $_SESSION['add_room_error'] = $error;
    $_SESSION['add_room_form'] = [
        'room_number' => $room_number,
        'room_type' => $room_type,
        'description' => $description,
        'facility' => $facility,
        'price' => $price,
        'status' => $status
    ];
    header('Location: room_management.php?show_modal=1');
    exit();
}
// Fallback: direct access
header('Location: room_management.php');
exit();
