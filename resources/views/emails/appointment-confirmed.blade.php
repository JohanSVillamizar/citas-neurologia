<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Cita Confirmada</title>
</head>
<body style="font-family: Arial, sans-serif; color: #333; background: #f7fafc;">
    <div style="max-width: 600px; margin: 0 auto; background: #fff; border-radius: 12px; box-shadow: 0 2px 8px #e5e7eb; padding: 24px;">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px;">
            <!-- Ícono check SVG -->
            <span style="display:inline-block;">
                <svg width="32" height="32" fill="none" style="vertical-align:middle">
                  <circle cx="16" cy="16" r="16" fill="#10b981"/>
                  <path d="M8 16l6 6 10-10" stroke="#fff" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </span>
            <h2 style="color: #10b981; font-size: 1.6em; margin: 0;">
                ¡Cita Confirmada!
            </h2>
        </div>

        <p style="font-size:1.2em">Hola <strong>{{ $appointment->patient_name }}</strong> 😊</p>
        <p>Tu cita ha sido confirmada. Te esperamos en la fecha seleccionada.</p>

        <div style="background: #d1fae5; padding: 18px; border-radius: 8px; margin: 20px 0; border-left: 3px solid #10b981;">
            <p><strong>Médico:</strong> {{ $appointment->doctor->name }}</p>
            <p><strong>Fecha:</strong> {{ $appointment->appointment_date->format('d/m/Y') }}</p>
            <p><strong>Hora:</strong> {{ $appointment->appointment_date->format('H:i') }}</p>
            <p><strong>Duración:</strong> {{ $appointment->duration_minutes }} minutos</p>
            <p><strong>Motivo:</strong> {{ $appointment->reason ?? 'No especificado' }}</p>
        </div>

        <div style="margin-bottom: 20px;">
            <strong>Recomendaciones:</strong>
            <ul>
                <li>Llega 10 minutos antes</li>
                <li>Trae tu documento de identidad</li>
                <li>Si tienes exámenes previos, tráelos contigo</li>
            </ul>
        </div>

        <p style="margin-top: 40px; color: #10b981; font-weight: bold;">Clínica Neurología</p>
        <small style="color: #888;">Este es un correo automático, por favor no responder.</small>
    </div>
</body>
</html>
