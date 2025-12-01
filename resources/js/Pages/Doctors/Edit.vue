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
  photo: null,
  schedules: (props.doctor.schedules || []).map(s => ({
    id: s.id,
    day_of_week: s.day_of_week,
    start_time: s.start_time ? s.start_time.slice(0, 5) : '',
    end_time: s.end_time ? s.end_time.slice(0, 5) : '',
    is_active: !!s.is_active,
  })),
});

const handlePhotoChange = (e) => {
  const file = e.target.files[0];
  if (file) {
    form.photo = file;
  }
};

const dayNames = {
  0: 'Domingo',
  1: 'Lunes',
  2: 'Martes',
  3: 'Miércoles',
  4: 'Jueves',
  5: 'Viernes',
  6: 'Sábado',
};

// Agrupa horarios por día (permitiendo múltiples jornadas por día)
const schedulesByDay = computed(() => {
  const grouped = {};
  for (let i = 0; i <= 6; i++) {
    grouped[i] = form.schedules.filter(s => s.day_of_week === i);
  }
  return grouped;
});

// Detecta qué días tienen al menos un horario
const daysWithSchedules = computed(() =>
  new Set(form.schedules.map(s => s.day_of_week))
);

// Días disponibles para agregar nuevas jornadas (todos los días)
const availableDays = computed(() =>
  Object.entries(dayNames).map(([day, name]) => ({ value: parseInt(day), label: name }))
);

const submit = () => {
  form
    .transform((data) => ({
      ...data,
      _method: 'put',      // spoof de método
    }))
    .post(route('doctors.update', props.doctor.slug), {
      forceFormData: true, // obligar multipart/form-data
    });
};

// Agregar nueva jornada a un día
const addSchedule = (dayOfWeek) => {
  form.schedules.push({
    id: null,
    day_of_week: dayOfWeek,
    start_time: '08:00',
    end_time: '17:00',
    is_active: true,
  });
};

// Eliminar jornada (solo las nuevas, sin ID)
const removeSchedule = (index) => {
  const schedule = form.schedules[index];
  if (schedule.id === null) {
    form.schedules.splice(index, 1);
  } else {
    alert('No puedes eliminar horarios existentes. Desactívalos en su lugar.');
  }
};
</script>

<template>
  <AppLayout title="Editar Doctor">

    <Head :title="`Editar ${doctor.name}`" />

    <div class="py-10 max-w-5xl mx-auto sm:px-6 lg:px-8">

      <!-- Volver -->
      <div class="mb-6">
        <Link :href="route('doctors.index')"
          class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
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
                <input v-model="form.name" type="text" required
                  class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500" />
                <div v-if="form.errors.name" class="text-sm text-red-600 mt-1">{{ form.errors.name }}</div>
              </div>
              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Especialidad</label>
                <input v-model="form.specialty" type="text" required
                  class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500" />
                <div v-if="form.errors.specialty" class="text-sm text-red-600 mt-1">{{ form.errors.specialty }}</div>
              </div>
              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Correo</label>
                <input v-model="form.email" type="email"
                  class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500" />
                <div v-if="form.errors.email" class="text-sm text-red-600 mt-1">{{ form.errors.email }}</div>
              </div>
              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Teléfono</label>
                <input v-model="form.phone" type="text"
                  class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500" />
                <div v-if="form.errors.phone" class="text-sm text-red-600 mt-1">{{ form.errors.phone }}</div>
              </div>
              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Foto de perfil</label>

                <!-- Foto actual -->
                <div class="mb-2" v-if="doctor.photo">
                  <img :src="doctor.photo" alt="Foto actual" class="h-16 w-16 rounded-full object-cover border" />
                </div>

                <!-- Input para nueva foto -->
                <input type="file" accept="image/*" @change="handlePhotoChange"
                  class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500" />
                <p class="text-xs text-gray-500 mt-1">Formatos: JPG, PNG. Tamaño máximo: 2MB</p>

                <div v-if="form.errors.photo" class="text-sm text-red-600 mt-1">
                  {{ form.errors.photo }}
                </div>
              </div>
            </div>
          </section>

          <!-- Horario -->
          <section>
            <h2 class="text-xl font-semibold mb-4">Horario de Atención</h2>
            <p class="text-gray-600 mb-4 text-sm">
              Configura los días y rangos de hora en los que el médico atiende. Un doctor puede trabajar múltiples
              jornadas en el mismo día.
            </p>

            <!-- Por cada día de la semana -->
            <div class="space-y-4">
              <div v-for="day in [0, 1, 2, 3, 4, 5, 6]" :key="day" class="border rounded-lg p-4 bg-gray-50">
                <!-- Encabezado del día -->
                <div class="flex items-center justify-between mb-3">
                  <h3 class="text-lg font-semibold">{{ dayNames[day] }}</h3>
                  <button type="button" @click="addSchedule(day)"
                    class="px-3 py-1 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition">
                    + Agregar jornada
                  </button>
                </div>

                <!-- Jornadas del día -->
                <div v-if="schedulesByDay[day].length > 0" class="space-y-3">
                  <div v-for="(schedule, idx) in schedulesByDay[day]" :key="schedule.id ?? idx"
                    class="bg-white border rounded p-4 flex items-end gap-4">
                    <!-- Hora inicio -->
                    <div class="flex-1">
                      <label class="block text-xs font-semibold text-gray-600 mb-1">Hora inicio</label>
                      <input v-model="schedule.start_time" type="time"
                        class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
                        :disabled="!schedule.is_active" />
                    </div>

                    <!-- Hora fin -->
                    <div class="flex-1">
                      <label class="block text-xs font-semibold text-gray-600 mb-1">Hora fin</label>
                      <input v-model="schedule.end_time" type="time"
                        class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
                        :disabled="!schedule.is_active" />
                    </div>

                    <!-- Checkbox activo -->
                    <div class="flex items-center gap-2">
                      <input v-model="schedule.is_active" type="checkbox" class="h-4 w-4 text-blue-600"
                        :id="`active-${schedule.id}-${idx}`" />
                      <label :for="`active-${schedule.id}-${idx}`" class="text-xs font-semibold text-gray-600">
                        Activo
                      </label>
                    </div>

                    <!-- Botón eliminar (solo para nuevos) -->
                    <button v-if="schedule.id === null" type="button"
                      @click="removeSchedule(form.schedules.indexOf(schedule))"
                      class="px-3 py-2 bg-red-600 text-white text-sm font-semibold rounded-lg hover:bg-red-700 transition">
                      Eliminar
                    </button>
                  </div>
                </div>

                <!-- Mensaje si no hay jornadas -->
                <div v-else class="text-sm text-gray-500 italic">
                  Sin jornadas configuradas
                </div>
              </div>
            </div>
          </section>

          <!-- Botón guardar -->
          <section>
            <button type="submit"
              class="bg-blue-600 text-white font-semibold px-8 py-3 rounded-lg shadow hover:bg-blue-700 transition"
              :disabled="form.processing">
              {{ form.processing ? 'Guardando...' : 'Guardar Cambios' }}
            </button>
          </section>

        </form>
      </div>
    </div>
  </AppLayout>
</template>
