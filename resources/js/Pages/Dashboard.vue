<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    stats: Object,
    doctors: Array,
    pendingAppointments: Array,
    confirmedAppointments: Array,
    rejectedAppointments: Array,
    completedAppointments: Array,
    allAppointments: Array, // Todas las citas sin filtrar por estado
});

const selectedDoctorFilter = ref(null);
const currentWeekStart = ref(getWeekStart(new Date()));

// Obtener el inicio de la semana (lunes)
function getWeekStart(date) {
    const d = new Date(date);
    const day = d.getDay();
    const diff = d.getDate() - day + (day === 0 ? -6 : 1);
    return new Date(d.setDate(diff));
}

function formatLocalDate(date) {
    const y = date.getFullYear();
    const m = String(date.getMonth() + 1).padStart(2, '0');
    const d = String(date.getDate()).padStart(2, '0');
    return `${y}-${m}-${d}`;
}

const monthNames = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
const dayNamesShort = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];

// Generar días de la semana
const weekDays = computed(() => {
    const days = [];
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    for (let i = 0; i < 7; i++) {
        const date = new Date(currentWeekStart.value);
        date.setDate(date.getDate() + i);
        date.setHours(0, 0, 0, 0);
        
        const dateStr = formatLocalDate(date);
        const isWeekend = date.getDay() === 0 || date.getDay() === 6;
        
        days.push({
            date,
            dateStr,
            day: date.getDate(),
            dayName: dayNamesShort[i],
            month: monthNames[date.getMonth()],
            isPast: date < today,
            isToday: date.toDateString() === today.toDateString(),
            isWeekend
        });
    }
    
    return days;
});

// Rango de fechas
const weekRange = computed(() => {
    const start = weekDays.value[0];
    const end = weekDays.value[6];
    
    if (start.month === end.month) {
        return `${start.day} - ${end.day} de ${start.month} ${start.date.getFullYear()}`;
    } else {
        return `${start.day} ${start.month} - ${end.day} ${end.month} ${start.date.getFullYear()}`;
    }
});

// Usar todas las citas y filtrar dinámicamente por estado
const allFilteredAppointments = computed(() => {
    const appointments = props.allAppointments || [];
    if (!selectedDoctorFilter.value) return appointments;
    return appointments.filter(apt => apt.doctor_id === selectedDoctorFilter.value);
});

// Agrupar TODAS las citas por día y estado
const appointmentsByDay = computed(() => {
    const grouped = {};
    
    weekDays.value.forEach(day => {
        grouped[day.dateStr] = {
            pending: [],
            confirmed: [],
            rejected: [],
            completed: []
        };
    });
    
    // Agrupar todas las citas según su estado actual
    allFilteredAppointments.value.forEach(apt => {
        const aptDate = apt.appointment_date.split('T')[0];
        if (grouped[aptDate]) {
            const status = apt.status; // pending, confirmed, rejected, completed
            if (grouped[aptDate][status]) {
                grouped[aptDate][status].push({ ...apt, status });
            }
        }
    });
    
    return grouped;
});

// Total de citas por día
const totalAppointmentsByDay = computed(() => {
    const totals = {};
    Object.keys(appointmentsByDay.value).forEach(date => {
        const day = appointmentsByDay.value[date];
        totals[date] = day.pending.length + day.confirmed.length + day.rejected.length + day.completed.length;
    });
    return totals;
});

const handlePrevWeek = () => {
    const newStart = new Date(currentWeekStart.value);
    newStart.setDate(newStart.getDate() - 7);
    currentWeekStart.value = newStart;
};

const handleNextWeek = () => {
    const newStart = new Date(currentWeekStart.value);
    newStart.setDate(newStart.getDate() + 7);
    currentWeekStart.value = newStart;
};

