<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Fity — {{ $trialDays }} dana besplatno</title>
    <meta name="robots" content="noindex">

    {{-- Smart App Banner: ako korisnik posle instalacije vrati u Safari, tap na
         "OPEN" pokrece aplikaciju sa ovim URL-om kao argumentom. --}}
    <meta name="apple-itunes-app" content="app-id={{ $iosAppId }}, app-argument={{ $appArgument }}">

    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100svh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 20px calc(24px + env(safe-area-inset-bottom));
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            color: #fff;
            background: linear-gradient(160deg, #68B767 0%, #19B888 100%);
            -webkit-font-smoothing: antialiased;
        }
        .card { width: 100%; max-width: 420px; text-align: center; }
        .eyebrow {
            font-size: 13px; font-weight: 700; letter-spacing: .12em;
            text-transform: uppercase; opacity: .85; margin: 0 0 12px;
        }
        h1 { font-size: clamp(30px, 9vw, 42px); line-height: 1.1; margin: 0 0 14px; font-weight: 800; }
        .lede { font-size: 17px; line-height: 1.5; margin: 0 0 28px; opacity: .95; }
        .btn {
            display: block; width: 100%; padding: 17px 22px; margin-bottom: 12px;
            border: 0; border-radius: 14px; background: #fff; color: #12805E;
            font-size: 17px; font-weight: 700; text-decoration: none; cursor: pointer;
            font-family: inherit;
        }
        .btn:active { transform: scale(.985); }
        .btn--ghost { background: rgba(255,255,255,.16); color: #fff; box-shadow: inset 0 0 0 1.5px rgba(255,255,255,.55); }
        .code-box { margin-top: 26px; padding: 16px; border-radius: 14px; background: rgba(0,0,0,.16); }
        .code-box p { margin: 0 0 8px; font-size: 13px; opacity: .85; }
        .code { font-size: 26px; font-weight: 800; letter-spacing: .16em; font-variant-numeric: tabular-nums; }
        .steps { margin: 24px 0 0; padding: 0; list-style: none; text-align: left; font-size: 14px; opacity: .9; }
        .steps li { display: flex; gap: 10px; margin-bottom: 8px; line-height: 1.45; }
        .steps span { flex: 0 0 22px; height: 22px; border-radius: 50%; background: rgba(255,255,255,.22);
                      display: grid; place-items: center; font-size: 12px; font-weight: 700; }
        .notice { margin-top: 22px; font-size: 14px; line-height: 1.5; opacity: .9; }
    </style>
</head>
<body>
<div class="card">
    @if ($available)
        <p class="eyebrow">Poklon za članove teretane</p>
        <h1>{{ $trialDays }} dana Fity-ja<br>besplatno</h1>
        <p class="lede">
            Personalizovan plan ishrane, recepti i lista za kupovinu.
            Bez plaćanja i bez obaveze — otkaži kad god želiš.
        </p>

        <a class="btn" id="android-btn" href="{{ $androidUrl }}" rel="noopener">Preuzmi za Android</a>
        <a class="btn btn--ghost" id="ios-btn" href="{{ $iosUrl }}" rel="noopener">Preuzmi za iPhone</a>

        <div class="code-box">
            <p>Tvoj kupon kod:</p>
            <div class="code">{{ $code }}</div>
        </div>

        <ul class="steps">
            <li><span>1</span> Preuzmi aplikaciju sa dugmeta iznad</li>
            <li><span>2</span> Pri registraciji unesi kod sa ove stranice (na Androidu će često biti već upisan)</li>
            <li><span>3</span> {{ $trialDays }} dana kreće odmah</li>
        </ul>
    @else
        <p class="eyebrow">Fity</p>
        <h1>Ponuda trenutno nije aktivna</h1>
        <p class="lede">
            @if ($unavailableReason === 'not_started')
                Ova ponuda počinje 1. septembra. Skeniraj kod ponovo od tog datuma.
            @else
                Ova ponuda je istekla, ali Fity možeš probati besplatno 7 dana.
            @endif
        </p>
        <a class="btn" href="{{ config('promo.stores.android') }}" rel="noopener">Preuzmi za Android</a>
        <a class="btn btn--ghost" href="{{ $iosUrl }}" rel="noopener">Preuzmi za iPhone</a>
    @endif
</div>

<script>
    (function () {
        var ua = navigator.userAgent || '';
        var isAndroid = /Android/i.test(ua);
        var isIOS = /iPad|iPhone|iPod/i.test(ua) ||
            (/Macintosh/.test(ua) && navigator.maxTouchPoints > 1);

        var android = document.getElementById('android-btn');
        var ios = document.getElementById('ios-btn');
        if (!android || !ios) return;

        // Na mobilnom prikazujemo samo relevantnu prodavnicu, na desktopu obe.
        if (isAndroid) {
            ios.remove();
        } else if (isIOS) {
            android.remove();
            ios.className = 'btn';
        }
    })();
</script>
</body>
</html>
