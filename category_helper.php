<?php
// category_helper.php

function get_category_svg($category_name, $size = 80) {
    $name = strtolower(trim($category_name));
    
    // Dog face line-art
    $dog_svg = '
    <svg viewBox="0 0 100 100" width="' . $size . '" height="' . $size . '" fill="none" stroke="#d6a86c" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round">
        <!-- Ears -->
        <path d="M25,35 L12,45 C9,47 9,52 14,53 L24,47 Z" />
        <path d="M75,35 L88,45 C91,47 91,52 86,53 L76,47 Z" />
        <!-- Face Outline -->
        <path d="M24,42 L76,42 L76,68 C76,76 68,82 50,82 C32,82 24,76 24,68 Z" />
        <!-- Eyes -->
        <circle cx="38" cy="53" r="2.5" fill="#d6a86c" />
        <circle cx="62" cy="53" r="2.5" fill="#d6a86c" />
        <!-- Nose / Mouth -->
        <path d="M45,63 L55,63 L50,67 Z" fill="#d6a86c" />
        <path d="M50,67 L50,73 C48.5,75 46,75 44,73" />
        <path d="M50,73 C51.5,75 54,75 56,73" />
    </svg>';

    // Cat face line-art
    $cat_svg = '
    <svg viewBox="0 0 100 100" width="' . $size . '" height="' . $size . '" fill="none" stroke="#d6a86c" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round">
        <!-- Ears -->
        <path d="M22,42 L18,18 L38,28" />
        <path d="M78,42 L82,18 L62,28" />
        <!-- Face Outline -->
        <path d="M22,42 C16,53 16,74 50,74 C84,74 84,53 78,42 C72,32 62,28 50,28 C38,28 28,32 22,42 Z" />
        <!-- Eyes -->
        <circle cx="36" cy="48" r="2.5" fill="#d6a86c" />
        <circle cx="64" cy="48" r="2.5" fill="#d6a86c" />
        <!-- Nose / Mouth -->
        <path d="M46,56 L54,56 L50,60 Z" fill="#d6a86c" />
        <path d="M50,60 L50,65 C48.5,67 46,67 44,65" />
        <path d="M50,65 C51.5,67 54,67 56,65" />
    </svg>';

    // Bird line-art in profile
    $bird_svg = '
    <svg viewBox="0 0 100 100" width="' . $size . '" height="' . $size . '" fill="none" stroke="#d6a86c" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round">
        <!-- Body & Tail -->
        <path d="M22,64 C32,64 54,54 64,36 C67,31 72,31 74,36 C76,41 74,50 68,58 C58,70 42,74 32,74" />
        <path d="M22,64 L16,68 L24,71 L22,64" />
        <!-- Eye -->
        <circle cx="66" cy="42" r="2.5" fill="#d6a86c" />
        <!-- Beak -->
        <path d="M72,34 L80,38 L72,42" />
        <!-- Wing -->
        <path d="M44,52 C49,46 58,46 62,52" stroke-width="2.5" />
    </svg>';

    // Fish line-art
    $fish_svg = '
    <svg viewBox="0 0 100 100" width="' . $size . '" height="' . $size . '" fill="none" stroke="#d6a86c" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round">
        <!-- Body -->
        <path d="M24,58 C32,52 46,38 66,38 C76,38 84,46 84,56 C84,66 76,74 66,74 C46,74 32,64 24,58 Z" />
        <!-- Tail Fin -->
        <path d="M24,58 L12,47 L18,58 L12,69 L24,58" />
        <!-- Eye -->
        <circle cx="72" cy="53" r="2.5" fill="#d6a86c" />
        <!-- Gill line -->
        <path d="M60,45 C57,51 57,61 60,67" stroke-width="2.5" />
    </svg>';

    // Foodies bowl line-art
    $food_svg = '
    <svg viewBox="0 0 100 100" width="' . $size . '" height="' . $size . '" fill="none" stroke="#d6a86c" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round">
        <!-- Food Mound -->
        <path d="M25,48 C25,32 75,32 75,48" />
        <path d="M35,48 C35,40 65,40 65,48" stroke-width="2.5" />
        <path d="M44,36 C47,42 45,48 45,48" stroke-width="2.5" />
        <path d="M56,36 C53,42 55,48 55,48" stroke-width="2.5" />
        <!-- Bowl -->
        <path d="M18,48 L82,48 C82,68 68,76 50,76 C32,76 18,68 18,48 Z" />
        <!-- Bowl Base -->
        <path d="M38,76 L62,76 L58,84 L42,84 Z" />
    </svg>';

    // Accessories collar and tag line-art
    $accessories_svg = '
    <svg viewBox="0 0 100 100" width="' . $size . '" height="' . $size . '" fill="none" stroke="#d6a86c" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round">
        <!-- Collar Loop -->
        <path d="M20,44 C20,30 80,30 80,44 C80,55 70,62 50,62 C30,62 20,55 20,44 Z" />
        <!-- Buckle -->
        <rect x="46" y="54" width="8" height="12" rx="2" stroke="#d6a86c" stroke-width="3.5" />
        <!-- Hanging Link -->
        <path d="M50,66 L50,71" />
        <!-- Tag Circle -->
        <circle cx="50" cy="77" r="6.5" stroke="#d6a86c" stroke-width="3.5" />
    </svg>';

    // Match name patterns
    if (strpos($name, 'dog') !== false) {
        return $dog_svg;
    } elseif (strpos($name, 'cat') !== false) {
        return $cat_svg;
    } elseif (strpos($name, 'bird') !== false) {
        return $bird_svg;
    } elseif (strpos($name, 'fish') !== false) {
        return $fish_svg;
    } elseif (strpos($name, 'food') !== false || strpos($name, 'treat') !== false) {
        return $food_svg;
    } elseif (strpos($name, 'acc') !== false || strpos($name, 'collar') !== false || strpos($name, 'leash') !== false) {
        return $accessories_svg;
    }

    return ''; // Fallback to category image
}

