# Architecture: Registry (Sylius Registry Component)

## Purpose

Provides a typed service registry pattern used throughout the Sylius ecosystem. Allows registering named services and retrieving them by name, with optional priority ordering.

## Directory Structure

```
src/
  Service_Registry.php                    Basic registry: register by name, get by name
  Service_Registry_Interface.php          Contract for the basic registry
  Prioritized_Service_Registry.php        Registry with priority ordering
  Prioritized_Service_Registry_Interface.php
  Existing_Service_Exception.php          Thrown when registering a duplicate name
  Non_Existing_Service_Exception.php      Thrown when getting an unknown name
tests/
  Service_Registry_Test.php
  Prioritized_Service_Registry_Test.php
```

## Key Design Decisions

- **Type enforcement**: The registry constructor accepts a required interface/class name. Any registered service must be an instance of that type; a `\InvalidArgumentException` is thrown otherwise.
- **Named services**: Services are keyed by string name, not class name, so multiple instances of the same class can coexist under different names.
- **Priority ordering**: `Prioritized_Service_Registry` sorts registered services by integer priority (higher = earlier). This allows ordered processing chains (e.g., payment method priority).
- **Tiny footprint**: Two classes, two exceptions. The registry is deliberately simple and reusable across many Sylius components.

## Extension Points

- Inject a `Service_Registry` instance pre-populated with default services; consuming code may call `register()` to add more.
- Use `Prioritized_Service_Registry` when the order of processing matters (e.g., shipping calculators, tax applicators).

## Dependency Flow

```
bootstrap
  -> ServiceRegistry('PaymentMethodInterface')
  -> registry->register('stripe', $stripeService)
  -> registry->register('paypal', $paypalService)

request
  -> registry->get('stripe') -> StripePaymentService
```
