<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mantenimiento — {{ config('app.name') }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: system-ui, sans-serif; background: #f8fafc; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .container { text-align: center; padding: 2rem; max-width: 400px; }
        .icon { width: 64px; height: 64px; background: #6366f1; border-radius: 16px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; }
        h1 { font-size: 1.5rem; font-weight: 700; color: #1e293b; margin-bottom: 0.75rem; }
        p { color: #64748b; font-size: 0.95rem; line-height: 1.6; }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">
            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
            </svg>
        </div>
        <h1>En mantenimiento</h1>
        <p>El sistema está siendo actualizado. Vuelve en unos minutos.</p>
    </div>
</body>
</html>
