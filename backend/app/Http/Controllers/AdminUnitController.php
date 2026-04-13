<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminUnitController extends Controller
{
    public function index(): JsonResponse
    {
        $units = Unit::orderBy('name')->get()
            ->map(fn ($u) => $this->format($u));

        return response()->json(['units' => $units]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'      => ['required', 'string', 'max:100'],
            'shortName' => ['required', 'string', 'max:20'],
            'isActive'  => ['boolean'],
        ]);

        $unit = Unit::create([
            'name'       => $data['name'],
            'short_name' => $data['shortName'],
            'is_active'  => $data['isActive'] ?? true,
        ]);

        return response()->json(['unit' => $this->format($unit)], 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $unit = Unit::findOrFail($id);

        $data = $request->validate([
            'name'      => ['sometimes', 'required', 'string', 'max:100'],
            'shortName' => ['sometimes', 'required', 'string', 'max:20'],
            'isActive'  => ['boolean'],
        ]);

        $unit->update([
            'name'       => $data['name']       ?? $unit->name,
            'short_name' => $data['shortName']  ?? $unit->short_name,
            'is_active'  => $data['isActive']   ?? $unit->is_active,
        ]);

        return response()->json(['message' => 'Unit updated.', 'unit' => $this->format($unit->fresh())]);
    }

    public function destroy(string $id): JsonResponse
    {
        Unit::findOrFail($id)->delete();
        return response()->json(['message' => 'Unit deleted.']);
    }

    private function format(Unit $u): array
    {
        return [
            'id'        => $u->id,
            'name'      => $u->name,
            'shortName' => $u->short_name,
            'isActive'  => (bool) $u->is_active,
            'createdAt' => $u->created_at->format('Y-m-d'),
        ];
    }
}
