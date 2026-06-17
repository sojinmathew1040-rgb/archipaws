<?php
require_once '../db.php';
if (!isAdmin()) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: product_manager.php');
    exit;
}

$product_id = $_GET['id'];

// Handle Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_product'])) {
        $title = $_POST['title'];
        $desc = $_POST['description'];
        $price = $_POST['price'];
        $old_price = $_POST['old_price'] ?: null;
        $category = $_POST['category'];
        $stock = (int) $_POST['stock_quantity'];
        $is_trending = isset($_POST['is_trending']) ? 1 : 0;

        $badge = '';
        $stock_status = 'In Stock';
        if ($stock <= 0) {
            $badge = 'sold-out';
            $stock_status = 'Sold Out';
        } elseif ($stock <= 5) {
            $badge = 'sale'; // Using sale color for limited stock warning
            $stock_status = 'Limited Stock';
        } elseif (!empty($_POST['badge'])) {
            $badge = $_POST['badge'];
        }

        $stmt = $pdo->prepare("UPDATE products SET title=?, description=?, price=?, old_price=?, category=?, badge=?, stock_status=?, stock_quantity=?, is_trending=? WHERE id=?");
        $stmt->execute([$title, $desc, $price, $old_price, $category, $badge, $stock_status, $stock, $is_trending, $product_id]);

        // Save specifications
        $pdo->prepare("DELETE FROM product_specifications WHERE product_id = ?")->execute([$product_id]);
        if (isset($_POST['spec_names']) && isset($_POST['spec_values'])) {
            $spec_names = $_POST['spec_names'];
            $spec_values = $_POST['spec_values'];
            for ($i = 0; $i < count($spec_names); $i++) {
                $s_name = trim($spec_names[$i]);
                $s_val = trim($spec_values[$i]);
                if ($s_name !== '' && $s_val !== '') {
                    $pdo->prepare("INSERT INTO product_specifications (product_id, spec_name, spec_value) VALUES (?, ?, ?)")
                        ->execute([$product_id, $s_name, $s_val]);
                }
            }
        }

        // Save variations
        $pdo->prepare("DELETE FROM product_variations WHERE product_id = ?")->execute([$product_id]);
        if (isset($_POST['var_names']) && isset($_POST['var_values'])) {
            $var_names = $_POST['var_names'];
            $var_values = $_POST['var_values'];
            $var_prices = $_POST['var_prices'] ?? [];
            $var_stocks = $_POST['var_stocks'] ?? [];
            $var_heights = $_POST['var_heights'] ?? [];
            $var_widths = $_POST['var_widths'] ?? [];
            for ($i = 0; $i < count($var_names); $i++) {
                $v_name = trim($var_names[$i]);
                $v_val = trim($var_values[$i]);
                $v_price = (float)($var_prices[$i] ?? 0.00);
                $v_stock = (int)($var_stocks[$i] ?? 10);
                $v_height = ($var_heights[$i] !== '') ? (float)$var_heights[$i] : null;
                $v_width = ($var_widths[$i] !== '') ? (float)$var_widths[$i] : null;
                if ($v_name !== '' && $v_val !== '') {
                    $pdo->prepare("INSERT INTO product_variations (product_id, variation_name, variation_value, price_modifier, stock_quantity, height, width) VALUES (?, ?, ?, ?, ?, ?, ?)")
                        ->execute([$product_id, $v_name, $v_val, $v_price, $v_stock, $v_height, $v_width]);
                }
            }
        }

        // Image uploads (grouped by color)
        if (isset($_POST['image_colors'])) {
            $image_colors = $_POST['image_colors'];
            
            // Find current max sort_order
            $sort_stmt = $pdo->prepare("SELECT MAX(sort_order) FROM product_images WHERE product_id = ?");
            $sort_stmt->execute([$product_id]);
            $max_sort = (int)$sort_stmt->fetchColumn();
            
            $sort_order = $max_sort + 1;
            foreach ($image_colors as $idx => $color) {
                $color_val = trim($color) !== '' ? trim($color) : null;
                $file_key = "images_" . $idx;
                if (isset($_FILES[$file_key])) {
                    $file_count = count($_FILES[$file_key]['name']);
                    for ($i = 0; $i < $file_count; $i++) {
                        if ($_FILES[$file_key]['error'][$i] === 0) {
                            $filename = time() . '_' . $idx . '_' . $i . '_' . $_FILES[$file_key]['name'][$i];
                            move_uploaded_file($_FILES[$file_key]['tmp_name'][$i], '../uploads/' . $filename);
                            $img_path = 'uploads/' . $filename;
                            $pdo->prepare("INSERT INTO product_images (product_id, image_path, sort_order, color_value) VALUES (?, ?, ?, ?)")
                                ->execute([$product_id, $img_path, $sort_order++, $color_val]);
                        }
                    }
                }
            }
        }

        header('Location: product_manager.php');
        exit;
    }
}

