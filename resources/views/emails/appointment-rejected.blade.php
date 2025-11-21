<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Solicitud de Cita Rechazada</title>
</head>
<body style="font-family: Arial, sans-serif; color: #333; background: #f7fafc;">
    <div style="max-width: 600px; margin: 0 auto; background: #fff; border-radius: 12px; box-shadow: 0 2px 8px #e5e7eb; padding: 24px;">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px;">
            <!-- Ícono X SVG -->
            <span style="display:inline-block;">
                <svg width="32" height="32" fill="none" style="vertical-align:middle">
                  <circle cx="16" cy="16" r="16" fill="#ef4444"/>
                  <path d="M10 10l12 12M22 10l-12 12" stroke="#fff" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </span>
            <h2 style="color: #ef4444; font-size: 1.6em; margin: 0;">
                Solicitud de Cita Rechazada
            </h2>
        </div>

        <p style="font-size:1.2em">Hola <strong>{{ $appointment->patient_name }}</strong> 😔</p>
        <p>Lamentamos informarte que tu solicitud de cita no pudo ser confirmada.</p>

        <div style="background: #fee2e2; padding: 18px; border-radius: 8px; margin: 20px 0; border-left: 3px solid #ee4444;">
            <p><strong>Médico:</strong> {{ $appointment->doctor->name }}</p>
            <p><strong>Fecha solicitada:</strong> {{ $appointment->appointment_date->format('d/m/Y H:i') }}</p>
            <p><strong>Motivo:</strong> {{ $appointment->reason ?? 'No especificado' }}</p>
            @if($appointment->admin_notes)
                <p><strong>Motivo del rechazo:</strong> {{ $appointment->admin_notes }}</p>
            @endif
        </div>

        <p>No te desanimes, puedes <a href="{{ route('appointments.new') }}" style="color:#ef4444; text-decoration:underline;">solicitar otra cita aquí</a> en el horario que te convenga.</p>
        
        <p style="margin-top: 40px; color: #ef4444; font-weight: bold;">Clínica Neurología</p>
        <small style="color: #888;">Este es un correo automático, por favor no responder.</small>
    </div>
</body>
</html>
