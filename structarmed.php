<?php

declare(strict_types=1);

use Boundwize\StructArmed\Architecture;
use Boundwize\StructArmed\Preset\Preset;

return Architecture::define()
    ->withPresets(Preset::PSR4(), Preset::CODEQUALITY())
    ->layer('Logger', 'src/LoggerFactory.php')
    ->layer('Annotated', 'src/Annotated/')
    ->layer('Auth', 'src/Auth/')
    ->layer('Bootloader', 'src/Bootloader/')
    ->layer('Config', 'src/Config/')
    ->layer('Console', 'src/Console/', 'src/Console/Command/Migrate/')
    ->layer('Migrate', 'src/Console/Command/Migrate/')
    ->layer('DataGrid', 'src/DataGrid/', 'src/DataGrid/Specification/')
    ->layer('Specification', 'src/DataGrid/Specification/')
    ->layer('Filter', 'src/Filter/')
    ->layer('Injector', 'src/Injector/')
    ->layer('Interceptor', 'src/Interceptor/')
    ->layer('Scaffolder', 'src/Scaffolder/')
    ->layer('Schema', 'src/Schema/')
    ->layer('Validation', 'src/Validation/')
    ->ruleset([
        'Logger'        => [],
        'Annotated'     => [],
        'Auth'          => [],
        'Config'        => [],
        'Migrate'       => [],
        'Specification' => [],
        'Filter'        => [],
        'Injector'      => [],
        'Interceptor'   => [],
        'Scaffolder'    => [],
        'Schema'        => [],
        'Validation'    => [],
        'DataGrid'      => ['Specification'],
        'Console'       => ['+Migrate', 'Bootloader', 'Config', 'Schema', 'Scaffolder'],
        'Bootloader'    => ['+Console', '+DataGrid', 'Annotated', 'Auth', 'Injector', 'Logger', 'Validation'],
    ]);
