<?php

/*
 * You can place your custom package configuration in here.
 */
return [



    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application. This value is used when the
    | framework needs to place the application's name in a notification or
    | any other location as required by the application or its packages.
    |
    */

    'storage' => 'local',


    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application. This value is used when the
    | framework needs to place the application's name in a notification or
    | any other location as required by the application or its packages.
    |
    */

    'default-image' => 'https://via.placeholder.com/{width}x{height}.png',


    /*
    |--------------------------------------------------------------------------
    | Size Names
    |--------------------------------------------------------------------------
    |
    | size names associated to specific width and length values
    | of the generated images at the moment of being uploaded
    |
    */

    'sizes' => [
        'thumbnail' => [100, 100],
        'xs' => [300, 300],
        'sm' => [576, 576],
        'md' => [768, 768],
        'lg' => [992, 800],
        'xl' => [1200, 800],
        'xxl' => [1400, 900]
    ],


    /*
    |--------------------------------------------------------------------------
    | Image Quality
    |--------------------------------------------------------------------------
    |
    | image quality in percentage represented with numerical
    | values 1 to 100 for each of the existing sizes
    |
    */

    'quality' => [
        'thumbnail' => 100,
        'xs' => 100,
        'sm' => 100,
        'md' => 80,
        'lg' => 75,
        'xl' => 75,
        'xxl' => 75
    ]




];