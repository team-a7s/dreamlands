/* eslint-disable global-require */
import FontAwesomeIcon from '@fortawesome/vue-fontawesome';
import VueRecaptcha from 'vue-recaptcha';

export default {
  'u-nav': require('./widget/u-nav').default,
  'u-login': require('./widget/u-login').default,
  'u-postbox': require('./widget/u-postbox').default,
  'u-postcontent': require('./widget/u-postcontent').default,
  FontAwesomeIcon,
  VueRecaptcha,
};
