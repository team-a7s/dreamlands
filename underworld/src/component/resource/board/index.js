import React from 'react';
import { Delete, Resource } from 'admin-on-rest';
import Icon from 'material-ui/svg-icons/action/list';
import { BoardEdit } from './edit';
import { BoardList } from './list';
import { BoardCreate } from './create';

export function boardResource(permissions) {
  return (
    <Resource name="board" icon={Icon}
              create={BoardCreate} remove={Delete}
              edit={BoardEdit} list={BoardList}
    />
  );
}