<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class PremiumController extends Controller
{
    public function plans(): JsonResponse
    {
        return response()->json([
            'plans' => [
                [
                    'id' => 'free',
                    'name' => 'Free',
                    'price' => 0,
                    'currency' => 'MWK',
                    'interval' => 'forever',
                    'features' => [
                        'Stream music with ads',
                        'Create up to 5 playlists',
                        'Follow up to 10 artists',
                        'Basic audio quality',
                    ],
                ],
                [
                    'id' => 'premium_monthly',
                    'name' => 'Premium Monthly',
                    'price' => 5000,
                    'currency' => 'MWK',
                    'interval' => 'monthly',
                    'features' => [
                        'Ad-free listening',
                        'Unlimited playlists',
                        'Unlimited follows',
                        'High quality audio',
                        'Offline downloads',
                        'Early access to new releases',
                    ],
                ],
                [
                    'id' => 'premium_yearly',
                    'name' => 'Premium Yearly',
                    'price' => 50000,
                    'currency' => 'MWK',
                    'interval' => 'yearly',
                    'features' => [
                        'Everything in Premium Monthly',
                        'Save 17% vs monthly',
                        'Priority support',
                        'Exclusive content',
                        'Artist analytics (for artists)',
                    ],
                ],
                [
                    'id' => 'family',
                    'name' => 'Family',
                    'price' => 8000,
                    'currency' => 'MWK',
                    'interval' => 'monthly',
                    'features' => [
                        'Up to 6 accounts',
                        'All Premium features',
                        'Family mix playlist',
                        'Parental controls',
                    ],
                ],
            ],
        ]);
    }

    public function status(Request $request): JsonResponse
    {
        $user = $request->user();
        $subscription = $user->subscription;

        $isActive = $subscription
            && $subscription->is_active
            && $subscription->expires_at
            && $subscription->expires_at->isFuture();

        return response()->json([
            'is_premium' => $user->is_premium && $isActive,
            'subscription' => $subscription ? [
                'plan' => $subscription->plan,
                'is_active' => $subscription->is_active,
                'starts_at' => $subscription->starts_at,
                'expires_at' => $subscription->expires_at,
                'is_valid' => $isActive,
            ] : null,
        ]);
    }

    public function subscribe(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'plan' => ['required', 'string', 'in:premium_monthly,premium_yearly,family'],
        ]);

        $amounts = [
            'premium_monthly' => 5000,
            'premium_yearly' => 50000,
            'family' => 8000,
        ];

        $intervals = [
            'premium_monthly' => '+1 month',
            'premium_yearly' => '+1 year',
            'family' => '+1 month',
        ];

        $amount = $amounts[$validated['plan']];
        $txRef = 'cliq_'.$validated['plan'].'_'.$user->id.'_'.Str::random(10);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.config('services.paychangu.secret_key'),
            'Content-Type' => 'application/json',
        ])->post('https://api.paychangu.com/payment', [
            'amount' => (string) $amount,
            'currency' => 'MWK',
            'tx_ref' => $txRef,
            'email' => $user->email,
            'first_name' => $user->name,
            'callback_url' => config('app.url').'/api/premium/callback',
            'return_url' => config('app.url').'/payment/success?tx_ref='.$txRef,
            'meta' => [
                'user_id' => $user->id,
                'plan' => $validated['plan'],
            ],
            'customization' => [
                'title' => 'Clique Music Premium',
                'description' => 'Subscribe to Clique Music '.$validated['plan'],
            ],
        ]);

        if ($response->successful()) {
            $payment = Payment::create([
                'user_id' => $user->id,
                'tx_ref' => $txRef,
                'amount' => $amount,
                'currency' => 'MWK',
                'plan' => $validated['plan'],
                'status' => 'pending',
                'paychangu_data' => $response->json(),
            ]);

            return response()->json([
                'checkout_url' => $response->json('data.checkout_url'),
                'tx_ref' => $txRef,
            ]);
        }

        return response()->json([
            'message' => 'Failed to initialize payment',
            'error' => $response->json('message'),
        ], 400);
    }

    public function callback(Request $request): JsonResponse
    {
        $txRef = $request->query('tx_ref') ?? $request->input('tx_ref');

        if (! $txRef) {
            return response()->json(['message' => 'Missing transaction reference'], 400);
        }

        $payment = Payment::where('tx_ref', $txRef)->first();

        if (! $payment) {
            return response()->json(['message' => 'Payment not found'], 404);
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.config('services.paychangu.secret_key'),
        ])->get("https://api.paychangu.com/verify/{$txRef}");

        if ($response->successful() && $response->json('data.status') === 'completed') {
            $intervals = [
                'premium_monthly' => '+1 month',
                'premium_yearly' => '+1 year',
                'family' => '+1 month',
            ];

            $user = $payment->user;

            $payment->update([
                'status' => 'completed',
                'paychangu_data' => $response->json(),
            ]);

            $user->update(['is_premium' => true]);

            $subscription = $user->subscription;
            if ($subscription) {
                $subscription->update([
                    'plan' => str_replace('premium_', '', $payment->plan),
                    'starts_at' => now(),
                    'expires_at' => now()->modify($intervals[$payment->plan]),
                    'is_active' => true,
                ]);
            } else {
                $user->subscription()->create([
                    'plan' => str_replace('premium_', '', $payment->plan),
                    'starts_at' => now(),
                    'expires_at' => now()->modify($intervals[$payment->plan]),
                    'is_active' => true,
                ]);
            }
        } else {
            $payment->update([
                'status' => 'failed',
                'paychangu_data' => $response->json(),
            ]);
        }

        return response()->json(['status' => $payment->status]);
    }

    public function webhook(Request $request): JsonResponse
    {
        $payload = $request->all();
        $txRef = $payload['data']['tx_ref'] ?? null;

        if (! $txRef) {
            return response()->json(['message' => 'Invalid payload'], 400);
        }

        $payment = Payment::where('tx_ref', $txRef)->first();

        if (! $payment || $payment->status === 'completed') {
            return response()->json(['message' => 'Already processed']);
        }

        $payment->update([
            'status' => $payload['data']['status'] ?? 'unknown',
            'paychangu_data' => $payload,
        ]);

        if (($payload['data']['status'] ?? '') === 'completed') {
            $intervals = [
                'premium_monthly' => '+1 month',
                'premium_yearly' => '+1 year',
                'family' => '+1 month',
            ];

            $user = $payment->user;
            $user->update(['is_premium' => true]);

            $subscription = $user->subscription;
            if ($subscription) {
                $subscription->update([
                    'plan' => str_replace('premium_', '', $payment->plan),
                    'starts_at' => now(),
                    'expires_at' => now()->modify($intervals[$payment->plan] ?? '+1 month'),
                    'is_active' => true,
                ]);
            } else {
                $user->subscription()->create([
                    'plan' => str_replace('premium_', '', $payment->plan),
                    'starts_at' => now(),
                    'expires_at' => now()->modify($intervals[$payment->plan] ?? '+1 month'),
                    'is_active' => true,
                ]);
            }
        }

        return response()->json(['message' => 'Webhook processed']);
    }

    public function cancel(Request $request): JsonResponse
    {
        $user = $request->user();
        $subscription = $user->subscription;

        if (! $subscription || ! $subscription->is_active) {
            return response()->json(['message' => 'No active subscription'], 400);
        }

        $subscription->update(['is_active' => false]);
        $user->update(['is_premium' => false]);

        return response()->json(['message' => 'Subscription cancelled successfully']);
    }

    public function paymentHistory(Request $request): JsonResponse
    {
        $payments = $request->user()->payments()
            ->latest()
            ->paginate($request->get('per_page', 20));

        return response()->json($payments);
    }
}
