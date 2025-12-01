<script setup>
import { ref, computed, watch } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import axios from 'axios'
import Swal from 'sweetalert2'

const props = defineProps({
  selectedDoctor: Object,
  doctors: Array
})

const page = usePage()

function formatLocalDate(date) {
  const y = date.getFullYear()
  const m = String(date.getMonth() + 1).padStart(2, '0')
  const d = String(date.getDate()).padStart(2, '0')
  return `${y}-${m}-${d}`
}

const routeParams = new URLSearchParams(window.location.search);
const startParam = routeParams.get('start');

const selectedDate = ref(startParam ? startParam.split('T')[0] : formatLocalDate(new Date()))
const selectedSlot = ref(startParam ? startParam.split('T')[1]?.slice(0, 5) : '')
const currentWeekStart = ref(getWeekStart(startParam ? new Date(startParam) : new Date()))
const weekSlots = ref({})
const loading = ref(false)
const submitting = ref(false)

function getWeekStart(date) {
  const d = new Date(date)
  const day = d.getDay()
  const diff = d.getDate() - day + (day === 0 ? -6 : 1)
  return new Date(d.setDate(diff))
}

const form = ref({
  patient_name: '',
  patient_email: '',
  patient_phone: '',
  patient_id_number: '',
  patient_birth_date: '',
  reason: ''
})

const monthNames = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre']
const dayNames = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo']
const dayNamesShort = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom']
const maxBirthDate = computed(() => formatLocalDate(new Date()))

const weekDays = computed(() => {
  const days = []
  const today = new Date()
  today.setHours(0, 0, 0, 0)
  
  for (let i = 0; i < 7; i++) {
    const date = new Date(currentWeekStart.value)
    date.setDate(date.getDate() + i)
    date.setHours(0, 0, 0, 0)
    
    const dateStr = formatLocalDate(date)
    const isWeekend = date.getDay() === 0 || date.getDay() === 6
    
    days.push({
      date,
      dateStr,
      day: date.getDate(),
      dayName: dayNamesShort[i],
      dayNameFull: dayNames[i],
      month: monthNames[date.getMonth()],
      isPast: date < today,
      isToday: date.toDateString() === today.toDateString(),
      isWeekend,
      slots: weekSlots.value[dateStr] || []
    })
  }
  
  return days
})

const weekRange = computed(() => {
  const start = weekDays.value[0]
  const end = weekDays.value[6]
  
  if (start.month === end.month) {
    return `${start.day} - ${end.day} de ${start.month} ${start.date.getFullYear()}`
  } else {
    return `${start.day} ${start.month} - ${end.day} ${end.month} ${start.date.getFullYear()}`
  }
})

const formattedSelectedDate = computed(() => {
  if (!selectedDate.value) return ''
  const d = new Date(selectedDate.value + 'T00:00')
  const day = dayNames[d.getDay() === 0 ? 6 : d.getDay() - 1]
  return `${day}, ${d.getDate()} de ${monthNames[d.getMonth()]} de ${d.getFullYear()}`
})

// Cargar horarios para toda la semana - REVISA LA BD Y DESHABILITA LOS TOMADOS
async function loadWeekSlots() {
  if (!props.selectedDoctor) return
  loading.value = true
  weekSlots.value = {}
  
  try {
    const promises = weekDays.value
      .filter(day => !day.isPast && !day.isWeekend)
      .map(async day => {
        try {
          const res = await axios.get(`/medicos/${props.selectedDoctor.slug}/disponibilidad?date=${day.dateStr}`)
          // AQUÍ SE MARCAN LOS HORARIOS TOMADOS DESDE LA BD
          weekSlots.value[day.dateStr] = res.data.slots.map(slot => ({
            time: slot.time,
            taken: !!slot.taken  // <- ESTO DESHABILITA LOS HORARIOS OCUPADOS
          }))
        } catch (e) {
          weekSlots.value[day.dateStr] = []
        }
      })
    
    await Promise.all(promises)
  } catch (e) {
    console.error('Error loading week slots:', e)
  } finally {
    loading.value = false
  }
}

