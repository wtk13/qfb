<?php

namespace App\Http\Controllers\BusinessProfile;

use App\Application\Command\CreateBusinessProfile;
use App\Application\Command\DeleteBusinessProfile;
use App\Application\Command\UpdateBusinessProfile;
use App\Application\Query\GetBusinessProfile;
use App\Application\Query\GetBusinessProfiles;
use App\Http\Controllers\Controller;
use Domain\Business\Port\BusinessProfileRepositoryInterface;
use Illuminate\Http\Request;

class BusinessProfileController extends Controller
{
    private const VALIDATION_RULES = [
        'name' => 'required|string|max:255',
        'address' => 'nullable|string|max:500',
        'google_review_link' => 'nullable|url|max:500',
        'locale' => 'nullable|string|in:en,pl',
        'logo' => 'nullable|image|max:2048',
    ];

    public function __construct(
        private GetBusinessProfiles $getBusinessProfiles,
        private GetBusinessProfile $getBusinessProfile,
        private CreateBusinessProfile $createBusinessProfile,
        private UpdateBusinessProfile $updateBusinessProfile,
        private DeleteBusinessProfile $deleteBusinessProfile,
        private BusinessProfileRepositoryInterface $repository,
    ) {}

    public function index(Request $request)
    {
        $profiles = $this->getBusinessProfiles->execute($request->get('tenant_id'));

        return view('business-profiles.index', compact('profiles'));
    }

    public function create()
    {
        return view('business-profiles.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate(self::VALIDATION_RULES);

        $this->createBusinessProfile->execute(
            tenantId: $request->get('tenant_id'),
            name: $validated['name'],
            address: $validated['address'] ?? null,
            googleReviewLink: $validated['google_review_link'] ?? null,
            logoPath: $this->uploadLogo($request),
            locale: $validated['locale'] ?? 'en',
        );

        return redirect()->route('business-profiles.index')
            ->with('success', __('business.created'));
    }

    public function show(Request $request, string $id)
    {
        $data = $this->getBusinessProfile->execute($id);

        if (!$data) {
            abort(404);
        }

        $this->authorizeProfile($request, $data['profile']);

        return view('business-profiles.show', $data);
    }

    public function edit(Request $request, string $id)
    {
        $profile = $this->findAndAuthorize($request, $id);

        return view('business-profiles.edit', compact('profile'));
    }

    public function update(Request $request, string $id)
    {
        $profile = $this->findAndAuthorize($request, $id);

        $validated = $request->validate(self::VALIDATION_RULES);

        $this->updateBusinessProfile->execute(
            id: $id,
            name: $validated['name'],
            address: $validated['address'] ?? null,
            googleReviewLink: $validated['google_review_link'] ?? null,
            logoPath: $this->uploadLogo($request) ?? $profile->logoPath,
            locale: $validated['locale'] ?? 'en',
        );

        return redirect()->route('business-profiles.show', $id)
            ->with('success', __('business.updated'));
    }

    public function destroy(Request $request, string $id)
    {
        $this->findAndAuthorize($request, $id);

        $this->deleteBusinessProfile->execute($id);

        return redirect()->route('business-profiles.index')
            ->with('success', __('business.deleted'));
    }

    private function findAndAuthorize(Request $request, string $id)
    {
        $profile = $this->repository->findById($id);

        if (!$profile) {
            abort(404);
        }

        $this->authorizeProfile($request, $profile);

        return $profile;
    }

    private function authorizeProfile(Request $request, $profile): void
    {
        if (!$profile->tenantId->equals($request->get('tenant_id'))) {
            abort(403);
        }
    }

    private function uploadLogo(Request $request): ?string
    {
        if ($request->hasFile('logo')) {
            return $request->file('logo')->store('logos', 'public');
        }

        return null;
    }
}
