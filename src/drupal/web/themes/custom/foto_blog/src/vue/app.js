/**
 * @file
 */
// ((Drupal, $) => {
((Drupal) => {

	const initVue = () => {
		return new Promise(async (resolve, reject) => {
			try {
				const { createApp, defineAsyncComponent } = await import("vue");

				const { default: fontAwesome } = await import(
					/* webpackChunkName: "fontAwesomeLibrary" */
					"./libraries/fontAwesome/index.js"
				);

				const { default: primeVue } = await import(
					/* webpackChunkName: "primevueLibrary" */
					"./libraries/primeVue/index.js"
				);

				const Test = defineAsyncComponent(() => import('./common/components/test.vue'));

				const app = createApp({
					delimiters: ['${', '}'],
					mounted: () => {
						console.log("Vue mounted");
						// if ($) $.holdReady(false);
					}
				});

				app.use(fontAwesome)
				
				app.use(primeVue,{
					async:{
						defineAsyncComponent
					}
				})

				app.component("Test", Test);
				app.mount("#vue-app");
				resolve(true);
			} catch (error) {
				reject(error);
			}
		});
	}

	// if ($) $.holdReady(true);
	Drupal.behaviors.attachVue = {
		attach: function (context, settings) {

			once('attachVue', 'html').forEach((element) => {
				initVue()
					.catch(error => {
						console.error(error);
						// if ($) $.holdReady(false);
					});
			})
		}
	}

})(Drupal);
// })(Drupal, 'jQuery' in window ? jQuery : null);