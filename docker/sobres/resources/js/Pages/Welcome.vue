<script setup>
import { Head, Link } from '@inertiajs/vue3'

defineProps({
  canLogin: Boolean,
  canRegister: Boolean,
  laravelVersion: { type: String, required: true },
  phpVersion: { type: String, required: true },
})

const POS_URL = 'https://pos.kyravaluos.com'
</script>

<template>
  <Head title="Inicio" />

  <div class="min-h-screen bg-gray-50 text-gray-900 dark:bg-zinc-950 dark:text-zinc-100">
    <!-- Top bar -->
    <header
      class="border-b border-gray-200 bg-white/70 backdrop-blur dark:border-zinc-800 dark:bg-zinc-900/40"
    >
      <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-4">
        <div class="flex items-center gap-3">
          <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#FF2D20]/10">
            <span class="font-bold text-[#FF2D20]">S</span>
          </div>
          <div class="leading-tight">
            <p class="font-semibold">Sobres</p>
            <p class="text-xs text-gray-500 dark:text-zinc-400">
              Subir imágenes → generar PDF
            </p>
          </div>
        </div>

        <nav v-if="canLogin" class="flex flex-wrap items-center gap-2">
          <a
            :href="POS_URL"
            target="_blank"
            rel="noopener noreferrer"
            class="rounded-lg px-4 py-2 text-sm font-medium ring-1 ring-gray-200 transition hover:bg-gray-100 dark:ring-zinc-700 dark:hover:bg-zinc-800"
          >
            Ir a POS
          </a>

          <Link
            v-if="$page.props.auth.user"
            :href="route('dashboard')"
            class="rounded-lg bg-[#FF2D20] px-4 py-2 text-sm font-semibold text-white transition hover:opacity-90"
          >
            Dashboard
          </Link>

          <template v-else>
            <Link
              :href="route('login')"
              class="rounded-lg px-4 py-2 text-sm font-medium ring-1 ring-gray-200 transition hover:bg-gray-100 dark:ring-zinc-700 dark:hover:bg-zinc-800"
            >
              Iniciar sesión
            </Link>

            <Link
              v-if="canRegister"
              :href="route('register')"
              class="rounded-lg bg-[#FF2D20] px-4 py-2 text-sm font-semibold text-white transition hover:opacity-90"
            >
              Crear cuenta
            </Link>
          </template>
        </nav>
      </div>
    </header>

    <!-- Hero -->
    <main class="mx-auto max-w-6xl px-6 py-14">
      <div class="grid gap-10 lg:grid-cols-2 lg:items-center">
        <div>
          <h1 class="text-3xl font-semibold tracking-tight sm:text-4xl">
            Bienvenido a Sobres 👋
          </h1>

          <p class="mt-4 text-base leading-relaxed text-gray-600 dark:text-zinc-300">
            Sube tus imágenes, elige una plantilla y descarga tu PDF listo.
          </p>

          <div class="mt-8 flex flex-wrap gap-3">
            <Link
              v-if="$page.props.auth.user"
              :href="route('dashboard')"
              class="rounded-xl bg-[#FF2D20] px-5 py-3 text-sm font-semibold text-white transition hover:opacity-90"
            >
              Ir al Dashboard
            </Link>

            <!-- Plantillas (solo si está logueado) -->
            <Link
              v-if="$page.props.auth.user"
              :href="route('plantillas.index')"
              class="rounded-xl px-5 py-3 text-sm font-semibold ring-1 ring-gray-200 transition hover:bg-gray-100 dark:ring-zinc-700 dark:hover:bg-zinc-800"
            >
              Plantillas
            </Link>

            <!-- POS externo -->
            <a
              :href="POS_URL"
              target="_blank"
              rel="noopener noreferrer"
              class="rounded-xl px-5 py-3 text-sm font-semibold ring-1 ring-gray-200 transition hover:bg-gray-100 dark:ring-zinc-700 dark:hover:bg-zinc-800"
            >
              Abrir POS
            </a>

            <template v-if="!$page.props.auth.user">
              <Link
                :href="route('login')"
                class="rounded-xl px-5 py-3 text-sm font-semibold ring-1 ring-gray-200 transition hover:bg-gray-100 dark:ring-zinc-700 dark:hover:bg-zinc-800"
              >
                Iniciar sesión
              </Link>
            </template>
          </div>

          <div class="mt-10 grid gap-4 sm:grid-cols-2">
            <div
              class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200 dark:bg-zinc-900 dark:ring-zinc-800"
            >
              <p class="text-sm font-semibold">Flujo principal</p>
              <p class="mt-1 text-sm text-gray-600 dark:text-zinc-300">
                1) Subir imágenes · 2) Elegir plantilla · 3) Descargar PDF
              </p>
            </div>

            <div
              class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200 dark:bg-zinc-900 dark:ring-zinc-800"
            >
              <p class="text-sm font-semibold">Sistema</p>
              <p class="mt-1 text-sm text-gray-600 dark:text-zinc-300">
                Laravel v{{ laravelVersion }} · PHP v{{ phpVersion }}
              </p>
            </div>
          </div>
        </div>

        <!-- Right card -->
        <div
          class="rounded-3xl bg-white p-8 shadow-[0_18px_50px_rgba(0,0,0,0.08)] ring-1 ring-gray-200 dark:bg-zinc-900 dark:ring-zinc-800"
        >
          <h2 class="text-lg font-semibold">Accesos rápidos</h2>

          <div class="mt-6 space-y-3">
            <Link
              v-if="$page.props.auth.user"
              :href="route('dashboard')"
              class="block rounded-2xl bg-gray-50 p-4 ring-1 ring-gray-200 transition hover:bg-gray-100 dark:bg-zinc-950 dark:ring-zinc-800 dark:hover:bg-zinc-900"
            >
              <p class="text-sm font-semibold">Dashboard</p>
              <p class="mt-1 text-sm text-gray-600 dark:text-zinc-300">
                Subir imágenes y generar el PDF.
              </p>
            </Link>

            <Link
              v-if="$page.props.auth.user"
              :href="route('plantillas.index')"
              class="block rounded-2xl bg-gray-50 p-4 ring-1 ring-gray-200 transition hover:bg-gray-100 dark:bg-zinc-950 dark:ring-zinc-800 dark:hover:bg-zinc-900"
            >
              <p class="text-sm font-semibold">Plantillas</p>
              <p class="mt-1 text-sm text-gray-600 dark:text-zinc-300">
                Administra diseños adicionales para tus PDFs.
              </p>
            </Link>

            <a
              :href="POS_URL"
              target="_blank"
              rel="noopener noreferrer"
              class="block rounded-2xl bg-gray-50 p-4 ring-1 ring-gray-200 transition hover:bg-gray-100 dark:bg-zinc-950 dark:ring-zinc-800 dark:hover:bg-zinc-900"
            >
              <p class="text-sm font-semibold">POS (externo)</p>
              <p class="mt-1 text-sm text-gray-600 dark:text-zinc-300">
                Abrir pos.kyravaluos.com en una nueva pestaña.
              </p>
            </a>

            <div
              v-if="!$page.props.auth.user"
              class="rounded-2xl bg-gray-50 p-4 ring-1 ring-gray-200 dark:bg-zinc-950 dark:ring-zinc-800"
            >
              <p class="text-sm font-semibold">Tip</p>
              <p class="mt-1 text-sm text-gray-600 dark:text-zinc-300">
                Inicia sesión para acceder al dashboard y a plantillas.
              </p>
            </div>
          </div>
        </div>
      </div>
    </main>

    <footer class="py-10 text-center text-sm text-gray-500 dark:text-zinc-400">
      © {{ new Date().getFullYear() }} Sobres
    </footer>
  </div>
</template>
