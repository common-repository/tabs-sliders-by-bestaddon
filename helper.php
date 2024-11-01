<?php

defined('ABSPATH') or die;

class PlgBestAddonHelper
{
    public static function getList(&$modData, &$params)
    {
        self::getobj($modData, 'data-mode', $dataSelect);
        self::getobj($modData, $dataSelect, $jData);

        $type = self::is($jData['article_type']) ?: 'post';
        $catids = self::is($jData['catid']) ?: '1,3,5,8,9';
        $child_category = (int)self::is($jData['child_category']);
        //$levels = $jData['levels'];
        $author_filtering_type = (int)self::is($jData['author_filtering_type']);
        $created_by = self::is($jData['created_by']) ?: '';
        $excluded_articles = self::is($jData['article_ids']) ?: '';
        $tags = self::is($jData['article_tags']) ?: '';
        // Ordering
        $article_ordering = self::is($jData['article_ordering']) ?: 'name';
        $article_direction = self::is($jData['article_direction']) ?: 'ASC';

        $show_front = self::is($jData['show_front']) ?: 'show';
        $show_title = (int) self::is($jData['show_title']);
        $show_date = (int) self::is($jData['show_date']);
        $show_category = (int) self::is($jData['show_category']);
        $show_author = (int) self::is($jData['show_author']);
        $show_introtext = (int) self::is($jData['show_introtext']);
        $introtext_limit = (int) self::is($jData['introtext_limit']);
        $show_readmore = (int) self::is($jData['show_readmore']);
        $readmore_text = self::is($jData['readmore_text']) ?: 'Readmore';

        $args = [
            'post_type' => $type,
            'posts_per_page' => (int) $jData['count'],
            'order' => $article_direction,
            'orderby' => $article_ordering,
            'post__not_in' => explode(',', $excluded_articles),
            'post_status' => 'publish'
        ];
        if (!empty($created_by)) {
            $args[empty($author_filtering_type) ? 'author__not_in' : 'author__in'] = explode(',', $created_by);
        }
        if ($type == 'post') {
            $args['tag'] = $tags;
            $args['tax_query'] = [
                [
                    'taxonomy' => 'category',
                    'field'    => 'term_id',
                    'terms'    => explode(',', $catids),
                    'include_children' => $child_category
                ]
            ];
        }
        $items = get_posts($args);
        // Prepare data for display using display options
        foreach ($items as $key => &$item) {
            $item->link = get_permalink($item->ID);
            $item->introtext = get_extended($item->post_content)['main'];

            // Used for styling the active article
            $image = wp_get_attachment_image_src(get_post_thumbnail_id($item->ID), 'large'); //thumbnail | medium | large
            if (isset($image)) {
                $item->image = $image;
                $item->imageAlt = $item->post_title;
            } else {
                preg_match('/<img\s.*?\bsrc="(.*?)".*?>/si', $item->introtext, $matches);
                $item->image = isset($matches[1]) ? $matches[1] : '';
                $item->imageAlt = '';
            }

            $categories = get_the_category($item->ID);
            $displayCategoryName = '';
            foreach ($categories as $cat) {
                $displayCategoryName .= '<a href="' . esc_url(get_category_link($cat->term_id)) . '">' . esc_html($cat->name) . '</a>';
            }
            $item->displayCategoryTitle = $show_category ? $displayCategoryName : '';

            $item->title = $show_title ? $item->post_title : '';

            if ((int)$introtext_limit > 0) {
                $item->introtext = self::wordLimit(trim(strip_tags($item->introtext)), $introtext_limit);
            }
            $item->maintext = '<div class="ba-infor">'.
            ($show_category ? '<span class="ba-category">'.esc_html($item->displayCategoryTitle).'</span>' : '').
            ($show_author ? '<span class="ba-author">'.get_the_author_meta('user_nicename', $item->post_author).'</span>' : '').
            ($show_date ? '<span class="ba-date">'.get_the_date('', $item->ID).'</span>' : '').'</div>'.
            '<div>'.($show_introtext > 0 ? wp_kses_post($item->introtext) : '').'</div>'.
            ($show_readmore ? '<a class="ba-readmore btn btn-primary" href="'.esc_url($item->link).'"><span>'.esc_html($readmore_text).'</span></a>' : '');
        }
        return $items;
    }


    /**
     * ADD YOUR ASSETS
     */
    public static function assets($path='', $addRoot=true, /*$options = [], $attributes = [],*/ $dependencies = [])
    {
        if (empty($path)) {
            return;
        }
        $rootPath = dirname(__FILE__);
        $webPath = plugins_url('/', __FILE__);
        if (!is_array($path) && strpos($path, '{') !== false && strpos($path, '}') !== false) {
            if ($addRoot) {
                wp_register_style(basename($rootPath), false);
                wp_enqueue_style(basename($rootPath));
                wp_add_inline_style(basename($rootPath), $path);
            }
        } else {
            $realPath = is_array($path) ? $path : glob($rootPath.$path.'{,*/,*/*/}{*.js,*.css}', GLOB_BRACE);
            foreach ($realPath as $key => $filename) {
                $basePath = is_array($path) ? $filename : $webPath.$path.(preg_match("/\.(js|jsx)$/", $filename) ? 'js/' : 'css/').basename($filename);
                $baseName = is_array($path) ? $key : basename($rootPath).'-'.basename($filename);
                if ($addRoot) {
                    if (preg_match("/\.(js|jsx)$/", $filename)) {
                        wp_enqueue_script($baseName, $basePath, /*$options, $attributes,*/ $dependencies);
                    } else {
                        //wp_enqueue_style('ba-fontawesome', $webPath.'assets/front/css/fontawesome-free-5/css/all.min.css');
                        wp_enqueue_style($baseName, $basePath, /*$options, $attributes,*/ $dependencies);
                    }
                }
            }
        }
    }

