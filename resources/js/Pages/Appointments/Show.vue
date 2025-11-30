<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    appointment: Object,
});

const showModal = ref(false);
const modalAction = ref(null);

const statusColors = {
    pendiente: { bg: 'bg-yellow-100', text: 'text-yellow-800', label: 'Pendiente', icon: '⏳' },
    confirmada: { bg: 'bg-blue-100', text: 'text-blue-800', label: 'Confirmada', icon: '✓' },
    completada: { bg: 'bg-green-100', text: 'text-green-800', label: 'Completada', icon: '✓✓' },
    rechazada: { bg: 'bg-red-100', text: 'text-red-800', label: 'Rechazada', icon: '✗' },
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

const formatDateTime = (date) => {
    const d = new Date(date);
    return d.toLocaleDateString('es-ES') + ' ' + d.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' });
};

const openModal = (action) => {
    modalAction.value = action;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    modalAction.value = null;
};

const acceptAppointment = () => {
    router.post(
        route('appointments.accept', props.appointment.id),
        {},
        { onSuccess: closeModal }
    );
};

const rejectAppointment = () => {
    router.post(
        route('appointments.reject', props.appointment.id),
        {},
        { onSuccess: closeModal }
    );
};

const completeAppointment = () => {
    router.post(
        route('appointments.complete', props.appointment.id),
        {},
        { onSuccess: closeModal }
    );
};
</script>

<template>
    <AppLayout :title="`Cita de ${appointment.patient_name}`">
        <Head :title="`Cita - ${appointment.patient_name}`" />

        <div class="py-10 max-w-4xl mx-auto sm:px-6 lg:px-8">

            <!-- Volver -->
            <div class="mb-6">
                <Link :href="route('appointments.index')"
                    class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                    ← Volver a citas
                </Link>
            </div>

            <div class="bg-white rounded-xl shadow-lg border overflow-hidden">

                <!-- HEADER CON ESTADO -->
                <div class="bg-gradient-to-r from-blue-600 to-indigo-600 p-8 text-white">
                    <div class="flex items-start justify-between">
                        <div>
                            <h1 class="text-4xl font-bold mb-2">{{ appointment.patient_name }}</h1>
                            <p class="text-blue-100 text-lg">Cita con Dr. {{ appointment.doctor.name }}</p>
                        </div>
                        <span :class="[
                            'px-4 py-2 rounded-full font-bold text-lg',
                            statusColors[appointment.status].bg,
                            statusColors[appointment.status].text
                        ]">
                            {{ statusColors[appointment.status].icon }} {{ statusColors[appointment.status].label }}
                        </span>
                    </div>
                </div>

                <!-- CONTENIDO PRINCIPAL -->
                <div class="p-8 space-y-8">

                    <!-- SECCIÓN: DATOS DE LA CITA -->
                    <section>
                        <h2 class="text-2xl font-semibold mb-4 text-gray-800">Información de la Cita</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <p class="text-sm text-gray-600 font-semibold">Fecha</p>
                                <p class="text-lg font-bold text-gray-800 mt-1">
                                    {{ formatDate(appointment.appointment_date) }}
                                </p>
                            </div>
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <p class="text-sm text-gray-600 font-semibold">Hora</p>
                                <p class="text-lg font-bold text-gray-800 mt-1">
                                    {{ formatTime(appointment.appointment_date) }}
                                </p>
                            </div>
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <p class="text-sm text-gray-600 font-semibold">Duración</p>
                                <p class="text-lg font-bold text-gray-800 mt-1">
                                    {{ appointment.duration_minutes }} minutos
                                </p>
                            </div>
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <p class="text-sm text-gray-600 font-semibold">Motivo de Consulta</p>
                                <p class="text-lg font-bold text-gray-800 mt-1">
                                    {{ appointment.reason || '—' }}
                                </p>
                            </div>
                        </div>
                    </section>

                    <!-- SECCIÓN: DATOS DEL PACIENTE -->
                    <section>
                        <h2 class="text-2xl font-semibold mb-4 text-gray-800">Datos del Paciente</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <p class="text-sm text-gray-600 font-semibold">Nombre</p>
                                <p class="text-lg font-bold text-gray-800 mt-1">{{ appointment.patient_name }}</p>
                            </div>
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <p class="text-sm text-gray-600 font-semibold">Email</p>
                                <p class="text-lg font-bold text-gray-800 mt-1">{{ appointment.patient_email }}</p>
                            </div>
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <p class="text-sm text-gray-600 font-semibold">Teléfono</p>
                                <p class="text-lg font-bold text-gray-800 mt-1">
                                    {{ appointment.patient_phone || '—' }}
                                </p>
                            </div>
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <p class="text-sm text-gray-600 font-semibold">Cédula</p>
                                <p class="text-lg font-bold text-gray-800 mt-1">
                                    {{ appointment.patient_id_number || '—' }}
                                </p>
                            </div>
                            <div class="p-4 bg-gray-50 rounded-lg md:col-span-2">
                                <p class="text-sm text-gray-600 font-semibold">Fecha de Nacimiento</p>
                                <p class="text-lg font-bold text-gray-800 mt-1">
                                    {{ appointment.patient_birth_date || '—' }}
                                </p>
                            </div>
                        </div>
                    </section>

                    <!-- SECCIÓN: DATOS DEL DOCTOR -->
                    <section>
                        <h2 class="text-2xl font-semibold mb-4 text-gray-800">Doctor Asignado</h2>
                        <div class="bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-200 rounded-lg p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <p class="text-sm text-gray-600 font-semibold">Nombre</p>
                                    <p class="text-xl font-bold text-gray-800 mt-1">{{ appointment.doctor.name }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600 font-semibold">Especialidad</p>
                                    <p class="text-xl font-bold text-gray-800 mt-1">{{ appointment.doctor.specialty }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600 font-semibold">Email</p>
                                    <p class="text-lg text-gray-800 mt-1">{{ appointment.doctor.email || '—' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600 font-semibold">Teléfono</p>
                                    <p class="text-lg text-gray-800 mt-1">{{ appointment.doctor.phone || '—' }}</p>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- SECCIÓN: NOTAS DE ADMIN -->
                    <section v-if="appointment.admin_notes">
                        <h2 class="text-2xl font-semibold mb-4 text-gray-800">Notas del Administrador</h2>
                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded">
                            <p class="text-gray-800">{{ appointment.admin_notes }}</p>
                        </div>
                    </section>

                    <!-- SECCIÓN: REGISTRO DE TIMESTAMPS -->
                    <section>
                        <h2 class="text-2xl font-semibold mb-4 text-gray-800">Registro de Actividad</h2>
                        <div class="space-y-2 text-sm text-gray-600">
                            <p><strong>Creado:</strong> {{ formatDateTime(appointment.created_at) }}</p>
                            <p><strong>Última actualización:</strong> {{ formatDateTime(appointment.updated_at) }}</p>
                        </div>
                    </section>

                    <!-- BOTONES DE ACCIÓN -->
                    <section class="flex flex-wrap gap-4 pt-6 border-t">
                        <Link :href="route('appointments.index')"
                            class="px-6 py-3 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition">
                            Volver al listado
                        </Link>

                        <!-- Aceptar (pendiente) -->
                        <button v-if="appointment.status === 'pendiente'"
                            @click="openModal('accept')"
                            class="px-6 py-3 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition">
                            ✓ Aceptar cita
                        </button>

                        <!-- Rechazar (pendiente) -->
                        <button v-if="appointment.status === 'pendiente'"
                            @click="openModal('reject')"
                            class="px-6 py-3 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700 transition">
                            ✗ Rechazar cita
                        </button>

                        <!-- Completar (confirmada) -->
                        <button v-if="appointment.status === 'confirmada'"
                            @click="openModal('complete')"
                            class="px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition">
                            ✓✓ Completar cita
                        </button>
                    </section>

                </div>

            </div>

        </div>

        <!-- MODAL DE CONFIRMACIÓN -->
        <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-xl shadow-2xl p-6 max-w-md w-full">
                <h2 class="text-xl font-bold mb-2">
                    {{
                        modalAction === 'accept' ? 'Aceptar cita'
                        : modalAction === 'reject' ? 'Rechazar cita'
                        : 'Completar cita'
                    }}
                </h2>
                <p class="text-gray-600 mb-6">
                    {{
                        modalAction === 'accept' ? '¿Confirmar esta cita? Se enviará notificación al paciente.'
                        : modalAction === 'reject' ? '¿Rechazar esta cita? Se notificará al paciente.'
                        : '¿Marcar esta cita como completada?'
                    }}
                </p>

                <div class="flex gap-3">
                    <button @click="closeModal"
                        class="flex-1 px-4 py-2 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition">
                        Cancelar
                    </button>

                    <button v-if="modalAction === 'accept'" @click="acceptAppointment"
                        class="flex-1 px-4 py-2 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition">
                        Confirmar
                    </button>

                    <button v-else-if="modalAction === 'reject'" @click="rejectAppointment"
                        class="flex-1 px-4 py-2 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700 transition">
                        Rechazar
                    </button>

                    <button v-else-if="modalAction === 'complete'" @click="completeAppointment"
                        class="flex-1 px-4 py-2 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition">
                        Completar
                    </button>
                </div>
            </div>
        </div>

    </AppLayout>
</template>
