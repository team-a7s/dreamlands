<template>
  <div id="app">
    <md-app md-mode="reveal">
      <md-app-toolbar class="md-primary">
        <md-button class="md-icon-button" @click="menuVisible = !menuVisible">
          <!--<md-icon>menu</md-icon>-->
          <font-awesome-icon :icon="$store.state.icon" class="fa-2x" fixed-width/>
        </md-button>
        <router-link :to="$store.state.titleHref" class="md-title" v-if="$store.state.titleHref">
          {{$store.state.title}}
        </router-link>
        <span class="md-title" v-else>
          {{$store.state.title}}
        </span>
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
          <span>{{message}}</span>
        </md-snackbar>
      </md-app-content>
    </md-app>

    <md-dialog
      :md-active.sync="modalActive"
      :md-close-on-esc="false"
      :md-click-outside-to-close="false"
      v-if="$store.state.modal"
    >
      <template v-if="$store.state.modal.type==='karma'">
        <md-dialog-title>Pause!</md-dialog-title>
        <md-dialog-content>
          <vue-recaptcha
            @verify="verifyCaptcha"
            :sitekey="$root.$options.config.RECAPTCHA_KEY"
          ></vue-recaptcha>

        </md-dialog-content>
      </template>
      <template v-else>
        <md-dialog-title>"{{$store.state.modal.type}}"</md-dialog-title>
      </template>
    </md-dialog>
  </div>
</template>

<script>
import throttle from 'lodash.throttle';
import gql from 'graphql-tag';

export default {
  name: 'App',
  data() {
    return {
      menuVisible: false,
      showSnackbar: false,
      message: '',
    };
  },
  computed: {
    modalActive: {
      get() {
        return !!this.$store.state.modal;
      },
      set(v) {
        if (v) {
          throw new Error('..');
        } else {
          this.$store.commit('closeModal');
        }
      },
    },
  },
  methods: {
    handleError: throttle(function handleError() {
      if (!this.$store.state.error || this.showSnackbar) {
        return;
      }

      const err = this.$store.state.error[0];
      this.$store.commit('shiftError');

      let msg = err.message || err;
      if (msg.match(/^GraphQL error: /)) {
        msg = msg.slice(15);
      }

      this.message = msg;
      this.showSnackbar = true;
    }, 5000),
    verifyCaptcha(response) {
      this.$apollo.mutate({
        mutation: gql`
        mutation($response: String!) {
          createSession {
            token
          }
          challengeCaptcha(response:$response)
        }
`,
        variables: {
          response,
        },
      })
        .then((rst) => {
          if (!rst || !rst.data || !rst.data.createSession) {
            throw new Error('bad response');
          }
          localStorage.setItem('token', rst.data.createSession.token);
          location.reload();
        });
    },
  },
  mounted() {
    this._unbind = this.$store.subscribe((mutation, state) => {
      this.$nextTick(() => {
        this.menuVisible = false;
      });

      if (!state.error) {
        return;
      }

      this.handleError();
    });
  },
  destroyed() {
    this._unbind();
  },
};
</script>

<style scoped lang="scss">
  .md-app {
    min-height: 100vh;
  }
</style>
