import React from 'react';
import { Datagrid, List, TextField } from 'admin-on-rest';

export const PostList = (props) => (
  <List {...props}>
    <Datagrid>
      <TextField source="content"/>
    </Datagrid>
  </List>
);