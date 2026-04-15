<?php

namespace App\Http\Controllers;

use App\Models\Offer;
use Illuminate\Http\Request;

class OffersDashboardController extends Controller
{
    public function index()
    {
        $offers = Offer::orderBy('created_at', 'desc')->get()
            ->map(fn ($o) => [
                'id'               => $o->id,
                'name'             => $o->name,
                'code'             => $o->code,
                'type'             => $o->type,
                'value'            => (float) $o->value,
                'minOrderAmount'   => $o->min_order_amount ? (float) $o->min_order_amount : null,
                'maxDiscountAmount'=> $o->max_discount_amount ? (float) $o->max_discount_amount : null,
                'startsAt'         => $o->starts_at?->format('Y-m-d'),
                'expiresAt'        => $o->expires_at?->format('Y-m-d'),
                'usageLimit'       => $o->usage_limit,
                'usageCount'       => $o->usage_count,
                'isActive'         => (bool) $o->is_active,
                'createdAt'        => $o->created_at->format('Y-m-d'),
            ])
            ->values();

        return view('admin.offers.index', compact('offers'));
    }
}