$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();
$categories = $pdo->query("SELECT * FROM categories ORDER BY sort_order ASC")->fetchAll();

$img_stmt = $pdo->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order ASC");
$img_stmt->execute([$product_id]);
$product_images = $img_stmt->fetchAll();

$spec_stmt = $pdo->prepare("SELECT * FROM product_specifications WHERE product_id = ? ORDER BY id ASC");
$spec_stmt->execute([$product_id]);
$product_specs = $spec_stmt->fetchAll();

$var_stmt = $pdo->prepare("SELECT * FROM product_variations WHERE product_id = ? ORDER BY id ASC");
$var_stmt->execute([$product_id]);
$product_vars = $var_stmt->fetchAll();

$existing_colors = [];
$existing_sizes = [];
foreach ($product_vars as $var) {
    if ($var['variation_name'] === 'Combination') {
        $parts = explode('/', $var['variation_value']);
        if (count($parts) === 2) {
            $existing_colors[] = trim($parts[0]);
            $existing_sizes[] = trim($parts[1]);
        } else {
            $existing_colors[] = trim($var['variation_value']);
        }
    }
}
$existing_colors = array_values(array_unique($existing_colors));
$existing_sizes = array_values(array_unique($existing_sizes));
$colors_str = implode(', ', $existing_colors);
$sizes_str = implode(', ', $existing_sizes);