watch(currentWeekStart, loadWeekSlots, { immediate: true })

const handlePrevWeek = () => {
  const newStart = new Date(currentWeekStart.value)
  newStart.setDate(newStart.getDate() - 7)
  currentWeekStart.value = newStart
}

const handleNextWeek = () => {
  const newStart = new Date(currentWeekStart.value)
  newStart.setDate(newStart.getDate() + 7)
  currentWeekStart.value = newStart
}

const selectSlot = (dateStr, slotTime, isTaken) => {
  if (isTaken) return // No permitir seleccionar horarios tomados
  selectedDate.value = dateStr
  selectedSlot.value = slotTime
}

const handleSubmit = () => {
  if (!selectedSlot.value) { 
    Swal.fire({
      icon: 'warning',
      title: 'Atención',
      text: 'Por favor selecciona un horario disponible',
      confirmButtonColor: '#2563eb'
    })
    return 
  }
  
  if (!form.value.patient_name || !form.value.patient_email || !form.value.patient_phone || 
      !form.value.patient_id_number || !form.value.patient_birth_date) { 
    Swal.fire({
      icon: 'warning',
      title: 'Campos incompletos',
      text: 'Por favor completa todos los campos obligatorios',
      confirmButtonColor: '#2563eb'
    })
    return 
  }

  submitting.value = true
  const appointmentDateTime = `${selectedDate.value} ${selectedSlot.value}:00`
  const payload = { doctor_id: props.selectedDoctor.id, ...form.value, appointment_date: appointmentDateTime }

  router.post('/appointments', payload, {
    preserveScroll: true,
    preserveState: true,
    replace: false,
    onSuccess: () => {
      Swal.fire({
        icon: 'success',
        title: '¡Cita confirmada!',
        text: 'Recibirás un correo de confirmación.',
        timer: 3000,
        showConfirmButton: false,
        position: 'top-end',
        toast: true,
        background: '#f0f9ff',
        color: '#0c5460'
      }).then(() => router.get('/'))
    },
    onError: (errors) => {
      Swal.fire({
        icon: 'error',
        title: 'Error al agendar',
        text: Object.values(errors).flat().join('. '),
        confirmButtonColor: '#dc2626'
      })
    },
    onFinish: () => submitting.value = false
  })
}

