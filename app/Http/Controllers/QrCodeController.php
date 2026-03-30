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
        $profile = $this->findAndAuthorize($request, $id);
        $url = $this->ratingUrl($profile);
        $svg = $this->qrCodeAdapter->generateSvg($url);

        return view('business-profiles.qr-code', [
            'profile' => $profile,
            'svg' => $svg,
            'url' => $url,
        ]);
    }

    public function download(Request $request, string $id)
    {
        $profile = $this->findAndAuthorize($request, $id);
        $url = $this->ratingUrl($profile);
        $png = $this->qrCodeAdapter->generatePng($url);

        return response($png)
            ->header('Content-Type', 'image/png')
            ->header('Content-Disposition', "attachment; filename=\"qr-{$profile->slug}.png\"");
    }

    private function findAndAuthorize(Request $request, string $id)
    {
        $profile = $this->profileRepository->findById($id);

        if (! $profile || ! $profile->tenantId->equals($request->get('tenant_id'))) {
            abort(403);
        }

        return $profile;
    }

    private function ratingUrl($profile): string
    {
        return route('rate.show', ['slug' => $profile->slug, 'token' => 'qr']);
    }
}
