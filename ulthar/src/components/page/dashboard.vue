<template>
  <section class="dashboard">
    <h1>Hello Dash</h1>
    <hr>
    <ul>
      <li v-for="board in boards.nodes" :key="board.id">
        {{board.name}}
        - {{board.tagline}}
      </li>
    </ul>
  </section>
</template>

<script>
import gql from 'graphql-tag';

export default {
  name: 'dashboard',
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
  data() {
    return {
      boards: null,
    };
  },
};
</script>

<style scoped>

</style>
