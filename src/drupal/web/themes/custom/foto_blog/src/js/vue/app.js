import { createApp } from "vue";

import FontAwesomeIcon from "./libraries/fontAwesome.js";

import Page from "./pages/page.vue";

import Debug from "./components/__debug.vue";

//VueApp
const appDocument = document.querySelector("#vue-app");

if (appDocument) {
	const app = createApp({
		delimiters: ['${', '}']
	});

	//FontAwesome
	app.component("FontAwesomeIcon", FontAwesomeIcon);

	//Own
	app.component("DebugComponent", Debug);
	app.component("VuePage", Page);


	app.mount(appDocument);
}