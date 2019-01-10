<?php
	/**
	 * File: admin-navigation.php
	 * Description: An import of functionality
	 * Version: 1.0
	 *
	 * @package textdomain
	 */
	
	
	/**
	 * Function Name: proj_get_field_names
	 * Description: Sets the field names used for the navigation
	 * Version: 1.0
	 *
	 * @package textdomain
	 *
	 * @return array
	 *
	 */
	function proj_get_field_names() {
		
		$field_names = [
			'proj-submenu-columns',
			'proj-submenu-divider',
			'proj-submenu-insidecol'
		];
		
		return $field_names;
	}
	
	/**
	 * Adds custom meta fields to nav menu admin UI
	 *
	 * @since 1.0.0
	 *
	 * @param string $item_id The ID of the menu item.
	 * @param object $item    Menu item.
	 * @param int    $depth   Hierarchy level of the menu item.
	 * @param object $args    Parameters for building the menu list.
	 *
	 * @return string HTML of form fields.
	 */
	function proj_add_megamenu_fields( $item_id, $item, $depth, $args ) { ?>

		<div class="dbdb-megamenu-options">

			<p class="field-megamenu-submenu-columns description description-wide first-level">
				<label for="edit-menu-item-submenu-columns-<?php echo $item_id; ?>">
					<?php esc_attr_e( 'Number of Sub Menu Columns', 'dbdb-nav-walker' ); ?>
					<select id="edit-menu-item-submenu-columns-<?php echo $item_id; ?>" class="widefat code edit-menu-item-submenu-columns" name="proj-submenu-columns[<?php echo $item_id; ?>]">
						<option value="">Auto</option>
						<option value="1" <?php selected( $item->proj_submenu_columns, '1' ); ?>>1</option>
						<option value="2" <?php selected( $item->proj_submenu_columns, '2' ); ?>>2</option>
						<option value="3" <?php selected( $item->proj_submenu_columns, '3' ); ?>>3</option>
						<option value="4" <?php selected( $item->proj_submenu_columns, '4' ); ?>>4</option>
					</select>
				</label>
			</p>

			<p class="field-megamenu-submenu-divider description description-wide second-level">
				<label for="edit-menu-item-submenu-divider-<?php echo $item_id; ?>">
					<input type="checkbox" id="edit-menu-item-submenu-divider-<?php echo $item_id; ?>" class="widefat code edit-menu-item-submenu-divider" name="proj-submenu-divider[<?php echo $item_id; ?>]" value="y" <?php checked( $item->proj_submenu_divider, 'y' ); ?> />
					<?php esc_attr_e( 'Is this a sub menu divider?', 'dbdb-nav-walker' ); ?>
				</label>
			</p>

			<p class="field-megamenu-submenu-insidecol description description-wide third-level">
				<label for="edit-menu-item-submenu-insidecol-<?php echo $item_id; ?>">
					<input type="checkbox" id="edit-menu-item-submenu-insidecol-<?php echo $item_id; ?>" class="widefat code edit-menu-item-submenu-insidecol" name="proj-submenu-insidecol[<?php echo $item_id; ?>]" value="y" <?php checked( $item->proj_submenu_insidecol, 'y' ); ?> />
					<?php esc_attr_e( 'Is this inside another column?', 'dbdb-nav-walker' ); ?>
				</label>
			</p>

		</div>
		<?php
	}
	
	/**
	 * Saves custom metadata for nav menu items.
	 *
	 * @since 1.0.0
	 *
	 * @uses  proj_get_field_names()
	 * @uses  delete_post_meta()
	 * @uses  update_post_meta()
	 *
	 * @param int    $menu_id         The ID of the menu.
	 * @param int    $menu_item_db_id The ID of the menu item.
	 * @param object $menu_item_data  The menu item's data.
	 *
	 * @return void
	 */
	function proj_nav_menu_save_fields( $menu_id, $menu_item_db_id, $menu_item_data ) {
		
		$field_names = proj_get_field_names();
		
		foreach ( $field_names as $name ) {
			$meta_field = '_' . str_replace( '-', '_', $name );
			
			if ( empty( $_REQUEST[ $name ][ $menu_item_db_id ] ) ) {
				delete_post_meta( $menu_item_db_id, $meta_field );
			} else {
				$meta_value = trim( $_REQUEST[ $name ][ $menu_item_db_id ] );
				update_post_meta( $menu_item_db_id, $meta_field, $meta_value );
			}
		}
	}
	
	/**
	 * Sets up the menu item object with the custom metadata.
	 *
	 * Adds custom metadata to the menu item.  This is so the metadata will show up in the
	 * nav menu admin UI.
	 *
	 * @since 1.0.0
	 *
	 * @see   proj_add_megamenu_fields()
	 * @uses  proj_get_field_names()
	 *
	 * @return object $menu_item Menu item
	 */
	function proj_add_data_to_menu_item( $menu_item ) {
		
		if ( isset( $menu_item->ID ) ) :
			$field_names = proj_get_field_names();
			foreach ( $field_names as $name ) {
				$item_field = str_replace( '-', '_', $name );
				$meta_field = '_' . $item_field;
				$value      = get_post_meta( $menu_item->ID, $meta_field, true );
				
				$menu_item->$item_field = $value;
			}
		endif;
		
		if ( isset( $menu_item->ID ) ) :
			$menu_item_parent = absint( $menu_item->menu_item_parent );
			
			// get child count for top-level items
			if ( 'q' === $menu_item_parent ) {
				$args     = [
					'meta_key'       => '_menu_item_menu_item_parent',
					'meta_value'     => $menu_item->db_id,
					'post_type'      => 'nav_menu_item',
					'posts_per_page' => 50,
					'post_status'    => 'publish',
				];
				$children = count( get_posts( $args ) );
				if ( $children > 0 ) {
					$menu_item->child_count = $children;
				}
			}
		endif;
		
		return $menu_item;
	}
	
	// Actions and Filters
	add_action( 'wp_update_nav_menu_item', 'proj_nav_menu_save_fields', 10, 3 );
	add_action( 'wp_nav_menu_item_custom_fields', 'proj_add_megamenu_fields', 10, 4 );
	add_filter( 'wp_setup_nav_menu_item', 'proj_add_data_to_menu_item' );
