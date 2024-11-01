<?php
/*
Plugin Name: Tabs & Sliders by BestAddon
Plugin URI: https://bestaddon.com/product/ba-tabs/
Description: A minimal, clean, responsive tabs plugin built with CSS and Vanilla JavaScript.
Version: 1.0.0
Author: BestAddon
Author URI: https://bestaddon.com
*/
defined('ABSPATH') or die;

class PlgBestAddonTabs
{
    public $postType;
    public $shortcodeName;
    public function __construct()
    {
        $this->postType = basename(dirname(__FILE__));
        $this->shortcodeName = str_replace('-', '_', strtoupper($this->postType));

        /**
         * DEFINE PATHS
         */
        define($this->shortcodeName.'_BASENAME', plugin_basename(__FILE__));
        define($this->shortcodeName.'_TABS_PATH', plugin_dir_path(__FILE__));
        define($this->shortcodeName.'_URL', plugins_url('/', __FILE__));
        define($this->shortcodeName.'_VERSION', '1.0.0');
        define($this->shortcodeName.'_TEXTDOMAIN', $this->postType);

        /**
         * INCLUDE FILES
         */
        require_once dirname(__FILE__).'/assets/admin/fields/basetting.php';
        require_once dirname(__FILE__).'/helper.php';
        require_once dirname(__FILE__).'/widget.php';


        /**
         * SHORTCODE
         */
        add_shortcode($this->shortcodeName, [$this, 'baex__shortcode_display']);


        /**
         * PLUGIN LOAD DATA
         */
        add_action('plugins_loaded', [$this, 'baex__textdomain']);
        // Ajax to preview
        add_action('wp_ajax_nopriv_ba_preview', [$this, 'baex__render_preview']);
        add_action('wp_ajax_ba_preview', [$this, 'baex__render_preview']);


        // Display shortcode in widgets
        add_filter('widget_text', 'do_shortcode');
        add_action('widgets_init', [$this,'baex__load_widget']);
    }

    // Translate for the plugin
    public function baex__textdomain()
    {
        load_plugin_textdomain($this->shortcodeName.'_TEXTDOMAIN', false, plugin_basename(dirname(__FILE__)).'/language/');
    }

    // Register and load the widget
    public function baex__load_widget()
    {
        register_widget('WidgetBestAddonTabs');
    }


    // Render preview
    public function baex__render_preview()
    {
        $id = (int) $_POST['id'];
        $ajaxData = wp_kses_post($_POST['ba-form-content']);
        echo $this->baex__output($id, json_decode(stripslashes($ajaxData), true));
        exit();
    }

    public function baex__shortcode_display($atts, $content = null)
    {
        $atts = shortcode_atts(['id' => ''], $atts);
        $itemId = (int) $atts['id'];
        return '<div id="ba-post-'.esc_attr($itemId).'">
                    <i class="screen-reader-text">'.$this->shortcodeName.' '.$itemId.'</i>'. $this->baex__output($itemId) .'</div>';
    }

    public function baex__output($setId, $ajaxData = '')
    {
        $helper = new PlgBestAddonHelper(); // Call Helper class
        $moduleid = !empty($setId) ? $setId : 0;
        $modID = 'modID'.$moduleid;
        $modName = $this->postType;

        $data = get_post_meta((int)$setId, $this->postType.'-content', true);
        $modData = !empty($ajaxData) ? $ajaxData : json_decode($data, true);

        $jList = $helper::getList($modData, $params);
        $helper::getobj($modData, 'data-mode', $dataSelect);
        $helper::getobj($modData, $dataSelect, $dataBasic);
        $helper::getobj($modData, 'setting-source', $setting);

        // RENDER VARIABLES BY ARRAY AND GET DATA OBJECT
        $css .= str_replace(['{ID}', '[ID]', 'ID'], $modID, $setting['tagCSS']);

        // CHECK AJAX BY PREVIEW($ajaxData) & SITE
        $assetPath = plugins_url('/', __FILE__).'assets/front/';
        $listCss = [
            $modName.'-css'=>$assetPath.'css/styles.css'
        ];
        $listJs = [
            'ba-tabs-js'=>$assetPath.'js/ba-tabs.js'
        ];
        $listAsset = array_merge($listCss, $listJs); //'/assets/front/'
        $helper::assets($listAsset, empty($ajaxData) ? true : false);
        $helper::assets((string)$css, empty($ajaxData) ? true : false);


        $options = '{'.
            '"width":"'.(string)$helper::is($setting['width']).'"'.
            ',"height":"'.(string)$helper::is($setting['height']).'"'.
            ',"horizontal":'.(int)$helper::is($setting['displayMode']).
            ',"defaultid":'.((int)$helper::is($setting['defaultId']) - 1).
            ',"speed":"'.(string)$helper::is($setting['speed']).'"'.
            ',"event":"'.(string)$helper::is($setting['trigger']).'"'.
        '}';
        $list = ($dataSelect == "source-article" && $jList) ? $jList : $dataBasic['children'];
        $tabNavs = '';
        $tabPanels = '';
        foreach ($list ?: [] as $key => $item) {
            $title = isset($item->title) ? '<span>'.$item->title.'</span>' : '<span>'.$item['header'].'</span>';
            $tabNavs .= '<li rel="ba--title"><a role="tab" href="#tabs-'.$modID.'-'.$key.'">'.$title.'</a></li>';
            $tabPanels .= '<li id="tabs-'.$modID.'-'.$key.'">
                            <h4>'.wp_kses_post($title).'</h4>
                            <div class="ba__tabs-content"><div rel="ba--description">'.wp_kses_post((isset($item->maintext) ? $item->maintext : $helper::isMod($item['main'], $moduleid))).'</div></div>
                        </li>';
        }
        $html = '<div class="baContainer clearfix '.$setting['tagClass'].' '.(empty($ajaxData) ? '' : 'ba-dialog-body').'">'.
                '<div data-id="ba-'.$modID.'-wrap" style="display:none">'.
                    '<article id="'.$modID.'" class="ba--general ba__tabs '.($helper::is($setting['styleMode']) ? 'custom' : '').'" data-ba-tabs data-options="'.htmlentities($options, ENT_QUOTES).'">';
        $html .=        '<ul id="nav'.$modID.'" class="ba__tabs-nav" role="tablist">'.$tabNavs.'</ul>'.
                        '<ul id="body'.$modID.'" class="ba__tabs-panel">'.$tabPanels.'</ul>';
        $html .=    '</article>
        </div>
    </div>';


        return $html;
    }
}
new PlgBestAddonTabs();
