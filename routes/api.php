<?php

use App\Http\Controllers\StripeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/**
 * @OA\Post(
 *     path="/api/create-checkout-session",
 *     summary="Create a new checkout session",
 *     description="Creates a new Stripe checkout session for processing payments",
 *     operationId="createCheckoutSession",
 *     tags={"Payments"},
 *     @OA\RequestBody(
 *         required=true,
 *         description="Quantité de films à louer",
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 @OA\Property(
 *                     property="film_count",
 *                     type="integer",
 *                     example="1"
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(
 *             @OA\Property(property="id", type="string", example="cs_test_abc123")
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Invalid input"
 *     )
 * )
 */
Route::post('/create-checkout-session', [StripeController::class, 'createCheckoutSession']);

Route::post('/stripe/webhook', [StripeController::class, 'handleWebhook']);
