<template>
  <div class="p-4 bg-gray-50 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700">
    <nav class="flex flex-wrap justify-center gap-1" aria-label="Pagination">
      <!-- Previous -->
      <template v-if="links[0]?.url">
        <Link :href="links[0].url" v-html="links[0].label" preserve-scroll class="px-3 py-2 text-sm rounded-md border border-gray-300 bg-white text-gray-500 hover:bg-gray-100 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-400 dark:hover:bg-gray-700" />
      </template>
      <template v-else>
        <span v-html="links[0]?.label" class="px-3 py-2 text-sm rounded-md border border-gray-300 bg-white text-gray-300 cursor-not-allowed dark:bg-gray-800 dark:border-gray-600 dark:text-gray-600" />
      </template>

      <!-- Pages -->
      <Link
        v-for="link in links.slice(1, -1)"
        :key="link.label"
        :href="link.url || '#'"
        v-html="link.label"
        preserve-scroll
        :class="[
          'px-3 py-2 text-sm rounded-md border',
          link.active
            ? 'bg-blue-600 border-blue-600 text-white font-medium'
            : 'bg-white border-gray-300 text-gray-700 hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700'
        ]"
      />

      <!-- Next -->
      <template v-if="links[links.length - 1]?.url">
        <Link :href="links[links.length - 1].url" v-html="links[links.length - 1].label" preserve-scroll class="px-3 py-2 text-sm rounded-md border border-gray-300 bg-white text-gray-500 hover:bg-gray-100 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-400 dark:hover:bg-gray-700" />
      </template>
      <template v-else>
        <span v-html="links[links.length - 1]?.label" class="px-3 py-2 text-sm rounded-md border border-gray-300 bg-white text-gray-300 cursor-not-allowed dark:bg-gray-800 dark:border-gray-600 dark:text-gray-600" />
      </template>
    </nav>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  links: Array<{ url: string | null; label: string; active: boolean }>
}>()
import { Link } from '@inertiajs/vue3'
</script>