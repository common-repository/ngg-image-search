<?php

namespace Imagely\FIS\DisplayType;

class Installer extends \C_Gallery_Display_Installer
{
    function install($reset = FALSE)
    {
        $this->install_display_types();
    }

    function install_display_types()
    {
        $this->install_display_type(
            NGG_FIS_DISPLAY_TYPE_ID,
            [
                'title'                 => __('Frontend Image Search', 'ngg-image-search'),
                'entity_types'          => ['image'],
                'default_source'        => 'galleries',
                'preview_image_relpath' => NGG_FIS_DISPLAY_TYPE_ID . '#preview.jpg',
                'hidden_from_ui'        => FALSE,
                'view_order'            => NGG_DISPLAY_PRIORITY_BASE + (NGG_DISPLAY_PRIORITY_STEP * 10) + 40,
                'aliases'               => []
            ]
        );
    }

    function uninstall($hard = FALSE)
    {
        $mapper = C_Display_Type_Mapper::get_instance();
        if (($entity = $mapper->find_by_name(NGG_FIS_DISPLAY_TYPE_ID)))
        {
            if ($hard)
            {
                $mapper->destroy($entity);
            }
            else {
                $entity->hidden_from_ui = TRUE;
                $mapper->save($entity);
            }
        }
    }
}