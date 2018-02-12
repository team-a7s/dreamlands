import Vue from 'vue';
import { sync } from 'vuex-router-sync';
import App from './App';
import router from './router';
import store from './store';
import apolloMixin from './apollo';

Vue.config.productionTip = false;

sync(store, router);

window.v = new Vue(Object.assign({
  el: '#app',
  router,
  store,
  render: h => h(App),
}, apolloMixin));
