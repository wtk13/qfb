<?php

namespace App\Infrastructure\QrCode;

use SimpleSoftwareIO\QrCode\Facades\QrCode;

class SimpleQrCodeAdapter
{
    public function generateSvg(string $url, int $size = 300): string
    {
        return QrCode::size($size)->generate($url);
    }

    public function generatePng(string $url, int $size = 300): string
    {
        return QrCode::format('png')->size($size)->generate($url);
    }
}
