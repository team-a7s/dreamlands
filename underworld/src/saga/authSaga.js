import { call, put, takeEvery } from 'redux-saga/effects';
import { ACTIONS } from '../constants';
import { dispatcher } from '../action/dispatcher';

const actionDispatcher = dispatcher(put).dispathcer;
export default class AuthSaga {
  constructor(client) {
    this.client = client;
  }

  * handleCallback() {
    yield takeEvery(ACTIONS.AUTH_REQUEST_HANDLE_CALLBACK, (function* (action) {
        try {
          const result = yield call(
            [this.client.auth, this.client.auth.handleAuthentication],
            action.payload.hash,
          );
          console.log(action, result);
          if (result.role) {
            yield actionDispatcher.showMessage('Login Success');
          } else {
            yield actionDispatcher.showMessage('Login Failed');
          }

        } catch (e) {
          console.info(e);
          yield actionDispatcher.showMessage('Login Failed');
        }

      }).bind(this),
    );
  }
}