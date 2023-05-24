@extends('layouts/edit-form', [
   'createText' => trans('admin/models/table.create') ,
   'updateText' => trans('admin/models/table.update'),
   'topSubmit' => true,
   'helpPosition' => 'right',
   'helpText' => trans('admin/models/general.about_models_text'),
   'formAction' => (isset($item->id)) ? route('models.update', ['model' => $item->id]) : route('models.store'),
])

{{-- Page content --}}
@section('inputFields')

@include ('partials.forms.edit.name', ['translated_name' => trans('admin/models/table.name'), 'required' => 'true'])
@include ('partials.forms.edit.category-select', ['translated_name' => trans('admin/categories/general.category_name'), 'fieldname' => 'category_id', 'required' => 'true', 'category_type' => 'asset'])
@include ('partials.forms.edit.manufacturer-select', ['translated_name' => trans('general.manufacturer'), 'fieldname' => 'manufacturer_id'])
@include ('partials.forms.edit.model_number')
@include ('partials.forms.edit.depreciation')

<!-- Custom Fieldset -->
@livewire('custom-field-set-default-values-for-model',["model_id" => $item->id])

@include ('partials.forms.edit.image-upload', ['image_path' => app('models_upload_path')])


@stop
