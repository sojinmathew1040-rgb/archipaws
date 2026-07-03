<?php
require_once '../db.php';
if (!isAdmin()) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_product'])) {
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

        $stmt = $pdo->prepare("INSERT INTO products (title, description, price, old_price, category, badge, stock_status, stock_quantity, is_trending) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $desc, $price, $old_price, $category, $badge, $stock_status, $stock, $is_trending]);
        $product_id = $pdo->lastInsertId();

        // Image uploads (grouped by color)
        if (isset($_POST['image_colors'])) {
            $image_colors = $_POST['image_colors'];
            $sort_order = 0;
            foreach ($image_colors as $idx => $color) {
                $color_val = trim($color) !== '' ? trim($color) : null;
                $file_key = "images_" . $idx;
                if (isset($_FILES[$file_key])) {
                    $file_count = count($_FILES[$file_key]['name']);
                    for ($i = 0; $i < $file_count; $i++) {
                        if ($_FILES[$file_key]['error'][$i] === 0) {
                            $filename = time() . '_' . $idx . '_' . $i . '_' . $_FILES[$file_key]['name'][$i];
                            $target_dir = '../uploads/';
                            if (!file_exists($target_dir)) {
                                mkdir($target_dir, 0755, true);
                            }
                            move_uploaded_file($_FILES[$file_key]['tmp_name'][$i], $target_dir . $filename);
                            $img_path = 'uploads/' . $filename;
                            $pdo->prepare("INSERT INTO product_images (product_id, image_path, sort_order, color_value) VALUES (?, ?, ?, ?)")
                                ->execute([$product_id, $img_path, $sort_order++, $color_val]);
                        }
                    }
                }
            }
        }

        // Save combinations to product_variations
        if (isset($_POST['comb_values']) && isset($_POST['comb_stocks'])) {
            $comb_values = $_POST['comb_values'];
            $comb_prices = $_POST['comb_prices'];
            $comb_stocks = $_POST['comb_stocks'];
            $comb_heights = $_POST['comb_heights'] ?? [];
            $comb_widths = $_POST['comb_widths'] ?? [];
            
            for ($i = 0; $i < count($comb_values); $i++) {
                $c_val = trim($comb_values[$i]);
                $c_price = (float)($comb_prices[$i] ?? 0.00);
                $c_stock = (int)($comb_stocks[$i] ?? 10);
                $c_height = ($comb_heights[$i] !== '') ? (float)$comb_heights[$i] : null;
                $c_width = ($comb_widths[$i] !== '') ? (float)$comb_widths[$i] : null;
                if ($c_val !== '') {
                    $pdo->prepare("INSERT INTO product_variations (product_id, variation_name, variation_value, price_modifier, stock_quantity, height, width) VALUES (?, 'Combination', ?, ?, ?, ?, ?)")
                        ->execute([$product_id, $c_val, $c_price, $c_stock, $c_height, $c_width]);
                }
            }
        }
    } elseif (isset($_POST['delete_product'])) {
        $product_id = $_POST['product_id'];

        // Fetch images to delete from filesystem
        $stmt = $pdo->prepare("SELECT image_path FROM product_images WHERE product_id = ?");
        $stmt->execute([$product_id]);
        $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($images as $img) {
            $file_path = '../' . $img['image_path'];
            if (file_exists($file_path) && is_file($file_path)) {
                unlink($file_path);
            }
        }

        // Delete images from DB
        $pdo->prepare("DELETE FROM product_images WHERE product_id = ?")->execute([$product_id]);

        // Delete product
        $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$product_id]);
    }
    header('Location: product_manager.php');
    exit;
}

