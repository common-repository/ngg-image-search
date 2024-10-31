<?php

namespace Imagely\FIS\DisplayType;

/**
 * @mixin \C_Display_Type_Controller
 * @adapts \I_Display_Type_Controller using NGG_FIS_DISPLAY_TYPE_ID context
 */
class Controller extends \Mixin
{
    protected static $recursed = FALSE;

    /**
     * @param \C_Displayed_Gallery $displayed_gallery
     */
    public function enqueue_frontend_resources($displayed_gallery)
    {
        $this->call_parent('enqueue_frontend_resources', $displayed_gallery);

        \wp_enqueue_style(
            'nextgen_gallery_frontend_image_search_style',
            $this->get_static_url(NGG_FIS_DISPLAY_TYPE_ID . '#display.css')
        );
        \wp_enqueue_script(
            'nextgen_gallery_frontend_image_search_script',
            $this->get_static_url(NGG_FIS_DISPLAY_TYPE_ID . '#display.js'),
            [],
            FALSE,
            TRUE
        );

        $this->enqueue_ngg_styles();
    }

    /**
     * @param \C_Displayed_Gallery $displayed_gallery
     * @param bool $return
     * @return string
     */
    public function index_action($displayed_gallery, $return = FALSE)
    {
        $term = $this->param('ngg_fis_search');
        if (!empty($term) && !self::$recursed)
        {
            $result = array_unique(array_merge(
                $this->search_images($term),
                $this->search_tags($term)
            ));

            if (!empty($result))
            {
                self::$recursed = TRUE;
                $renderer = \C_Displayed_Gallery_Renderer::get_instance('inner');
                $gallery_params = [
                    'source'       => 'images',
                    'image_ids'    => $result,
                    'display_type' => $displayed_gallery->display_type,
                ];

                return $renderer->display_images($gallery_params, $return);
            }
        }

        $params = [
            'term'        => $term,
            'images'      => $displayed_gallery->get_included_entities(),
            'storage'     => \C_Gallery_Storage::get_instance(),
            'i18n'        => $this->get_i18n(),
            'effect_code' => $this->object->get_effect_code($displayed_gallery)
        ];

        $params = $this->object->prepare_display_parameters($displayed_gallery, $params);
        return $this->render_partial(NGG_FIS_DISPLAY_TYPE_ID . '#display', $params, $return);
    }

    public function get_i18n()
    {
        return [
            'placeholder' => __('Search term', 'ngg-image-search'),
            'search'      => __('Search', 'ngg-image-search')
        ];
    }

    /**
     * Retrieve images by tag name
     *
     * Taken from nggallery's lib/tags.php and modified to only return image ID
     * @param string[] $taglist
     * @param string $mode One of 'ASC', 'DESC' or 'RAND'
     * @return \C_Image[]
     */
    public function search_tags($taglist, $mode = "ASC")
    {
        global $wpdb;

        // extract it into a array
        $taglist = explode(",", $taglist);

        if (!is_array($taglist)) {
            $taglist = [$taglist];
        }

        $taglist       = array_map('trim', $taglist);
        $new_slugarray = array_map('sanitize_title', $taglist);
        $sluglist      = implode("', '", $new_slugarray);

        // Treat % as a literal in the database, for unicode support
        $sluglist = str_replace("%", "%%", $sluglist);

        // Get all $term_ids with this tag
        $term_ids = $wpdb->get_col(
            $wpdb->prepare("SELECT `term_id` FROM {$wpdb->terms} WHERE `slug` IN (%s) ORDER BY `term_id` ASC ", $sluglist)
        );
        $picids = get_objects_in_term($term_ids, 'ngg_tag');

        if ($mode == 'RAND') {
            shuffle($picids);
        }

        if ('DESC' == $mode) {
            $picids = array_reverse($picids);
        }

        return $picids;
    }

    /**
     * Retrieve images by non-tag attributes
     *
     * Taken from NextGen Gallery and modified to only return image ID
     * @param string $request
     * @param int $limit
     * @return array
     */
    function search_images($request, $limit = 0)
    {
        global $wpdb;

        // If a search pattern is specified, load the posts that match
        if (empty($request))
        {
            return [];
        }

        // Added slashes screw with quote grouping when done early, so done later
        $request = stripslashes($request);

        // Split the words into an array if separated by a space or comma
        preg_match_all('/".*?("|$)|((?<=[\\s",+])|^)[^\\s",+]+/', $request, $matches);
        $search_terms = array_map(function($str) { return trim($str, "\"'\n\r"); }, $matches[0]);

        $n         = '%';
        $searchand = '';
        $search    = '';

        foreach ((array)$search_terms as $term) {
            $term = addslashes_gpc($term);
            $search .= "{$searchand}((tt.description LIKE '{$n}{$term}{$n}') OR (tt.alttext LIKE '{$n}{$term}{$n}') OR (tt.filename LIKE '{$n}{$term}{$n}'))";
            $searchand = ' AND ';
        }

        $term = esc_sql($request);
        if (count($search_terms) > 1 && $search_terms[0] != $request)
        {
            $search .= " OR (tt.description LIKE '{$n}{$term}{$n}') OR (tt.alttext LIKE '{$n}{$term}{$n}') OR (tt.filename LIKE '{$n}{$term}{$n}')";
        }

        if (!empty($search))
        {
            $search = " AND ({$search}) ";
        }

        $limit_by  = ($limit > 0) ? 'LIMIT ' . intval($limit) : '';

        // Build the final query
        $query = "SELECT `tt`.`pid` FROM `{$wpdb->nggallery}` AS `t` INNER JOIN `{$wpdb->nggpictures}` AS `tt` ON `t`.`gid` = `tt`.`galleryid` WHERE 1=1 {$search} ORDER BY `tt`.`pid` ASC {$limit_by}";

        return $wpdb->get_col($query);
    }
}
