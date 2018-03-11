<template>
  <section class="nav">
    <md-list v-if="boards" class="md-double-line md-dense">
      <md-list-item v-for="board in boards.nodes" :key="board.id"
                    :to="'/board/' + board.id" class="board-link md-list-item-text"
      >
        <!--<md-icon>move_to_inbox</md-icon>-->
        <div class="md-list-item-text">
          <span>{{board.name}}</span>
          <span>{{board.tagline}}</span>
        </div>
      </md-list-item>
    </md-list>
  </section>
</template>

<script>
import gql from 'graphql-tag';

export default {
  name: 'u-nav',
  data() {
    return {
      boards: null,
    };
  },
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

<style scoped lang="scss">
  > > > .router-link-active {
    cursor: default;
  }
</style>
