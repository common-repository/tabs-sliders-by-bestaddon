<?php

defined('ABSPATH') or die;

class WidgetBestAddonTabs extends WP_Widget
{
    public $postType;
    public $shortcodeName;
    public $show_instance_in_rest = true;
    public function __construct()
    {
        $this->postType = basename(dirname(__FILE__));
        $this->shortcodeName = str_replace('-', '_', strtoupper($this->postType));
        parent::__construct($this->postType, __(strtoupper($this->postType)), array('description' => __('Show '.$this->postType)));
    }

    public function widget($args, $instance)
    {
        $title 		= apply_filters('widget_title', $instance['title']);
        $item_id	= isset($instance['item_id']) ? $instance['item_id'] : '';

        echo wp_kses_post($args['before_widget']);
        if (!empty($title)) {
            echo $args['before_title'] . $title . $args['after_title'];
        }
        echo do_shortcode('['.$this->shortcodeName.' id="'.$item_id.'"]');
        echo wp_kses_post($args['after_widget']);
    }

    public function form($instance)
    {
        $title 		= isset($instance['title']) ? $instance['title'] : __($this->postType);
        $item_id	= isset($instance['item_id']) ? $instance['item_id'] : '';
        $items		= get_posts(array('posts_per_page' => -1, 'post_type' => $this->postType));


        echo '<p>
			<label for="'.esc_attr($this->get_field_id('title')).'">'.__('Title').':</label>
			<input class="widefat" id="'.esc_attr($this->get_field_id('title')).'" name="'.esc_attr($this->get_field_name('title')).'" type="text" value="'.esc_attr($title).'" />
		</p>
		<p>
			<label for='.esc_attr($this->get_field_id('item_id')).'>'.__('Select item').':</label>
			<select name='.esc_attr($this->get_field_name('item_id')).' id='.esc_attr($this->get_field_id('item_id')).' class="widefat">';

        foreach ($items as $item) {
            $selected = $item_id == $item->ID ? 'selected' : '';
            echo '<option value='.esc_html($item->ID).' '.esc_attr($selected).'>'.esc_html($item->post_title).'</option>';
        }
        echo '</select>
		</p>';
    }

    public function update($new_instance, $old_instance)
    {
        $instance = array();
        $instance['title'] = isset($new_instance['title']) ? strip_tags($new_instance['title']) : '';
        $instance['item_id'] = isset($new_instance['item_id']) ? strip_tags($new_instance['item_id']) : '';
        return $instance;
    }
}
