<template>
  <section class="post-content">
    <div class="content md-body-1" ref="content"></div>
  </section>
</template>

<script>
export default {
  name: 'u-postcontent',
  props: ['content', 'content-type'],
  mounted() {
    const text = this.$props.content;
    this.renderPlain(text);
  },
  methods: {
    renderPlain(text) {
      this.$refs.content.innerHTML = '';
      text.split('\n').forEach((line) => {
        const p = document.createElement('p');
        p.innerText = line;
        this.$refs.content.appendChild(p);
      });
    },
  },
};
</script>

<style scoped>
  .post-content {
    word-wrap: break-word;
  }
</style>
