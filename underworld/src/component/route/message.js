import React, { Component } from 'react';
import { Route } from 'react-router-dom';
import { Card, CardText } from 'material-ui/Card';
import { ViewTitle } from 'admin-on-rest';
import { connect } from 'react-redux';

class MessageComponent extends Component {
  render() {
    const state = this.props.location.state || {};
    const title = state.message ? 'Message' : 'Welcome Back';
    setTimeout(_ =>
        this.props.history.replace(state.forward || '/', state.forwardState)
      , state.timeout || 2000);

    return (
      <Card>
        <ViewTitle title={title}/>
        <CardText>{state.message}</CardText>
        <CardText color="grey">Redirecting...</CardText>
      </Card>
    );
  }
}

function stateMapper(state) {
  return {
    message: state.message,
  };
}

export default <Route exact path="/message"
                      component={connect(stateMapper)(MessageComponent)}/>;
