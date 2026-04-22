<?php

use Avax\DataHandling\ObjectHandling\DTO\AbstractDTO;

require 'vendor/autoload.php';

class test_dto extends AbstractDTO
{
    public string $email = '';

    /**
     * @throws Throwable
     */
    #[Override]
    protected function hydrateField(string $name, ReflectionProperty $property, array $attributes, array $data) : void
    {

        foreach ($attributes as $attr) {
            $instance = $attr->newInstance();
            if (method_exists($instance, 'validate')) {
                try {
                    $instance->validate($data[$name] ?? null, $name);
                } catch (Throwable $e) {
                    throw $e;
                }
            }
        }

        parent::hydrateField(name: $name, property: $property, attributes: $attributes, data: $data);

    }
}

echo 'Creating InstrumentedDTO...'.PHP_EOL;
$dto = new InstrumentedDTO(data: ['email' => 'not-an-email']);
echo 'Email: '.$dto->email.PHP_EOL;
