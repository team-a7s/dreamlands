import gql from 'graphql-tag';

export const sessionQuery = gql`query {
  sessionQuery: session {
    currentUser {
      id
      displayName
      avatar
    }
  }
}`;