if (!$product) {
    header('Location: product_manager.php');
    exit;
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/admin.css">
    <!-- Quill Rich Text Editor CDN -->
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
    <!-- SortableJS CDN for Drag-and-Drop -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
    <style>
        .ql-container {
            font-family: 'Quicksand', sans-serif;
            font-size: 14px;
        }
        .ql-toolbar.ql-snow {
            border: 1px solid #ddd;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
            background: #fafafa;
        }
        .ql-container.ql-snow {
            border: 1px solid #ddd;
            border-bottom-left-radius: 10px;
            border-bottom-right-radius: 10px;
        }
    </style>
</head>

<body>
    <div class="admin-container">
        <div class="sidebar">
            <h2>🐶 ARCHIPAWS Pro</h2>
            <ul>
                <li><a href="index.php">Dashboard</a></li>
                <li><a href="hero_manager.php">Hero Slider</a></li>
                <li><a href="category_manager.php">Categories</a></li>
                <li><a href="product_manager.php" class="active">Products</a></li>
                <li><a href="deal_manager.php">Deal Of The Day</a></li>
                <li><a href="testimonial_manager.php">Testimonials</a></li>
                <li><a href="orders.php">Orders</a></li>
                <li><a href="review_manager.php">Reviews</a></li>
                <li><a href="customizations.php">Customizations</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
        <div class="main-content">
            <div class="header-top">
                <h1>Edit Product #<?= $product['id'] ?></h1>
                <a href="product_manager.php" class="btn-primary" style="text-decoration:none;">Back to Products</a>
            </div>

            <div class="card">
                <h3>Update Details</h3>
                <form method="POST" enctype="multipart/form-data" style="margin-top:20px;">
                    <div style="display:flex; gap:20px;">
                        <div class="form-group" style="flex:2;">
                            <label>Title</label>
                            <input type="text" name="title" value="<?= htmlspecialchars($product['title']) ?>" required>
                        </div>
                        <div class="form-group" style="flex:1;">
                            <label>Price</label>
                            <input type="number" step="0.01" name="price"
                                value="<?= htmlspecialchars($product['price']) ?>" required>
                        </div>
                        <div class="form-group" style="flex:1;">
                            <label>Old Price (optional)</label>
                            <input type="number" step="0.01" name="old_price"
                                value="<?= htmlspecialchars($product['old_price'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <div id="description-editor" style="height: 150px; background: #fff;"></div>
                        <input type="hidden" name="description" id="description-input">
                    </div>
                    <div style="display:flex; gap:20px;">
                        <div class="form-group" style="flex:1;">
                            <label>Category</label>
                            <select name="category" required>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= htmlspecialchars($cat['name']) ?>"
                                        <?= $product['category'] === $cat['name'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group" style="flex:1;">
                            <label>Stock Qty</label>
                            <input type="number" name="stock_quantity"
                                value="<?= htmlspecialchars($product['stock_quantity'] ?? 0) ?>" required>
                        </div>
                        <div class="form-group" style="flex:1;">
                            <label>Badge</label>
                            <select name="badge">
                                <option value="" <?= $product['badge'] == '' ? 'selected' : '' ?>>Auto (Based on Stock)
                                </option>
                                <option value="new" <?= $product['badge'] == 'new' ? 'selected' : '' ?>>New</option>
                            </select>
                        </div>
                        <div class="form-group" style="flex:1; display:flex; align-items:center; gap:10px;">
                            <input type="checkbox" name="is_trending" id="is_trending" value="1"
                                <?= isset($product['is_trending']) && $product['is_trending'] ? 'checked' : '' ?>
                                style="width:20px;height:20px;cursor:pointer;">
                            <label for="is_trending" style="margin-bottom:0; cursor:pointer;">Trending</label>
                        </div>
                    </div>
                    
                    <div class="form-group" style="margin-top: 20px;">
                        <label>Current Product Images (Drag to Reorder, Click ✖ to Delete Instantly)</label>
                        <?php if (!empty($product_images)): ?>
                            <div id="image-sortable-grid" style="display: flex; gap: 15px; flex-wrap: wrap; margin-bottom: 15px;">
                                <?php foreach ($product_images as $img): ?>
                                    <div class="sortable-image-item" data-id="<?= $img['id'] ?>" style="position: relative; width: 110px; text-align: center; border: 1px solid #ddd; padding: 8px; border-radius: 10px; background: #fafafa; cursor: grab; display: flex; flex-direction: column; gap: 5px; align-items: center;">
                                        <img src="../<?= htmlspecialchars($img['image_path']) ?>" 
                                             style="width: 90px; height: 90px; object-fit: cover; border-radius: 8px; pointer-events: none;">
                                        
                                        <!-- Color Mapping Dropdown -->
                                        <select class="image-color-select" onchange="updateImageColor(<?= $img['id'] ?>, this.value)" style="width: 100%; font-size: 11px; padding: 3px; border-radius: 4px; border: 1px solid #ccc; cursor: pointer;">
                                            <option value="">General</option>
                                            <?php foreach ($existing_colors as $color): ?>
                                                <option value="<?= htmlspecialchars($color) ?>" <?= ($img['color_value'] === $color) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($color) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>

                                        <button type="button" class="btn-instant-delete" onclick="deleteImageInstantly(<?= $img['id'] ?>, this)" 
                                                style="position: absolute; top: -5px; right: -5px; width: 22px; height: 22px; border-radius: 50%; background: #ff3b30; color: white; border: none; font-size: 12px; font-weight: bold; cursor: pointer; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 5px rgba(0,0,0,0.2);">✖</button>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div id="image-sortable-grid" style="display: flex; gap: 15px; flex-wrap: wrap; margin-bottom: 15px;"></div>
                            <p id="no-images-text" style="font-size: 14px; color: #888; margin-bottom: 15px;">No images uploaded for this product.</p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label>Upload New Images (Optional, Grouped by Color)</label>
                        <div id="image-upload-rows" style="display:flex; flex-direction:column; gap:15px; margin-bottom:10px;">
                            <div class="upload-row" data-index="0" style="display:flex; gap:10px; align-items:center; background:#fafafa; padding:10px; border-radius:8px; border:1px solid #eee;">
                                <div style="flex:2; display:flex; flex-direction:column; gap:4px;">
                                    <span style="font-size:11px; font-weight:600; color:#666;">Choose Images (Multiple allowed)</span>
                                    <input type="file" name="images_0[]" multiple accept="image/*">
                                </div>
                                <div style="flex:1; display:flex; flex-direction:column; gap:4px;">
                                    <span style="font-size:11px; font-weight:600; color:#666;">Associated Color</span>
                                    <input type="text" name="image_colors[0]" placeholder="e.g. Red, Blue, or leave empty" style="padding: 6px; border: 1px solid #ddd; border-radius: 6px; background:#fff;">
                                </div>
                                <button type="button" class="btn-danger" onclick="removeUploadRow(this)" style="padding: 8px 12px; display:none; margin-top: 15px;">✖</button>
                            </div>
                        </div>
                        <button type="button" onclick="addUploadRow()" style="background:#fafafa; border:1px dashed #d6a86c; color:#1d1d1f; padding:8px 15px; border-radius:8px; cursor:pointer; font-weight:600; font-size:13px; border-style:dashed; outline:none;">+ Add Another Color Group</button>
                    </div>

                    <!-- Specifications Section -->
                    <div class="form-group" style="margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px;">
                        <h4 style="font-size: 16px; margin-bottom: 15px; color: #1d1d1f; display: flex; align-items: center; gap: 8px;">📋 Product Specifications</h4>
                        <div id="specs-container">
                            <?php if (!empty($product_specs)): ?>
                                <?php foreach ($product_specs as $spec): ?>
                                    <div class="spec-row" style="display: flex; gap: 15px; margin-bottom: 10px; align-items: center;">
                                        <input type="text" name="spec_names[]" value="<?= htmlspecialchars($spec['spec_name']) ?>" placeholder="Specification Name (e.g. Weight)" style="flex: 1;" required>
                                        <input type="text" name="spec_values[]" value="<?= htmlspecialchars($spec['spec_value']) ?>" placeholder="Specification Value (e.g. 5 kg)" style="flex: 1;" required>
                                        <button type="button" class="btn-danger" onclick="removeRow(this)" style="padding: 10px 15px; font-size: 14px;">Remove</button>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <button type="button" class="btn-primary" onclick="addSpecRow()" style="padding: 8px 16px; font-size: 13px; background: #555; margin-top: 5px; border-radius: 8px;">+ Add Specification</button>
                    </div>

                    <!-- Variations Section -->
                    <div class="form-group" style="margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px; padding-bottom: 20px;">
                        <h4 style="font-size: 16px; margin-bottom: 15px; color: #1d1d1f; display: flex; align-items: center; gap: 8px;">🎨 Product Variations Combination Builder</h4>
                        <p style="font-size: 13px; color: #666; margin-bottom: 15px;">Enter colors and sizes separated by commas to generate the combination matrix (e.g. Colors: Red, Blue | Sizes: S, M).</p>
                        
                        <div style="display:flex; gap:20px; margin-bottom: 15px; flex-wrap: wrap; align-items: flex-end;">
                            <div class="form-group" style="flex:2; min-width: 200px; margin-bottom: 0;">
                                <label>Colors (Comma separated)</label>
                                <input type="text" id="matrix-colors" value="<?= htmlspecialchars($colors_str) ?>" placeholder="e.g. Red, Blue, Black" style="margin-bottom:0;">
                            </div>
                            <div class="form-group" style="flex:2; min-width: 200px; margin-bottom: 0;">
                                <label>Sizes (Comma separated)</label>
                                <input type="text" id="matrix-sizes" value="<?= htmlspecialchars($sizes_str) ?>" placeholder="e.g. S, M, L" style="margin-bottom:0;">
                            </div>
                            <div style="flex: 1; min-width: 200px;">
                                <button type="button" class="btn-primary" onclick="generateMatrix()" style="background:#555; padding: 12px 20px; border-radius: 8px; border:none; color:white; font-weight:600; cursor:pointer; width: 100%;">Re-generate Matrix</button>
                            </div>
                        </div>

                        <div id="matrix-container" style="background:#fafafa; border:1px solid #eee; padding:15px; border-radius:12px; min-height: 50px; margin-bottom: 20px;">
                            <table style="width:100%; border-collapse: collapse;">
                                <thead>
                                    <tr style="border-bottom: 2px solid #ddd; text-align: left; font-size:14px; color:#555;">
                                        <th style="padding: 10px 5px;">Combination</th>
                                        <th style="padding: 10px 5px;">Price Modifier (+/- ₹)</th>
                                        <th style="padding: 10px 5px;">Stock Qty</th>
                                        <th style="padding: 10px 5px;">Height (cm)</th>
                                        <th style="padding: 10px 5px;">Width (cm)</th>
                                        <th style="padding: 10px 5px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="matrix-tbody">
                                    <?php if (!empty($product_vars)): ?>
                                        <?php foreach ($product_vars as $var): 
                                            $val = htmlspecialchars($var['variation_value']);
                                            $price_mod = htmlspecialchars($var['price_modifier']);
                                            $stock_qty = htmlspecialchars($var['stock_quantity']);
                                            $height = htmlspecialchars($var['height'] ?? '');
                                            $width = htmlspecialchars($var['width'] ?? '');
                                        ?>
                                            <tr class="variation-row" style="border-bottom: 1px solid #eee; font-size:14px;">
                                                <td style="padding: 10px 5px; font-weight:600; color:#333;">
                                                    <?= $val ?>
                                                    <input type="hidden" name="var_names[]" value="Combination">
                                                    <input type="hidden" name="var_values[]" value="<?= $val ?>">
                                                </td>
                                                <td style="padding: 10px 5px;">
                                                    <input type="number" step="0.01" name="var_prices[]" value="<?= $price_mod ?>" style="width:110px; padding: 8px; border-radius: 6px; border: 1px solid #ddd; box-sizing:border-box;" required>
                                                </td>
                                                <td style="padding: 10px 5px;">
                                                    <input type="number" name="var_stocks[]" value="<?= $stock_qty ?>" style="width:100px; padding: 8px; border-radius: 6px; border: 1px solid #ddd; box-sizing:border-box;" required>
                                                </td>
                                                <td style="padding: 10px 5px;">
                                                    <input type="number" step="0.1" name="var_heights[]" value="<?= $height ?>" placeholder="Height cm" style="width:100px; padding: 8px; border-radius: 6px; border: 1px solid #ddd; box-sizing:border-box;">
                                                </td>
                                                <td style="padding: 10px 5px;">
                                                    <input type="number" step="0.1" name="var_widths[]" value="<?= $width ?>" placeholder="Width cm" style="width:100px; padding: 8px; border-radius: 6px; border: 1px solid #ddd; box-sizing:border-box;">
                                                </td>
                                                <td style="padding: 10px 5px;">
                                                    <button type="button" class="btn-danger" onclick="removeRow(this)" style="padding: 8px 12px; font-size: 13px; border-radius:6px; cursor:pointer;">Remove</button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <button type="submit" name="update_product" class="btn-primary">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</body>
    <script>
        // Initialize Quill Editor
        const quill = new Quill('#description-editor', {
            theme: 'snow',
            placeholder: 'Write the product description here...',
            modules: {
                toolbar: [
                    ['bold', 'italic', 'underline'],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    ['clean']
                ]
            }
        });

        // Load existing content
        quill.root.innerHTML = <?= json_encode($product['description'] ?? '') ?>;

        // Sync Quill HTML content into hidden input on form submit
        const form = document.querySelector('form');
        form.addEventListener('submit', function(e) {
            const descInput = document.getElementById('description-input');
            let content = quill.root.innerHTML;
            if (content === '<p><br></p>') {
                content = '';
            }
            descInput.value = content;
        });

        // Initialize SortableJS for Image Reordering
        const sortableGrid = document.getElementById('image-sortable-grid');
        if (sortableGrid) {
            new Sortable(sortableGrid, {
                animation: 150,
                onEnd: function (evt) {
                    const order = [];
                    document.querySelectorAll('.sortable-image-item').forEach(item => {
                        order.push(item.dataset.id);
                    });
                    
                    fetch('ajax/reorder_images.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ order: order })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (!data.success) {
                            alert('Failed to save image order: ' + (data.message || 'Unknown error'));
                        }
                    })
                    .catch(err => {
                        console.error('Error reordering images:', err);
                        alert('Failed to reorder images due to connection error.');
                    });
                }
            });
        }

        // Instant Image Delete
        function deleteImageInstantly(imgId, button) {
            if (!confirm('Are you sure you want to delete this image instantly?')) return;
            
            fetch('ajax/delete_image.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ id: imgId })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const item = button.closest('.sortable-image-item');
                    item.style.transition = 'all 0.3s ease';
                    item.style.opacity = '0';
                    item.style.transform = 'scale(0.8)';
                    setTimeout(() => {
                        item.remove();
                        if (document.querySelectorAll('.sortable-image-item').length === 0) {
                            const grid = document.getElementById('image-sortable-grid');
                            let text = document.getElementById('no-images-text');
                            if (!text) {
                                text = document.createElement('p');
                                text.id = 'no-images-text';
                                text.style.fontSize = '14px';
                                text.style.color = '#888';
                                text.style.marginBottom = '15px';
                                text.innerText = 'No images uploaded for this product.';
                                grid.parentNode.appendChild(text);
                            }
                        }
                    }, 300);
                } else {
                    alert('Failed to delete image: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(err => {
                console.error('Error deleting image:', err);
                alert('Failed to delete image due to connection error.');
            });
        }

        // Image rows management
        let uploadRowIndex = 0;
        function addUploadRow() {
            uploadRowIndex++;
            const container = document.getElementById('image-upload-rows');
            const row = document.createElement('div');
            row.className = 'upload-row';
            row.dataset.index = uploadRowIndex;
            row.style.display = 'flex';
            row.style.gap = '10px';
            row.style.alignItems = 'center';
            row.style.background = '#fafafa';
            row.style.padding = '10px';
            row.style.borderRadius = '8px';
            row.style.border = '1px solid #eee';
            row.style.marginTop = '10px';
            row.innerHTML = `
                <div style="flex:2; display:flex; flex-direction:column; gap:4px;">
                    <span style="font-size:11px; font-weight:600; color:#666;">Choose Images (Multiple allowed)</span>
                    <input type="file" name="images_${uploadRowIndex}[]" multiple accept="image/*" required>
                </div>
                <div style="flex:1; display:flex; flex-direction:column; gap:4px;">
                    <span style="font-size:11px; font-weight:600; color:#666;">Associated Color</span>
                    <input type="text" name="image_colors[${uploadRowIndex}]" placeholder="e.g. Red, Blue, or leave empty" style="padding: 6px; border: 1px solid #ddd; border-radius: 6px; background:#fff;">
                </div>
                <button type="button" class="btn-danger" onclick="removeUploadRow(this)" style="padding: 8px 12px; margin-top: 15px;">✖</button>
            `;
            container.appendChild(row);
            
            const rows = container.querySelectorAll('.upload-row');
            if (rows.length > 1) {
                rows.forEach(r => {
                    const btn = r.querySelector('.btn-danger');
                    if (btn) btn.style.display = 'block';
                });
            }
        }

        function removeUploadRow(btn) {
            const container = document.getElementById('image-upload-rows');
            btn.closest('.upload-row').remove();
            
            const rows = container.querySelectorAll('.upload-row');
            if (rows.length === 1) {
                const deleteBtn = rows[0].querySelector('.btn-danger');
                if (deleteBtn) deleteBtn.style.display = 'none';
            }
        }

        // Dynamic Rows management
        function removeRow(btn) {
            btn.parentNode.remove();
        }

        function addSpecRow() {
            const container = document.getElementById('specs-container');
            const row = document.createElement('div');
            row.className = 'spec-row';
            row.style.display = 'flex';
            row.style.gap = '15px';
            row.style.marginBottom = '10px';
            row.style.alignItems = 'center';
            row.innerHTML = `
                <input type="text" name="spec_names[]" placeholder="Specification Name (e.g. Weight)" style="flex: 1;" required>
                <input type="text" name="spec_values[]" placeholder="Specification Value (e.g. 5 kg)" style="flex: 1;" required>
                <button type="button" class="btn-danger" onclick="removeRow(this)" style="padding: 10px 15px; font-size: 14px;">Remove</button>
            `;
            container.appendChild(row);
        }

        function updateImageColor(imgId, colorVal) {
            fetch('ajax/update_image_color.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ id: imgId, color_value: colorVal })
            })
            .then(res => res.json())
            .then(data => {
                if (!data.success) {
                    alert('Failed to save image color mapping: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(err => {
                console.error('Error saving image color mapping:', err);
                alert('Connection error. Failed to save color mapping.');
            });
        }

        // Generate Variations Matrix
        function generateMatrix() {
            const colorInput = document.getElementById('matrix-colors').value.trim();
            const sizeInput = document.getElementById('matrix-sizes').value.trim();
            
            const colors = colorInput ? colorInput.split(',').map(c => c.trim()).filter(c => c !== '') : [];
            const sizes = sizeInput ? sizeInput.split(',').map(s => s.trim()).filter(s => s !== '') : [];
            
            const tbody = document.getElementById('matrix-tbody');
            tbody.innerHTML = '';
            
            let combinations = [];
            if (colors.length > 0 && sizes.length > 0) {
                colors.forEach(c => {
                    sizes.forEach(s => {
                        combinations.push(`${c} / ${s}`);
                    });
                });
            } else if (colors.length > 0) {
                combinations = colors;
            } else if (sizes.length > 0) {
                combinations = sizes;
            }
            
            if (combinations.length === 0) {
                return;
            }
            
            combinations.forEach((comb) => {
                const tr = document.createElement('tr');
                tr.className = 'variation-row';
                tr.style.borderBottom = '1px solid #eee';
                tr.style.fontSize = '14px';
                tr.innerHTML = `
                    <td style="padding: 10px 5px; font-weight:600; color:#333;">
                        ${comb}
                        <input type="hidden" name="var_names[]" value="Combination">
                        <input type="hidden" name="var_values[]" value="${comb}">
                    </td>
                    <td style="padding: 10px 5px;">
                        <input type="number" step="0.01" name="var_prices[]" value="0.00" style="width:110px; padding: 8px; border-radius: 6px; border: 1px solid #ddd; box-sizing:border-box;" required>
                    </td>
                    <td style="padding: 10px 5px;">
                        <input type="number" name="var_stocks[]" value="10" style="width:100px; padding: 8px; border-radius: 6px; border: 1px solid #ddd; box-sizing:border-box;" required>
                    </td>
                    <td style="padding: 10px 5px;">
                        <input type="number" step="0.1" name="var_heights[]" placeholder="Height cm" style="width:100px; padding: 8px; border-radius: 6px; border: 1px solid #ddd; box-sizing:border-box;">
                    </td>
                    <td style="padding: 10px 5px;">
                        <input type="number" step="0.1" name="var_widths[]" placeholder="Width cm" style="width:100px; padding: 8px; border-radius: 6px; border: 1px solid #ddd; box-sizing:border-box;">
                    </td>
                    <td style="padding: 10px 5px;">
                        <button type="button" class="btn-danger" onclick="removeRow(this)" style="padding: 8px 12px; font-size: 13px; border-radius:6px; cursor:pointer;">Remove</button>
                    </td>
                `;
                tbody.appendChild(tr);
            });

            // Dynamically update color selects for current images
            updateImageColorSelects(colors);
        }

        function updateImageColorSelects(colors) {
            const selects = document.querySelectorAll('.image-color-select');
            selects.forEach(select => {
                const currentValue = select.value;
                select.innerHTML = '<option value="">General</option>';
                colors.forEach(color => {
                    const option = document.createElement('option');
                    option.value = color;
                    option.textContent = color;
                    if (color === currentValue) {
                        option.selected = true;
                    }
                    select.appendChild(option);
                });
            });
        }
    </script>
</html>