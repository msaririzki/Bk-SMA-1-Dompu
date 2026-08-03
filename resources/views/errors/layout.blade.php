<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#061423">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('code') — @yield('title') | Sistem Informasi BK</title>
    <style>
        :root { color-scheme: light; font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; color: #172033; background: #eef4f6; }
        .shell { position: relative; display: grid; min-height: 100vh; place-items: center; overflow: hidden; padding: 32px 20px; }
        .glow { position: absolute; width: 520px; height: 520px; border-radius: 999px; filter: blur(25px); opacity: .22; pointer-events: none; }
        .glow-one { right: -180px; top: -220px; background: #14b8a6; }
        .glow-two { bottom: -260px; left: -180px; background: #0f3b60; }
        .card { position: relative; width: min(100%, 680px); overflow: hidden; border: 1px solid rgba(255,255,255,.9); border-radius: 30px; background: rgba(255,255,255,.91); box-shadow: 0 28px 80px rgba(6,20,35,.14); backdrop-filter: blur(18px); }
        .bar { height: 7px; background: linear-gradient(90deg, #0f766e, #2dd4bf, #0f3b60); }
        .content { padding: 42px; }
        .brand { display: flex; align-items: center; gap: 12px; color: #0f2740; font-weight: 800; }
        .brand-mark { display: grid; width: 44px; height: 44px; place-items: center; border-radius: 15px; color: #06201f; background: linear-gradient(135deg, #5eead4, #14b8a6); box-shadow: 0 10px 25px rgba(20,184,166,.25); }
        .eyebrow { margin: 40px 0 0; color: #0f766e; font-size: 12px; font-weight: 800; letter-spacing: .16em; text-transform: uppercase; }
        .code { margin: 10px 0 0; color: #0b2339; font-size: clamp(64px, 14vw, 108px); font-weight: 900; letter-spacing: -.075em; line-height: .88; }
        h1 { margin: 22px 0 0; color: #0b2339; font-size: clamp(27px, 5vw, 38px); letter-spacing: -.035em; line-height: 1.12; }
        .message { max-width: 540px; margin: 16px 0 0; color: #5b6878; font-size: 16px; line-height: 1.75; }
        .hint { display: flex; gap: 10px; margin-top: 24px; padding: 14px 16px; border: 1px solid #ccfbf1; border-radius: 16px; color: #115e59; background: #f0fdfa; font-size: 13px; line-height: 1.55; }
        .dot { width: 8px; height: 8px; flex: 0 0 auto; margin-top: 6px; border-radius: 99px; background: #14b8a6; box-shadow: 0 0 0 5px rgba(20,184,166,.12); }
        .actions { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 30px; }
        .button { display: inline-flex; min-height: 46px; align-items: center; justify-content: center; border-radius: 14px; padding: 0 20px; color: #fff; background: #0b2a43; font-size: 14px; font-weight: 800; text-decoration: none; box-shadow: 0 12px 24px rgba(11,42,67,.16); }
        .button:hover { background: #0f3b60; }
        .button-secondary { border: 1px solid #d9e2e8; color: #334155; background: #fff; box-shadow: none; }
        .button-secondary:hover { background: #f8fafc; }
        .footer { margin-top: 36px; color: #94a3b8; font-size: 12px; }
        @media (max-width: 560px) { .content { padding: 30px 24px; } .eyebrow { margin-top: 32px; } .actions { flex-direction: column; } .button { width: 100%; } }
    </style>
</head>
<body>
    <main class="shell">
        <span class="glow glow-one"></span>
        <span class="glow glow-two"></span>
        <section class="card" aria-labelledby="error-title">
            <div class="bar"></div>
            <div class="content">
                <div class="brand"><span class="brand-mark">BK</span><span>Sistem Informasi BK<br><small style="color:#64748b;font-weight:600">SMAN 1 Dompu</small></span></div>
                <p class="eyebrow">Pemberitahuan sistem</p>
                <p class="code">@yield('code')</p>
                <h1 id="error-title">@yield('title')</h1>
                <p class="message">@yield('message')</p>
                @hasSection('hint')
                    <div class="hint"><span class="dot"></span><span>@yield('hint')</span></div>
                @endif
                <div class="actions">
                    <a class="button" href="@yield('primary_url', url('/'))">@yield('primary_label', 'Kembali ke beranda')</a>
                    <a class="button button-secondary" href="{{ url()->previous() !== url()->current() ? url()->previous() : url('/') }}">Kembali ke halaman sebelumnya</a>
                </div>
                <p class="footer">Jika masalah terus terjadi, catat kode di atas dan hubungi pengelola Sistem Informasi BK.</p>
            </div>
        </section>
    </main>
</body>
</html>
