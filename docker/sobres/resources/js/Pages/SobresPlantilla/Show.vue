<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import { ref, onMounted } from 'vue'
import axios from 'axios'
import draggable from 'vuedraggable'
import { useToast } from "vue-toastification";

const toast = useToast();

// límites
const MAX_MB = 6; // mismo que tu backend (max:4096 => 4MB)
const MAX_BYTES = MAX_MB * 1024 * 1024;

// extensiones permitidas (alineadas con backend)
const ALLOWED_EXT = ["jpg", "jpeg", "png", "gif"];
const ALLOWED_MIME = ["image/jpeg", "image/png", "image/gif"];

const props = defineProps({
  sobre: Object,
})

const showModal = ref(false)
const selectedFiles = ref([])
const imagenes = ref([])
const selectedImages = ref([])
const showEditModal = ref(false)
const editImageData = ref({ id: null, title: '', newFile: null })
//para la carga
const isUploading = ref(false)
const isUpdating = ref(false)
const uploadProgress = ref(0)

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
  const files = Array.from(event.target.files || []);
  if (files.length === 0) return;

  const rejected = [];

  const validFiles = files.filter((file) => {
    const ext = (file.name.split(".").pop() || "").toLowerCase();
    const okExt = ALLOWED_EXT.includes(ext);
    const okMime = ALLOWED_MIME.includes(file.type);
    const okSize = file.size <= MAX_BYTES;

    if (!okExt || !okMime || !okSize) {
      rejected.push({
        name: file.name,
        reason: !okSize
          ? `Supera ${MAX_MB}MB`
          : `Extensión no permitida`,
      });
      return false;
    }
    return true;
  });

  // si hay rechazadas, toast
  if (rejected.length > 0) {
    toast.warning(
      `Algunas imágenes no se cargaron.\nMáx: ${MAX_MB}MB | Permitidas: ${ALLOWED_EXT.join(", ")}`
    );

    // opcional: mostrar detalle corto
    const preview = rejected.slice(0, 3).map(r => `• ${r.name} (${r.reason})`).join("\n");
    toast.info(rejected.length > 3 ? `${preview}\n...` : preview);
  }

  // si no quedó ninguna válida
  if (validFiles.length === 0) {
    // limpiar input para permitir re-selección del mismo archivo
    event.target.value = "";
    return;
  }

  // guardar válidas
  selectedFiles.value = validFiles;

  toast.success(
    `${validFiles.length} imagen(es) listas para subir. Máx: ${MAX_MB}MB | ${ALLOWED_EXT.join(", ")}`
  );
};


