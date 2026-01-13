<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
  links: {
    type: Array,
    required: true,
  },
})
</script>

<template>
  <div v-if="links.length > 3" class="flex flex-wrap gap-1 justify-center">
    <template v-for="(link, key) in links" :key="key">
      <!-- Link deshabilitado -->
      <span
        v-if="link.url === null"
        class="px-3 py-2 text-sm text-gray-400 border rounded"
        v-html="link.label"
      />

      <!-- Link activo / normal -->
      <Link
        v-else
        :href="link.url"
        preserve-scroll
        preserve-state
        class="px-3 py-2 text-sm border rounded hover:bg-gray-100"
        :class="link.active
          ? 'bg-blue-600 text-white border-blue-600 hover:bg-blue-600'
          : 'text-gray-700'"
        v-html="link.label"
      />
    </template>
  </div>
</template>
