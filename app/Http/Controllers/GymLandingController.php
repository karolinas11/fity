<?php

namespace App\Http\Controllers;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Http\Request;

class GymLandingController extends Controller
{
    /**
     * Stara ruta na ovom (api.) domenu. Prava landing stranica sada zivi u
     * posebnom React repou (fity-landing) na config('promo.public_base_url') -
     * ovo je samo redirect za svakog ko na ovu adresu naleti direktno
     * (stari link, App Link fallback pre nego sto se assetlinks.json
     * verifikuje na oba domena).
     */
    public function show(Request $request, ?string $code = null)
    {
        $code = strtoupper(trim($code ?? config('promo.default_code')));

        return redirect()->away(
            rtrim(config('promo.public_base_url'), '/') . '/gym/' . $code,
            301
        );
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
        $url = rtrim(config('promo.public_base_url'), '/') . '/gym/' . strtoupper($code);

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
            // Ruta je van /api/* pa je van default CORS pravila - landing repo
            // (drugi domen) fetch-uje ovo direktno kao blob radi pravog
            // preuzimanja jednim klikom, ne samo otvaranja u novom tabu.
            'Access-Control-Allow-Origin' => '*',
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
