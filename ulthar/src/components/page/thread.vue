<template>
  <section class="thread">
    <md-card v-if="threadQuery">
      <md-card-header>
        <md-avatar>
          <img :src="threadQuery.author.avatar" alt="Avatar">
        </md-avatar>

        <div class="md-title">{{threadQuery.author.displayName}}</div>
        <div class="md-subhead">Subtitle here</div>
      </md-card-header>
      <md-card-header>
        <div class="md-title">{{threadQuery.title}}</div>
      </md-card-header>
      <md-card-content>
        {{threadQuery.content}}
      </md-card-content>
    </md-card>
    <script type="foo" v-if="board">{{board.name}}</script>
  </section>
</template>

<script>
import { boardFragment, boardQuery, threadQuery } from '@/query';

export default {
  name: 'thread',
  props: ['id'],
  data() {
    return {
      boardId: null,
    };
  },
  computed: {
    board() {
      return this.$apollo.provider.defaultClient.readFragment({
        id: `Board_${this.id}`,
        fragment: boardFragment,
      }) || this.boardQuery;
    },
  },
  apollo: {
    boardQuery: {
      query: boardQuery,
      variables() {
        return {
          id: this.boardId,
        };
      },
      skip() {
        return !this.boardId || !!this.board;
      },
    },
    threadQuery: {
      query: threadQuery,
      variables() {
        return { id: this.id };
      },
    },
  },
  updated() {
    if (!this.threadQuery) { return; }
    this.boardId = this.threadQuery.parentId;
    if (this.board) {
      this.$store.commit({
        type: 'setTitle',
        title: this.board.name,
        titleHref: `/board/${this.board.id}`,
      });
    }
  },
};
</script>

<style scoped>

</style>
