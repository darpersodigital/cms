<div class="checkbox-number">
    <div class="form-input-container">
        <label class="checkbox-container ">
            <input type="checkbox" class="custom-form-input" name="" {!! $checked == 1 ? 'checked=""' : '' !!}>
            <div></div>
        </label>
    </div>
    <input class="d-none" type="number" name="{{ $name}}"
        min="0" max="1" value="{{ $checked }}">
</div>