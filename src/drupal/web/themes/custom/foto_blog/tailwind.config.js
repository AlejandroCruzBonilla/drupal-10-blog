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
		"./src/vue/**/*.vue",
		"./src/vue/libraries/primeVue/customTailwindPassThrough.js",
		"./src/vue/libraries/primeVue/passthroughTailwind.js",
		// "./node_modules/primevue/**/*.{vue,js,ts,jsx,tsx}"
	],

	theme: {

		screens: {
			"xs": "480px",
			...defaultTheme.screens,
			"2xl": "1440px",
		},

		extend: {
			screens: {
				"3xl": "1600px",
				"4xl": "1800px",
			},

			colors: {
				primary: {
					light: colors.cyan[400],
					DEFAULT: colors.cyan[500],
					dark: colors.cyan[600],
				},

				secondary: {
					light: colors.emerald[400],
					DEFAULT: colors.emerald[500],
					dark: colors.emerald[600],
				},

				font: {
					light: colors.stone[200],
					dark: colors.stone[800],
				},

				background: {
					light: colors.stone[100],
					dark: colors.stone[900],
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