<template>
  <form class="postbox">
    <div class="row">
      <input type="text" name="title" placeholder="Title">
      <slot></slot>
    </div>
    <textarea name="content" placeholder="Content"></textarea>
    <button>Submit</button>
  </form>
</template>

<script>
export default {
  name: 'u-postbox',
};
</script>

<style scoped>

</style>