const goBack = () => router.get('/')
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 py-8 px-4">
    <div class="max-w-7xl mx-auto">
      
      <!-- HEADER MEJORADO -->
      <div class="bg-white rounded-2xl shadow-xl mb-6 overflow-hidden">
        <div class="bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 p-6">
          <button @click="goBack" class="text-white/90 hover:text-white font-medium mb-4 inline-flex items-center gap-2 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2">
              <polyline points="15 18 9 12 15 6"></polyline>
            </svg>
            Regresar
          </button>
          <h1 class="text-3xl font-bold text-white mb-2">Agendar Cita Médica</h1>
          <p class="text-blue-100 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
              <circle cx="9" cy="7" r="4"></circle>
              <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
              <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
            </svg>
            {{ selectedDoctor?.name || 'Selecciona un doctor' }} - Neurología
          </p>
        </div>
      </div>

      <div class="grid lg:grid-cols-3 gap-6">
        
        <!-- CALENDARIO SEMANAL - 2 COLUMNAS -->
        <div class="lg:col-span-2">
          <div class="bg-white rounded-2xl shadow-xl p-6">
            
            <!-- NAVEGACIÓN SEMANAL -->
            <div class="flex items-center justify-between mb-6 pb-4 border-b-2">
              <button @click="handlePrevWeek" 
                class="p-2 hover:bg-blue-50 rounded-lg transition group">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-gray-600 group-hover:text-blue-600" 
                  fill="none" stroke="currentColor" stroke-width="2">
                  <polyline points="15 18 9 12 15 6"></polyline>
                </svg>
              </button>

              <div class="text-center">
                <h2 class="text-2xl font-bold text-gray-800">{{ weekRange }}</h2>
                <p class="text-sm text-gray-500 mt-1">Selecciona fecha y horario</p>
              </div>

              <button @click="handleNextWeek" 
                class="p-2 hover:bg-blue-50 rounded-lg transition group">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-gray-600 group-hover:text-blue-600" 
                  fill="none" stroke="currentColor" stroke-width="2">
                  <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
              </button>
            </div>

            <!-- LOADING STATE -->
            <div v-if="loading" class="text-center py-16">
              <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-blue-500 border-t-transparent"></div>
              <p class="mt-4 text-gray-600 font-medium">Cargando disponibilidad...</p>
            </div>

            <!-- GRID DE DÍAS CON HORARIOS -->
            <div v-else class="grid grid-cols-7 gap-2">
              <div 
                v-for="(dayObj, idx) in weekDays" 
                :key="idx" 
                :class="[
                  'rounded-xl overflow-hidden border-2 transition-all',
                  dayObj.isToday ? 'border-blue-500 shadow-lg' : 'border-gray-200',
                  !dayObj.isPast && !dayObj.isWeekend ? 'hover:shadow-md' : ''
                ]">
                
                <!-- ENCABEZADO DEL DÍA -->
                <div :class="[
                  'p-3 text-center font-bold',
                  dayObj.isPast || dayObj.isWeekend 
                    ? 'bg-gray-100 text-gray-400' 
                    : dayObj.isToday 
                      ? 'bg-gradient-to-br from-blue-500 to-blue-600 text-white' 
                      : 'bg-gradient-to-br from-blue-50 to-indigo-50 text-blue-900'
                ]">
                  <div class="text-[10px] uppercase tracking-wide">{{ dayObj.dayName }}</div>
                  <div class="text-2xl font-black mt-1">{{ dayObj.day }}</div>
                  <div v-if="dayObj.isToday" class="text-[10px] mt-1 font-semibold">HOY</div>
                </div>

                <!-- LISTA DE HORARIOS -->
                <div class="p-1.5 space-y-1 bg-gray-50 min-h-[280px] max-h-[400px] overflow-y-auto">
                  
                  <!-- Día no disponible -->
                  <div v-if="dayObj.isPast || dayObj.isWeekend" 
                    class="text-center py-12 px-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" stroke-width="2">
                      <circle cx="12" cy="12" r="10"></circle>
                      <line x1="4.93" y1="4.93" x2="19.07" y2="19.07"></line>
                    </svg>
                    <p class="text-[10px] text-gray-400 font-medium">
                      {{ dayObj.isPast ? 'Fecha pasada' : 'Cerrado' }}
                    </p>
                  </div>

                  <!-- Sin horarios disponibles -->
                  <div v-else-if="dayObj.slots.length === 0" 
                    class="text-center py-12 px-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" stroke-width="2">
                      <circle cx="12" cy="12" r="10"></circle>
                      <line x1="12" y1="8" x2="12" y2="12"></line>
                      <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    <p class="text-[10px] text-gray-400 font-medium">Sin horarios</p>
                  </div>

                  <!-- HORARIOS DISPONIBLES Y OCUPADOS -->
                  <button
                    v-else
                    v-for="slot in dayObj.slots"
                    :key="slot.time"
                    @click="selectSlot(dayObj.dateStr, slot.time, slot.taken)"
                    :disabled="slot.taken"
                    :class="[
                      'w-full py-2 px-1.5 rounded-lg text-[11px] font-bold transition-all duration-200',
                      slot.taken
                        ? 'bg-red-100 text-red-400 cursor-not-allowed border border-red-200 line-through'
                        : selectedDate === dayObj.dateStr && selectedSlot === slot.time
                          ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-lg scale-105 border-2 border-blue-400'
                          : 'bg-white text-blue-700 hover:bg-blue-500 hover:text-white border border-blue-300 hover:scale-105 hover:shadow-md'
                    ]">
                    <div class="flex items-center justify-center gap-1">
                      <svg v-if="slot.taken" xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="15" y1="9" x2="9" y2="15"></line>
                        <line x1="9" y1="9" x2="15" y2="15"></line>
                      </svg>
                      <svg v-else-if="selectedDate === dayObj.dateStr && selectedSlot === slot.time" 
                        xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="3">
                        <polyline points="20 6 9 17 4 12"></polyline>
                      </svg>
                      <span>{{ slot.time }}</span>
                    </div>
                  </button>
                </div>
              </div>
            </div>

            <!-- RESUMEN SELECCIÓN -->
            <div v-if="selectedDate && selectedSlot" 
              class="mt-6 p-4 bg-gradient-to-r from-green-50 to-emerald-50 border-2 border-green-300 rounded-xl">
              <div class="flex items-center gap-3">
                <div class="bg-green-500 rounded-full p-2">
                  <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="20 6 9 17 4 12"></polyline>
                  </svg>
                </div>
                <div>
                  <p class="text-sm font-bold text-green-800">Cita seleccionada</p>
                  <p class="text-sm text-green-700">{{ formattedSelectedDate }} • {{ selectedSlot }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- FORMULARIO - 1 COLUMNA -->
        <div class="lg:col-span-1">
          <div class="bg-white rounded-2xl shadow-xl p-6 sticky top-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
              </svg>
              Datos del Paciente
            </h2>
            
            <div class="space-y-3">
              
              <!-- NOMBRE -->
              <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Nombre completo *</label>
                <input v-model="form.patient_name" type="text" required
                  class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  placeholder="Juan Pérez González" />
                <div v-if="page.props.errors?.patient_name" class="text-xs text-red-600 mt-1">
                  {{ page.props.errors.patient_name }}
                </div>
              </div>

              <!-- EMAIL -->
              <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Correo electrónico *</label>
                <input v-model="form.patient_email" type="email" required
                  class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  placeholder="correo@ejemplo.com" />
                <div v-if="page.props.errors?.patient_email" class="text-xs text-red-600 mt-1">
                  {{ page.props.errors.patient_email }}
                </div>
              </div>

              <!-- TELÉFONO -->
              <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Teléfono *</label>
                <input v-model="form.patient_phone" type="tel" required
                  class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  placeholder="123-456-7890" />
                <div v-if="page.props.errors?.patient_phone" class="text-xs text-red-600 mt-1">
                  {{ page.props.errors.patient_phone }}
                </div>
              </div>

              <!-- ID -->
              <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Número de identificación *</label>
                <input v-model="form.patient_id_number" type="text" required
                  class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  placeholder="1234567890" />
                <div v-if="page.props.errors?.patient_id_number" class="text-xs text-red-600 mt-1">
                  {{ page.props.errors.patient_id_number }}
                </div>
              </div>

              <!-- FECHA NACIMIENTO -->
              <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Fecha de nacimiento *</label>
                <input v-model="form.patient_birth_date" type="date" required :max="maxBirthDate"
                  class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                <div v-if="page.props.errors?.patient_birth_date" class="text-xs text-red-600 mt-1">
                  {{ page.props.errors.patient_birth_date }}
                </div>
              </div>

              <!-- MOTIVO -->
              <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Motivo de consulta (opcional)</label>
                <textarea v-model="form.reason" rows="2"
                  class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                  placeholder="Describe brevemente..."></textarea>
              </div>

              <!-- BOTÓN -->
              <button @click="handleSubmit" :disabled="!selectedSlot || submitting"
                class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold py-3 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                <span v-if="submitting" class="flex items-center justify-center gap-2">
                  <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  Procesando...
                </span>
                <span v-else>Confirmar Cita</span>
              </button>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</template>