import { ACTIONS } from '../constants';

export function dispatcher(dispatch) {
  return {
    dispatch,
    dispathcer: {
      showMessage(message, forward = '/') {
        return dispatchFsa(ACTIONS.MESSAGE_SHOW, {
          message,
          forward,
          timeout: 3000,
        });
      },
    },
  };

  function dispatchFsa(type, payload) {
    return dispatch({ type, payload });
  }
}

