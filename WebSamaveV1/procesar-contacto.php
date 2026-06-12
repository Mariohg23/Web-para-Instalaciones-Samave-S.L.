<?php
// PHP 8
declare(strict_types=1);
header('Content-Type: text/plain; charset=utf-8');

// Comprobación (Solo POST)
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo "Método de envío no permitido.";
    exit;
}

// Validación

$dominios_permitidos = ['samave.es', 'www.samave.es', 'localhost'];
$referer = $_SERVER['HTTP_REFERER'] ?? '';
$origen_valido = false;

foreach ($dominios_permitidos as $dominio) {
    if (strpos($referer, $dominio) !== false) {
        $origen_valido = true;
        break;
    }
}

if (!$origen_valido && $referer !== '') { 
    http_response_code(403);
    echo "Error 403: Origen de la petición no seguro.";
    exit;
}

// Antispam

$honeypot = filter_input(INPUT_POST, 'website_url', FILTER_DEFAULT);

if (!empty($honeypot)) {
    http_response_code(200);
    echo "¡Mensaje enviado con éxito! Nos pondremos en contacto contigo lo antes posible.";
    exit;
}


// Procesamiento Formulario
$nombre  = filter_input(INPUT_POST, 'nombre', FILTER_DEFAULT);
$email   = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
$mensaje = filter_input(INPUT_POST, 'mensaje', FILTER_DEFAULT);

$nombre  = $nombre ? htmlspecialchars(strip_tags(trim($nombre))) : '';
$mensaje = $mensaje ? htmlspecialchars(strip_tags(trim($mensaje))) : '';

// Validamos que no falte nada
if (empty($nombre) || empty($mensaje) || !$email) {
    http_response_code(400);
    echo "Por favor, rellene todos los campos correctamente.";
    exit;
}

// Configuramos el correo a enviar a la empresa
$destinatario = "contacto@samave.es"; 
$asunto       = "Nueva consulta web de: " . $nombre;

$cuerpoEmail = <<<EOD
Has recibido un nuevo mensaje desde el formulario de la página web.

Información del contacto:
--------------------------------------------------
Nombre: $nombre
Email:  $email

Mensaje enviado:
--------------------------------------------------
$mensaje
EOD;

$cabeceras = [
    'From'         => 'no-responder@samave.es', 
    'Reply-To'     => $email,
    'X-Mailer'     => 'PHP/' . phpversion(),
    'Content-Type' => 'text/plain; charset=UTF-8'
];

// Enviamos el correo final
if (mail($destinatario, $asunto, $cuerpoEmail, $cabeceras)) {
    http_response_code(200);
    echo "¡Mensaje enviado con éxito! Nos pondremos en contacto con usted lo antes posible.";
} else {
    http_response_code(500);
    echo "Hubo un problema en el servidor de correo. Por favor, inténtelo de nuevo más tarde.";
}