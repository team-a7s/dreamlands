import AuthSaga from './authSaga';
import MessageSaga from './messageSaga';

export default (client, history) => {
  const auth = new AuthSaga(client);
  const msg = new MessageSaga(history);
  return [
    auth.handleCallback.bind(auth),
    msg.handleShowMessage.bind(msg),
  ];
};
