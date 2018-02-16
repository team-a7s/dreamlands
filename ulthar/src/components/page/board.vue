<template>
  <section class="board" v-if="board">
    <u-postbox><span class="label">{{board.name}}</span></u-postbox>
  </section>
</template>

<script>
import gql from 'graphql-tag';

export default {
  name: 'board',
  props: ['id'],
  data() {
    return {};
  },
  computed: {
    board() {
      return this.$apollo.provider.defaultClient.readFragment({
        id: this.id,
        fragment: gql`fragment x on Board {
          id
          name
          tagline
        }`,
      }) || this.boardQuery;
    },
  },
  apollo: {
    boardQuery: {
      query: gql`
        query($boardId:ID!) {
          boardQuery: node(id:$boardId) {
            ... on Board {
              id
              name
              tagline
            }
          }
        }
      `,
      variables() {
        return {
          boardId: this.id,
        };
      },
      skip() {
        return !!this.board;
      },
    },
    threads: {
      query: gql`
        query($boardId:ID!, $after:String) {
          threads: node(id:$boardId) {
            id
            ... on Board {
              threads(page: {
                first:10
                after: $after
              }) {
                nodes{
                  title
                }
              }
            }
          }
        }
      `,
      variables() {
        return {
          boardId: this.id,
        };
      },
    },
  },
  updated() {
    if (!this.board) { return; }
    this.$store.commit('setTitle', this.board.name);
  },
};
</script>

<style scoped>

</style>
