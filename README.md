<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="200" alt="Laravel Logo"></a></p>

# API de Paiement Laravel avec Stripe

Cette API Laravel permet de créer une session de paiement avec Stripe pour gérer les locations de films. Elle inclut un endpoint pour créer une session de paiement et un webhook pour écouter les événements de paiement.

## Prérequis

-   PHP 7.4 ou supérieur
-   Composer
-   Node.js et npm
-   Compte Stripe
-   Stripe CLI (pour tester les webhooks localement)

## Installation

1. Clonez ce dépôt sur votre machine locale.
2. Commencez par cette commande pour installer toutes les dépendances PHP nécessaires définies dans votre fichier composer.json. Cela est essentiel pour le bon fonctionnement de Laravel et peut être requis pour certaines configurations ou tâches backend. :

    ```bash
    composer install
    ```

3. Copiez le fichier `.env.example` en `.env` et configurez vos variables d'environnement, y compris vos clés Stripe.

    ```bash
    cp .env.example .env
    ```

4. Générez une clé d'application Laravel :

    ```bash
    php artisan key\:generate
    ```

## Configuration

1. Assurez-vous que votre fichier `.env` contient les clés suivantes pour Stripe :

    ```
    STRIPE_KEY=votre_clé_stripe
    STRIPE_SECRET=votre_clé_secrète_stripe
    STRIPE_WEBHOOK_SECRET=votre_clé_webhook
    ```

2. Configurez CORS pour accepter les requêtes de votre client React :

    ```php
    // Dans le fichier config/cors.php, assurez-vous que votre domaine est autorisé
    'allowed_origins' => [env('FRONTEND_URL', 'http://localhost:5173'), 'http://127.0.0.1:5173'],
    ```

3. Activez les webhooks Stripe en utilisant la CLI Stripe pour tester localement :

    ```bash
    stripe listen --forward-to localhost:8000/api/stripe/webhook
    ```

## Utilisation

1. Démarrez le serveur Laravel :

    ```bash
    php artisan serve
    ```

2. Le frontend React doit envoyer une requête POST à l'endpoint `/api/create-checkout-session` avec la quantité de films à louer.

## Endpoints

-   **POST /api/create-checkout-session** : Crée une session de paiement Stripe. Le corps de la requête doit inclure le nombre de films à louer. Exemple :

    ```json
    {
        "film_count": 2
    }
    ```

-   **Webhook** : Écoute les événements Stripe pour gérer les succès et les annulations de paiement. Configurez votre compte Stripe pour envoyer des événements à l'URL de votre webhook.

    Exemple de configuration sur Stripe Dashboard : `https://yourdomain.com/api/stripe/webhook`

## Gestion des Événements Stripe

Le webhook écoute les événements suivants :

-   `checkout.session.completed` : Paiement réussi.
-   `checkout.session.expired` : Session expirée sans paiement.

Le front-end doit être configuré pour persister les données locales avant de rediriger vers la page de paiement Stripe. Après le retour sur votre site, il peut rétablir ces données en fonction de l'état du paiement.

## Tests

Pour tester l'API, vous pouvez utiliser Postman ou tout autre outil de test d'API pour envoyer des requêtes POST à votre endpoint de création de session de paiement.
