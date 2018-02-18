<template>
  <section class="board" v-if="board">
    <u-postbox
      :parent-id="board.id" post-type="THREAD"
      v-if="showPostBox"
    >
      <span class="label">{{board.name}}</span>
    </u-postbox>

    <md-list class="md-double-line md-dense" v-if="threadsQuery">
      <md-subheader>
        <span>
          {{board.name}}
        </span>
        <md-button class="icon-add md-icon-button" @click="showPostBox=!showPostBox">
          <md-icon>{{showPostBox?'clear':'add'}}</md-icon>
        </md-button>
      </md-subheader>
      <md-list-item
        v-for="thread in threadsQuery.threads.nodes" :key="thread.id"
        :to="`/thread/${thread.id}`">

        <md-avatar class="avatar">
          <router-link :to="`/user/${thread.author.id}`">
            <img :src="thread.author.avatar" alt="">
            <md-tooltip md-direction="bottom">{{thread.author.displayName}}</md-tooltip>
          </router-link>
        </md-avatar>
        <div class="md-list-item-text">
          <span>{{thread.title}}</span>
          <span>{{thread.content}}</span>
        </div>
      </md-list-item>
    </md-list>
  </section>
</template>

<script>
import gql from 'graphql-tag';
import { boardQuery, postFragment, userFragment, boardFragment } from '@/query';

export default {
  name: 'board',
  props: ['id'],
  data() {
    return {
      showPostBox: false,
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
          id: this.id,
        };
      },
      skip() {
        return !!this.board;
      },
    },
    threadsQuery: {
      query: gql`
        query($boardId:ID!, $after:String) {
          threadsQuery: node(id:$boardId) {
            id
            ... on Board {
              threads(page: {
                first:10
                after: $after
              }) {
                nodes{
                  ...postFragment
                  author {
                    ...userFragment
                  }
                }
              }
            }
          }
        }
        ${postFragment}
        ${userFragment}
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
    this.$store.commit({
      type: 'setTitle',
      title: this.board.name,
    });
  },
};
</script>

<style scoped>
  .icon-add {
    flex: 0 0 auto;
    margin-left: auto;
    margin-right: 0;
  }
</style>
