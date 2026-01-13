<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import { ref, onMounted } from 'vue'
import axios from 'axios'
import draggable from 'vuedraggable'

const props = defineProps({
  sobre: Object,
})

const showModal = ref(false)
const selectedFiles = ref([])
const imagenes = ref([])
const selectedImages = ref([])

onMounted(() => {
  fetchImages()
})

const fetchImages = () => {
  axios.get(`/api/sobres-plantilla/${props.sobre.id}/imagenes`)
    .then(res => {
      imagenes.value = Array.isArray(res.data) ? res.data : []
    })
    .catch(() => {
      imagenes.value = []
    })
}

const handleFileUpload = (event) => {
  selectedFiles.value = Array.from(event.target.files || [])
}

const uploadImages = () => {
  if (!props.sobre?.id) return

  const formData = new FormData()
  selectedFiles.value.forEach((file, index) => {
    formData.append(`images[${index}]`, file)
  })
  formData.append('sobre_plantilla_id', props.sobre.id)

  axios.post('/api/registro-imagenes', formData, {
    headers: { 'Content-Type': 'multipart/form-data' },
  })
  .then(() => {
    showModal.value = false
    selectedFiles.value = []
    fetchImages()
  })
}

const updateOrder = () => {
  const orders = imagenes.value.map((img, index) => ({
    id: img.id,
    order: index + 1,
  }))

  axios.post('/api/registro-imagenes/update-order', { orders })
    .then(() => fetchImages())
}

const deleteImage = (id) => {
  if (!confirm('¿Eliminar esta imagen?')) return
  axios.delete(`/api/registro-imagenes/${id}`)
    .then(() => fetchImages())
}

const deleteSelectedImages = () => {
  if (selectedImages.value.length === 0) return
  if (!confirm('¿Eliminar imágenes seleccionadas?')) return

  axios.post('/api/registro-imagenes/delete-multiple', { ids: selectedImages.value }, {
    headers: { 'Content-Type': 'application/json' }
  })
  .then(() => {
    selectedImages.value = []
    fetchImages()
  })
}
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

        <!-- Info -->
        <div class="mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900 space-y-2">
            <div><span class="font-semibold">Nombre:</span> {{ sobre.nombre_sobre }}</div>
            <div><span class="font-semibold">Fecha creación:</span> {{ sobre.created_at }}</div>
            <div><span class="font-semibold">Última modificación:</span> {{ sobre.updated_at }}</div>
          </div>
        </div>

        <!-- Acciones -->
        <div class="mb-6 flex items-center justify-between">
          <button
            @click="showModal = true"
            class="inline-flex items-center rounded-md bg-green-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-green-700"
          >
            Añadir imágenes
          </button>

          <button
            v-if="selectedImages.length > 0"
            @click="deleteSelectedImages"
            class="inline-flex items-center rounded-md bg-red-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-red-700"
          >
            Eliminar seleccionadas ({{ selectedImages.length }})
          </button>
        </div>

        <!-- Grid draggable -->
        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900">
            <h3 class="mb-4 text-lg font-semibold">Imágenes</h3>

            <div v-if="imagenes.length === 0" class="text-gray-500">
              No hay imágenes asociadas.
            </div>

            <draggable
              v-else
              :list="imagenes"
              item-key="id"
              @end="updateOrder"
              class="grid grid-cols-1 md:grid-cols-2 gap-6"
            >
              <template #item="{ element }">
                <div class="bg-white shadow-md rounded-lg p-4 border flex flex-col items-center">
                  <input type="checkbox" v-model="selectedImages" :value="element.id" class="mb-2">

                  <img
                    :src="`/storage/${element.imagen}`"
                    :alt="element.title"
                    class="w-full h-40 object-cover rounded-lg"
                  >

                  <div class="mt-3 text-center">
                    <p class="font-semibold text-gray-800">{{ element.title }}</p>
                    <p class="text-sm text-gray-600">Orden: {{ element.orden }}</p>
                  </div>

                  <div class="mt-3 flex gap-3">
                    <button
                      @click="deleteImage(element.id)"
                      class="inline-flex items-center rounded-md bg-red-600 px-3 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-red-700"
                    >
                      Eliminar
                    </button>
                  </div>
                </div>
              </template>
            </draggable>
          </div>
        </div>

      </div>
    </div>

    <!-- Modal upload -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
      <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
        <h3 class="text-lg font-semibold mb-4">Subir Imágenes</h3>

        <input type="file" multiple @change="handleFileUpload" class="mb-4 w-full">

        <div class="flex justify-end">
          <button
            @click="showModal = false"
            class="inline-flex items-center px-4 py-2 bg-gray-500 rounded-md text-xs font-semibold uppercase tracking-widest text-white hover:bg-gray-700"
          >
            Cancelar
          </button>

          <button
            @click="uploadImages"
            class="ml-3 inline-flex items-center px-4 py-2 bg-blue-600 rounded-md text-xs font-semibold uppercase tracking-widest text-white hover:bg-blue-700"
          >
            Subir
          </button>
        </div>
      </div>
    </div>

  </AuthenticatedLayout>
</template>
