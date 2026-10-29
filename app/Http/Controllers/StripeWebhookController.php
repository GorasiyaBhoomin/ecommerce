<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use UnexpectedValueException;

class StripeWebhookController extends Controller
{
    public function __construct(private ShopController $shopController) {}

    /**
     * Handle a Stripe webhook event sent by the Stripe servers.
     */
    public function handle(Request $request): Response
    {
        $secret = config('services.stripe.webhook_secret');

        if (empty($secret)) {
            Log::error('Stripe webhook secret is not configured.');

            return response('Webhook configuration error.', 500);
        }

        $signature = $request->header('Stripe-Signature');

        if (! $signature) {
            return response('Missing Stripe signature.', 400);
        }

        try {
            $event = Webhook::constructEvent(
                $request->getContent(),
                $signature,
                $secret
            );
        } catch (UnexpectedValueException $e) {
            Log::warning('Invalid Stripe webhook payload.');

            return response('Invalid payload.', 400);
        } catch (SignatureVerificationException $e) {
            Log::warning('Invalid Stripe webhook signature.');

            return response('Invalid signature.', 400);
        }

        // Log event types only after signature verification.
        Log::info('Verified Stripe webhook received', [
            'event_id' => $event->id,
            'type' => $event->type,
        ]);

        try {
            switch ($event->type) {
                case 'checkout.session.completed':
                case 'checkout.session.async_payment_succeeded':

                    $session = $event->data->object;

                    if (($session->payment_status ?? null) === 'paid') {
                        $this->shopController
                            ->fulfillStripeCheckoutSession($session);
                    } else {
                        Log::info(
                            'Stripe Checkout completed but is not paid yet.',
                            ['session_id' => $session->id ?? null]
                        );
                    }

                    break;

                case 'payment_intent.payment_failed':

                    $this->shopController
                        ->handleFailedStripePayment(
                            $event->data->object
                        );

                    break;

                case 'charge.refunded':

                    $this->shopController
                        ->handleRefundedStripePayment(
                            $event->data->object
                        );

                    break;

                default:
                    // Valid event, but this endpoint does not need to act on it.
                    Log::info('Stripe event ignored.', [
                        'type' => $event->type,
                    ]);
            }

            return response('Webhook handled.', 200);

        } catch (\Throwable $e) {
            Log::error('Stripe webhook processing failed.', [
                'event_id' => $event->id ?? null,
                'type' => $event->type ?? null,
                'error' => $e->getMessage(),
            ]);

            // Return 500 so Stripe can retry a genuine processing failure.
            return response('Webhook processing failed.', 500);
        }
    }
}
