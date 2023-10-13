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

       <div class="col-md-6">
           <div class="box box-default" id="checkedin-div" style="display: none">
               <div class="box-header with-border">
                   <h2 class="box-title"> {{ trans('general.quickscan_checkin_status') }} (<span id="checkin-counter">0</span> {{ trans('general.assets_checked_in_count') }}) </h2>
               </div>
               <div class="box-body">
    
                   <table id="checkedin" class="table table-striped snipe-table">
                       <thead>
                       <tr>
                           <th>{{ trans('general.asset_tag') }}</th>
                           <th>{{ trans('general.quickscan_checkin_status') }}</th>
                           <th></th>
                       </tr>
                       <tr id="checkin-loader" style="display: none;">
                           <td colspan="3">
                               <i class="fas fa-spinner spin" aria-hidden="true"></i> {{ trans('general.processing') }}...
                           </td>
                       </tr>
                       </thead>
                       <tbody>
                       </tbody>
                   </table>
               </div>
           </div>
       </div>       
@stop


@section('moar_scripts')
<script nonce="{{ csrf_token() }}">

$("#checkin-form").submit(function (event) {
    event.preventDefault();

    var assetTag = $('#checkin-form').serializeArray()[2].value;
    var selectedLocation = $('#checkin-form').serializeArray()[3];
    var selectedLocationName = document.getElementById('select2-location_id_location_select-container').title;

    if ( !assetTag ) { return; }
    $('#checkin-form')[0].reset()
    $('#checkedin-div').show();
    $('#checkin-loader').show();

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
            createCheckinStatus(assetTag, data.messages, 1);
            return;
        }
        const assetID = data.id;
        const assetStatusID = data.status_label.id;
        const currentLocation = data.location;
        const currentRTDLocation = data.rtd_location;
        // Item is currently checked in
        if (data.user_can_checkout) {
            if ( selectedLocation.value === '' ) { 
                let checkinStatus = 'Asset is checked-in and no new location was specified. No changes made.';
                createCheckinStatus(assetTag, checkinStatus, 1);
                return; 
            }
            if ( currentLocation && (selectedLocation.value == currentLocation.id && selectedLocation.value == currentRTDLocation.id) ) {
                let checkinStatus = 'Asset location is the same. No changes made.'; 
                createCheckinStatus(assetTag, checkinStatus, 1);
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
                let checkinStatus = `Asset already checked in, but location changed to ${selectedLocationName}.`; 
                createCheckinStatus(assetTag, checkinStatus, 0);
                incrementOnSuccess();
            })
            .catch(error => {
                createCheckinStatus(assetTag, error, 1);
            });
        } else {
            // Item is currently checked out
            checkInAsset(assetTag, assetID, currentLocation, currentRTDLocation, selectedLocation, selectedLocationName, assetStatusID);
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });

    return false;
});

function checkInAsset(_assetTag, _assetID, _currentLocation, _currentRTDLocation, _selectedLocation, _selectedLocationName, _assetStatusID){

    // Check in the asset
    fetch(`/api/v1/hardware/bytag/${_assetTag}/checkin`, {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            location_id: _selectedLocation.value
        })
    }).then(response => response.json())
    .then(updatedData => {
        // Update the default location if it was changed.

        if ( _selectedLocation.value == '' || (_currentRTDLocation.id == _selectedLocation.value) ) { 
            let checkedInStatus = 'Checked into default location';
            createCheckinStatus(_assetTag, checkedInStatus, 0);
            incrementOnSuccess();
            return;
        }
        fetch(`/api/v1/hardware/${_assetID}`, {
                method: 'PATCH',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    rtd_location_id: _selectedLocation.value
                })
            }).then(response => response.json())
            .then(updatedData => {
                let checkinStatus = `Asset checked into ${_selectedLocationName}.`; 
                createCheckinStatus(_assetTag, checkinStatus, 0);
                incrementOnSuccess();
            })
            .catch(error => {
                createCheckinStatus(_assetTag, error, 1);
        });

    })
    .catch(error => {
        console.error('Error', error);
    });

}

function createCheckinStatus(assetTag, checkinStatus, error) {
    $('#checkin-loader').hide();
    $('#checkedin tbody').prepend(`<tr class='${error ? 'danger' : 'success'}'><td>${assetTag}</td><td>${checkinStatus}</td><td><i class='fas fa-times text-${error ? 'danger' : 'success'}'></i></td></tr>`);
}

function incrementOnSuccess() {
    var x = parseInt($('#checkin-counter').html());
    y = x + 1;
    $('#checkin-counter').html(y);
}

$("#checkin_tag").focus();

</script>
@stop
