<template>
  <section class="nav">
    <ul v-if="boards" class="board-list">
      <li v-for="board in boards.nodes" :key="board.id">
        <router-link :to="'/board/' + board.id" class="board-link group">
          <span>{{board.name}}</span>
          <span class="muted">{{board.tagline}}</span>
        </router-link>
      </li>
    </ul>
  </section>
</template>

<script>
import gql from 'graphql-tag';

export default {
  name: 'u-nav',
  apollo: {
    boards: gql`query {
    boards(page:{first: 5}) {
      nodes {
        id
        name
        tagline
      }
    }
  }`,
  },
};
</script>

<style scoped>

</style>
