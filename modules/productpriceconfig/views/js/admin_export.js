$(document).ready(function() {
    
    // Generic function to handle parent-child checkbox logic
    function bindParentChildLogic(parentClass, childClass, parentIdAttr, childParentIdAttr) {
        
        // 1. Handle Parent Click
        $(document).on('change', parentClass, function() {
            var parentId = $(this).attr(parentIdAttr);
            var isChecked = $(this).prop('checked');
            
            // Find all children
            $(childClass + '[' + childParentIdAttr + '="' + parentId + '"]').each(function() {
                if ($(this).prop('checked') !== isChecked) {
                    $(this).prop('checked', isChecked).trigger('change');
                }
            });
            // Clear indeterminate state when parent is directly clicked
            $(this).prop('indeterminate', false).removeClass('indeterminate');
        });

        // 2. Handle Child Click
        $(document).on('change', childClass, function() {
            var parentId = $(this).attr(childParentIdAttr);
            var parentCheckbox = $(parentClass + '[' + parentIdAttr + '="' + parentId + '"]');
            
            if (parentCheckbox.length === 0) return;

            var totalChildren = $(childClass + '[' + childParentIdAttr + '="' + parentId + '"]').length;
            var checkedChildren = $(childClass + '[' + childParentIdAttr + '="' + parentId + '"]:checked').length;
            
            var newChecked = false;
            var newIndeterminate = false;

            if (checkedChildren === 0) {
                newChecked = false;
                newIndeterminate = false;
            } else if (checkedChildren === totalChildren) {
                newChecked = true;
                newIndeterminate = false;
            } else {
                newChecked = false;
                newIndeterminate = true;
            }

            // Update parent state without triggering change to avoid infinite recursion
            parentCheckbox.prop('checked', newChecked);
            parentCheckbox.prop('indeterminate', newIndeterminate);
            
            // PrestaShop MD checkboxes need the class for visual representation
            if (newIndeterminate) {
                parentCheckbox.addClass('indeterminate');
            } else {
                parentCheckbox.removeClass('indeterminate');
            }
        });
    }

    // Bind Variables
    bindParentChildLogic('.variable-parent-checkbox', '.option-child-checkbox', 'data-code', 'data-parent');

    // Bind Alerts
    bindParentChildLogic('.alert-parent-checkbox', '.alert-child-checkbox', 'data-product', 'data-parent');

    // Bind Products
    bindParentChildLogic('.product-parent-checkbox', '.product-child-checkbox', 'data-product', 'data-parent');

    // Bind Global Setting Type Toggles
    bindParentChildLogic('.global-type-checkbox', '.product-child-checkbox', 'data-type', 'value');

    // Handle "Select All Section"
    $('.select-all-section').on('change', function() {
        var targetId = $(this).data('target');
        var isChecked = $(this).prop('checked');
        
        $('#' + targetId).find('input[type="checkbox"]').each(function() {
            if ($(this).prop('checked') !== isChecked) {
                $(this).prop('checked', isChecked).trigger('change'); 
            }
            $(this).prop('indeterminate', false).removeClass('indeterminate');
        });
    });

    // Initial state check (if we had pre-filled values, which we don't yet, but good practice)
    // We can loop through all parents and trigger a check
});
