/* eslint-disable global-require */
import FontAwesomeIcon from '@fortawesome/vue-fontawesome';

export default {
  'u-nav': require('./widget/u-nav').default,
  'u-login': require('./widget/u-login').default,
  'u-postbox': require('./widget/u-postbox').default,
  FontAwesomeIcon,
};

