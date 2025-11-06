<?php

namespace App\Services;

use Firebase\JWT\JWT;

class AppleClientSecretService
{
    public function generate(): string
    {
        $teamId = config('services.apple.team_id');
        $clientId = config('services.apple.client_id');
        $keyId = config('services.apple.key_id');
        $privateKeyPath = config('services.apple.private_key');

        // Read the private key
        $privateKey = file_get_contents(storage_path($privateKeyPath));

        $claims = [
            'iss' => $teamId,
            'iat' => time(),
            'exp' => time() + 86400 * 180, // 6 months
            'aud' => 'https://appleid.apple.com',
            'sub' => $clientId,
        ];

        return JWT::encode($claims, $privateKey, 'ES256', $keyId);
    }
}
