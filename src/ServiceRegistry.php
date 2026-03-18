<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Sylius\Component\Registry;

/**
 * Cannot be final, because it is proxied
 */
class ServiceRegistry implements ServiceRegistryInterface
{
    /**
     * @psalm-var array<string, object>
     *
     * @var object[]
     */
    private array $services = [];

    public function __construct(
        /**
         * Interface or parent class which is required by all services.
         */
        private string $className,
        /**
         * Human readable context for these services, e.g. "grid field"
         */
        private string $context = 'service'
    )
    {
    }

    public function all(): array
    {
        return $this->services;
    }

    public function register(string $identifier, $service): void
    {
        if ($this->has($identifier)) {
            throw new ExistingServiceException($this->context, $identifier);
        }

        if (!$service instanceof $this->className) {
            throw new \InvalidArgumentException(
                sprintf('%s needs to be of type "%s", "%s" given.', ucfirst($this->context), $this->className, $service::class)
            );
        }

        $this->services[$identifier] = $service;
    }

    public function unregister(string $identifier): void
    {
        if (!$this->has($identifier)) {
            throw new NonExistingServiceException($this->context, $identifier, array_keys($this->services));
        }

        unset($this->services[$identifier]);
    }

    public function has(string $identifier): bool
    {
        return isset($this->services[$identifier]);
    }

    public function get(string $identifier): object
    {
        if (!$this->has($identifier)) {
            throw new NonExistingServiceException($this->context, $identifier, array_keys($this->services));
        }

        return $this->services[$identifier];
    }
}