function render_category_icon($cat, $size = 70) {
    $image_path = $cat['image_path'] ?? '';
    
    // Check if uploaded image is an SVG file
    $ext = strtolower(pathinfo($image_path, PATHINFO_EXTENSION));
    if ($ext === 'svg') {
        $path = $image_path;
        if (!file_exists($path) && file_exists('../' . $path)) {
            $path = '../' . $path;
        }
        
        if (file_exists($path)) {
            $svg_content = file_get_contents($path);
            
            // Normalize sizing in SVG tag
            $svg_content = preg_replace('/width="\d+px"|width="\d+"|width="[^"]+"/', 'width="' . $size . '"', $svg_content);
            $svg_content = preg_replace('/height="\d+px"|height="\d+"|height="[^"]+"/', 'height="' . $size . '"', $svg_content);
            
            // Ensure color theme is applied (replace color codes in strokes with our primary color #d6a86c)
            $svg_content = preg_replace('/stroke="#[0-9a-fA-F]+"/i', 'stroke="#d6a86c"', $svg_content);
            $svg_content = preg_replace('/stroke="currentColor"/i', 'stroke="#d6a86c"', $svg_content);
            $svg_content = preg_replace('/stroke="black"/i', 'stroke="#d6a86c"', $svg_content);
            
            return $svg_content;
        }
    }
    
    // Fall back to predefined line art matching the name
    $default_svg = get_category_svg($cat['name'], $size);
    if (!empty($default_svg)) {
        return $default_svg;
    }
    
    // Fall back to standard img tag if neither is available
    return '<img src="' . htmlspecialchars($image_path) . '" alt="' . htmlspecialchars($cat['name']) . '" style="width:' . $size . 'px; height:' . $size . 'px; object-fit:contain;">';
}
?>
