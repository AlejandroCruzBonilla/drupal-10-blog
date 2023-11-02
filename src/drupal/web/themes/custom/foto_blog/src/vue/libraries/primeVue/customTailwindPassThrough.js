import { usePassThrough } from "primevue/passthrough/index.esm.js";
// import Tailwind from "primevue/passthrough/tailwind/index.esm.js";
import { global, directives, button, sidebar } from "./passthroughTailwind.js";

//Tailwind customization
const CustomTailwindPassThrough = usePassThrough(
	// Tailwind,
	{
		global,
		directives,
		button,
		sidebar,
	},
	{
		mergeSections: true,
		mergeProps: false,
	}
);

export default CustomTailwindPassThrough;