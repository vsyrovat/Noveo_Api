<?php declare(strict_types=1);

namespace App\Domain\Command\UpdateUser;

use App\Domain\Exception\ValidationException;
use App\Framework\Annotation\Annotations\Api;
use Doctrine\Common\Annotations\Reader;

class ChangesetFactory
{
    private $annotationReader;

    public function __construct(Reader $reader)
    {
        $this->annotationReader = $reader;
    }

    /**
     * @throws ValidationException
     */
    public function build(array $data, string $entityClassName): UpdateUserChangeset
    {
        $entityReflection = new \ReflectionClass($entityClassName);
        $changeset = new UpdateUserChangeset();
        $violations = [];

        foreach ($entityReflection->getProperties() as $property) {
            $apiAnnotation = $this->annotationReader->getPropertyAnnotation($property, Api::class);
            if ($apiAnnotation !== null) {
                $propertyName = $property->getName();
                if (property_exists($changeset, $propertyName)) {
                    if (\array_key_exists($propertyName, $data)) {
                        $changeset->{$propertyName} = $data[$propertyName];
                    } else {
                        $violations[] = [
                            'property_path' => $propertyName,
                            'message' => 'This field should be given'
                        ];
                    }
                } else {
                    throw new \RuntimeException("Incomplete UpdateUserChangeset, property {$propertyName} required");
                }
            }
        }

        if (count($violations) > 0) {
            throw new ValidationException($violations);
        }

        return $changeset;
    }
}