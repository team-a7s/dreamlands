<template>
  <section class="post-content">
    <div class="content md-body-1">
      <p v-for="(line, idx) in lines" :key="idx" v-html="line"></p>
    </div>
  </section>
</template>

<script>
const ctn = document.createElement('div');
function formatLine() {
  const ctx = {};
  return (line) => {
    if (!ctx.hasImage && /^!(https?|magnet):[!-~]+$/.test(line)) {
      ctx.hasImage = true;
      const src = line.slice(1);
      return `<a href="${src}" rel="nofollow noopener noreferer" target="_blank"><img class="external" src="${src}"></a>`;
    }

    ctn.innerText = line;
    return ctn.innerHTML.replace(/(https?|magnet):[!-~]+/, m => `<a href="${m}" rel="nofollow noopener noreferer" target="_blank" class="external">${m}</a>`);
  };
}

export default {
  name: 'u-postcontent',
  props: ['content', 'content-type'],
  computed: {
    lines() {
      return this.$props.content.split('\n').map(formatLine());
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
    img.external {
      max-width: 100%;
      max-height: 400px;
      margin: 0.5em 0;
      object-fit: contain;
    }
  }
</style>
