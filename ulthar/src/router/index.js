import Vue from 'vue';
import Router from 'vue-router';
import Dashboard from '@/components/page/dashboard';

Vue.use(Router);

export default new Router({
  mode: 'history',
  routes: [
    {
      path: '/',
      name: 'HelloWorld',
      component: Dashboard,
    },
  ],
});
