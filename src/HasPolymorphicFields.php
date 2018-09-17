<?php

namespace MichielKempen\NovaPolymorphicField;

use Illuminate\Support\Facades\Route;
use Laravel\Nova\Fields\FieldCollection;
use Laravel\Nova\Http\Requests\NovaRequest;

trait HasPolymorphicFields
{
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
                    if($this->requestIsAssociateRequest()) {
                        $availableFields = array_merge($availableFields, $type['fields']);
                    }
                }
            } else {
                $availableFields[] = $field;
            }
        }

        return new FieldCollection(array_values($this->filter($availableFields)));
    }

    /**
     * @return bool
     */
    protected function requestIsAssociateRequest(): bool
    {
        return ends_with(Route::currentRouteAction(), 'AssociatableController@index');
    }
}