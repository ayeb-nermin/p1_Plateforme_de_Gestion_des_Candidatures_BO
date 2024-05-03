<?php
/**
 * Created by PhpStorm.
 * User: Hassen
 * Date: 02/04/2020
 * Time: 05:59
 */
return [
    'front_views_folder' => 'front',
    'admin_prefix' => env('ADMIN_PREFIX', 'admin'),
    'menu_type' => [
        1 => 'internal_link',
        2 => 'external_link',
        3 => 'template',
    ],
    'producat_statuses' => [
        'available' => 'Available',
        'not_available' => 'Not available',
    ],
    'languages' => [
        //'ar' => 'Arabe',
        'fr' => 'Francais',
    ],
    'modules' => [
        'home' => [
            'reference' => 'home',
            'has_element_assignment' => false,
            'show_only_menu' => true,
            'used_in_widget' => false,
            'is_active' => true,
        ],
        'contact' => [
            'reference' => 'contact',
            'has_element_assignment' => false,
            'show_only_menu' => true,
            'used_in_widget' => false,
            'is_active' => true,
        ],
        'testimonial' => [
            'reference' => 'testimonial',
            'has_element_assignment' => false,
            'show_only_menu' => true,
            'used_in_widget' => true,
            'is_active' => true,
        ],
        'page' => [
            'reference' => 'page',
            'has_element_assignment' => false,
            'show_only_menu' => true,
            'used_in_widget' => false,
            'is_active' => true,
        ],
        'gallery' => [
            'reference' => 'gallery',
            'has_element_assignment' => true,
            'model_name' => 'Gallery',
            'show_only_menu' => true,
            'used_in_widget' => false,
            'is_active' => true,
        ],
        'partner' => [
            'reference' => 'partner',
            'has_element_assignment' => true,
            'model_name' => 'Partner',
            'show_only_menu' => true,
            'used_in_widget' => true,
            'widget_orderable_columns' => json_encode([
                'id' => 'ID',
                'order' => 'Order',
                'created_at' => 'Creation_date',
                'updated_at' => 'Last update date'
            ]),
            'is_active' => true,
        ],
        'news' => [
            'reference' => 'news',
            'has_element_assignment' => true,
            'model_name' => 'News',
            'show_only_menu' => true,
            'used_in_widget' => true,
            'widget_orderable_columns' => json_encode([
                'id' => 'ID',
                'start_date' => 'Start date',
                'created_at' => 'Creation_date',
                'updated_at' => 'Last update date'
            ]),
            'is_active' => true,
        ],
        'faq' => [
            'reference' => 'faq',
            'has_element_assignment' => true,
            'model_name' => 'Faq',
            'show_only_menu' => true,
            'used_in_widget' => true,
            'widget_orderable_columns' => json_encode([
                'id' => 'ID',
                'created_at' => 'Creation_date',
                'updated_at' => 'Last update date'
            ]),
            'is_active' => true,
        ],
    ],
];
