<?php
namespace TurboSMTP\ProMailSMTP\Email;
if ( ! defined( 'ABSPATH' ) ) exit;

use TurboSMTP\ProMailSMTP\DB\ConditionRepository;

class EmailRoutingService {
   private $conditionRepo;

    public function __construct() {
       $this->conditionRepo = new ConditionRepository();
    }

    /**
     * Checks if there are any matching routing conditions for the given email data
     *
     * @param array $email_data The email data to check against conditions
     * @return array Array of matching conditions
     */
    public function getRoutingConditionIfExists($email_data) {
        $result = [];
        $routing_conditions = $this->getRoutingConditions();
        foreach ($routing_conditions as $condition) {
            $conditionObj = $condition;
            // Only process enabled conditions
            if ($conditionObj->is_enabled && $this->checkCondition($conditionObj, $email_data)) {
                $result[] = (object)[
                    'connection_id' => $conditionObj->connection_id,
                    'overwrite_sender' => $conditionObj->overwrite_sender ?? false,
                    'overwrite_connection' => $conditionObj->overwrite_connection ?? false,
                    'forced_senderemail' => $conditionObj->forced_senderemail ?? '',
                    'forced_sendername' => $conditionObj->forced_sendername ?? ''
                ];
            }
        }
        return $result;
    }

    /**
     * Retrieves all routing conditions from the repository
     *
     * @return array Array of routing conditions
     */
    private function getRoutingConditions() {
        return $this->conditionRepo->load_all_conditions();
    }

    /**
     * Evaluates if an email matches the given condition
     *
     * @param object $condition The condition to check
     * @param array $email_data The email data to check against
     * @return boolean True if the condition is met, false otherwise
     */
    private function checkCondition($condition, $email_data) {
        $condition_logical_statement_vector = [];
        $condition_data = json_decode($condition->condition_data);
        foreach ($condition_data as $term) {
            $termObj = is_string($term) ? json_decode($term) : $term;
            if (is_array($termObj)) {
                $termObj = is_string($termObj[0]) ? json_decode($termObj[0]) : $termObj[0];
            }
            
            if (is_object($termObj)) {
                array_push($condition_logical_statement_vector, $termObj->logical_operator);
                array_push($condition_logical_statement_vector, $this->evaluateTerm($termObj, $email_data));
            }
        }
        return $this->evaluateLogicalStatement($condition_logical_statement_vector);
    }

    /**
     * Evaluates a single term in a condition against email data
     *
     * @param object $condition Single condition term containing field, value, and operator
     * @param array $email_data The email data to evaluate against
     * @return boolean Result of the term evaluation
     */
    private function evaluateTerm($condition, $email_data) {
        $field =$email_data[$condition->field];
        $value = $condition->value;
        $operator = $condition->operator;
        $result = false;
        switch($operator){
            case 'is':
                $result = ($field == $value);
                break;
            case 'contains':
                $result = (strpos($field, $value) !== false);
                break;
            case 'start_with':
                $result = (strpos($field, $value) === 0);
                break;
            case 'end_with':
                $result = (substr($field, -strlen($value)) === $value);
                break;
            case 'is_not':
                $result = ($field != $value);
                break;
            case 'does_not_contain':
                $result = (strpos($field, $value) === false);
                break;
            case 'regex_match':
                $result = (preg_match($value, $field));
                break;
            case 'regex_not_match':
                $result = (!preg_match($value, $field));
                break;
            case 'is_empty':
                $result = empty($field);
                break;
            case 'is_not_empty':
                $result = !empty($field);
                break;
            default:
                $result = false;
        }
        return $result;
    }

    /**
     * Evaluates a logical statement composed of AND/OR operations
     *
     * @param array $condition_logical_statement_vector Array of logical operators and boolean values
     * @return boolean Final result of the logical statement evaluation
     */
    private function evaluateLogicalStatement($condition_logical_statement_vector) {
        if(count($condition_logical_statement_vector) == 0) {
            return false;
        }
        $and_group_vector = [];
        $or_group_vector = [];
        foreach ($condition_logical_statement_vector as $term){
            if(is_bool($term)){
                array_push($and_group_vector, $term);
            } elseif ($term == 'or') {
               $value_of_last_and_group = array_product($and_group_vector);
                array_push($or_group_vector, $value_of_last_and_group);
                $and_group_vector = [];
            }
        }
        if(count($and_group_vector) > 0){
            $value_of_last_and_group = array_product($and_group_vector);
            array_push($or_group_vector, $value_of_last_and_group);
        }
        $result =array_sum($or_group_vector);
        return $result;
    }
}