<?php

namespace Statamic\Addons\StatamifyVariants;


use Statamic\API\Helper;
use Statamic\CP\Fieldset;
use Statamic\Extend\Fieldtype;
use Statamic\CP\FieldtypeFactory;

class StatamifyVariantsFieldtype extends Fieldtype
{
    /**
     * The blank/default value
     *
     * @return array
     */
    public function blank()
    {
        return null;
    }

    public function preProcess($data)
    {

        if (! $data) {
            return ['settings' => []];
        }

        $this->process = 'preProcess';

        return $this->performProcess($data);
    }

    public function process($data)
    {
        $this->process = 'process';

        return $this->performProcess($data);
    }

    private function performProcess($data)
    {
        $processed = [];

        foreach ($data['settings'] as $i => $row) {
            $processed_row = $this->processRow($row);

            // Empty rows can create issues with
            // populating data in subsequent requests
            // so we completely remove them.
            if (! empty($processed_row)) {
                $processed[$i] = $processed_row;
            }
        }

        $data['settings'] = array_values($processed);

        return $data;
    }

    private function processRow($row_data)
    {
        $processed = [];

        foreach ($row_data as $field => $value) {
            $field_config = Helper::ensureArray($this->getFieldConfig('fields.'.$field));

            $processed[$field] = $this->processField($value, $field_config);
        }

        return array_filter($processed);
    }

    private function processField($field_data, $field_config)
    {
        $type = array_get($field_config, 'type', 'text');

        $fieldtype = FieldtypeFactory::create($type, $field_config);

        // Either call $fieldtype->process($data) or $fieldtype->preProcess($data)
        return call_user_func([$fieldtype, $this->process], $field_data);
    }
}
