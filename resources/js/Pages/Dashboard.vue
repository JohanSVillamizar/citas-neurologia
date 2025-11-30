<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    stats: Object,   // { pending, confirmed, completed, rejected }
    doctors: Array,
});
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

            <!-- ====== BOTÓN GESTIONAR CITAS ====== -->
            <div class="text-center mt-10">
                <Link
                    href="/appointments"
                    class="bg-indigo-600 text-white px-6 py-3 rounded-lg text-lg shadow hover:bg-indigo-700"
                >
                    Gestionar Citas
                </Link>
            </div>

            <!-- ====== GESTIONAR DOCTORES ====== -->
            <section class="bg-white p-8 rounded-lg shadow-md mt-10 border">
                <h3 class="text-2xl font-semibold mb-4">Gestión de Doctores</h3>

                <p class="text-gray-600 mb-6">
                    Administra médicos activos, edita su información o desactívalos del sistema.
                </p>

                <Link
                    href="/doctors"
                    class="bg-green-600 text-white px-6 py-3 rounded-lg shadow hover:bg-green-700"
                >
                    Ir al Módulo de Doctores
                </Link>
            </section>
        </div>
    </AppLayout>
</template>
