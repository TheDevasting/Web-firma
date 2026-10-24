<?php
declare(strict_types=1);

// Nunca mostrar errores de PHP al visitante: rompen el JSON y revelan datos del servidor.
// Los errores quedan en el registro del servidor (error_log).
ini_set('display_errors', '0');

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
date_default_timezone_set('America/Bogota');

/* ============================================================
   CONFIGURACIÓN
   ============================================================ */
const MAX_ENVIOS_POR_HORA = 3;   // por IP y por correo del cliente
const EXIGIR_AUTORIZACION = false; // ponla en true cuando agregues el checkbox name="acepto" value="si"
const CORREO_GENERAL      = 'info@somoslegalmente.com';
const CORREO_SISTEMA      = 'consultas@somoslegalmente.com'; // debe existir en tu dominio (SPF/DKIM)
const CORREO_COPIA        = 'consultas@somoslegalmente.com'; // recibe una copia independiente de TODAS las consultas

// Carpeta FUERA de public_html para guardar un respaldo de cada consulta.
// Así, si el correo falla, la consulta no se pierde.
const RUTA_LOG = __DIR__ . '/../datos_privados/consultas.log';

// Clave para cifrar el respaldo. Se crea una sola vez con: php consultas.php clave
// Nunca la subas a Git ni la pongas dentro de public_html.
const RUTA_CLAVE = __DIR__ . '/../datos_privados/clave.key';

// REPARTO POR ÁREA: cambia cada 'info@somoslegalmente.com' por el correo del abogado
// de esa área. Puede ser cualquier correo (no tiene que existir en tu hosting).
// Mientras no lo cambies, todo llega a info@ para que una persona lo reparta.
// slug del <select>  =>  [nombre legible, correo del abogado]
const AREAS = [
    'consultoria'            => ['Consultoría Jurídica General',        'info@somoslegalmente.com'],
    'derecho-civil'          => ['Derecho Civil y Contratos',           'info@somoslegalmente.com'],
    'derecho-comercial'      => ['Derecho Comercial y Financiero',      'info@somoslegalmente.com'],
    'derecho-penal'          => ['Derecho Penal',                       'info@somoslegalmente.com'],
    'derecho-laboral'        => ['Derecho Laboral',                     'info@somoslegalmente.com'],
    'derecho-administrativo' => ['Derecho Administrativo',              'info@somoslegalmente.com'],
    'otro'                   => ['Otro asunto legal',                   CORREO_GENERAL],
];

/* ============================================================
   FUNCIONES AUXILIARES
   ============================================================ */
// Cifra un texto con libsodium (clave secreta). Devuelve null si falta la clave o la extensión.
function cifrar_registro(string $texto): ?string
{
    if (!function_exists('sodium_crypto_secretbox')) {
        return null;
    }
    $clave = base64_decode(trim((string) @file_get_contents(RUTA_CLAVE)), true);
    if ($clave === false || strlen($clave) !== SODIUM_CRYPTO_SECRETBOX_KEYBYTES) {
        return null;
    }
    $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
    return base64_encode($nonce . sodium_crypto_secretbox($texto, $nonce, $clave));
}

function responder(string $status, string $mensaje, int $codigo = 200): void
{
    http_response_code($codigo);
    echo json_encode(['status' => $status, 'message' => $mensaje], JSON_UNESCAPED_UNICODE);
    exit;
}

// Texto de una sola línea: quita saltos de línea y caracteres de control
function limpiar_linea(string $v): string
{
    $v = preg_replace('/[\p{C}]+/u', ' ', $v) ?? '';
    $v = preg_replace('/\s+/u', ' ', $v) ?? '';
    return trim($v);
}

// Texto de varias líneas: conserva los saltos de línea, quita el resto de caracteres de control
function limpiar_texto(string $v): string
{
    $v = str_replace(["\r\n", "\r"], "\n", $v);
    $v = preg_replace('/[^\P{C}\n]+/u', '', $v) ?? '';
    return trim($v);
}

