import Vue from 'vue';
import Router from 'vue-router';
import Dashboard from '@/components/page/dashboard';
import Board from '@/components/page/board';
import Thread from '@/components/page/thread';

Vue.use(Router);

const props = true;

export default new Router({
  mode: 'hash',
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
    {
      path: '/thread/:id',
      name: 'thread',
      component: Thread,
      props,
    },
  ],
});
