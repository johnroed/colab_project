<?php include '../dashboard_things/sidebar_form.php'; ?>
<?php
include '../includes_files/connection.php';
$items = [];
$sql = 'SELECT * FROM inventory_items ORDER BY created_at DESC';
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $item_id = $row['id'];
        $img_sql = "SELECT image_path FROM inventory_images WHERE inventory_id = $item_id ORDER BY image_id";
        $img_result = $conn->query($img_sql);
        $images = [];
        if ($img_result) {
            while ($img_row = $img_result->fetch_assoc()) {
                $images[] = $img_row['image_path'];
            }
        }
        $row['images'] = $images;
        $items[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory List</title>
    <link rel="stylesheet" href="inventory_list.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="main-content">
        <div class="section-title-box">
            <h1 class="section-title"><i class="fa-solid fa-boxes-stacked"></i> Inventory List</h1>
        </div>
        <div class="action-buttons">
            <button class="add-btn" id="openAddItemModal">
                <i class="fa fa-plus"></i> Add Item
            </button>
        </div>
        <!-- Inventory List content goes here -->
        <!-- Add Item Modal -->
        <div id="addItemModal" class="modal-overlay">
            <div class="modal-content">
                <div class="modal-header-row">
                    <h2>Add Inventory Item</h2>
                </div>
                <form id="addItemForm" action="inventory_list.php" method="POST" enctype="multipart/form-data">
                    <label for="itemName">Product Name</label>
                    <input type="text" id="itemName" name="itemName" required>
                    <label for="itemQty">Quantity</label>
                    <input type="number" id="itemQty" name="itemQty" min="0" required>
                    <label for="itemPrice">Price</label>
                    <input type="number" id="itemPrice" name="itemPrice" min="0" step="0.01">
                    <label for="itemDesc">Description</label>
                    <textarea id="itemDesc" name="itemDesc"></textarea>
                    <button type="button" id="uploadImageBtn" class="upload-btn">Upload Image</button>
                    <input type="file" id="itemImage" name="itemImage[]" accept="image/*" style="display:none;" multiple required>
                    <span id="selectedImageName" style="font-size:0.98rem; color:#1976d2; margin-left:8px;"></span>
                    <div class="modal-actions">
                        <button type="submit" class="submit-btn">Add Item</button>
                        <button type="button" class="cancel-btn" id="cancelAddItemModal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
        <!-- Product Details Modal -->
        <div id="detailsModal" class="modal-overlay">
            <div class="modal-content details-modal-content">
                <button class="close-modal" id="closeDetailsModal">&times;</button>
                <div class="modal-header-row">
                    <h2 id="detailsTitle" class="details-modal-title"></h2>
                </div>
                <div id="detailsCarousel" class="details-carousel"></div>
                <div class="details-content">
                    <div class="details-info">
                        <div class="detail-row">
                            <i class="fa-solid fa-boxes-stacked detail-icon"></i>
                            <span class="detail-label">Quantity:</span>
                            <span class="detail-value qty-value" id="detailsQty"></span>
                        </div>
                        <div class="detail-row">
                            <i class="fa-solid fa-tag detail-icon"></i>
                            <span class="detail-label">Price:</span>
                            <span class="detail-value price-value" id="detailsPrice"></span>
                        </div>
                        <div class="detail-row description-row" id="detailsDescRow" style="display: none;">
                            <i class="fa-solid fa-info-circle detail-icon"></i>
                            <span class="detail-label">Description:</span>
                            <span class="detail-value desc-value" id="detailsDesc"></span>
                        </div>
                    </div>
                    <div class="details-actions">
                        <a href="#" class="details-action-btn edit-stock-btn" id="detailsEditStock">
                            <i class="fa-solid fa-edit"></i> Edit Stock
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <!-- Inventory List Gallery -->
        <div class="inventory-gallery-grid">
            <?php if (count($items) > 0): ?>
                <?php foreach ($items as $item): ?>
                    <div class="inventory-card-outer">
                        <div class="inventory-card">
                            <div class="inventory-card-img-carousel">
                                <?php $imgs = $item['images']; if (count($imgs) === 0) { $imgs = ['https://placehold.co/400x250?text=No+Image']; } ?>
                                <?php foreach ($imgs as $idx => $img): ?>
                                    <img class="carousel-img" id="carouselImgInv<?= $item['id'] ?>-<?= $idx ?>" src="<?= htmlspecialchars($img) ?>" alt="Inventory Image" style="display:<?= $idx===0?'block':'none' ?>;">
                                <?php endforeach; ?>
                            </div>
                            <div class="inventory-card-content">
                                <div class="inventory-card-title"><?= htmlspecialchars($item['name']) ?></div>
                                
                                <div class="inventory-card-details">
                                    <div class="detail-row">
                                        <i class="fa-solid fa-boxes-stacked detail-icon"></i>
                                        <span class="detail-label">Quantity:</span>
                                        <span class="detail-value qty-value"><?= htmlspecialchars($item['quantity']) ?></span>
                                    </div>
                                    <div class="detail-row">
                                        <i class="fa-solid fa-tag detail-icon"></i>
                                        <span class="detail-label">Price:</span>
                                        <span class="detail-value price-value">₱<?= number_format($item['price'], 2) ?></span>
                                    </div>
                                    <?php if (!empty($item['description'])): ?>
                                    <div class="detail-row description-row">
                                        <i class="fa-solid fa-info-circle detail-icon"></i>
                                        <span class="detail-label">Description:</span>
                                        <span class="detail-value desc-value"><?= htmlspecialchars($item['description']) ?></span>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="inventory-card-actions">
                                    <button class="inventory-card-btn details-btn" data-item='<?= json_encode([
                                        "id" => $item["id"],
                                        "name" => $item["name"],
                                        "quantity" => $item["quantity"],
                                        "price" => $item["price"],
                                        "description" => $item["description"],
                                        "images" => $imgs
                                    ]) ?>'>
                                        <i class="fa-solid fa-eye"></i> Details
                                    </button>
                                    <a href="stock_in_out_form.php?item_id=<?= $item['id'] ?>" class="inventory-card-btn adjust-btn" style="text-decoration:none; display:inline-block;">
                                        <i class="fa-solid fa-edit"></i> Edit Stock
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="grid-column: 1/-1; text-align:center; color:#3a6073; font-size:1.1rem;">No inventory items found.</div>
            <?php endif; ?>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const openBtn = document.getElementById('openAddItemModal');
            const closeBtn = document.getElementById('closeAddItemModal');
            const cancelBtn = document.getElementById('cancelAddItemModal');
            const modal = document.getElementById('addItemModal');
            if (openBtn && modal) {
                openBtn.onclick = function() {
                    modal.style.display = 'flex';
                };
            }
            if (closeBtn && modal) {
                closeBtn.onclick = function() {
                    modal.style.display = 'none';
                };
            }
            if (cancelBtn && modal) {
                cancelBtn.onclick = function() {
                    modal.style.display = 'none';
                };
            }
            // Do NOT close modal when clicking outside (overlay)
            // Upload Image button logic
            const uploadBtn = document.getElementById('uploadImageBtn');
            const fileInput = document.getElementById('itemImage');
            const fileNameSpan = document.getElementById('selectedImageName');
            if (uploadBtn && fileInput) {
                uploadBtn.onclick = function() {
                    fileInput.click();
                };
                fileInput.onchange = function() {
                    if (fileInput.files && fileInput.files.length > 0) {
                        fileNameSpan.textContent = fileInput.files.length + ' files selected';
                    } else {
                        fileNameSpan.textContent = '';
                    }
                };
            }
            // Details modal logic
            const detailsModal = document.getElementById('detailsModal');
            const closeDetailsBtn = document.getElementById('closeDetailsModal');
            const detailsTitle = document.getElementById('detailsTitle');
            const detailsQty = document.getElementById('detailsQty');
            const detailsPrice = document.getElementById('detailsPrice');
            const detailsDesc = document.getElementById('detailsDesc');
            const detailsCarousel = document.getElementById('detailsCarousel');
            let detailsCarouselIdx = 0;
            let detailsCarouselImgs = [];
            document.querySelectorAll('.details-btn').forEach(function(btn) {
                btn.onclick = function() {
                    const item = JSON.parse(this.getAttribute('data-item'));
                    detailsTitle.textContent = item.name;
                    document.getElementById('detailsQty').textContent = item.quantity;
                    document.getElementById('detailsPrice').textContent = '₱' + parseFloat(item.price).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    
                    // Handle description
                    const descRow = document.getElementById('detailsDescRow');
                    const descValue = document.getElementById('detailsDesc');
                    if (item.description && item.description.trim() !== '') {
                        descValue.textContent = item.description;
                        descRow.style.display = 'flex';
                    } else {
                        descRow.style.display = 'none';
                    }
                    
                    // Set up edit stock link
                    document.getElementById('detailsEditStock').href = 'stock_in_out_form.php?item_id=' + item.id;
                    
                    // Set up carousel
                    detailsCarousel.innerHTML = '';
                    detailsCarouselImgs = item.images && item.images.length > 0 ? item.images : ['https://placehold.co/400x250?text=No+Image'];
                    detailsCarouselImgs = detailsCarouselImgs.map(function(path) {
                        if (typeof path === 'string' && path.startsWith('uploads/')) {
                            return './' + path;
                        }
                        return path;
                    });
                    detailsCarouselIdx = 0;
                    
                    // Create carousel container
                    const carouselContainer = document.createElement('div');
                    carouselContainer.className = 'details-carousel-container';
                    carouselContainer.style.position = 'relative';
                    carouselContainer.style.width = '100%';
                    carouselContainer.style.height = '300px';
                    carouselContainer.style.background = '#e0eafc';
                    carouselContainer.style.borderRadius = '12px';
                    carouselContainer.style.overflow = 'hidden';
                    carouselContainer.style.marginBottom = '20px';
                    
                    if (detailsCarouselImgs.length > 1) {
                        const leftBtn = document.createElement('button');
                        leftBtn.className = 'carousel-arrow left';
                        leftBtn.innerHTML = '<i class="fa fa-chevron-left"></i>';
                        leftBtn.onclick = function(e) {
                            e.stopPropagation();
                            detailsCarouselIdx = (detailsCarouselIdx - 1 + detailsCarouselImgs.length) % detailsCarouselImgs.length;
                            updateDetailsCarouselImg();
                        };
                        carouselContainer.appendChild(leftBtn);
                    }
                    
                    const img = document.createElement('img');
                    img.className = 'carousel-img';
                    img.id = 'detailsCarouselImg';
                    img.src = detailsCarouselImgs[0];
                    img.alt = 'Inventory Image';
                    img.style.width = '100%';
                    img.style.height = '100%';
                    img.style.objectFit = 'contain';
                    carouselContainer.appendChild(img);
                    
                    if (detailsCarouselImgs.length > 1) {
                        const rightBtn = document.createElement('button');
                        rightBtn.className = 'carousel-arrow right';
                        rightBtn.innerHTML = '<i class="fa fa-chevron-right"></i>';
                        rightBtn.onclick = function(e) {
                            e.stopPropagation();
                            detailsCarouselIdx = (detailsCarouselIdx + 1) % detailsCarouselImgs.length;
                            updateDetailsCarouselImg();
                        };
                        carouselContainer.appendChild(rightBtn);
                    }
                    
                    detailsCarousel.appendChild(carouselContainer);
                    detailsModal.style.display = 'flex';
                };
            });
            function updateDetailsCarouselImg() {
                const img = document.getElementById('detailsCarouselImg');
                if (img) img.src = detailsCarouselImgs[detailsCarouselIdx];
            }
            if (closeDetailsBtn && detailsModal) {
                closeDetailsBtn.onclick = function() {
                    detailsModal.style.display = 'none';
                };
            }
        });
    </script>
    <script>
        // Inventory image carousel logic
        function carouselPrev(invId) {
            let imgs = document.querySelectorAll('#carouselImgInv'+invId+'-0, #carouselImgInv'+invId+'-1, #carouselImgInv'+invId+'-2, #carouselImgInv'+invId+'-3, #carouselImgInv'+invId+'-4, #carouselImgInv'+invId+'-5, #carouselImgInv'+invId+'-6, #carouselImgInv'+invId+'-7, #carouselImgInv'+invId+'-8, #carouselImgInv'+invId+'-9');
            let idx = window['carouselIndexInv'+invId] || 0;
            let total = imgs.length;
            idx = (idx-1+total)%total;
            for (let i=0; i<total; i++) imgs[i].style.display = (i===idx)?'block':'none';
            window['carouselIndexInv'+invId] = idx;
        }
        function carouselNext(invId) {
            let imgs = document.querySelectorAll('#carouselImgInv'+invId+'-0, #carouselImgInv'+invId+'-1, #carouselImgInv'+invId+'-2, #carouselImgInv'+invId+'-3, #carouselImgInv'+invId+'-4, #carouselImgInv'+invId+'-5, #carouselImgInv'+invId+'-6, #carouselImgInv'+invId+'-7, #carouselImgInv'+invId+'-8, #carouselImgInv'+invId+'-9');
            let idx = window['carouselIndexInv'+invId] || 0;
            let total = imgs.length;
            idx = (idx+1)%total;
            for (let i=0; i<total; i++) imgs[i].style.display = (i===idx)?'block':'none';
            window['carouselIndexInv'+invId] = idx;
        }
    </script>
</body>
</html> 