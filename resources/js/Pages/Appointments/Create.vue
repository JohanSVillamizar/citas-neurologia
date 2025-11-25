<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import VueCal from 'vue-cal';
import 'vue-cal/dist/vuecal.css';
import axios from 'axios';

const props = defineProps({
    doctors: Array,
    selectedDoctor: Object,
});

const selectedDate = ref(new Date().toISOString().slice(0, 10));
const slots = ref([]);
const selectedSlot = ref('');

const activeDoctor = computed(() => props.selectedDoctor);

watch(selectedDate, async (date) => {
    selectedSlot.value = ''; // limpia el slot
    if (!activeDoctor.value) return;

    try {
        const res = await axios.get(`/medicos/${activeDoctor.value.slug}/disponibilidad?date=${date}`);
        slots.value = res.data.slots;
    } catch (error) {
        console.error('Error cargando disponibilidad:', error);
    }
}, { immediate: true });


const pickSlot = slot => {
    if (!slot.taken) selectedSlot.value = slot.time;
};

const form = ref({
    doctor_id: props.selectedDoctor ? props.selectedDoctor.id : '',
    patient_name: '',
    patient_email: '',
    patient_phone: '',
    reason: '',
    appointment_date: ''
});

const confirmSlot = () => {
    if (!selectedSlot.value) return;

    form.value.appointment_date = selectedDate.value + 'T' + selectedSlot.value;
    // ahora sí envías el form
    document.querySelector('form').submit();
};
</script>

<template>
    <AppLayout title="Agendar cita">

        <Head title="Agendar Cita" />
        <div class="max-w-5xl mx-auto my-12 bg-white rounded-lg shadow-lg flex flex-col md:flex-row p-8 gap-8">
            <!-- LADO IZQUIERDO: FORMULARIO -->
            <div class="flex-1 flex flex-col gap-6">
                <form :action="route('appointments.store')" method="POST" class="space-y-5"
                    @submit.prevent="confirmSlot">
                    <input v-if="form.doctor_id" type="hidden" name="doctor_id" :value="form.doctor_id" />
                    <input v-if="form.appointment_date" type="hidden" name="appointment_date"
                        :value="form.appointment_date" />
                    <div>
                        <label class="font-semibold mb-1 block">Nombre</label>
                        <input type="text" name="patient_name" v-model="form.patient_name" required
                            class="w-full border rounded px-3 py-2" />
                    </div>
                    <div>
                        <label class="font-semibold mb-1 block">Correo electrónico</label>
                        <input type="email" name="patient_email" v-model="form.patient_email" required
                            class="w-full border rounded px-3 py-2" />
                    </div>
                    <div>
                        <label class="font-semibold mb-1 block">Teléfono</label>
                        <input type="text" name="patient_phone" v-model="form.patient_phone" required
                            class="w-full border rounded px-3 py-2" />
                    </div>
                    <div>
                        <label class="font-semibold mb-1 block">Motivo de la cita (opcional)</label>
                        <textarea name="reason" v-model="form.reason" class="w-full border rounded px-3 py-2"
                            rows="2"></textarea>
                    </div>
                    <button type="submit" :disabled="!selectedSlot"
                        class="w-full py-3 rounded bg-blue-600 text-white font-semibold hover:bg-blue-700">
                        Confirmar Cita
                    </button>
                </form>
            </div>

            <!-- LADO DERECHO: CALENDARIO Y SLOTS -->
            <div class="flex-1 flex flex-col items-start gap-6">
                <div class="mb-4 w-full">
                    <vue-cal style="width:100%; height:300px; border-radius: 0.5rem;" :time="false" default-view="month"
                        :on-cell-click="cell => selectedDate.value = cell.date.toISOString().slice(0, 10)"
                        :disable-views="['years', 'year', 'week', 'day']"
                        :locales="{ es: { weekDays: ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'], months: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'], years: 'Años', year: 'Año', month: 'Mes', week: 'Semana', day: 'Día', today: 'Hoy', noEvent: 'Sin eventos', allDay: 'Todo el día' } }"
                        locale="es" :hide-title-bar="false" />
                </div>
                <h4 class="font-semibold text-lg mb-2">Selecciona un horario:</h4>
                <div class="flex flex-wrap gap-2 w-full">
                    <button v-for="slot in slots" :key="slot.time" type="button" @click="pickSlot(slot)"
                        :disabled="slot.taken" :class="[
                            'px-5 py-2 rounded font-semibold transition-all border',
                            slot.taken
                                ? 'bg-gray-200 text-gray-400 border-gray-200 line-through'
                                : (selectedSlot === slot.time
                                    ? 'bg-blue-700 text-white border-blue-800 shadow'
                                    : 'bg-blue-100 text-blue-900 hover:bg-blue-200 border-blue-100')
                        ]">
                        {{ slot.time }}
                    </button>
                </div>
                <div v-if="selectedSlot" class="mt-2 text-blue-700 font-semibold">
                    Cita seleccionada: {{ selectedDate }} a las {{ selectedSlot }}
                </div>
            </div>
        </div>
    </AppLayout>
</template>