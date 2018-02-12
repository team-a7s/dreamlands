import React, { Component } from 'react';
import getMuiTheme from 'material-ui/styles/getMuiTheme';
import MuiThemeProvider from 'material-ui/styles/MuiThemeProvider';
import Card, { CardActions, CardHeader, CardText } from 'material-ui/Card';
import RaisedButton from 'material-ui/RaisedButton';
// import { userLogin } from 'admin-on-rest';

export default class Login extends Component {
  render() {
    const muiTheme = getMuiTheme();
    return (
      <MuiThemeProvider muiTheme={muiTheme}>
        <Card zDepth={    3} style={{ width: '80%', margin: '2em auto' }}>
          <CardHeader
            title="Permission Denied"
          />
          <CardText color="grey">
            You might need go to authentication service
          </CardText>
          <CardActions>
            <RaisedButton label="Login" primary
                          onClick={this.onClickLogin}/>
          </CardActions>
        </Card>
      </MuiThemeProvider>
    );
  }

  onClickLogin = e => {
    this.props.client.auth.login();
  }
}
