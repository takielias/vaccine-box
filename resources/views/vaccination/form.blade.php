<div class="row mb-4">
    <div class="col-md-4">
        <label class="form-label">   {!! Form::label('name', 'Patient Name') !!}
            <span class="text-danger">*</span>
        </label>
        <div>
            {{ Form::text('name', '', ['class' => 'form-control' .
            ($errors->has('name') ? ' is-invalid' : ''), 'placeholder' => 'Patient Name']) }}
            @invalid
        </div>
    </div>
    <div class="col-md-4">
        <label class="form-label">   {!! Form::label('email', 'Email') !!}
            <span class="text-danger">*</span>
        </label>
        <div>
            {{ Form::text('email', '', ['class' => 'form-control' .
            ($errors->has('email') ? ' is-invalid' : ''), 'placeholder' => 'Patient Email']) }}
            @invalid
        </div>
    </div>
    <div class="col-md-4">
        <label class="form-label">   {{ Form::label('nid', 'NID') }}
            <span class="text-danger">*</span>
        </label>
        <div>
            {{ Form::text('nid', '', ['required', 'class' => 'form-control' .
            ($errors->has('nid') ? ' is-invalid' : ''), 'placeholder' => 'NID']) }}
            @invalid

        </div>
    </div>
</div>
<div class="row mb-4">
    <div class="col-md-4">
        <label class="form-label">   {{ Form::label('birth_date') }}
            <span class="text-danger">*</span>
        </label>
        <div>
            {{ Form::text('birth_date', '', ['class' => 'form-control' .
            ($errors->has('birth_date') ? ' is-invalid' : ''), 'id'=>'birth-date', 'placeholder' => 'Birth Date']) }}
            @invalid
        </div>
        <small class="form-hint"><b>You have to be at least 18 years of age today.</b></small>
    </div>
    <div class="col-md-4">
        <label class="form-label">   {{ Form::label('phone_number') }}</label>
        <div>
            {{ Form::text('phone_number', '', ['class' => 'form-control' .
            ($errors->has('phone_number') ? ' is-invalid' : ''), 'placeholder' => 'Phone number']) }}
            @invalid

        </div>
    </div>
    <div class="col-md-4">
        <label class="form-label">   {{ Form::label('Vaccination Center') }}</label>
        <div>
            {{ Form::select('vaccination_center_id', $vaccination_centers, null, ['class' => 'form-select' .
            ($errors->has('vaccination_center_id') ? ' is-invalid' : '')]) }}
            @invalid

        </div>
    </div>
</div>

<div class="form-footer">
    <div class="text-end">
        @submit(Register)
    </div>
</div>


