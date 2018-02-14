<template>
  <section class="dashboard">
    <h1>Hello Dash</h1>
    <hr>
    ASDF
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
  data() {
    return {
      boards: null,
    };
  },
  mounted() {
    this.$store.commit('setTitle', 'Home');
  },
};
</script>

<style scoped>

</style>
