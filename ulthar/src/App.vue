<template>
  <div id="app">
    <!--<p>-->
    <!--<router-link to="/foo">Go to Foo</router-link>-->
    <!--<router-link to="/bar">Go to Bar</router-link>-->
    <!--</p>-->
    <nav class="site-nav">
      <h1>{{$store.state.title}}</h1>
      <u-nav></u-nav>
    </nav>
    <section class="main">
      <router-view></router-view>
    </section>
  </div>
</template>

<script>

export default {
  name: 'App',
};
</script>
