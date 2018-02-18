import gql from 'graphql-tag';

export const userFragment = gql`fragment userFragment on User {
  id
  displayName
  avatar
}`;

export const boardFragment = gql`fragment boardFragment on Board {
  id
  name
  tagline
}`;

export const postFragment = gql`fragment postFragment on Post {
  id
  title
  content
  contentType
  via
  parentId
  type
  created
}
`;

export const sessionQuery = gql`query {
  sessionQuery: session {
    currentUser {
      id
      displayName
      avatar
    }
  }
}`;

export const boardQuery = gql`
  query($id:ID!) {
    boardQuery: node(id:$id) {
      ... on Board {
        ...boardFragment
      }
    }
  }
  ${boardFragment}
`;

export const threadQuery = gql`query($id:ID!) {
  threadQuery: node(id:$id) {
    id
    ... on Post {
      author {
        ...userFragment
      }
      ...postFragment
    }
  }
}
${userFragment}
${postFragment}
`;

