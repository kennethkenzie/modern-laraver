<?php

namespace App\Http\Controllers;

use App\Models\AttributeSet;

class AttributeSetsDashboardController extends Controller
{
    public function index()
    {
        $attributeSets = AttributeSet::with('options')->orderBy('name')->get()
            ->map(fn ($s) => [
                'id'        => $s->id,
                'name'      => $s->name,
                'inputType' => $s->input_type,
                'isActive'  => (bool) $s->is_active,
                'createdAt' => $s->created_at->format('Y-m-d'),
                'options'   => $s->options->map(fn ($o) => [
                    'id'       => $o->id,
                    'value'    => $o->value,
                    'colorHex' => $o->color_hex,
                ])->values(),
            ])
            ->values();

        $profile = session('admin_profile');

        return view('admin.products.attribute-sets', compact('attributeSets', 'profile'));
    }
}
