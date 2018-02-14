import Vue from 'vue';
import { sync } from 'vuex-router-sync';
import App from '@/App';
import components from '@/components';
import router from '@/router';
import store from '@/store';
import apolloMixin from '@/apollo';

require('./style/index.scss');

Vue.config.productionTip = false;

sync(store, router);

Object.keys(components).forEach(key => Vue.component(key, components[key]));

window.v = new Vue(Object.assign({
  el: '#app',
  router,
  store,
  render: h => h(App),
}, apolloMixin));
