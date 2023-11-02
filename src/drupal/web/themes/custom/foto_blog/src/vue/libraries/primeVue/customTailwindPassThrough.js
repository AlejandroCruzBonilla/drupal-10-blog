import { usePassThrough } from "primevue/passthrough/index.esm.js";
// import Tailwind from "primevue/passthrough/tailwind/index.esm.js";
import { global, directives, button } from "./passthroughTailwind.js";

//Tailwind customization
const CustomTailwindPassThrough = usePassThrough(
	// Tailwind,
	{
		global,
		directives,
		button,
	},
	{
		mergeSections: true,
		mergeProps: false,
	}
);

export default CustomTailwindPassThrough;