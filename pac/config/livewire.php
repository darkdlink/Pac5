<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Class Namespace
    |--------------------------------------------------------------------------
    |
    | This value sets the root namespace for Livewire component classes in
    | your application. This value affects component auto-discovery and
    | any Livewire file helper commands, like `artisan make:livewire`.
    |
    | After changing this item, run: `php artisan livewire:discover`.
    |
    */
    'class_namespace' => 'App\\Http\\Livewire',

    /*
    |--------------------------------------------------------------------------
    | View Path
    |--------------------------------------------------------------------------
    |
    | This value sets the path for Livewire component views. This affects
    | component auto-discovery and any Livewire file helper commands.
    |
    */
    'view_path' => resource_path('views/livewire'),

    // ... outras configurações ...

    /*
    |--------------------------------------------------------------------------
    | Component Auto-Registration
    |--------------------------------------------------------------------------
    |
    | By default, Livewire will auto-register all components in the
    | directory specified by the 'class_namespace' value above.
    | If you prefer to manually register your components, set this to false.
    |
    */
    'auto_register' => true,

    /*
    |--------------------------------------------------------------------------
    | Livewire Components
    |--------------------------------------------------------------------------
    |
    | Here you can explicitly register components to be loaded.
    | This is useful for cases where auto-discovery isn't the right choice.
    |
    | Example:
    | 'components' => [
    |     App\Http\Livewire\CartCounter::class,
    |     App\Http\Livewire\OtherComponent::class,
    | ],
    |
    */
    'components' => [
        App\Http\Livewire\CartCounter::class,
        // Adicione outros componentes aqui se necessário
    ],
];
