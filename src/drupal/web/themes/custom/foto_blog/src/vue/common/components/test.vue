<script setup>
import { onMounted } from 'vue';

onMounted(() => {
	console.log('Test Mounted');
})
</script>
<template>
	<slot></slot>
</template>
