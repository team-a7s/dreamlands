<template>
  <section class="nav">
    <h3>Boards</h3>
    <ul v-if="boards">
      <li v-for="board in boards.nodes" :key="board.id">
        <router-link :to="'/board/' + board.id">{{board.name}}</router-link>
        <span class="muted">{{board.tagline}}</span>
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
