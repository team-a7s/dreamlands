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
        <md-snackbar
          md-position="center"
          :md-duration="4000" :md-active.sync="showSnackbar" md-persistent
          @md-closed="handleError()"
        >
          <span>{{errorMessage}}</span>
        </md-snackbar>
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
      showSnackbar: false,
      errorMessage: '',
    };
  },
  methods: {
    handleError() {
      if (!this.$store.state.error || this.showSnackbar) {
        return;
      }

      const err = this.$store.state.error[0];
      this.$store.commit('shiftError');

      let msg = err.message || err;
      if (msg.match(/^GraphQL error: /)) {
        msg = msg.slice(15);
      }

      this.errorMessage = msg;
      this.showSnackbar = true;
    },
  },
  mounted() {
    this.$store.subscribe(() => {
      setTimeout(_ => this.handleError());
    });
  },
};
</script>
