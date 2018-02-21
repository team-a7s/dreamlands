<template>
  <section class="thread">
    <article class="thread-main" v-if="threadQuery">
      <md-card>
        <md-card-header>
          <md-avatar>
            <img :src="threadQuery.author.avatar" alt="Avatar">
          </md-avatar>

          <div class="md-title">{{threadQuery.author.displayName}}</div>
          <div class="md-subhead">{{threadQuery.created | datetime}}</div>
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
      </md-card>
    </article>

    <md-card class="posts" v-if="postsQuery">
      <article class="post" v-for="(post, index) in postsQuery.posts.nodes" :key="post.id">
        <md-divider v-if="index > 0"></md-divider>
        <md-card-header>
          <md-avatar>
            <img :src="post.author.avatar" alt="Avatar">
          </md-avatar>

          <div class="md-title">{{post.author.displayName}}</div>
          <div class="md-subhead">{{post.created | datetime}}</div>
        </md-card-header>
        <md-card-header v-if="post.title">
          <div class="md-title">{{post.title}}</div>
        </md-card-header>
        <md-card-content>
          <u-postcontent
            :content-type="post.contentType" :content="post.content"
          ></u-postcontent>
        </md-card-content>
      </article>
    </md-card>

    <u-postbox
      :parent-id="threadQuery.id" post-type="POST"
      v-if="showPostBox && threadQuery" @posted="onPosted()"
    >
      <span class="label">{{threadQuery.title}}</span>
    </u-postbox>

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
      showPostBox: true,
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
    onPosted() {
      this.showPostBox = false;
      this.$apollo.queries.postsQuery.refetch();
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
 query($postId:ID!, $after:String) {
    postsQuery: node(id:$postId) {
      id
      ... on Post {
        posts(page: {
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
          postId: this.id,
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

<style scoped>
  .thread-main {
    margin-bottom: 1em;
  }
</style>
