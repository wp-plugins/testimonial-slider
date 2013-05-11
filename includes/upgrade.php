<?php
class testimonial_update_class
{
    public $testimonial_slider_current_version;
    public $testimonial_slider_update_path;
    public $testimonial_slider_plugin_slug;
    public $testimonial_slider_slug;
    function __construct($testimonial_slider_current_version, $testimonial_slider_update_path, $testimonial_slider_plugin_slug)
    { 
        // Set the class public variables
        $this->current_version = $testimonial_slider_current_version;
        $this->update_path = $testimonial_slider_update_path;
        $this->plugin_slug = $testimonial_slider_plugin_slug;
        list ($t1, $t2) = explode('/', $testimonial_slider_plugin_slug);
        $this->slug = str_replace('.php', '', $t2);

        // define the alternative API for updating checking
        add_filter('pre_set_site_transient_update_plugins', array(&$this, 'testimonial_check_update'));

        // Define the alternative response for information checking
        add_filter('plugins_api', array(&$this, 'testimonial_check_info'), 10, 3);
    }

    public function testimonial_check_update($transient)
    { 
        if (empty($transient->checked)) {
            return $transient;
        }

        $remote_version = $this->testimonial_getRemote_version();

        if (version_compare($this->current_version, $remote_version, '<')) {
            $obj = new stdClass();
            $obj->slug = $this->slug;
            $obj->new_version = $remote_version;
            $obj->url = $this->update_path;
            $transient->response[$this->plugin_slug] = $obj;
        }
        return $transient;
    }

    public function testimonial_check_info($false, $action, $arg)
    {
        if ($arg->slug === $this->slug) {
            $information = $this->testimonial_getRemote_information();
            return $information;
        }
        return false;
    }

    public function testimonial_getRemote_version()
    {
        $request = wp_remote_post($this->update_path, array('body' => array('action' => 'version')));
        if (!is_wp_error($request) || wp_remote_retrieve_response_code($request) === 200) {
            return $request['body'];
        }
        return false;
    }

    public function testimonial_getRemote_information()
    {
        $request = wp_remote_post($this->update_path, array('body' => array('action' => 'info')));
        if (!is_wp_error($request) || wp_remote_retrieve_response_code($request) === 200) {
            return unserialize($request['body']);
        }
        return false;
    }

    public function testimonial_getRemote_license()
    {
        $request = wp_remote_post($this->update_path, array('body' => array('action' => 'license')));
        if (!is_wp_error($request) || wp_remote_retrieve_response_code($request) === 200) {
            return $request['body'];
        }
        return false;
    }
}