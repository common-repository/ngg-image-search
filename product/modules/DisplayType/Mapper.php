<?php

namespace Imagely\FIS\DisplayType;

/**
 * @mixin \C_Display_Type_Mapper
 * @adapts \I_Display_Type_Mapper
 */
class Mapper extends \Mixin
{
    function set_defaults($entity)
    {
        $this->call_parent('set_defaults', $entity);

        if ($entity->name == NGG_FIS_DISPLAY_TYPE_ID)
        {
            $this->object->_set_default_value($entity, 'settings', 'is_ecommerce_enabled', FALSE);
        }
    }
}
