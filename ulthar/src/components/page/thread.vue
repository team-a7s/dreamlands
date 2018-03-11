<template>
  <section class="thread">
    <article class="thread-main" v-if="threadQuery">
      <md-card>
        <md-card-header>
          <md-card-header-text>
            <md-avatar>
              <img :src="threadQuery.author.avatar" alt="Avatar">
            </md-avatar>

            <div class="md-title">{{threadQuery.author.displayName}}</div>
            <div class="md-subhead">{{threadQuery.created | datetime}}</div>
          </md-card-header-text>
          <md-menu md-direction="bottom-end">
            <md-button class="md-icon-button" md-menu-trigger>
              <md-icon>more_vert</md-icon>
            </md-button>

            <md-menu-content>
              <md-menu-item>
                <span>新回复在前&nbsp;</span>
                <md-switch v-model="reversed" class="md-primary"></md-switch>
              </md-menu-item>

              <md-menu-item @click="reply(threadQuery)">
                <span>{{threadQuery.id}}&nbsp;</span>
                <md-icon>reply</md-icon>
              </md-menu-item>
            </md-menu-content>
          </md-menu>
        </md-card-header>
        <md-card-header>
          <div class="md-title">{{threadQuery.title}}</div>
        </md-card-header>
        <md-card-content>
          <u-postcontent
            :content-type="threadQuery.contentType" :content="threadQuery.content"
          ></u-postcontent>
        </md-card-content>
        <script type="foo" v-if="board">{{board.name}}</script>
        <md-card-actions>
          <md-button class="md-primary" @click="showPostBox=!showPostBox">回复</md-button>
        </md-card-actions>
      </md-card>
    </article>
    <u-postbox
      :parent-id="threadQuery.id" post-type="POST"
      v-if="showPostBox && threadQuery" @posted="onPosted()"
      ref="postbox"
    >
      <span class="label">{{threadQuery.title}}</span>
    </u-postbox>


    <md-card class="posts" v-if="postsQuery">
      <article class="post" v-for="(post, index) in postsQuery.posts.nodes" :key="post.id">
        <md-divider v-if="index > 0"></md-divider>
        <md-card-header>
          <md-card-header-text>
            <md-avatar>
              <img :src="post.author.avatar" alt="Avatar">
            </md-avatar>

            <div class="md-title">{{post.author.displayName}}</div>
            <div class="md-subhead">{{post.created | datetime}}</div>
          </md-card-header-text>
        </md-card-header>
        <md-card-content>
          <template v-if="post.title">
            <div class="md-body-2">{{post.title}}</div>
          </template>
          <u-postcontent
            :content-type="post.contentType" :content="post.content"
          ></u-postcontent>
        </md-card-content>
      </article>
    </md-card>

    <div class="md-layout md-alignment-center"
         v-if="postsQuery && postsQuery.posts.pageInfo.hasNextPage"
    >
      <md-button class="md-icon-button md-raised md-primary" @click="fetchMore">
        <font-awesome-icon :icon="['fas', 'ellipsis-h']"></font-awesome-icon>
      </md-button>
    </div>
  </section>
</template>

<script>
import gql from 'graphql-tag';
import { boardFragment, boardQuery, postFragment, threadQuery, userFragment } from '@/query';

export default {
  name: 'thread',
  props: ['id'],
  data() {
    return {
      boardId: null,
      showPostBox: false,
      reversed: false,
      boardQuery: null,
      threadQuery: null,
      postsQuery: null,
    };
  },
  watch: {
    reversed() {
      this.$nextTick(() => this.$apollo.queries.postsQuery.refetch());
    },
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
    onPosted() {
      this.showPostBox = false;
      this.$apollo.queries.postsQuery.refetch();
    },
    fetchMore() {
      this.$apollo.queries.postsQuery.fetchMore({
        variables: {
          after: this.postsQuery.posts.pageInfo.endCursor,
        },
        updateQuery(previousResult, { fetchMoreResult }) {
          const posts = fetchMoreResult.postsQuery.posts;
          posts.nodes = posts.nodes.filter(post =>
            previousResult.postsQuery.posts.nodes.every(post2 => post2.id !== post.id),
          );
          if (!posts.nodes.length) {
            return previousResult;
          }

          return Object.assign({}, previousResult, {
            postsQuery: Object.assign({}, previousResult.postsQuery, {
              posts: {
                nodes: [...previousResult.postsQuery.posts.nodes, ...posts.nodes],
                pageInfo: posts.pageInfo,
                __typename: 'PostConnection',
              },
            }),
          });
        },
      });
    },
    reply(target) {
      const quote = `>>> ${target.id}`;
      if (this.showPostBox) {
        this.$refs.postbox.append(quote);
      } else {
        this.$copyText(quote);
        this.$store.commit('error', 'Copied');
      }
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
    postsQuery: {
      query: gql`
 query($postId:ID!, $after:String, $reversed:Boolean) {
    postsQuery: node(id:$postId) {
      id
      ... on Post {
        posts(page: {
          first: 3
          after: $after
        }, reversed:$reversed) {
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
          postId: this.id,
          after: null,
          reversed: this.reversed,
        };
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

<style scoped lang="scss">
  .thread-main, .posts {
    margin-bottom: 1em;
  }
</style>
