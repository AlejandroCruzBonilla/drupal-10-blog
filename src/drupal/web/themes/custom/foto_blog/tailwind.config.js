import plugin from 'tailwindcss/plugin';
import defaultTheme from "tailwindcss/defaultTheme";
import colors from "tailwindcss/colors";
import forms from "@tailwindcss/forms";



console.log('Tailwind-config');
// console.log(defaultTheme.fontSize)

export default {
	// prefix: 'tw-',
	important: true,
	darkMode: 'class',

	content: [
		"./templates/**/*.html.twig",
		"./src/js/**/*.vue",
		"./node_modules/primevue/**/*.{vue,js,ts,jsx,tsx}"
	],

	theme: {

		screens: {
			"xs": "480px",
			...defaultTheme.screens,
			"2xl": "1440px",
		},

		plugins: [
			forms({
				strategy: "base", // only generate global styles
				// strategy: "class", // only generate classes
			}),
		],
	}
}