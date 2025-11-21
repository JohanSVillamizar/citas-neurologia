<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Solicitud de Cita Recibida</title>
</head>
<body style="font-family: Arial, sans-serif; color: #333; background: #f7fafc;">
    <div style="max-width: 600px; margin: 0 auto; background: #fff; border-radius: 12px; box-shadow: 0 2px 8px #e5e7eb; padding: 24px;">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px;">
            <!-- Ícono reloj SVG -->
            <span style="display:inline-block;">
                <svg width="32" height="32" fill="none" style="vertical-align:middle">
                  <circle cx="16" cy="16" r="16" fill="#3b82f6"/>
                  <path d="M16 10v6h4" stroke="#fff" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </span>
            <h2 style="color: #3b82f6; font-size: 1.6em; margin: 0;">
                Solicitud de Cita Recibida
            </h2>
        </div>

        <p style="font-size:1.2em">Hola <strong>{{ $appointment->patient_name }}</strong> 👋</p>
        <p>¡Hemos recibido tu solicitud de cita! Estos son los datos de tu solicitud:</p>

        <div style="background: #eef2ff; padding: 18px; border-radius: 8px; margin: 20px 0; border-left: 3px solid #3b82f6;">
            <p><strong>Médico:</strong> {{ $appointment->doctor->name }}</p>
            <p><strong>Fecha:</strong> {{ $appointment->appointment_date->format('d/m/Y H:i') }}</p>
            <p><strong>Motivo:</strong> {{ $appointment->reason ?? 'No especificado' }}</p>
            <p><strong>Estado:</strong> <span style="color:#6366f1; font-weight: bold;">Pendiente de confirmación</span></p>
        </div>

        <p style="font-size:1em;">Pronto recibirás un correo confirmando tu cita. Si tienes dudas, contáctanos.</p>
        
        <p style="margin-top: 40px; color: #2563eb; font-weight: bold;">Clínica Neurología</p>
        <small style="color: #888;">Este es un correo automático, por favor no responder.</small>
    </div>
</body>
</html>