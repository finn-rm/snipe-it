@extends('layouts/default')

{{-- Page title --}}
@section('title')
   Quick Scan Checkout
   @parent
@stop

{{-- Page content --}}
@section('content')
{{ Form::open(['method' => 'POST', 'class' => 'form-horizontal', 'role' => 'form', 'id' => 'checkout-form' ]) }}
       <!-- left column -->
       <div class="col-md-6">
           <div class="box box-default">
               <div class="box-header with-border">
                   <h2 class="box-title"> Checkout Assets </h2>
               </div>
               <div class="box-body">
                   {{csrf_field()}}
                   <!-- Asset Tag -->
                   <div class="form-group {{ $errors->has('asset_tag') ? 'error' : '' }}">
                       {{ Form::label('asset_tag', trans('general.asset_tag'), array('class' => 'col-md-3 control-label', 'id' => 'checkout_tag')) }}
                       <div class="col-md-9">
                           <div class="input-group date col-md-5" data-date-format="yyyy-mm-dd">
                               <input type="text" class="form-control" name="asset_tag" id="asset_tag" value="{{ Request::old('asset_tag') }}">

                           </div>
                           {!! $errors->first('asset_tag', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                       </div>
                   </div>
    
                   <!-- User -->
                   @include ('partials.forms.edit.user-select', ['translated_name' => trans('general.user'), 'fieldname' => 'assigned_user', 'required'=>'true'])
               </div> <!--/.box-body-->
               <div class="box-footer">
                   <button type="submit" id="checkin_button" class="btn btn-success pull-right"><i class="fas fa-check icon-white" aria-hidden="true"></i> Checkout</button>
               </div>
           </div>
           {{Form::close()}}
       </div> <!--/.col-md-6-->

       <div class="col-md-6">
           <div class="box box-default" id="checkedout-div" style="display: none">
               <div class="box-header with-border">
                   <h2 class="box-title"> Checkout Status (<span id="checkout-counter">0</span> assets checked out </h2>
               </div>
               <div class="box-body">
    
                   <table id="checkedout" class="table table-striped snipe-table">
                       <thead>
                       <tr>
                           <th>{{ trans('general.asset_tag') }}</th>
                           <th>Checkout Status</th>
                           <th></th>
                       </tr>
                       <tr id="checkout-loader" style="display: none;">
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

$("#checkout-form").submit(function (event) {
    event.preventDefault();

    var assetTag = $('#checkout-form').serializeArray()[2].value;
    var assignedUser = $('#checkout-form').serializeArray()[3].value;

    if ( !assetTag || !assignedUser ) { return; }
    $('#asset_tag')[0].value = ""
    $('#checkedout-div').show();
    $('#checkout-loader').show();

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
            createCheckoutStatus(assetTag, data.messages, 1);
            return;
        }
        const assetID = data.id;
        const assetStatusID = data.status_label.id;

        if ( !data.user_can_checkout ){
            let checkoutStatus = `Asset already checked out.`; 
            createCheckoutStatus(assetTag, checkoutStatus, 1);
            return;
        }

        fetch(`/api/v1/hardware/${assetID}/checkout`, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                status_id: assetStatusID,
                checkout_to_type: "user",
                assigned_user: assignedUser,
            })
        })
        .then(response => response.json())
        .then(data => {
            let checkoutStatus = `Asset checked out`; 
            createCheckoutStatus(assetTag, checkoutStatus, 0);
            incrementOnSuccess();
        })
        .catch(error => {
            console.error('Error:', error);
        });
    })
    .catch(error => {
        console.error('Error:', error);
    });

    return false;
});

function createCheckoutStatus(assetTag, checkoutStatus, error) {
    $('#checkout-loader').hide();
    $('#checkedout tbody').prepend(`<tr class='${error ? 'danger' : 'success'}'><td>${assetTag}</td><td>${checkoutStatus}</td><td><i class='fas fa-times text-${error ? 'danger' : 'success'}'></i></td></tr>`);
}

function incrementOnSuccess() {
    var x = parseInt($('#checkout-counter').html());
    y = x + 1;
    $('#checkout-counter').html(y);
}

$("#checkout_tag").focus();

</script>
@stop
