import PrimeVue from 'primevue/config/config.esm.js';
import CustomTailwindPassThrough from './customTailwindPassThrough.js';

export default {
	install(app, { async }) {
		const { defineAsyncComponent } = async;
		const Button = defineAsyncComponent(() => import('primevue/button/Button.vue'));
		const Sidebar = defineAsyncComponent(() => import('primevue/sidebar/Sidebar.vue'));

		app.use(PrimeVue, { unstyled: true, pt: CustomTailwindPassThrough });
		app.component('PvButton', Button);
		app.component('PvSidebar', Sidebar);
	}
}