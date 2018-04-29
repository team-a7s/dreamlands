import Vue from 'vue';
import { sync } from 'vuex-router-sync';

import App from '@/App';
import components from '@/components';
import * as filters from '@/filters';
import config from '@/config';
import router from '@/router';
import store from '@/store';
import plugins from '@/plugins';
import apolloMixin from '@/apollo';
import fontawesome from '@fortawesome/fontawesome';
import brands from '@fortawesome/fontawesome-free-brands';
import solid from '@fortawesome/fontawesome-free-solid';

import 'vue-material/dist/vue-material.min.css';
import 'vue-material/dist/theme/default.css';
import '@/style/index.scss';

Vue.config.productionTip = false;

sync(store, router);
router.afterEach(() => {
  store.commit('pickIcon');
  const focused = document.querySelector(':focus');
  if (focused) {
    focused.blur();
  }
});

// Object.keys(components).forEach(key => Vue.component(key, components[key]));
plugins.forEach(p => Vue.use(p));
Object.assign(Vue.options.components, components);
Object.assign(Vue.options.filters, filters);
Vue.mixin({
  data() {
    const data = {};
    if (this.$options.apollo) {
      Object.getOwnPropertyNames(this.$options.apollo).forEach((k) => {
        data[k] = null;
      });
    }
    return data;
  },
});

fontawesome.library.add(brands, solid);
window.v = new Vue(Object.assign({
  el: '#app',
  config,
  router,
  store,
  render: h => h(App),
}, apolloMixin));
