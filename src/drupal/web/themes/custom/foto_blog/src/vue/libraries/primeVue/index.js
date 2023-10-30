// import { defineAsyncComponent } from "vue";
;
// import PrimeVue from 'primevue/config/config.esm.js';
// import CustomTailwindPassThrough from './customTailwindPassThrough.js'

//Components

// import Button from 'primevue/button/Button.vue';
// import Sidebar from 'primevue/sidebar/Sidebar.vue';

// const Button = defineAsyncComponent(()=>import('primevue/button/Button.vue'));
// const Sidebar = defineAsyncComponent( ()=>import('primevue/sidebar/Sidebar.vue'));

export default async (app) => {
	return new Promise(async (resolve, reject) => {
		try {
			const { defineAsyncComponent } = await import("vue");
			const PrimeVue = await import('primevue/config/config.esm.js');
			const CustomTailwindPassThrough = await import('./customTailwindPassThrough.js');

			const Button = defineAsyncComponent(() => import('primevue/button/Button.vue'));
			const Sidebar = defineAsyncComponent(() => import('primevue/sidebar/Sidebar.vue'));

			console.log({CustomTailwindPassThrough})

			app.use(PrimeVue, { unstyled: true, pt: CustomTailwindPassThrough.default });
			app.component('PvButton', Button);
			app.component('PvSidebar', Sidebar);

			resolve(true);
			
		} catch (error) {
			reject(error);
		}
	});
}