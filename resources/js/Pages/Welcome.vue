<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
  doctors: Array,
  canLogin: Boolean,
  canRegister: Boolean,
  laravelVersion: String,
  phpVersion: String,
});

// Datos para el carrusel
const carouselItems = [
  {
    image: 'https://www.vivomisalud.com/content/images/2025/02/doctor-arranging-scanning-device-head-female-patient-1.jpg', 
    title: 'Neurología avanzada',
    description: 'Diagnóstico y tratamientos innovadores para trastornos neurológicos.',
  },
  {
    image: 'https://centromedicoabc.com/wp-content/uploads/2024/07/Que-es-la-neurologia-.webp',
    title: 'Especialistas dedicados',
    description: 'Profesionales con amplia experiencia en neurología clínica.',
  },
  {
    image: 'https://avantiasalud.com/wp-content/uploads/2023/10/reso-tac.jpg',
    title: 'Tecnología de punta',
    description: 'Equipos modernos para un diagnóstico preciso y efectivo.',
  },
];

const currentIndex = ref(0);
const nextSlide = () => {
  currentIndex.value = (currentIndex.value + 1) % carouselItems.length;
};
const prevSlide = () => {
  currentIndex.value = (currentIndex.value + carouselItems.length - 1) % carouselItems.length;
};
</script>

<template>
  <Head title="Inicio - Citas Neurología" />

  <div class="min-h-screen bg-gray-50">
    <nav class="bg-blue-700 text-white p-4 flex justify-between items-center shadow-lg">
      <h1 class="text-3xl font-extrabold tracking-wide">Clínica de Neurología</h1>
      <div class="space-x-4">
        <Link v-if="canLogin" href="/login" class="hover:underline">Ingresar</Link>
        <Link v-if="canRegister" href="/register" class="hover:underline">Registrarse</Link>
      </div>
    </nav>

    <!-- Carrusel -->
    <section class="relative max-w-6xl mx-auto mt-8 rounded-lg overflow-hidden shadow-lg">
      <div
        class="aspect-[16/6] bg-cover bg-center transition-all duration-700"
        :style="{ backgroundImage: `url(${carouselItems[currentIndex].image})` }"
      ></div>
      <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/75 to-transparent p-6 text-white">
        <h2 class="text-2xl font-bold">{{ carouselItems[currentIndex].title }}</h2>
        <p class="mt-2">{{ carouselItems[currentIndex].description }}</p>
      </div>
      <button @click="prevSlide" class="absolute top-1/2 left-3 -translate-y-1/2 text-white text-4xl opacity-70 hover:opacity-100">‹</button>
      <button @click="nextSlide" class="absolute top-1/2 right-3 -translate-y-1/2 text-white text-4xl opacity-70 hover:opacity-100">›</button>
    </section>

    <main class="container mx-auto px-4 py-12">
      <h2 class="text-4xl font-bold mb-8 text-center text-gray-800">Nuestros Especialistas</h2>

      <div class="grid md:grid-cols-3 gap-8">
        <div v-for="doctor in doctors" :key="doctor.id" class="bg-white rounded-lg shadow-lg p-6 hover:shadow-xl transition-shadow">
          <h3 class="text-xl font-semibold mb-2 text-blue-800">{{ doctor.name }}</h3>
          <p class="text-gray-600 mb-4 italic">{{ doctor.specialty }}</p>
          <p class="text-sm text-gray-700 mb-4">{{ doctor.bio }}</p>
          <Link :href="route('public.doctors.show', doctor.slug)"
                class="block text-center bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition-colors">
            Ver perfil y agendar
          </Link>
        </div>
      </div>

      <footer class="mt-12 text-center text-gray-500">
        <p>Laravel v{{ laravelVersion }} - PHP v{{ phpVersion }}</p>
      </footer>
    </main>
  </div>
</template>
