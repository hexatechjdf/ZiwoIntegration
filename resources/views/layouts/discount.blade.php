@php
    $default = $settings['discount_type'] ?? 'flat';
@endphp
<div class="form-group">
    <select name="setting[discount_type]" class="form-control" id="discount_type" onchange="applyDiscount()">
        <option value="flat" @if ($default == 'flat') selected @endif>Fixed</option>
        <option value="percentage" @if ($default == 'percentage') selected @endif>%</option>
    </select>
</div>
<input type="hidden" class="extra_percentage" name="setting[extra_percentage]"
                    value="{{ $settings['extra_percentage'] ?? 0 }}">
<div class="form-group" id="flat_fields">
    <label for="flat_discount">Fixed</label>
    <input type="number" id="flat_discount" name="flat_discount" min="0" value="0" class="form-control" placeholder="Enter fixed price">
    <span id="flat_discount_error" class="error-message text-danger"></span>
</div>

<div class="form-group" id="percentage_fields" style="display: none;">
    <label for="percentage_discount">Percentage</label>
    <input type="number" id="percentage_discount" name="percentage_discount" step="any" max="100"
        min="0" value="0" class="form-control" placeholder="Enter percentage">
    <span id="percentage_discount_error" class="error-message text-danger"></span>
</div>

<script>
    let discountType = document.getElementById('discount_type');
    let flatDiscountField = document.getElementById('flat_discount');
    let flatDiscountContainer = document.getElementById('flat_fields');
    let percentageContainer = document.getElementById('percentage_fields');
    let percentageDiscountField = document.getElementById('percentage_discount');
    let percentageDiscountError = document.getElementById('percentage_discount_error');
    let flat_discount_error = document.getElementById('flat_discount_error');
    let extra_percentage = document.querySelector('.extra_percentage');

    let lastValue = -2;

    function applyDiscount() {
        let type = discountType.value;
        let isFlat = type === 'flat';
        if (lastValue == -2) {
            if (extra_percentage) {
                lastValue = extra_percentage.value ?? 0;
            } else {
                lastValue = -1;
            }
            if (lastValue > 0) {
                if (isFlat) {
                    flatDiscountField.value = lastValue;
                } else {
                    percentageDiscountField.value = lastValue;
                }
            }
        }
        let req='required';
        if (isFlat) {
            flatDiscountContainer.style.display = 'block';
            percentageContainer.style.display = 'none';
            flatDiscountField.setAttribute(req,req);
            percentageDiscountField.removeAttribute(req);
        } else {
            flatDiscountContainer.style.display = 'none';
            percentageContainer.style.display = 'block';
            percentageDiscountField.setAttribute(req,req);
            flatDiscountField.removeAttribute(req);
        }
    }


    function validatePercentageDiscount() {
        const percentageDiscount = parseFloat(percentageDiscountField.value);
        percentageDiscountError.textContent = '';
        if (isNaN(percentageDiscount) || percentageDiscount <= 0 || percentageDiscount > 100) {
            percentageDiscountError.textContent = 'Percentage must be between 0 and 100.';
            return false;
        }
        return true;
    }

    function validateFixed() {
        const percentageDiscount = parseFloat(flatDiscountField.value);
        flat_discount_error.textContent = '';
        if (isNaN(percentageDiscount) || percentageDiscount <= 0) {
            flat_discount_error.textContent = 'Fixed must be greater than 0';
            return false;
        }
        return true;
    }

    function validateForm() {
        let type = discountType.value;

        let valid = true;
        if (type === 'percentage') {
            isValid = validatePercentageDiscount();
        }else{
            isValid = validateFixed();
        }
        if (isValid && extra_percentage) {
            extra_percentage.value = (type === 'percentage' ? percentageDiscountField.value : flatDiscountField.value);
        }

        return isValid;
    }
    document.addEventListener('DOMContentLoaded', applyDiscount);


    percentageDiscountField.addEventListener('blur', validatePercentageDiscount);
    flatDiscountField.addEventListener('blur', validateFixed);
</script>
