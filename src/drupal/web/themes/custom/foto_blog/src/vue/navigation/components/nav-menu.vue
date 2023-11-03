<script setup>
import { ref } from 'vue';

const visible = ref(false);

const toggleMenu = () => {
	visible.value = !visible.value
}
</script>

<template>
	<div class="lg:hidden flex items-center">
		<pv-button
			outlined
			@click="toggleMenu"
		>
			<font-awesome-icon
				:icon="visible
					? 'fa-solid fa-xmark'
					: 'fa-solid fa-grip-lines'"
			></font-awesome-icon>
		</pv-button>

	</div>
	<pv-sidebar
		position="top"
		v-model:visible="visible"
	>
		<template #header>
			<slot name="sidebar-header"></slot>
		</template>
		<slot></slot>
	</pv-sidebar>
</template>
