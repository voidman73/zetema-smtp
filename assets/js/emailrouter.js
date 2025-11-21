jQuery(document).ready(function($) {
    let currentEditId = null;
    let conditionCount = 0;
    const fields =  [ 'To', 'Subject', 'Source App', 'Message', 'From Email', 'From Name', 'CC', 'BCC', 'Reply To'];
    const operators = ['Is', 'Is not', 'Contains', 'Does not Contain', 'Start with', 'End with', 'Regex Match', 'Regex Not Match', 'Is Empty', 'Is Not Empty'];
    const sourceAppOperators = ['Is', 'Is not'];

    $('.add-router-condition , #add-router-condition-button').on('click', function(e) {
        e.preventDefault();
        $('#router-modal').show();
    });

    $(document).on('click', '.modal-close', function(e) {
        e.preventDefault();
        $(this).closest('.modal').hide();
    });

    function closeModal(saved) {
        if(!saved){
            if (confirm('Are you sure you want to close? Any unsaved changes will be lost.')) {
                $('#router-modal').hide();
                resetForm();
            }
        } else{
            $('#router-modal').hide();
            resetForm();
        }
    }

    function resetForm() {
        currentEditId = null;
        $('#routerLabel').val('');
        $('#conditions').empty();
        $('#connectionToggle').prop('checked', false);
        $('#emailInfoToggle').prop('checked', false);
        $('#connectionSelect').val('');
        $('#emailInfoContent input').val('');
        $('#condition_id').val('');
        $('#is_enabled').val('');
        addCondition();
    }

    function collectFormData() {
        const formData = {
            label: $('#routerLabel').val(),
            conditions: getConditionsData(),
            connection: {
                enabled: $('#connectionToggle').is(':checked'),
                selected: $('#connectionSelect').val()
            },
            email: {
                enabled: $('#emailInfoToggle').is(':checked'),
                email: $('#emailInfoContent input[type="email"]').val(),
                name: $('#emailInfoContent input[type="text"]').val()
            },
            id: $('#condition_id').val(),
            is_enabled: $('#is_enabled').val(),
        };
        if (currentEditId) {
            formData.id = currentEditId;
        }
        return formData;
    }

    function getConditionsData() {
        const conditions = [];
        $('.condition-row').each(function() {
            const conditionData = {
                field: $(this).find('.field-select').val(),
                operator: $(this).find('.operator-select').last().val(),
                value: $(this).find('.value-input').val()
            };
            const logicalOperator = $(this).closest('.condition-container').find('.operator-select').first().val();
            if (logicalOperator) {
                conditionData.logical_operator = logicalOperator;
            }
            conditions.push(conditionData);
        });
        return conditions;
    }

    function validateForm(formData) {
        if (!formData.label.trim()) {
            alert('Please enter a Router Label');
            return false;
        }
        if (formData.conditions.length === 0) {
            alert('Please add at least one condition');
            return false;
        }
        if (formData.connection.enabled && !formData.connection.selected) {
            alert('Please select a connection or disable the section');
            return false;
        }
        if (formData.email.enabled && (!formData.email.email || !formData.email.name)) {
            alert('Please complete email details or disable the section');
            return false;
        }
        return true;
    }

    function saveRouter() {
        const formData = collectFormData();
        if(!formData.id){
            formData.is_enabled = false; 
        }

        if (!validateForm(formData)) {
            return;
        }

        $.ajax({
            url: ProMailSMTPEmailRouter.ajaxUrl,
            type: 'POST',
            data: {
                action: 'pro_mail_smtp_save_email_router',
                data: formData,
                nonce: ProMailSMTPEmailRouter.nonce
            },
            success: function(response) {
                if (response.success) {
                    closeModal(true);
                    location.reload();
                } else {
                    alert('Error saving: ' + response.data.message);
                }
            },
            error: function() {
                alert('Server error occurred while saving');
            }
        });
    }

    function getOperatorsForField(fieldName) {
        return fieldName.toLowerCase() === 'source_app' ? sourceAppOperators : operators;
    }

    function createValueInput(fieldName) {
        if (fieldName.toLowerCase() === 'source_app') {
            return createSourceAppSelect();
        } else {
            return createTextInput();
        }
    }

    function createSourceAppSelect() {
        const select = document.createElement('select');
        select.className = 'value-input';

        const placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = 'Select Plugin';
        select.appendChild(placeholder);

        if (ProMailSMTPEmailRouter.pluginsList) {
            try {
                const plugins = JSON.parse(ProMailSMTPEmailRouter.pluginsList);
                plugins.forEach(plugin => {
                    const opt = document.createElement('option');
                    opt.value = plugin.path;
                    opt.textContent = plugin.name; 
                    select.appendChild(opt);
                });
            } catch (error) {
                console.error('Error parsing plugins list:', error);
            }
        }
        return select;
    }

    function createTextInput() {
        const input = document.createElement('input');
        input.type = 'text';
        input.className = 'value-input';
        return input;
    }

    function createSelect(options, className, onchange) {
        const select = document.createElement('select');
        select.className = className;
        options.forEach(option => {
            const opt = document.createElement('option');
            opt.value = option.toLowerCase().replace(/ /g, '_');
            opt.textContent = option;
            select.appendChild(opt);
        });
        if (onchange) {
            select.onchange = onchange;
        }
        if (className === 'operator-select') {
            select.addEventListener('change', handleOperatorChange);
        }
        return select;
    }

    function handleOperatorChange() {
        const valueWrapper = this.parentNode.nextElementSibling;
        if (valueWrapper && valueWrapper.classList.contains('value-wrapper')) {
            const valueInput = valueWrapper.querySelector('.value-input');
            if (valueInput) {
                const isEmptyOperator = this.value === 'is_empty' || this.value === 'is_not_empty';
                valueInput.disabled = isEmptyOperator;
                if (isEmptyOperator) {
                    valueInput.value = '';
                }
            }
        }
    }

    function createConditionContainer(index) {
        const conditionContainer = document.createElement('div');
        conditionContainer.className = 'condition-container';

        if (index > 0) {
            const operatorSelect = createLogicalOperatorSelect();
            conditionContainer.appendChild(operatorSelect);
        }
        return conditionContainer;
    }

    function createConditionRow(conditionCount, containerLength) {
        const conditionRow = document.createElement('div');
        conditionRow.className = 'condition-row';
        conditionRow.id = `condition-${conditionCount}`;

        const numberSpan = createConditionNumberSpan(containerLength + 1);
        const fieldSelect = createFieldSelect();
        const operatorWrapper = createOperatorWrapper();
        const valueWrapper = createValueWrapper('');
        
        const conditionContainer = createConditionContainer(containerLength);
        const removeButton = createRemoveConditionButton(conditionContainer);

        conditionRow.appendChild(numberSpan);
        conditionRow.appendChild(fieldSelect);
        conditionRow.appendChild(operatorWrapper);
        conditionRow.appendChild(valueWrapper);
        conditionRow.appendChild(removeButton);

        return conditionRow;
    }

    function createLogicalOperatorSelect() {
        const operatorSelect = createSelect(['AND', 'OR'], 'operator-select');
        operatorSelect.onchange = updateGrouping;
        return operatorSelect;
    }

    function createConditionNumberSpan(number) {
        const numberSpan = document.createElement('span');
        numberSpan.className = 'condition-number';
        numberSpan.textContent = number;
        return numberSpan;
    }

    function createFieldSelect() {
        const fieldSelect = createSelect(fields, 'field-select');
        fieldSelect.addEventListener('change', handleFieldChange);
        return fieldSelect;
    }

    function createOperatorWrapper() {
        const operatorWrapper = document.createElement('div');
        operatorWrapper.className = 'operator-wrapper';
        operatorWrapper.appendChild(createSelect(operators, 'operator-select'));
        return operatorWrapper;
    }

    function createValueWrapper(initialValue) {
        const valueWrapper = document.createElement('div');
        valueWrapper.className = 'value-wrapper';
        valueWrapper.appendChild(createValueInput(initialValue));
        return valueWrapper;
    }

    function createRemoveConditionButton(conditionContainer) {
        const removeButton = document.createElement('button');
        removeButton.className = 'delete-condition-btn';
        removeButton.innerHTML = '<i class="material-icons delete-icon">delete</i>';
        removeButton.type = 'button';
        return removeButton;
    }

    function handleFieldChange() {

        let operatorWrapper = this.nextElementSibling;
        if (!operatorWrapper || !operatorWrapper.classList.contains('operator-wrapper')) {
            operatorWrapper = document.createElement('div');
            operatorWrapper.className = 'operator-wrapper';
            this.parentNode.insertBefore(operatorWrapper, this.nextSibling);
        }

        const operators = getOperatorsForField(this.value);
        const operatorSelect = createSelect(operators, 'operator-select');
        operatorWrapper.innerHTML = '';
        operatorWrapper.appendChild(operatorSelect);

        let valueWrapper = operatorWrapper.nextElementSibling;
        const newValueInput = createValueInput(this.value);

        if (valueWrapper && valueWrapper.classList.contains('value-wrapper')) {
            valueWrapper.innerHTML = '';
            valueWrapper.appendChild(newValueInput);
        } else {
            valueWrapper = document.createElement('div');
            valueWrapper.className = 'value-wrapper';
            valueWrapper.appendChild(newValueInput);
            operatorWrapper.parentNode.insertBefore(valueWrapper, operatorWrapper.nextSibling);
        }

        const isEmptyOperator = operatorSelect.value === 'is_empty' || operatorSelect.value === 'is_not_empty';
        newValueInput.disabled = isEmptyOperator;
    }

    function addCondition() {
        const container = document.getElementById('conditions');
        if (!container) {
            console.error('Conditions container not found');
            return;
        }
        
        const conditionContainer = createConditionContainer(container.children.length);
        const conditionRow = createConditionRow(conditionCount, container.children.length);

        conditionContainer.appendChild(conditionRow);
        container.appendChild(conditionContainer);
        conditionCount++;

        updateGrouping();
    }

    function updateGrouping() {
        const containers = Array.from(document.querySelectorAll('.condition-container'));
        const conditionsDiv = document.getElementById('conditions');

        while (conditionsDiv.firstChild) {
            conditionsDiv.firstChild.remove();
        }

        let currentOrGroup = null;
        let currentAndGroup = null;

        containers.forEach((container, index) => {
            const operator = container.querySelector('.operator-select')?.value?.toLowerCase();

            if (index === 0 || operator === 'or') {
                currentAndGroup = null;
                currentOrGroup = document.createElement('div');
                currentOrGroup.className = 'or-group';
                conditionsDiv.appendChild(currentOrGroup);
            }

            if (operator === 'and' && !currentAndGroup) {
                currentAndGroup = document.createElement('div');
                currentAndGroup.className = 'and-group';
                currentOrGroup.appendChild(currentAndGroup);
            }

            const targetContainer = currentAndGroup || currentOrGroup;
            targetContainer.appendChild(container);
        });

        updateConditionNumbers();
    }

    function updateConditionNumbers() {
        const numbers = document.querySelectorAll('.condition-number');
        numbers.forEach((num, index) => {
            num.textContent = index + 1;
        });
    }

    function removeConditionContainer(container) {
        if (container && container.parentNode) {
            container.remove();
            updateGrouping();
            
            const remainingConditions = document.querySelectorAll('.condition-container');
            if (remainingConditions.length === 0) {
                addCondition();
            }
        }
    }

    function setupToggles() {
        const connectionToggle = $('#connectionToggle');
        const emailInfoToggle = $('#emailInfoToggle');
        const connectionContent = $('#connectionContent');
        const emailInfoContent = $('#emailInfoContent');

        if (connectionToggle.length && connectionContent.length) {
            connectionToggle.on('change', function(){
                connectionContent.toggleClass('disabled', !$(this).is(':checked'));
            });
        }

        if (emailInfoToggle.length && emailInfoContent.length) {
            emailInfoToggle.on('change', function(){
                emailInfoContent.toggleClass('disabled', !$(this).is(':checked'));
            });
        }
    }

    $('.toggle-header').on('click', function() {
        const target = $( $(this).data('target') );
        target.slideToggle();
        const indicator = $(this).find('.toggle-indicator');
        indicator.text(indicator.text() === 'arrow_drop_down' ? 'arrow_right' : 'arrow_drop_down');
    });

    function initializeForm() {
        try {
            addCondition();
            setupToggles();
            
            $('.modal-close').on('click', function(e) {
                e.preventDefault();
                $(this).closest('.modal').hide();
            });
        } catch (error) {
            console.error('Error initializing form:', error);
        }
    }

    initializeForm();

    $(document).keydown(function(e) {
        if (e.key === "Escape" && $('#router-modal').is(':visible')) {
            closeModal(false);
        }
    });

    $(document).on('change', '.toggle-is-enabled', function() {
        const conditionId = $(this).data('id');
        const newStatus = $(this).is(':checked') ? 1 : 0;
        $.ajax({
            url: ProMailSMTPEmailRouter.ajaxUrl,
            type: 'POST',
            data: {
                action: 'pro_mail_smtp_update_email_router_status',
                condition_id: conditionId,
                status: newStatus,
                nonce: ProMailSMTPEmailRouter.nonce
            },
            success: function(response) {
                if(response.success) {
                    console.log('Status updated successfully');
                } else {
                    alert('Failed to update status: ' + response.data.message);
                }
            },
            error: function() {
                alert('Server error occurred while updating status');
            }
        });
    });

    $(document).on('click', '.edit-condition', function() {
        const conditionId = $(this).data('id');
        $.ajax({
            url: ProMailSMTPEmailRouter.ajaxUrl,
            type: 'POST',
            data: {
                action: 'pro_mail_smtp_get_email_router_condition',
                condition_id: conditionId,
                nonce: ProMailSMTPEmailRouter.nonce
            },
            success: function(response) {
                if (response.success) {
                    populateEditForm(response.data);
                    $('#router-modal').show();
                } else {
                    alert('Error retrieving condition: ' + response.data.message);
                }
            },
            error: function() {
                alert('Server error occurred while retrieving condition data');
            }
        });
    });

    $(document).on('click', '.delete-condition', function() {
        const conditionId = $(this).data('id');
        if (confirm('Are you sure you want to delete this condition?')) {
            $.ajax({
                url: ProMailSMTPEmailRouter.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'pro_mail_smtp_delete_email_router_condition',
                    condition_id: conditionId,
                    nonce: ProMailSMTPEmailRouter.nonce
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Failed to delete condition: ' + response.data.message);
                    }
                },
                error: function() {
                    alert('Server error occurred while deleting condition');
                }
            });
        }
    });

    function populateEditForm(condition) {
        currentEditId = condition.id;
        resetForm();
        setFormHeaderLabel(condition.condition_label);
        clearConditionsContainer();
        conditionCount = 0;
        try {
            let conditionData = parseConditionData(condition.condition_data);

            if (Array.isArray(conditionData)) {
                populateConditions(conditionData);
                updateGrouping();
            }

            setConnectionSettings(condition);
            setEmailInfoSettings(condition);
            setConditionId(condition.id);
            setConditionStatus(condition.is_enabled);

        } catch (error) {
            handleFormPopulationError(error);
        }
    }

    function setFormHeaderLabel(label) {
        $('#routerLabel').val(label);
    }

    function clearConditionsContainer() {
        $('#conditions').empty();
    }

    function parseConditionData(conditionData) {
        if (typeof conditionData === 'string') {
            return JSON.parse(conditionData);
        }
        return conditionData;
    }

    function populateConditions(conditionData) {
        conditionData.forEach((item, index) => {
            addCondition();
            const $lastRow = $('#conditions').find('.condition-row').last();
            const $container = $lastRow.closest('.condition-container');

            if (index > 0) {
                setLogicalOperator($container, item.logical_operator);
            }
            setConditionRowValues($lastRow, item);
        });
    }

    function setLogicalOperator($container, logicalOperator) {
        const $operatorSelect = $container.find('.operator-select').first();
        if ($operatorSelect.length) {
            const logicalOp = logicalOperator || 'and';
            $operatorSelect.val(logicalOp.toLowerCase());
        }
    }

    function setConditionRowValues($lastRow, item) {
        const $fieldSelect = $lastRow.find('.field-select');
        $fieldSelect.val(item.field);

        handleFieldChange.call($fieldSelect[0]);

        const $operatorSelect = $lastRow.find('.operator-select').last();
        $operatorSelect.val(item.operator);

        const isEmptyOperator = item.operator === 'is_empty' || item.operator === 'is_not_empty';
        const $valueInput = $lastRow.find('.value-input');
        $valueInput.prop('disabled', isEmptyOperator);
        $valueInput.val(isEmptyOperator ? '' : item.value);
    }

    function setConnectionSettings(condition) {
        if (condition.connection_id) {
            $('#connectionToggle').prop('checked', true).trigger('change');
            $('#connectionSelect').val(condition.connection_id);
        } else {
            $('#connectionToggle').prop('checked', false).trigger('change');
        }
    }

    function setEmailInfoSettings(condition) {
        if (condition.forced_senderemail) {
            $('#emailInfoToggle').prop('checked', true).trigger('change');
            $('#emailInfoContent input[type="email"]').val(condition.forced_senderemail);
            $('#emailInfoContent input[type="text"]').val(condition.forced_sendername);
        } else {
            $('#emailInfoToggle').prop('checked', false).trigger('change');
        }
    }

    function setConditionId(id){
        if(id){
            $('#condition_id').val(id);
        }
    }
    function setConditionStatus(value){
        if(value){
            $('#is_enabled').val(value);
        }
    }

    function handleFormPopulationError(error) {
        console.error('Error populating form:', error);
        alert('Error loading condition data');
    }

    window.ProMailSMTPRouter = {
        closeModal,
        addCondition,
        saveRouter,
        resetForm
    };

    $(document).on('click', '.delete-condition-btn', function(e) {
        e.preventDefault();
        const container = $(this).closest('.condition-container');
        if (container.length) {
            removeConditionContainer(container[0]);
        }
    });

});