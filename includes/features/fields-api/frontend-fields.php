<?php

/**
 * Easy Content Manager - Frontend Field API
 *
 * Drop-in replacements for ACF-style template functions.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$GLOBALS['ecm_repeater_loop'] = [];

function ecm_get_valid_post_id( $post_id = null ) {
	return $post_id ?? get_the_ID();
}

function ecm_get_field_schema( string $field_name, string $post_type ) {
	$key = "post_{$post_type}_{$field_name}";
	return $GLOBALS['ecm_field_registry'][ $key ] ?? [];
}

function ecm_format_value( $value, array $schema ) {
	if ( empty( $schema['type'] ) ) {
		return $value;
	}

	// Use 'field_type' if provided, fallback to 'type'
	$field_type = $schema['field_type'] ?? $schema['type'];

	switch ( $field_type ) {
		case 'image':
			$id = (int) $value[0];
			return [
				'id'      => $id,
				'url'     => wp_get_attachment_url( $id ),
				'alt'     => get_post_meta( $id, '_wp_attachment_image_alt', true ),
				'title'   => get_the_title( $id ),
				'caption' => wp_get_attachment_caption( $id ),
				'sizes'   => wp_get_attachment_metadata( $id )['sizes'] ?? [],
			];

		case 'gallery':
			$ids = is_array( $value ) ? $value : explode( ',', $value );
			return array_map(
				fn( $id) => wp_get_attachment_image_src( (int) $id, 'full' ),
				array_filter( $ids )
			);

		case 'file':
			return wp_get_attachment_url( (int) $value );

		default:
			return $value;
	}//end switch
}

function ecm_get_field( $field_name, $post_id = null, $format = true ) {
	$post_id = ecm_get_valid_post_id( $post_id );
	$value = get_post_meta( $post_id, $field_name, true );
	if ( $format ) {
		$schema = ecm_get_field_schema( $field_name, get_post_type( $post_id ) );
		$value = ecm_format_value( $value, $schema );
	}
	return $value;
}

function ecm_the_field( $field_name, $post_id = null, $format = true ) {
	echo esc_html( ecm_get_field( $field_name, $post_id, $format ) );
}

function ecm_get_fields( $post_id = null ) {
	return get_post_meta( ecm_get_valid_post_id( $post_id ) );
}

function ecm_get_field_object( $field_name, $post_id = null ) {
	$post_id = ecm_get_valid_post_id( $post_id );
	$schema = ecm_get_field_schema( $field_name, get_post_type( $post_id ) );
	if ( ! $schema ) {
		return false;
	}
	$value = get_post_meta( $post_id, $field_name, true );
	return [
		'key' => $field_name,
		'label' => $schema['description'] ?? $field_name,
		'name' => $field_name,
		'type' => $schema['type'] ?? 'text',
		'field_type' => $schema['field_type'] ?? 'text',
		'value' => ecm_format_value( $value, $schema ),
		'raw' => $value,
		'schema' => $schema
	];
}

function ecm_get_field_objects( $post_id = null ) {
	$post_id = ecm_get_valid_post_id( $post_id );
	$meta = get_post_meta( $post_id );
	$fields = [];
	foreach ( $meta as $key => $values ) {
		$fields[ $key ] = ecm_get_field_object( $key, $post_id );
	}
	return $fields;
}

function ecm_have_rows( $field_name, $post_id = null ) {
	$rows = ecm_get_field( $field_name, $post_id );
	if ( ! is_array( $rows ) || empty( $rows ) ) {
		return false;
	}
	$GLOBALS['ecm_repeater_loop'] = [
		'field' => $field_name,
		'post_id' => ecm_get_valid_post_id( $post_id ),
		'rows' => $rows,
		'index' => -1
	];
	return true;
}

function ecm_the_row() {
	$GLOBALS['ecm_repeater_loop']['index']++;
	return isset( $GLOBALS['ecm_repeater_loop']['rows'][ $GLOBALS['ecm_repeater_loop']['index'] ] );
}

function ecm_get_row_index() {
	return $GLOBALS['ecm_repeater_loop']['index'] ?? -1;
}

function ecm_get_row() {
	$loop = $GLOBALS['ecm_repeater_loop'] ?? [];
	return $loop['rows'][ $loop['index'] ] ?? [];
}

function ecm_get_sub_field( $sub_field_name ) {
	$row = ecm_get_row();
	return $row[ $sub_field_name ] ?? null;
}

function ecm_the_sub_field( $sub_field_name ) {
	echo esc_html( ecm_get_sub_field( $sub_field_name ) );
}

function ecm_get_sub_field_object( $sub_field_name ) {
	$row = ecm_get_row();
	return [
		'name' => $sub_field_name,
		'value' => $row[ $sub_field_name ] ?? null,
	];
}

function ecm_get_group( $field_name, $post_id = null ) {
	$group = ecm_get_field( $field_name, $post_id, false );
	return is_array( $group ) ? $group : [];
}

function ecm_get_group_field( $group_name, $sub_field_path, $post_id = null ) {
	$group = ecm_get_group( $group_name, $post_id );

	$keys = explode( '.', $sub_field_path );
	$value = $group;
	foreach ( $keys as $key ) {
		if ( ! is_array( $value ) || ! array_key_exists( $key, $value ) ) {
			return null;
		}
		$value = $value[ $key ];
	}
	return $value;
}


function ecm_the_group_field( $group_name, $sub_field_name, $post_id = null ) {
	echo esc_html( ecm_get_group_field( $group_name, $sub_field_name, $post_id ) );
}

function ecm_update_field( $field_name, $value, $post_id = null ) {
	return update_post_meta( ecm_get_valid_post_id( $post_id ), $field_name, $value );
}

function ecm_delete_field( $field_name, $post_id = null ) {
	return delete_post_meta( ecm_get_valid_post_id( $post_id ), $field_name );
}
