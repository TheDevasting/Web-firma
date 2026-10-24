<?php
// Herramienta SOLO para la terminal. NO la pongas dentro de public_html.
//
//   php consultas.php clave   -> crea la clave de cifrado (una sola vez)
//   php consultas.php leer    -> muestra las consultas descifradas

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Solo se puede ejecutar desde la terminal.');
}

$dir          = __DIR__ . '/../datos_privados';
$archivoClave = $dir . '/clave.key';
$archivoLog   = $dir . '/consultas.log';
$accion       = $argv[1] ?? '';

if ($accion === 'clave') {
    if (file_exists($archivoClave)) {
        exit("Ya existe una clave. No se sobrescribe, porque perderías el acceso a las consultas cifradas.\n");
    }
    if (!is_dir($dir)) {
        mkdir($dir, 0700, true);
    }
    file_put_contents($archivoClave, base64_encode(sodium_crypto_secretbox_keygen()));
    chmod($archivoClave, 0600);
    exit("Clave creada en: $archivoClave\nHAZ UNA COPIA SEGURA de ese archivo en otro lugar: si lo pierdes, no podrás leer las consultas.\n");
}

if ($accion === 'leer') {
    $clave = base64_decode(trim((string) @file_get_contents($archivoClave)), true);
    if ($clave === false || strlen($clave) !== SODIUM_CRYPTO_SECRETBOX_KEYBYTES) {
        exit("Clave no encontrada o inválida. Crea una con: php consultas.php clave\n");
    }
    $lineas = @file($archivoLog, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
    if (!$lineas) {
        exit("Todavía no hay consultas guardadas.\n");
    }
    foreach ($lineas as $n => $linea) {
        $num = $n + 1;
        $bin = base64_decode($linea, true);
        if ($bin === false || strlen($bin) < SODIUM_CRYPTO_SECRETBOX_NONCEBYTES + SODIUM_CRYPTO_SECRETBOX_MACBYTES) {
            echo "#$num: línea no válida\n";
            continue;
        }
        $nonce = substr($bin, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $texto = sodium_crypto_secretbox_open(substr($bin, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES), $nonce, $clave);
        if ($texto === false) {
            echo "#$num: no se pudo descifrar (¿clave distinta?)\n";
            continue;
        }
        $datos = json_decode($texto, true);
        echo str_repeat('=', 50) . "\nCONSULTA #$num\n";
        foreach ((array) $datos as $campo => $valor) {
            echo strtoupper($campo) . ': ' . $valor . "\n";
        }
    }
    exit;
}

echo "Uso:\n";
echo "  php consultas.php clave   (crea la clave de cifrado, una sola vez)\n";
echo "  php consultas.php leer    (muestra las consultas descifradas)\n";