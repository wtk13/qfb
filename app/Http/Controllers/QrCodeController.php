<?php

namespace App\Http\Controllers;

use App\Infrastructure\QrCode\SimpleQrCodeAdapter;
use Domain\Business\Port\BusinessProfileRepositoryInterface;
use Illuminate\Http\Request;

class QrCodeController extends Controller
{
    public function __construct(
        private BusinessProfileRepositoryInterface $profileRepository,
        private SimpleQrCodeAdapter $qrCodeAdapter,
    ) {}

    public function show(Request $request, string $id)
    {
        $profile = $this->profileRepository->findById($id);

        if (!$profile || !$profile->tenantId->equals($request->get('tenant_id'))) {
            abort(403);
        }

        $url = url("/rate/{$profile->slug}/qr");
        $svg = $this->qrCodeAdapter->generateSvg($url);

        return view('business-profiles.qr-code', [
            'profile' => $profile,
            'svg' => $svg,
            'url' => $url,
        ]);
    }

    public function download(Request $request, string $id)
    {
        $profile = $this->profileRepository->findById($id);

        if (!$profile || !$profile->tenantId->equals($request->get('tenant_id'))) {
            abort(403);
        }

        $url = url("/rate/{$profile->slug}/qr");
        $png = $this->qrCodeAdapter->generatePng($url);

        return response($png)
            ->header('Content-Type', 'image/png')
            ->header('Content-Disposition', "attachment; filename=\"qr-{$profile->slug}.png\"");
    }
}
