<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
  doctor: Object,
});

const daysOfWeek = {
  0: 'Domingo',
  1: 'Lunes',
  2: 'Martes',
  3: 'Miércoles',
  4: 'Jueves',
  5: 'Viernes',
  6: 'Sábado',
};

// Horarios ordenados por día de la semana
const schedulesByDay = computed(() => {
  const sorted = [...(props.doctor.schedules || [])].sort(
    (a, b) => a.day_of_week - b.day_of_week
  );
  return sorted;
});

// Cuenta horarios activos e inactivos
const statsSchedules = computed(() => ({
  total: props.doctor.schedules?.length || 0,
  active: props.doctor.schedules?.filter(s => s.is_active).length || 0,
  inactive: props.doctor.schedules?.filter(s => !s.is_active).length || 0,
}));
</script>

<template>
  <AppLayout :title="doctor.name">
    <Head :title="`Ver ${doctor.name}`" />

    <div class="py-10 max-w-5xl mx-auto sm:px-6 lg:px-8">

      <!-- Volver -->
      <div class="mb-6">
        <Link
          :href="route('doctors.index')"
          class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200"
        >
          ← Volver al listado
        </Link>
      </div>

      <div class="bg-white rounded-xl shadow-lg border overflow-hidden">

        <!-- HEADER CON FOTO Y DATOS PRINCIPALES -->
        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 p-8 text-white">
          <div class="flex items-center gap-6">
            <div>
              <img
                v-if="doctor.photo"
                :src="doctor.photo"
                :alt="doctor.name"
                class="w-32 h-32 object-cover rounded-full border-4 border-white shadow-lg"
              />
              <div
                v-else
                class="w-32 h-32 bg-white bg-opacity-20 rounded-full flex items-center justify-center text-5xl font-bold"
              >
                {{ doctor.name.charAt(0) }}
              </div>
            </div>
            <div class="flex-1">
              <h1 class="text-4xl font-bold mb-2">{{ doctor.name }}</h1>
              <p class="text-xl text-blue-100 mb-3">{{ doctor.specialty }}</p>
              <div class="flex gap-4 text-sm">
                <span
                  :class="[
                    'px-3 py-1 rounded-full',
                    doctor.is_active
                      ? 'bg-green-500 text-white'
                      : 'bg-red-500 text-white'
                  ]"
                >
                  {{ doctor.is_active ? '✓ Activo' : '✗ Inactivo' }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- CONTENIDO PRINCIPAL -->
        <div class="p-8 space-y-8">

          <!-- Información de contacto -->
          <section>
            <h2 class="text-2xl font-semibold mb-4 text-gray-800">Información de Contacto</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="p-4 bg-gray-50 rounded-lg">
                <p class="text-sm text-gray-600 font-semibold">Correo electrónico</p>
                <p class="text-lg">{{ doctor.email || '—' }}</p>
              </div>
              <div class="p-4 bg-gray-50 rounded-lg">
                <p class="text-sm text-gray-600 font-semibold">Teléfono</p>
                <p class="text-lg">{{ doctor.phone || '—' }}</p>
              </div>
              <div class="p-4 bg-gray-50 rounded-lg">
                <p class="text-sm text-gray-600 font-semibold">Número de licencia</p>
                <p class="text-lg">{{ doctor.license_number || '—' }}</p>
              </div>
              <div class="p-4 bg-gray-50 rounded-lg">
                <p class="text-sm text-gray-600 font-semibold">Slug</p>
                <p class="text-lg font-mono text-blue-600">{{ doctor.slug }}</p>
              </div>
            </div>
          </section>

          <!-- Biografía -->
          <section v-if="doctor.bio">
            <h2 class="text-2xl font-semibold mb-4 text-gray-800">Biografía</h2>
            <p class="text-gray-700 leading-relaxed">{{ doctor.bio }}</p>
          </section>

          <!-- Horarios -->
          <section>
            <div class="flex items-center justify-between mb-4">
              <h2 class="text-2xl font-semibold text-gray-800">Horario de Atención</h2>
              <div class="flex gap-2 text-sm">
                <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full">
                  {{ statsSchedules.active }} activos
                </span>
                <span class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full">
                  {{ statsSchedules.inactive }} inactivos
                </span>
              </div>
            </div>

            <div class="overflow-x-auto">
              <table class="min-w-full border border-gray-200 rounded-lg">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Día</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Hora inicio</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Hora fin</th>
                    <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700">Estado</th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="schedule in schedulesByDay"
                    :key="schedule.id"
                    class="border-t hover:bg-gray-50 transition"
                  >
                    <td class="px-4 py-3 text-sm font-medium">
                      {{ daysOfWeek[schedule.day_of_week] }}
                    </td>
                    <td class="px-4 py-3 text-sm">
                      {{ schedule.start_time?.slice(0, 5) || '—' }}
                    </td>
                    <td class="px-4 py-3 text-sm">
                      {{ schedule.end_time?.slice(0, 5) || '—' }}
                    </td>
                    <td class="px-4 py-3 text-center">
                      <span
                        :class="[
                          'px-3 py-1 rounded-full text-xs font-semibold',
                          schedule.is_active
                            ? 'bg-green-100 text-green-700'
                            : 'bg-gray-100 text-gray-700'
                        ]"
                      >
                        {{ schedule.is_active ? '✓ Activo' : '✗ Inactivo' }}
                      </span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </section>

          <!-- Acciones -->
          <section class="flex gap-4 pt-4">
            <Link
              :href="route('doctors.edit', doctor.id)"
              class="px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition"
            >
              Editar Doctor
            </Link>

            <Link
              :href="route('doctors.index')"
              class="px-6 py-3 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition"
            >
              Volver
            </Link>
          </section>

        </div>

      </div>

    </div>
  </AppLayout>
</template>
