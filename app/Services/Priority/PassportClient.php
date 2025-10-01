<?php
namespace App\Services\Priority;

class PassportClient {
    public function createHostedCheckoutLink(string $intentRef, string $returnUrl): array {
        // TODO: replace with real Priority/Passport call
        return ['url' => 'https://example.test/checkout/'.$intentRef.'?return='.$returnUrl];
    }
}
