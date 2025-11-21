<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<head>
</head>

<body>
        <div class="conditions-modal-body">
            <div class="helper-text">
                <strong>How it works:</strong>
                <ul>
                    <li>Conditions connected by AND are grouped together (green border)</li>
                    <li>OR creates a new separate group</li>
                    <li>The logic follows: (condition1 AND condition2) OR (condition3) OR (condition4 AND condition5)</li>
                </ul>
            </div>
            <!-- Add Router Label field -->
            <div class="router-label-section">
                <label for="routerLabel">Router Label</label>
                <input type="text" id="routerLabel" placeholder="Enter Router Label" class="router-label-input">
            </div>
            <input type="hidden" name="condition_id" id="condition_id" value="">
            <input type="hidden" name="is_enabled" id="is_enabled" value="">
            
            <div class="logic-title toggle-header" data-target="#if-section">
                <span>IF</span>
                <i class="toggle-indicator material-icons" style="font-size:35px;">arrow_drop_down</i>
            </div>
            <div id="if-section" class="toggle-content">
                <div id="conditionBuilder">
                    <div id="conditions"></div>
                    <button type="button" class="btn add-btn" onclick="ProMailSMTPRouter.addCondition()">Add Condition</button>
                </div>
            </div>

            <div class="logic-title then toggle-header" data-target="#then-section">
                <span>THEN</span>
                <i class="toggle-indicator material-icons" style="font-size:35px;">arrow_drop_down</i>
            </div>
            <div id="then-section" class="toggle-content">
                <div class="section-container">
                    <div class="section-column">
                        <div class="toggle-container">
                            <label class="toggle-switch">
                                <input type="checkbox" id="connectionToggle">
                                <span class="toggle-slider"></span>
                            </label>
                            <span style="margin-left: 10px;">Send Email Using:</span>
                        </div>
                        <div class="section-content" id="connectionContent">
                            <select id="connectionSelect" class="field-select">
                                <?php foreach ($connections_list as $connection): ?>
                                    <option value="<?php echo esc_attr($connection->connection_id); ?>">
                                        <?php echo esc_html($connection->connection_label); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="section-column">
                        <div class="toggle-container">
                            <label class="toggle-switch">
                                <input type="checkbox" id="emailInfoToggle">
                                <span class="toggle-slider"></span>
                            </label>
                            <span style="margin-left: 10px;">Force Sender:</span>
                        </div>
                        <div class="section-content" id="emailInfoContent">
                            <input type="email" placeholder="From Email" class="value-input" style="width: 100%; margin-bottom: 10px;">
                            <input type="text" placeholder="From Name" class="value-input" style="width: 100%;">
                        </div>
                    </div>
                </div>
            </div>
        </div>
</body>