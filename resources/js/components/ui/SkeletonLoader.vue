<template>
  <div
    class="skeleton"
    :style="{
      width: width,
      height: height,
      borderRadius: borderRadius,
      flexShrink: 0,
    }"
  ></div>
</template>

<script setup>
defineProps({
  width:        { type: String, default: '100%' },
  height:       { type: String, default: '1rem' },
  borderRadius: { type: String, default: '0.5rem' },
})
</script>
