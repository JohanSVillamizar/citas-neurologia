<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
  doctor: Object,
});

// Carga los datos del doctor y copia los horarios para edición reactiva
const form = useForm({
  name: props.doctor.name || '',
  specialty: props.doctor.specialty || '',
  email: props.doctor.email || '',
  phone: props.doctor.phone || '',
  schedules: (props.doctor.schedules || []).map(s => ({
    id: s.id,
    day_of_week: s.day_of_week,
    start_time: s.start_time ? s.start_time.slice(0,5) : '',
    end_time: s.end_time ? s.end_time.slice(0,5) : '',
    is_active: !!s.is_active,
  })),
});

const dayNames = {
  0: 'Domingo',
  1: 'Lunes',
  2: 'Martes',
  3: 'Miércoles',
  4: 'Jueves',
  5: 'Viernes',
  6: 'Sábado',
};

// Ordena los horarios de lunes a domingo
const orderedSchedules = computed(() =>
  [...form.schedules].sort((a, b) => a.day_of_week - b.day_of_week)
);

const submit = () => {
  form.put(route('doctors.update', props.doctor.id));
};
</script>

<template>
  <AppLayout title="Editar Doctor">
    <Head :title="`Editar ${doctor.name}`" />

    <div class="py-10 max-w-5xl mx-auto sm:px-6 lg:px-8">

      <!-- Volver -->
      <div class="mb-6">
        <Link
          :href="route('doctors.index')"
          class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200"
        >
          ← Volver al listado de doctores
        </Link>
      </div>

      <h1 class="text-3xl font-bold mb-6">Editar Doctor</h1>

      <div class="bg-white rounded-xl shadow-lg border p-6 space-y-8">

        <!-- FORMULARIO PRINCIPAL -->
        <form @submit.prevent="submit" class="space-y-8">

          <!-- Datos del Médico -->
          <section>
            <h2 class="text-xl font-semibold mb-4">Datos del Médico</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Nombre completo</label>
                <input
                  v-model="form.name"
                  type="text"
                  required
                  class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
                />
                <div v-if="form.errors.name" class="text-sm text-red-600 mt-1">{{ form.errors.name }}</div>
              </div>
              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Especialidad</label>
                <input
                  v-model="form.specialty"
                  type="text"
                  required
                  class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
                />
                <div v-if="form.errors.specialty" class="text-sm text-red-600 mt-1">{{ form.errors.specialty }}</div>
              </div>
              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Correo</label>
                <input
                  v-model="form.email"
                  type="email"
                  class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
                />
                <div v-if="form.errors.email" class="text-sm text-red-600 mt-1">{{ form.errors.email }}</div>
              </div>
              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Teléfono</label>
                <input
                  v-model="form.phone"
                  type="text"
                  class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
                />
                <div v-if="form.errors.phone" class="text-sm text-red-600 mt-1">{{ form.errors.phone }}</div>
              </div>
            </div>
          </section>

          <!-- Horario -->
          <section>
            <h2 class="text-xl font-semibold mb-4">Horario de Atención</h2>
            <p class="text-gray-600 mb-3 text-sm">
              Configura los días y rangos de hora en los que el médico atiende. Marca “Activo” para habilitar el día.
            </p>
            <div class="overflow-x-auto">
              <table class="min-w-full border border-gray-200 rounded-lg">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Día</th>
                    <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Hora inicio</th>
                    <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Hora fin</th>
                    <th class="px-4 py-2 text-center text-sm font-semibold text-gray-700">Activo</th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="(schedule, index) in orderedSchedules"
                    :key="schedule.id ?? index"
                    class="border-t"
                  >
                    <td class="px-4 py-2 text-sm">
                      {{ dayNames[schedule.day_of_week] }}
                    </td>
                    <td class="px-4 py-2 text-sm">
                      <input
                        v-model="schedule.start_time"
                        type="time"
                        class="px-2 py-1 border rounded w-28"
                        :disabled="!schedule.is_active"
                      />
                    </td>
                    <td class="px-4 py-2 text-sm">
                      <input
                        v-model="schedule.end_time"
                        type="time"
                        class="px-2 py-1 border rounded w-28"
                        :disabled="!schedule.is_active"
                      />
                    </td>
                    <td class="px-4 py-2 text-center">
                      <input
                        v-model="schedule.is_active"
                        type="checkbox"
                        class="h-4 w-4 text-blue-600"
                      />
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </section>

          <!-- Botón guardar -->
          <section>
            <button
              type="submit"
              class="bg-blue-600 text-white font-semibold px-8 py-3 rounded-lg shadow hover:bg-blue-700 transition"
              :disabled="form.processing"
            >
              {{ form.processing ? 'Guardando...' : 'Guardar Cambios' }}
            </button>
          </section>

        </form>
      </div>
    </div>
  </AppLayout>
</template>
