<?php

namespace MichielKempen\NovaPolymorphicField;

use Laravel\Nova\Fields\Field;
use Laravel\Nova\Http\Requests\NovaRequest;

class PolymorphicField extends Field
{
    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'polymorphic-field';

    /**
     * PolymorphicField constructor.
     *
     * @param string $name
     * @param null $attribute
     */
    public function __construct($name, $attribute = null)
    {
        parent::__construct($name, $attribute);

        $this->withMeta(['types' => []]);

        $this->displayUsing(function ($value) {
            $result = null;

            foreach ($this->meta['types'] as $type) {
                if ($type['value'] == $value) {
                    $result = $type['label'];
                }
            }

            return $result;
        });
    }

    /**
     * @param $typeClass
     * @param $label
     * @param array $fields
     * @return PolymorphicField
     */
    public function type(string $label, string $typeClass, array $fields)
    {
        return $this->withMeta([
            'types' => array_merge($this->meta['types'], [
                [
                    'value' => $typeClass,
                    'label' => $label,
                    'fields' => $fields
                ]
            ]),
        ]);
    }

    /**
     * @param mixed $model
     * @param null $attribute
     */
    public function resolveForDisplay($model, $attribute = null)
    {
        parent::resolveForDisplay($model, $this->attribute.'_type');

        foreach ($this->meta['types'] as $index => $type) {
            $this->meta['types'][$index]['active'] = $type['value'] == $model->{$this->attribute . '_type'};
        }
    }

    /**
     * Retrieve values of dependency fields
     *
     * @param mixed $model
     * @param string $attribute
     * @return array|mixed
     */
    protected function resolveAttribute($model, $attribute)
    {
        $result = $model->{$this->attribute.'_type'};

        foreach ($this->meta['types'] as $type) {

            $class = Relation::getMorphedModel($type['value']) ?? $type['value'];
            $relatedModel = new $class;

            if($type['value'] == $model->{$this->attribute . '_type'}) {
                $relatedModel = $relatedModel->newQuery()->findOrFail($model->{$this->attribute . '_id'});
            }

            foreach ($type['fields'] as $field) {
                $field->resolve($relatedModel);
            }

        }

        return $result;
    }

    /**
     * Fills the attributes of the model within the container if the dependencies for the container are satisfied.
     *
     * @param NovaRequest $request
     * @param string $requestAttribute
     * @param object $model
     * @param string $attribute
     */
    protected function fillAttributeFromRequest(NovaRequest $request, $requestAttribute, $model, $attribute)
    {
        foreach ($this->meta['types'] as $type) {

            if($request->get($attribute) == $type['value']) {

                $class = Relation::getMorphedModel($type['value']) ?? $type['value'];
                $relatedModel = new $class;

                if($type['value'] == $model->{$this->attribute . '_type'}) {
                    $relatedModel = $relatedModel->newQuery()->findOrFail($model->{$this->attribute . '_id'});
                } elseif(! is_null($model->{$this->attribute . '_type'})) {
                    $oldRelatedModel = (new $model->{$this->attribute . '_type'})->newQuery()->findOrFail($model->{$this->attribute . '_id'});
                    $oldRelatedModel->delete();
                }

                foreach ($type['fields'] as $field) {
                    $field->fill($request, $relatedModel);
                }

                $relatedModel->save();

                $model->{$this->attribute.'_id'} = $relatedModel->id;
                $model->{$this->attribute.'_type'} = $type['value'];
            }

        }
    }
}
