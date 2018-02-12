import Vue from 'vue';
import Vuex from 'vuex';
import apolloMixin from '../apollo';

Vue.use(Vuex);

export function createStore() {
  return new Vuex.Store({
    state: {
      apollo: apolloMixin.apollo,
    },
  });
}

export default createStore();
