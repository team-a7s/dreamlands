import Vue from 'vue';
import Vuex from 'vuex';

Vue.use(Vuex);

const iconPool = [
  ['fab', 'fort-awesome'],
  ['fab', 'nintendo-switch'],
  ['fab', 'python'],
  ['fab', 'steam'],
  ['fas', 'heartbeat'],
  ['fas', 'chess-bishop'],
  ['fas', 'chess-knight'],
  ['fas', 'chess-rook'],
  ['fas', 'cube'],
  ['fas', 'fire'],
  ['fas', 'gamepad'],
  ['fas', 'flask'],
];

export function createStore() {
  return new Vuex.Store({
    state: {
      title: 'loading...',
      icon: 'gamepad',
    },

    mutations: {
      setTitle(state, title) {
        state.title = title;
      },
      pickIcon(state) {
        state.icon = iconPool[Math.floor(Math.random() * iconPool.length)];
      },
    },
  });
}

export default createStore();
