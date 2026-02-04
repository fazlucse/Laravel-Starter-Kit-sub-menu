<template>
  <span
    class="inline-block px-2 py-0.5 rounded text-xs font-medium ml-2"
    :class="active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
  >
    <slot />
  </span>
</template>

<script setup>
defineProps(['active'])
</script>