// FontAwesome
import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';

import {
	faLinkedin,
	faTwitter,
	faGithub,
} from '@fortawesome/free-brands-svg-icons';

import {
	faGripLines,
	faPhone,
	faUser,
	faCircleUser,
	faXmark,
} from '@fortawesome/free-solid-svg-icons';


library.add(
	faGripLines,
	faLinkedin,
	faPhone,
	faTwitter,
	faGithub,
	faUser,
	faCircleUser,
	faXmark,
);

export default {
	install(app) {
		app.component('FontAwesomeIcon', FontAwesomeIcon);
	}
}

