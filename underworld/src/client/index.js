// import React from 'react';
import postgrestClient from './rest';
import {
  AUTH_CHECK, AUTH_ERROR, AUTH_GET_PERMISSIONS, AUTH_LOGIN,
  AUTH_LOGOUT,
} from 'admin-on-rest';
import Auth from './auth';
import { connect } from 'react-redux';
import Login from '../component/login';
import { fetchJson } from 'admin-on-rest/lib/util/fetch';

export default class Client {
  constructor(apiUrl) {
    this.apiUrl = apiUrl;
    this.auth = new Auth();
  }

  get restClient() {
    return postgrestClient(this.apiUrl, (url, _options) => {
      const options = Object.assign({}, _options);
      console.log(this.auth.isAuthenticated(), options);
      if (this.auth.isAuthenticated()) {
        options.user = {
          authenticated: true,
          token: `Bearer ${localStorage.id_token}`,
        };
      }
      return fetchJson(url, options);
    });
  }

  get authClient() {
    return (type, params) => {
      console.log(type, params);
      switch (type) {
        case AUTH_GET_PERMISSIONS:
          return Promise.resolve(this.auth.getIdToken());
        case AUTH_CHECK:
          return Promise.resolve(this.auth.isAuthenticated());
        case AUTH_LOGOUT:
          return Promise.resolve(this.auth.logout());
        case AUTH_ERROR:
          if (params.status === 401 || params.status === 403) {
            return Promise.reject(this.auth.logout());
          }
          return Promise.resolve();

        case AUTH_LOGIN:
        default:
          return Promise.reject();
      }
    };
  }

  get loginPage() {
    return connect(undefined, undefined,
      (stateProps, dispatchProps, ownProps) => {
        return Object.assign(
          { client: this },
          ownProps, stateProps, dispatchProps,
        );
      },
    )(Login);
  }
}