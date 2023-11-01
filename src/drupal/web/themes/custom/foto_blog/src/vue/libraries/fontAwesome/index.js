// FontAwesome
import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';

import {
	faFacebookF,
	faInstagram,
	faLinkedin,
	faTiktok,
	faTwitter,
} from '@fortawesome/free-brands-svg-icons';

import {
	faArrowRightFromBracket,
	faEnvelope,
	faGripLines,
	faPhone,
	faUser,
	faCircleUser,
	faXmark,
} from '@fortawesome/free-solid-svg-icons';


library.add(
	faArrowRightFromBracket,
	faEnvelope,
	faFacebookF,
	faGripLines,
	faInstagram,
	faLinkedin,
	faPhone,
	faTiktok,
	faTwitter,
	faUser,
	faCircleUser,
	faXmark,
);

export default {
	install(app) {
		app.component('FontAwesomeIcon', FontAwesomeIcon);
	}
}

