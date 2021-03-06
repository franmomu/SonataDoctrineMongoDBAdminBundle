<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\DoctrineMongoDBAdminBundle\Admin;

use Sonata\AdminBundle\Admin\BaseFieldDescription;

/**
 * @final since sonata-project/doctrine-mongodb-admin-bundle 3.5.
 */
class FieldDescription extends BaseFieldDescription
{
    /**
     * NEXT_MAJOR: Change visibility to protected.
     *
     * @return void
     */
    public function setAssociationMapping($associationMapping)
    {
        if (!\is_array($associationMapping)) {
            throw new \RuntimeException('The association mapping must be an array');
        }

        $this->associationMapping = $associationMapping;

        $this->type = $this->type ?: $associationMapping['type'];
        $this->mappingType = $this->mappingType ?: $associationMapping['type'];
        $this->fieldName = $associationMapping['fieldName'];
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.4 and will be removed in version 4.0. Use FieldDescription::getTargetModel() instead.
     */
    public function getTargetEntity()
    {
        @trigger_error(sprintf(
            'Method %s() is deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.4 and will be removed in version 4.0.'
            .' Use %s::getTargetModel() instead.',
            __METHOD__,
            __CLASS__
        ), \E_USER_DEPRECATED);

        return $this->getTargetModel();
    }

    public function getTargetModel(): ?string
    {
        if ($this->associationMapping) {
            return $this->associationMapping['targetDocument'];
        }

        return null;
    }

    /**
     * NEXT_MAJOR: Change visibility to protected.
     *
     * @return void
     */
    public function setFieldMapping($fieldMapping)
    {
        if (!\is_array($fieldMapping)) {
            throw new \RuntimeException('The field mapping must be an array');
        }

        $this->fieldMapping = $fieldMapping;

        $this->type = $this->type ?: $fieldMapping['type'];
        $this->mappingType = $this->mappingType ?: $fieldMapping['type'];
        $this->fieldName = $this->fieldName ?: $fieldMapping['fieldName'];
    }

    /**
     * NEXT_MAJOR: Change visibility to protected.
     *
     * @return void
     */
    public function setParentAssociationMappings(array $parentAssociationMappings)
    {
        foreach ($parentAssociationMappings as $parentAssociationMapping) {
            if (!\is_array($parentAssociationMapping)) {
                throw new \RuntimeException('An association mapping must be an array');
            }
        }

        $this->parentAssociationMappings = $parentAssociationMappings;
    }

    public function isIdentifier()
    {
        return $this->fieldMapping['id'] ?? false;
    }

    public function getValue($object)
    {
        foreach ($this->parentAssociationMappings as $parentAssociationMapping) {
            $object = $this->getFieldValue($object, $parentAssociationMapping['fieldName']);
        }

        return $this->getFieldValue($object, $this->fieldName);
    }
}
