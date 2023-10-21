import { usePassThrough } from "primevue/passthrough/index.esm.js";
import Tailwind from "primevue/passthrough/tailwind/index.esm.js";
// import PrefixTailwind from "./primeVue-passthrough-tailwind-prefix.js";

//Tailwind customization

const CustomTailwindPassThrough = usePassThrough(
	Tailwind,
	{},
	{
		mergeSections: true,
		mergeProps: false,
	}
);

export default CustomTailwindPassThrough;