<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Cita Rechazada</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #ef4444;">Solicitud de Cita Rechazada</h2>
        
        <p>Hola {{ $appointment->patient_name }},</p>
        
        <p>Lamentamos informarte que tu solicitud de cita no pudo ser confirmada.</p>
        
        <div style="background-color: #fee2e2; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #ef4444;">
            <p><strong>Médico:</strong> {{ $appointment->doctor->name }}</p>
            <p><strong>Fecha solicitada:</strong> {{ $appointment->appointment_date->format('d/m/Y H:i') }}</p>
            <p><strong>Motivo:</strong> {{ $appointment->reason ?? 'No especificado' }}</p>
        </div>
        
        @if($appointment->admin_notes)
        <p><strong>Motivo del rechazo:</strong> {{ $appointment->admin_notes }}</p>
        @endif
        
        <p>Te invitamos a solicitar una nueva cita en otro horario disponible.</p>
        
        <p style="margin-top: 30px;">
            <strong>Clínica Neurología</strong><br>
            <small style="color: #6b7280;">Este es un correo automático, por favor no responder.</small>
        </p>
    </div>
</body>
</html>
