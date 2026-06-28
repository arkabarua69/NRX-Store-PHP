<?php

namespace App\Services;


class PWAService
{
    public function generate()
    {
        $basicManifest =  [
            'name' => gs()->site_name,
            'short_name' => gs()->site_name,
            'description' => gs()->site_title,
            'start_url' => '/',
            'id' => '/',
            'display' => 'standalone',
            'theme_color' => gs()->theme_color,
            'background_color' => gs()->background_color,
            'orientation' =>  'any',
            'status_bar' =>  'black',
            'icons' => [
                [
                    'src' => get_image(gs()->pwa_icon),
                    'type' => 'image/png',
                    'sizes' => '512x512',
                    'purpose' => 'any'
                ]
            ]
        ];

        return $basicManifest;
    }

    public function render()
    {
        return "<?php \$config = (new \App\Services\PWAService)->generate(); echo \$__env->make( 'pwa.meta' , ['config' => \$config])->render(); ?>";
    }
}
