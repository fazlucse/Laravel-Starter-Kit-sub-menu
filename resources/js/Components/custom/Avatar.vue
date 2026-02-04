<template>
  <div class="w-10 h-10 rounded-full overflow-hidden border border-gray-300 dark:border-gray-600">
    <img v-if="src" :src="src" :alt="name" class="w-full h-full object-cover" />
    <div v-else
      class="w-full h-full flex items-center justify-center text-white text-xs font-bold uppercase"
      :class="bgColor"
    >
      {{ initials }}
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{
  src?: string | null
  name: string
}>()

const initials = computed(() => {
  if (!props.name) return '??'
  return props.name
    .trim()
    .split(/\s+/)
    .map(n => n[0])
    .filter(Boolean)
    .slice(0, 2)
    .join('')
    .toUpperCase() || '??'
})

const colors = ['bg-red-500', 'bg-yellow-500', 'bg-green-500', 'bg-blue-500', 'bg-indigo-500', 'bg-purple-500', 'bg-pink-500']
const bgColor = computed(() => colors[Math.abs(hash(props.name) % colors.length)])

function hash(str: string) {
  let h = 0
  for (let i = 0; i < str.length; i++) h = str.charCodeAt(i) + ((h << 5) - h)
  return h
}
</script>