import Vue from 'vue';
import Vuex from 'vuex';

Vue.use(Vuex);

export function createStore() {
  return new Vuex.Store({
    state: {
      title: 'loading...',
    },

    mutations: {
      setTitle(state, title) {
        state.title = title;
      },
    },
  });
}

export default createStore();
