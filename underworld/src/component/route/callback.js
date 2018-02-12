import React, { Component } from 'react';
import { Route } from 'react-router-dom';
import { Card } from 'material-ui/Card';
import { ViewTitle } from 'admin-on-rest';
import { connect } from 'react-redux';
import { ACTIONS } from '../../constants';

class CallbackComponent extends Component {
  render() {
    setTimeout(_ =>
      this.props.dispatch(
        {
          type: ACTIONS.AUTH_REQUEST_HANDLE_CALLBACK,
          payload: {
            hash: this.props.location.hash,
          },
        },
      ),
    );

    return (
      <Card>
        <ViewTitle title="Processing..."/>
      </Card>
    );
  }

  shouldComponentUpdate(nextProps, nextState) {
    // console.log(nextProps, nextState);
    return false;
  }
}

function stateMapper(state) {
  return {
    auth: state.auth,
  };
}

export default <Route exact path="/callback"
                      component={connect(stateMapper)(CallbackComponent)}/>;
