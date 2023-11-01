"use strict";(self.webpackChunkvue_tailwind_theme=self.webpackChunkvue_tailwind_theme||[]).push([["primevueLibrary"],{"./src/vue/libraries/primeVue/customTailwindPassThrough.js":(__unused_webpack___webpack_module__,__webpack_exports__,__webpack_require__)=>{eval('__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n/* harmony import */ var primevue_passthrough_index_esm_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! primevue/passthrough/index.esm.js */ "./node_modules/primevue/passthrough/index.esm.js");\n\n// import Tailwind from "primevue/passthrough/tailwind/index.esm.js";\n\n\n//Tailwind customization\nconst CustomTailwindPassThrough = (0,primevue_passthrough_index_esm_js__WEBPACK_IMPORTED_MODULE_0__.usePassThrough)(\n\t{\n\t\tbutton: {\n\t\t\troot: ({ props, context }) => ({\n\t\t\t\tclass: [\n\t\t\t\t\t\'btn\',\n\t\t\t\t]\n\t\t\t}),\n\t\t},\n\t},\n\t{\n\t\tmergeSections: true,\n\t\tmergeProps: false,\n\t}\n);\n\n/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (CustomTailwindPassThrough);//# sourceURL=[module]\n//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiLi9zcmMvdnVlL2xpYnJhcmllcy9wcmltZVZ1ZS9jdXN0b21UYWlsd2luZFBhc3NUaHJvdWdoLmpzIiwibWFwcGluZ3MiOiI7Ozs7O0FBQUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EiLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly92dWUtdGFpbHdpbmQtdGhlbWUvLi9zcmMvdnVlL2xpYnJhcmllcy9wcmltZVZ1ZS9jdXN0b21UYWlsd2luZFBhc3NUaHJvdWdoLmpzPzRhZjAiXSwic291cmNlc0NvbnRlbnQiOlsiaW1wb3J0IHsgdXNlUGFzc1Rocm91Z2ggfSBmcm9tIFwicHJpbWV2dWUvcGFzc3Rocm91Z2gvaW5kZXguZXNtLmpzXCI7XG4vLyBpbXBvcnQgVGFpbHdpbmQgZnJvbSBcInByaW1ldnVlL3Bhc3N0aHJvdWdoL3RhaWx3aW5kL2luZGV4LmVzbS5qc1wiO1xuXG5cbi8vVGFpbHdpbmQgY3VzdG9taXphdGlvblxuY29uc3QgQ3VzdG9tVGFpbHdpbmRQYXNzVGhyb3VnaCA9IHVzZVBhc3NUaHJvdWdoKFxuXHR7XG5cdFx0YnV0dG9uOiB7XG5cdFx0XHRyb290OiAoeyBwcm9wcywgY29udGV4dCB9KSA9PiAoe1xuXHRcdFx0XHRjbGFzczogW1xuXHRcdFx0XHRcdCdidG4nLFxuXHRcdFx0XHRdXG5cdFx0XHR9KSxcblx0XHR9LFxuXHR9LFxuXHR7XG5cdFx0bWVyZ2VTZWN0aW9uczogdHJ1ZSxcblx0XHRtZXJnZVByb3BzOiBmYWxzZSxcblx0fVxuKTtcblxuZXhwb3J0IGRlZmF1bHQgQ3VzdG9tVGFpbHdpbmRQYXNzVGhyb3VnaDsiXSwibmFtZXMiOltdLCJzb3VyY2VSb290IjoiIn0=\n//# sourceURL=webpack-internal:///./src/vue/libraries/primeVue/customTailwindPassThrough.js\n')},"./src/vue/libraries/primeVue/index.js":(__unused_webpack___webpack_module__,__webpack_exports__,__webpack_require__)=>{eval('__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n/* harmony import */ var primevue_config_config_esm_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! primevue/config/config.esm.js */ "./node_modules/primevue/config/config.esm.js");\n/* harmony import */ var _customTailwindPassThrough_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./customTailwindPassThrough.js */ "./src/vue/libraries/primeVue/customTailwindPassThrough.js");\n\n\n\n/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({\n\tinstall(app, { async }) {\n\t\tconst { defineAsyncComponent } = async;\n\t\tconst Button = defineAsyncComponent(() => Promise.all(/*! import() */[__webpack_require__.e("vendors-node_modules_primevue_basecomponent_basecomponent_esm_js-node_modules_primevue_baseic-3cc458"), __webpack_require__.e("vendors-node_modules_primevue_button_Button_vue")]).then(__webpack_require__.bind(__webpack_require__, /*! primevue/button/Button.vue */ "./node_modules/primevue/button/Button.vue")));\n\t\tconst Sidebar = defineAsyncComponent(() => Promise.all(/*! import() */[__webpack_require__.e("vendors-node_modules_primevue_basecomponent_basecomponent_esm_js-node_modules_primevue_baseic-3cc458"), __webpack_require__.e("vendors-node_modules_primevue_sidebar_Sidebar_vue")]).then(__webpack_require__.bind(__webpack_require__, /*! primevue/sidebar/Sidebar.vue */ "./node_modules/primevue/sidebar/Sidebar.vue")));\n\n\t\tapp.use(primevue_config_config_esm_js__WEBPACK_IMPORTED_MODULE_0__["default"], { unstyled: true, pt: _customTailwindPassThrough_js__WEBPACK_IMPORTED_MODULE_1__["default"] });\n\t\tapp.component(\'PvButton\', Button);\n\t\tapp.component(\'PvSidebar\', Sidebar);\n\t}\n});//# sourceURL=[module]\n//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiLi9zcmMvdnVlL2xpYnJhcmllcy9wcmltZVZ1ZS9pbmRleC5qcyIsIm1hcHBpbmdzIjoiOzs7Ozs7QUFBQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBIiwic291cmNlcyI6WyJ3ZWJwYWNrOi8vdnVlLXRhaWx3aW5kLXRoZW1lLy4vc3JjL3Z1ZS9saWJyYXJpZXMvcHJpbWVWdWUvaW5kZXguanM/OTJkZiJdLCJzb3VyY2VzQ29udGVudCI6WyJpbXBvcnQgUHJpbWVWdWUgZnJvbSAncHJpbWV2dWUvY29uZmlnL2NvbmZpZy5lc20uanMnO1xuaW1wb3J0IEN1c3RvbVRhaWx3aW5kUGFzc1Rocm91Z2ggZnJvbSAnLi9jdXN0b21UYWlsd2luZFBhc3NUaHJvdWdoLmpzJztcblxuZXhwb3J0IGRlZmF1bHQge1xuXHRpbnN0YWxsKGFwcCwgeyBhc3luYyB9KSB7XG5cdFx0Y29uc3QgeyBkZWZpbmVBc3luY0NvbXBvbmVudCB9ID0gYXN5bmM7XG5cdFx0Y29uc3QgQnV0dG9uID0gZGVmaW5lQXN5bmNDb21wb25lbnQoKCkgPT4gaW1wb3J0KCdwcmltZXZ1ZS9idXR0b24vQnV0dG9uLnZ1ZScpKTtcblx0XHRjb25zdCBTaWRlYmFyID0gZGVmaW5lQXN5bmNDb21wb25lbnQoKCkgPT4gaW1wb3J0KCdwcmltZXZ1ZS9zaWRlYmFyL1NpZGViYXIudnVlJykpO1xuXG5cdFx0YXBwLnVzZShQcmltZVZ1ZSwgeyB1bnN0eWxlZDogdHJ1ZSwgcHQ6IEN1c3RvbVRhaWx3aW5kUGFzc1Rocm91Z2ggfSk7XG5cdFx0YXBwLmNvbXBvbmVudCgnUHZCdXR0b24nLCBCdXR0b24pO1xuXHRcdGFwcC5jb21wb25lbnQoJ1B2U2lkZWJhcicsIFNpZGViYXIpO1xuXHR9XG59Il0sIm5hbWVzIjpbXSwic291cmNlUm9vdCI6IiJ9\n//# sourceURL=webpack-internal:///./src/vue/libraries/primeVue/index.js\n')}}]);