$products = $pdo->query("SELECT * FROM products ORDER BY id DESC")->fetchAll();
$categories = $pdo->query("SELECT * FROM categories ORDER BY sort_order ASC")->fetchAll();
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Manager</title>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/admin.css">
    <!-- Quill Rich Text Editor CDN -->
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
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
                <li><a href="sales_report.php">Sales Report</a></li>
                <li><a href="review_manager.php">Reviews</a></li>
                <li><a href="customizations.php">Customizations</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
        <div class="main-content">
            <div class="header-top">
                <h1>Product Manager</h1>
            </div>

            <div class="card">
                <h3>Add New Product</h3>
                <form method="POST" enctype="multipart/form-data" style="margin-top:20px;">
                    <div style="display:flex; gap:20px;">
                        <div class="form-group" style="flex:2;">
                            <label>Title</label>
                            <input type="text" name="title" required>
                        </div>
                        <div class="form-group" style="flex:1;">
                            <label>Price</label>
                            <input type="number" step="0.01" name="price" required>
                        </div>
                        <div class="form-group" style="flex:1;">
                            <label>Old Price (optional)</label>
                            <input type="number" step="0.01" name="old_price">
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
                                    <option value="<?= htmlspecialchars($cat['name']) ?>">


                                        <?= htmlspecialchars($cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group" style="flex:1;">
                            <label>Stock Qty</label>
                            <input type="number" name="stock_quantity" value="10" required>
                        </div>
                        <div class="form-group" style="flex:1;">
                            <label>Badge</label>
                            <select name="badge">
                                <option value="">Auto (Based on Stock)</option>
                                <option value="new">New</option>
                            </select>
                        </div>
                        <div class="form-group" style="flex:1; display:flex; align-items:center; gap:10px;">
                            <input type="checkbox" name="is_trending" id="is_trending" value="1"
                                style="width:20px;height:20px;cursor:pointer;">
                            <label for="is_trending" style="margin-bottom:0; cursor:pointer;">Trending</label>
                        </div>
                        <div class="form-group" style="flex:2;">
                            <label>Product Images (Grouped by Color)</label>
                            <div id="image-upload-rows" style="display:flex; flex-direction:column; gap:15px;">
                                <div class="upload-row" data-index="0" style="display:flex; gap:10px; align-items:center; background:#fafafa; padding:10px; border-radius:8px; border:1px solid #eee;">
                                    <div style="flex:2; display:flex; flex-direction:column; gap:4px;">
                                        <span style="font-size:11px; font-weight:600; color:#666;">Choose Images (Multiple allowed)</span>
                                        <input type="file" name="images_0[]" multiple accept="image/*" required>
                                    </div>
                                    <div style="flex:1; display:flex; flex-direction:column; gap:4px;">
                                        <span style="font-size:11px; font-weight:600; color:#666;">Associated Color</span>
                                        <input type="text" name="image_colors[0]" placeholder="e.g. Red, Blue, or leave empty" style="padding: 6px; border: 1px solid #ddd; border-radius: 6px;">
                                    </div>
                                    <button type="button" class="btn-danger" onclick="removeUploadRow(this)" style="padding: 8px 12px; display:none; margin-top: 15px;">✖</button>
                                </div>
                            </div>
                            <button type="button" onclick="addUploadRow()" style="margin-top:10px; background:#fafafa; border:1px dashed #d6a86c; color:#1d1d1f; padding:8px 15px; border-radius:8px; cursor:pointer; font-weight:600; font-size:13px; border-style:dashed; outline:none;">+ Add Another Color Group</button>
                        </div>
                    </div>

                    <!-- Variations Combination Matrix Section -->
                    <div class="form-group" style="margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px;">
                        <h4 style="font-size: 16px; margin-bottom: 15px; color: #1d1d1f; display: flex; align-items: center; gap: 8px;">🎨 Product Variations Combination Builder</h4>
                        <p style="font-size: 13px; color: #666; margin-bottom: 15px;">Enter colors and sizes separated by commas to generate the combination matrix (e.g. Colors: Red, Blue | Sizes: S, M).</p>
                        
                        <div style="display:flex; gap:20px; margin-bottom: 15px; flex-wrap: wrap; align-items: flex-end;">
                            <div class="form-group" style="flex:2; min-width: 200px; margin-bottom: 0;">
                                <label>Colors (Comma separated)</label>
                                <input type="text" id="matrix-colors" placeholder="e.g. Red, Blue, Black" style="margin-bottom:0;">
                            </div>
                            <div class="form-group" style="flex:2; min-width: 200px; margin-bottom: 0;">
                                <label>Sizes (Comma separated)</label>
                                <input type="text" id="matrix-sizes" placeholder="e.g. S, M, L" style="margin-bottom:0;">
                            </div>
                            <div style="flex: 1; min-width: 200px;">
                                <button type="button" class="btn-primary" onclick="generateMatrix()" style="background:#555; padding: 12px 20px; border-radius: 8px; border:none; color:white; font-weight:600; cursor:pointer; width: 100%;">Generate Combinations</button>
                            </div>
                        </div>
                        
                        <div id="matrix-container" style="background:#fafafa; border:1px solid #eee; padding:15px; border-radius:12px; min-height: 50px; margin-bottom: 20px;">
                            <p style="color:#888;font-size:14px; margin:0;">No combinations generated. Enter colors and/or sizes above and click 'Generate'.</p>
                        </div>
                    </div>

                    <button type="submit" name="add_product" class="btn-primary">Add Product</button>
                </form>
            </div>

            <div class="card">
                <h3>Current Products</h3>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                            <?php foreach ($products as $p): ?>
                            <tr>
                                <td><?= $p['id'] ?></td>
                                <td><?= htmlspecialchars($p['title']) ?></td>
                                <td>₹<?= number_format($p['price'], 2) ?></td>
                                <td><?= isset($p['stock_quantity']) ? $p['stock_quantity'] : '10' ?></td>
                                <td>
                                    <a href="edit_product.php?id=<?= $p['id'] ?>" class="btn-primary"
                                        style="padding: 6px 12px; text-decoration: none; font-size: 14px; border-radius: 5px; margin-right: 5px;">Edit</a>
                                    <form method="POST" style="display:inline;"
                                        onsubmit="return confirm('Delete this product?');">
                                        <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                                        <button type="submit" name="delete_product" class="btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
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
                    <input type="text" name="image_colors[${uploadRowIndex}]" placeholder="e.g. Red, Blue, or leave empty" style="padding: 6px; border: 1px solid #ddd; border-radius: 6px;">
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

        // Generate Variations Matrix
        function generateMatrix() {
            const colorInput = document.getElementById('matrix-colors').value.trim();
            const sizeInput = document.getElementById('matrix-sizes').value.trim();
            
            const colors = colorInput ? colorInput.split(',').map(c => c.trim()).filter(c => c !== '') : [];
            const sizes = sizeInput ? sizeInput.split(',').map(s => s.trim()).filter(s => s !== '') : [];
            
            const container = document.getElementById('matrix-container');
            container.innerHTML = '';
            
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
                container.innerHTML = '<p style="color:#888;font-size:14px; margin:0;">No combinations generated. Enter colors and/or sizes above and click \'Generate\'.</p>';
                return;
            }
            
            // Create matrix table
            let html = `
                <table style="width:100%; border-collapse: collapse; margin-top: 10px;">
                    <thead>
                        <tr style="border-bottom: 2px solid #ddd; text-align: left; font-size:14px; color:#555;">
                            <th style="padding: 10px 5px;">Combination</th>
                            <th style="padding: 10px 5px;">Price Modifier (+/- ₹)</th>
                            <th style="padding: 10px 5px;">Stock Qty</th>
                            <th style="padding: 10px 5px;">Height (cm)</th>
                            <th style="padding: 10px 5px;">Width (cm)</th>
                        </tr>
                    </thead>
                    <tbody>
            `;
            
            combinations.forEach((comb, idx) => {
                html += `
                    <tr style="border-bottom: 1px solid #eee; font-size:14px;">
                        <td style="padding: 10px 5px; font-weight:600; color:#333;">
                            ${comb}
                            <input type="hidden" name="comb_values[]" value="${comb}">
                        </td>
                        <td style="padding: 10px 5px;">
                            <input type="number" step="0.01" name="comb_prices[]" value="0.00" style="width:110px; padding: 8px; border-radius: 6px; border: 1px solid #ddd; box-sizing:border-box;" required>
                        </td>
                        <td style="padding: 10px 5px;">
                            <input type="number" name="comb_stocks[]" value="10" style="width:100px; padding: 8px; border-radius: 6px; border: 1px solid #ddd; box-sizing:border-box;" required>
                        </td>
                        <td style="padding: 10px 5px;">
                            <input type="number" step="0.1" name="comb_heights[]" placeholder="Height cm" style="width:100px; padding: 8px; border-radius: 6px; border: 1px solid #ddd; box-sizing:border-box;">
                        </td>
                        <td style="padding: 10px 5px;">
                            <input type="number" step="0.1" name="comb_widths[]" placeholder="Width cm" style="width:100px; padding: 8px; border-radius: 6px; border: 1px solid #ddd; box-sizing:border-box;">
                        </td>
                    </tr>
                `;
            });
            
            html += `
                    </tbody>
                </table>
            `;
            container.innerHTML = html;
        }
    </script>
</html>