<?php

namespace App\Http\Controllers\Api;

use App\Enums\SubscriptionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreSubscriptionRequest;
use App\Models\Subscription;
use Illuminate\Support\Str;

class SubscriptionController extends Controller
{
    public function store(StoreSubscriptionRequest $request)
    {
        $sub = Subscription::firstOrNew(['email' => $request->email]);

        $sub->fill([
            'token'  => $sub->token ?? Str::random(48),
            'status' => SubscriptionStatus::Active,
        ])->save();

        return response()->json(['message' => 'Вы подписаны на новости города'], 201);
    }

    public function cancel(string $token)
    {
        Subscription::where('token', $token)
            ->firstOrFail()
            ->update(['status' => SubscriptionStatus::Unsubscribed]);

        return response()->json(['message' => 'Вы отписаны от рассылки']);
    }
}