// Limita cuántas veces puede enviar una misma clave (IP o correo) por hora
function limite_excedido(string $clave): bool
{
    $dir = sys_get_temp_dir() . '/legalmente_rl';
    if (!is_dir($dir)) {
        @mkdir($dir, 0700, true);
    }
    $archivo = $dir . '/' . hash('sha256', $clave) . '.json';
    $ahora   = time();
    $marcas  = [];

    if (is_file($archivo)) {
        $datos = json_decode((string) @file_get_contents($archivo), true);
        if (is_array($datos)) {
            $marcas = array_values(array_filter($datos, function ($t) use ($ahora) {
                return is_int($t) && ($ahora - $t) < 3600;
            }));
        }
    }

    if (count($marcas) >= MAX_ENVIOS_POR_HORA) {
        return true;
    }

    $marcas[] = $ahora;
    @file_put_contents($archivo, json_encode($marcas), LOCK_EX);
    return false;
}

function enviar_correo(string $para, string $asunto, string $cuerpo, string $nombre_remitente, string $correo_remitente, ?string $reply_to = null): bool
{
    $headers = [
        'MIME-Version: 1.0',
        'Content-Type: text/plain; charset=UTF-8',
        'Content-Transfer-Encoding: 8bit',
        'From: ' . mb_encode_mimeheader($nombre_remitente, 'UTF-8') . ' <' . $correo_remitente . '>',
        'X-Mailer: PHP/' . phpversion(),
    ];
    if ($reply_to !== null) {
        $headers[] = 'Reply-To: ' . $reply_to;
    }
    return @mail($para, mb_encode_mimeheader($asunto, 'UTF-8'), $cuerpo, implode("\r\n", $headers));
}

/* ============================================================
   1. SOLO POST
   ============================================================ */
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    responder('error', 'Acceso no permitido', 405);
}

/* ============================================================
   2. ANTI-SPAM: campo trampa (honeypot)
   Los bots lo llenan, las personas no lo ven.
   Se responde "éxito" para que el bot no aprenda nada.
   ============================================================ */
if (!empty($_POST['sitio_web'])) {
    responder('success', 'Consulta despachada correctamente.');
}

/* ============================================================
   3. LECTURA Y VALIDACIÓN EN EL SERVIDOR
   ============================================================ */
$nombre   = limpiar_linea((string) ($_POST['nombre'] ?? ''));
$correo   = trim((string) ($_POST['correo'] ?? ''));
$telefono = limpiar_linea((string) ($_POST['telefono'] ?? ''));
$servicio = (string) ($_POST['servicio'] ?? '');
$mensaje  = limpiar_texto((string) ($_POST['mensaje'] ?? ''));

$errores = [];

if (mb_strlen($nombre) < 3 || mb_strlen($nombre) > 100 || !preg_match('/^[\p{L}\s\'’.-]+$/u', $nombre)) {
    $errores[] = 'nombre';
}
if (strlen($correo) > 254 || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    $errores[] = 'correo';
}
$digitos = preg_replace('/\D/', '', $telefono) ?? '';
if (!preg_match('/^[0-9+\s-]{7,20}$/', $telefono) || strlen($digitos) < 7 || strlen($digitos) > 15) {
    $errores[] = 'teléfono';
}
if (!array_key_exists($servicio, AREAS)) {
    $errores[] = 'área de consulta';
}
if (mb_strlen($mensaje) < 10 || mb_strlen($mensaje) > 3000) {
    $errores[] = 'detalles del caso';
}
if (EXIGIR_AUTORIZACION && (($_POST['acepto'] ?? '') !== 'si')) {
    $errores[] = 'autorización de tratamiento de datos';
}

if ($errores) {
    responder('error', 'Revisa estos campos: ' . implode(', ', $errores) . '.', 422);
}

/* ============================================================
   4. LÍMITE DE ENVÍOS (evita spam y que usen tu autorespuesta
      para molestar a terceros con su correo)
   ============================================================ */
$ip = $_SERVER['REMOTE_ADDR'] ?? 'desconocida';
if (limite_excedido('ip:' . $ip) || limite_excedido('mail:' . strtolower($correo))) {
    responder('error', 'Has enviado varias consultas en poco tiempo. Por favor inténtalo más tarde.', 429);
}

/* ============================================================
   5. RESPALDO LOCAL DE LA CONSULTA
   ============================================================ */
[$nombre_area, $destinatario_interno] = AREAS[$servicio];
$fecha = date('d/m/Y H:i:s');

