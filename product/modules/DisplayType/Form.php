<?php

namespace Imagely\FIS\DisplayType;

/**
 * @mixin \C_Form
 * @adapts \I_Form using NGG_FIS_DISPLAY_TYPE_ID context
 */
class Form extends \Mixin_Display_Type_Form
{
    function get_display_type_name()
    {
        return NGG_FIS_DISPLAY_TYPE_ID;
    }

    function enqueue_static_resources()
    {
        \wp_enqueue_script(
            $this->object->get_display_type_name() . '-js',
            $this->get_static_url(NGG_FIS_DISPLAY_TYPE_ID . '#admin.js')
        );

        \C_Attach_Controller::get_instance()->mark_script($this->object->get_display_type_name() . '-js');
    }

    function _get_field_names()
    {
        return [];
    }
}