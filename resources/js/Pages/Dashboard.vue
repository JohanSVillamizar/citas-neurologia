<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    pendingAppointments: Array,
    confirmedAppointments: Array,
    doctors: Array,
});

function formatDate(datetime) {
    return new Date(datetime).toLocaleString(undefined, {
        dateStyle: 'medium',
        timeStyle: 'short',
    });
}
</script>

<template>
    <AppLayout title="Dashboard">
        <Head title="Dashboard" />

        <div class="py-12 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-12">
            <h2 class="text-3xl font-bold text-center mb-6">Panel de Administración</h2>

            <section class="bg-yellow-50 p-6 rounded-lg shadow-md">
                <h3 class="text-2xl font-semibold mb-4 flex items-center gap-2">
                    Citas Pendientes
                    <span class="inline-block bg-yellow-400 text-yellow-900 px-3 py-1 rounded-full text-sm font-bold">
                      {{ pendingAppointments.length }}
                    </span>
                </h3>
                <div v-if="pendingAppointments.length === 0" class="text-gray-500 text-center">
                    No hay citas pendientes
                </div>
                <div v-else class="space-y-6 max-h-96 overflow-y-auto">
                    <div v-for="appointment in pendingAppointments" :key="appointment.id"
                         class="border-l-4 border-yellow-500 bg-yellow-100 p-4 rounded shadow">
                        <p><strong>Paciente:</strong> {{ appointment.patient_name }}</p>
                        <p><strong>Médico:</strong> {{ appointment.doctor.name }}</p>
                        <p><strong>Fecha:</strong> {{ formatDate(appointment.appointment_date) }}</p>
                        <p><strong>Motivo:</strong> {{ appointment.reason }}</p>
                    </div>
                </div>
            </section>

            <section class="bg-green-50 p-6 rounded-lg shadow-md">
                <h3 class="text-2xl font-semibold mb-4 flex items-center gap-2">
                    Próximas Citas Confirmadas
                    <span class="inline-block bg-green-400 text-green-900 px-3 py-1 rounded-full text-sm font-bold">
                      {{ confirmedAppointments.length }}
                    </span>
                </h3>
                <div v-if="confirmedAppointments.length === 0" class="text-gray-500 text-center">
                    No hay citas confirmadas próximas
                </div>
                <div v-else class="space-y-6 max-h-96 overflow-y-auto">
                    <div v-for="appointment in confirmedAppointments" :key="appointment.id"
                         class="border-l-4 border-green-500 bg-green-100 p-4 rounded shadow">
                        <p><strong>Paciente:</strong> {{ appointment.patient_name }}</p>
                        <p><strong>Médico:</strong> {{ appointment.doctor.name }}</p>
                        <p><strong>Fecha:</strong> {{ formatDate(appointment.appointment_date) }}</p>
                    </div>
                </div>
            </section>
        </div>
    </AppLayout>
</template>