$dir_log = dirname(RUTA_LOG);
if (!is_dir($dir_log)) {
    @mkdir($dir_log, 0700, true);
}
$registro = json_encode([
    'fecha'    => $fecha,
    'nombre'   => $nombre,
    'correo'   => $correo,
    'telefono' => $telefono,
    'area'     => $nombre_area,
    'mensaje'  => $mensaje,
], JSON_UNESCAPED_UNICODE);

// El respaldo se guarda CIFRADO. Si falta la clave, no se guarda en texto plano.
$cifrado = cifrar_registro($registro);
if ($cifrado === null) {
    error_log('Legalmente: falta la clave de cifrado (clave.key) o la extensión sodium.');
    responder(
        'error',
        'No pudimos registrar tu consulta en este momento. Por favor inténtalo más tarde o escríbenos a ' . CORREO_GENERAL . '.',
        500
    );
}
$guardado = @file_put_contents(RUTA_LOG, $cifrado . "\n", FILE_APPEND | LOCK_EX) !== false;
@chmod(RUTA_LOG, 0600);

/* ============================================================
   6. CORREO INTERNO AL EQUIPO LEGAL
   ============================================================ */
$cuerpo_interno  = "==================================================\n";
$cuerpo_interno .= "SISTEMA DE RECEPCIÓN CONFIDENCIAL - LEGALMENTE\n";
$cuerpo_interno .= "==================================================\n\n";
$cuerpo_interno .= "FECHA: {$fecha}\n";
$cuerpo_interno .= "NOMBRE CLIENTE: {$nombre}\n";
$cuerpo_interno .= "CORREO ELECTRÓNICO: {$correo}\n";
$cuerpo_interno .= "TELÉFONO / WHATSAPP: {$telefono}\n";
$cuerpo_interno .= "ÁREA SOLICITADA: {$nombre_area}\n\n";
$cuerpo_interno .= "DETALLES DEL CASO:\n";
$cuerpo_interno .= "--------------------------------------------------\n";
$cuerpo_interno .= $mensaje . "\n";
$cuerpo_interno .= "--------------------------------------------------\n";

$enviado_interno = enviar_correo(
    $destinatario_interno,
    "Nueva consulta legal - {$nombre_area} - {$nombre}",
    $cuerpo_interno,
    'Formulario Web',
    CORREO_SISTEMA,
    $correo
);

// Copia INDEPENDIENTE para consultas@ (respaldo de todos los casos).
// Es un correo aparte: si falla uno, el otro llega igual.
// Si el área ya va a consultas@, no se repite.
if (strcasecmp($destinatario_interno, CORREO_COPIA) !== 0) {
    enviar_correo(
        CORREO_COPIA,
        "[Copia] Nueva consulta legal - {$nombre_area} - {$nombre}",
        $cuerpo_interno,
        'Formulario Web',
        CORREO_SISTEMA,
        $correo
    );
}

// Si no se pudo ni enviar el correo ni guardar el respaldo, no decimos "éxito"
if (!$enviado_interno && !$guardado) {
    responder(
        'error',
        'No pudimos registrar tu consulta en este momento. Por favor inténtalo de nuevo o escríbenos a ' . CORREO_GENERAL . '.',
        500
    );
}

/* ============================================================
   7. ACUSE DE RECIBO AL CLIENTE
   ============================================================ */
$cuerpo_cliente  = "Estimado/a {$nombre},\n\n";
$cuerpo_cliente .= "Confirmamos la recepción de su solicitud de asistencia legal en el área de {$nombre_area}.\n\n";
$cuerpo_cliente .= "Bajo nuestro compromiso de secreto profesional y confidencialidad, un abogado especialista evaluará la información suministrada y se pondrá en contacto con usted a la brevedad posible.\n\n";
$cuerpo_cliente .= "Atentamente,\n";
$cuerpo_cliente .= "Equipo de Asesoría Jurídica\n";
$cuerpo_cliente .= "Legalmente | www.somoslegalmente.com\n";

enviar_correo(
    $correo,
    'Acuse de recibo: hemos recibido su consulta - Legalmente',
    $cuerpo_cliente,
    'Legalmente Firma de Abogados',
    CORREO_GENERAL
);

responder('success', 'Consulta despachada correctamente.');