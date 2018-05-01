<template>
  <div class="postbox">
    <md-card class="md-elevation-5">
      <md-card-content v-if="!loading">
        <u-login></u-login>
        <div class="md-layout">
          <md-field class="md-layout-item field-title">
            <label>标题</label>
            <md-input maxlength="30" name="title" placeholder="标题" v-model="postTitle"></md-input>
          </md-field>
          <md-field class="md-layout-item md-small-size-30 md-medium-size-20 title-label">
            <slot></slot>
          </md-field>
        </div>
        <md-field>
          <label>内容</label>
          <md-textarea
            name="content" placeholder="内容" md-counter="300"
            v-model="postContent" ref="content"
          ></md-textarea>
        </md-field>
      </md-card-content>
      <md-card-actions v-if="!loading">
        <md-button
          type="button" class="md-primary md-raised"
          @click="postSubmit()"
          :disabled="!sessionQuery || !sessionQuery.currentUser"
        >
          POST
        </md-button>
      </md-card-actions>
      <md-card-content v-else>
        <div class="md-layout md-alignment-center loading">
          <font-awesome-icon :icon="['fas', 'spinner']" class="fa-spin fa-3x"></font-awesome-icon>
        </div>
      </md-card-content>
    </md-card>
  </div>
</template>

<script>
import { postMutation, sessionQuery } from '@/query';

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
      loading: false,
      sessionQuery: null,
    };
  },
  props: [
    'parent-id',
    'post-type',
  ],
  methods: {
    postSubmit() {
      const variables = {
        parentId: this.$props.parentId,
        postType: this.$props.postType,
        title: this.postTitle,
        content: this.postContent,
      };
      this.postTitle = '';
      this.postContent = '';
      this.loading = true;

      this.$apollo.provider.defaultClient.mutate({
        mutation: postMutation,
        variables,
      }).then((response) => {
        this.$store.commit('error', '发布成功');
        this.$emit('posted', { response });
        this.loading = false;
      }).catch((err) => {
        this.$apolloProvider.errorHandler.call(this, err);
        this.loading = false;
      });
    },
    append(content) {
      if (!this.postContent.endsWith('\n')) {
        this.postContent += '\n';
      }

      this.postContent += `${content}\n`;
      this.$refs.content.$el.focus();
    },
  },
};
</script>

<style scoped lang="scss">
  .postbox {
    margin-bottom: 1em;
    > .md-card {
      background-image: url(../../img/postbox-bg.jpg);
      background-position: center;
      background-size: cover;
    }
  }

  .title-label {
    justify-content: flex-end;
  }

  .loading {
    height: 304px;
  }

  /deep/ .field-title {
    .md-count {
      display: none;
    }
  }

  /deep/ .md-field {
    margin-bottom: 12px;
  }
</style>
