<?php

declare(strict_types=1);

\class_alias(
    \Spiral\Cycle\Bootloader\AnnotatedBootloader::class,
    \Spiral\Bootloader\Cycle\AnnotatedBootloader::class
);

\class_alias(
    \Spiral\Cycle\Bootloader\AuthTokensBootloader::class,
    \Spiral\Bootloader\Auth\TokenStorage\CycleTokensBootloader::class
);

\class_alias(
    \Spiral\Cycle\Bootloader\CycleOrmBootloader::class,
    \Spiral\Bootloader\Cycle\CycleBootloader::class
);

\class_alias(
    \Spiral\Cycle\Bootloader\DatabaseBootloader::class,
    \Spiral\Bootloader\Database\DatabaseBootloader::class
);

\class_alias(
    \Spiral\Cycle\Bootloader\DataGridBootloader::class,
    \Spiral\DataGrid\Bootloader\GridBootloader::class
);

\class_alias(
    \Spiral\Cycle\Bootloader\DisconnectsBootloader::class,
    \Spiral\Bootloader\Database\DisconnectsBootloader::class
);

\class_alias(
    \Spiral\Cycle\Bootloader\MigrationsBootloader::class,
    \Spiral\Bootloader\Database\MigrationsBootloader::class
);

\class_alias(
    \Spiral\Cycle\Bootloader\SchemaBootloader::class,
    \Spiral\Bootloader\Cycle\SchemaBootloader::class
);
