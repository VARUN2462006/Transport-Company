<?php
/**
 * Image Optimization Script for Maharaja Transport Company
 * 
 * Converts all images to WebP format, resizes to match display dimensions,
 * and organizes into an optimized folder structure.
 * 
 * Run once: php optimize_images.php  (or visit in browser)
 * Requires: GD library (included with XAMPP)
 */

// ─── Configuration ───────────────────────────────────────────────────────────
$baseDir   = __DIR__ . '/../Images';
$outputDir = $baseDir . '/optimized';
$truckDir  = $outputDir . '/trucks';

// Image definitions: [source_path, output_path, max_width, max_height, quality]
$images = [
    // Logo: displayed at 80-100px, output 200px for 2x retina
    [
        'src'     => $baseDir . '/logo.png',
        'dest'    => $outputDir . '/logo.webp',
        'width'   => 200,
        'height'  => 200,
        'quality' => 75,
    ],
    // Header: full-width hero, displayed at max 1920×800
    [
        'src'     => $baseDir . '/Header image.png',
        'dest'    => $outputDir . '/header.webp',
        'width'   => 1920,
        'height'  => 800,
        'quality' => 70,
    ],
    // Background texture for logo text (tiny file, just convert format)
    [
        'src'     => $baseDir . '/logobgimage.jpg',
        'dest'    => $outputDir . '/logobgimage.webp',
        'width'   => 0, // 0 = keep original dimensions
        'height'  => 0,
        'quality' => 75,
    ],
    // Truck images: displayed at 250px wide, output 500px for 2x retina
    [
        'src'     => $baseDir . '/Truck Images/intra.jpg',
        'dest'    => $truckDir . '/intra.webp',
        'width'   => 500,
        'height'  => 0,
        'quality' => 70,
    ],
    [
        'src'     => $baseDir . '/Truck Images/Yodha.jpg',
        'dest'    => $truckDir . '/yodha.webp',
        'width'   => 500,
        'height'  => 0,
        'quality' => 70,
    ],
    [
        'src'     => $baseDir . '/Truck Images/Tata Prima.jpg',
        'dest'    => $truckDir . '/tata-prima.webp',
        'width'   => 500,
        'height'  => 0,
        'quality' => 70,
    ],
    [
        'src'     => $baseDir . '/Truck Images/ashok layland.jpg',
        'dest'    => $truckDir . '/ashok-leyland.webp',
        'width'   => 500,
        'height'  => 0,
        'quality' => 70,
    ],
    [
        'src'     => $baseDir . '/Truck Images/Bharatbenz.jpg',
        'dest'    => $truckDir . '/bharatbenz.webp',
        'width'   => 500,
        'height'  => 0,
        'quality' => 70,
    ],
];

// ─── Helper Functions ────────────────────────────────────────────────────────

function loadImage(string $path) {
    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    switch ($ext) {
        case 'png':  return imagecreatefrompng($path);
        case 'jpg':
        case 'jpeg': return imagecreatefromjpeg($path);
        case 'gif':  return imagecreatefromgif($path);
        case 'webp': return imagecreatefromwebp($path);
        default:     return false;
    }
}

function resizeImage($img, int $maxW, int $maxH) {
    $origW = imagesx($img);
    $origH = imagesy($img);

    // If both are 0, keep original size
    if ($maxW <= 0 && $maxH <= 0) {
        return $img;
    }

    // Calculate target dimensions maintaining aspect ratio
    if ($maxW > 0 && $maxH > 0) {
        // Fit within box
        $ratioW = $maxW / $origW;
        $ratioH = $maxH / $origH;
        $ratio  = min($ratioW, $ratioH);
    } elseif ($maxW > 0) {
        $ratio = $maxW / $origW;
    } else {
        $ratio = $maxH / $origH;
    }

    // Don't upscale
    if ($ratio >= 1.0) {
        return $img;
    }

    $newW = (int) round($origW * $ratio);
    $newH = (int) round($origH * $ratio);

    $resized = imagecreatetruecolor($newW, $newH);

    // Preserve transparency for PNGs
    imagealphablending($resized, false);
    imagesavealpha($resized, true);

    imagecopyresampled($resized, $img, 0, 0, 0, 0, $newW, $newH, $origW, $origH);
    imagedestroy($img);

    return $resized;
}

function formatBytes(int $bytes): string {
    if ($bytes >= 1048576) return round($bytes / 1048576, 1) . ' MB';
    if ($bytes >= 1024)    return round($bytes / 1024, 1) . ' KB';
    return $bytes . ' B';
}

// ─── Main Execution ──────────────────────────────────────────────────────────

// Check GD support
if (!function_exists('imagecreatefromjpeg') || !function_exists('imagewebp')) {
    die("ERROR: GD library with WebP support is required.\n");
}

// Create output directories
if (!is_dir($outputDir)) mkdir($outputDir, 0755, true);
if (!is_dir($truckDir))  mkdir($truckDir, 0755, true);

$isCli = (php_sapi_name() === 'cli');
if (!$isCli) echo "<pre style='font-family:monospace; padding:20px;'>";

echo "=== Image Optimization Script ===\n\n";

$totalOriginal  = 0;
$totalOptimized = 0;
$successCount   = 0;

foreach ($images as $cfg) {
    $src  = $cfg['src'];
    $dest = $cfg['dest'];
    $name = basename($src) . ' → ' . basename($dest);

    if (!file_exists($src)) {
        echo "⚠  SKIP: {$name} — source not found\n";
        continue;
    }

    $originalSize = filesize($src);
    $totalOriginal += $originalSize;

    echo "Processing: {$name}\n";
    echo "  Original: " . formatBytes($originalSize) . "\n";

    // Load image
    $img = loadImage($src);
    if (!$img) {
        echo "  ✗ ERROR: Could not load image\n\n";
        continue;
    }

    $origW = imagesx($img);
    $origH = imagesy($img);
    echo "  Original dimensions: {$origW}×{$origH}\n";

    // Resize
    $img = resizeImage($img, $cfg['width'], $cfg['height']);
    $newW = imagesx($img);
    $newH = imagesy($img);
    echo "  Optimized dimensions: {$newW}×{$newH}\n";

    // Save as WebP
    imagewebp($img, $dest, $cfg['quality']);
    imagedestroy($img);

    $optimizedSize = filesize($dest);
    $totalOptimized += $optimizedSize;
    $reduction = round((1 - $optimizedSize / $originalSize) * 100, 1);

    echo "  Optimized: " . formatBytes($optimizedSize) . " ({$reduction}% smaller)\n";
    echo "  ✓ Saved to: {$dest}\n\n";
    $successCount++;
}

echo "=== Summary ===\n";
echo "Images processed: {$successCount} / " . count($images) . "\n";
echo "Total original:   " . formatBytes($totalOriginal) . "\n";
echo "Total optimized:  " . formatBytes($totalOptimized) . "\n";
if ($totalOriginal > 0) {
    $totalReduction = round((1 - $totalOptimized / $totalOriginal) * 100, 1);
    echo "Total reduction:  {$totalReduction}%\n";
}
echo "\nDone! You can now update your PHP/CSS files to use the optimized images.\n";

if (!$isCli) echo "</pre>";
