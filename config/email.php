<?php
// Funciones para manejo de emails - Compatible con XAMPP

function enviarEmailConsulta($email, $nombre, $asunto) {
    // Crear directorio de emails simulados si no existe
    $email_dir = __DIR__ . '/../emails_simulados';
    if (!file_exists($email_dir)) {
        mkdir($email_dir, 0777, true);
    }
    
    $subject = "Confirmación de consulta - Quantum Tour";
    $message = generarHtmlEmailConsulta($email, $nombre, $asunto);
    
    // Guardar email en archivo para visualización
    $filename = $email_dir . '/consulta_' . date('Y-m-d_H-i-s') . '_' . sanitizeFilename($email) . '.html';
    file_put_contents($filename, $message);
    
    // Log para debugging
    error_log("EMAIL SIMULADO - Consulta guardado en: $filename");
    error_log("Destinatario: $email");
    error_log("Asunto: $subject");
    
    // Intentar envío real si está configurado
    $email_real_enviado = false;
    if (function_exists('mail') && isEmailConfigured()) {
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: Quantum Tour <noreply@quantumtour.com>" . "\r\n";
        
        $email_real_enviado = mail($email, $subject, $message, $headers);
    }
    
    return [
        'success' => true,
        'archivo_generado' => $filename,
        'email_real_enviado' => $email_real_enviado
    ];
}

function enviarEmailCompra($email, $nombre, $numero_pedido, $total, $productos) {
    // Crear directorio de emails simulados si no existe
    $email_dir = __DIR__ . '/../emails_simulados';
    if (!file_exists($email_dir)) {
        mkdir($email_dir, 0777, true);
    }
    
    $subject = "Confirmación de compra #$numero_pedido - Quantum Tour";
    $message = generarHtmlEmailCompra($email, $nombre, $numero_pedido, $total, $productos);
    
    // Guardar email en archivo para visualización
    $filename = $email_dir . '/compra_' . date('Y-m-d_H-i-s') . '_' . $numero_pedido . '.html';
    file_put_contents($filename, $message);
    
    // Log para debugging
    error_log("EMAIL SIMULADO - Compra guardado en: $filename");
    error_log("Destinatario: $email");
    error_log("Pedido: $numero_pedido");
    error_log("Total: " . formatPrice($total));
    
    // Intentar envío real si está configurado
    $email_real_enviado = false;
    if (function_exists('mail') && isEmailConfigured()) {
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: Quantum Tour <noreply@quantumtour.com>" . "\r\n";
        
        $email_real_enviado = mail($email, $subject, $message, $headers);
    }
    
    return [
        'success' => true,
        'archivo_generado' => $filename,
        'email_real_enviado' => $email_real_enviado,
        'numero_pedido' => $numero_pedido
    ];
}

