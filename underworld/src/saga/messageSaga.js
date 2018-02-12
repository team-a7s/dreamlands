import { call, takeEvery } from 'redux-saga/effects';
import { ACTIONS } from '../constants';

export default class MessageSaga {
  constructor(history) {
    this.history = history;
  }

  * handleShowMessage() {
    yield takeEvery(ACTIONS.MESSAGE_SHOW, (function* (action) {
        console.log('msg_saga', action, this.history);
        yield call(
          [this.history, this.history.replace],
          '/message',
          action.payload,
        );
      }).bind(this),
    );

  }
}