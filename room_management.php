<?php
// room_management.php - Room and Facility Management (Admin and Staff)
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: login_form.php');
    exit();
}
include 'connnection.php';

// Fetch error and form data from session if present
$add_room_error = isset($_SESSION['add_room_error']) ? $_SESSION['add_room_error'] : '';
$add_room_form = isset($_SESSION['add_room_form']) ? $_SESSION['add_room_form'] : [
    'room_number' => '',
    'room_type' => '',
    'description' => '',
    'facility' => '',
    'price' => '',
    'status' => 'available'
];
if (isset($_SESSION['add_room_error'])) unset($_SESSION['add_room_error']);
if (isset($_SESSION['add_room_form'])) unset($_SESSION['add_room_form']);

// Fetch all rooms/facilities with images
$rooms = [];
$sql = "SELECT * FROM rooms ORDER BY CAST(SUBSTRING(room_number, LOCATE(' ', room_number) + 1) AS UNSIGNED) ASC";
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        // Fetch images for this room
        $room_id = $row['room_id'];
        $img_sql = "SELECT image_path FROM room_images WHERE room_id = $room_id ORDER BY image_id LIMIT 10";
        $img_result = $conn->query($img_sql);
        $images = [];
        if ($img_result) {
            while ($img_row = $img_result->fetch_assoc()) {
                $images[] = $img_row['image_path'];
            }
        }
        $row['images'] = $images;
        $rooms[] = $row;
    }
}

