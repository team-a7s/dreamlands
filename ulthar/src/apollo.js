import Vue from 'vue';
import { ApolloClient } from 'apollo-client';
import { HttpLink } from 'apollo-link-http';
import { ApolloLink } from 'apollo-link';
import { withClientState } from 'apollo-link-state';
import { InMemoryCache } from 'apollo-cache-inmemory';
import VueApollo from 'vue-apollo';

Vue.use(VueApollo);

const cache = new InMemoryCache();

const httpLink = new HttpLink({
  uri: '/graphql',
});
const stateLink = withClientState({
  cache,
  resolvers: {},
});

const apolloClient = new ApolloClient({
  link: ApolloLink.from([
    stateLink, httpLink,
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
