<?php

namespace Laradium\Laradium\Content\Base\Fields;


use Laradium\Laradium\Content\Registries\WidgetRegistry;
use Illuminate\Database\Eloquent\Model;
use Laradium\Laradium\Base\Field;
use Laradium\Laradium\Base\Fields\Hidden;
use Laradium\Laradium\Base\FieldSet;

class WidgetConstructor extends Field
{

    /**
     * @var string
     */
    protected $relationName;

    /**
     * @var \Illuminate\Foundation\Application|mixed
     */
    protected $widgetRegistry;

    /**
     * @var
     */
    protected $fieldGroups;

    /**
     * @var string
     */
    protected $morphName;

    /**
     * @var string
     */
    protected $morphClass;

    /**
     * @var string
     */
    protected $morphAttributeName;
    /**
     * @var string
     */
    protected $sortableColumn;

    /**
     * @var bool
     */
    protected $isSortable;

    /**
     * WidgetConstructor constructor.
     * @param $parameters
     * @param Model $model
     */
    public function __construct($parameters, Model $model)
    {
        parent::__construct($parameters, $model);

        $this->widgetRegistry = app(WidgetRegistry::class);
        $this->relationName = 'blocks';
        $this->morphName = 'block';
        $this->sortableColumn = 'sequence_no';
        $this->isSortable = true;

    }

    /**
     * @param array $parentAttributeList
     * @param null $model
     * @return $this|Field
     */
    public function build($parentAttributeList = [], $model = null)
    {
        $this->parentAttributeList = $parentAttributeList;
        $fieldList = [];
        $rules = [];
        $items = $this->relation()->orderBy('sequence_no')->get();
        foreach ($items as $item) {
            $widgetClass = $this->widgetRegistry->getByModel($item->block_type);
            $widget = new $widgetClass;
            $fieldSet = new FieldSet;
            $fieldSet->setModel($item);
            $widget->fields($fieldSet);
            $fields = $fieldSet->fields();
            $model = new $item->block_type;
            $model = $model->find($item->block_id);
            $this->morphClass = $item->block_type;
            $this->morphAttributeName = strtolower(array_last(explode('\\', $this->morphClass)));

            $attributeList = array_merge($this->parentAttributeList, [
                $this->relationName,
                $item->id,
            ]);

            foreach ($fields as $field) {
                $morphAttributeList = array_merge($this->parentAttributeList, [
                    $this->relationName,
                    $item->id,
                    $this->morphAttributeName
                ]);
                $clonedField = clone $field;
                $clonedField->setModel($model);
                $clonedField->build($morphAttributeList, $model);

                $fieldList[$item->id]['fields'][] = $clonedField;
                $rules[key($clonedField->getRules())] = array_first($clonedField->getRules());

            }

            $fieldList[$item->id]['fields'][] = $this->createContentTypeField($this->morphClass, $morphAttributeList);
            $fieldList[$item->id]['fields'][] = $this->createMorphNameField($this->morphClass, $morphAttributeList);
            $fieldList[$item->id]['fields'][] = $this->createIdField($item, $attributeList);

            if ($this->isSortable()) {
                $fieldList[$item->id]['fields'][] = $this->createSortableField($item, $attributeList);
                $fieldList[$item->id][$this->sortableColumn] = $item->{$this->sortableColumn};
            }
            $fieldList[$item->id]['name'] = 'Widget - ' . str_singular(ucfirst(str_replace('_', ' ',
                    $model->getTable())));
        }
        if ($rules) {
            $this->validationRules = $rules;
        }
        $this->fieldGroups = $fieldList;

        return $this;
    }

    /**
     * @param $model
     * @param $attributeList
     * @return Hidden
     */
    public function createSortableField($model, $attributeList)
    {
        $field = new Hidden($this->sortableColumn, $model);
        $field->build($attributeList);
        $field->class('js-sortable-item');
        $field->params([
            'orderable' => true
        ]);

        return $field;
    }

    /**
     * @param $morphClass
     * @param $attributeList
     * @return Hidden
     */
    public function createContentTypeField($morphClass, $attributeList)
    {
        $field = new Hidden('morph_type', $this->model);
        $field->build($attributeList);
        $field->setValue($morphClass);

        return $field;
    }

