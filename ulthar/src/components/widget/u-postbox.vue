<template>
  <div class="postbox">
    <md-card class="md-primary">
      <md-card-content>
        <u-login></u-login>
        <div class="md-layout">
          <md-field class="md-layout-item">
            <label>标题</label>
            <md-input name="title" placeholder="标题" v-model="postTitle"></md-input>
          </md-field>
          <md-field class="md-layout-item md-small-size-35 md-medium-size-20 title-label">
            <slot></slot>
          </md-field>
        </div>
        <md-field>
          <label>内容</label>
          <md-textarea
            name="content" placeholder="内容" md-counter="800"
            v-model="postContent"
          ></md-textarea>
        </md-field>
      </md-card-content>
      <md-card-actions>
        <md-button
          type="button" class="md-primary md-raised"
          @click="postSubmit()"
          :disabled="!sessionQuery || !sessionQuery.currentUser"
        >
          POST
        </md-button>
      </md-card-actions>
    </md-card>
  </div>
</template>

<script>
import gql from 'graphql-tag';
import { sessionQuery } from '@/query';

export default {
  name: 'u-postbox',
  apollo: {
    sessionQuery,
  },
  data() {
    return {
      showSnackbar: false,
      postTitle: '',
      postContent: '',
    };
  },
  props: [
    'parent-id',
    'post-type',
  ],
  methods: {
    postSubmit() {
      this.$apollo.provider.defaultClient.mutate({
        mutation: gql`
        mutation(
          $parentId: String!
          $postType: PostType!
          $title: String!
          $content: String!
        ) {
          post(
            parentId: $parentId
            type: $postType
            title: $title
            content: $content
          ){
            id
          }
        }`,
        variables: {
          parentId: this.$props.parentId,
          postType: this.$props.postType,
          title: this.postTitle,
          content: this.postContent,
        },
      }).then(() => {
        this.$store.commit('error', '发布成功');
      }).catch((err) => {
        this.$store.commit('error', err);
      });
    },
  },
};
</script>

<style scoped lang="scss">
  @import "~vue-material/src/theme/engine";
  @import "~vue-material/src/components/MdElevation/mixins";

  .postbox {
    margin-bottom: 1em;
    > .md-card {
      @include md-elevation(5);
      background-image: url(../../img/postbox-bg.jpg);
      background-position: center;
      background-size: cover;
    }
  }

  .title-label {
    justify-content: flex-end;
  }
</style>
