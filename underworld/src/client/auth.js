import auth0 from 'auth0-js';
import * as qs from 'qs';
import * as IdVerifier from 'idtoken-verifier';

// const PATH_HOME = '/';
export default class Auth {
  auth0 = new auth0.WebAuth({
    domain: 'mcfog.auth0.com',
    clientID: 'qo4dedKpftBjO8wqdy5YINhCnYxNq7wi',
    redirectUri: 'http://borderline.lvh.me/callback',
    audience: 'https://mcfog.auth0.com/userinfo',
    responseType: 'code id_token',
    scope: 'openid profile email role',
  });
  verifier = new IdVerifier({
    issuer: 'https://mcfog.auth0.com/',
    audience: 'qo4dedKpftBjO8wqdy5YINhCnYxNq7wi',
  });

  login() {
    this.auth0.authorize();
  }

  handleAuthentication(hash) {
    return this.parseHash(hash)
      .then(authResult => {
        if (authResult.idToken) {
          // console.log('ok', authResult);
          this.setSession(authResult);
          return authResult.idTokenPayload;
        }

        return Promise.reject(
          { message: 'bad result', authResult: authResult });
      })
      ;
  }

  parseHash(hash = window.location.hash) {
    return new Promise((resolve, reject) => {

      const hashStr = hash.replace(/^#?\/?/, '');
      const parsedHash = qs.parse(hashStr);

      if (parsedHash.hasOwnProperty('error')) {
        return reject(parsedHash);
      }

      const state = parsedHash.state;
      const transaction = this.auth0
        .transactionManager.getStoredTransaction(state);
      const transactionStateMatchesState = transaction
        && transaction.state === state;
      if (state && !transactionStateMatchesState) {
        return reject({
          error: 'invalid_token',
          errorDescription: '`state` does not match.',
        });
      }

      const transactionNonce = (transaction && transaction.nonce) || null;
      const parsedIdToken = this.verifier.decode(parsedHash.id_token);
      if (transactionNonce &&
        transactionNonce !== parsedIdToken.payload.nonce) {
        return reject({
          error: 'invalid_token',
          errorDescription: '`nonce` does not match.',
        });
      }

      resolve({
        idToken: parsedHash.id_token,
        idTokenPayload: parsedIdToken.payload,
        expiresIn: parsedIdToken.payload.exp,
      });
    });

  }

  setSession(authResult) {
    // Set the time that the access token will expire at
    let expiresAt = JSON.stringify((authResult.expiresIn * 1000) +
      new Date().getTime());
    localStorage.setItem('access_token', authResult.accessToken);
    localStorage.setItem('id_token', authResult.idToken);
    localStorage.setItem('expires_at', expiresAt);
    // navigate to the home route
    // history.replace(PATH_HOME);
  }

  logout() {
    // Clear access token and ID token from local storage
    localStorage.removeItem('access_token');
    localStorage.removeItem('id_token');
    localStorage.removeItem('expires_at');
    // navigate to the home route
    // history.replace(PATH_HOME);
  }

  isAuthenticated() {
    // Check whether the current time is past the
    // access token's expiry time
    let expiresAt = JSON.parse(localStorage.getItem('expires_at'));
    return new Date().getTime() < expiresAt;
  }

  getIdToken() {
    if (!localStorage.id_token) {
      return {};
    }

    return this.verifier.decode(localStorage.id_token).payload;
  }
}

// function promisify(func) {
//   return (...args) =>
//     new Promise(
//       (resolve, reject) => func(
//         ...args,
//         (err, result) => err ? reject(err) : resolve(result),
//       ),
//     );
// }
