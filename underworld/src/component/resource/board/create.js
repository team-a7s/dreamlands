import React from 'react';
import { Create, NumberInput, SimpleForm, TextInput } from 'admin-on-rest';

export function BoardCreate(props) {
  return (
    <Create {...props}>
      <SimpleForm redirect="show">
        <TextInput source="name"/>
        <TextInput source="tagline"/>
        <NumberInput source="flag" defaultValue={0}/>
      </SimpleForm>
    </Create>
  );
}