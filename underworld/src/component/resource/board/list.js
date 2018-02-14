import React from 'react';
import {
  Datagrid, DeleteButton, EditButton, List, ShowButton,
  TextField,
} from 'admin-on-rest'

export function BoardList(props) {
  return (
    <List {...props}>
      <Datagrid>
        <TextField source="id"/>
        <TextField source="name"/>
        <TextField source="tagline"/>

        <EditButton headerStyle={{width:"50px"}} label="" />
        <DeleteButton headerStyle={{width:"50px"}} label="" />
        <ShowButton headerStyle={{width:"50px"}}  label=""/>
      </Datagrid>
    </List>
  );
}
