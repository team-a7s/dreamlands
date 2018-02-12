import React from 'react';
import { Resource } from 'admin-on-rest';
import { UserList } from './list';
import UserIcon from 'material-ui/svg-icons/social/group';

export function userResource(permissions) {
  console.log(permissions);
  return (
    <Resource name="user" list={UserList} icon={UserIcon}/>
  );
}