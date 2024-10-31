<?php
/***
	{
		Product: imagely-ngg-image-search,
		Depends: { photocrati-nextgen }
	}
***/

namespace Imagely\FIS;

class Product extends \C_Base_Product
{
	static $modules = [];

    function define_modules()
    {
        self::$modules = ['imagely-ngg-image-search'];
    }

	function define($id = 'pope-product',
                    $name = 'Pope Product',
                    $description = '',
                    $version = '',
                    $uri = '',
                    $author = '',
                    $author_uri = '',
                    $context = FALSE)
	{
		parent::define(
			'imagely-ngg-image-search-product',
			'NextGEN Gallery Frontend Image Search',
			'NextGEN Gallery Frontend Image Search',
            NGG_FIS_PLUGIN_VERSION,
			'https://www.imagely.com',
			'Imagely',
			'http://www.imagely.com'
		);

		$module_path = path_join(dirname(__FILE__), 'modules');
		$registry = $this->get_registry();
		$registry->set_product_module_path($this->module_id, $module_path);
        $this->define_modules();

		foreach (self::$modules as $module_name) {
		    $registry->load_module($module_name);
        }

		\C_Photocrati_Installer::add_handler($this->module_id, __NAMESPACE__ . '\\ProductInstaller');
	}
}

class ProductInstaller
{
    function install_display_types()
    {
        foreach (Product::$modules as $module_name) {
            if (($handler = \C_Photocrati_Installer::get_handler_instance($module_name)))
            {
                if (method_exists($handler, 'install_display_types'))
                    $handler->install_display_types();
            }
        }
    }

    function uninstall($hard=FALSE)
    {
        foreach (Product::$modules as $module_name) {
            if (($handler = \C_Photocrati_Installer::get_handler_instance($module_name)))
            {
                if (method_exists($handler, 'uninstall'))
                    $handler->uninstall($hard);

            }
        }
    }
}

new Product();
