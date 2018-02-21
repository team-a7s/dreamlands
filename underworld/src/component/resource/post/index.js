import React from 'react';
import { Delete, Resource } from 'admin-on-rest';
import { PostList } from './list';
import PostIcon from 'material-ui/svg-icons/av/library-books';

export function postResource(permissions) {
  return (
      <Resource name="post" icon={PostIcon}
                list={PostList} remove={Delete}
                
      />
  );
}