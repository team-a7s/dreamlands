<template>
  <label>
    <input type="text" v-model="username">
  </label>
</template>

<script>
import gql from 'graphql-tag';

export default {
  name: 'u-login',
  apollo: {
    currentUser: gql`query {
      session {
        currentUser {
          id
          displayName
          avatar
        }
      }
    }`,
  },
};
</script>

<style scoped>

</style>
