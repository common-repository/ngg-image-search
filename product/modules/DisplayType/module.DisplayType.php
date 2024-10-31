<?php
/* {
    Module: imagely-ngg-image-search
} */

namespace Imagely\FIS\DisplayType;

define('NGG_FIS_DISPLAY_TYPE_ID', 'imagely-ngg-image-search');

class Module extends \C_Base_Module
{
    function define($id          = 'pope-module',
                    $name        = 'Pope Module',
                    $description = '',
                    $version     = '',
                    $uri         = '',
                    $author      = '',
                    $author_uri  = '',
                    $context     = FALSE)
    {
        parent::define(
            NGG_FIS_DISPLAY_TYPE_ID,
            'Frontend Image Search',
            "Provides a display type allowing users to search NextGen Gallery images",
            '0.1',
            'https://www.imagely.com/wordpress-gallery-plugin/',
            'Imagely',
            'https://www.imagely.com',
            $context
        );

        \C_Photocrati_Installer::add_handler(NGG_FIS_DISPLAY_TYPE_ID, __NAMESPACE__ . '\\Installer');
    }

    function _register_adapters()
    {
        $registry = $this->get_registry();
        $registry->add_adapter('I_Display_Type_Mapper', __NAMESPACE__ . '\\Mapper');

        if (\M_Attach_To_Post::is_atp_url() || is_admin())
            $registry->add_adapter('I_Form', __NAMESPACE__ . '\\Form', NGG_FIS_DISPLAY_TYPE_ID);

        if (!is_admin())
            $registry->add_adapter('I_Display_Type_Controller', __NAMESPACE__ . '\\Controller', NGG_FIS_DISPLAY_TYPE_ID);
    }

    function initialize()
    {
        parent::initialize();

        if (\M_Attach_To_Post::is_atp_url() || is_admin())
            \C_Form_Manager::get_instance()->add_form(NGG_DISPLAY_SETTINGS_SLUG, NGG_FIS_DISPLAY_TYPE_ID);
    }
}

new Module();