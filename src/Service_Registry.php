<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare (strict_types=1);
namespace Sylius\Component\Registry;

/**
 * Cannot be final, because it is proxied
 */
class Service_Registry implements Service_Registry_Interface
{
    /**
     * Map of registered services keyed by their string identifier.
     *
     * @psalm-var array<string, object>
     *
     * @var object[]
     */
    private array $services = [];

    /**
     * Creates a new registry that only accepts services of the given type.
     *
     * @param string $class_name Fully-qualified interface or class name that every registered service must implement/extend
     * @param string $context    Human-readable label for error messages, e.g. "grid field" or "payment method"
     */
    public function __construct(
        private readonly string $class_name,
        private readonly string $context = 'service'
    ) {
    }

    /**
     * Returns all registered services keyed by their identifier.
     *
     * @return object[] Map of identifier => service instance
     *
     * @complexity O(1)
     */
    public function all(): array
    {
        return $this->services;
    }

    /**
     * Registers a service under the given identifier.
     *
     * @param string $identifier Unique string key for this service within the registry
     * @param object $service    Service instance; must be an instanceof $class_name
     *
     * @return void
     *
     * @throws Existing_Service_Exception   If a service with this identifier is already registered
     * @throws \InvalidArgumentException    If the service does not implement/extend the required type
     */
    public function register(string $identifier, object $service): void
    {
        if ($this->has($identifier)) {
            throw new Existing_Service_Exception($this->context, $identifier);
        }
        if (!$service instanceof $this->class_name) {
            throw new \InvalidArgumentException(sprintf('%s needs to be of type "%s", "%s" given.', ucfirst($this->context), $this->class_name, $service::class));
        }
        $this->services[$identifier] = $service;
    }

    /**
     * Removes a previously registered service.
     *
     * @param string $identifier Identifier of the service to remove
     *
     * @return void
     *
     * @throws Non_Existing_Service_Exception If no service with this identifier is registered
     */
    public function unregister(string $identifier): void
    {
        if (!$this->has($identifier)) {
            throw new Non_Existing_Service_Exception($this->context, $identifier, array_keys($this->services));
        }
        unset($this->services[$identifier]);
    }

    /**
     * Checks whether a service is registered under the given identifier.
     *
     * @param string $identifier Identifier to look up
     *
     * @return bool True if the identifier is registered, false otherwise
     */
    public function has(string $identifier): bool
    {
        return isset($this->services[$identifier]);
    }

    /**
     * Retrieves a registered service by its identifier.
     *
     * @param string $identifier Identifier of the service to retrieve
     *
     * @return object The registered service instance
     *
     * @throws Non_Existing_Service_Exception If no service with this identifier is registered
     */
    public function get(string $identifier): object
    {
        if (!$this->has($identifier)) {
            throw new Non_Existing_Service_Exception($this->context, $identifier, array_keys($this->services));
        }
        return $this->services[$identifier];
    }
}