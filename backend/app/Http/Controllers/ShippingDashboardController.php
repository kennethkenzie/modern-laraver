<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class ShippingDashboardController extends Controller
{
    public function configuration(): View
    {
        return $this->renderPage(
            title: 'Shipping Configuration',
            eyebrow: 'Shipping Setup',
            description: 'Manage the shipping workflow configuration, default rates, and operational rules.',
            primaryAction: 'Update Settings',
            cards: [
                ['label' => 'Default Zones', 'value' => '0', 'note' => 'No shipping zones configured yet.'],
                ['label' => 'Active Methods', 'value' => '0', 'note' => 'No delivery methods published.'],
                ['label' => 'Free Shipping Rules', 'value' => '0', 'note' => 'No cart-value rules available.'],
            ],
            emptyTitle: 'Shipping configuration has not been set up yet',
            emptyBody: 'Connect this page to your shipping settings and delivery pricing rules when the backend configuration model is ready.'
        );
    }

    public function countries(): View
    {
        return $this->renderPage(
            title: 'Available Countries',
            eyebrow: 'Shipping Coverage',
            description: 'Choose which countries can receive deliveries from your storefront.',
            primaryAction: 'Add Country',
            cards: [
                ['label' => 'Published Countries', 'value' => '0', 'note' => 'No destination countries available.'],
                ['label' => 'Restricted Countries', 'value' => '0', 'note' => 'No exclusions configured.'],
                ['label' => 'Storefront Reach', 'value' => '0%', 'note' => 'Customers cannot select shipping countries yet.'],
            ],
            emptyTitle: 'No shipping countries configured',
            emptyBody: 'Populate this section when you are ready to define country-level delivery availability.'
        );
    }

    public function states(): View
    {
        return $this->renderPage(
            title: 'Available States',
            eyebrow: 'Regional Coverage',
            description: 'Manage sub-regions and states that can be selected during delivery checkout.',
            primaryAction: 'Add State',
            cards: [
                ['label' => 'Mapped States', 'value' => '0', 'note' => 'No regional subdivisions configured.'],
                ['label' => 'Country Links', 'value' => '0', 'note' => 'No countries currently contain state mappings.'],
                ['label' => 'Priority Routes', 'value' => '0', 'note' => 'No accelerated fulfillment areas set.'],
            ],
            emptyTitle: 'No states available yet',
            emptyBody: 'Add state-level coverage here once you define the countries and service areas in your shipping model.'
        );
    }

    public function cities(): View
    {
        return $this->renderPage(
            title: 'Available Cities',
            eyebrow: 'City Delivery Map',
            description: 'Create city-level delivery coverage for more accurate checkout and routing.',
            primaryAction: 'Add City',
            cards: [
                ['label' => 'Cities Enabled', 'value' => '0', 'note' => 'No cities are currently available.'],
                ['label' => 'Same-Day Areas', 'value' => '0', 'note' => 'No premium delivery cities configured.'],
                ['label' => 'Delivery Clusters', 'value' => '0', 'note' => 'No city groupings defined yet.'],
            ],
            emptyTitle: 'No delivery cities configured',
            emptyBody: 'This page is ready for city coverage data once you start building localized delivery rules.'
        );
    }

    public function pickupLocations(): View
    {
        return $this->renderPage(
            title: 'Pickup Locations',
            eyebrow: 'Collection Points',
            description: 'Track warehouses, partner counters, and in-store pickup points.',
            primaryAction: 'Add Pickup Location',
            cards: [
                ['label' => 'Active Pickup Points', 'value' => '0', 'note' => 'No pickup stations published.'],
                ['label' => 'Primary Warehouses', 'value' => '0', 'note' => 'No fulfillment hubs defined.'],
                ['label' => 'Customer Pickup Orders', 'value' => '0', 'note' => 'No orders assigned to pickup yet.'],
            ],
            emptyTitle: 'No pickup locations created yet',
            emptyBody: 'When pickup support is enabled, this page can hold branch counters, warehouses, and locker locations.'
        );
    }

    private function renderPage(
        string $title,
        string $eyebrow,
        string $description,
        string $primaryAction,
        array $cards,
        string $emptyTitle,
        string $emptyBody
    ): View {
        $profile = session('admin_profile');

        return view('admin.shipping.page', compact(
            'title',
            'eyebrow',
            'description',
            'primaryAction',
            'cards',
            'emptyTitle',
            'emptyBody',
            'profile'
        ));
    }
}
