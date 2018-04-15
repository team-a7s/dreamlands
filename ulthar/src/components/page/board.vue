<template>
  <section class="board" v-if="board">
    <u-postbox
      :parent-id="board.id" post-type="THREAD"
      v-if="showPostBox" @posted="onPosted()"
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
          <p>{{thread.content}}</p>
        </div>
        <div class="md-list-action" v-if="thread.childCount > 0">
          +{{thread.childCount}}
        </div>
      </md-list-item>
    </md-list>

    <div class="md-layout md-alignment-center"
         v-if="threadsQuery && threadsQuery.threads.pageInfo.hasNextPage"
    >
      <md-button class="md-icon-button md-raised md-primary" @click="fetchMore">
        <font-awesome-icon :icon="['fas', 'ellipsis-h']"></font-awesome-icon>
      </md-button>
    </div>
  </section>
</template>

<script>
import gql from 'graphql-tag';
import { boardFragment, boardQuery, postFragment, userFragment } from '@/query';

export default {
  name: 'board',
  props: ['id'],
  data() {
    return {
      showPostBox: false,
      boardQuery: null,
      threadsQuery: null,
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
  methods: {
    fetchMore() {
      this.$apollo.queries.threadsQuery.fetchMore({
        variables: {
          after: this.threadsQuery.threads.pageInfo.endCursor,
        },
        updateQuery(previousResult, { fetchMoreResult }) {
          const threads = fetchMoreResult.threadsQuery.threads;
          if (!threads.nodes.length) {
            return previousResult;
          }

          return Object.assign({}, previousResult, {
            threadsQuery: Object.assign({}, previousResult.threadsQuery, {
              threads: {
                nodes: [...previousResult.threadsQuery.threads.nodes, ...threads.nodes],
                pageInfo: threads.pageInfo,
                __typename: 'PostConnection',
              },
            }),
          });
        },
      });
    },
    onPosted() {
      this.showPostBox = false;
      this.$apollo.queries.threadsQuery.refetch();
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
                first: 13
                after: $after
              }) {
                nodes{
                  ...postFragment
                  author {
                    ...userFragment
                  }
                }
                pageInfo {
                  hasNextPage
                  endCursor
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
          after: null,
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
