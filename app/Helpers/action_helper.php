<?php

/**
 * Function to split the function name into module and action
 * @param string $funcName
 * @return array
 */
if (!function_exists('formActionData')) {
    function formActionData($funcName, $refId, $pId, $actionData): array
    {
        $functionArray = getModuleAndAction($funcName);
        $module_id = $functionArray[0];
        $action_id = $functionArray[1];
        $action = [
            'r_action_type_id' => $action_id,
            'r_module_id' => $module_id,
            'reference_id' => $refId,
            'product_id' => $pId,
            'action_data' => $actionData
        ];
        return $action;
    }
}



function getModuleAndAction($funcName)
{
    $function = $funcName;
    $pattern = "/(?=[A-Z])/";
    $components = preg_split($pattern, $function, 2);

    if (strtolower($components[1]) == 'document') {
        //all document history should store under the backlogitem
        $arr[0] = BACKLOG_MODULE;
    } else {
        $arr[0] = getModuleId($components[1]);
    }
    $arr[1] = (getActionType($components[0]))[0]['action_type_id'];
    return $arr;
}

/**
 * Get the action type ID from the database
 * @param string $name
 * @return array|int
 */
function getActionType($name)
{
    $db = \Config\Database::connect(); // Connect to the database
    $sql = "SELECT action_type_id FROM orvexa_action_type WHERE action_type_name = :name:";
    $query = $db->query($sql, ['name' => $name]);

    if ($query) {
        return $query->getResultArray();
    } else {
        return 0;
    }
}

/**
 * Get the module ID from the database
 * @param string $name
 * @return array|int
 */
function getModuleId($name)
{
    $db = \Config\Database::connect(); // Connect to the database
    $sql = "SELECT module_id FROM orvexa_module WHERE module_name = :name:";
    $query = $db->query($sql, ['name' => $name]);

    if ($query) {
        $res = $query->getResultArray();
        return $res[0]['module_id'];
    } else {
        return 0;
    }
}