    /**
     * @param $morphClass
     * @param $attributeList
     * @return Hidden
     */
    public function createMorphNameField($morphClass, $attributeList)
    {
        $field = new Hidden('morph_name', $this->model);
        $field->build($attributeList);
        $field->setValue($this->morphName);

        return $field;
    }

    /**
     * @param $model
     * @param $attributeList
     * @return Hidden
     */
    public function createIdField($model, $attributeList)
    {
        $field = new Hidden('id', $model);
        $field->build($attributeList);

        return $field;
    }

    /**
     * @return array
     */
    public function template()
    {
        $widgets = $this->widgetRegistry->all()->map(function ($item) {
            return array_last(explode('\\', key($item)));
        })->toArray();

        $templates = [];
        $baseAttributeList = array_merge($this->parentAttributeList, [
            $this->relationName,
            '__ID__',
        ]);
        foreach ($this->widgetRegistry->all() as $widget) {
            $widgetClass = key($widget);
            $widgetName = array_last(explode('\\', $widgetClass));
            $widget = new $widgetClass;
            $widgetModelClass = $widget->model();
            $widgetModel = new $widgetModelClass;
            $fieldSet = new FieldSet;
            $fieldSet->setModel($widgetModel);
            $widget->fields($fieldSet);
            $fields = $fieldSet->fields();
            $morphAttributeName = strtolower(array_last(explode('\\', $widgetModelClass)));

            $attributeList = array_merge($this->parentAttributeList, [
                $this->relationName,
                '__ID__',
                $morphAttributeName
            ]);

            foreach ($fields as $f) {
                $field = clone $f;

                $field->build($attributeList, null);
                $field->isTemplate(true);
                $field->setValue(null);

                $templates[$widgetName]['fields'][] = $field->formattedResponse($field);
            }

            $contentTypeField = $this->createContentTypeField($widgetModelClass, $attributeList);
            $morphNameField = $this->createMorphNameField($widgetModel, $attributeList);
            $templates[$widgetName]['fields'][] = $contentTypeField->formattedResponse($contentTypeField);
            $templates[$widgetName]['fields'][] = $morphNameField->formattedResponse($morphNameField);
            $templates[$widgetName]['name'] = 'Widget - ' . ucfirst($morphAttributeName);
            $templates[$widgetName]['isSortable'] = true;
            if ($this->isSortable()) {
                $sortableField = $this->createSortableField($this->model, $baseAttributeList);
                $templates[$widgetName]['fields'][] = $sortableField->formattedResponse($sortableField);
                $templates[$widgetName][$this->sortableColumn] = 0;
            }

            $templates[$widgetName]['id'] = 0;
            $templates[$widgetName]['show'] = false;
            $templates[$widgetName]['order'] = 0;
            $templates[$widgetName]['replacementIds'] = [];
        }

        return ([
            'widgets'   => $widgets,
            'templates' => $templates
        ]);
    }

    /**
     * @param null $field
     * @return array
     */
    public function formattedResponse($field = null)
    {
        $items = [];
        foreach ($this->fieldGroups as $id => $group) {
            $item = [
                'id'             => $id,
                'name'           => $group['name'],
                'replacementIds' => [],
                'show'           => false,
                'isSortable'     => $this->isSortable(),
                'url'            => '/admin/content-block/' . $id
            ];
            if ($this->isSortable()) {
                $item['order'] = $group[$this->sortableColumn];
            }

            foreach ($group['fields'] as $field) {
                $item['fields'][] = $field->formattedResponse();
            }
            $items[] = $item;
        }

        return [
            'type'        => 'widget-constructor',
            'full_column' => true,
            'name'        => $this->relationName,
            'template'    => $this->template(),
            'tab'         => $this->tab(),
            'col'         => $this->col,
            'items'       => $items
        ];

    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function relation(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->model()->load($this->relationName)->{$this->relationName}();
    }

    /**
     * @return bool
     */
    public function isSortable()
    {
        return $this->isSortable;
    }
}