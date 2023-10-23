import { usePassThrough } from "primevue/passthrough/index.esm.js";
// import Tailwind from "primevue/passthrough/tailwind/index.esm.js";


//Tailwind customization

const CustomTailwindPassThrough = usePassThrough(
	{
		button: {
			root: ({ props, context }) => ({
				class: [
					'btn',
				]
			}),
		},
	},
	{
		mergeSections: true,
		mergeProps: false,
	}
);

export default CustomTailwindPassThrough;