<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    specialty: '',
    email: '',
    phone: '',
    license_number: '',
    bio: '',
    photo: null,
});

const submit = () => {
    form.post(route('doctors.store'));
};

const handlePhotoChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.photo = file;
    }
};
</script>

<template>
    <AppLayout title="Crear Doctor">

        <Head title="Crear Doctor" />

        <div class="py-10 max-w-5xl mx-auto sm:px-6 lg:px-8">

            <!-- Volver -->
            <div class="mb-6">
                <Link :href="route('doctors.index')"
                    class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                ← Volver al listado de doctores
                </Link>
            </div>

            <h1 class="text-3xl font-bold mb-6">Crear Nuevo Doctor</h1>

            <div class="bg-white rounded-xl shadow-lg border p-8">

                <!-- FORMULARIO -->
                <form @submit.prevent="submit" class="space-y-6">

                    <!-- Datos básicos -->
                    <section>
                        <h2 class="text-xl font-semibold mb-4">Datos del Médico</h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Nombre -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Nombre completo *</label>
                                <input v-model="form.name" type="text" required placeholder="Ej: Dr. Juan Pérez"
                                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                                <div v-if="form.errors.name" class="text-sm text-red-600 mt-1">{{ form.errors.name }}
                                </div>
                            </div>

                            <!-- Especialidad -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Especialidad *</label>
                                <input v-model="form.specialty" type="text" required placeholder="Ej: Cardiología"
                                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                                <div v-if="form.errors.specialty" class="text-sm text-red-600 mt-1">{{
                                    form.errors.specialty }}</div>
                            </div>

                            <!-- Email -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Correo electrónico</label>
                                <input v-model="form.email" type="email" placeholder="doctor@ejemplo.com"
                                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                                <div v-if="form.errors.email" class="text-sm text-red-600 mt-1">{{ form.errors.email }}
                                </div>
                            </div>

                            <!-- Teléfono -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Teléfono</label>
                                <input v-model="form.phone" type="tel" placeholder="300-123-4567"
                                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                                <p class="text-xs text-gray-500 mt-1">Formato: XXX-XXX-XXXX (Ej: 300-123-4567)</p>
                                <div v-if="form.errors.phone" class="text-sm text-red-600 mt-1">{{ form.errors.phone }}
                                </div>
                            </div>

                            <!-- Número de licencia -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Número de licencia
                                    *</label>
                                <input v-model="form.license_number" type="text" required placeholder="MED-123456"
                                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                                <p class="text-xs text-gray-500 mt-1">Formato: MED-XXXXXX (Ej: MED-123456)</p>
                                <div v-if="form.errors.license_number" class="text-sm text-red-600 mt-1">{{
                                    form.errors.license_number }}</div>
                            </div>

                            <!-- Foto -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Foto de perfil</label>
                                <input type="file" accept="image/*" @change="handlePhotoChange"
                                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                                <p class="text-xs text-gray-500 mt-1">Formatos: JPG, PNG. Tamaño máximo: 2MB</p>
                                <div v-if="form.errors.photo" class="text-sm text-red-600 mt-1">{{ form.errors.photo }}
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Biografía -->
                    <section>
                        <h2 class="text-xl font-semibold mb-4">Información adicional</h2>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Biografía</label>
                            <textarea v-model="form.bio" rows="5"
                                placeholder="Cuéntanos sobre la experiencia y especialización del doctor..."
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                            <div v-if="form.errors.bio" class="text-sm text-red-600 mt-1">{{ form.errors.bio }}</div>
                        </div>
                    </section>

                    <!-- Botones de acción -->
                    <section class="flex gap-4 pt-6 border-t">
                        <button type="submit"
                            class="px-8 py-3 bg-green-600 text-white font-semibold rounded-lg shadow hover:bg-green-700 transition"
                            :disabled="form.processing">
                            {{ form.processing ? 'Guardando...' : 'Crear Doctor' }}
                        </button>

                        <Link :href="route('doctors.index')"
                            class="px-8 py-3 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition">
                        Cancelar
                        </Link>
                    </section>

                </form>

            </div>

        </div>
    </AppLayout>
</template>