function generarHtmlEmailConsulta($email, $nombre, $asunto) {
    return "
    <!DOCTYPE html>
    <html lang='es'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Confirmación de consulta - Quantum Tour</title>
        <style>
            body { 
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
                line-height: 1.6; 
                color: #333; 
                margin: 0; 
                padding: 0; 
                background-color: #f4f4f4;
            }
            .container { 
                max-width: 600px; 
                margin: 20px auto; 
                background: white;
                border-radius: 10px;
                overflow: hidden;
                box-shadow: 0 0 20px rgba(0,0,0,0.1);
            }
            .header { 
                background: linear-gradient(135deg, #6366f1, #8b5cf6); 
                color: white; 
                padding: 40px 30px; 
                text-align: center; 
            }
            .header h1 {
                margin: 0;
                font-size: 28px;
                font-weight: 700;
            }
            .header .subtitle {
                margin: 10px 0 0 0;
                font-size: 18px;
                opacity: 0.9;
            }
            .content { 
                padding: 40px 30px; 
            }
            .content h3 {
                color: #6366f1;
                margin-bottom: 20px;
                font-size: 22px;
            }
            .content p {
                margin-bottom: 15px;
                font-size: 16px;
                line-height: 1.6;
            }
            .info-box {
                background: #f8fafc;
                border-left: 4px solid #6366f1;
                padding: 20px;
                margin: 25px 0;
                border-radius: 0 8px 8px 0;
            }
            .info-box h4 {
                margin: 0 0 15px 0;
                color: #374151;
                font-size: 18px;
            }
            .info-box ul {
                margin: 0;
                padding-left: 20px;
            }
            .info-box li {
                margin-bottom: 8px;
                color: #6b7280;
            }
            .cta-section {
                text-align: center;
                margin: 30px 0;
            }
            .btn {
                display: inline-block;
                background: #6366f1;
                color: white;
                padding: 15px 30px;
                text-decoration: none;
                border-radius: 8px;
                font-weight: 600;
                font-size: 16px;
                transition: background 0.3s;
            }
            .btn:hover {
                background: #5855eb;
            }
            .footer {
                background: #1f2937;
                color: #9ca3af;
                padding: 30px;
                text-align: center;
            }
            .footer h3 {
                color: white;
                margin: 0 0 15px 0;
                font-size: 20px;
            }
            .footer p {
                margin: 5px 0;
                font-size: 14px;
            }
            .contact-info {
                display: flex;
                justify-content: center;
                gap: 30px;
                margin-top: 20px;
                flex-wrap: wrap;
            }
            .contact-item {
                display: flex;
                align-items: center;
                gap: 8px;
                font-size: 14px;
            }
            @media (max-width: 600px) {
                .container { margin: 10px; }
                .header, .content { padding: 20px; }
                .contact-info { flex-direction: column; gap: 10px; }
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🌍 Quantum Tour</h1>
                <div class='subtitle'>Consulta Recibida Exitosamente</div>
            </div>
            <div class='content'>
                <h3>¡Hola " . htmlspecialchars($nombre) . "!</h3>
                <p>Gracias por contactarte con <strong>Quantum Tour</strong>. Hemos recibido tu consulta y queremos asegurarte que nuestro equipo de expertos en viajes la revisará cuidadosamente.</p>
                
                <div class='info-box'>
                    <h4>📋 Detalles de tu consulta:</h4>
                    <ul>
                        <li><strong>Nombre:</strong> " . htmlspecialchars($nombre) . "</li>
                        <li><strong>Email:</strong> " . htmlspecialchars($email) . "</li>
                        <li><strong>Asunto:</strong> " . htmlspecialchars($asunto) . "</li>
                        <li><strong>Fecha:</strong> " . date('d/m/Y H:i') . "</li>
                        <li><strong>Número de referencia:</strong> QT-" . date('Ymd-His') . "</li>
                    </ul>
                </div>
                
                <p><strong>⏰ Tiempo de respuesta:</strong> Te responderemos en un plazo máximo de <strong>24 horas hábiles</strong>.</p>
                
                <p>Mientras tanto, te invitamos a:</p>
                <ul>
                    <li>🌟 Explorar nuestros paquetes destacados</li>
                    <li>📞 Llamarnos directamente al +54 11 4567-8900</li>
                    <li>💬 Escribirnos por WhatsApp al +54 9 11 1234-5678</li>
                    <li>📧 Responder este email si tienes información adicional</li>
                </ul>
                
                <div class='cta-section'>
                    <a href='#' class='btn'>🎒 Ver Paquetes Turísticos</a>
                </div>
                
                <p style='margin-top: 30px; font-style: italic; color: #6b7280;'>
                    Este es un email automático de confirmación. Si no realizaste esta consulta, 
                    por favor ignora este mensaje o contáctanos para reportar el problema.
                </p>
            </div>
            <div class='footer'>
                <h3>Quantum Tour</h3>
                <p>Transformamos tus sueños de viaje en experiencias extraordinarias</p>
                <div class='contact-info'>
                    <div class='contact-item'>📧 info@quantumtour.com</div>
                    <div class='contact-item'>📞 +54 11 4567-8900</div>
                    <div class='contact-item'>📍 Av. Corrientes 1234, CABA</div>
                </div>
                <p style='margin-top: 20px; font-size: 12px; opacity: 0.7;'>
                    © " . date('Y') . " Quantum Tour. Todos los derechos reservados.
                </p>
            </div>
        </div>
    </body>
    </html>";
}

function generarHtmlEmailCompra($email, $nombre, $numero_pedido, $total, $productos) {
    $productos_html = "";
    foreach ($productos as $producto) {
        $productos_html .= "
        <tr style='border-bottom: 1px solid #e5e7eb;'>
            <td style='padding: 15px 10px; color: #374151;'>" . htmlspecialchars($producto['nombre']) . "</td>
            <td style='padding: 15px 10px; text-align: center; color: #6b7280;'>" . $producto['cantidad'] . "</td>
            <td style='padding: 15px 10px; text-align: right; color: #6b7280;'>" . formatPrice($producto['precio_unitario']) . "</td>
            <td style='padding: 15px 10px; text-align: right; font-weight: 600; color: #059669;'>" . formatPrice($producto['subtotal']) . "</td>
        </tr>";
    }
    
    return "
    <!DOCTYPE html>
    <html lang='es'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Confirmación de compra #$numero_pedido - Quantum Tour</title>
        <style>
            body { 
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
                line-height: 1.6; 
                color: #333; 
                margin: 0; 
                padding: 0; 
                background-color: #f4f4f4;
            }
            .container { 
                max-width: 650px; 
                margin: 20px auto; 
                background: white;
                border-radius: 10px;
                overflow: hidden;
                box-shadow: 0 0 20px rgba(0,0,0,0.1);
            }
            .header { 
                background: linear-gradient(135deg, #10b981, #059669); 
                color: white; 
                padding: 40px 30px; 
                text-align: center; 
            }
            .success-icon {
                font-size: 60px;
                margin-bottom: 15px;
                display: block;
            }
            .header h1 {
                margin: 0;
                font-size: 28px;
                font-weight: 700;
            }
            .header .subtitle {
                margin: 10px 0 0 0;
                font-size: 18px;
                opacity: 0.9;
            }
            .content { 
                padding: 40px 30px; 
            }
            .order-summary {
                background: #f8fafc;
                border-radius: 10px;
                padding: 25px;
                margin: 25px 0;
                border: 1px solid #e5e7eb;
            }
            .order-summary h4 {
                margin: 0 0 20px 0;
                color: #374151;
                font-size: 20px;
                display: flex;
                align-items: center;
                gap: 10px;
            }
            .order-info {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 15px;
                margin-bottom: 25px;
            }
            .info-item {
                background: white;
                padding: 15px;
                border-radius: 8px;
                border: 1px solid #e5e7eb;
            }
            .info-item strong {
                color: #374151;
                display: block;
                margin-bottom: 5px;
            }
            .info-item span {
                color: #6b7280;
                font-size: 15px;
            }
            .products-table {
                width: 100%;
                border-collapse: collapse;
                margin: 20px 0;
                background: white;
                border-radius: 8px;
                overflow: hidden;
                border: 1px solid #e5e7eb;
            }
            .products-table th {
                background: #6366f1;
                color: white;
                padding: 15px 10px;
                text-align: left;
                font-weight: 600;
            }
            .products-table th:nth-child(2),
            .products-table th:nth-child(3),
            .products-table th:nth-child(4) {
                text-align: center;
            }
            .products-table th:nth-child(3),
            .products-table th:nth-child(4) {
                text-align: right;
            }
            .total-section {
                background: #f0f9ff;
                padding: 20px;
                border-radius: 8px;
                margin: 25px 0;
                text-align: right;
            }
            .total-section h3 {
                margin: 0;
                font-size: 24px;
                color: #059669;
            }
            .next-steps {
                background: #fef3c7;
                border-left: 4px solid #f59e0b;
                padding: 20px;
                margin: 25px 0;
                border-radius: 0 8px 8px 0;
            }
            .next-steps h4 {
                margin: 0 0 15px 0;
                color: #92400e;
                font-size: 18px;
            }
            .next-steps ul {
                margin: 0;
                padding-left: 20px;
            }
            .next-steps li {
                margin-bottom: 8px;
                color: #a16207;
            }
            .contact-section {
                background: #f3f4f6;
                padding: 20px;
                border-radius: 8px;
                margin: 25px 0;
            }
            .contact-section h4 {
                margin: 0 0 15px 0;
                color: #374151;
            }
            .contact-methods {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 10px;
            }
            .contact-method {
                color: #6b7280;
                font-size: 14px;
            }
            .footer {
                background: #1f2937;
                color: #9ca3af;
                padding: 30px;
                text-align: center;
            }
            .footer h3 {
                color: white;
                margin: 0 0 10px 0;
                font-size: 20px;
            }
            .footer p {
                margin: 5px 0;
                font-size: 14px;
            }
            @media (max-width: 600px) {
                .container { margin: 10px; }
                .header, .content { padding: 20px; }
                .order-info { grid-template-columns: 1fr; }
                .contact-methods { grid-template-columns: 1fr; }
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <span class='success-icon'>✅</span>
                <h1>🌍 Quantum Tour</h1>
                <div class='subtitle'>¡Compra Confirmada Exitosamente!</div>
            </div>
            <div class='content'>
                <h3 style='color: #059669; font-size: 24px; margin-bottom: 20px;'>¡Hola " . htmlspecialchars($nombre) . "!</h3>
                <p style='font-size: 18px; margin-bottom: 25px;'>¡Excelente noticia! Tu compra ha sido procesada exitosamente y ya estamos preparando tu increíble experiencia de viaje.</p>
                
                <div class='order-summary'>
                    <h4>📋 Resumen de tu Pedido</h4>
                    <div class='order-info'>
                        <div class='info-item'>
                            <strong>Número de Pedido</strong>
                            <span>$numero_pedido</span>
                        </div>
                        <div class='info-item'>
                            <strong>Fecha de Compra</strong>
                            <span>" . date('d/m/Y H:i') . "</span>
                        </div>
                        <div class='info-item'>
                            <strong>Cliente</strong>
                            <span>" . htmlspecialchars($nombre) . "</span>
                        </div>
                        <div class='info-item'>
                            <strong>Email</strong>
                            <span>" . htmlspecialchars($email) . "</span>
                        </div>
                    </div>
                    
                    <h4 style='margin-top: 25px;'>🎒 Productos Adquiridos</h4>
                    <table class='products-table'>
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Cantidad</th>
                                <th>Precio Unit.</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            $productos_html
                        </tbody>
                    </table>
                    
                    <div class='total-section'>
                        <h3>💰 Total Pagado: " . formatPrice($total) . "</h3>
                    </div>
                </div>
                
                <div class='next-steps'>
                    <h4>📞 Próximos Pasos</h4>
                    <p>Nuestro equipo se pondrá en contacto contigo en las próximas <strong>24 horas</strong> para:</p>
                    <ul>
                        <li>✈️ Coordinar fechas exactas y detalles del viaje</li>
                        <li>📄 Enviarte toda la documentación necesaria</li>
                        <li>🎯 Personalizar tu experiencia según tus preferencias</li>
                        <li>📋 Confirmar itinerarios detallados</li>
                        <li>🛡️ Gestionar seguros de viaje si es necesario</li>
                    </ul>
                </div>
                
                <div class='contact-section'>
                    <h4>💬 ¿Tienes alguna pregunta o necesitas ayuda inmediata?</h4>
                    <div class='contact-methods'>
                        <div class='contact-method'>📞 <strong>Teléfono:</strong> +54 11 4567-8900</div>
                        <div class='contact-method'>📧 <strong>Email:</strong> info@quantumtour.com</div>
                        <div class='contact-method'>💬 <strong>WhatsApp:</strong> +54 9 11 1234-5678</div>
                        <div class='contact-method'>🕒 <strong>Horarios:</strong> Lun-Vie 9:00-18:00</div>
                    </div>
                </div>
                
                <p style='margin-top: 30px; padding: 20px; background: #ecfdf5; border-radius: 8px; color: #065f46; text-align: center; font-weight: 500;'>
                    🎉 ¡Gracias por confiar en Quantum Tour para tu próxima aventura! 
                    Estamos emocionados de ser parte de esta experiencia única.
                </p>
            </div>
            <div class='footer'>
                <h3>Quantum Tour</h3>
                <p>Tu aventura comienza aquí - Transformamos sueños en realidad</p>
                <p style='margin-top: 15px; font-size: 12px; opacity: 0.7;'>
                    © " . date('Y') . " Quantum Tour. Todos los derechos reservados.<br>
                    Este email contiene información confidencial de tu reserva.
                </p>
            </div>
        </div>
    </body>
    </html>";
}

function sanitizeFilename($filename) {
    return preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
}

function isEmailConfigured() {
    // Verificar si PHP mail está configurado
    return ini_get('SMTP') || ini_get('sendmail_path');
}

// Función para mostrar emails generados (para debugging)
function listarEmailsGenerados() {
    $email_dir = __DIR__ . '/../emails_simulados';
    if (!file_exists($email_dir)) {
        return [];
    }
    
    $files = glob($email_dir . '/*.html');
    $emails = [];
    
    foreach ($files as $file) {
        $emails[] = [
            'archivo' => basename($file),
            'ruta_completa' => $file,
            'fecha' => date('d/m/Y H:i:s', filemtime($file)),
            'tamaño' => filesize($file)
        ];
    }
    
    // Ordenar por fecha más reciente
    usort($emails, function($a, $b) {
        return filemtime($b['ruta_completa']) - filemtime($a['ruta_completa']);
    });
    
    return $emails;
}
?>

