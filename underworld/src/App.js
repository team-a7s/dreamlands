import React, { Component } from 'react';
import './App.css';
import { Admin } from 'admin-on-rest';
import Client from './client';
import history from './history';
import saga from './saga';
import routes from './routes';
import * as reducers from './reducer';
import { userResource } from './component/resource/user';
import { postResource } from './component/resource/post';
import { boardResource } from './component/resource/board';

const client = new Client('/api');
const sagas = saga(client, history);

class App extends Component {
  render() {
    return (
      <Admin loginPage={client.loginPage}
             restClient={client.restClient}
             authClient={client.authClient}
             customRoutes={routes}
             customSagas={sagas}
             customReducers={reducers}
             history={history}
      >
        {permissions => [
          userResource,
          boardResource,
          postResource,
        ].map(_ => _(permissions))}
      </Admin>
    );
  }
}

export default App;
