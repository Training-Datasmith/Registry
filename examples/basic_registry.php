<?php

declare(strict_types=1);

/**
 * Sylius Registry — basic service registry example.
 *
 * Demonstrates: registering services by name, type checking, retrieval.
 *
 * Run:
 *   php examples/basic_registry.php
 */

require __DIR__ . '/../vendor/autoload.php';

use Sylius\Component\Registry\Service_Registry;

// Define a simple interface for the example
interface GreeterInterface
{
    public function greet(string $name): string;
}

class FormalGreeter implements GreeterInterface
{
    public function greet(string $name): string
    {
        return 'Good day, ' . $name . '.';
    }
}

class CasualGreeter implements GreeterInterface
{
    public function greet(string $name): string
    {
        return 'Hey ' . $name . '!';
    }
}

// Create a registry that only accepts GreeterInterface instances
$registry = new Service_Registry(GreeterInterface::class, 'greeter');

$registry->register('formal', new FormalGreeter());
$registry->register('casual', new CasualGreeter());

// Check registration
echo 'Has formal: '  . ($registry->has('formal') ? 'yes' : 'no') . PHP_EOL;
echo 'Has unknown: ' . ($registry->has('unknown') ? 'yes' : 'no') . PHP_EOL;

// Retrieve and use a service
/** @var GreeterInterface $greeter */
$greeter = $registry->get('formal');
echo $greeter->greet('Alice') . PHP_EOL;

$greeter = $registry->get('casual');
echo $greeter->greet('Bob') . PHP_EOL;

// List all registered services
echo PHP_EOL . 'All registered greeters:' . PHP_EOL;
foreach (array_keys($registry->all()) as $name) {
    echo '  ' . $name . PHP_EOL;
}
