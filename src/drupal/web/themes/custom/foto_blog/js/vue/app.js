import { createApp } from "vue";

import FontAwesomeIcon from "./libraries/fontAwesome";

import Page from "./components/Page";

import Debug from "./components/Debug";

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