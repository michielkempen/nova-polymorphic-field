<?php

namespace MichielKempen\NovaPolymorphicField;

use Laravel\Nova\Fields\Field;
use Laravel\Nova\Http\Requests\NovaRequest;
use Illuminate\Database\Eloquent\Relations\Relation;

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

            foreach ($this->meta['types'] as $type) {
                if ($this->mapToKey($type['value']) == $value) {
                    return $type['label'];
                }
            }

            return null;
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
        $this->meta['types'][] = [
            'value' => $typeClass,
            'label' => $label,
            'fields' => $fields
        ];

        return $this;
    }

    /**
     * @param mixed $model
     * @param null $attribute
     */
    public function resolveForDisplay($model, $attribute = null)
    {
        parent::resolveForDisplay($model, $this->attribute.'_type');

        foreach ($this->meta['types'] as &$type) {
            $type['active'] = $this->mapToKey($type['value']) == $model->{$this->attribute . '_type'};

            foreach ($type['active'] ? $type['fields'] : [] as $field) {
                $field->resolveForDisplay($model->{$this->attribute});
            }
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
        $result = $this->mapToClass($model->{$this->attribute . '_type'});

        foreach ($this->meta['types'] as $type) {

            $relatedModel = new $type['value'];

            if($this->mapToKey($type['value']) == $model->{$this->attribute . '_type'}) {
                $relatedModel = ($model->{$this->attribute})
                    ? $model->{$this->attribute}
                    : $relatedModel->newQuery()->findOrFail($model->{$this->attribute . '_id'});
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
                $relatedModel = new $type['value'];

                if($this->mapToKey($type['value']) == $model->{$this->attribute . '_type'}) {
                    $relatedModel = $relatedModel->newQuery()->findOrFail($model->{$this->attribute . '_id'});
                } elseif(! is_null($model->{$this->attribute . '_type'})) {
                    $oldRelatedClass = $this->mapToClass($model->{$this->attribute . '_type'});
                    $oldRelatedModel = (new $oldRelatedClass)->newQuery()->findOrFail($model->{$this->attribute . '_id'});
                    $oldRelatedModel->delete();
                }

                $callbacks = [];
                foreach ($type['fields'] as $field) {
                    $callbacks[] = $field->fill($request, $relatedModel);
                }

                $relatedModel->save();

                $model->{$this->attribute.'_id'}   = $relatedModel->id;
                $model->{$this->attribute.'_type'} = $this->mapToKey($type['value']);

                return function () use ($callbacks) {
                    collect(array_filter($callbacks))->each->__invoke();
                };
            }

        }
    }

    /**
     * @param $class
     * @return string
     */
    protected function mapToKey($class)
    {
        return array_search($class, Relation::$morphMap) ?: $class;
    }

    /**
     * @param $key
     * @return string
     */
    protected function mapToClass($key)
    {
        return Relation::$morphMap[$key] ?? $key;
    }

    /**
     * When set to true, the field should not be displayed when updating the resource. This can be
     * used when you do not want the user to change the type once a relationship has been created.
     *
     * @return self
     */
    public function hideTypeWhenUpdating()
    {
        return $this->withMeta([
            'hideTypeWhenUpdating' => true,
        ]);
    }

    /**
     * When set to true, the field should disabled when updating the resource. This can be
     * used when you do not want the user to change the type once a relationship has been created.
     *
     * @return self
     */
    public function disableTypeWhenUpdating()
    {
        return $this->withMeta([
            'disableTypeWhenUpdating' => true,
        ]);
    }
}