    /**
     * Render a module by id in CONTENT
     */
    public static function isMod($content, $thisID)
    {
        // Find all instances of plugin and put in $matchesmodid for loadmoduleid
        preg_match_all('@\[([^<>&/\[\]\x00-\x20=]++)(.*?)\]@', $content, $matches, PREG_SET_ORDER);

        // If no matches, skip this
        if (!empty($matches)) {
            foreach ($matches as $match) {
                if (empty(preg_grep('/'.$thisID.'/i', $match))) {
                    $content = do_shortcode($content);
                }
            }
        }
        return $content;
    }

    /**
     * Get a array with Recursive Arrays
     */
    public static function getobj($data, $isID, &$node)
    {
        if (isset($data) && !empty($data) && is_array($data)) {
            foreach ($data as $key => $item) {
                if (($key === $isID) || (isset($item['id']) && $item['id'] === $isID)) {
                    $node = $item;
                } elseif (is_array($item)) {
                    self::getobj($item, $isID, $node);
                }
            }
            return $node;
        }
    }

    /**
     * Cut string by specified by a number
     */
    public static function wordLimit($string, $word_limit)
    {
        $words = explode(' ', strip_tags($string));
        return implode(' ', array_splice($words, 0, $word_limit));
    }



    //////////////////////////////////
    ///////// BEGIN ADD STYLES ///////////////////////
    /////////////////////////////////
    public static function is(&$name, $val='')
    {
        return !empty(is_string($name) ? trim($name) : $name) ? (!empty($val) ? $val : $name) : null;
    }
    public static function prop($attr, $prop, $prefix='', $suffix='', $hover=false)
    {
        $val = self::is($attr[$prefix.$prop.$suffix]);
        $val = strpos($val, '§§') ? explode('§§', $val) : $val;
        return is_array($val) ? ($hover ? self::is($val[1]) : self::is($val[0])) : $val;
    }
    public static function css($attr, $properties=[], $prefix='', $suffix='', $hover=false)
    {
        $output = '';
        $properties = is_array($properties) ? $properties : explode(',', $properties);
        foreach ($properties as $prop) {
            $isProp = strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', $prop));
            $val = self::prop($attr, $prop, $prefix, $suffix, $hover);
            $splitGroupVal = str_replace("||", " ", $val);
            if (strpos($isProp, '-image') !== false) {
                $output.= self::is($val, $isProp.':url('.$splitGroupVal.');');
            } elseif (strpos($isProp, '-shadow') !== false) {
                $shadows = explode('||', $val);
                foreach ($shadows as &$item) {
                    $item = ($item === end($shadows) ? ($item ? 'inset' : '') : (empty(trim($item)) ? 0 : $item));
                }
                $output.= $isProp.':'.implode(" ", $shadows).';';
            } else {
                if (preg_match('/background|color/i', $isProp)) {
                    $output.= self::is($val, '--ba-'.$isProp.':'.$splitGroupVal.';');
                }
                $output.= trim($splitGroupVal) != '' ? self::is($val, $isProp.':'.$splitGroupVal.';') : '';
            }
        }
        return $output;
    }
    public static function bg($attr, $prefix='', $suffix='', $hover=false)
    {
        $output = '';
        $bgType = self::prop($attr, 'backgroundType', $prefix, $suffix, $hover);
        if ($bgType == 'color') {
            $output.= self::css($attr, ['backgroundColor'], $prefix, $suffix, $hover);
        }
        if ($bgType == 'gradient') {
            $props = ["gradientStartColor","gradientStartPoint","gradientEndColor","gradientEndPoint","gradientType","gradientAngle","gradientPosition"];
            foreach ($props as &$value) {
                $value = self::prop($attr, $value, $prefix, $suffix, $hover);
            }
            $output.='background:'.$props[4].'-gradient('.($props[4] == 'linear' ? $props[5] : 'at '.$props[6]).','.$props[0].' '.$props[1].','.$props[2].' '.$props[3].')'.';';
        }
        if ($bgType == 'image') {
            $output .= self::css($attr, ['backgroundImage','backgroundSize','backgroundPosition', 'backgroundAttachment','backgroundRepeat'], $prefix, $suffix, $hover);
        }
        return $output;
    }
    //////////////////////////////
    ///////// END ADD STYLES ///////////////////////
    /////////////////////////////
}
