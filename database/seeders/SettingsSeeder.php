<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $settings = [
            [
                'key' => 'primary_color',
                'value' => '#92E3A9',
                'group' => 'appearance',
                'type' => 'color',
                'label' => 'Primary Color',
                'description' => 'The primary color for buttons and accents',
                'order' => 1
            ],
            [
                'key' => 'secondary_color',
                'value' => '#4CAF50',
                'group' => 'appearance',
                'type' => 'color',
                'label' => 'Secondary Color',
                'description' => 'The secondary color for buttons and accents',
                'order' => 2
            ],
            [
                'key' => 'background_color',
                'value' => '#F4F7FE',
                'group' => 'appearance',
                'type' => 'color',
                'label' => 'Background Color',
                'description' => 'The background color of the page',
                'order' => 3
            ],
            [
                'key' => 'redirection_url',
                'value' => 'https://eureka-digital.ma',
                'group' => 'general',
                'type' => 'text',
                'label' => 'Redirection URL',
                'description' => 'The URL where users are redirected after successful WiFi login',
                'order' => 1
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
} 