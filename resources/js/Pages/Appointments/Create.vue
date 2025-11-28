<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 p-8">
    <div class="max-w-6xl mx-auto bg-white rounded-2xl shadow-2xl overflow-hidden">

      <!-- HEADER -->
      <div class="bg-gradient-to-r from-blue-600 to-indigo-600 p-8 text-white">
        <h1 class="text-3xl font-bold mb-2">Agendar Cita Médica</h1>
        <p class="text-blue-100">Neurología - {{ selectedDoctor?.name || 'Selecciona un doctor' }}</p>
      </div>

      <div class="grid md:grid-cols-2 gap-8 p-8">

        <!-- FORMULARIO -->
        <div class="space-y-6">

          <!-- BOTON REGRESAR -->
          <div class="mb-4">
            <button @click="goBack" class="text-blue-600 hover:text-blue-800 font-semibold">
              ← Regresar al panel principal
            </button>
          </div>

          <div class="space-y-4">
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Nombre completo *</label>
              <input v-model="form.patient_name" type="text" required
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                placeholder="Juan Pérez González" />
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Correo electrónico *</label>
              <input v-model="form.patient_email" type="email" required
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                placeholder="correo@ejemplo.com" />
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Teléfono *</label>
              <input v-model="form.patient_phone" type="tel" required
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                placeholder="3001234567" />
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Número de identificación *</label>
              <input v-model="form.patient_id_number" type="text" required
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                placeholder="1234567890" />
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Fecha de nacimiento *</label>
              <input v-model="form.patient_birth_date" type="date" required :max="maxBirthDate"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" />
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Motivo de la cita (opcional)</label>
              <textarea v-model="form.reason" rows="3"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 resize-none"
                placeholder="Describe brevemente el motivo de tu consulta..."></textarea>
            </div>

            <button @click="handleSubmit" :disabled="!selectedSlot || submitting"
              class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-4 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed transition">
              {{ submitting ? 'Guardando...' : 'Confirmar Cita' }}
            </button>
          </div>
        </div>

        <!-- CALENDARIO Y HORARIOS -->
        <div class="space-y-6">
          <div class="border border-gray-200 rounded-lg p-4">

            <!-- NAV MENSUAL -->
            <div class="flex items-center justify-between mb-4">
              <button @click="handlePrevMonth" class="p-2 hover:bg-gray-100 rounded-lg">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2">
                  <polyline points="15 18 9 12 15 6"></polyline>
                </svg>
              </button>

              <h3 class="text-lg font-semibold">
                {{ monthNames[currentMonth.getMonth()] }} {{ currentMonth.getFullYear() }}
              </h3>

              <button @click="handleNextMonth" class="p-2 hover:bg-gray-100 rounded-lg">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2">
                  <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
              </button>
            </div>

            <!-- nombres semana -->
            <div class="grid grid-cols-7 gap-1 mb-2">
              <div v-for="day in dayNames" :key="day" class="text-center text-xs font-semibold text-gray-600 py-2">
                {{ day.slice(0, 3) }}
              </div>
            </div>

            <!-- días -->
            <div class="grid grid-cols-7 gap-1">
              <button v-for="(dayObj, idx) in calendarDays" :key="idx" @click="handleDateClick(dayObj.date)"
                :disabled="dayObj.isPast || !dayObj.isCurrentMonth || dayObj.isWeekend"
                :class="[ 
                  'aspect-square p-2 text-sm rounded-lg transition',
                  !dayObj.isCurrentMonth && 'text-gray-300',
                  dayObj.isPast || dayObj.isWeekend ? 'text-gray-300 cursor-not-allowed' : 'hover:bg-blue-50',
                  dayObj.isSelected && 'bg-blue-600 text-white font-bold',
                  dayObj.isToday && !dayObj.isSelected && 'border-2 border-blue-600 font-semibold'
                ]">
                {{ dayObj.day }}
              </button>
            </div>
          </div>

          <!-- HORARIOS -->
          <div class="border border-gray-200 rounded-lg p-4">
            <h3 class="font-semibold text-gray-800 mb-3 flex items-center gap-2">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <polyline points="12 6 12 12 16 14"></polyline>
              </svg>
              Selecciona un horario:
            </h3>

            <div v-if="!selectedDate" class="text-gray-500 text-center py-8">Selecciona una fecha</div>
            <div v-else-if="loading" class="text-gray-500 text-center py-8">Cargando horarios...</div>

            <div v-else>
              <div v-if="slots.length === 0" class="text-gray-500 text-center py-8">
                No hay horarios disponibles
              </div>

              <div v-else class="grid grid-cols-3 gap-2 max-h-64 overflow-y-auto">
                <button v-for="slot in slots" :key="slot.time" @click="pickSlot(slot)" :disabled="slot.taken"
                  :class="[ 
                    'py-2 px-3 rounded-lg text-sm font-medium transition border',
                    slot.taken 
                      ? 'bg-gray-200 text-gray-500 cursor-not-allowed line-through border-gray-300'
                      : selectedSlot === slot.time
                        ? 'bg-blue-600 text-white border-blue-700 shadow-lg'
                        : 'bg-blue-50 text-blue-700 hover:bg-blue-100 border-blue-200'
                  ]">
                  {{ slot.time }}
                </button>
              </div>

              <div v-if="selectedDate && selectedSlot" class="mt-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                <p class="text-sm text-green-800"><strong>Cita seleccionada:</strong><br />
                  {{ formattedSelectedDate }} a las {{ selectedSlot }}
                </p>
              </div>
            </div>
          </div>

        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import axios from 'axios'
