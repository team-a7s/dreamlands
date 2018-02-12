import React from 'react';
import {
  DisabledInput, Edit, NumberInput, SimpleForm,
  TextInput,
} from 'admin-on-rest';

export function BoardEdit(props) {
  return (
    <Edit {...props}>
      <SimpleForm>
        <DisabledInput source="id"/>
        <TextInput source="name"/>
        <TextInput source="tagline"/>
        <NumberInput source="flag" defaultValue={0}/>
      </SimpleForm>
    </Edit>
  );
}