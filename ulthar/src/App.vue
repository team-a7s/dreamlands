<template>
  <div id="app">
    <!--<p>-->
    <!--<router-link to="/foo">Go to Foo</router-link>-->
    <!--<router-link to="/bar">Go to Bar</router-link>-->
    <!--</p>-->
    <nav class="site-nav">
      <h1 class="title">
        <a tabindex="-1" class="handler">
          <font-awesome-icon :icon="$store.state.icon" fixed-width/>
        </a>
        {{$store.state.title}}
      </h1>
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
