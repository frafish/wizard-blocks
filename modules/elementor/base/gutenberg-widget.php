<?php

namespace WizardBlocks\Modules\Elementor\Base;

use Elementor\Widget_Base;
use WizardBlocks\Core\Utils;

/**
 * Elementor Gutenberg Widget.
 *
 * Elementor widget that display a Gutenberg Block
 *
 * @since 1.0.0
 */
class GutenbergWidget extends Widget_Base {

    public $block;

    public function __construct($data = [], $args = null) {
        $this->block = $args['block'];
        parent::__construct($data, $args);
    }

    /**
     * Get widget name.
     *
     * Retrieve oEmbed widget name.
     *
     * @since 1.0.0
     * @access public
     * @return string Widget name.
     */
    public function get_name() {
        return str_replace('/', '_', $this->block->name);
    }

    /**
     * Get widget title.
     *
     * Retrieve widget title.
     *
     * @since 1.0.0
     * @access public
     * @return string Widget title.
     */
    public function get_title() {
        if (empty($this->block->title)) {
            list($domain, $slug) = explode('/', $this->block->name);
            return ucwords(str_replace('-', ' ', $slug));
        }
        return $this->block->title;
    }

    /**
     * Get widget categories.
     *
     * Retrieve the list of categories the widget belongs to.
     *
     * @since 1.0.0
     * @access public
     * @return array Widget categories.
     */
    public function get_categories() {
        return ['gutenberg'];
    }

    /**
     * Get widget icon.
     *
     * Retrieve widget icon.
     *
     * @since 1.0.0
     * @access public
     * @return string Widget icon.
     */
    public function get_icon() {
        if (!empty($this->block->icon)) {
            $icon = trim(wp_strip_all_tags($this->block->icon));
            if ($icon) {
                return 'dashicons dashicons-'.$icon;
            }
        }
        return 'eicon-wordpress';
    }

    /**
     * Get widget keywords.
     *
     * Retrieve the list of keywords the oEmbed widget belongs to.
     *
     * @since 1.0.0
     * @access public
     * @return array Widget keywords.
     */
    public function get_keywords() {
        return array_merge($this->block->keywords, ['gutenberg', 'block']);
    }

    /**
     * Register widget controls.
     *
     * Add input fields to allow the user to customize the widget settings.
     *
     * @since 1.0.0
     * @access protected
     */
    protected function register_controls() {

        $first = true;

        $attributes = $this->block->get_attributes();
        //var_dump($attributes); die();
        foreach ($attributes as $key => $attribute) {
            
            // remove supports fields
            if (in_array($key, ['lock', 'align', 'textColor', 'backgroundColor', 'className', 'layout', 'style'])) {
                continue;
            }

            /*
              null
              boolean
              object
              array
              string
              integer
              number (same as integer)
             */
            $type = \Elementor\Controls_Manager::TEXT; // string, null
            if (!empty($attribute['type'])) {
                switch ($attribute['type']) {
                    case 'null':
                        //$type = \Elementor\Controls_Manager::HIDDEN;
                        break;
                    case 'boolean':
                        $type = \Elementor\Controls_Manager::SWITCHER;
                        break;
                    case 'integer':
                    case 'number':
                        $type = \Elementor\Controls_Manager::NUMBER;
                        break;
                    case 'object':
                    case 'array':
                        $type = \Elementor\Controls_Manager::TEXTAREA;
                        break;
                }
            }
            if ($type == \Elementor\Controls_Manager::TEXT) {
                if (stripos($key, 'color') !== false) {
                    $type = \Elementor\Controls_Manager::COLOR;
                }
            }
            if (!empty($attribute['enum']) || !empty($attribute['options'])) {
                $type = \Elementor\Controls_Manager::SELECT;
             }
            
            $label = ucwords(implode(' ', preg_split('/(?=[A-Z])/', $key)));;
            if (!empty($attribute['title'])) {
                $label = $attribute['title'];
            }

            $control = [
                'label' => $label,
                'type' => $type,
            ];
            
            if ($type == \Elementor\Controls_Manager::TEXT) {
                foreach (['url', 'email'] as $input_type) {
                    if (stripos($key, $input_type) !== false) {
                        $control['input_type'] = $input_type;
                    }
                }
            }
            
            if (!empty($attribute['enum'])) {
                $options = [];
                foreach ($attribute['enum'] as $val) {
                    $options[$val] = $val;
                }
                $control['options'] = $options;
            }
            if (!empty($attribute['options'])) {
                $options = [];
                foreach ($attribute['options'] as $okey => $oval) {
                    $options[$okey] = $oval;
                }
                $control['options'] = $options;
            }
            
            if (!empty($attribute['default'])) {
                $default = $attribute['default'];
                if (is_object($default)) {
                    $default = wp_json_encode($default);
                }
                $default = Utils::maybe_json_decode($default);
                $default = maybe_unserialize($default);
                $control['default'] = $default;
            }
            if (!empty($attribute['selected'])) {
                $control['default'] = $attribute['selected'];
            }

            if ($first) {
                $this->start_controls_section(
                    'content_section',
                    [
                        'label' => esc_html__('Content', 'wizard-blocks'),
                        'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
                    ]
                );
                $first = false;
            }
            $this->add_control(
                    $key,
                    $control
            );
            
        }
        if (!$first) {
            $this->end_controls_section();
        }
    }

    /**
     * Render widget output on the frontend.
     *
     * Written in PHP and used to generate the final HTML.
     *
     * @since 1.0.0
     * @access protected
     */
    protected function render() {

        $block = $this->block;
        $attributes = $this->get_settings_for_display();
        $content = '';
        
        $modules = \WizardBlocks\Plugin::instance()->modules_manager;
        $blocks = $modules->get_modules('block');
        $block_content = $blocks->render($attributes, $content, $block);
        
        echo $block_content;
        
    }

    /**
     * Get script dependencies.
     *
     * Retrieve the list of script dependencies the element requires.
     *
     * @since 1.3.0
     * @access public
     *
     * @return array Element scripts dependencies.
     */
    public function get_script_depends() {
        return array_merge($this->block->script_handles, $this->block->view_script_handles);
    }

    /**
     * Get style dependencies.
     *
     * Retrieve the list of style dependencies the element requires.
     *
     * @since 1.9.0
     * @access public
     *
     * @return array Element styles dependencies.
     */
    public function get_style_depends() {
        return $this->block->style_handles;
    }

    /**
     * Get custom help URL.
     *
     * Retrieve a URL where the user can get more information about the widget.
     *
     * @since 1.0.0
     * @access public
     * @return string Widget help URL.

      public function get_custom_help_url() {
      return 'https://developers.elementor.com/docs/widgets/';
      }
     */
}
