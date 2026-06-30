<?php
/**
 * KDBannedCombinationValidator - Server-side banned combination validation.
 *
 * Mirrors the exact same logic as getBannedCombinationJs() but evaluates
 * on the server using customization values instead of DOM elements.
 *
 * Data structure (from ps_rule_list):
 *   rule:     JSON array of conditions [{variable, sign, option, and_or_sign}, ...]
 *   disallow: JSON array of actions [{disallow_variable, disallow_options: [...]}]
 *             or plain variable IDs for full-variable disabling
 *
 * JS logic flow (from getBannedCombinationJs):
 *   1. For each rule, evaluate: if (condition1 SIGN option1 AND/OR condition2 SIGN option2 ...)
 *   2. Conditions are chained: and_or_sign of each condition links it to the NEXT one
 *      - and_or_sign = 1 means OR (link to next condition)
 *      - and_or_sign = 2 means AND (link to next condition)
 *      - and_or_sign = 0 means this is the LAST condition (close the if)
 *   3. For type 2 (select) variables: compare selected option ID against rule['option']
 *   4. For other types: compare input value against rule['option']
 *   5. When conditions are met, disallow actions are applied:
 *      - If disallow is array: disable specific options for specific variables
 *      - If disallow is scalar: disable the entire variable
 *
 * A combination is BANNED when:
 *   - Rule conditions are met AND
 *   - The customer's current selection includes a disallowed option
 *
 * This validator can be reused by:
 *   - Reconfigure & Reorder workflow
 *   - Future server-side validation workflows
 *   - AJAX validation endpoints
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class KDBannedCombinationValidator
{
    /** @var Db */
    private $db;

    /** @var int */
    private $id_lang;

    public function __construct()
    {
        $this->db = Db::getInstance();
        $this->id_lang = (int) Context::getContext()->language->id;
    }

    // ──────────────────────────────────────────────
    //  PUBLIC API
    // ──────────────────────────────────────────────

    /**
     * Validate a set of customization values against all banned combination rules
     * for a given product.
     *
     * @param int   $id_product           Product ID
     * @param array $customization_values  Map of id_variable => selected value
     *                                     For type 2 (select): value is option ID
     *                                     For other types: value is the raw input value
     * @return array ['banned' => bool, 'reason' => string|null, 'violated_rule' => array|null]
     */
    public function validate($id_product, $customization_values)
    {
        $rules_list = KDRuleList::getAllRules((int) $id_product);

        if (!count($rules_list)) {
            return ['banned' => false, 'reason' => null, 'violated_rule' => null];
        }

        foreach ($rules_list as $rule_entry) {
            $result = $this->evaluateRule($rule_entry, $customization_values, $id_product);
            if ($result['banned']) {
                return $result;
            }
        }

        return ['banned' => false, 'reason' => null, 'violated_rule' => null];
    }

    // ──────────────────────────────────────────────
    //  RULE EVALUATION
    // ──────────────────────────────────────────────

    /**
     * Evaluate a single rule entry (conditions + disallow actions).
     *
     * Mirrors the JS logic:
     *   if (condition1 && condition2) { disallow actions }
     *
     * @param array $rule_entry           Row from ps_rule_list
     * @param array $customization_values id_variable => selected value
     * @param int   $id_product
     * @return array
     */
    private function evaluateRule($rule_entry, $customization_values, $id_product)
    {
        $rule_data = json_decode($rule_entry['rule'], true);
        if (empty($rule_data)) {
            return ['banned' => false, 'reason' => null, 'violated_rule' => null];
        }

        // Step 1: Evaluate the conditions (the "if" part)
        $conditions_met = $this->evaluateConditions($rule_data, $customization_values, $id_product);

        if (!$conditions_met) {
            return ['banned' => false, 'reason' => null, 'violated_rule' => null];
        }

        // Step 2: Conditions are met - check if disallow actions affect the current selections
        $disallow_data = json_decode($rule_entry['disallow'], true);
        if (empty($disallow_data)) {
            // Conditions met but no disallow actions = no actual ban
            return ['banned' => false, 'reason' => null, 'violated_rule' => null];
        }

        // Step 3: Check if the customer's current selection includes a disallowed option
        $ban_result = $this->checkDisallowActions($disallow_data, $customization_values, $id_product, $rule_entry);

        return $ban_result;
    }

    /**
     * Evaluate the conditions part of a rule.
     *
     * Conditions are chained with AND/OR logic:
     *   and_or_sign = 0: this is the last condition (no connector)
     *   and_or_sign = 1: OR (link to next condition)
     *   and_or_sign = 2: AND (link to next condition)
     *
     * The JS builds: if (cond1 && cond2) or if (cond1 || cond2)
     * Where each condition is: selected_value SIGN rule_option
     *
     * Logic:
     *   - Start with first condition
     *   - For each subsequent condition, combine with previous using the PREVIOUS condition's and_or_sign
     *   - AND: both must be true
     *   - OR: either can be true
     *   - Last condition (and_or_sign == 0): just evaluate it and combine with result so far
     *
     * @param array $rule_data            Array of condition objects
     * @param array $customization_values id_variable => selected value
     * @param int   $id_product
     * @return bool
     */
    private function evaluateConditions($rule_data, $customization_values, $id_product)
    {
        $result = null;
        $prev_connector = null;

        foreach ($rule_data as $i => $condition) {
            $id_variable = (int) $condition['variable'];
            $rule_option = $condition['option'];
            $sign = (int) $condition['sign'];
            $and_or_sign = (int) $condition['and_or_sign'];

            // Get the selected value for this variable
            $selected_value = $this->getSelectedValue($id_variable, $customization_values, $id_product);

            // If we can't find a value for this variable, the condition cannot be met
            if ($selected_value === null) {
                $condition_met = false;
            } else {
                $condition_met = $this->compareValues($selected_value, $rule_option, $sign, $id_variable);
            }

            // Combine with previous result using the PREVIOUS connector
            if ($result === null) {
                $result = $condition_met;
            } else {
                // Use the previous condition's and_or_sign to combine
                if ($prev_connector == 2) {
                    // AND: both must be true
                    $result = $result && $condition_met;
                } elseif ($prev_connector == 1) {
                    // OR: either can be true
                    $result = $result || $condition_met;
                } else {
                    // No connector (prev was last), just take current
                    $result = $condition_met;
                }
            }

            $prev_connector = $and_or_sign;
        }

        return $result === true;
    }

    /**
     * Get the selected value for a variable from customization values.
     *
     * For type 2 (select) variables: the customization_values map contains
     * the option ID, which is what the JS reads from data-option_id.
     *
     * For other types: the map contains the raw input value.
     *
     * The key challenge: customization_values is keyed by id_variable,
     * but we also need to handle the case where the value is stored
     * under id_product_variable. We resolve by looking up the
     * product_variable for this id_variable on this product.
     *
     * @param int   $id_variable
     * @param array $customization_values Can be keyed by id_variable OR id_product_variable
     * @param int   $id_product
     * @return mixed|null
     */
    private function getSelectedValue($id_variable, $customization_values, $id_product)
    {
        // First try: direct lookup by id_variable
        if (isset($customization_values[$id_variable])) {
            return $customization_values[$id_variable];
        }

        // Second try: lookup by id_product_variable
        // The rule references id_variable, but customization values may be keyed by
        // id_product_variable. Find the product_variable for this variable.
        $pv_row = $this->db->getRow('
            SELECT id_product_variable
            FROM ' . _DB_PREFIX_ . 'product_variable
            WHERE id_variable = ' . (int) $id_variable . '
            AND id_product = ' . (int) $id_product
        );

        if ($pv_row && isset($customization_values[(int) $pv_row['id_product_variable']])) {
            return $customization_values[(int) $pv_row['id_product_variable']];
        }

        // Third try: search by 'variable_X' key format (params format)
        if ($pv_row && isset($customization_values['variable_' . $pv_row['id_product_variable']])) {
            return $customization_values['variable_' . $pv_row['id_product_variable']];
        }

        return null;
    }

    /**
     * Compare a selected value against a rule option using the sign operator.
     *
     * Mirrors the JS comparison:
     *   For type 2 (select): compares option ID against rule['option'] (both numeric)
     *   For other types: compares raw value against rule['option']
     *
     * sign: 1 = ==, 2 = >, 3 = <
     *
     * @param mixed $selected_value
     * @param mixed $rule_option
     * @param int   $sign
     * @param int   $id_variable  Used to determine variable type
     * @return bool
     */
    private function compareValues($selected_value, $rule_option, $sign, $id_variable)
    {
        // Determine if this is a select-type variable
        $varObj = new KDVariable($id_variable);

        if ($varObj->type == 2) {
            // Select type: both values are option IDs (numeric)
            $selected = (float) $selected_value;
            $option = (float) $rule_option;
        } else {
            // Other types: numeric comparison
            $selected = (float) $selected_value;
            $option = (float) $rule_option;
        }

        switch ($sign) {
            case 1: return $selected == $option;
            case 2: return $selected > $option;
            case 3: return $selected < $option;
            default: return false;
        }
    }

    /**
     * Check if disallow actions affect the customer's current selections.
     *
     * Disallow data structure:
     *   - Array of objects: [{disallow_variable: id, disallow_options: [id1, id2, ...]}, ...]
     *   - Or plain variable IDs (scalar values) for full-variable disabling
     *
     * A combination is banned when:
     *   - The customer has selected an option that is in the disallow_options list
     *     for a variable that is in the disallow_variable list
     *   - OR the customer has selected a value for a fully-disabled variable
     *
     * @param array $disallow_data
     * @param array $customization_values
     * @param int   $id_product
     * @param array $rule_entry
     * @return array
     */
    private function checkDisallowActions($disallow_data, $customization_values, $id_product, $rule_entry)
    {
        $reason_parts = [];
        $is_banned = false;

        foreach ($disallow_data as $disallow) {
            if (is_array($disallow)) {
                // Structured disallow: specific options for specific variables
                $disallow_variables = $disallow['disallow_variable'];
                $disallow_options = $disallow['disallow_options'];

                // Normalize to arrays
                if (!is_array($disallow_variables)) {
                    $disallow_variables = [$disallow_variables];
                }
                if (!is_array($disallow_options)) {
                    $disallow_options = [$disallow_options];
                }

                foreach ($disallow_variables as $id_variable) {
                    $selected = $this->getSelectedValue((int) $id_variable, $customization_values, $id_product);

                    if ($selected === null) {
                        continue;
                    }

                    $varObj = new KDVariable((int) $id_variable, $this->id_lang);
                    $var_label = $varObj->label ?: $varObj->name;

                    if ($varObj->type == 2) {
                        // Select type: check if selected option ID is in disallowed options
                        if (in_array((int) $selected, array_map('intval', $disallow_options))) {
                            $is_banned = true;
                            $optionObj = new KDOption((int) $selected, $this->id_lang);
                            $reason_parts[] = $var_label . ': ' . ($optionObj->label ?: 'option') . ' is not allowed with this combination';
                        }
                    } else {
                        // Non-select type: if the variable is fully disallowed, any selection is banned
                        // Check if any of the disallow_options match the selected value
                        foreach ($disallow_options as $disallowed_value) {
                            if ((float) $selected == (float) $disallowed_value) {
                                $is_banned = true;
                                $reason_parts[] = $var_label . ': value ' . $selected . ' is not allowed with this combination';
                                break;
                            }
                        }
                    }
                }
            } else {
                // Scalar disallow: entire variable is disabled
                $id_variable = (int) $disallow;
                $selected = $this->getSelectedValue($id_variable, $customization_values, $id_product);

                if ($selected !== null && $selected !== '' && $selected !== 0) {
                    $varObj = new KDVariable($id_variable, $this->id_lang);
                    $var_label = $varObj->label ?: $varObj->name;
                    $is_banned = true;
                    $reason_parts[] = $var_label . ' is not available with this combination';
                }
            }
        }

        if ($is_banned) {
            return [
                'banned' => true,
                'reason' => implode('; ', $reason_parts) ?: 'Banned combination detected',
                'violated_rule' => [
                    'id_rule_list' => (int) $rule_entry['id_rule_list'],
                    'name' => $rule_entry['name'],
                ],
            ];
        }

        return ['banned' => false, 'reason' => null, 'violated_rule' => null];
    }
}
