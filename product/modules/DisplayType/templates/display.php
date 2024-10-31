<?php $this->start_element('nextgen_gallery.gallery_container', 'container', $displayed_gallery); ?>
    <div class="<?php print NGG_FIS_DISPLAY_TYPE_ID; ?>-search">
        <form method="GET" action="">
            <input type="text"
                   placeholder="<?php esc_attr_e($i18n['placeholder']); ?>"
                   name="ngg_fis_search"
                   value="<?php esc_attr_e($term); ?>"/>
            <input type="submit"
                   value="<?php esc_attr_e($i18n['search']); ?>"/>
        </form>
    </div>
    <div class="<?php print NGG_FIS_DISPLAY_TYPE_ID; ?>">
        <?php
        $this->start_element('nextgen_gallery.image_list_container', 'container', $images);
            foreach ($images as $image) {
                $this->start_element('nextgen_gallery.image_panel', 'item', $image); ?>
                        <?php $this->start_element('nextgen_gallery.image', 'item', $image); ?>
                            <a href="<?php print esc_attr($storage->get_image_url($image))?>"
                               title="<?php print esc_attr($image->description)?>"
                               data-src="<?php print esc_attr($storage->get_image_url($image)); ?>"
                               data-thumbnail="<?php print esc_attr($storage->get_image_url($image, 'thumb')); ?>"
                               data-image-id="<?php print esc_attr($image->{$image->id_field}); ?>"
                               data-title="<?php print esc_attr($image->alttext); ?>"
                               data-description="<?php print esc_attr(stripslashes($image->description)); ?>"
                               <?php print $effect_code ?>>
                                <?php M_NextGen_PictureFill::render_picture_element($image, 'full'); ?>
                            </a>
                        <?php $this->end_element(); ?>
                <?php
                $this->end_element();
            }
        $this->end_element(); ?>
    </div>
<?php $this->end_element(); ?>