// Pagination logic
$rooms_per_page = 8;
$total_rooms = count($rooms);
$total_pages = ceil($total_rooms / $rooms_per_page);
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
if ($page > $total_pages) $page = $total_pages;
$start_index = ($page - 1) * $rooms_per_page;
$rooms_to_show = array_slice($rooms, $start_index, $rooms_per_page);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Room & Facility Management</title>
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="login.css">
    <link rel="stylesheet" href="staff_management.css">
    <link rel="stylesheet" href="room_management.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    <div class="dashboard-layout">
        <aside class="sidebar">
            <h2>Admin Panel</h2>
            <nav>
                <a href="dashboard.php" class="<?= basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : '' ?>">Dashboard</a>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                <a href="staff_management.php" class="<?= basename($_SERVER['PHP_SELF']) === 'staff_management.php' ? 'active' : '' ?>">Staff Management</a>
                <?php endif; ?>
                <a href="room_management.php" class="<?= basename($_SERVER['PHP_SELF']) === 'room_management.php' ? 'active' : '' ?>">Room/Facility Management</a>
                <a href="booking.php">Bookings</a>
                <a href="IN_OUT.php" class="<?= basename($_SERVER['PHP_SELF']) === 'IN_OUT.php' ? 'active' : '' ?>">Check-in/Check-out</a>
                <a href="#">Settings</a>
                <a href="logout.php" style="color:#e53935;">Logout</a>
            </nav>
        </aside>
        <main class="main-content">
            <div class="dashboard-header">Room & Facility Management</div>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <div style="display: flex; justify-content: flex-start; align-items: center; width: 100%;">
                <button class="add-staff-btn" id="openAddRoomModal" type="button">+ Add Room/Facility</button>
            </div>
            <!-- Add Room Modal -->
            <div id="addRoomModal" class="modal">
                <div class="modal-content">
                    <span class="close" id="closeAddRoomModal">&times;</span>
                    <h2>Add Room/Facility</h2>
                    <?php if (!empty($add_room_error)): ?>
                        <div class="error" style="margin-bottom:12px;"> <?= htmlspecialchars($add_room_error) ?> </div>
                    <?php endif; ?>
                    <form method="POST" action="add_room.php" autocomplete="off" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="room_number">Room Number<span style="color:#e53935;">*</span></label>
                            <input type="text" name="room_number" id="room_number" value="<?= htmlspecialchars($add_room_form['room_number']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="room_type">Room Type<span style="color:#e53935;">*</span></label>
                            <textarea id="room_type" name="room_type" rows="1" style="height:38px;" required><?= htmlspecialchars($add_room_form['room_type']) ?></textarea>
                        </div>
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" rows="1" style="height:38px;" ><?= htmlspecialchars($add_room_form['description']) ?></textarea>
                        </div>
                        <div class="form-group">
                            <label for="facility">Facility</label>
                            <textarea id="facility" name="facility" rows="1" style="height:38px;" ><?= htmlspecialchars($add_room_form['facility']) ?></textarea>
                        </div>
                        <div class="form-group">
                            <label for="price">Price<span style="color:#e53935;">*</span></label>
                            <textarea name="price" id="price" rows="1" style="height:38px;" required class="form-control"><?= htmlspecialchars($add_room_form['price']) ?></textarea>
                        </div>
                        <div class="form-group">
                            <label for="room_images">Room Images (up to 10, jpg/png/gif/webp, max 5MB each)</label>
                            <input type="file" name="room_images[]" id="room_images" accept="image/*" multiple>
                        </div>
                        <input type="hidden" name="add_room_modal" value="1">
                        <button type="submit" class="add-staff-btn">Add Room/Facility</button>
                    </form>
                </div>
            </div>
            <!-- End Modal -->
            <?php endif; ?>
            <div class="room-gallery-grid">
                <?php if (count($rooms_to_show) > 0): ?>
                    <?php foreach ($rooms_to_show as $room): ?>
                        <div class="room-card-outer">
                            <span class="room-status-dot status-<?= htmlspecialchars($room['status']) ?>" title="<?= ucfirst(htmlspecialchars($room['status'])) ?>"></span>
                            <div class="room-card">
                                <img class="room-card-img" src="<?= !empty($room['images']) ? htmlspecialchars($room['images'][0]) : 'https://placehold.co/400x250?text=No+Image' ?>" alt="Room Image">
                                <div class="room-card-content">
                                    <div class="room-card-title"> <?= htmlspecialchars($room['room_number']) ?></div>
                                    <div class="room-card-type">Type: <?= htmlspecialchars($room['room_type']) ?></div>
                                    <button class="room-card-btn" onclick="openRoomModal(<?= $room['room_id'] ?>)">Details</button>
                                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                                        <button class="room-card-btn" style="margin-top:8px;" onclick="openEditRoomModal(<?= $room['room_id'] ?>)">Edit</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <!-- Edit Room Modal (admin only) -->
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <div class="modal" id="editRoomModal<?= $room['room_id'] ?>">
                            <div class="modal-content">
                                <span class="close" onclick="closeEditRoomModal(<?= $room['room_id'] ?>)">&times;</span>
                                <h2>Edit Room/Facility</h2>
                                <form method="POST" action="room_update.php" autocomplete="off" enctype="multipart/form-data">
                                    <input type="hidden" name="room_id" value="<?= $room['room_id'] ?>">
                                    <div class="form-group">
                                        <label for="edit_room_number_<?= $room['room_id'] ?>">Room Number<span style="color:#e53935;">*</span></label>
                                        <input type="text" name="room_number" id="edit_room_number_<?= $room['room_id'] ?>" value="<?= htmlspecialchars($room['room_number']) ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="edit_room_type_<?= $room['room_id'] ?>">Room Type<span style="color:#e53935;">*</span></label>
                                        <textarea id="edit_room_type_<?= $room['room_id'] ?>" name="room_type" rows="1" style="height:38px;" required><?= htmlspecialchars($room['room_type']) ?></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label for="edit_description_<?= $room['room_id'] ?>">Description</label>
                                        <textarea id="edit_description_<?= $room['room_id'] ?>" name="description" rows="1" style="height:38px;" ><?= htmlspecialchars($room['description']) ?></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label for="edit_facility_<?= $room['room_id'] ?>">Facility</label>
                                        <textarea id="edit_facility_<?= $room['room_id'] ?>" name="facility" rows="1" style="height:38px;" ><?= htmlspecialchars($room['facility']) ?></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label for="edit_price_<?= $room['room_id'] ?>">Price<span style="color:#e53935;">*</span></label>
                                        <textarea name="price" id="edit_price_<?= $room['room_id'] ?>" rows="1" style="height:38px;" required class="form-control"><?= htmlspecialchars($room['price']) ?></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label for="edit_status_<?= $room['room_id'] ?>">Status<span style="color:#e53935;">*</span></label>
                                        <select name="status" id="edit_status_<?= $room['room_id'] ?>" required>
                                            <option value="available" <?= $room['status']==='available'?'selected':'' ?>>Available</option>
                                            <option value="occupied" <?= $room['status']==='occupied'?'selected':'' ?>>Occupied</option>
                                            <option value="maintenance" <?= $room['status']==='maintenance'?'selected':'' ?>>Maintenance</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="edit_room_images_<?= $room['room_id'] ?>">Room Images (up to 10, jpg/png/gif/webp, max 5MB each)</label>
                                        <input type="file" name="room_images[]" id="edit_room_images_<?= $room['room_id'] ?>" accept="image/*" multiple>
                                    </div>
                                    <button type="submit" class="add-staff-btn">Update Room/Facility</button>
                                </form>
                            </div>
                        </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="grid-column: 1/-1; text-align:center; color:#3a6073; font-size:1.1rem;">No rooms or facilities found.</div>
                <?php endif; ?>
            </div>
            <!-- Pagination Controls -->
            <div style="display:flex;justify-content:center;align-items:center;margin:32px 0 0 0;gap:16px;">
                <a href="?page=<?= $page-1 ?>" class="add-staff-btn" style="padding:6px 18px;min-width:90px;<?= $page <= 1 ? 'pointer-events:none;opacity:0.5;' : '' ?>">&laquo; Previous</a>
                <span style="font-size:1.1rem;color:#3a6073;font-weight:600;">Page <?= $page ?> of <?= $total_pages ?></span>
                <a href="?page=<?= $page+1 ?>" class="add-staff-btn" style="padding:6px 18px;min-width:90px;<?= $page >= $total_pages ? 'pointer-events:none;opacity:0.5;' : '' ?>">Next &raquo;</a>
            </div>
            <!-- Room Details Modals -->
            <?php foreach ($rooms as $room): ?>
            <div class="room-modal" id="roomModal<?= $room['room_id'] ?>">
                <div class="room-modal-content">
                    <span class="room-modal-close" onclick="closeRoomModal(<?= $room['room_id'] ?>)">&times;</span>
                    <div class="room-modal-header">
                        <div class="room-modal-title">Room <?= htmlspecialchars($room['room_number']) ?> - <?= htmlspecialchars($room['room_type']) ?></div>
                    </div>
                    <div class="room-modal-carousel">
                        <?php $imgs = $room['images']; if (count($imgs) === 0) { $imgs = ['https://placehold.co/600x260?text=No+Image']; } ?>
                        <button class="carousel-arrow left" onclick="carouselPrev(<?= $room['room_id'] ?>)"><i class="fa fa-chevron-left"></i></button>
                        <?php foreach ($imgs as $idx => $img): ?>
                            <img class="carousel-img" id="carouselImg<?= $room['room_id'] ?>-<?= $idx ?>" src="<?= htmlspecialchars($img) ?>" alt="Room Image" style="display:<?= $idx===0?'block':'none' ?>;">
                        <?php endforeach; ?>
                        <button class="carousel-arrow right" onclick="carouselNext(<?= $room['room_id'] ?>)"><i class="fa fa-chevron-right"></i></button>
                    </div>
                    <div class="room-modal-details">
                        <div class="room-modal-details-row">
                            <span class="room-modal-details-label">Room Number:</span>
                            <div class="room-modal-details-value-indented"><?= htmlspecialchars($room['room_number']) ?></div>
                        </div>
                        <div class="room-modal-details-row">
                            <span class="room-modal-details-label">Type:</span>
                            <?php foreach (preg_split('/\r?\n/', $room['room_type']) as $line): ?>
                                <div class="room-modal-details-value-indented"><?= htmlspecialchars($line) ?></div>
                            <?php endforeach; ?>
                        </div>
                        <div class="room-modal-details-row">
                            <span class="room-modal-details-label">Description:</span>
                            <?php foreach (preg_split('/\r?\n/', $room['description']) as $line): ?>
                                <div class="room-modal-details-value-indented"><?= htmlspecialchars($line) ?></div>
                            <?php endforeach; ?>
                        </div>
                        <div class="room-modal-details-row">
                            <span class="room-modal-details-label">Facility:</span>
                            <?php foreach (preg_split('/\r?\n/', $room['facility']) as $line): ?>
                                <div class="room-modal-details-value-indented"><?= htmlspecialchars($line) ?></div>
                            <?php endforeach; ?>
                        </div>
                        <div class="room-modal-details-row">
                            <span class="room-modal-details-label">Price:</span>
                            <?php foreach (preg_split('/\r?\n/', $room['price']) as $line): ?>
                                <div class="room-modal-details-value-indented"><?= htmlspecialchars($line) ?></div>
                            <?php endforeach; ?>
                        </div>
                        <div class="room-modal-details-row">
                            <span class="room-modal-details-label">Status:</span>
                            <div class="room-modal-details-value-indented"><?= htmlspecialchars($room['status']) ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </main>
    </div>
    <script>
    // Modal open/close logic for Add Room
    const openModalBtn = document.getElementById('openAddRoomModal');
    const modal = document.getElementById('addRoomModal');
    const closeModalBtn = document.getElementById('closeAddRoomModal');
    openModalBtn.onclick = function() { modal.style.display = 'flex'; };
    closeModalBtn.onclick = function() { modal.style.display = 'none'; };
    <?php if (!empty($add_room_error)): ?>
    modal.style.display = 'flex';
    <?php endif; ?>
    // Room Details Modal logic
    function openRoomModal(roomId) {
        document.getElementById('roomModal'+roomId).style.display = 'flex';
        window["carouselIndex"+roomId] = 0;
        showCarouselImg(roomId, 0);
    }
    function closeRoomModal(roomId) {
        document.getElementById('roomModal'+roomId).style.display = 'none';
    }
    function showCarouselImg(roomId, idx) {
        let imgs = document.querySelectorAll('#roomModal'+roomId+' .carousel-img');
        for (let i=0; i<imgs.length; i++) {
            imgs[i].style.display = (i===idx) ? 'block' : 'none';
        }
        window["carouselIndex"+roomId] = idx;
    }
    function carouselPrev(roomId) {
        let imgs = document.querySelectorAll('#roomModal'+roomId+' .carousel-img');
        let idx = window["carouselIndex"+roomId] || 0;
        idx = (idx-1+imgs.length)%imgs.length;
        showCarouselImg(roomId, idx);
    }
    function carouselNext(roomId) {
        let imgs = document.querySelectorAll('#roomModal'+roomId+' .carousel-img');
        let idx = window["carouselIndex"+roomId] || 0;
        idx = (idx+1)%imgs.length;
        showCarouselImg(roomId, idx);
    }
    // Auto-resize for all textareas (description, facility, price)
    function setupAutoResizeTextarea(id) {
        const textarea = document.getElementById(id);
        if (!textarea) return;
        const defaultHeight = 38;
        textarea.setAttribute('rows', 1);
        textarea.style.overflowY = 'hidden';
        textarea.style.height = defaultHeight + 'px';
        textarea.addEventListener('focus', function() {
            // Expand to fit content
            this.style.height = defaultHeight + 'px';
            this.style.height = (this.scrollHeight) + 'px';
        });
        textarea.addEventListener('input', function() {
            this.style.height = defaultHeight + 'px';
            this.style.height = (this.scrollHeight) + 'px';
        });
        textarea.addEventListener('blur', function() {
            // Shrink back to single line, hide overflow
            this.style.height = defaultHeight + 'px';
            this.scrollTop = 0;
        });
        // On page load, keep single line
        window.addEventListener('DOMContentLoaded', function() {
            textarea.style.height = defaultHeight + 'px';
        });
    }
    ['description','facility','price','room_type'].forEach(setupAutoResizeTextarea);
    // Auto-resize price textarea to grow only as user adds lines
    const priceTextarea = document.getElementById('price');
    if (priceTextarea) {
        priceTextarea.setAttribute('rows', 1);
        priceTextarea.style.overflowY = 'hidden';
        priceTextarea.addEventListener('input', function() {
            this.style.height = '38px'; // reset to single-line
            this.style.height = (this.scrollHeight) + 'px';
        });
        // Trigger resize on load if value has line breaks
        window.addEventListener('DOMContentLoaded', function() {
            priceTextarea.dispatchEvent(new Event('input'));
        });
    }
    // Edit Room Modal logic (admin only)
    function openEditRoomModal(roomId) {
        var modal = document.getElementById('editRoomModal'+roomId);
        if (modal) modal.style.display = 'flex';
        // Auto-resize all textareas in the edit modal
        ['edit_room_type_'+roomId,'edit_description_'+roomId,'edit_facility_'+roomId,'edit_price_'+roomId].forEach(function(id) {
            var textarea = document.getElementById(id);
            if (textarea) {
                const defaultHeight = 38;
                textarea.setAttribute('rows', 1);
                textarea.style.overflowY = 'hidden';
                textarea.style.height = defaultHeight + 'px';
                textarea.addEventListener('focus', function() {
                    this.style.height = defaultHeight + 'px';
                    this.style.height = (this.scrollHeight) + 'px';
                });
                textarea.addEventListener('input', function() {
                    this.style.height = defaultHeight + 'px';
                    this.style.height = (this.scrollHeight) + 'px';
                });
                textarea.addEventListener('blur', function() {
                    this.style.height = defaultHeight + 'px';
                    this.scrollTop = 0;
                });
                // On modal open, keep single line
                textarea.style.height = defaultHeight + 'px';
            }
        });
    }
    function closeEditRoomModal(roomId) {
        var modal = document.getElementById('editRoomModal'+roomId);
        if (modal) modal.style.display = 'none';
    }
    </script>
</body>
</html>
