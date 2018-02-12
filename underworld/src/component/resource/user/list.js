import React from 'react';
import { Datagrid, List, TextField } from 'admin-on-rest';

export const UserList = (props) => (
  <List {...props}>
    <Datagrid>
      <TextField source="hash"/>
      <TextField source="nickname"/>
    </Datagrid>
  </List>
);