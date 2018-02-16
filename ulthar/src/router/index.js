import Vue from 'vue';
import Router from 'vue-router';
import Dashboard from '@/components/page/dashboard';
import Board from '@/components/page/board';

Vue.use(Router);

const props = true;

export default new Router({
  mode: 'history',
  routes: [
    {
      path: '/',
      name: 'home',
      component: Dashboard,
    },
    {
      path: '/board/:id',
      name: 'board',
      component: Board,
      props,
    },
  ],
});
