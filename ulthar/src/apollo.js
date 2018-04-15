import Vue from 'vue';
import { ApolloClient } from 'apollo-client';
import { HttpLink } from 'apollo-link-http';
import { ApolloLink } from 'apollo-link';
import { withClientState } from 'apollo-link-state';
import { InMemoryCache } from 'apollo-cache-inmemory';
import VueApollo from 'vue-apollo';
import { setContext } from 'apollo-link-context';

Vue.use(VueApollo);

const cache = new InMemoryCache({
  dataIdFromObject: obj => (obj.id ? `${obj.__typename}_${obj.id}` : null),
});

const httpLink = new HttpLink({
  uri: '/graphql',
});

const authLink = setContext((_, { headers }) => {
  const token = localStorage.getItem('token');
  return {
    headers: {
      ...headers,
      'X-Kadath-Token': token || '',
    },
  };
});

const stateLink = withClientState({
  cache,
  resolvers: {},
});

const apolloClient = new ApolloClient({
  link: ApolloLink.from([
    stateLink, authLink, httpLink,
  ]),
  cache,
  connectToDevTools: true,
});

const apolloProvider = new VueApollo({
  defaultClient: apolloClient,
  errorHandler(e) {
    if (e.networkError) {
      this.$store.commit('error',
        `${e.networkError.statusCode}: ${e.networkError.message}`);
      return;
    }
    if (!e.graphQLErrors || !e.graphQLErrors[0]) {
      this.$store.commit('error', `Unknown Error: ${e.message || e}`);
      return;
    }

    switch (e.graphQLErrors[0].category) {
      case 'karma':
        this.$store.commit('refreshKarma', e.graphQLErrors[0].category);
        break;
      default:
        console.error(e.graphQLErrors, this);
    }
  },
});

const apollo = {};

export default {
  provide: apolloProvider.provide(),
  apollo,
};
