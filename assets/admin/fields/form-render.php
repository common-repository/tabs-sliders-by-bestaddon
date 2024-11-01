<?php
/**
* @Copyright   Copyright (C) 2010 - 2021 BestAddon . All rights reserved.
* @license     GNU General Public License version 2 or later
* @link        http://www.bestaddon.com
**/
defined('ABSPATH') or die;
require_once(dirname(__FILE__).'/form-helper.php');
class BestAddonFormRender
{
    //////////////////// BEGIN CLASS ////////////////////
    use BestAddonFormElements;

    //////////////////////////////////
    ///////// BEGIN DATA ///////////////////////
    /////////////////////////////////
    public static function renderData($node)
    {
        return $node['id'] == 'source-basic' ? self::renderDataBasic($node) : self::renderDataArticle($node);
    }

    public static function renderDataBasic($node)
    {
        //self::input('image', self::l('IMAGE'), ($values['image']?:''), 'class="ba-input width-lg" data-rel="media"')
        //self::select('icon', self::l('ICON'), self::fontAwesome(), 'data-rel="dropdown" title="fontWeASome"', (array)($values['icon']?:''))
        $html = '<header class="ba-header panel-heading clearfix"><i class="btn ba__basic-add">'.self::l('ADD_NEW_ITEM').'</i></header><div class="ba__basic_data">';
        foreach (isset($node['children']) && is_array($node['children']) ? $node['children'] : [] as $i => $values) {
            $html .= '<div class="ba-group accordion-basic clearfix"><h3 class="panel-heading"><i class="ba__move">&nbsp;</i><span>'.$values['header'].'</span><i class="ba__button ba__edit tooltip" title="Edit">&#x270E;</i><i class="ba__button ba__clone tooltip" title="Clone">&#x2398</i><i class="ba__button ba__remove tooltip" title="Remove">&times;</i></h3><div class="panel-body"><div class="ba-controls clearfix">'.'
            '.self::fieldset_open('class="ba__header-group group-auto-width"', self::l('TITLE')).'
                '.self::group_open('group-inline').'
                    '.self::input('header', self::l('MAIN_TITLE'), (!empty($values['header']) ? $values['header'] : '')).'
                    '.self::input('subheader', self::l('SUB_TITLE'), (!empty($values['subheader']) ? $values['subheader'] : '')).'
                '.self::group_close().'
            '.self::fieldset_close().'
            '.self::fieldset_open('class="ba__icon-group"', self::l('ICON')).'          
                '.self::select('iconMode', self::l('TYPE'), [''=>'None','icon'=>'Icon','image'=>'Image'], 'class="ba-input select-group" data-rel="button"', (array)(!empty($values['iconMode']) ? $values['iconMode'] : '')).'
                <div class="ba-subcontrols iconMode-icon">
                '.self::select('icon', self::l('ICON'), self::fontAwesome(1), 'data-rel="dropdown" title="fontWeASome"', (array)(!empty($values['icon']) ? $values['icon'] : 'fab fa-None')).'
                </div>
                <div class="ba-subcontrols iconMode-image">
                '.self::input('image', self::l('IMAGE'), (!empty($values['image']) ? $values['image'] : ''), 'class="ba-input width-lg" data-rel="media"').'
                </div>'.'
            '.self::fieldset_close().'
            '.self::textarea('main', self::l('CONTENT', 1), (!empty($values['main']) ? $values['main'] : ''), 'class="ba-input ba-editor"').'
            </div></div></div>';
        }
        $html .='</div>';
        return $html;
    }
    public static function renderDataArticle($node)
    {
        $tags = get_terms('post_tag', ['fields' => 'names']);
        return self::select('article_type', self::l('POST_TYPE', 1), ["post"=>"Post","page"=>"Page"], 'class="ba-input select-group" data-rel="button"').'
                '.'<div class="ba-subcontrols article_type-post">
                    <div class="ba-control clearfix">
                        <label>'.self::l('SELECT_CATEGORY', 1).'</label>
                        '.preg_replace('/<select/', '<select data-name="catid" multiple="multiple"', wp_dropdown_categories(['name'=>'catid','class'=>'ba-input','echo'=>0, 'hierarchical' => 1])).'
                    </div>'.'
                    '.self::select('child_category', self::l('CHILD_CATEGORY', 1), ["1"=>self::l('YES'),"0"=>self::l('NO')], 'data-rel="button"').'
                </div>
                '.self::select('author_filtering_type', self::l('AUTHOR_FILTERING', 1), ["1"=>self::l('INCLUSIVE'),"0"=>self::l('EXCLUSIVE')], 'data-rel="button"').'
                '.'<div class="ba-control clearfix">
                    <label>'.self::l('AUTHORS', 1).'</label>
                    '.preg_replace('/<select/', '<select data-name="created_by" multiple="multiple"', wp_dropdown_users(['name'=>'userid','class'=>'ba-input','echo'=>0,'who'=>'authors'])).'
                </div>'.'
                '.self::input('article_ids', self::l('EXCLUDED_POSTS', 1), '', 'class="ba-input width-md" placeholder="1,2,3"').'
                '.'<div class="ba-subcontrols article_type-post">
                    '.self::select('article_tags', self::l('POST_TAGS', 1), self::arrayCombine($tags), 'multiple').'
                '.'</div>'.'
                '.'<hr/>'.'
                '.self::select('article_ordering', self::l('ORDER_BY', 1), ["name"=>'Name',"author"=>'Author',"title"=>self::l('TITLE'),"ID"=>self::l('ID'),"date"=>self::l('CREATEDDATE'),"modified"=>self::l('MODIFIEDDATE'),"rand"=>self::l('RANDOM')]).'
                '.self::select('article_direction', self::l('ORDERING_DIRECTION', 1), ["ASC"=>self::l('ASCENDING'),"DESC"=>self::l('DESCENDING')], 'data-rel="button"').'
                '.'<hr/>'.'
                '.self::input('count', self::l('MAX_OF_ITEMS', 1), '5', 'data-rel="spinner" data-no-unit="1"').'
                '.self::select('show_title', self::l('TITLE', 1), ["1"=>self::l('YES'),"0"=>self::l('NO')], 'data-rel="button"').'
                '.self::select('show_date', self::l('DATE', 1), ["1"=>self::l('SHOW'),"0"=>self::l('HIDE')], 'data-rel="button"').'
                '.self::select('show_category', self::l('CATEGORY', 1), ["1"=>self::l('SHOW'),"0"=>self::l('HIDE')], 'data-rel="button"').'
                '.self::select('show_author', self::l('AUTHOR', 1), ["1"=>self::l('SHOW'),"0"=>self::l('HIDE')], 'data-rel="button"').'
                '.self::select('show_introtext', self::l('POST_CONTENT', 1), ["1"=>self::l('SHOW'),"0"=>self::l('HIDE')], 'class="ba-input select-group" data-rel="button"').'
                '.'<div class="ba-subcontrols show_introtext-1">
                    '.self::input('introtext_limit', self::l('POST_CONTENT_LIMIT', 1), '36', 'data-rel="spinner" data-no-unit="1"').'
                </div>'.'
                '.self::select('show_readmore', self::l('READMORE', 1), ["1"=>self::l('SHOW'),"0"=>self::l('HIDE')], 'class="ba-input select-group" data-rel="button"').'
                '.'<div class="ba-subcontrols show_readmore-1">
                    '.self::input('readmore_text', self::l('READMORETEXT', 1), 'Read more ->', 'class="ba-input width-md"').'
                </div>';
    }
    //////////////////////////////////
    ///////// END DATA ///////////////////////
    /////////////////////////////////



