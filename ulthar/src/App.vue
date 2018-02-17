<template>
  <div id="app">
    <md-app md-mode="reveal">
      <md-app-toolbar class="md-primary">
        <md-button class="md-icon-button" @click="menuVisible = !menuVisible">
          <!--<md-icon>menu</md-icon>-->
          <font-awesome-icon :icon="$store.state.icon" class="fa-2x" fixed-width/>
        </md-button>
        <span class="md-title">{{$store.state.title}}</span>
      </md-app-toolbar>

      <md-app-drawer :md-active.sync="menuVisible">
        <md-toolbar class="md-transparent" md-elevation="0">Navigation</md-toolbar>
        <u-nav></u-nav>
      </md-app-drawer>

      <md-app-content>
        <router-view></router-view>
      </md-app-content>
    </md-app>
  </div>
</template>

<script>

export default {
  name: 'App',
  data() {
    return {
      menuVisible: false,
    };
  },
};
</script>
