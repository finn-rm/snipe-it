<!-- unit_cost -->
<div class="form-group {{ $errors->has('unit_cost') ? ' has-error' : '' }}">
    <label for="unit_cost" class="col-md-3 control-label">Unit Cost (€)</label>
    <div class="col-md-7 col-sm-12 {{ (Helper::checkIfRequired($item, 'unit_cost')) ? ' required' : '' }}">
        <input class="form-control" type="text" name="unit_cost" aria-label="unit_cost" id="unit_cost" value="{{ old('unit_cost', $item->unit_cost) }}" {{ (Helper::checkIfRequired($item, 'unit_cost')) ? ' data-validation="required" required' : '' }} />
        {!! $errors->first('unit_cost', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
    </div>
</div>
