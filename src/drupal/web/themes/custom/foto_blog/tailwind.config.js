import defaultTheme from "tailwindcss/defaultTheme";
import colors from "tailwindcss/colors";
import forms from "@tailwindcss/forms";


/** @type {import('tailwindcss').Config} */
export default {
	prefix: 'tw-',
	// important: true,
	darkMode: 'class',

	content: [
		"**/*.html.twig",
		"./src/js/**/*.vue"
	],

	// theme: {
	// 	extend: {
	// 		colors: {
	// 			font: {
	// 				DEFAULT: colors.neutral[950],
	// 				dark: colors.neutral[950],
	// 				light: colors.neutral[100],
	// 				'on-bg-light': colors.neutral[950],
	// 				'on-bg-dark': colors.neutral[100],
	// 				'on-accent-light': colors.neutral[950],
	// 				'on-accent-dark': colors.neutral[100],
	// 			},

	// 			background: {
	// 				dark: colors.neutral,
	// 				light: colors.neutral,
	// 			},

	// 			primary: {
	// 				dark: colors.neutral,
	// 				light: colors.neutral,
	// 			},

	// 			accent: colors.teal,
	// 		},

	// 		fontFamily: {
	// 			sans: ["Figtree", ...defaultTheme.fontFamily.sans],
	// 		},
	// 		screens: {
	// 			xs: "320px",
	// 			sm: "640px",
	// 			md: "768px",
	// 			lg: "1024px",
	// 			xl: "1280px",
	// 			"2xl": "1440px",
	// 		},
	// 	},
	// },
	plugins: [
		forms({
			strategy: "base", // only generate global styles
			// strategy: "class", // only generate classes
		}),
	],
};

