<?php

/**
 * Add a Crop position setting to the post thumbnail metabox.
 *
 */

define( 'JOIST_THUMBNAIL_CROP_FIELD', 'crb_thumbnail_crop' );
define( 'JOIST_CROP_SETTING_INPUT_NAME', 'joist_thumbnail_crop_setting' );
define( 'JOIST_CROP_SETTINGS', [
	// As defined in timber/lib/Image/Operation/Resize.php
	'default'       => __( 'Default', 'joist' ),
	'left'          => __( 'Left', 'joist' ),
	'center'        => __( 'Center', 'joist' ),
	'right'         => __( 'Right', 'joist' ),
	'top'           => __( 'Top', 'joist' ),
	'top-center'    => __( 'Top Center', 'joist' ),
	'bottom'        => __( 'Bottom', 'joist' ),
	'bottom-center' => __( 'Bottom Center', 'joist' ),
] );


add_filter( 'admin_post_thumbnail_html', 'joist_thumbnail_crop_settings', 10, 2 );
function joist_thumbnail_crop_settings( $html, $post_id ) {
	$selected_option = get_post_meta( $post_id, JOIST_THUMBNAIL_CROP_FIELD, true );

	$options = [];
	foreach ( JOIST_CROP_SETTINGS as $crop => $label ) {
		$options[] = '<option value="' . $crop . '" ' . selected( $selected_option, $crop, false ) . '>' . $label . '</option>';
	}

	$label = '<label for="' . JOIST_CROP_SETTING_INPUT_NAME . '" class="post-attributes-label">' . __( 'Crop position', 'joist' ) . '</label> ';
	$input = '<select name="' . JOIST_CROP_SETTING_INPUT_NAME . '" id="' . JOIST_CROP_SETTING_INPUT_NAME . '">' . implode( '', $options ) . '</select>';

	return $html . '<p class="post-attributes-label-wrapper">' . $label . '</p>' . $input;
}

add_action( 'save_post', 'joist_save_thumbnail_crop_settings' );
function joist_save_thumbnail_crop_settings( $post_id ) {
	if ( isset( $_POST[ JOIST_CROP_SETTING_INPUT_NAME ] ) ) {
		$crop = sanitize_text_field( wp_unslash( $_POST[ JOIST_CROP_SETTING_INPUT_NAME ] ) );

		if ( array_key_exists( $crop, JOIST_CROP_SETTINGS ) ) {
			update_post_meta( $post_id, JOIST_THUMBNAIL_CROP_FIELD, $crop );
		}
	}
}
