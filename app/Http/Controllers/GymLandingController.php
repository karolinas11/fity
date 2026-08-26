<?php

namespace App\Http\Controllers;

use App\Models\PromoCode;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Http\Request;

class GymLandingController extends Controller
{
    /**
     * Stranica na koju vodi QR kod iz teretane.
     *
     * Ako je aplikacija instalirana, Android App Link / iOS Universal Link presretne
     * ovaj URL i otvori aplikaciju - do ove stranice se tada nikad ne dolazi.
     * Ovo je, dakle, put za korisnike koji aplikaciju jos nemaju.
     */
    public function show(Request $request, ?string $code = null)
    {
        $code = strtoupper(trim($code ?? config('promo.default_code')));
        $promoCode = PromoCode::findByCode($code);

        $trialDays = $promoCode?->trial_days ?? 30;
        $available = $promoCode !== null && $promoCode->isRedeemable();

        // Play Install Referrer isporucuje ovaj string aplikaciji pri prvom pokretanju
        // posle instalacije - to je Android put koji radi bez ijedne akcije korisnika.
        $referrer = http_build_query([
            'utm_source' => 'gym',
            'utm_medium' => 'qr',
            'utm_campaign' => strtolower($code),
            'promo' => $code,
        ]);

        return response()->view('gym-landing', [
            'code' => $code,
            'trialDays' => $trialDays,
            'available' => $available,
            'unavailableReason' => $promoCode?->unavailableReason(),
            'androidUrl' => config('promo.stores.android') . '&referrer=' . urlencode($referrer),
            'iosUrl' => config('promo.stores.ios'),
            'iosAppId' => config('promo.apps.ios_app_id'),
            'appArgument' => $request->url(),
        ]);
    }

    /**
     * QR kod za stampu. Format: png (default, za flajere) ili svg (skalira
     * se bez gubitka kvaliteta - za vece formate, npr. posteri, baneri).
     *
     * Primer: /gym/GYM30/qr?format=svg&size=1200
     */
    public function qr(Request $request, string $code)
    {
        $format = $request->query('format', 'png') === 'svg' ? 'svg' : 'png';
        $size = max(200, min(4000, (int) $request->query('size', 1000)));
        $url = route('gym-landing-code', ['code' => strtoupper($code)]);

        $result = Builder::create()
            ->writer($format === 'svg' ? new SvgWriter() : new PngWriter())
            ->data($url)
            ->errorCorrectionLevel(ErrorCorrectionLevel::High)
            ->size($size)
            ->margin(24)
            ->build();

        return response($result->getString(), 200, [
            'Content-Type' => $result->getMimeType(),
            'Content-Disposition' => 'inline; filename="fity-qr-' . strtolower($code) . '.' . $format . '"',
        ]);
    }

    /**
     * Digital Asset Links - Android proverava ovaj fajl da bi verifikovao App Link
     * i otvarao https://<domen>/gym direktno u aplikaciji umesto u browseru.
     */
    public function assetLinks()
    {
        $fingerprints = config('promo.android_sha256_fingerprints');

        return response()->json([[
            'relation' => ['delegate_permission/common.handle_all_urls'],
            'target' => [
                'namespace' => 'android_app',
                'package_name' => config('promo.apps.android_package'),
                'sha256_cert_fingerprints' => $fingerprints,
            ],
        ]], 200, ['Content-Type' => 'application/json']);
    }

    /**
     * Apple App Site Association. Sluzi iOS Universal Link-ovima i postaje aktivan
     * tek kada aplikacija dobije associated-domains entitlement (iOS faza).
     */
    public function appleAppSiteAssociation()
    {
        return response()->json([
            'applinks' => [
                'apps' => [],
                'details' => [[
                    'appID' => config('promo.apps.ios_team_id') . '.' . config('promo.apps.ios_bundle_id'),
                    'paths' => ['/gym', '/gym/*'],
                ]],
            ],
        ], 200, ['Content-Type' => 'application/json']);
    }
}
