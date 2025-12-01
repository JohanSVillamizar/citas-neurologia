<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    doctor: Object,
});

const daysOfWeek = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];

// Agrupa las franjas horarias por día - SOLO las activas
const availableByDay = computed(() => {
    const grouped = {};
    for (const schedule of (props.doctor.schedules || [])) {
        if (!schedule.is_active) continue;
        
        if (!grouped[schedule.day_of_week]) {
            grouped[schedule.day_of_week] = [];
        }
        grouped[schedule.day_of_week].push({
            start_time: schedule.start_time,
            end_time: schedule.end_time,
            day_of_week: schedule.day_of_week
        });
    }
    return grouped;
});

// Obtener el próximo horario disponible
const nextAvailableSlot = computed(() => {
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    for (let i = 0; i < 30; i++) {
        const checkDate = new Date(today);
        checkDate.setDate(checkDate.getDate() + i);
        const dayOfWeek = checkDate.getDay();
        
        if (availableByDay.value[dayOfWeek] && availableByDay.value[dayOfWeek].length > 0) {
            const schedule = availableByDay.value[dayOfWeek][0];
            const year = checkDate.getFullYear();
            const month = String(checkDate.getMonth() + 1).padStart(2, '0');
            const day = String(checkDate.getDate()).padStart(2, '0');
            const dateStr = `${year}-${month}-${day}`;
            
            return `${dateStr}T${schedule.start_time.substring(0, 5)}`;
        }
    }
    
    return null;
});

// Generar URL con start parameter
const appointmentUrl = computed(() => {
    if (!nextAvailableSlot.value) {
        return route('appointments.new', { doctor: props.doctor.slug });
    }
    return `/appointments/new?doctor=${props.doctor.slug}&start=${nextAvailableSlot.value}`;
});
</script>

<template>
  <AppLayout :title="props.doctor.name">
    <Head :title="props.doctor.name" />

    <div class="container mx-auto py-8 px-4 max-w-3xl">
      <div class="flex justify-center mb-4">
        <img
          v-if="props.doctor.photo"
          :src="props.doctor.photo"
          alt="Foto de {{ props.doctor.name }}"
          class="w-32 h-32 object-cover rounded-full border-4 border-blue-200 shadow"
        />
        <div v-else
          class="w-32 h-32 rounded-full bg-gray-200 flex items-center justify-center text-4xl text-gray-400 border-4 border-blue-200 shadow"
        >
          <span>{{ props.doctor.name.charAt(0) }}</span>
        </div>
      </div>
      <h1 class="text-4xl font-bold mb-4 text-center">{{ props.doctor.name }}</h1>
      <p class="text-lg italic mb-4 text-center">{{ props.doctor.specialty }}</p>
      <p class="mb-6 text-center">{{ props.doctor.bio }}</p>

      <h2 class="text-2xl font-semibold mb-4">Disponibilidad Semanal</h2>
      <ul class="list-disc list-inside mb-8 space-y-2">
        <li v-for="day in Object.keys(availableByDay)" :key="day">
          <strong>{{ daysOfWeek[day] }}: </strong>
          <span v-for="(schedule, idx) in availableByDay[day]" :key="schedule.start_time">
            {{ schedule.start_time.slice(0, 5) }} - {{ schedule.end_time.slice(0, 5) }}<span v-if="idx < availableByDay[day].length-1"> y </span>
          </span>
        </li>
      </ul>

      <!-- ✅ Usa appointmentUrl en lugar de route() -->
      <Link :href="appointmentUrl"
        class="inline-block bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition">
        Agendar Cita
      </Link>
    </div>
  </AppLayout>
</template>
