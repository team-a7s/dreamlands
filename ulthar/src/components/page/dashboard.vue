<template>
  <section class="dashboard">
    <md-empty-state
      md-icon="call_missed"
      md-label="从左上角开始"
      md-description="">
      <small class="md-caption">Alpha测试期间数据<strong>不定期清理</strong></small>
    </md-empty-state>

  </section>
</template>

<script>
/* eslint-disable */

import gql from 'graphql-tag';

export default {
  name: 'dashboard',
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
  mounted() {
    this.$store.commit('setTitle', 'Home');
  },
};
</script>

<style scoped>

</style>
