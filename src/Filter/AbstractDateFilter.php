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

namespace Sonata\DoctrineMongoDBAdminBundle\Filter;

use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Form\Type\Filter\DateRangeType;
use Sonata\AdminBundle\Form\Type\Filter\DateTimeRangeType;
use Sonata\AdminBundle\Form\Type\Filter\DateTimeType;
use Sonata\AdminBundle\Form\Type\Filter\DateType;

abstract class AbstractDateFilter extends Filter
{
    /**
     * Flag indicating that filter will have range.
     *
     * @var bool
     */
    protected $range = false;

    /**
     * Flag indicating that filter will filter by datetime instead by date.
     *
     * @var bool
     */
    protected $time = false;

    /**
     * {@inheritdoc}
     */
    public function filter(ProxyQueryInterface $queryBuilder, $alias, $field, $data)
    {
        //check data sanity
        if (true !== \is_array($data)) {
            return;
        }

        //default type for simple filter
        $data['type'] = !isset($data['type']) || !is_numeric($data['type']) ? DateType::TYPE_EQUAL : $data['type'];

        // Some types do not require a value to be set (NULL, NOT NULL).
        if (!($data['value'] ?? false) && !$this->typeRequiresValue($data['type'])) {
            return;
        }

        switch ($data['type']) {
            case DateType::TYPE_EQUAL:
                $this->active = true;

                return $this->applyTypeIsEqual($queryBuilder, $field, $data);

            case DateType::TYPE_GREATER_THAN:
                if (!\array_key_exists('value', $data) || !$data['value']) {
                    return;
                }

                $this->active = true;

                return $this->applyTypeIsGreaterThan($queryBuilder, $field, $data);

            case DateType::TYPE_LESS_EQUAL:
                if (!\array_key_exists('value', $data) || !$data['value']) {
                    return;
                }

                $this->active = true;

                return $this->applyTypeIsLessEqual($queryBuilder, $field, $data);

            case DateType::TYPE_NULL:
            case DateType::TYPE_NOT_NULL:
                $this->active = true;

                return $this->applyType($queryBuilder, $this->getOperator($data['type']), $field, null);

            case DateType::TYPE_GREATER_EQUAL:
            case DateType::TYPE_LESS_THAN:
                $this->active = true;

                return $this->applyType($queryBuilder, $this->getOperator($data['type']), $field, $data['value']);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions()
    {
        return ['input_type' => 'datetime'];
    }

    /**
     * {@inheritdoc}
     */
    public function getRenderSettings()
    {
        $name = DateType::class;

        if ($this->time && $this->range) {
            $name = DateTimeRangeType::class;
        } elseif ($this->time) {
            $name = DateTimeType::class;
        } elseif ($this->range) {
            $name = DateRangeType::class;
        }

        return [$name, [
            'field_type' => $this->getFieldType(),
            'field_options' => $this->getFieldOptions(),
            'label' => $this->getLabel(),
        ]];
    }

    /**
     * @param string $field
     * @param array  $data
     */
    protected function applyTypeIsLessEqual(ProxyQueryInterface $queryBuilder, $field, $data)
    {
    }

    /**
     * @param string $field
     * @param array  $data
     */
    protected function applyTypeIsGreaterThan(ProxyQueryInterface $queryBuilder, $field, $data)
    {
    }

    /**
     * @param string $field
     * @param array  $data
     */
    protected function applyTypeIsEqual(ProxyQueryInterface $queryBuilder, $field, $data)
    {
    }

    /**
     * @param string    $operation
     * @param string    $field
     * @param \DateTime $datetime
     */
    protected function applyType(ProxyQueryInterface $queryBuilder, $operation, $field, \DateTime $datetime = null)
    {
        $queryBuilder->field($field)->$operation($datetime);
        $this->active = true;
    }

    /**
     * Returns if the filter type requires a value to be set.
     *
     * @param int $type
     *
     * @return bool
     */
    protected function typeRequiresValue($type)
    {
        return \in_array($type, [
            DateType::TYPE_NULL,
            DateType::TYPE_NOT_NULL,
        ], true);
    }

    /**
     * Resolves DataType:: constants to MongoDb operators.
     *
     * @param int $type
     *
     * @return string
     */
    protected function getOperator($type)
    {
        $choices = [
            DateType::TYPE_NULL => 'equals',
            DateType::TYPE_NOT_NULL => 'notEqual',
            DateType::TYPE_EQUAL => 'equals',
            DateType::TYPE_GREATER_EQUAL => 'gte',
            DateType::TYPE_GREATER_THAN => 'gt',
            DateType::TYPE_LESS_EQUAL => 'lte',
            DateType::TYPE_LESS_THAN => 'lt',
        ];

        return $choices[(int) $type];
    }
}
