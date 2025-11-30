<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    doctors: Object
});

const showConfirm = ref(false);
const selectedDoctor = ref(null);

function openConfirm(doctor) {
    selectedDoctor.value = doctor;
    showConfirm.value = true;
}

function closeConfirm() {
    selectedDoctor.value = null;
    showConfirm.value = false;
}
</script>

<template>
    <AppLayout title="Doctores">
        <div class="py-10 max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="mb-6">
                <Link :href="route('dashboard')"
                    class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                ← Volver al panel principal
                </Link>
            </div>

            <h1 class="text-3xl font-bold mb-8">Gestión de Doctores</h1>

            <div class="bg-white p-6 rounded-xl shadow-lg border">

                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3">Foto</th>
                            <th class="px-4 py-3">Nombre</th>
                            <th class="px-4 py-3">Especialidad</th>
                            <th class="px-4 py-3">Estado</th>
                            <th class="px-4 py-3">Acciones</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200 bg-white">
                        <tr v-for="doctor in doctors.data" :key="doctor.id">

                            <td class="px-4 py-4">
                                <img v-if="doctor.photo" :src="doctor.photo"
                                    class="h-12 w-12 rounded-full object-cover border">
                                <div v-else class="h-12 w-12 bg-gray-200 rounded-full flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-500">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            fill="none" stroke="currentColor"
                                            d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.5 20.25a8.25 8.25 0 1115 0v.75H4.5v-.75z" />
                                    </svg>
                                </div>
                            </td>

                            <td class="px-4 py-4 font-medium text-gray-900">{{ doctor.name }}</td>
                            <td class="px-4 py-4 text-gray-700">{{ doctor.specialty }}</td>

                            <td class="px-4 py-4">
                                <span :class="doctor.is_active
                                    ? 'bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs'
                                    : 'bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs'">
                                    {{ doctor.is_active ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>

                            <td class="px-4 py-4 flex gap-3">
                                <Link :href="`/doctors/${doctor.slug}/edit`"
                                    class="px-3 py-1.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                Editar
                                </Link>

                                <Link :href="`/doctors/${doctor.slug}`"
                                    class="px-3 py-1.5 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                                Ver
                                </Link>

                                <!-- Aquí va el popup -->
                                <button @click="openConfirm(doctor)" class="px-3 py-1.5 rounded-lg text-white" :class="doctor.is_active
                                    ? 'bg-red-600 hover:bg-red-700'
                                    : 'bg-green-600 hover:bg-green-700'">
                                    {{ doctor.is_active ? 'Desactivar' : 'Activar' }}
                                </button>

                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Paginación -->
                <div class="mt-6 flex justify-between">
                    <Link v-if="doctors.prev_page_url" :href="doctors.prev_page_url"
                        class="px-4 py-2 bg-gray-200 rounded">
                    ← Anterior
                    </Link>

                    <Link v-if="doctors.next_page_url" :href="doctors.next_page_url"
                        class="px-4 py-2 bg-gray-200 rounded">
                    Siguiente →
                    </Link>
                </div>

            </div>
        </div>

        <!-- MODAL -->
        <div v-if="showConfirm" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50">
            <div class="bg-white p-6 rounded-xl shadow-xl w-full max-w-sm text-center space-y-6">

                <h2 class="text-xl font-bold">Confirmar acción</h2>

                <p class="text-gray-700">
                    ¿Seguro que deseas
                    <strong>{{ selectedDoctor.is_active ? 'desactivar' : 'activar' }}</strong>
                    al médico <strong>{{ selectedDoctor.name }}</strong>?
                </p>

                <div class="flex justify-center gap-4">
                    <button @click="closeConfirm" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">
                        Cancelar
                    </button>

                    <Link as="button" method="post" :href="`/doctors/${selectedDoctor.slug}/toggle`"
                        class="px-4 py-2 rounded text-white"
                        :class="selectedDoctor.is_active ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700'"
                        @click="closeConfirm">
                    Sí, continuar
                    </Link>
                </div>

            </div>
        </div>
    </AppLayout>
</template>
