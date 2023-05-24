@extends('layouts/default')

{{-- Page title --}}
@section('title')
   {{ trans('admin/hardware/general.checkout') }}
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
       <div class="col-md-7">
           <div class="box box-default">
               <form class="form-horizontal" method="post" action="" autocomplete="off">
                   <div class="box-header with-border">
                       <h2 class="box-title"> {{ trans('admin/hardware/form.tag') }} {{ $asset->asset_tag }}</h2>
                   </div>
                   <div class="box-body">
                   {{csrf_field()}}
                       @if ($asset->company && $asset->company->name)
                           <div class="form-group">
                               {{ Form::label('model', trans('general.company'), array('class' => 'col-md-3 control-label')) }}
                               <div class="col-md-8">
                                   <p class="form-control-static">
                                       {{ $asset->company->name }}
                                   </p>
                               </div>
                           </div>
                       @endif
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

                       <!-- Status -->
                       <div class="form-group {{ $errors->has('status_id') ? 'error' : '' }}">
                           {{ Form::label('status_id', trans('admin/hardware/form.status'), array('class' => 'col-md-3 control-label')) }}
                           <div class="col-md-7 required">
                               {{ Form::select('status_id', $statusLabel_list, $asset->status_id, array('class'=>'select2', 'style'=>'width:100%','', 'aria-label'=>'status_id')) }}
                               {!! $errors->first('status_id', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                           </div>
                       </div>

                   @include ('partials.forms.checkout-selector', ['user_select' => 'true','asset_select' => 'false', 'location_select' => 'false'])

                   @include ('partials.forms.edit.user-select', ['translated_name' => trans('general.user'), 'fieldname' => 'assigned_user', 'required'=>'true'])

                   <!-- We have to pass unselect here so that we don't default to the asset that's being checked out. We want that asset to be pre-selected everywhere else. -->
                   @include ('partials.forms.edit.asset-select', ['translated_name' => trans('general.asset'), 'fieldname' => 'assigned_asset', 'unselect' => 'true', 'style' => 'display:none;', 'required'=>'true'])

                   @include ('partials.forms.edit.location-select', ['translated_name' => trans('general.location'), 'fieldname' => 'assigned_location', 'style' => 'display:none;', 'required'=>'true'])



                       @if ($asset->requireAcceptance() || $asset->getEula() || ($snipeSettings->webhook_endpoint!=''))
                           <div class="form-group notification-callout">
                               <div class="col-md-8 col-md-offset-3">
                                   <div class="callout callout-info">

                                       @if ($asset->requireAcceptance())
                                           <i class="far fa-envelope" aria-hidden="true"></i>
                                           {{ trans('admin/categories/general.required_acceptance') }}
                                           <br>
                                       @endif

                                       @if ($asset->getEula())
                                           <i class="far fa-envelope" aria-hidden="true"></i>
                                           {{ trans('admin/categories/general.required_eula') }}
                                           <br>
                                       @endif

                                       @if ($snipeSettings->webhook_endpoint!='')
                                           <i class="fab fa-slack" aria-hidden="true"></i>
                                           {{ trans('general.webhook_msg_note') }}
                                       @endif
                                   </div>
                               </div>
                           </div>
                       @endif

                   </div> <!--/.box-body-->
                   <div class="box-footer">
                       <a class="btn btn-link" href="{{ URL::previous() }}"> {{ trans('button.cancel') }}</a>
                       <button type="submit" class="btn btn-primary pull-right"><i class="fas fa-check icon-white" aria-hidden="true"></i> {{ trans('general.checkout') }}</button>
                   </div>
               </form>
           </div>
       </div> <!--/.col-md-7-->

       <!-- right column -->
       <div class="col-md-5" id="current_assets_box" style="display:none;">
           <div class="box box-primary">
               <div class="box-header with-border">
                   <h2 class="box-title">{{ trans('admin/users/general.current_assets') }}</h2>
               </div>
               <div class="box-body">
                   <div id="current_assets_content">
                   </div>
               </div>
           </div>
       </div>
   </div>
@stop

@section('moar_scripts')
   @include('partials/assets-assigned')

   <script>
       //        $('#checkout_at').datepicker({
       //            clearBtn: true,
       //            todayHighlight: true,
       //            endDate: '0d',
       //            format: 'yyyy-mm-dd'
       //        });


   </script>
@stop
