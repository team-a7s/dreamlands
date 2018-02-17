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
  dataIdFromObject: obj => `${obj.__typename}_${obj.id}`,
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
});

const apollo = {};

export default {
  apolloProvider,
  apollo,
};
