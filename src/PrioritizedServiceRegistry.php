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

final class Prioritized_Service_Registry implements Prioritized_Service_Registry_Interface
{
    /**
     * @psalm-var array<int, array{service: object, priority: int}>
     */
    private array $registry = [];
    private bool $sorted = true;
    public function __construct(
        /**
         * Interface which is required by all services.
         */
        private string $interface,
        /**
         * Human readable context for these services, e.g. "tax calculation"
         */
        private string $context = 'service'
    )
    {
    }
    public function all(): iterable
    {
        if ($this->sorted === false) {
            /** @psalm-suppress InvalidPassByReference Doing PHP magic, it works this way */
            array_multisort(array_column($this->registry, 'priority'), \SORT_DESC, array_keys($this->registry), \SORT_ASC, $this->registry);
            $this->sorted = true;
        }
        foreach ($this->registry as $record) {
            yield $record['service'];
        }
    }
    public function register($service, int $priority = 0): void
    {
        $this->assert_service_have_type($service);
        $this->registry[] = ['service' => $service, 'priority' => $priority];
        $this->sorted = false;
    }
    public function unregister($service): void
    {
        if (!$this->has($service)) {
            throw new Non_Existing_Service_Exception($this->context, $service::class, array_map('get_class', array_column($this->registry, 'service')));
        }
        $this->registry = array_filter($this->registry, static fn(array $record): bool => $record['service'] !== $service);
    }
    public function has($service): bool
    {
        $this->assert_service_have_type($service);
        foreach ($this->registry as $record) {
            if ($record['service'] === $service) {
                return true;
            }
        }
        return false;
    }
    private function assert_service_have_type(object $service): void
    {
        if (!$service instanceof $this->interface) {
            throw new \InvalidArgumentException(sprintf('%s needs to implements "%s", "%s" given.', $this->context, $this->interface, $service::class));
        }
    }
}