    //////////////////////////////////
    ///////// BEGIN OPTIONS ///////////////////////
    /////////////////////////////////
    public static function renderOptions()
    {
        $output = self::fieldset_open('class="ba-controls group-inline"', self::l('OPTIONS')).'
        '.self::input('width', self::l('WIDTH', 1), '100%', 'data-rel="range" max="500"').'
        '.self::input('height', self::l('HEIGHT', 1), 'auto', 'data-rel="range" max="500"').'
        '.self::select('displayMode', self::l('MODE', 1), ["0"=>"Vertical","1"=>"Horizontal"], 'data-rel="button"').'
        '.self::select('position', self::l('POSITION', 1), ["default"=>"Default","reverse"=>"Reverse"], 'data-rel="button"').'
        '.self::select('align', self::l('ALIGNMENT', 1), ["start"=>"Start","center"=>"Center","end"=>"End"], 'data-rel="button"').'
        '.self::select('effect', self::l('EFFECT', 1), self::cssAnimation()).'
        '.self::select('nextPrev', self::l('NAVIGATION', 1), ["0"=>self::l('NO'), "1"=>self::l('YES')], 'data-rel="button"').'
        '.self::select('keyNav', self::l('KEYNAV', 1), ["0"=>self::l('NO'), "1"=>self::l('YES')], 'data-rel="button"').'
        '.self::input('defaultId', self::l('DEFAULT_ITEM', 1), '1', 'data-rel="spinner" data-no-unit="1"').'
        '.self::select('trigger', self::l('TRIGGER', 1), ["click"=>"Click","mouseenter"=>"Mouse Over"], 'data-rel="button"').'
        '.self::input('speed', self::l('SPEED', 1), '900ms', 'data-rel="range" max="5000" data-unit="ms"').'
        '.self::select('autoPlay', self::l('AUTOPLAY', 1), ["0"=>self::l('NO'),"1"=>self::l('YES')], 'class="ba-input select-group" data-rel="button"').'
        '.self::group_open('autoPlay-1 ba-sub-controls').'
        '.self::input('autoplayDelay', self::l('AUTOPLAY_DELAY', 1), '3000ms', 'data-rel="range" max="15000" data-unit="ms"').'
        '.self::select('pauseOnHover', self::l('PAUSE_ON_HOVER', 1), ["0"=>self::l('NO'), "1"=>self::l('YES')], 'data-rel="button"').'
        '.self::group_close().'
        '.self::select('breakPoint', self::l('BREAKPOINT_LAYOUT', 1), self::arrayCombine(['default', 'accordion','dropdown'])).'
        '.self::input('breakPointWidth', self::l('BREAKPOINT', 1), '576', 'data-rel="range" max="1500" data-no-unit').'
        '.self::fieldset_close();
        $output .= self::fieldset_open('class="ba-controls group-inline"', self::l('SKINS')).'            
            '.self::select('style', self::l('SKIN'), self::arrayCombine(explode(',', 'style'.implode(',style', range(1, 1)))), 'data-rel="image"').'
            '.self::select('styleMode', self::l('SKIN_EDIT', 1), ["0"=>self::l('NO'),"1"=>self::l('YES')], 'class="ba-input select-group" data-rel="button"').'
                <div class="styleMode-1 edit-style">
                '.self::fieldset_open('class="style-custom" data-batype="title"', self::l('TITLE').'<b><i class="ba-css-action active">Normal</i><i class="ba-css-action hover">Active</i></b>').'
                    '.self::customStyle(1).'
                '.self::fieldset_close().'
                '.self::fieldset_open('class="style-custom" data-batype="icon"', self::l('ICON').'<b><i class="ba-css-action active">Normal</i><i class="ba-css-action hover">Active</i></b>').'
                    '.self::input('fontSize', self::l('SIZE'), '16px', 'data-rel="range" max="500"').'
                    '.self::input('color', self::l('COLOR'), '#ff0', 'class="ba-input tinycolor"').'
                    '.self::select('order', self::l('ALIGNMENT'), ['-1'=>self::l('LEFT'),'1'=>self::l('RIGHT')], 'data-rel="button"').'
                    '.self::input(['margin','margin','margin','margin'], [self::l('TOP'),self::l('RIGHT'),self::l('BOTTOM'),self::l('LEFT')], ['','','',''], ['data-rel="spinner" data-group', 'data-rel="spinner" data-group', 'data-rel="spinner" data-group', 'data-rel="spinner" data-group'], 'Margin', true).'
                '.self::fieldset_close().'
                '.self::fieldset_open('class="style-custom" data-batype="description"', self::l('CONTENT')).'
                    '.self::customStyle().'
                '.self::fieldset_close().'
                </div>
        '.self::fieldset_close();
        $output .= self::fieldset_open('class="ba-controls group-inline"', self::l('ADVANCED')).'
        '.self::input('tagClass', self::l('CLASS', 1), '', 'class="ba-input width-md"').'
        '.self::textarea('tagCSS', self::l('CSS', 1)).'
        '.self::fieldset_close();
        return $output;
    }

