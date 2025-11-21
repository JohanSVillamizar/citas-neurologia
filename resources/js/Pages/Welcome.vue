<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
  doctors: Array,
  canLogin: Boolean,
  canRegister: Boolean,
  laravelVersion: String,
  phpVersion: String,
});
</script>

<template>
  <Head title="Inicio - Citas Neurología" />

  <div class="min-h-screen bg-gray-50">
    <nav class="bg-blue-600 text-white p-4 flex justify-between items-center">
      <h1 class="text-2xl font-bold">Clínica de Neurología</h1>
      <div>
        <Link v-if="canLogin" href="/login" class="mr-4">Ingresar</Link>
        <Link v-if="canRegister" href="/register">Registrarse</Link>
      </div>
    </nav>

    <div class="container mx-auto px-4 py-8">
      <h2 class="text-3xl font-bold mb-6">Nuestros Especialistas</h2>

      <div class="grid md:grid-cols-3 gap-6">
        <div v-for="doctor in doctors" :key="doctor.id"
             class="bg-white rounded-lg shadow-md p-6">
          <h3 class="text-xl font-semibold mb-2">{{ doctor.name }}</h3>
          <p class="text-gray-600 mb-4">{{ doctor.specialty }}</p>
          <p class="text-sm text-gray-700 mb-4">{{ doctor.bio }}</p>
          <Link :href="route('doctors.show', doctor.slug)"
                class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            Ver perfil y agendar
          </Link>
        </div>
      </div>

      <footer class="mt-12 text-center text-gray-500">
        <p>Laravel v{{ laravelVersion }} - PHP v{{ phpVersion }}</p>
      </footer>
    </div>
  </div>
</template>
