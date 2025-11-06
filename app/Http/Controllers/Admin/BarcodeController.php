<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Picqer\Barcode\BarcodeGeneratorPNG;
use App\Models\Product;

class BarcodeController extends Controller
{
    public function image(Product $product)
    {
        // Safely resolve product fields (guard when $product is null/array)
        $pid = optional($product)->id ?? data_get($product, 'id');
        $barcodeField = data_get($product, 'barcode');
        $sku = data_get($product, 'sku');
        // Use barcode if present, otherwise SKU; fall back to PR + zero-padded id or a placeholder
        $code = $barcodeField ?: $sku ?: ($pid ? 'PR' . str_pad($pid, 6, '0', STR_PAD_LEFT) : 'PR000000');
        // Prefer Picqer barcode generator if available; otherwise fall back to a simple PNG placeholder.
        $barcode = null;
        if (class_exists('Picqer\\Barcode\\BarcodeGeneratorPNG')) {
            $generator = new BarcodeGeneratorPNG();
            $barcode = $generator->getBarcode($code, $generator::TYPE_CODE_128);
        } else {
            // Fallback: generate a simple PNG with the code as text (uses GD). If GD isn't available, return 501 JSON.
            if (function_exists('imagecreatetruecolor') && function_exists('imagestring') && function_exists('imagepng')) {
                $width = 300; $height = 80;
                $img = imagecreatetruecolor($width, $height);
                $bg = imagecolorallocate($img, 255, 255, 255);
                $textColor = imagecolorallocate($img, 0, 0, 0);
                imagefilledrectangle($img, 0, 0, $width, $height, $bg);
                // center text
                $fontSize = 5;
                $text = (string) $code;
                $textWidth = imagefontwidth($fontSize) * strlen($text);
                $x = max(5, (int)(($width - $textWidth) / 2));
                $y = (int)(($height - imagefontheight($fontSize)) / 2);
                imagestring($img, $fontSize, $x, $y, $text, $textColor);
                ob_start();
                imagepng($img);
                imagedestroy($img);
                $barcode = ob_get_clean();
            } else {
                return response()->json(['error' => 'Barcode generator not available'], 501);
            }
        }
        // If ?print=1 is present, return a label HTML view for easy printing
        if (request()->query('print')) {
            return view('products.label', compact('product'));
        }

        return response($barcode, 200)->header('Content-Type', 'image/png');
    }

    public function batch(Request $request)
    {
        $ids = $request->input('ids', []);
        $products = Product::whereIn('id', (array) $ids)->get();
        return view('products.labels_sheet', compact('products'));
    }
}
