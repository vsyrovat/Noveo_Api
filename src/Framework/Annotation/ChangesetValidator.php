<?php declare(strict_types=1);

namespace App\Framework\Annotation;

use App\Domain\Exception\ValidationException;
use App\Framework\Annotation\Annotations\Api;
use Doctrine\Common\Annotations\Reader;

class ChangesetValidator
{
    private $annotationReader;

    public function __construct(Reader $annotationReader)
    {
        $this->annotationReader = $annotationReader;
    }

    /**
     * @throws ValidationException
     */
    public function assertChangesetValid(array $changeset, string $entityClassName): void
    {
        $entityReflection = new \ReflectionClass($entityClassName);
        $violations = [];

        foreach ($entityReflection->getProperties() as $property) {
            $apiAnnotation = $this->annotationReader->getPropertyAnnotation($property, Api::class);
            if ($apiAnnotation !== null) {
                $propertyName = $property->getName();
                if (!\array_key_exists($propertyName, $changeset)) {
                    $violations[] = [
                        'property_path' => $propertyName,
                        'message' => 'This field should be given'
                    ];
                }
            }
        }

        if (count($violations) > 0) {
            throw new ValidationException($violations);
        }
    }
}