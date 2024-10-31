<?php
/*
 * Plugin Name: Image Search for NextGen Gallery
 * Description: Adds a searchable display type to NextGen Gallery
 * Version: 0.01
 * Plugin URI: https://wordpress.org/support/plugin/ngg-image-search
 * Author: Benjamin Owens
 * Author URI: https://x1e.us/
 * License: GPLv2
 * Text Domain: ngg-image-search
 */

class NGG_Image_Search
{
    public static $plugin_name = 'Image Search for NextGen Gallery';

    protected static $minimum_php_version = '5.6.0';
	protected static $minimum_ngg_version = '3.0.1';
	protected static $product_loaded      = FALSE;

	private static $nextgen_found   = FALSE;
	private static $nextgen_version = '0.0';

    public function __construct()
    {
        define('NGG_FIS_PLUGIN_VERSION', '0.01');
        define('NGG_FIS_PLUGIN_BASENAME', plugin_basename(__FILE__));

        if (defined('NGG_PLUGIN_VERSION'))
        {
            self::$nextgen_found = TRUE;
            self::$nextgen_version = constant('NGG_PLUGIN_VERSION');
        }

	    if (!$this->is_activating()
        &&  $this->check_min_php_version()
        &&  $this->check_min_ngg_version())
	    {
            spl_autoload_register(array($this, 'autoload'));

			$ngg_activated = class_exists('C_NextGEN_Bootstrap');
			$ngg_modules_initialized = did_action('load_nextgen_gallery_modules');
			if (!$ngg_activated && !$ngg_modules_initialized)
			{
				add_action('load_nextgen_gallery_modules', array($this, 'load_product'));
			}
			else {
			    $this->load_product(NULL, $ngg_activated, $ngg_modules_initialized);
            }
	    }

	    $this->_register_hooks();
    }

    /**
     * @param string $class
     */
    public function autoload($class)
    {
        $prefix = 'Imagely\\FIS\\';
        $namespaces = explode('\\', $class);
        if (count($namespaces) < 2)
            return;
        if ($namespaces[0] !== 'Imagely' && $namespaces[1] !== 'FIS')
            return;

        $class2 = substr($class, strlen($prefix));
        $location = __DIR__ . '/product/modules/' . str_replace('\\', '/', $class2) . '.php';

        if (is_file($location))
        {
            require_once($location);
        }
    }

    /**
     * @param null|\C_Component_Registry $registry
     * @param true|bool $ngg_activated
     * @param false|bool $ngg_modules_loaded
     * @return bool
     */
    public function load_product($registry = NULL, $ngg_activated = TRUE, $ngg_modules_loaded = FALSE)
    {
	    if (!self::$product_loaded)
	    {
		    // version mismatch: do not load
		    if (!defined('NGG_PLUGIN_VERSION') || version_compare(NGG_PLUGIN_VERSION, self::$minimum_ngg_version) == -1)
			    return FALSE;

		    if (!$registry)
		        $registry = C_Component_Registry::get_instance();
			$dir = dirname(__FILE__);
			$registry->add_module_path($dir, 3, FALSE);
			$registry->load_all_products();
            $registry->initialize_all_modules();
		    $retval = self::$product_loaded = TRUE;
	    }
	    else {
		    $retval = self::$product_loaded;
	    }

	    return $retval;
    }

    /**
     * Used to prevent the loading of product & modules when viewing these pages in the admin
     *
     * @return bool
     */
	public function is_activating()
	{
	    $retval = FALSE;

	    if (!is_admin())
	        return $retval;

	    $pages = array('plugins.php', 'update.php');
        foreach ($pages as $page) {
	        if (strpos($_SERVER['REQUEST_URI'], $page) !== FALSE)
	            $retval = TRUE;
        }

        return $retval;
	}

    public function _register_hooks()
    {
        add_action('admin_notices', array($this, 'admin_notices'));

        add_action('plugins_loaded', function() {
            load_plugin_textdomain(
                'ngg-image-search',
                FALSE,
                basename(dirname(__FILE__)) . '/lang/'
            );
        });
    }

    /**
     * @return bool
     */
    public function check_min_php_version()
    {
        return version_compare(phpversion(), self::$minimum_php_version) !== -1;
    }

    /**
     * @return bool
     */
    public function check_min_ngg_version()
    {
        return version_compare(self::$nextgen_version, self::$minimum_ngg_version) !== -1;
    }

    public function admin_notices()
    {
        // NextGen Gallery is not installed
        if (!self::$nextgen_found)
        {
            $message = sprintf(
                __(
                    'Please install &amp; activate <a href="http://wordpress.org/plugins/nextgen-gallery/" target="_blank">NextGEN Gallery</a> to allow %s to work.',
                    'ngg-image-search'
                ),
                self::$plugin_name
            );
            print "<div class='updated'><p>{$message}</p></div>";
        }
        // NextGen Gallery is not up to date
		else if (!$this->check_min_ngg_version())
		{
			$ngg_fis_version = NGG_FIS_PLUGIN_VERSION;
			$upgrade_url = admin_url('/plugin-install.php?tab=plugin-information&plugin=nextgen-gallery&section=changelog&TB_iframe=true&width=640&height=250');
			$message = sprintf(
                __(
                    'NextGEN Gallery version %s is incompatible with %s version %s. Please update <a class="thickbox" href="%s">NextGEN Gallery</a> to version %s or higher.',
                    'ngg-image-search'
                ),
                self::$nextgen_version,
                self::$plugin_name,
                $ngg_fis_version,
                $upgrade_url,
                self::$minimum_ngg_version
            );
			print "<div class='updated'><p>{$message}</p></div>";
		}
		// PHP is not up to date
        else if (!$this->check_min_php_version())
        {
            $message = sprintf(
                __(
                    '%s will not function with PHP version %s. Please upgrade your PHP to version %s or higher.',
                    'ngg-image-search'
                ),
                self::$plugin_name,
                phpversion(),
                self::$minimum_php_version
            );
            print "<div class='updated'><p>{$message}</p></div>";
        }
    }
}

new NGG_Image_Search();