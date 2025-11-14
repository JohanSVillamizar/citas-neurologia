<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Cita Confirmada</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #10b981;">¡Cita Confirmada!</h2>
        
        <p>Hola {{ $appointment->patient_name }},</p>
        
        <p>Nos complace informarte que tu cita ha sido <strong>confirmada</strong>.</p>
        
        <div style="background-color: #d1fae5; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #10b981;">
            <p><strong>Médico:</strong> {{ $appointment->doctor->name }}</p>
            <p><strong>Fecha:</strong> {{ $appointment->appointment_date->format('d/m/Y') }}</p>
            <p><strong>Hora:</strong> {{ $appointment->appointment_date->format('H:i') }}</p>
            <p><strong>Duración:</strong> {{ $appointment->duration_minutes }} minutos</p>
            <p><strong>Motivo:</strong> {{ $appointment->reason ?? 'No especificado' }}</p>
        </div>
        
        <p><strong>Recomendaciones:</strong></p>
        <ul>
            <li>Por favor llega 10 minutos antes de tu cita</li>
            <li>Trae tu documento de identidad</li>
            <li>Si tienes exámenes previos, tráelos contigo</li>
        </ul>
        
        @if($appointment->admin_notes)
        <p><strong>Notas del administrador:</strong> {{ $appointment->admin_notes }}</p>
        @endif
        
        <p style="margin-top: 30px;">
            <strong>Clínica Neurología</strong><br>
            <small style="color: #6b7280;">Este es un correo automático, por favor no responder.</small>
        </p>
    </div>
</body>
</html>