import Swal from 'sweetalert2'

const props = defineProps({
  selectedDoctor: Object,
  doctors: Array
})

function formatLocalDate(date){
  const y=date.getFullYear()
  const m=String(date.getMonth()+1).padStart(2,'0')
  const d=String(date.getDate()).padStart(2,'0')
  return `${y}-${m}-${d}`
}

const selectedDate = ref(formatLocalDate(new Date()))
const selectedSlot = ref('')
const currentMonth = ref(new Date())
const slots = ref([])
const loading = ref(false)
const submitting = ref(false)

const form = ref({
  patient_name:'',
  patient_email:'',
  patient_phone:'',
  patient_id_number:'',
  patient_birth_date:'',
  reason:''
})

const monthNames = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre']
const dayNames = ['Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo']
const maxBirthDate = computed(()=> formatLocalDate(new Date()))

const calendarDays = computed(()=>{
  const year=currentMonth.value.getFullYear()
  const month=currentMonth.value.getMonth()
  const firstDay=new Date(year,month,1)
  const daysInMonth=new Date(year,month+1,0).getDate()
  const startingDayOfWeek=(firstDay.getDay()+6)%7
  const today=new Date(); today.setHours(0,0,0,0)
  const days=[]

  // días previos mes
  const prevMonthLastDay=new Date(year,month,0).getDate()
  for(let i=startingDayOfWeek-1;i>=0;i--){
    const date=new Date(year,month-1,prevMonthLastDay-i)
    date.setHours(0,0,0,0)
    days.push({day:prevMonthLastDay-i,isCurrentMonth:false,date,isPast:date<today,isToday:false,isSelected:false,isWeekend:date.getDay()==0||date.getDay()==6})
  }

  // días mes actual
  for(let i=1;i<=daysInMonth;i++){
    const date=new Date(year,month,i)
    date.setHours(0,0,0,0)
    const dateStr=formatLocalDate(date)
    days.push({
      day:i,
      isCurrentMonth:true,
      date,
      isPast:date<today,
      isToday:date.toDateString()===today.toDateString(),
      isSelected:selectedDate.value===dateStr,
      isWeekend:date.getDay()==0||date.getDay()==6
    })
  }

  // días siguientes mes
  const remaining=42-days.length
  for(let i=1;i<=remaining;i++){
    const date=new Date(year,month+1,i)
    date.setHours(0,0,0,0)
    days.push({day:i,isCurrentMonth:false,date,isPast:date<today,isToday:false,isSelected:false,isWeekend:date.getDay()==0||date.getDay()==6})
  }

  return days
})

const formattedSelectedDate = computed(()=>{
  if(!selectedDate.value) return ''
  const d=new Date(selectedDate.value+'T00:00')
  const day=dayNames[d.getDay()===0?6:d.getDay()-1]
  return `${day}, ${d.getDate()} de ${monthNames[d.getMonth()]} de ${d.getFullYear()}`
})

// Carga slots y marca ocupados según BD
watch(selectedDate, async(date)=>{
  selectedSlot.value=''
  if(!props.selectedDoctor) return
  loading.value=true
  try{
    const res=await axios.get(`/medicos/${props.selectedDoctor.slug}/disponibilidad?date=${date}`)
    // marcar tomados y deshabilitar fines de semana
    slots.value=res.data.slots.map(slot=>({
      ...slot,
      taken: !!slot.taken
    }))
  }catch(e){
    slots.value=[]
  }finally{loading.value=false}
},{immediate:true})

const handlePrevMonth=()=> currentMonth.value=new Date(currentMonth.value.getFullYear(),currentMonth.value.getMonth()-1)
const handleNextMonth=()=> currentMonth.value=new Date(currentMonth.value.getFullYear(),currentMonth.value.getMonth()+1)
const handleDateClick=date=>{ const copy=new Date(date); copy.setHours(0,0,0,0); selectedDate.value=formatLocalDate(copy)}
const pickSlot=slot=>{ if(!slot.taken) selectedSlot.value=slot.time }

const handleSubmit=()=>{
  if(!selectedSlot.value){ alert('Selecciona un horario'); return }
  if(!form.value.patient_name || !form.value.patient_email || !form.value.patient_phone || !form.value.patient_id_number || !form.value.patient_birth_date){ alert('Completa todos los campos'); return }

  submitting.value=true
  const appointmentDateTime=`${selectedDate.value} ${selectedSlot.value}:00`
  const payload={doctor_id:props.selectedDoctor.id,...form.value,appointment_date:appointmentDateTime}

  router.post('/appointments', payload,{
    preserveScroll:true,
    preserveState:true,
    replace:false,
    onSuccess:()=>{
      Swal.fire({
        icon:'success',
        title:'¡Cita solicitada!',
        text:'Recibirás un correo de confirmación.',
        timer:3000,
        showConfirmButton:false,
        position:'top-end',
        toast:true,
        background:'#f0f9ff',
        color:'#0c5460'
      }).then(()=> router.get('/'))
    },
    onError:(errors)=>{
      Swal.fire({
        icon:'error',
        title:'Error',
        text:'Revisa los datos e intenta nuevamente.',
        timer:3000,
        showConfirmButton:false,
        position:'top-end',
        toast:true,
        background:'#f8d7da',
        color:'#721c24'
      })
    },
    onFinish:()=> submitting.value=false
  })
}

const goBack=()=> router.get('/')
</script>
