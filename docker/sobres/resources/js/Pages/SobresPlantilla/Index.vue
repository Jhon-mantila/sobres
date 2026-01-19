<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import Pagination from '@/Components/Pagination.vue'


const props = defineProps({
  sobres_plantillas: Object,
  filters: Object,
})

const search = ref(props.filters?.search ?? '')

// debounce para no disparar request en cada tecla sin control
let t = null
watch(search, (val) => {
  clearTimeout(t)
  t = setTimeout(() => {
    router.get(
      route('sobres-plantilla.index'),
      { search: val },
      { preserveState: true, preserveScroll: true, replace: true }
    )
  }, 300)
})
</script>

<template>
  <Head title="Sobres Plantilla" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
          Sobres Plantilla
        </h2>

        <Link
          :href="route('sobres-plantilla.create')"
          class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-blue-700"
        >
          Crear Plantilla
        </Link>
      </div>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900">

            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
              <div class="text-lg font-semibold">
                Total plantillas: {{ sobres_plantillas.total }}
              </div>

              <input
                v-model="search"
                type="text"
                placeholder="Buscar por nombre del sobre..."
                class="w-full rounded-md border border-gray-300 p-2 sm:max-w-md"
              />
            </div>

            <div class="overflow-x-auto">
              <table class="w-full mt-2">
                <thead>
                  <tr class="border-b">
                    <th class="px-4 py-2 text-left">Nombre</th>
                    <th class="px-4 py-2 text-left">Última modificación</th>
                    <th class="px-4 py-2 text-left">Fecha Creación</th>
                    <th class="px-4 py-2 text-left">Acciones</th>
                  </tr>
                </thead>

                <tbody>
                  <tr v-if="sobres_plantillas.data.length === 0">
                    <td class="px-4 py-4 text-gray-500" colspan="4">
                      No hay resultados.
                    </td>
                  </tr>

                  <tr
                    v-for="sobre_plantilla in sobres_plantillas.data"
                    :key="sobre_plantilla.id"
                    class="border-b"
                  >
                    <td class="px-4 py-2 whitespace-nowrap">
                      <Link
                        :href="route('sobres-plantilla.show', sobre_plantilla.id)"
                        class="text-blue-600 hover:text-blue-800 hover:underline"
                      >
                        {{ sobre_plantilla.nombre_sobre }}
                      </Link>
                    </td>

                    <td class="px-4 py-2 whitespace-nowrap">
                      {{ sobre_plantilla.updated_at }}
                    </td>

                    <td class="px-4 py-2 whitespace-nowrap">
                      {{ sobre_plantilla.created_at }}
                    </td>

                    <td class="px-4 py-2">
                      <div class="flex items-center gap-3">
                        <Link
                          :href="route('sobres-plantilla.edit', sobre_plantilla.id)"
                          class="text-blue-600 hover:text-blue-800 hover:underline"
                        >
                          Editar
                        </Link>

                        <a
                          v-if="(sobre_plantilla.imagenes_count ?? 0) > 0"
                          :href="`/sobres-plantilla/${sobre_plantilla.id}/pdf`"
                          target="_blank"
                          class="inline-flex items-center rounded-md bg-blue-500 px-3 py-1.5 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-blue-700"
                        >
                          PDF
                        </a>

                        <span
                          v-else
                          class="text-xs text-gray-400"
                          title="Este sobre aún no tiene imágenes"
                        >
                          Sin PDF
                        </span>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Paginación -->
            <div class="mt-4">
              <Pagination :links="sobres_plantillas.links" />
            </div>

          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
