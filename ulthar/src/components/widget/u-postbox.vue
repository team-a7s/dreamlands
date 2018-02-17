<template>
  <div class="postbox">
    <md-card>
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
          type="button" class="md-primary"
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
      postTitle: '',
      postContent: '',
    };
  },
  methods: {
    postSubmit() {
      this.$apollo.provider.defaultClient.mutate({
        mutation: gql`
        mutation {
          post()
        }`,
      });
    },
  },
};
</script>

<style scoped>
  .title-label {
    justify-content: flex-end;
  }
</style>
