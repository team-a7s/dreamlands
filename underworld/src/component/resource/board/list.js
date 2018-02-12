import React from 'react';
import {
  Datagrid, DeleteButton, EditButton, List,
  TextField,
} from 'admin-on-rest';

export function BoardList(props) {
  return (
    <List {...props}>
      <Datagrid>
        <TextField source="id"/>
        <TextField source="name"/>
        <TextField source="tagline"/>
        <EditButton/>
        <DeleteButton/>
      </Datagrid>
    </List>
  );
}
