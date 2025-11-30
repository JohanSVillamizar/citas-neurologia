<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    appointments: Object,
});

const statusColors = {
    pendiente: { bg: 'bg-yellow-100', text: 'text-yellow-800', label: 'Pendiente' },
    confirmada: { bg: 'bg-blue-100', text: 'text-blue-800', label: 'Confirmada' },
    completada: { bg: 'bg-green-100', text: 'text-green-800', label: 'Completada' },
    rechazada: { bg: 'bg-red-100', text: 'text-red-800', label: 'Rechazada' },
};

const selectedAppointment = ref(null);
const showModal = ref(false);

const openModal = (appointment, action) => {
    selectedAppointment.value = { ...appointment, action };
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    selectedAppointment.value = null;
};

const acceptAppointment = () => {
    router.post(
        route('appointments.accept', selectedAppointment.value.id),
        {},
        { onSuccess: closeModal }
    );
};

const rejectAppointment = () => {
    router.post(
        route('appointments.reject', selectedAppointment.value.id),
        {},
        { onSuccess: closeModal }
    );
};

const completeAppointment = () => {
    router.post(
        route('appointments.complete', selectedAppointment.value.id),
        {},
        { onSuccess: closeModal }
    );
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('es-ES', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

const formatTime = (date) => {
    return new Date(date).toLocaleTimeString('es-ES', {
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>

<template>
    <AppLayout title="Gestión de Citas">
        <Head title="Gestión de Citas" />

        <div class="py-10 max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Volver -->
            <div class="mb-6">
                <Link :href="route('dashboard')"
                    class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                    ← Volver al panel principal
                </Link>
            </div>

            <h1 class="text-3xl font-bold mb-8">Gestión de Citas</h1>

            <!-- FILTROS -->
            <div class="bg-white rounded-xl shadow-lg border p-6 mb-6">
                <h2 class="text-lg font-semibold mb-4">Filtrar por estado</h2>
                <div class="flex flex-wrap gap-2">
                    <Link href="/appointments"
                        class="px-4 py-2 rounded-lg font-semibold transition bg-gray-100 text-gray-700 hover:bg-gray-200">
                        Todas
                    </Link>
                    <Link href="/appointments?status=pendiente"
                        class="px-4 py-2 rounded-lg font-semibold transition bg-yellow-100 text-yellow-700 hover:bg-yellow-200">
                        Pendientes
                    </Link>
                    <Link href="/appointments?status=confirmada"
                        class="px-4 py-2 rounded-lg font-semibold transition bg-blue-100 text-blue-700 hover:bg-blue-200">
                        Confirmadas
                    </Link>
                    <Link href="/appointments?status=completada"
                        class="px-4 py-2 rounded-lg font-semibold transition bg-green-100 text-green-700 hover:bg-green-200">
                        Completadas
                    </Link>
                    <Link href="/appointments?status=rechazada"
                        class="px-4 py-2 rounded-lg font-semibold transition bg-red-100 text-red-700 hover:bg-red-200">
                        Rechazadas
                    </Link>
                </div>
            </div>

            <!-- TABLA DE CITAS -->
            <div class="bg-white rounded-xl shadow-lg border overflow-hidden">
                <div v-if="props.appointments.data && props.appointments.data.length > 0" class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Paciente</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Doctor</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Fecha</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Hora</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Estado</th>
                                <th class="px-6 py-3 text-center text-sm font-semibold text-gray-700">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="appointment in props.appointments.data" :key="appointment.id"
                                class="hover:bg-gray-50 transition">
                                <!-- Paciente -->
                                <td class="px-6 py-4 text-sm font-semibold text-gray-800">
                                    {{ appointment.patient_name }}
                                </td>

                                <!-- Doctor -->
                                <td class="px-6 py-4 text-sm font-semibold text-gray-800">
                                    {{ appointment.doctor.name }}
                                </td>

                                <!-- Fecha -->
                                <td class="px-6 py-4 text-sm text-gray-700">
                                    {{ formatDate(appointment.appointment_date) }}
                                </td>

                                <!-- Hora -->
                                <td class="px-6 py-4 text-sm text-gray-700">
                                    {{ formatTime(appointment.appointment_date) }}
                                </td>

                                <!-- Estado -->
                                <td class="px-6 py-4 text-sm">
                                    <span :class="[
                                        'px-3 py-1 rounded-full text-xs font-semibold',
                                        statusColors[appointment.status].bg,
                                        statusColors[appointment.status].text
                                    ]">
                                        {{ statusColors[appointment.status].label }}
                                    </span>
                                </td>

                                <!-- Acciones -->
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <!-- Aceptar -->
                                        <button v-if="appointment.status === 'pendiente'"
                                            @click="openModal(appointment, 'accept')"
                                            class="px-3 py-1 bg-green-600 text-white text-xs font-semibold rounded hover:bg-green-700 transition">
                                            Confirmar
                                        </button>

                                        <!-- Rechazar -->
                                        <button v-if="appointment.status === 'pendiente'"
                                            @click="openModal(appointment, 'reject')"
                                            class="px-3 py-1 bg-red-600 text-white text-xs font-semibold rounded hover:bg-red-700 transition">
                                            Rechazar
                                        </button>

                                        <!-- Completar -->
                                        <button v-if="appointment.status === 'confirmada'"
                                            @click="openModal(appointment, 'complete')"
                                            class="px-3 py-1 bg-blue-600 text-white text-xs font-semibold rounded hover:bg-blue-700 transition">
                                            Completar
                                        </button>

                                        <!-- Ver detalles -->
                                        <Link :href="route('appointments.show', appointment.id)"
                                            class="px-3 py-1 bg-gray-600 text-white text-xs font-semibold rounded hover:bg-gray-700 transition">
                                            Ver
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Sin resultados -->
                <div v-else class="p-8 text-center">
                    <p class="text-gray-500 text-lg">No hay citas para mostrar en este filtro.</p>
                </div>
            </div>

            <!-- Paginación -->
            <div v-if="props.appointments.links && props.appointments.links.length > 3"
                class="mt-6 flex justify-center gap-2 flex-wrap">
                <template v-for="link in props.appointments.links" :key="link.label">
                    <Link v-if="link.url" :href="link.url" :class="[
                        'px-4 py-2 rounded-lg font-semibold transition',
                        link.active
                            ? 'bg-blue-600 text-white'
                            : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                    ]" v-html="link.label" />

                    <span v-else :class="[
                        'px-4 py-2 rounded-lg font-semibold',
                        link.active
                            ? 'bg-blue-600 text-white'
                            : 'bg-gray-50 text-gray-400 cursor-not-allowed'
                    ]" v-html="link.label" />
                </template>
            </div>

        </div>

        <!-- MODAL -->
        <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-xl shadow-2xl p-6 max-w-md w-full">
                <h2 class="text-xl font-bold mb-2">
                    {{
                        selectedAppointment.action === 'accept' ? 'Aceptar cita'
                        : selectedAppointment.action === 'reject' ? 'Rechazar cita'
                        : 'Completar cita'
                    }}
                </h2>
                <p class="text-gray-600 mb-6">
                    {{
                        selectedAppointment.action === 'accept' ? '¿Confirmar esta cita? Se enviará notificación al paciente.'
                        : selectedAppointment.action === 'reject' ? '¿Rechazar esta cita? Se notificará al paciente.'
                        : '¿Marcar esta cita como completada?'
                    }}
                </p>

                <div class="bg-gray-50 rounded p-4 mb-6 text-sm space-y-2">
                    <p><strong>Paciente:</strong> {{ selectedAppointment.patient_name }}</p>
                    <p><strong>Doctor:</strong> {{ selectedAppointment.doctor?.name }}</p>
                    <p><strong>Fecha:</strong> {{ formatDate(selectedAppointment.appointment_date) }}</p>
                    <p><strong>Hora:</strong> {{ formatTime(selectedAppointment.appointment_date) }}</p>
                </div>

                <div class="flex gap-3">
                    <button @click="closeModal"
                        class="flex-1 px-4 py-2 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition">
                        Cancelar
                    </button>

                    <button v-if="selectedAppointment.action === 'accept'" @click="acceptAppointment"
                        class="flex-1 px-4 py-2 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition">
                        Confirmar
                    </button>

                    <button v-else-if="selectedAppointment.action === 'reject'" @click="rejectAppointment"
                        class="flex-1 px-4 py-2 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700 transition">
                        Rechazar
                    </button>

                    <button v-else-if="selectedAppointment.action === 'complete'" @click="completeAppointment"
                        class="flex-1 px-4 py-2 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition">
                        Completar
                    </button>
                </div>
            </div>
        </div>

    </AppLayout>
</template>