const formatDate = (dateString) => {
    const parts = dateString.split('T')[0].split('-');
    const localDate = new Date(parts[0], parts[1] - 1, parts[2]);
    
    return localDate.toLocaleDateString('es-ES', {
        weekday: 'short',
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const formatTime = (dateString) => {
    const timePart = dateString.split('T')[1];
    if (!timePart) return '';
    return timePart.substring(0, 5);
};

// Función para limpiar el nombre del doctor
const cleanDoctorName = (name) => {
    if (!name) return '';
    return name.replace(/^(Dr\.?|Dra\.?)\s*/i, '').trim();
};

// Configuración de estilos por estado
const getStatusConfig = (status) => {
    const configs = {
        pending: {
            bgColor: 'bg-yellow-50',
            borderColor: 'border-yellow-500',
            badgeColor: 'bg-yellow-500',
            buttonColor: 'bg-yellow-600 hover:bg-yellow-700',
            label: 'Pendiente'
        },
        confirmed: {
            bgColor: 'bg-green-50',
            borderColor: 'border-green-500',
            badgeColor: 'bg-green-500',
            buttonColor: 'bg-green-600 hover:bg-green-700',
            label: 'Confirmada'
        },
        rejected: {
            bgColor: 'bg-red-50',
            borderColor: 'border-red-500',
            badgeColor: 'bg-red-500',
            buttonColor: 'bg-red-600 hover:bg-red-700',
            label: 'Rechazada'
        },
        completed: {
            bgColor: 'bg-blue-50',
            borderColor: 'border-blue-500',
            badgeColor: 'bg-blue-500',
            buttonColor: 'bg-blue-600 hover:bg-blue-700',
            label: 'Completada'
        }
    };
    return configs[status] || configs.pending;
};
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

            <!-- ====== FILTRO POR MÉDICO ====== -->
            <div class="bg-white p-6 rounded-lg shadow-md border">
                <h3 class="text-lg font-semibold mb-4">Filtrar por Médico</h3>
                <select v-model="selectedDoctorFilter"
                    class="w-full md:w-64 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option :value="null">Todos los Médicos</option>
                    <option v-for="doctor in doctors" :key="doctor.id" :value="doctor.id">
                        Dr. {{ cleanDoctorName(doctor.name) }}
                    </option>
                </select>
            </div>


            <!-- ====== CALENDARIO SEMANAL - TODAS LAS CITAS ====== -->
            <section class="bg-white p-8 rounded-lg shadow-md border">
                <h3 class="text-2xl font-semibold mb-6 text-gray-800 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                    Calendario de Citas
                </h3>

                <!-- LEYENDA DE ESTADOS -->
                <div class="flex flex-wrap gap-4 mb-6 p-4 bg-gray-50 rounded-lg">
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 bg-yellow-500 rounded"></div>
                        <span class="text-sm font-medium text-gray-700">Pendiente</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 bg-green-500 rounded"></div>
                        <span class="text-sm font-medium text-gray-700">Confirmada</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 bg-blue-500 rounded"></div>
                        <span class="text-sm font-medium text-gray-700">Completada</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 bg-red-500 rounded"></div>
                        <span class="text-sm font-medium text-gray-700">Rechazada</span>
                    </div>
                </div>

                <!-- NAVEGACIÓN SEMANAL -->
                <div class="flex items-center justify-between mb-6 pb-4 border-b-2">
                    <button @click="handlePrevWeek" 
                        class="p-2 hover:bg-gray-50 rounded-lg transition group">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-gray-600 group-hover:text-blue-600" 
                            fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="15 18 9 12 15 6"></polyline>
                        </svg>
                    </button>

                    <div class="text-center">
                        <h4 class="text-xl font-bold text-gray-800">{{ weekRange }}</h4>
                    </div>

                    <button @click="handleNextWeek" 
                        class="p-2 hover:bg-gray-50 rounded-lg transition group">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-gray-600 group-hover:text-blue-600" 
                            fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </button>
                </div>

                <!-- GRID SEMANAL -->
                <div class="grid grid-cols-7 gap-3">
                    <div 
                        v-for="(dayObj, idx) in weekDays" 
                        :key="idx" 
                        :class="[
                            'rounded-xl overflow-hidden border-2 transition-all',
                            dayObj.isToday ? 'border-blue-500 shadow-lg' : 'border-gray-200',
                            dayObj.isWeekend ? 'bg-gray-50' : 'bg-white'
                        ]">
                        
                        <!-- ENCABEZADO DEL DÍA -->
                        <div :class="[
                            'p-3 text-center font-bold',
                            dayObj.isWeekend 
                                ? 'bg-gray-100 text-gray-400' 
                                : dayObj.isToday 
                                    ? 'bg-gradient-to-br from-blue-500 to-indigo-600 text-white' 
                                    : 'bg-gradient-to-br from-blue-50 to-indigo-50 text-blue-900'
                        ]">
                            <div class="text-[10px] uppercase tracking-wide">{{ dayObj.dayName }}</div>
                            <div class="text-2xl font-black mt-1">{{ dayObj.day }}</div>
                            <div v-if="dayObj.isToday" class="text-[10px] mt-1 font-semibold">HOY</div>
                            <div v-if="totalAppointmentsByDay[dayObj.dateStr] > 0" 
                                class="text-[10px] mt-1 bg-white/20 rounded-full px-2 py-0.5 inline-block">
                                {{ totalAppointmentsByDay[dayObj.dateStr] }} cita(s)
                            </div>
                        </div>

                        <!-- CITAS DEL DÍA -->
                        <div class="p-2 space-y-2 min-h-[300px] max-h-[500px] overflow-y-auto">
                            
                            <!-- Sin citas -->
                            <div v-if="totalAppointmentsByDay[dayObj.dateStr] === 0" 
                                class="text-center py-12 px-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="12" y1="8" x2="12" y2="12"></line>
                                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                                </svg>
                                <p class="text-[10px] text-gray-400 font-medium">Sin citas</p>
                            </div>

                            <!-- CITAS PENDIENTES -->
                            <div 
                                v-for="apt in appointmentsByDay[dayObj.dateStr].pending"
                                :key="'pending-' + apt.id"
                                :class="[
                                    getStatusConfig('pending').bgColor,
                                    'border-l-4',
                                    getStatusConfig('pending').borderColor,
                                    'p-3 rounded-lg hover:shadow-md transition-all'
                                ]">
                                
                                <div class="flex items-start justify-between gap-2 mb-2">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-1 mb-1">
                                            <div :class="[
                                                'w-2 h-2 rounded-full',
                                                getStatusConfig('pending').badgeColor
                                            ]"></div>
                                            <span class="text-[9px] font-bold text-gray-600 uppercase">
                                                {{ getStatusConfig('pending').label }}
                                            </span>
                                        </div>
                                        <p class="text-xs font-bold text-gray-800 truncate">
                                            {{ apt.patient_name }}
                                        </p>
                                        <p class="text-[10px] text-gray-600 truncate">
                                            Dr. {{ cleanDoctorName(apt.doctor.name) }}
                                        </p>
                                    </div>
                                    <div :class="[
                                        getStatusConfig('pending').badgeColor,
                                        'text-white text-[10px] font-bold px-2 py-1 rounded flex-shrink-0'
                                    ]">
                                        {{ formatTime(apt.appointment_date) }}
                                    </div>
                                </div>

                                <Link :href="route('appointments.show', apt.slug)"
                                    :class="[
                                        'block w-full text-center px-2 py-1.5 text-white text-[10px] font-bold rounded transition',
                                        getStatusConfig('pending').buttonColor
                                    ]">
                                    Ver Detalles
                                </Link>
                            </div>

                            <!-- CITAS CONFIRMADAS -->
                            <div 
                                v-for="apt in appointmentsByDay[dayObj.dateStr].confirmed"
                                :key="'confirmed-' + apt.id"
                                :class="[
                                    getStatusConfig('confirmed').bgColor,
                                    'border-l-4',
                                    getStatusConfig('confirmed').borderColor,
                                    'p-3 rounded-lg hover:shadow-md transition-all'
                                ]">
                                
                                <div class="flex items-start justify-between gap-2 mb-2">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-1 mb-1">
                                            <div :class="[
                                                'w-2 h-2 rounded-full',
                                                getStatusConfig('confirmed').badgeColor
                                            ]"></div>
                                            <span class="text-[9px] font-bold text-gray-600 uppercase">
                                                {{ getStatusConfig('confirmed').label }}
                                            </span>
                                        </div>
                                        <p class="text-xs font-bold text-gray-800 truncate">
                                            {{ apt.patient_name }}
                                        </p>
                                        <p class="text-[10px] text-gray-600 truncate">
                                            Dr. {{ cleanDoctorName(apt.doctor.name) }}
                                        </p>
                                    </div>
                                    <div :class="[
                                        getStatusConfig('confirmed').badgeColor,
                                        'text-white text-[10px] font-bold px-2 py-1 rounded flex-shrink-0'
                                    ]">
                                        {{ formatTime(apt.appointment_date) }}
                                    </div>
                                </div>

                                <Link :href="route('appointments.show', apt.slug)"
                                    :class="[
                                        'block w-full text-center px-2 py-1.5 text-white text-[10px] font-bold rounded transition',
                                        getStatusConfig('confirmed').buttonColor
                                    ]">
                                    Ver Detalles
                                </Link>
                            </div>

                            <!-- CITAS COMPLETADAS -->
                            <div 
                                v-for="apt in appointmentsByDay[dayObj.dateStr].completed"
                                :key="'completed-' + apt.id"
                                :class="[
                                    getStatusConfig('completed').bgColor,
                                    'border-l-4',
                                    getStatusConfig('completed').borderColor,
                                    'p-3 rounded-lg hover:shadow-md transition-all'
                                ]">
                                
                                <div class="flex items-start justify-between gap-2 mb-2">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-1 mb-1">
                                            <div :class="[
                                                'w-2 h-2 rounded-full',
                                                getStatusConfig('completed').badgeColor
                                            ]"></div>
                                            <span class="text-[9px] font-bold text-gray-600 uppercase">
                                                {{ getStatusConfig('completed').label }}
                                            </span>
                                        </div>
                                        <p class="text-xs font-bold text-gray-800 truncate">
                                            {{ apt.patient_name }}
                                        </p>
                                        <p class="text-[10px] text-gray-600 truncate">
                                            Dr. {{ cleanDoctorName(apt.doctor.name) }}
                                        </p>
                                    </div>
                                    <div :class="[
                                        getStatusConfig('completed').badgeColor,
                                        'text-white text-[10px] font-bold px-2 py-1 rounded flex-shrink-0'
                                    ]">
                                        {{ formatTime(apt.appointment_date) }}
                                    </div>
                                </div>

                                <Link :href="route('appointments.show', apt.slug)"
                                    :class="[
                                        'block w-full text-center px-2 py-1.5 text-white text-[10px] font-bold rounded transition',
                                        getStatusConfig('completed').buttonColor
                                    ]">
                                    Ver Detalles
                                </Link>
                            </div>

                            <!-- CITAS RECHAZADAS -->
                            <div 
                                v-for="apt in appointmentsByDay[dayObj.dateStr].rejected"
                                :key="'rejected-' + apt.id"
                                :class="[
                                    getStatusConfig('rejected').bgColor,
                                    'border-l-4',
                                    getStatusConfig('rejected').borderColor,
                                    'p-3 rounded-lg hover:shadow-md transition-all'
                                ]">
                                
                                <div class="flex items-start justify-between gap-2 mb-2">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-1 mb-1">
                                            <div :class="[
                                                'w-2 h-2 rounded-full',
                                                getStatusConfig('rejected').badgeColor
                                            ]"></div>
                                            <span class="text-[9px] font-bold text-gray-600 uppercase">
                                                {{ getStatusConfig('rejected').label }}
                                            </span>
                                        </div>
                                        <p class="text-xs font-bold text-gray-800 truncate">
                                            {{ apt.patient_name }}
                                        </p>
                                        <p class="text-[10px] text-gray-600 truncate">
                                            Dr. {{ cleanDoctorName(apt.doctor.name) }}
                                        </p>
                                    </div>
                                    <div :class="[
                                        getStatusConfig('rejected').badgeColor,
                                        'text-white text-[10px] font-bold px-2 py-1 rounded flex-shrink-0'
                                    ]">
                                        {{ formatTime(apt.appointment_date) }}
                                    </div>
                                </div>

                                <Link :href="route('appointments.show', apt.slug)"
                                    :class="[
                                        'block w-full text-center px-2 py-1.5 text-white text-[10px] font-bold rounded transition',
                                        getStatusConfig('rejected').buttonColor
                                    ]">
                                    Ver Detalles
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ====== BOTÓN GESTIONAR CITAS ====== -->
            <div class="text-center mt-10">
                <Link href="/appointments"
                    class="bg-indigo-600 text-white px-6 py-3 rounded-lg text-lg shadow hover:bg-indigo-700">
                Gestionar Todas las Citas
                </Link>
            </div>

            <!-- ====== GESTIONAR DOCTORES ====== -->
            <section class="bg-white p-8 rounded-lg shadow-md mt-10 border">
                <h3 class="text-2xl font-semibold mb-4">Gestión de Doctores</h3>

                <p class="text-gray-600 mb-6">
                    Administra médicos activos, edita su información o desactívalos del sistema.
                </p>

                <Link href="/doctors" class="bg-green-600 text-white px-6 py-3 rounded-lg shadow hover:bg-green-700">
                Ir al Módulo de Doctores
                </Link>
            </section>
        </div>
    </AppLayout>
</template>