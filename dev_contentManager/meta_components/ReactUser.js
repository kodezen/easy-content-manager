import React, { useEffect } from 'react';
import { __ } from '@wordpress/i18n';

export default function ReactUser() {
    useEffect(()=>{
        const user = document.querySelector('.ecm__user-fields')
        console.log({user});
    },[])
  return (
    <div>
      load user
    </div>
  );
};
