import React from 'react';
import {
  Datagrid,
  DeleteButton,
  EditButton,
  List,
  SelectField,
  ShowButton,
  TextField,
} from 'admin-on-rest';
import { PostType } from '@/enum';

export const PostList = (props) => (
    <List {...props}>
      <Datagrid>
        <SelectField source="type" choices={PostType}/>
        <TextField source="title"/>
        <TextField source="content"/>
        <EditButton headerStyle={{ width: '50px' }} label=""/>
        <DeleteButton headerStyle={{ width: '50px' }} label=""/>
        <ShowButton headerStyle={{ width: '50px' }} label=""/>
      </Datagrid>
    </List>
);