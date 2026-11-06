import VueSocialSharing from 'vue-social-sharing';

// This package is CommonJS: depending on the bundler, its default import is the whole module
export default VueSocialSharing.default ?? VueSocialSharing;
