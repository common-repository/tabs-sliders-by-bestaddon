<?php
/**
* @Copyright   Copyright (C) 2010 - 2023 BestAddon . All rights reserved.
* @license     GNU General Public License version 2 or later
* @link        http://www.bestaddon.com
**/
defined('ABSPATH') or die;

class BestAddonAdminSetting
{
    public $pluginFolder;
    public $pluginLabel;
    public $postType;
    public function __construct()
    {
        $this->postType = $this->pluginFolder = basename(dirname(dirname(dirname(__DIR__))));
        $this->pluginLabel = str_replace(['_', '-'], ' ', ucwords($this->postType));

        if (is_admin()) {
            add_action('admin_footer', [$this,'baex__admin_scripts'], 99);
            add_action('init', [$this,'baex__custom_post_type'], 1);
            add_action('add_meta_boxes', [$this, 'baex__meta_boxes_group']);
            //add_action('admin_init', array(&$this, 'wpsm_accordion_meta_boxes_group'), 1);
            add_action('save_post', [$this, 'baex__save_meta_box'], 9, 3);
            //add_action('save_post', array(&$this, 'accordion_settings_meta_box_save'), 9, 1);
            add_action('in_admin_header', [$this, 'baex__tabs_header_info']);

            add_filter('manage_edit-'.$this->postType.'_columns', [$this,'baex__edit_columns']);
            add_action('manage_'.$this->postType.'_posts_custom_column', [$this,'baex__manage_posts_custom_column'], 10, 2);
        }
    }

    // admin scripts
    public function baex__admin_scripts()
    {
        if (get_post_type() == $this->postType) {
            wp_enqueue_script('jquery');
            wp_enqueue_script('jquery-ui-core');

            $jqueryUi = explode(',', 'draggable.js, droppable.js, resizable.js, selectable.js, sortable.js, accordion.js, button.js, checkboxradio.js, controlgroup.js, dialog.js, mouse.js, tabs.js, tooltip.js');

            foreach ($jqueryUi as &$value) {
                wp_enqueue_script('jquery-ui-'.(str_replace([' ', '.js'], '', $value)));
            }

            $base_url = plugin_dir_url(__DIR__);
            //wp_enqueue_script('ba-jquery-ui', $base_url.'js/jquery-ui-custom.min.js');
            //wp_enqueue_script('ba-tinymce-js', includes_url().'js/tinymce/tinymce.min.js');
            wp_enqueue_script('ba-modadmin0-js', $base_url.'js/color-picker.js');
            wp_enqueue_script('ba-modadmin1-js', $base_url.'js/form.js');
            wp_enqueue_script('ba-modadmin2-js', $base_url.'js/admin.js');
            wp_enqueue_style('ba-modadmin-css', $base_url.'css/admin.css');
            wp_add_inline_script('ba-custom-js', 'var modName="'.$this->postType.'";');
        }
    }


    // Register Custom Post Type
    public function baex__custom_post_type()
    {
        $realTitle = str_replace(['ba_', 'ba-'], ' ', $this->postType);
        // Set the labels. This variable is used in the $args array
        $labels = array(
            'name'               => __($this->pluginLabel, $this->postType),
            'singular_name'      => __($this->pluginLabel, $this->postType),
            'menu_name'           => __($this->pluginLabel, $this->postType),
            'parent_item_colon'   => __('Parent Item:', $this->postType),
            'all_items'           => sprintf(__('All %s', $this->postType), $realTitle),
            'view_item'           => sprintf(__('View %s', $this->postType), $realTitle),
            'add_new_item'        => __('Add New', $this->postType),
            'add_new'             => __('Add New', $this->postType),
            'edit_item'           => __('Edit', $this->postType),
            'update_item'         => __('Update', $this->postType),
            'search_items'        => __('Search', $this->postType),
            'not_found' 			=> sprintf(__('No %s found', $this->postType), $realTitle),
            'not_found_in_trash' 	=> sprintf(__('No %s found in trash', $this->postType), $realTitle)
        );
        // The arguments for our post type, to be entered as parameter 2 of register_post_type()
        $args = array(
            'labels'            => $labels,
            'description'       => 'Our custom post specific data for plugin',
            'supports'          => array('title'),
            'hierarchical'        => false,
            'public'              => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'show_in_nav_menus'   => false,
            'show_in_admin_bar'   => false,
            'menu_position'       => 88,
            'menu_icon'           => 'data:image/svg+xml;base64,'.base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="512" height="512"><path stroke="#ccc" stroke-width="50" d="M308 58h178v60H308z"/><path fill="#fff" stroke="#0f0" stroke-width="25" d="M18 18h206.25v137.016l273.75.925v335.133L18 492V18z"/><path d="M107 236h300v89H107zm-1 133h300v48H106zM61 83h114v13H61z"/></svg>'),
            'can_export'          => true,
            'has_archive'         => true,
            'exclude_from_search' => false,
            'publicly_queryable'  => false,
            'capability_type'     => 'page',
        );
        // Call the actual WordPress function
        // Parameter 1 is a name for the post type
        // Parameter 2 is the $args array

        register_post_type($this->postType, $args);
    }


    public function baex__edit_columns($columns)
    {
        $columns = array(
            'cb' => '<input type="checkbox" />',
            'title' => __('Title'),
            'shortcode' => __('Shortcode'),
            'date' => __('Date')
        );
        return $columns;
    }

