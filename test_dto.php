<?php

require 'vendor/autoload.php';

class InstrumentedDTO extends \Avax\DataHandling\ObjectHandling\DTO\AbstractDTO
{
    public string $email = '';

    protected function hydrateField(string $name, \ReflectionProperty $property, array $attributes, array $data): void
    {
        error_log("InstrumentedDTO::hydrateField called for: $name");
        error_log('  attributes count: '.count($attributes));

        foreach ($attributes as $attr) {
            $instance = $attr->newInstance();
            error_log('  attribute: '.get_class($instance));
            if (method_exists($instance, 'validate')) {
                try {
                    $instance->validate($data[$name] ?? null, $name);
                    error_log('  validate PASSED');
                } catch (Throwable $e) {
                    error_log('  validate FAILED: '.$e->getMessage());
                    throw $e;
                }
            }
        }

        parent::hydrateField($name, $property, $attributes, $data);

        error_log("InstrumentedDTO::hydrateField completed for: $name");
    }
}

echo 'Creating InstrumentedDTO...'.PHP_EOL;
$dto = new InstrumentedDTO(['email' => 'not-an-email']);
echo 'Email: '.$dto->email.PHP_EOL;