    public static function customStyle($join = false, $prefix = '')
    {
        $fontFamily = ["Inherit", "Arial", "Helvetica", "Times", "Times New Roman", "Palatino", "Garamond", "Bookman", "Avant Garde", "Courier", "Verdana", "Georgia"];
        $fontWeight = ["Inherit", "normal", "bold","100", "200", "300", "400", "500", "600", "700", "800", "900"];
        return self::fieldset_open('class="style-custom-basic"', self::l('BASIC')).'
            '.self::input($prefix.'background', self::l('BACKGROUND'), '#721775', 'class="ba-input tinycolor"').'
            '.self::input($prefix.'color', self::l('COLOR'), '#ff0', 'class="ba-input tinycolor"').'            
            '.($join ? self::input($prefix.'markcolor', self::l('HIGHLIGHT'), '#f00', 'class="ba-input tinycolor"') : '').'
            '.self::select($prefix.'fontFamily', self::l('FONT_FAMILY'), self::arrayCombine($fontFamily, 1)).'
            '.self::input($prefix.'fontSize', self::l('FONT_SIZE'), '18px', 'data-rel="range"').'
            '.self::select($prefix.'fontWeight', self::l('FONT_WEIGHT'), self::arrayCombine($fontWeight, 1)).'
            '.self::select($prefix.'textTransform', self::l('TEXT_TRANSFORM'), self::arrayCombine(["none","uppercase","lowercase","capitalize"])).'
        '.self::fieldset_close().'
        '.self::fieldset_open('class="style-custom-border"', self::l('BORDER')).'
            '.self::select($prefix.'borderStyle', self::l('STYLE'), ["none"=>"None","solid"=>"Solid","double"=>"Double","dotted"=>"Dotted","dashed"=>"Dashed","groove"=>"Groove","ridge"=>"Ridge","inset"=>"Inset","outset"=>"Outset"]).'
            '.self::input([$prefix.'borderWidth',$prefix.'borderWidth',$prefix.'borderWidth',$prefix.'borderWidth'], [self::l('TOP'),self::l('RIGHT'),self::l('BOTTOM'),self::l('LEFT')], ['','','',''], ['data-rel="spinner" data-group', 'data-rel="spinner" data-group', 'data-rel="spinner" data-group', 'data-rel="spinner" data-group'], self::l('WIDTH'), true).'
            '.self::input($prefix.'borderColor', self::l('COLOR'), '#333', 'class="ba-input tinycolor"').'
            '.self::input([$prefix.'borderRadius',$prefix.'borderRadius',$prefix.'borderRadius',$prefix.'borderRadius'], [self::l('TOP'),self::l('RIGHT'),self::l('BOTTOM'),self::l('LEFT')], ['','','',''], ['data-rel="spinner" data-group', 'data-rel="spinner" data-group', 'data-rel="spinner" data-group', 'data-rel="spinner" data-group'], self::l('RADIUS'), true).'
        '.self::fieldset_close().'
        '.self::fieldset_open('class="style-custom-spacing"', self::l('SPACING')).'
            '.self::input([$prefix.'margin',$prefix.'margin',$prefix.'margin',$prefix.'margin'], [self::l('TOP'),self::l('RIGHT'),self::l('BOTTOM'),self::l('LEFT')], ['','','',''], ['data-rel="spinner" data-group', 'data-rel="spinner" data-group', 'data-rel="spinner" data-group', 'data-rel="spinner" data-group'], 'Margin', true).'
            '.self::input([$prefix.'padding',$prefix.'padding',$prefix.'padding',$prefix.'padding'], [self::l('TOP'),self::l('RIGHT'),self::l('BOTTOM'),self::l('LEFT')], ['','','',''], ['data-rel="spinner" data-group', 'data-rel="spinner" data-group', 'data-rel="spinner" data-group', 'data-rel="spinner" data-group'], 'Padding', true).'
        '.self::fieldset_close();
    }
    //////////////////////////////////
    ///////// END OPTIONS ///////////////////////
    /////////////////////////////////



