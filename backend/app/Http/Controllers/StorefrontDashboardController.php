<?php

namespace App\Http\Controllers;

use App\Models\SiteSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StorefrontDashboardController extends Controller
{
    private const KEY = 'frontend_data';

    public function header(): View
    {
        $data = $this->frontendData();

        return view('admin.storefront.header', [
            'navbar' => $data['navbar'],
        ]);
    }

    public function updateHeader(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'siteTitle' => ['nullable', 'string', 'max:255'],
            'logoUrl' => ['nullable', 'string', 'max:2048'],
            'logoAlt' => ['nullable', 'string', 'max:255'],
            'faviconUrl' => ['nullable', 'string', 'max:2048'],
            'searchPlaceholder' => ['nullable', 'string', 'max:255'],
            'showMarquee' => ['nullable', 'boolean'],
            'marqueeText' => ['nullable', 'string'],
            'topLinks' => ['array'],
            'topLinks.*.label' => ['nullable', 'string', 'max:255'],
            'topLinks.*.href' => ['nullable', 'string', 'max:255'],
            'topLinks.*.icon' => ['nullable', 'string', 'max:64'],
            'quickLinks' => ['array'],
            'quickLinks.*.label' => ['nullable', 'string', 'max:255'],
            'quickLinks.*.href' => ['nullable', 'string', 'max:255'],
        ]);

        $data = $this->frontendData();
        $data['navbar'] = array_replace_recursive($data['navbar'], $validated);
        $this->persistFrontendData($data);

        return response()->json([
            'message' => 'Header settings updated.',
            'data' => $data['navbar'],
        ]);
    }

    public function slider(): View
    {
        $data = $this->frontendData();

        return view('admin.storefront.slider', [
            'slides' => $data['hero']['slides'],
        ]);
    }

    public function updateSlider(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'slides' => ['array'],
            'slides.*.id' => ['required', 'string', 'max:255'],
            'slides.*.image' => ['nullable', 'string', 'max:2048'],
            'slides.*.title' => ['nullable', 'string', 'max:255'],
            'slides.*.description' => ['nullable', 'string'],
            'slides.*.ctaLabel' => ['nullable', 'string', 'max:255'],
            'slides.*.ctaHref' => ['nullable', 'string', 'max:255'],
        ]);

        $data = $this->frontendData();
        $data['hero']['slides'] = array_values($validated['slides'] ?? []);
        $this->persistFrontendData($data);

        return response()->json([
            'message' => 'Slider settings updated.',
            'data' => $data['hero']['slides'],
        ]);
    }

    private function frontendData(): array
    {
        $defaults = [
            'navbar' => [
                'logoUrl' => '',
                'logoAlt' => '',
                'siteTitle' => 'Modern Electronics',
                'faviconUrl' => '/favicon.ico',
                'searchPlaceholder' => 'Search here...',
                'showMarquee' => true,
                'marqueeText' => 'HOT SALE 🔥 | MODERN ELECTRONICS LTD Trusted Electronics Experts Since 1998! Get quality electronics, appliances, accessories, and reliable tech solutions from Modern Electronics Ltd. Affordable Prices • Genuine Products • Trusted Service • Visit us today and upgrade your lifestyle with modern technology.',
                'topLinks' => [
                    ['label' => 'Home', 'href' => '/', 'icon' => 'home'],
                    ['label' => 'About Us', 'href' => '/about', 'icon' => 'info'],
                    ['label' => 'Contact', 'href' => '/contact', 'icon' => 'mail'],
                ],
                'quickLinks' => [
                    ['label' => 'TV Parts', 'href' => '/tv-parts'],
                    ['label' => 'Featured Category', 'href' => '/featured'],
                    ['label' => 'Hot Deals!', 'href' => '/wholesale'],
                    ['label' => 'Blog', 'href' => '/blog'],
                ],
            ],
            'hero' => [
                'slides' => [],
                'sideCards' => [],
            ],
        ];

        $row = SiteSettings::find(self::KEY);
        $stored = $row ? json_decode($row->value, true) : [];

        return array_replace_recursive($defaults, is_array($stored) ? $stored : []);
    }

    private function persistFrontendData(array $data): void
    {
        SiteSettings::updateOrCreate(
            ['key' => self::KEY],
            [
                'value' => json_encode($data),
                'description' => 'Serialized storefront and admin-managed frontend content',
            ]
        );
    }
}
