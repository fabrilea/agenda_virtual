<?php
/**
 * notificaciones.php
 * Helper de notificaciones automáticas: Email (PHPMailer) y WhatsApp (Twilio).
 *
 * Variables de entorno requeridas para Email:
 *   MAIL_HOST, MAIL_PORT, MAIL_USER, MAIL_PASS, MAIL_FROM, MAIL_FROM_NAME
 *
 * Variables de entorno opcionales para WhatsApp (Twilio):
 *   TWILIO_SID, TWILIO_TOKEN, TWILIO_WHATSAPP_FROM  (ej: whatsapp:+14155238886)
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception as MailException;

// ──────────────────────────────────────────────────────
// Mensajes por tipo de evento
// ──────────────────────────────────────────────────────
function _mensajeTurno(string $nombre, string $evento, string $fecha, string $hora): array
{
    $fechaFmt = date('d/m/Y', strtotime($fecha));
    $horaFmt  = date('H:i',   strtotime($hora));

    $textos = [
        'PENDIENTE' => [
            'asunto' => '✅ Reserva recibida – pendiente de confirmación',
            'html'   => "
                <h2>¡Hola, {$nombre}!</h2>
                <p>Tu solicitud de turno fue recibida correctamente.</p>
                <p><strong>📅 Fecha:</strong> {$fechaFmt}<br>
                   <strong>🕐 Hora:</strong> {$horaFmt}</p>
                <p>Tu turno está <span style='color:#f59e0b;font-weight:bold'>PENDIENTE</span>
                   de confirmación por parte del administrador.<br>
                   Te avisaremos cuando sea confirmado.</p>
            ",
            'texto'  => "Hola {$nombre}, tu turno del {$fechaFmt} a las {$horaFmt} fue recibido y está PENDIENTE de confirmación.",
        ],
        'CONFIRMADO' => [
            'asunto' => '🎉 Turno CONFIRMADO',
            'html'   => "
                <h2>¡Hola, {$nombre}!</h2>
                <p>Tu turno fue <span style='color:#16a34a;font-weight:bold'>CONFIRMADO</span>.</p>
                <p><strong>📅 Fecha:</strong> {$fechaFmt}<br>
                   <strong>🕐 Hora:</strong> {$horaFmt}</p>
                <p>Por favor, recuerda asistir puntualmente.</p>
            ",
            'texto'  => "Hola {$nombre}, tu turno del {$fechaFmt} a las {$horaFmt} fue CONFIRMADO. ¡Te esperamos!",
        ],
        'CANCELADO' => [
            'asunto' => '❌ Turno cancelado',
            'html'   => "
                <h2>Hola, {$nombre}.</h2>
                <p>Tu turno del <strong>{$fechaFmt}</strong> a las <strong>{$horaFmt}</strong>
                   fue <span style='color:#dc2626;font-weight:bold'>CANCELADO</span>.</p>
                <p>Si lo deseas, podés reservar un nuevo turno desde la agenda.</p>
            ",
            'texto'  => "Hola {$nombre}, tu turno del {$fechaFmt} a las {$horaFmt} fue CANCELADO.",
        ],
    ];

    return $textos[$evento] ?? [
        'asunto' => "Actualización de turno ({$evento})",
        'html'   => "<p>Hola {$nombre}, tu turno del {$fechaFmt} a las {$horaFmt} cambió a estado: {$evento}.</p>",
        'texto'  => "Hola {$nombre}, tu turno del {$fechaFmt} a las {$horaFmt} cambió a: {$evento}.",
    ];
}

// ──────────────────────────────────────────────────────
// Envío de Email via PHPMailer (SMTP)
// ──────────────────────────────────────────────────────
function enviarEmail(string $emailDestino, string $nombre, string $evento, string $fecha, string $hora): bool
{
    $host     = getenv('MAIL_HOST')      ?: '';
    $port     = (int)(getenv('MAIL_PORT') ?: 587);
    $user     = getenv('MAIL_USER')      ?: '';
    $pass     = getenv('MAIL_PASS')      ?: '';
    $from     = getenv('MAIL_FROM')      ?: $user;
    $fromName = getenv('MAIL_FROM_NAME') ?: 'Agenda Virtual';

    if (empty($host) || empty($user) || empty($pass)) {
        // Sin configuración SMTP: no se envía, no se interrumpe el flujo
        error_log('[Notificaciones] Email no enviado: faltan variables MAIL_HOST / MAIL_USER / MAIL_PASS');
        return false;
    }

    $msg = _mensajeTurno($nombre, $evento, $fecha, $hora);

    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = $host;
        $mail->SMTPAuth   = true;
        $mail->Username   = $user;
        $mail->Password   = $pass;
        $mail->SMTPSecure = $port === 465 ? PHPMailer::ENCRYPTION_SMIME : PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = $port;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom($from, $fromName);
        $mail->addAddress($emailDestino, $nombre);

        $mail->isHTML(true);
        $mail->Subject = $msg['asunto'];
        $mail->Body    = "<div style='font-family:sans-serif;max-width:600px;margin:auto'>" . $msg['html'] . "</div>";
        $mail->AltBody = $msg['texto'];

        $mail->send();
        return true;
    } catch (MailException $e) {
        error_log('[Notificaciones] Error al enviar email a ' . $emailDestino . ': ' . $e->getMessage());
        return false;
    }
}

// ──────────────────────────────────────────────────────
// Envío de WhatsApp via Twilio REST API
// Requiere: TWILIO_SID, TWILIO_TOKEN, TWILIO_WHATSAPP_FROM
// El teléfono del usuario debe incluir código de país, sin espacios.
// Ej: +5491112345678
// ──────────────────────────────────────────────────────
function enviarWhatsApp(string $telefono, string $nombre, string $evento, string $fecha, string $hora): bool
{
    $sid      = getenv('TWILIO_SID')            ?: '';
    $token    = getenv('TWILIO_TOKEN')          ?: '';
    $from     = getenv('TWILIO_WHATSAPP_FROM')  ?: '';   // ej: whatsapp:+14155238886

    if (empty($sid) || empty($token) || empty($from)) {
        error_log('[Notificaciones] WhatsApp no enviado: faltan variables TWILIO_SID / TWILIO_TOKEN / TWILIO_WHATSAPP_FROM');
        return false;
    }

    // Normalizar número: agregar prefijo whatsapp: si no lo tiene
    $telefono = trim($telefono);
    if (!str_starts_with($telefono, 'whatsapp:')) {
        $telefono = 'whatsapp:' . $telefono;
    }

    $msg  = _mensajeTurno($nombre, $evento, $fecha, $hora);
    $body = $msg['texto'];
    $url  = "https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json";

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_USERPWD        => "{$sid}:{$token}",
        CURLOPT_POSTFIELDS     => http_build_query([
            'From' => $from,
            'To'   => $telefono,
            'Body' => $body,
        ]),
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode >= 200 && $httpCode < 300) {
        return true;
    }

    error_log('[Notificaciones] Error Twilio WhatsApp (' . $httpCode . '): ' . $response);
    return false;
}

// ──────────────────────────────────────────────────────
// Función principal: orquesta email + WhatsApp
// ──────────────────────────────────────────────────────
function notificarCambioTurno(
    string $emailUsuario,
    string $nombreUsuario,
    string $evento,       // 'PENDIENTE' | 'CONFIRMADO' | 'CANCELADO'
    string $fecha,
    string $hora,
    string $telefonoUsuario = ''
): void {
    enviarEmail($emailUsuario, $nombreUsuario, $evento, $fecha, $hora);

    if (!empty($telefonoUsuario)) {
        enviarWhatsApp($telefonoUsuario, $nombreUsuario, $evento, $fecha, $hora);
    }
}
