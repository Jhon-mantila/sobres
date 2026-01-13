<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link } from '@inertiajs/vue3'

const props = defineProps({
  sobre: Object,
})
</script>

<template>
  <Head :title="`Plantilla: ${sobre.nombre_sobre}`" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
          {{ sobre.nombre_sobre }}
        </h2>

        <div class="flex gap-2">
          <Link
            :href="route('sobres-plantilla.index')"
            class="inline-flex items-center rounded-md bg-gray-200 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-800 hover:bg-gray-300"
          >
            Volver
          </Link>

          <Link
            :href="route('sobres-plantilla.edit', sobre.id)"
            class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-blue-700"
          >
            Editar
          </Link>
        </div>
      </div>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">

        <!-- Info básica -->
        <div class="mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900 space-y-2">
            <div>
              <span class="font-semibold">Nombre:</span>
              {{ sobre.nombre_sobre }}
            </div>

            <div>
              <span class="font-semibold">Fecha creación:</span>
              {{ sobre.created_at }}
            </div>

            <div>
              <span class="font-semibold">Última modificación:</span>
              {{ sobre.updated_at }}
            </div>
          </div>
        </div>

        <!-- Imágenes -->
        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900">
            <h3 class="mb-4 text-lg font-semibold">
              Imágenes asociadas
            </h3>

            <div v-if="sobre.imagenes.length === 0" class="text-gray-500">
              No hay imágenes asociadas a esta plantilla.
            </div>

            <div v-else class="overflow-x-auto">
              <table class="w-full">
                <thead>
                  <tr class="border-b">
                    <th class="px-4 py-2 text-left">ID</th>
                    <th class="px-4 py-2 text-left">Fecha creación</th>
                  </tr>
                </thead>

                <tbody>
                  <tr
                    v-for="imagen in sobre.imagenes"
                    :key="imagen.id"
                    class="border-b"
                  >
                    <td class="px-4 py-2">
                      {{ imagen.id }}
                    </td>
                    <td class="px-4 py-2">
                      {{ imagen.created_at }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

          </div>
        </div>

      </div>
    </div>
  </AuthenticatedLayout>
</template>
