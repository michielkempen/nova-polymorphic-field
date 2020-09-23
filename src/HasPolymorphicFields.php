<?php

namespace MichielKempen\NovaPolymorphicField;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Laravel\Nova\Fields\FieldCollection;
use Laravel\Nova\Http\Requests\NovaRequest;

trait HasPolymorphicFields
{
    protected $childFieldsArr = [];

    /**
     * @param NovaRequest $request
     * @return FieldCollection
     */
    public function availableFields(NovaRequest $request)
    {
        $fields = $this->fields($request);
        $availableFields = [];
        foreach ($fields as $field) {
            if ($field instanceof PolymorphicField) {
                $availableFields[] = $field;
                foreach ($field->meta['types'] as $type) {
                    if ($this->doesRouteRequireChildFieldsAndValidates()) {
                        if($type['value'] == $request->input($field->attribute)) {
                            $this->extractChildFields($type['fields'] ?? []);
                        }
                    }
                }
            } else {
                $availableFields[] = $field;
            }
        }

        if ($this->childFieldsArr) {
            $availableFields = array_merge($availableFields, $this->childFieldsArr);
        }

        return new FieldCollection(array_values($this->filter($availableFields)));
    }

    protected function extractChildFields($childFields)
    {
        foreach ($childFields as $childField) {
            if ($childField instanceof PolymorphicField) {
                $this->extractChildFields($childField->meta['fields']);
            } else {
                if (array_search($childField->attribute, array_column($this->childFieldsArr, 'attribute')) === false) {
                    $childField = $this->applyRulesForChildFields($childField);
                    $this->childFieldsArr[] = $childField;
                }
            }
        }
    }

    protected function applyRulesForChildFields($childField)
    {
        if (isset($childField->rules)) {
            $childField->rules[] = "sometimes:required:".$childField->attribute;
        }
        if (isset($childField->creationRules)) {
            $childField->creationRules[] = "sometimes:required:".$childField->attribute;
        }
        if (isset($childField->updateRules)) {
            $childField->updateRules[] = "sometimes:required:".$childField->attribute;
        }
        return $childField;
    }

    public function validateFields() {
        $availableFields = [];
        foreach ($this->action()->fields() as $field) {
            if ($field instanceof PolymorphicField) {
                $availableFields[] = $field;
                $this->extractChildFields($field->meta['fields']);
            } else {
                $availableFields[] = $field;
            }
        }

        if ($this->childFieldsArr) {
            $availableFields = array_merge($availableFields, $this->childFieldsArr);
        }

        $this->validate(collect($availableFields)->mapWithKeys(function ($field) {
            return $field->getCreationRules($this);
        })->all());
    }

    /**
     * @return bool
     */
    protected function doesRouteRequireChildFieldsAndValidates() : bool
    {
        if(!Str::endsWith(Route::currentRouteAction(), [
            'FieldDestroyController@handle',
            'ResourceUpdateController@handle',
            'ResourceStoreController@handle',
            'AssociatableController@index',
            'MorphableController@index',
        ])) {
            return false;
        }

        $backtrace = debug_backtrace(null, 10);

        $steps = [];
        foreach($backtrace as $step) {
            if(!isset($step['file'])) {
                continue;
            }
            if(Str::endsWith($step['file'], ['PerformsValidation.php'])) {
                return true;
            }
        }

        return false;
    }
}
