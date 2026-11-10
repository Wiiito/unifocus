{{-- App instalável (PWA): manifest, ícones e metas para iOS. O service worker é registrado em resources/js/pwa.js. --}}
<link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
<link rel="apple-touch-icon" href="{{ asset('images/pwa/apple-touch-icon.png') }}">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-title" content="UniFocus">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
