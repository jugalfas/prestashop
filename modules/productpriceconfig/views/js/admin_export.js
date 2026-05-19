$(document).ready(function() {
    
    // Generic function to handle parent-child checkbox logic
    function bindParentChildLogic(parentClass, childClass, parentIdAttr, childParentIdAttr) {
        
        // 1. Handle Parent Click
        $(document).on('change', parentClass, function() {
            var parentId = $(this).attr(parentIdAttr);
            var isChecked = $(this).prop('checked');
            
            // Find all children
            $(childClass + '[' + childParentIdAttr + '="' + parentId + '"]').each(function() {
                $(this).prop('checked', isChecked);
            });
        });

        // 2. Handle Child Click
        $(document).on('change', childClass, function() {
            var parentId = $(this).attr(childParentIdAttr);
            var parentCheckbox = $(parentClass + '[' + parentIdAttr + '="' + parentId + '"]');
            
            var totalChildren = $(childClass + '[' + childParentIdAttr + '="' + parentId + '"]').length;
            var checkedChildren = $(childClass + '[' + childParentIdAttr + '="' + parentId + '"]:checked').length;
            
            if (checkedChildren === 0) {
                parentCheckbox.prop('checked', false);
                parentCheckbox.prop('indeterminate', false);
            } else if (checkedChildren === totalChildren) {
                parentCheckbox.prop('checked', true);
                parentCheckbox.prop('indeterminate', false);
            } else {
                parentCheckbox.prop('checked', false);
                parentCheckbox.prop('indeterminate', true);
            }
        });
    }

    // Bind Variables
    bindParentChildLogic('.variable-parent-checkbox', '.option-child-checkbox', 'data-code', 'data-parent');

    // Bind Alerts
    bindParentChildLogic('.alert-parent-checkbox', '.alert-child-checkbox', 'data-product', 'data-parent');

    // Bind Products
    bindParentChildLogic('.product-parent-checkbox', '.product-child-checkbox', 'data-product', 'data-parent');

    // Handle "Select All Section"
    $('.select-all-section').on('change', function() {
        var targetId = $(this).data('target');
        var isChecked = $(this).prop('checked');
        
        $('#' + targetId).find('input[type="checkbox"]').each(function() {
            $(this).prop('checked', isChecked);
            $(this).prop('indeterminate', false); // Clear indeterminate state
        });
    });

    // Initial state check (if we had pre-filled values, which we don't yet, but good practice)
    // We can loop through all parents and trigger a check
});
