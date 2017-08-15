<?php
	namespace SQUERY\Widget_Terms;
?>
<div class="widgin-widget-form">

		<fieldset data-fieldset-id="general" class="settings-general">

			<legend class="screen-reader-text"><span><?php _e('General Settings') ?></span></legend>

			<!-- title -->
			<p>
				<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
				<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>" />
			</p>

			<!-- tax -->
			<p>
				<label for="<?php echo $this->get_field_id( 'tax' ); ?>"><?php _e( 'Taxanomy:', 'advanced-categories-widget' ); ?></label>
				<select name="<?php echo $this->get_field_name('tax'); ?>" id="<?php echo $this->get_field_id('tax'); ?>" class="widefat">
					<?php foreach( _taxanomies_list() as $query_var => $label  ) { ?>
					<option value="<?php echo esc_attr( $query_var ); ?>" <?php selected( $instance['tax'] , $query_var ); ?>><?php echo esc_html( $label ); ?></option>
					<?php } ?>
				</select>
			</p>

			<!-- oreder by -->
			<p>
				<label for="<?php echo $this->get_field_id('orderby'); ?>">
					<?php _e( 'Order By:', 'advanced-categories-widget' ); ?>
				</label>
				<select name="<?php echo $this->get_field_name('orderby'); ?>" id="<?php echo $this->get_field_id('orderby'); ?>" class="widefat">
					<?php foreach( _orderby_term_list() as $query_var => $label  ) { ?>
					<option value="<?php echo esc_attr( $query_var ); ?>" <?php selected( $instance['orderby'] , $query_var ); ?>><?php echo esc_html( $label ); ?></option>
					<?php } ?>
				</select>
			</p>

			<!-- oreder -->
			<p>
				<label for="<?php echo $this->get_field_id('order'); ?>">
					<?php _e( 'Order:', 'advanced-categories-widget' ); ?>
				</label>
				<select name="<?php echo $this->get_field_name('order'); ?>" id="<?php echo $this->get_field_id('order'); ?>" class="widefat">
					<option value="desc" <?php selected( $instance['order'] , 'desc' ); ?>><?php _e( 'Descending', 'advanced-categories-widget' ); ?></option>
					<option value="asc" <?php selected( $instance['order'] , 'asc' ); ?>><?php _e( 'Ascending', 'advanced-categories-widget' ); ?></option>
				</select>
			</p>

		</fieldset>
	<?php if( false ) : ?>
		<!-- // @todo: uncomment after add ajax term updater -->
		<!-- // @todo: change to excluder (from includer) -->

	<div class="widgin-section">

		<?php echo fieldset_header_html( $fieldset = 'filters', $title = 'Filters', $instance, $this ); ?>

		<fieldset data-fieldset-id="filters" class="widgin-settings widgin-fieldset settings-filters">

			<legend class="screen-reader-text"><span><?php _e('Filters') ?></span></legend>

			<?php
			$_intro = __( 'Use the following fields to limit your list to certain categories.' );
			$intro = apply_filters( 'acatw_intro_text_filters', $_intro );
			?>

			<div class="description widgin-description">
				<?php echo wpautop( $intro ); ?>
			</div>

			<?php echo build_field_tax_term( $instance, $this ); ?>

		</fieldset>

	</div><!-- /.widgin-section -->

	<div class="widgin-section">

		<?php echo fieldset_header_html( $fieldset = 'thumbnails', $title = 'Term Thumbnail', $instance, $this ); ?>

		<fieldset data-fieldset-id="thumbnails" class="widgin-settings widgin-fieldset settings-thumbnails">

			<legend class="screen-reader-text"><span><?php _e('Category Thumbnail') ?></span></legend>

				<?php
				$_intro = __( "If you choose to display a thumbnail of each category&#8217;s featured image, you can either select from an image size already registered with your site, or set a custom size." );
				$intro = apply_filters( 'acatw_intro_text_thumbnails', $_intro );
				?>

				<div class="description widgin-description">
					<?php echo wpautop( $intro ); ?>
				</div>

				<!-- show thumb -->
				<p>
					<input class="checkbox" type="checkbox" id="<?php echo $this->get_field_id( 'show_thumb' ); ?>" name="<?php echo $this->get_field_name( 'show_thumb' ); ?>" <?php checked( $instance['show_thumb'], 1 ); ?>/>
					<label for="<?php echo $this->get_field_id( 'show_thumb' ); ?>">
						<?php _e( 'Display Thumbnail?', 'advanced-categories-widget' ); ?>
					</label>
				</p>

				<!-- thumb size defaults -->
				<p class="widgin-thumb-size-defaults">
					<label for="<?php echo $this->get_field_id('thumb_size'); ?>">
						<?php _e( 'Choose Registered Image Size:', 'advanced-categories-widget' ); ?>
					</label>
					<?php img_sizes_select_html( $instance, $this ); ?>
				</p>

				<!-- custom thumbs size -->
				<div class="widgin-thumbsize-wrap">
					<p>
						<label><?php _e( 'Set Custom Size:', 'advanced-categories-widget' ); ?></label><br />

						<label for="<?php echo $this->get_field_id( 'thumb_size_w' ); ?>">
							<?php _e( 'Width:', 'advanced-categories-widget' ); ?>
						</label>
						<input class="small-text widgin-thumb-size widgin-thumb-width" id="<?php echo $this->get_field_id( 'thumb_size_w' ); ?>" name="<?php echo $this->get_field_name( 'thumb_size_w' ); ?>" type="number" value="<?php echo absint( $instance['thumb_size_w'] ); ?>" />

						<br />

						<label for="<?php echo $this->get_field_id( 'thumb_size_h' ); ?>">
							<?php _e( 'Height:', 'advanced-categories-widget' ); ?>
						</label>
						<input class="small-text widgin-thumb-size widgin-thumb-height" id="<?php echo $this->get_field_id( 'thumb_size_h' ); ?>" name="<?php echo $this->get_field_name( 'thumb_size_h' ); ?>" type="number" value="<?php echo absint( $instance['thumb_size_h'] ); ?>" />
					</p>

					<p>
						<?php _e( 'Preview Custom Size:', 'easy-shuffle-widget' ); ?><br />
						<span class="widgin-preview-container">
							<span class="widgin-thumbnail-preview" style="font-size: <?php echo absint( $instance['thumb_size_h'] ); ?>px; height:<?php echo absint( $instance['thumb_size_h'] ); ?>px; width:<?php echo absint( $instance['thumb_size_w'] ); ?>px">
								<i class="widgin-preview-image dashicons dashicons-format-image"></i>
							</span>
						</span>
					</p>
				</div>

		</fieldset>

	</div><!-- /.widgin-section -->
	<?php endif; ?>


	<div class="widgin-section">

		<?php echo fieldset_header_html( $fieldset = 'excerpts', $title = 'Term Information', $instance, $this ); ?>

		<fieldset data-fieldset-id="excerpts" class="widgin-settings widgin-fieldset settings-excerpts">

			<legend class="screen-reader-text"><span><?php _e('Category Description and Counts') ?></span></legend>

			<!-- show counts -->
			<p>
				<input id="<?php echo $this->get_field_id( 'show_count' ); ?>" name="<?php echo $this->get_field_name( 'show_count' ); ?>" type="checkbox" <?php checked( $instance['show_count'], 1 ); ?> />
				<label for="<?php echo $this->get_field_id( 'show_count' ); ?>">
					<?php _e( 'Display Post Count?', 'advanced-categories-widget' ); ?>
				</label>
			</p>

			<!-- show desc -->
			<p>
				<input id="<?php echo $this->get_field_id( 'show_desc' ); ?>" name="<?php echo $this->get_field_name( 'show_desc' ); ?>" type="checkbox" <?php checked( $instance['show_desc'], 1 ); ?> />
				<label for="<?php echo $this->get_field_id( 'show_desc' ); ?>">
					<?php _e( 'Display Category Description?', 'advanced-categories-widget' ); ?>
				</label>
			</p>

			<!-- desc excerpt length -->
			<div class="widgin-excerptsize-wrap">
				<p>
					<label for="<?php echo $this->get_field_id( 'desc_length' ); ?>">
						<?php _e( 'Excerpt Length:', 'advanced-categories-widget' ); ?>
					</label>
					<input class="widefat widgin-excerpt-length" id="<?php echo $this->get_field_id( 'desc_length' ); ?>" name="<?php echo $this->get_field_name( 'desc_length' ); ?>" type="number" step="1" min="0" value="<?php echo absint( $instance['desc_length'] ); ?>" />
				</p>

				<p>
					<?php _e( 'Preview:', 'advanced-categories-widget' ); ?><br />
					<span class="widgin-preview-container">
						<span class="widgin-excerpt-preview">
							<span class="widgin-excerpt"><?php echo wp_trim_words( _excerpt_sample(), 15, '&hellip;' ); ?></span>
							<span class="widgin-excerpt-sample" aria-hidden="true" role="presentation"><?php echo _excerpt_sample() ?></span>
						</span>
					</span>
				</p>
			</div>

		</fieldset>

	</div><!-- /.widgin-section -->

	<div class="widgin-section">

		<?php echo fieldset_header_html( $fieldset = 'template', $title = 'Template', $instance, $this ); ?>

		<fieldset data-fieldset-id="template" class="widgin-settings widgin-fieldset settings-template">

			<legend class="screen-reader-text"><span><?php _e('Template') ?></span></legend>

			<p>
				<input id="<?php echo $this->get_field_id( 'hierarchy' ); ?>" name="<?php echo $this->get_field_name( 'hierarchy' ); ?>" type="checkbox" <?php checked( $instance['hierarchy'], 1 ); ?> />
				<label for="<?php echo $this->get_field_id( 'hierarchy' ); ?>">
					<?php _e( 'Display Term Hierarchy?', 'advanced-categories-widget' ); ?>
				</label>
			</p>

			<p>
				<input id="<?php echo $this->get_field_id( 'expandable' ); ?>" name="<?php echo $this->get_field_name( 'expandable' ); ?>" type="checkbox" <?php checked( $instance['expandable'], 1 ); ?> />
				<label for="<?php echo $this->get_field_id( 'expandable' ); ?>">
					<?php _e( 'Expandable', 'advanced-categories-widget' ); ?>
				</label>
			</p>

			<p>
				<label for="<?php echo $this->get_field_id('list_style'); ?>">
					<?php _e( 'Template:', 'advanced-categories-widget' ); ?>
				</label>
				<select name="<?php echo $this->get_field_name('list_style'); ?>" id="<?php echo $this->get_field_id('list_style'); ?>" class="widefat">
					<option value="ul" <?php selected( $instance['list_style'] , 'ul' ); ?>><?php _e( 'Unordered List (ul)', 'advanced-categories-widget' ); ?></option>
					<option value="ol" <?php selected( $instance['list_style'] , 'ol' ); ?>><?php _e( 'Ordered List (ol)', 'advanced-categories-widget' ); ?></option>
					<option value="div" <?php selected( $instance['list_style'] , 'div' ); ?>><?php _e( 'Layer (div)', 'advanced-categories-widget' ); ?></option>
				</select>
			</p>

		</fieldset>

	</div><!-- /.widgin-section -->

</div>