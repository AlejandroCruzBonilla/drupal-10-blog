// (($) => {

// 	window.onload = (event) => {
// 		console.log("page is fully loaded");
// 	};

// 	document.addEventListener("DOMContentLoaded", () => {
// 		console.log("DOMContentLoaded");
// 	})

// 	$(document).ready(() => {
// 		console.log("Jquery Ready");
// 	})
// })(jQuery);

/**
 * @file
 */
import { createApp, defineAsyncComponent } from "vue";
(($, Drupal) => {
	$.holdReady(true);
	Drupal.behaviors.attachVue = {
		attach: function (context, settings) {
			// const initVue = () => {
			// 	return new Promise(async (resolve, reject) => {
			// 		try {
			// 			const { createApp, defineAsyncComponent } = await import("vue");
			// 			// const FontAwesomeIcon = defineAsyncComponent(() => import("./libraries/fontAwesome/index.js"));


			// 			// const { default: PrimeVue } = await import('primevue/config/config.esm.js');
			// 			// const { default: CustomTailwindPassThrough } = await import('./libraries/primeVue/customTailwindPassThrough.js');

			// 			// const Button = defineAsyncComponent(() => import('primevue/button/Button.vue'));
			// 			// const Sidebar = defineAsyncComponent(() => import('primevue/sidebar/Sidebar.vue'));

			// 			const Page = defineAsyncComponent(() => import('./pages/page.vue'));

			// 			const app = createApp({
			// 				delimiters: ['${', '}'],
			// 				mounted: () => {
			// 					console.log("Vue mounted");
			// 					$.holdReady(false)
			// 					console.log(context.body.querySelector('#edit-tid-raw-1--2'), "----2");
			// 				}
			// 			});

			// 			// app.component('FontAwesomeIcon', FontAwesomeIcon);
			// 			// app.use(PrimeVue, { unstyled: true, pt: CustomTailwindPassThrough });
			// 			// app.component('PvButton', Button);
			// 			// app.component('PvSidebar', Sidebar);

			// 			app.component("VuePage", Page);
			// 			app.mount("#vue-app");
			// 			resolve(true);
			// 		} catch (error) {
			// 			reject(error);
			// 		}
			// 	});
			// }

			once('attachVue', 'html').forEach((element) => {

				const Page = defineAsyncComponent(() => import('./pages/page.vue'));
	
				const app = createApp({
					delimiters: ['${', '}'],
					mounted: () => {
						console.log("Vue mounted");
						$.holdReady(false)
					}
				});
				app.component("VuePage", Page);
				app.mount("#vue-app");
			})


			// initVue()
			// 	.then(console.log)
			// 	.catch(console.error);
			// })
		}
	}

})(jQuery, Drupal);




