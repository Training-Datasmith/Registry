<?php

declare(strict_types=1);

/*
 * Type-safety tests for Service_Registry.
 *
 * These tests validate that the registry's type enforcement prevents
 * incorrect service types from being registered.
 */

namespace Sylius\Component\Registry\Tests;

use PHPUnit\Framework\TestCase;
use Sylius\Component\Registry\Existing_Service_Exception;
use Sylius\Component\Registry\Non_Existing_Service_Exception;
use Sylius\Component\Registry\Service_Registry;

interface TestServiceInterface
{
    public function execute(): string;
}

class ConcreteTestService implements TestServiceInterface
{
    public function execute(): string
    {
        return 'executed';
    }
}

class UnrelatedService
{
    public function doSomething(): void {}
}

/**
 * Type-safety and correctness tests for Service_Registry.
 *
 * @covers \Sylius\Component\Registry\Service_Registry
 */
class Service_Registry_Type_Safety_Test extends TestCase
{
    /**
     * Registering a service that does not implement the required interface must throw.
     *
     * This prevents misconfigured DI containers from silently registering wrong types
     * which would only fail at call time rather than at registration time.
     */
    public function testRegisteringServiceOfWrongTypeThrowsInvalidArgumentException(): void
    {
        $registry = new Service_Registry(TestServiceInterface::class, 'test service');

        $this->expectException(\InvalidArgumentException::class);
        $registry->register('wrong', new UnrelatedService());
    }

    /**
     * Registering a service that implements the required interface must succeed.
     */
    public function testRegisteringCorrectServiceTypeSucceeds(): void
    {
        $registry = new Service_Registry(TestServiceInterface::class, 'test service');
        $service  = new ConcreteTestService();

        $registry->register('my_service', $service);

        $this->assertTrue($registry->has('my_service'));
        $this->assertSame($service, $registry->get('my_service'));
    }

    /**
     * Registering a service under a name that is already taken must throw.
     *
     * This prevents silent overwriting of services by a misconfigured extension
     * or double-registration.
     */
    public function testRegisteringDuplicateNameThrowsExistingServiceException(): void
    {
        $registry = new Service_Registry(TestServiceInterface::class, 'test service');
        $registry->register('my_service', new ConcreteTestService());

        $this->expectException(Existing_Service_Exception::class);
        $registry->register('my_service', new ConcreteTestService());
    }

    /**
     * Getting a service that was not registered must throw.
     *
     * Silent null returns would allow calling code to proceed with a null service
     * and produce obscure downstream errors.
     */
    public function testGettingNonExistentServiceThrowsNonExistingServiceException(): void
    {
        $registry = new Service_Registry(TestServiceInterface::class, 'test service');

        $this->expectException(Non_Existing_Service_Exception::class);
        $registry->get('nonexistent');
    }

    /**
     * Unregistering a service that does not exist must throw.
     */
    public function testUnregisteringNonExistentServiceThrowsNonExistingServiceException(): void
    {
        $registry = new Service_Registry(TestServiceInterface::class, 'test service');

        $this->expectException(Non_Existing_Service_Exception::class);
        $registry->unregister('nonexistent');
    }

    /**
     * After unregistering, has() must return false and get() must throw.
     */
    public function testUnregisteredServiceIsNoLongerRetrievable(): void
    {
        $registry = new Service_Registry(TestServiceInterface::class, 'test service');
        $registry->register('my_service', new ConcreteTestService());

        $this->assertTrue($registry->has('my_service'));

        $registry->unregister('my_service');

        $this->assertFalse($registry->has('my_service'));

        $this->expectException(Non_Existing_Service_Exception::class);
        $registry->get('my_service');
    }

    /**
     * all() must return an empty array when no services are registered.
     */
    public function testAllReturnsEmptyArrayWhenNoServicesRegistered(): void
    {
        $registry = new Service_Registry(TestServiceInterface::class, 'test service');

        $this->assertSame([], $registry->all());
    }

    /**
     * all() must return all registered services keyed by their identifier.
     */
    public function testAllReturnsAllRegisteredServices(): void
    {
        $registry   = new Service_Registry(TestServiceInterface::class, 'test service');
        $serviceA   = new ConcreteTestService();
        $serviceB   = new ConcreteTestService();

        $registry->register('alpha', $serviceA);
        $registry->register('beta', $serviceB);

        $all = $registry->all();

        $this->assertCount(2, $all);
        $this->assertArrayHasKey('alpha', $all);
        $this->assertArrayHasKey('beta', $all);
        $this->assertSame($serviceA, $all['alpha']);
        $this->assertSame($serviceB, $all['beta']);
    }
}
