<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

const form = useForm({
  nombre_sobre: '',
})

const submit = () => {
  form.post(route('sobres-plantilla.store'), {
    preserveScroll: true,
  })
}
</script>

<template>
  <Head title="Crear Plantilla" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
          Crear Plantilla
        </h2>

        <Link
          :href="route('sobres-plantilla.index')"
          class="inline-flex items-center rounded-md bg-gray-200 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-800 hover:bg-gray-300"
        >
          Volver
        </Link>
      </div>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900">
            <form @submit.prevent="submit" class="space-y-4">

              <div>
                <label class="block text-sm font-medium text-gray-700">
                  Nombre del sobre
                </label>

                <input
                  v-model="form.nombre_sobre"
                  type="text"
                  placeholder="Ej: Sobre Azul A4"
                  class="mt-1 w-full rounded-md border border-gray-300 p-2"
                  :class="form.errors.nombre_sobre ? 'border-red-500' : ''"
                />

                <p v-if="form.errors.nombre_sobre" class="mt-1 text-sm text-red-600">
                  {{ form.errors.nombre_sobre }}
                </p>
              </div>

              <div class="flex items-center gap-3">
                <button
                  type="submit"
                  class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-blue-700 disabled:opacity-50"
                  :disabled="form.processing"
                >
                  {{ form.processing ? 'Guardando...' : 'Guardar' }}
                </button>

                <Link
                  :href="route('sobres-plantilla.index')"
                  class="text-blue-600 hover:text-blue-800 hover:underline"
                >
                  Cancelar
                </Link>
              </div>

            </form>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
