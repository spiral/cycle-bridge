<?php

declare(strict_types=1);

namespace Spiral\Cycle\DataGrid\Interceptor;

use Psr\Container\ContainerInterface;
use Spiral\Attributes\ReaderInterface;
use Spiral\Core\CoreInterceptorInterface;
use Spiral\Core\CoreInterface;
use Spiral\Cycle\DataGrid\Annotation\DataGrid;
use Spiral\Cycle\DataGrid\Response\GridResponseInterface;
use Spiral\DataGrid\GridFactory;
use Spiral\DataGrid\GridFactoryInterface;

/**
 * Automatically render grids using schema declared in annotation.
 *
 * @see DataGrid
 */
final class GridInterceptor implements CoreInterceptorInterface
{
    private array $cache = [];

    public function __construct(
        private GridResponseInterface $response,
        private ContainerInterface $container,
        private GridFactory $gridFactory,
        private ReaderInterface $reader
    ) {
    }

    /**
     * @throws \Throwable
     */
    public function process(string $controller, string $action, array $parameters, CoreInterface $core): mixed
    {
        $result = $core->callAction($controller, $action, $parameters);

        if (is_iterable($result)) {
            $schema = $this->getSchema($controller, $action);
            if ($schema !== null) {
                $factory = $this->makeFactory($schema);
                if (method_exists($factory, 'withDefaults')) {
                    $factory = $factory->withDefaults($schema['defaults']);
                }

                $grid = $factory->create($result, $schema['grid']);

                if ($schema['view'] !== null) {
                    $grid = $grid->withView($schema['view']);
                }

                return $this->response->withGrid($grid, $schema['options']);
            }
        }

        return $result;
    }

    /**
     * @throws \Doctrine\Common\Annotations\AnnotationException
     */
    private function getSchema(string $controller, string $action): ?array
    {
        $key = sprintf('%s:%s', $controller, $action);
        if (array_key_exists($key, $this->cache)) {
            return $this->cache[$key];
        }

        $this->cache[$key] = null;
        try {
            $method = new \ReflectionMethod($controller, $action);
        } catch (\ReflectionException $e) {
            return null;
        }

        /** @var null|DataGrid $dataGrid */
        $dataGrid = $this->reader->firstFunctionMetadata($method, DataGrid::class);
        if ($dataGrid === null) {
            return null;
        }

        return $this->cache[$key] = $this->makeSchema($dataGrid);
    }

    private function makeSchema(DataGrid $dataGrid): array
    {
        $schema = [
            'grid'     => $this->container->get($dataGrid->grid),
            'view'     => $dataGrid->view,
            'options'  => $dataGrid->options,
            'defaults' => $dataGrid->defaults,
            'factory'  => $dataGrid->factory,
        ];

        if (is_string($schema['view'])) {
            $schema['view'] = $this->container->get($schema['view']);
        }

        if ($schema['defaults'] === [] && method_exists($schema['grid'], 'getDefaults')) {
            $schema['defaults'] = $schema['grid']->getDefaults();
        }

        if ($schema['options'] === [] && method_exists($schema['grid'], 'getOptions')) {
            $schema['options'] = $schema['grid']->getOptions();
        }

        if ($schema['view'] === null && is_callable($schema['grid'])) {
            $schema['view'] = $schema['grid'];
        }

        return $schema;
    }

    private function makeFactory(array $schema): GridFactoryInterface
    {
        if (!empty($schema['factory'])) {
            $factory = $this->container->get($schema['factory']);
            if ($factory instanceof GridFactoryInterface) {
                return $factory;
            }
        }

        return $this->gridFactory;
    }
}
