import React from 'react'
import { Show, SimpleShowLayout, TextField } from 'admin-on-rest'

export function BoardShow (props) {
  return (
    <Show {...props}>
      <SimpleShowLayout>
        <TextField source="id"/>
        <TextField source="name"/>
        <TextField source="tagline"/>
        <TextField source="flag"/>
      </SimpleShowLayout>
    </Show>
  )
}