const uploadImages = () => {
  if (!props.sobre?.id) return
  if (selectedFiles.value.length === 0) {
    toast.warning("Selecciona al menos una imagen.")
    return
  }

  isUploading.value = true
  uploadProgress.value = 0
  toast.info("Subiendo imágenes...")

  const formData = new FormData()
  selectedFiles.value.forEach((file, index) => {
    formData.append(`images[${index}]`, file)
  })
  formData.append('sobre_plantilla_id', props.sobre.id)

  axios.post('/api/registro-imagenes', formData, {
    headers: { 'Content-Type': 'multipart/form-data' },
    onUploadProgress: (progressEvent) => {
      if (!progressEvent.total) return
      uploadProgress.value = Math.round((progressEvent.loaded * 100) / progressEvent.total)
    },
  })
  .then(() => {
    toast.success("Imágenes subidas con éxito.")
    showModal.value = false
    selectedFiles.value = []
    fetchImages()
  })
  .catch((err) => {
    const msg =
      err?.response?.data?.message ||
      (err?.response?.data?.errors ? "Error de validación al subir imágenes." : "Error subiendo imágenes.")
    toast.error(msg)
  })
  .finally(() => {
    isUploading.value = false
    uploadProgress.value = 0
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
const editFileInputRef = ref(null);
// Reemplazar una imagen
const openEditModal = (img) => {
  editImageData.value = {
    id: img.id,
    title: img.title ?? "",
    newFile: null,
  };

  showEditModal.value = true;

  // reset del input file
  setTimeout(() => {
    if (editFileInputRef.value) editFileInputRef.value.value = "";
  }, 0);
};





const handleFileUpdate = (event) => {
  const file = event.target.files?.[0] ?? null;
  if (!file) {
    editImageData.value.newFile = null;
    return;
  }

  const ext = (file.name.split(".").pop() || "").toLowerCase();
  const okExt = ALLOWED_EXT.includes(ext);
  const okMime = ALLOWED_MIME.includes(file.type);
  const okSize = file.size <= MAX_BYTES;

  if (!okSize) {
    toast.error(`La imagen supera el máximo permitido: ${MAX_MB}MB.`);
    editImageData.value.newFile = null;
    event.target.value = "";
    return;
  }

  if (!okExt || !okMime) {
    toast.error(`Extensión no permitida. Permitidas: ${ALLOWED_EXT.join(", ")}.`);
    editImageData.value.newFile = null;
    event.target.value = "";
    return;
  }

  editImageData.value.newFile = file;
  toast.info(`Nueva imagen lista: ${file.name}`);
};


const updateImage = () => {
  if (!editImageData.value.id) return;

  const title = (editImageData.value.title || "").trim();
  if (!title) {
    toast.warning("El título es obligatorio.");
    return;
  }

  // Si no cambió nada (opcional)
  if (!editImageData.value.newFile && title === (imagenes.value.find(i => i.id === editImageData.value.id)?.title ?? "").trim()) {
    toast.info("No hay cambios para guardar.");
    return;
  }

  const formData = new FormData();
  formData.append("title", title);

  if (editImageData.value.newFile) {
    formData.append("image", editImageData.value.newFile);
  }

    isUpdating.value = true
    toast.info("Guardando cambios...")

    axios.post(`/api/registro-imagenes/update/${editImageData.value.id}`, formData, {
    headers: { "Content-Type": "multipart/form-data" },
    })
    .then(() => {
    toast.success("Imagen actualizada con éxito.")
    showEditModal.value = false
    editImageData.value = { id: null, title: "", newFile: null }
    fetchImages()
    })
    .catch((err) => {
    const msg =
        err?.response?.data?.message ||
        (err?.response?.data?.errors ? "Error de validación al actualizar." : "Error actualizando imagen.")
    toast.error(msg)
    })
    .finally(() => {
    isUpdating.value = false
    })

};

//Limpiar input de editar (reeemplazar imagen)

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
          <a v-if="imagenes.length > 0" :href="`/sobres-plantilla/${sobre.id}/pdf`" target="_blank" class="inline-flex items-center px-4 py-2 bg-blue-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                PDF
          </a>
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
                    @click="openEditModal(element)"
                    class="inline-flex items-center rounded-md bg-blue-600 px-3 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-blue-700"
                    >
                    Editar
                    </button>
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
      <div class="relative bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
        <h3 class="text-lg font-semibold mb-4">Subir Imágenes</h3>

        <input type="file" multiple @change="handleFileUpload" class="mb-4 w-full">
        <p class="text-sm text-gray-600 mb-3">
            Máximo: <b>{{ MAX_MB }}MB</b> por imagen. Formatos: <b>{{ ALLOWED_EXT.join(', ') }}</b>.
        </p>
            <div class="flex justify-end">
            <button
                @click="showModal = false"
                class="inline-flex items-center px-4 py-2 bg-gray-500 rounded-md text-xs font-semibold uppercase tracking-widest text-white hover:bg-gray-700"
            >
                Cancelar
            </button>

                <button
                @click="uploadImages"
                :disabled="isUploading"
                class="ml-3 inline-flex items-center px-4 py-2 bg-blue-600 rounded-md text-xs font-semibold uppercase tracking-widest text-white hover:bg-blue-700 disabled:opacity-60 disabled:cursor-not-allowed"
                >
                <span v-if="isUploading">Subiendo...</span>
                <span v-else>Subir</span>
                </button>
            </div>

            <!-- Overlay Loading Upload -->
            <div
            v-if="isUploading"
            class="absolute inset-0 flex flex-col items-center justify-center bg-white/80 rounded-lg"
            >
            <div class="animate-spin h-10 w-10 rounded-full border-4 border-gray-300 border-t-gray-700"></div>
            <div class="mt-3 text-sm font-semibold text-gray-700">
                Subiendo... {{ uploadProgress }}%
            </div>

            <div class="mt-3 w-3/4 bg-gray-200 rounded-full h-2">
                <div
                class="bg-blue-600 h-2 rounded-full transition-all"
                :style="{ width: uploadProgress + '%' }"
                ></div>
            </div>
            </div>

      </div>
    </div>

    <!-- Modal editar/reemplazar -->
    <div v-if="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
    <div class="relative bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
        <h3 class="text-lg font-semibold mb-4">Editar Imagen</h3>

        <label class="block text-sm font-medium text-gray-700">Título</label>
        <input
        v-model="editImageData.title"
        type="text"
        class="mt-1 w-full rounded-md border border-gray-300 p-2 mb-3"
        placeholder="Nuevo título"
        >

        <label class="block text-sm font-medium text-gray-700">Reemplazar imagen (opcional)</label>
        <input ref="editFileInputRef" type="file" @change="handleFileUpdate" class="mt-1 w-full mb-4">
        <p class="text-sm text-gray-600 mb-3">
            Máximo: <b>{{ MAX_MB }}MB</b> por imagen. Formatos: <b>{{ ALLOWED_EXT.join(', ') }}</b>.
        </p>
            <div class="flex justify-end">
                <button
                    @click="showEditModal = false"
                    class="inline-flex items-center px-4 py-2 bg-gray-500 rounded-md text-xs font-semibold uppercase tracking-widest text-white hover:bg-gray-700"
                >
                    Cancelar
                </button>

                <button
                @click="updateImage"
                :disabled="isUpdating"
                class="ml-3 inline-flex items-center px-4 py-2 bg-blue-600 rounded-md text-xs font-semibold uppercase tracking-widest text-white hover:bg-blue-700 disabled:opacity-60 disabled:cursor-not-allowed"
                >
                <span v-if="isUpdating">Guardando...</span>
                <span v-else>Guardar</span>
                </button>

            </div>
            <!-- Overlay Loading Update -->
            <div
            v-if="isUpdating"
            class="absolute inset-0 flex flex-col items-center justify-center bg-white/80 rounded-lg"
            >
            <div class="animate-spin h-10 w-10 rounded-full border-4 border-gray-300 border-t-gray-700"></div>
            <div class="mt-3 text-sm font-semibold text-gray-700">
                Guardando cambios...
            </div>
            </div>

    </div>
    </div>



  </AuthenticatedLayout>
</template>