    public static function defalutData()
    {
        return '[{"id":"data-source","data-mode":"source-basic","children":[{"id":"source-basic","children":[{"header":"Sports","subheader":"","iconMode":"icon","icon":"fas fa-air-freshener","image":"","main":"<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>"},{"header":"Health","subheader":"","iconMode":"","icon":"fas fa-allergies","image":"","main":"<p>Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo.</p>"},{"header":"Travel","subheader":"","iconMode":"","icon":"fas fa-ambulance","image":"","main":"<p>Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur.</p>"}]},{"id":"source-article","catid":"10,12","child_category":"1","author_filtering_type":"1","created_by":"","article_ids":"","article_ordering":"date","article_direction":"ASC","count":"5","show_title":"1","show_date":"1","show_category":"1","show_author":"1","show_introtext":"1","introtext_limit":"0","show_readmore":"0","readmore_text":"Read more","article_type":"post","article_tags":""}]},{"id":"setting-source","width":"100%","height":"auto","displayMode":"1","collapsible":"0","defaultId":"1","trigger":"click","speed":"900ms","autoPlay":"0","autoplayDelay":"6000ms","pauseOnHover":"1","nextPrev":"1","keyNav":"1","effect":"fadeIn","styleMode":"0","style":"style1","children":[{"id":"title","background":"#F0F0F2§§#F25252","color":"#404040§§#fff","borderColor":"#BFBFBD§§#025959","fontFamily":"Inherit§§Inherit","fontSize":"18px§§18px","fontWeight":"Inherit§§Inherit","textTransform":"none§§none"},{"id":"description","background":"#fff","color":"#404040","borderColor":"#BFBFBD","fontFamily":"Inherit","fontSize":"16px","fontWeight":"Inherit","textTransform":"none"}],"arrowType":"plus,minus","iconSize":"18px","iconOrder":"1","tagClass":"ba-mod","tagCSS":"#ID{font-size:16px}","breakPoint":"dropdown","breakPointWidth":"576","position":"default","align":"start"}]';
    }
    //////////////////// END CLASS ////////////////////
}
