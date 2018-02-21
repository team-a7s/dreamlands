import React from 'react';
import { Delete, Resource } from 'admin-on-rest';
import { UserList } from './list';
import UserIcon from 'material-ui/svg-icons/social/group';

export function userResource(permissions) {
  return (
      <Resource name="user" icon={UserIcon}
                list={UserList} remove={Delete}
      />
  );
}