<template>
  <section class="login">
    <div class="md-layout md-alignment-center-left" v-if="currentUser">
      <div class="md-layout-item">
        <md-field>
          <label>欢迎回来</label>
          <md-input :value="currentUser.displayName" readonly></md-input>
        </md-field>
      </div>
      <div class="md-layout-item md-size-10">
        <md-button class="md-icon-button md-accent" @click="doLogout()">
          <font-awesome-icon icon="power-off" fixed-width/>
        </md-button>
      </div>
    </div>
    <md-field v-else-if="loading">
      <label>loading...</label>
    </md-field>
    <div class="md-layout md-alignment-center-left" v-else>
      <div class="md-layout-item">
        <md-field>
          <label>登录</label>
          <md-input v-model="login"></md-input>
          <span class="md-helper-text">输入昵称直接注册</span>
        </md-field>
      </div>
      <div class="md-layout-item md-size-10">
        <md-button class="md-icon-button md-primary" @click="doLogin()" :disabled="!login">
          <md-icon>person_add</md-icon>
        </md-button>
      </div>
    </div>


    <md-dialog :md-active.sync="dialogShow">
      <md-dialog-title>{{ dialogTitle }}</md-dialog-title>
      <md-dialog-content v-html="dialogContent"/>

      <md-dialog-actions>
        <md-button class="" v-if="spawnedLogin"
                   v-clipboard:copy="spawnedLogin"
                   v-clipboard:success="$store.commit.bind($store, 'error', 'Copied')"
        >Copy</md-button>
        <md-button class="md-primary md-raised" @click="dialogShow=false">Ok</md-button>
      </md-dialog-actions>
    </md-dialog>

  </section>
</template>

<script>
import gql from 'graphql-tag';
import { sessionQuery } from '@/query';

export default {
  name: 'u-login',
  apollo: {
    sessionQuery: {
      query: sessionQuery,
      loadingKey: 'loading',
      watchLoading(a, b) {
        console.log(a, b, this.loading);
      },
    },
  },
  data() {
    return {
      login: null,
      dialogShow: false,
      dialogTitle: '',
      dialogContent: '',
      spawnedLogin: '',
      loading: 0,
    };
  },
  methods: {
    doLogin() {
      if (!this.loginHash) {
        this.doSpawn();
        return;
      }

      this.requestLogin(this.loginName, this.loginHash).then(({ token, currentUser }) => {
        localStorage.token = token;
        this.$apollo.provider.defaultClient.writeQuery({
          query: sessionQuery,
          data: {
            sessionQuery: {
              currentUser: {
                __typename: 'User',
                ...currentUser,
              },
              __typename: 'Session',
            },
          },
        });

        this.$store.commit('error', '登录成功');
      }).catch(this.handleError.bind(this));
    }, // doLogin()
    doSpawn() {
      this.$apollo.mutate({
        mutation: gql`
            mutation($name: String!) {
              spawnUser(nickname: $name) {
                hash
                user {
                  displayName
                  id
                  avatar
                }
              }
            }
          `,
        variables: {
          name: this.loginName,
        },
      }).then((rst) => {
        const spawnUser = rst.data.spawnUser;

        this.$apollo.provider.defaultClient.writeQuery({
          query: sessionQuery,
          data: {
            sessionQuery: {
              currentUser: {
                __typename: 'User',
                ...spawnUser.user,
              },
              __typename: 'Session',
            },
          },
        });

        return this.requestLogin(spawnUser.user.displayName, spawnUser.hash).then(({ token }) => {
          localStorage.token = token;
          this.spawnedLogin = `${spawnUser.user.displayName}:${spawnUser.hash}`;
          this.showMessage(`请保管好您的登录暗号<br>${this.spawnedLogin}`, '注册成功');
        });
      }).catch(this.handleError.bind(this));
    },
    showMessage(content, title = '提示信息') {
      this.dialogTitle = title;
      this.dialogContent = content;
      this.dialogShow = true;
    },
    requestLogin(displayName, hash) {
      return this.$apollo.mutate({
        mutation: gql`
        mutation($displayName: String!, $hash: String!) {
          login(displayName: $displayName, hash: $hash) {
            token
            currentUser {
              id
              displayName
              avatar
            }
          }
        }
`,
        variables: {
          displayName,
          hash,
        },
      }).then(o => o.data.login);
    }, // requestLogin
    doLogout() {
      delete localStorage.token;

      this.$apollo.provider.defaultClient.writeQuery({
        query: sessionQuery,
        data: {
          sessionQuery: null,
        },
      });
      this.login = '';
    },
    handleError(err) {
      let msg = err.message || err;
      if (msg.match(/^GraphQL error: /)) {
        msg = msg.slice(15);
      }

      this.$store.commit('error', `处理失败，原因：\n${msg}`);
      console.warn(err);
    },
  },
  computed: {
    currentUser() {
      return this.sessionQuery ? this.sessionQuery.currentUser : null;
    },
    loginName() {
      return (this.login || '').split(':')[0];
    },
    loginHash() {
      return (this.login || '').split(':')[1];
    },
  },
};
</script>

<style scoped>

</style>
