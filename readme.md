
# Easy Content Manager – Frontend Field API

Drop-in replacements for template functions.
This API lets developers fetch, display, loop, and manage custom fields created with **Easy Content Manager (ECM)**.

---

## 📚 Table of Contents

1. [Core Helpers](#core-helpers)
2. [Field Functions](#field-functions)
3. [Field Objects](#field-objects)
4. [Repeater Functions](#repeater-functions)
5. [Group Functions](#group-functions)
6. [Update/Delete](#updatedelete)
7. [Usage Examples](#usage-examples)
8. [Best Practices](#best-practices)

---

## Core Helpers

### `ecm_get_valid_post_id( $post_id = null )`

Ensures you always have a valid post ID.

* If `$post_id` is passed → returns it.
* Else → returns `get_the_ID()`.

```php
$post_id = ecm_get_valid_post_id();   // Current post
$post_id = ecm_get_valid_post_id(42); // Explicit post ID
```

---

### `ecm_get_field_schema( $field_name, $post_type )`

Fetches the **schema** (field config) from the registry.

* Returns array with keys like `type`, `description`, `format`.
* If schema not found → returns `[]`.

```php
$schema = ecm_get_field_schema('featured_image', 'post');
```

---

### `ecm_format_value( $value, array $schema )`

Formats raw field values according to schema:

* **image** → `[url, width, height, is_intermediate]`
* **file** → URL string
* **default** → unchanged

```php
$schema = ecm_get_field_schema('resume', 'post');
$url = ecm_format_value('123', $schema); // => https://site.com/uploads/resume.pdf
```

---

## Field Functions

### `ecm_get_field( $field_name, $post_id = null, $format = true )`

Gets the value of a custom field.

* `$format = true` → formats value (images, files).
* `$format = false` → returns raw stored value.

```php
$title = ecm_get_field('page_title');
$image = ecm_get_field('featured_image'); // formatted
$id    = ecm_get_field('featured_image', null, false); // raw ID
```

---

### `ecm_the_field( $field_name, $post_id = null, $format = true )`

Echoes a field value directly (wrapped in `esc_html()`).

```php
<h1><?php ecm_the_field('page_title'); ?></h1>
```

---

### `ecm_get_fields( $post_id = null )`

Returns **all custom fields** (meta) for a post.

```php
$fields = ecm_get_fields(42);
```

---

## Field Objects

### `ecm_get_field_object( $field_name, $post_id = null )`

Returns field details including schema, value, and raw value.

```php
$field = ecm_get_field_object('featured_image');
/*
[
  'key'    => 'featured_image',
  'label'  => 'Featured Image',
  'name'   => 'featured_image',
  'type'   => 'image',
  'value'  => [url, width, height],
  'raw'    => '123',
  'schema' => [...]
]
*/
```

---

### `ecm_get_field_objects( $post_id = null )`

Returns all fields for a post as **objects**.

```php
$all = ecm_get_field_objects(get_the_ID());
```

---

## Repeater Functions

Repeaters return arrays of rows.

### `ecm_have_rows( $field_name, $post_id = null )`

Initializes repeater loop. Returns `true` if rows exist.

### `ecm_the_row()`

Advances loop pointer. Returns `true` if current row exists.

### `ecm_get_row_index()`

Returns current row index (starting at 0).

### `ecm_get_row()`

Returns full current row as array.

### `ecm_get_sub_field( $sub_field_name )`

Gets a value from the current row.

### `ecm_the_sub_field( $sub_field_name )`

Echoes a value from the current row (escaped).

### `ecm_get_sub_field_object( $sub_field_name )`

Returns sub field object.

```php
<?php if ( ecm_have_rows('team_members') ) : ?>
  <ul>
    <?php while ( ecm_the_row() ) : ?>
      <li>
        <strong><?php ecm_the_sub_field('name'); ?></strong>
        <span><?php ecm_the_sub_field('role'); ?></span>
      </li>
    <?php endwhile; ?>
  </ul>
<?php endif; ?>
```

---

## Group Functions

Groups are single associative arrays of subfields.

### `ecm_get_group( $field_name, $post_id = null )`

Fetches entire group as array.

### `ecm_get_group_field( $group_name, $sub_field_path, $post_id = null )`

Gets a nested field from a group. Supports **dot notation** for deep nesting.

### `ecm_the_group_field( $group_name, $sub_field_path, $post_id = null )`

Echoes a nested field from a group (escaped).

```php
// Without helpers
$group = ecm_get_field('group_1');
echo $group['text_field'];
echo $group['email_field'];
echo $group['group_2']['email_field_inner'];

// With helpers
ecm_the_group_field('group_1', 'text_field');
ecm_the_group_field('group_1', 'email_field');

// Dot notation
echo ecm_get_group_field('group_1', 'group_2.email_field_inner');
```

---

## Update/Delete

### `ecm_update_field( $field_name, $value, $post_id = null )`

Update a custom field.

```php
ecm_update_field('views', 100);
```

---

### `ecm_delete_field( $field_name, $post_id = null )`

Delete a custom field.

```php
ecm_delete_field('old_meta_key');
```

---

## Usage Examples

### Simple Field

```php
<h1><?php ecm_the_field('page_title'); ?></h1>
```

### Image Field

```php
<?php
$img = ecm_get_field('featured_image');
if ($img) :
?>
  <img src="<?php echo esc_url($img[0]); ?>" width="<?php echo (int)$img[1]; ?>" height="<?php echo (int)$img[2]; ?>">
<?php endif; ?>
```

### Repeater with Nested Repeater

```php
<?php if ( ecm_have_rows('repeater') ) : ?>
  <ul>
    <?php while ( ecm_the_row() ) : ?>
      <?php $nested = ecm_get_sub_field('repeater_second'); ?>
      <li>
        <?php ecm_the_sub_field('text_field'); ?>
        <?php if ($nested) : ?>
          <ul>
            <?php foreach ($nested as $row) : ?>
              <li><?php echo esc_html($row['text_field']); ?></li>
              <li><?php echo esc_html($row['password']); ?></li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </li>
    <?php endwhile; ?>
  </ul>
<?php endif; ?>
```

### Group

```php
<h3><?php ecm_the_group_field('group_1', 'text_field'); ?></h3>
<p><?php ecm_the_group_field('group_1', 'email_field'); ?></p>
<p><?php echo esc_html( ecm_get_group_field('group_1', 'group_2.email_field_inner') ); ?></p>
```

---

## Best Practices

* Use **`ecm_the_field()`** only when directly printing text.
* Use **`ecm_get_field()`** when:

  * Inside attributes (`href`, `src`).
  * Conditional checks or transformations.
  * Concatenating or passing values.
* Escape outputs properly:

  * `esc_html()` for text.
  * `esc_url()` for links.
  * `wp_kses_post()` for HTML content.

---
