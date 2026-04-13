<?php

namespace App\Http\Controllers;

use App\Models\AttributeSet;
use App\Models\AttributeSetOption;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminAttributeSetController extends Controller
{
    public function index(): JsonResponse
    {
        $sets = AttributeSet::with('options')->orderBy('name')->get()
            ->map(fn ($s) => $this->format($s));

        return response()->json(['attributeSets' => $sets]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'inputType' => ['required', 'string', 'in:dropdown,text,color,radio,checkbox'],
            'isActive'  => ['boolean'],
            'options'   => ['array'],
            'options.*.value'    => ['required', 'string', 'max:255'],
            'options.*.colorHex' => ['nullable', 'string', 'max:10'],
        ]);

        $set = AttributeSet::create([
            'name'       => $data['name'],
            'input_type' => $data['inputType'],
            'is_active'  => $data['isActive'] ?? true,
        ]);

        foreach ($data['options'] ?? [] as $i => $opt) {
            $set->options()->create([
                'value'      => $opt['value'],
                'color_hex'  => $opt['colorHex'] ?? null,
                'sort_order' => $i,
            ]);
        }

        return response()->json(['attributeSet' => $this->format($set->load('options'))], 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $set = AttributeSet::findOrFail($id);

        $data = $request->validate([
            'name'      => ['sometimes', 'required', 'string', 'max:255'],
            'inputType' => ['sometimes', 'required', 'string', 'in:dropdown,text,color,radio,checkbox'],
            'isActive'  => ['boolean'],
            'options'   => ['array'],
            'options.*.value'    => ['required', 'string', 'max:255'],
            'options.*.colorHex' => ['nullable', 'string', 'max:10'],
        ]);

        $set->update([
            'name'       => $data['name']      ?? $set->name,
            'input_type' => $data['inputType'] ?? $set->input_type,
            'is_active'  => $data['isActive']  ?? $set->is_active,
        ]);

        // Replace options when provided
        if (array_key_exists('options', $data)) {
            $set->options()->delete();
            foreach ($data['options'] as $i => $opt) {
                $set->options()->create([
                    'value'      => $opt['value'],
                    'color_hex'  => $opt['colorHex'] ?? null,
                    'sort_order' => $i,
                ]);
            }
        }

        return response()->json(['message' => 'Attribute set updated.', 'attributeSet' => $this->format($set->load('options'))]);
    }

    public function destroy(string $id): JsonResponse
    {
        AttributeSet::findOrFail($id)->delete(); // options cascade
        return response()->json(['message' => 'Attribute set deleted.']);
    }

    private function format(AttributeSet $s): array
    {
        return [
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
        ];
    }
}
