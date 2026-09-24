<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>SimBrief · Prométhée</title>
    <style>
        :root{font-family:Inter,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;color:#102a43;background:#f3f7fb}
        body{min-height:100vh;margin:0;display:grid;place-items:center;padding:24px;box-sizing:border-box}
        main{max-width:620px;width:100%;background:#fff;border:1px solid #d9e2ec;border-radius:16px;padding:34px;box-shadow:0 18px 50px rgba(15,42,67,.12)}
        .eyebrow{font-size:12px;letter-spacing:.16em;text-transform:uppercase;color:#486581;font-weight:700}
        h1{margin:.45rem 0 .8rem;font-size:30px}
        p{line-height:1.55;color:#486581}
        .status{display:inline-flex;align-items:center;gap:8px;margin-top:14px;padding:9px 13px;border-radius:999px;background:{{ $success ? '#e7f7ee' : '#fff1f0' }};color:{{ $success ? '#176b3a' : '#9f2722' }};font-weight:700}
    </style>
</head>
<body>
<main>
    <span class="eyebrow">Air Inter · Prométhée</span>
    <h1>{{ $success ? 'Plan de vol reçu' : 'Retour SimBrief impossible' }}</h1>
    <p>{{ $message }}</p>
    <span class="status">{{ $success ? '✓ IMPORT PRÊT' : '× SESSION EXPIRÉE' }}</span>
    @if($success)
        <p>Cette fenêtre va se fermer automatiquement.</p>
        <script>setTimeout(() => window.close(), 900);</script>
    @endif
</main>
</body>
</html>
