@extends('layouts/default')

{{-- Page title --}}
@section('title')
   {{ trans('general.quickscan_checkin') }}
   @parent
@stop

{{-- Page content --}}
@section('content')
{{ Form::open(['method' => 'POST', 'class' => 'form-horizontal', 'role' => 'form', 'id' => 'checkin-form' ]) }}
       <!-- left column -->
       <div class="col-md-6">
           <div class="box box-default">
               <div class="box-header with-border">
                   <h2 class="box-title"> {{ trans('admin/hardware/general.bulk_checkin') }} </h2>
               </div>
               <div class="box-body">
                   {{csrf_field()}}
                   <!-- Asset Tag -->
                   <div class="form-group {{ $errors->has('asset_tag') ? 'error' : '' }}">
                       {{ Form::label('asset_tag', trans('general.asset_tag'), array('class' => 'col-md-3 control-label', 'id' => 'checkin_tag')) }}
                       <div class="col-md-9">
                           <div class="input-group date col-md-5" data-date-format="yyyy-mm-dd">
                               <input type="text" class="form-control" name="asset_tag" id="asset_tag" value="{{ Request::old('asset_tag') }}">

                           </div>
                           {!! $errors->first('asset_tag', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                       </div>
                   </div>
    
                   <!-- Locations -->
                   @include ('partials.forms.edit.location-select', ['translated_name' => trans('general.location'), 'fieldname' => 'location_id'])
               </div> <!--/.box-body-->
               <div class="box-footer">
                   <button type="submit" id="checkin_button" class="btn btn-success pull-right"><i class="fas fa-check icon-white" aria-hidden="true"></i> {{ trans('general.checkin') }}</button>
               </div>
           </div>
           {{Form::close()}}
       </div> <!--/.col-md-6-->
@stop


@section('moar_scripts')
   <script nonce="{{ csrf_token() }}">
$("#checkin-form").submit(function (event) {
    $('#checkedin-div').show();
    $('#checkin-loader').show();

    event.preventDefault();

    var assetTag = $('#checkin-form').serializeArray()[2].value;
    var selectedLocation = $('#checkin-form').serializeArray()[3];
    $('#checkin-form')[0].reset()

    fetch(`/api/v1/hardware/bytag/${assetTag}`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'error') {
            console.log(data.messages); // Asset does not exist
            return;
        }

        const assetID = data.id;
        const currentLocation = data.location;
        const currentRTDLocation = data.rtd_location

        // Item is currently checked in
        if (data.user_can_checkout) {
            if ( selectedLocation.value === '' ) { 
                console.log('Asset is checked-in and no new location was specified. No changes made.');
                return; 
            }
            if ( selectedLocation.value == currentLocation.id && selectedLocation.value == currentRTDLocation.id) {
                console.log('Asset location is the same. No changes made.'); 
                return;                 
            }

            fetch(`/api/v1/hardware/${assetID}`, {
                method: 'PATCH',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    location_id: selectedLocation.value,
                    rtd_location_id: selectedLocation.value
                })
            }).then(response => response.json())
            .then(updatedData => {
                console.log(updatedData.messages)
            })
            .catch(error => {
                console.error('Error:', error);
            });
        } else {
            // Item is currently checked out
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });

    return false;
});




       function handlecheckinFail (data) {
           console.log('You goofed')
           console.log(data)
        }

       function incrementOnSuccess() {
            console.log('Increment?')
       }

       $("#checkin_tag").focus();

   </script>
@stop
