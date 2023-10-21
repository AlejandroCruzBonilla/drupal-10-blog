// import PrimeVue from 'primevue/config/config.js';
import PrimeVue from 'primevue/config/config.esm.js';
import CustomTailwindPassThrough from './customTailwindPassThrough.js'

//Components
import Button from 'primevue/button/Button.vue';

export default (app) => {
	app.use(PrimeVue, { unstyled: true, pt: CustomTailwindPassThrough });
	// app.use(PrimeVue, { unstyled: true});
	app.component('PvButton', Button);
}