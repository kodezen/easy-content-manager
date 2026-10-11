import { useCallback, useEffect, useRef, useState } from "react";

export const useWpUserFormValues = (existingValues) => {
  const hydratedKeys = useRef(new Set());
  const [values, setValues] = useState({ meta: {} });

  const getActiveForm = () =>
    document.getElementById('createuser') ||
    document.getElementById('your-profile');

  const updateValuesFromDOM = useCallback(() => {
    const form = getActiveForm();
    if (!form) return;

    const formData = new FormData(form);
    const data = { meta: {} };

    formData.forEach((value, key) => {
      if (key.startsWith('meta.')) {
        data.meta[key.split('.')[1]] = value;
      } else {
        data[key] = value;
      }
    });

    // console.log({data, formData});

    const checkboxes = form.querySelectorAll('input[type="checkbox"]');
    checkboxes.forEach(cb => {
      if (cb.name.startsWith('meta.')) {
        data.meta[cb.name.split('.')[1]] = cb.checked;
      }
    });

    setValues(data);
  }, []);

  // DOM listener
  useEffect(() => {
    const form = getActiveForm();
    if (!form) return;

    form.addEventListener('input', updateValuesFromDOM);
    form.addEventListener('change', updateValuesFromDOM);

    updateValuesFromDOM();

    return () => {
      form.removeEventListener('input', updateValuesFromDOM);
      form.removeEventListener('change', updateValuesFromDOM);
    };
  }, [updateValuesFromDOM]);

  // 🔥 Late hydration when existingValues arrives
  useEffect(() => {
    if (!existingValues) return;

    const form = getActiveForm();
    if (!form) return;

    Object.entries(existingValues).forEach(([key, value]) => {
      if (hydratedKeys.current.has(key)) return;

      let field = form.querySelector(`[name="meta.${key}"]`);   

      if (!field) {
        field = document.createElement('input');
        field.type = 'hidden';
        field.name = `meta.${key}`;
        form.appendChild(field);
      }

      // Only hydrate if empty
      const isEmpty =
        field.type === 'checkbox'
          ? field.checked === false
          : (field.value === '' || field.value === '0' || field.value === 'false' || field.value === 'null');

      if (isEmpty) {
        if (field.type === 'checkbox') {
          field.checked = Boolean(value);
        } else {
          field.value = (value !== null && typeof value === 'object') ? JSON.stringify(value) : (value ?? '');
        }

        hydratedKeys.current.add(key);
      }
    });

    updateValuesFromDOM();
  }, [existingValues]);

  const setWpFieldValue = (name, value, isMeta = true) => {
    const form = getActiveForm();
    if (!form) return;

    const clean = name.startsWith('meta.') ? name.split('.')[1] : name;
    const domName = isMeta ? `meta.${clean}` : clean;

    let field = form.querySelector(`[name="${domName}"]`);
    if (!field) {
      field = document.createElement('input');
      field.type = 'hidden';
      field.name = domName;
      form.appendChild(field);
    }

    if (field.type === 'checkbox') {
      field.checked = Boolean(value);
    } else {
      field.value = (value !== null && typeof value === 'object') ? JSON.stringify(value) : (value ?? '');
    }

    field.dispatchEvent(new Event('input', { bubbles: true }));
  };

  return [values, setWpFieldValue];
};