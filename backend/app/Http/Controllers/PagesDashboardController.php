<?php

namespace App\Http\Controllers;

use App\Models\SiteSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PagesDashboardController extends Controller
{
    private const KEY = 'pages_content';

    public function publicContactData(): \Illuminate\Http\JsonResponse
    {
        $data = $this->pagesData();
        return response()->json(['data' => $data['contact']]);
    }

    public function publicAboutData(): \Illuminate\Http\JsonResponse
    {
        $data = $this->pagesData();
        return response()->json(['data' => $data['about']]);
    }

    public function about(): \Illuminate\View\View
    {
        $data = $this->pagesData();
        return view('admin.pages.about', ['page' => $data['about']]);
    }

    public function updateAbout(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'hero_title'       => ['nullable', 'string', 'max:255'],
            'hero_subtitle'    => ['nullable', 'string', 'max:500'],
            'hero_image'       => ['nullable', 'string', 'max:2048'],
            'mission_title'    => ['nullable', 'string', 'max:255'],
            'mission_body'     => ['nullable', 'string'],
            'vision_title'     => ['nullable', 'string', 'max:255'],
            'vision_body'      => ['nullable', 'string'],
            'team_heading'     => ['nullable', 'string', 'max:255'],
            'team_members'     => ['array'],
            'team_members.*.name'    => ['nullable', 'string', 'max:255'],
            'team_members.*.role'    => ['nullable', 'string', 'max:255'],
            'team_members.*.avatar' => ['nullable', 'string', 'max:2048'],
        ]);

        $data = $this->pagesData();
        $data['about'] = array_replace_recursive($data['about'], $validated);
        $this->persist($data);

        return response()->json(['message' => 'About Us page updated.', 'data' => $data['about']]);
    }

    public function contact(): \Illuminate\View\View
    {
        $data = $this->pagesData();
        return view('admin.pages.contact', ['page' => $data['contact']]);
    }

    public function updateContact(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'hero_title'    => ['nullable', 'string', 'max:255'],
            'hero_subtitle' => ['nullable', 'string', 'max:500'],
            'address'       => ['nullable', 'string', 'max:500'],
            'phone'         => ['nullable', 'string', 'max:100'],
            'email'         => ['nullable', 'email', 'max:255'],
            'map_embed_url' => ['nullable', 'string', 'max:2048'],
            'working_hours' => ['nullable', 'string', 'max:255'],
            'social_links'  => ['array'],
            'social_links.*.platform' => ['nullable', 'string', 'max:64'],
            'social_links.*.url'      => ['nullable', 'string', 'max:2048'],
        ]);

        $data = $this->pagesData();
        $data['contact'] = array_replace_recursive($data['contact'], $validated);
        $this->persist($data);

        return response()->json(['message' => 'Contact page updated.', 'data' => $data['contact']]);
    }

    private function pagesData(): array
    {
        $defaults = [
            'about' => [
                'hero_title'    => 'About Modern Electronics',
                'hero_subtitle' => 'Your trusted destination for quality electronics and accessories.',
                'hero_image'    => '',
                'mission_title' => 'Our Mission',
                'mission_body'  => 'To provide customers across Uganda with high-quality electronics at fair prices, backed by excellent service.',
                'vision_title'  => 'Our Vision',
                'vision_body'   => 'To become the most trusted electronics retailer in East Africa.',
                'team_heading'  => 'Meet the Team',
                'team_members'  => [],
            ],
            'contact' => [
                'hero_title'    => 'Get in Touch',
                'hero_subtitle' => 'We are here to help. Reach out to us through any of the channels below.',
                'address'       => 'Kampala, Uganda',
                'phone'         => '+256 700 000 000',
                'email'         => 'info@e-modern.ug',
                'map_embed_url' => '',
                'working_hours' => 'Mon – Sat: 8 AM – 6 PM',
                'social_links'  => [],
            ],
        ];

        $row    = SiteSettings::find(self::KEY);
        $stored = $row ? json_decode($row->value, true) : [];

        return array_replace_recursive($defaults, is_array($stored) ? $stored : []);
    }

    private function persist(array $data): void
    {
        SiteSettings::updateOrCreate(
            ['key' => self::KEY],
            [
                'value'       => json_encode($data),
                'description' => 'About Us and Contact page content',
            ]
        );
    }
}
