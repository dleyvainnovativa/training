<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * Verifies Firebase ID tokens (RS256) against Google's public x509 certs.
 *
 * Uses firebase/php-jwt. Google's securetoken endpoint returns a map of
 * { kid => PEM x509 certificate }, NOT a JWK set — so we read the token
 * header to find the matching kid and verify against that single cert.
 * Certs are cached for one hour (well within Google's rotation window).
 */
class FirebaseAuthService
{
    private const CERT_URL = 'https://www.googleapis.com/robot/v1/metadata/x509/securetoken@system.gserviceaccount.com';

    public function __construct(
        private readonly string $projectId
    ) {}

    /**
     * Verify an ID token; return decoded claims as array, or null if invalid.
     */
    public function verify(string $idToken): ?array
    {
        try {
            $certs = $this->publicCerts();

            // Find the kid from the token header so we verify with the right cert.
            $kid = $this->tokenKid($idToken);
            if (! $kid || ! isset($certs[$kid])) {
                return null;
            }

            $decoded = JWT::decode($idToken, new Key($certs[$kid], 'RS256'));
            $claims  = (array) $decoded;

            // Firebase-required claim checks.
            $expectedIss = "https://securetoken.google.com/{$this->projectId}";
            if (($claims['aud'] ?? null) !== $this->projectId) return null;
            if (($claims['iss'] ?? null) !== $expectedIss)     return null;
            if (empty($claims['sub']))                          return null;
            if (($claims['exp'] ?? 0) < time())                 return null;

            return $claims;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /** Map of kid => PEM certificate, cached for its rotation window. */
    private function publicCerts(): array
    {
        return Cache::remember('firebase_public_certs', 3600, function () {
            return Http::get(self::CERT_URL)->json() ?? [];
        });
    }

    /** Read the `kid` from a JWT header without verifying the signature. */
    private function tokenKid(string $jwt): ?string
    {
        $parts = explode('.', $jwt);
        if (count($parts) !== 3) return null;

        $header = json_decode($this->base64UrlDecode($parts[0]), true);
        return $header['kid'] ?? null;
    }

    private function base64UrlDecode(string $data): string
    {
        $remainder = strlen($data) % 4;
        if ($remainder) {
            $data .= str_repeat('=', 4 - $remainder);
        }
        return base64_decode(strtr($data, '-_', '+/'));
    }
}
