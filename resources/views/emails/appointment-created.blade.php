<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Solicitud de Cita Recibida</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #2563eb;">Solicitud de Cita Recibida</h2>
        
        <p>Hola {{ $appointment->patient_name }},</p>
        
        <p>Hemos recibido tu solicitud de cita con el siguiente detalle:</p>
        
        <div style="background-color: #f3f4f6; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <p><strong>Médico:</strong> {{ $appointment->doctor->name }}</p>
            <p><strong>Fecha:</strong> {{ $appointment->appointment_date->format('d/m/Y H:i') }}</p>
            <p><strong>Motivo:</strong> {{ $appointment->reason ?? 'No especificado' }}</p>
            <p><strong>Estado:</strong> Pendiente de confirmación</p>
        </div>
        
        <p>Tu cita está en estado <strong>pendiente</strong>. Te notificaremos por correo cuando sea confirmada o rechazada.</p>
        
        <p>Gracias por confiar en nosotros.</p>
        
        <p style="margin-top: 30px;">
            <strong>Clínica Neurología</strong><br>
            <small style="color: #6b7280;">Este es un correo automático, por favor no responder.</small>
        </p>
    </div>
</body>
</html>
