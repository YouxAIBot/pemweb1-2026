<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\AdImpression;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdImpressionController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'ad_id' => ['nullable', 'integer', 'exists:ads,id'],
            'placement' => ['required', 'string', 'max:80'],
            'context_type' => ['nullable', 'string', 'max:80'],
            'context_id' => ['nullable', 'integer'],
        ]);

        if ($request->user()?->isPremium()) {
            return response()->json(['recorded' => false, 'reason' => 'premium_user']);
        }

        AdImpression::create([
            'user_id' => $request->user()?->id,
            'ad_id' => $data['ad_id'] ?? null,
            'placement' => $data['placement'],
            'context_type' => $data['context_type'] ?? null,
            'context_id' => $data['context_id'] ?? null,
            'shown_at' => now(),
        ]);

        return response()->json(['recorded' => true]);
    }
}
