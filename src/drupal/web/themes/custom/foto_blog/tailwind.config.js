import plugin from 'tailwindcss/plugin';
import defaultTheme from "tailwindcss/defaultTheme";
import colors from "tailwindcss/colors";
import forms from "@tailwindcss/forms";
import typography from "@tailwindcss/typography";



console.log('Tailwind-config');
// console.log(defaultTheme.fontSize)

export default {
	// prefix: 'tw-',
	// important: true,
	// darkMode: 'class',

	content: [
		"./templates/**/*.html.twig",
		"./layouts/**/*.html.twig",
		"./src/js/**/*.vue",
		"./src/js/vue/libraries/primeVue/customTailwindPassThrough.js",
		// "./node_modules/primevue/**/*.{vue,js,ts,jsx,tsx}"
	],

	theme: {

		screens: {
			"xs": "480px",
			...defaultTheme.screens,
			"2xl": "1440px",
		},

		extend: {
			screens:{
				"3xl": "1600px",
			},

			colors: {
				primary: {
					light: colors.red[300],
					DEFAULT: colors.red[500],
					dark: colors.red[700],
				},

				secondary: {
					light: colors.cyan[300],
					DEFAULT: colors.cyan[500],
					dark: colors.cyan[700],
				},

				background: {
					light: colors.gray[200],
					dark: colors.gray[900],
				},
			},
		},

		plugins: [
			typography(),
			forms({
				strategy: "base", // only generate global styles
				// strategy: "class", // only generate classes
			}),
		],
	}
}