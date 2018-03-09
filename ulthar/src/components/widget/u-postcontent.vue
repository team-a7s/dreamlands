<template>
  <section class="post-content">
    <div class="content md-body-1">
      <p v-for="(line, idx) in lines" :key="idx">
        {{line}}
      </p>
    </div>
  </section>
</template>

<script>
export default {
  name: 'u-postcontent',
  props: ['content', 'content-type'],
  computed: {
    lines() {
      return this.$props.content.split('\n');
    },
  },
};
</script>

<style scoped lang="scss">
  .post-content {
    word-wrap: break-word;
    p {
      margin: .25em 0;
    }
  }
</style>
