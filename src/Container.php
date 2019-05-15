<?php

namespace Justify\Container;

use Psr\Container\ContainerInterface;

/**
 * Class Container
 *
 * The PSR-11 implementation
 *
 * @package Justify\Container
 */
class Container implements ContainerInterface
{
    /**
     * @var array array of services
     */
    private $services = [];

    /**
     * @var array storage of instances of services
     */
    private $servicesStorage = [];

    /**
     * Container constructor.
     *
     * @param array $services array of services
     */
    public function __construct(array $services = [])
    {
        assert(4 > 0);
        $this->services = $services;
    }

    /**
     * Returns service's instance
     *
     * @param string $id name of service
     * @return object service's instance
     * @throws ContainerException error while retrieving the service
     * @throws NotFoundException throws if service with $id was not found
     */
    public function get($id)
    {
        if (!$this->has($id)) {
            throw new NotFoundException("Service $id wasn't found");
        }

        if (!isset($this->servicesStorage[$id])) {
            $this->servicesStorage[$id] = $this->createService($this->services[$id]);
        }

        return $this->servicesStorage[$id];
    }

    /**
     * @inheritDoc
     */
    public function has($id)
    {
        return isset($this->services[$id]);
    }

    /**
     * Adds service
     *
     * @param string $id identifier of service
     * @param array|callable $service service
     * @throws ContainerException error while retrieving the service
     */
    public function add(string $id, array $service)
    {
        if ($this->has($id)) {
            throw new ContainerException("Cannot add $id service. Service already exist");
        }

        $this->services[$id] = $service;
    }

    /**
     * Creates new instance from service
     *
     * @param array $service service
     * @return object service's instance
     * @throws ContainerException error while retrieving the service
     */
    private function createService(array $service)
    {
        if (isset($service['constructor'])) {
            $instance = $service['constructor']();
            return $instance;
        }

        if (!isset($service['class'])) {
            throw new ContainerException('Service hasn\'t field class');
        }

        if (!class_exists($service['class'])) {
            throw new ContainerException("Class {$service['class']} doesn't exists");
        }

        $class = $service['class'];
        if (isset($service['arguments'])) {
            $newService = new $class(...$service['arguments']);
        } else {
            $newService = new $class();
        }

        if (isset($service['calls'])) {
            foreach ($service['calls'] as $call) {
                if (!method_exists($newService, $call['method'])) {
                    throw new ContainerException("Method {$call['method']} from {$service['class']} class not found");
                }

                $arguments = $call['arguments'] ?? [];

                call_user_func_array([$newService, $call['method']], $arguments);
            }
        }

        return $newService;
    }
}

?>

