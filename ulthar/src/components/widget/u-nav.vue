<template>
  <section class="nav">
    <md-list v-if="boards" class="board-list">
      <md-list-item v-for="board in boards.nodes" :key="board.id">
        <md-icon>move_to_inbox</md-icon>
        <router-link :to="'/board/' + board.id" class="board-link md-list-item-text">
          <span>{{board.name}}</span>
          <span class="muted">{{board.tagline}}</span>
        </router-link>
      </md-list-item>
    </md-list>
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
