<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use UnexpectedValueException;
use Illuminate\Support\Facades\Log;

/**
 * @OA\PathItem(path="/api/create-checkout-session")
 */
class StripeController extends Controller
{
    public function createCheckoutSession(Request $request)
    {
        $filmCount = $request->input('film_count');

        // 1. Initialiser Stripe avec votre clé secrète
        Stripe::setApiKey(env('STRIPE_SECRET_KEY'));
        Stripe::setApiVersion('2025-05-28.basil');

        // URL de votre frontend pour la redirection après le paiement
        $successUrl = 'http://localhost:5173/success/{CHECKOUT_SESSION_ID}';
        $cancelUrl = 'http://localhost:5173/cart';

        header('Content-Type: application/json');

        try {
            // 3. Créer la session de paiement
            $session = Session::create([
                'mode' => 'payment',
                'success_url' => $successUrl,
                'cancel_url' => $cancelUrl,
                'shipping_address_collection' => [
                    'allowed_countries' => ['FR']
                ],
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price' => 'price_1RNqWuK6sHuLW9WSE7wvF08N',
                    'quantity' => $filmCount,
                ]],
            ]);

            header("HTTP/1.1 303 See Other");
            header("Location: " . $session->url);
            // 4. Retourner l'ID de la session au frontend
            return response()->json(['id' => $session->id]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Gère les événements entrants du webhook Stripe.
     */
    public function handleWebhook(Request $request)
    {
        // La clé secrète du webhook, à récupérer sur votre dashboard Stripe
        $webhookSecret = env('STRIPE_WEBHOOK_SECRET');

        if (!$webhookSecret) {
            // Log l'erreur si la clé n'est pas configurée
            Log::error('La clé secrète du webhook Stripe n\'est pas configurée.');
            return response()->json(['error' => 'Configuration serveur incorrecte.'], 500);
        }

        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $event = null;

        try {
            // Vérifie la signature du webhook pour s'assurer que la requête vient bien de Stripe
            $event = Webhook::constructEvent(
                $payload,
                $sigHeader,
                $webhookSecret
            );
        } catch (UnexpectedValueException $e) {
            // Payload invalide
            return response()->json(['error' => 'Payload invalide.'], 400);
        } catch (SignatureVerificationException $e) {
            // Signature invalide
            return response()->json(['error' => 'Signature invalide.'], 400);
        }

        // Gérer l'événement
        switch ($event->type) {
            case 'checkout.session.completed':
                $session = $event->data->object; // Contient la session de paiement

                // Logique de post-paiement :
                // 1. Récupérer les informations de la session (ex: $session->metadata['user_id'])
                // 2. Mettre à jour votre base de données (marquer la commande comme payée)
                // 3. Envoyer un email de confirmation à l'utilisateur
                // 4. Donner accès au film loué
                Log::info('Paiement réussi pour la session: ' . $session->id);
                // Exemple: Order::where('stripe_session_id', $session->id)->update(['status' => 'paid']);
                break;

            // ... gérez d'autres types d'événements si nécessaire
            // case 'payment_intent.succeeded':
            //     ...
            //     break;

            default:
                // Événement non géré
                Log::warning('Événement webhook non géré reçu: ' . $event->type);
        }

        // Renvoyer une réponse 200 à Stripe pour accuser réception de l'événement
        return response()->json(['status' => 'success']);
    }
}
