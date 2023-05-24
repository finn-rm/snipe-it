@extends('layouts/default')

{{-- Page title --}}
@section('title')
 {{ trans('admin/hardware/general.checkin') }}
 @parent
@stop

{{-- Page content --}}
@section('content')
 <style>

   .input-group {
     padding-left: 0px !important;
   }
 </style>


 <div class="row">
   <!-- left column -->
   <div class="col-md-9">
     <div class="box box-default">
       <div class="box-header with-border">
         <h2 class="box-title">{{ trans('admin/hardware/form.tag') }} {{ $asset->asset_tag }}</h2>
       </div><!-- /.box-header -->

       <div class="box-body">
         <div class="col-md-12">
           @if ($backto=='user')
             <form class="form-horizontal" method="post"
                   action="{{ route('hardware.checkin.store', array('assetId'=> $asset->id, 'backto'=>'user')) }}"
                   autocomplete="off">
               @else
                 <form class="form-horizontal" method="post"
                       action="{{ route('hardware.checkin.store', array('assetId'=> $asset->id)) }}" autocomplete="off">
                 @endif
                 {{csrf_field()}}

                 <!-- AssetModel name -->
                   <div class="form-group">
                     {{ Form::label('model', trans('admin/hardware/form.model'), array('class' => 'col-md-3 control-label')) }}
                     <div class="col-md-8">
                       <p class="form-control-static">
                         @if (($asset->model) && ($asset->model->name))
                           {{ $asset->model->name }}

                         @else
                           <span class="text-danger text-bold">
                     <i class="fas fa-exclamation-triangle"></i>{{ trans('admin/hardware/general.model_invalid')}}
                     <a href="{{ route('hardware.edit', $asset->id) }}"></a> {{ trans('admin/hardware/general.model_invalid_fix')}}</span>
                         @endif
                       </p>
                     </div>
                   </div>
 

                 @include ('partials.forms.edit.location-select', ['translated_name' => trans('general.location'), 'fieldname' => 'location_id', 'help_text' => ($asset->defaultLoc) ? 'You can choose to check this asset in to a location
other than the default location of '.$asset->defaultLoc->name.' if one is set.' : null])



                   <div class="box-footer">
                     <a class="btn btn-link" href="{{ URL::previous() }}"> {{ trans('button.cancel') }}</a>
                     <button type="submit" class="btn btn-primary pull-right"><i class="fas fa-check icon-white" aria-hidden="true"></i> {{ trans('general.checkin') }}</button>
                   </div>
                 </form>
         </div> <!--/.col-md-12-->
       </div> <!--/.box-body-->

     </div> <!--/.box.box-default-->
   </div>
 </div>

@stop
