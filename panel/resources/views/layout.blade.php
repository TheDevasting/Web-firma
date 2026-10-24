<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>@yield('titulo', 'Panel') | Legalmente</title>
<style>
:root{--m:#3D0066;--d:#C59B27;--g:#5d5668}
*{box-sizing:border-box}
body{margin:0;font-family:'Segoe UI',Tahoma,sans-serif;background:#f6f4f9;color:#2a2233;line-height:1.6}
.caja{max-width:440px;margin:8vh auto;background:#fff;padding:32px;border-top:4px solid var(--d);border-radius:6px;box-shadow:0 8px 28px rgba(61,0,102,.1)}
.caja.ancho{max-width:720px;margin:5vh auto}
h1{color:var(--m);font-size:1.5rem;margin:0 0 6px}
h2{color:var(--m);font-size:1.15rem;margin:24px 0 6px}
p.sub{color:var(--g);margin:0 0 20px}
label{display:block;font-weight:600;color:var(--m);margin:14px 0 6px}
input[type=text],input[type=email],input[type=password]{width:100%;padding:11px 13px;border:1px solid #cfc4de;border-radius:4px;font:inherit}
input:focus{outline:2px solid var(--d)}
button{margin-top:16px;width:100%;padding:12px;border:0;border-radius:4px;background:var(--d);color:#1a002c;font:inherit;font-weight:700;cursor:pointer}
button.sec{background:#fff;border:2px solid var(--m);color:var(--m)}
.error{color:#b3261e;font-size:.9rem;margin:6px 0 0}
.ok{background:#eef8ee;border-left:4px solid #2e7d32;padding:10px 14px;margin:0 0 16px}
.barra{display:flex;justify-content:space-between;align-items:center;padding:12px 5%;background:#fff;box-shadow:0 1px 0 rgba(61,0,102,.12)}
.barra a{color:var(--m);font-weight:600;text-decoration:none;margin-right:18px}
.barra form{margin:0}.barra button{margin:0;width:auto;padding:8px 16px}
code{font-family:Consolas,monospace;background:#f3ecf8;padding:2px 6px;border-radius:4px}
.codes{columns:2;padding-left:18px;font-family:Consolas,monospace}
.qr{max-width:200px;margin:12px 0}
table{width:100%;border-collapse:collapse;margin:8px 0 16px}
th,td{text-align:left;padding:10px 8px;border-bottom:1px solid #e3dded}
th{color:var(--m);font-size:.9rem}
button.peq{margin:0;width:auto;padding:7px 14px;font-size:.9rem}
td form{margin:0}
</style>
</head>
<body>
@yield('contenido')
</body>
</html>
