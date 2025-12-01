<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    stats: Object,
    doctors: Array,
    pendingAppointments: Array,
    confirmedAppointments: Array,
});

const selectedDoctorFilter = ref(null);

const filteredPending = computed(() => {
    if (!selectedDoctorFilter.value) return props.pendingAppointments;
    return props.pendingAppointments.filter(apt => apt.doctor_id === selectedDoctorFilter.value);
});

const filteredConfirmed = computed(() => {
    if (!selectedDoctorFilter.value) return props.confirmedAppointments;
    return props.confirmedAppointments.filter(apt => apt.doctor_id === selectedDoctorFilter.value);
});

const formatDate = (dateString) => {
    const date = new Date(dateString);
    const year = date.getFullYear();
    const month = date.getMonth();
    const day = date.getDate();
    
    // Usa las partes del string directamente
    const parts = dateString.split('T')[0].split('-');
    const localDate = new Date(parts[0], parts[1] - 1, parts[2]);
    
    return localDate.toLocaleDateString('es-ES', {
        weekday: 'short',
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const formatTime = (dateString) => {
    const timePart = dateString.split('T')[1];
    if (!timePart) return '';
    return timePart.substring(0, 5);
};
</script>

<template>
    <AppLayout title="Dashboard">

        <Head title="Dashboard" />

        <div class="py-10 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-12">

            <h2 class="text-3xl font-bold text-center mb-10">
                Panel de Administración
            </h2>

            <!-- ====== INSIGHTS ====== -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="p-6 bg-yellow-100 border-l-4 border-yellow-500 rounded-lg shadow">
                    <p class="text-xl font-bold">Pendientes</p>
                    <p class="text-4xl font-black mt-2">{{ stats.pending }}</p>
                </div>

                <div class="p-6 bg-green-100 border-l-4 border-green-500 rounded-lg shadow">
                    <p class="text-xl font-bold">Confirmadas</p>
                    <p class="text-4xl font-black mt-2">{{ stats.confirmed }}</p>
                </div>

                <div class="p-6 bg-blue-100 border-l-4 border-blue-500 rounded-lg shadow">
                    <p class="text-xl font-bold">Completadas</p>
                    <p class="text-4xl font-black mt-2">{{ stats.completed }}</p>
                </div>

                <div class="p-6 bg-red-100 border-l-4 border-red-500 rounded-lg shadow">
                    <p class="text-xl font-bold">Rechazadas</p>
                    <p class="text-4xl font-black mt-2">{{ stats.rejected }}</p>
                </div>
            </div>

            <!-- ====== FILTRO POR MÉDICO ====== -->
            <div class="bg-white p-6 rounded-lg shadow-md border">
                <h3 class="text-lg font-semibold mb-4">Filtrar por Médico</h3>
                <select v-model="selectedDoctorFilter"
                    class="w-full md:w-64 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option :value="null">Todos los Médicos</option>
                    <option v-for="doctor in doctors" :key="doctor.id" :value="doctor.id">
                        Dr. {{ doctor.name }}
                    </option>
                </select>
            </div>


            <!-- ====== CITAS PENDIENTES ====== -->
            <section class="bg-white p-8 rounded-lg shadow-md border">
                <h3 class="text-2xl font-semibold mb-6 text-yellow-700">Citas Pendientes de Confirmación</h3>

                <div v-if="filteredPending.length > 0" class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Paciente</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Médico</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Fecha</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Hora</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="appointment in filteredPending" :key="appointment.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm font-semibold text-gray-800">
                                    {{ appointment.patient_name }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">
                                    Dr. {{ appointment.doctor.name }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">
                                    {{ formatDate(appointment.appointment_date) }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">
                                    {{ formatTime(appointment.appointment_date) }}
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <Link :href="route('appointments.show', appointment.slug)"
                                        class="px-3 py-1 bg-blue-600 text-white text-xs font-semibold rounded hover:bg-blue-700 transition">
                                    Ver
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-else class="p-6 text-center text-gray-500">
                    <p>No hay citas pendientes</p>
                </div>
            </section>

            <!-- ====== CITAS CONFIRMADAS ====== -->
            <section class="bg-white p-8 rounded-lg shadow-md border">
                <h3 class="text-2xl font-semibold mb-6 text-green-700">Próximas Citas Confirmadas</h3>

                <div v-if="filteredConfirmed.length > 0" class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Paciente</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Médico</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Fecha</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Hora</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="appointment in filteredConfirmed" :key="appointment.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm font-semibold text-gray-800">
                                    {{ appointment.patient_name }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">
                                    Dr. {{ appointment.doctor.name }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">
                                    {{ formatDate(appointment.appointment_date) }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">
                                    {{ formatTime(appointment.appointment_date) }}
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <Link :href="route('appointments.show', appointment.slug)"
                                        class="px-3 py-1 bg-green-600 text-white text-xs font-semibold rounded hover:bg-green-700 transition">
                                    Ver
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-else class="p-6 text-center text-gray-500">
                    <p>No hay citas confirmadas próximas</p>
                </div>
            </section>

            <!-- ====== BOTÓN GESTIONAR CITAS ====== -->
            <div class="text-center mt-10">
                <Link href="/appointments"
                    class="bg-indigo-600 text-white px-6 py-3 rounded-lg text-lg shadow hover:bg-indigo-700">
                Gestionar Todas las Citas
                </Link>
            </div>

            <!-- ====== GESTIONAR DOCTORES ====== -->
            <section class="bg-white p-8 rounded-lg shadow-md mt-10 border">
                <h3 class="text-2xl font-semibold mb-4">Gestión de Doctores</h3>

                <p class="text-gray-600 mb-6">
                    Administra médicos activos, edita su información o desactívalos del sistema.
                </p>

                <Link href="/doctors" class="bg-green-600 text-white px-6 py-3 rounded-lg shadow hover:bg-green-700">
                Ir al Módulo de Doctores
                </Link>
            </section>
        </div>
    </AppLayout>
</template>