    public function baex__manage_posts_custom_column($column, $post_id)
    {
        if ($column == 'shortcode') {
            echo '<input type="text" onclick="this.select()" value=\'['.str_replace('-', '_', strtoupper($this->postType)).' id="'.esc_html($post_id).'"]\' readonly="readonly" />';
        }
    }

    public function baex__meta_boxes_group()
    {
        add_meta_box($this->postType.'_item-setting', __('Add Item'), [$this, 'baex__main_meta_box'], $this->postType, 'normal');
        add_meta_box($this->postType.'_shortcode', __('Shortcode'), [$this, 'baex__box_shortcode'], $this->postType, 'side');
    }

    public function baex__main_meta_box($post)
    {
        wp_nonce_field(basename(__FILE__), "baex__meta-box-nonce");
        $this->baex__get_input();
    }

    public function baex__box_shortcode()
    {
        echo '<label>'.__('Use below shortcode in any Page/Post').'</label>';
        echo '<input readonly="readonly" type="text" onclick="this.select()" value="'.esc_attr('['.str_replace('-', '_', strtoupper($this->postType)).' id="'.get_the_ID().'"]').'">';
    }


    public function baex__save_meta_box($post_id, $post, $update)
    {
        if (!isset($_POST["baex__meta-box-nonce"]) || !wp_verify_nonce($_POST["baex__meta-box-nonce"], basename(__FILE__))) {
            return $post_id;
        }

        if (!current_user_can("edit_post", $post_id)) {
            return $post_id;
        }

        if (defined("DOING_AUTOSAVE") && DOING_AUTOSAVE) {
            return $post_id;
        }

        if ($this->postType != $post->post_type) {
            return $post_id;
        }

        $content = wp_kses_normalize_entities($_POST[$this->postType.'-content']);
        update_post_meta($post_id, $this->postType.'-content', (!empty($content) ? $this->baex__json_fixer($content) : ''));
    }


    public function baex__json_fixer($jsonString)
    {
        //Fix line breaks and HTML tags in attribute of main tag
        $result = preg_replace_callback('/<[^>]+>/', function ($matche) {
            return '<'.strip_tags(trim(substr($matche[0], 1, -1))).'>';
        }, preg_replace(['/\r|\n|\t/', '/\s+/'], ' ', $jsonString));

        return $result;
    }


    // Add the box infor in the header of the site
    public function baex__tabs_header_info()
    {
        if (get_post_type()==$this->postType) {
            //echo 'BA TABS INFO';
        }
    }



    public function baex__get_input()
    {
        require_once dirname(__FILE__).'/form-render.php';
        $baRender = new BestAddonFormRender();
        wp_enqueue_media();
        wp_enqueue_editor();
        $getVal = get_post_meta(get_the_id(), $this->postType.'-content', true); //$this->form->getValue('content');
        $getVal = empty($getVal) ? $baRender->defalutData() : $getVal;
        $baData = json_decode($getVal, true);


        $baTabLabels = $baTabBody = $output = '';
        $output .= '<div id="ba-modal-preview" title="Preview"></div>
                        <div class="ba-manager clearfix" data-jversion="4.2.2" style="opacity:0"><a href="#" class="ba-preview hide">Preview &rarr;</a>';
        $output .= '<div class="para-tabs main-tabs" data-rel="tablist">';
        $output .= '<ul>';
        foreach (is_array($baData) ? $baData : [] as $key => $value) {
            $output .= '<li><a href="#'.$value['id'].$key.'">'.substr($value['id'], 0, -7).'</a></li>';
        }
        $output .= '</ul>';
        foreach (is_array($baData) ? $baData : [] as $key => $value) {
            $output .= '<div id="'.$value['id'].$key.'" class="'.$value['id'].'"><div '.(($value['id'] != 'data-source1') ? 'data-batype="'.$value['id'].'"' : '').'>';
            if ($value['id'] == 'data-source') {
                /////  DATA SOURCE /////////////////////
                $output .= '<div class="source-wrap">';
                $output .= '<div class="source-bar ba-controls clearfix">'.
                        $baRender->select('data-mode', 'Data source', ['source-basic'=>'Basic', 'source-article'=>(function_exists('__') ? 'Wordpress Posts' : 'Joomla Articles')], 'class="ba-input select-group" data-rel="button"').'
                        </div>';
                foreach (is_array($value['children']) ? $value['children'] : [] as $data) {
                    $output .= '<div class="data-mode-'.$data['id'].' '.$data['id'].' ba-controls data-content clearfix" data-batype="'.$data['id'].'">';
                    $output .= (is_array($data) ? $baRender->renderData($data) : '');
                    $output .= '</div>';
                }
                $output .= '</div>';
            }
            if ($value['id'] == 'setting-source') {
                $output .= $baRender->renderOptions();
            }
            $output .= '</div></div>';
        }
        $output .= '</div>';
        $output .= '</div>';
        $output .= '<textarea id="ba-form-content" name="'.$this->postType.'-content" style="display:none!important">'.esc_attr($getVal).'</textarea>';
        echo wp_kses_normalize_entities($output);
        //return $output;
    }
}
new BestAddonAdminSetting();
