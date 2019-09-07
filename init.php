<?php
// Kleeja Plugin
// thumbs_generator
// Version: 1.0
// Developer: Kleeja Team

// Prevent illegal run
if (! defined('IN_PLUGINS_SYSTEM'))
{
    exit();
}


// Plugin Basic Information
$kleeja_plugin['thumbs_generator']['information'] = [
    // The casual name of this plugin, anything can a human being understands
    'plugin_title' => [
        'en' => 'Thumbnails Generator',
        'ar' => 'مولد الصور المصغرة'
    ],
    // Who wrote this plugin?
    'plugin_developer' => 'Kleeja Team',
    // This plugin version
    'plugin_version' => '1.0',
    // Explain what is this plugin, why should I use it?
    'plugin_description' => [
        'en' => 'Generating thumbnails from the orginal files',
        'ar' => 'توليد الصور المصغرة من الملفات الأصلية'
    ],
    // Min version of Kleeja that's requiered to run this plugin
    'plugin_kleeja_version_min' => '3.0',
    // Max version of Kleeja that support this plugin, use 0 for unlimited
    'plugin_kleeja_version_max' => '3.9',
    // Should this plugin run before others?, 0 is normal, and higher number has high priority
    'plugin_priority' => 0 ,
    'settings_page'   => 'cp=options&smt=thumbnails_generator'
];

//after installation message, you can remove it, it's not requiered
$kleeja_plugin['thumbs_generator']['first_run'] =
[
    'ar' => 'توليد الصور المصغرة من الملفات الأصلية',
    'en' => 'Generating thumbnails from the orginal files'
];


// Plugin Installation function
$kleeja_plugin['thumbs_generator']['install'] = function ($plg_id) {
};


//Plugin update function, called if plugin is already installed but version is different than current
$kleeja_plugin['thumbs_generator']['update'] = function ($old_version, $new_version) {
};


// Plugin Uninstallation, function to be called at unistalling
$kleeja_plugin['thumbs_generator']['uninstall'] = function ($plg_id) {
};


// Plugin functions
$kleeja_plugin['thumbs_generator']['functions'] = [
    'default_go_page' => function($args) {
        if (g('go') == 'thumbnails_generator')
        {
            require_once PATH . 'includes/up_helpers/thumbs.php';

            global $SQL , $dbprefix , $config;

            $query = [
                'SELECT' => 'id , name , folder , type' ,
                'FROM'   => $dbprefix . 'files',
                'WHERE'  => "type IN ('png','gif','jpg','jpeg', 'bmp')"
            ];

            $result = $SQL->build($query);
            $file_counts = 0;

            while ($file_info = $SQL->fetch($result))
            {
                $file_path      = PATH . '/' . $file_info['folder'] . '/' . $file_info['name'];
                $thumbnail_path = PATH . '/' . $file_info['folder'] . '/thumbs/' . $file_info['name'];

                if (file_exists($file_path) && ! file_exists($thumbnail_path))
                {
                    // get default thumb dimensions
                    $thmb_dim_w = $thmb_dim_h = 150;

                    if (strpos($config['thmb_dims'], '*') !== false)
                    {
                        list($thmb_dim_w, $thmb_dim_h) = array_map('trim', explode('*', $config['thmb_dims']));
                    }
                    // generate a thumbnail
                    helper_thumb($file_path, $file_info['type'], $thumbnail_path, $thmb_dim_w, $thmb_dim_h);
                    $file_counts++;
                }
            }
            kleeja_info(
                $file_counts . ($config['language'] == 'ar' ? ' صورة مصغرة تم انشاءها' : ' thumbnails is generated'),
                '', true, $config['siteurl'] . 'admin/index.php?cp=d_img_ctrl', 5
            );

            exit;
        }
    },
    'require_admin_page_end_a_configs' => function ($args) {
        global $config;
        $go_menu = $args['go_menu'];
        $go_menu['thumbnails_generator'] = [
            'name'   => 'Thumbnails Generator',
            'link'   => $config['siteurl'] . 'go.php?go=thumbnails_generator',

        ];
        return compact('go_menu');
    },
    'begin_admin_page' => function ($args) {
        if (g('cp') == 'options' && g('smt') == 'thumbnails_generator')
        {
            global $config;
            redirect($config['siteurl'] . 'go.php?go=thumbnails_generator');

            exit;
        }
    }
];
