<?php

class Api {
    const API_ROUTES_MAP = [
        'getCategories' => [],
        'getSubCategories' => ['category_id'],
        'getFields' => ['category_id'],
        'updateFieldPrices' => ['field_prices'],
        'getProductFields' => ['p_id']
    ];
    public static $db_conn;

    public static function execute($api_name) {
        if(!isset(self::API_ROUTES_MAP[$api_name])) {
            $resp = [
                'status' => 404,
                'message' => 'API does not exist'
            ];
            echo json_encode($resp);
            exit();
        }

        self::checkAndSanitizeReqBody($_REQUEST, self::API_ROUTES_MAP[$api_name]);

        require_once("db.php");
        $db = new DB;
        self::$db_conn = $db->conn;
        self::$api_name($_REQUEST);
    }

    public static function getCategories() {
        $query = "select * from category where parent_id is null and active=1";
        $result = self::$db_conn->query($query);
        self::sendResponse(200, 'Categories fetched successfully', $result->fetch_all(MYSQLI_ASSOC));
    }

    public static function getSubCategories($req) {
        $category_id = $req['category_id'];
        $query = "select * from category where parent_id=$category_id and active=1";
        $result = self::$db_conn->query($query);
        self::sendResponse(200, 'Sub Categories fetched successfully', $result->fetch_all(MYSQLI_ASSOC));
    }

    public static function getFields($req) {
        $category_id = $req['category_id'];
        $query = "select * from category_fields where category_id is null and active=1";
        $result = self::$db_conn->query($query);
        $body['common_fields'] = $result->fetch_all(MYSQLI_ASSOC);
        $query = "select * from category_fields where category_id=$category_id and active=1";
        $result = self::$db_conn->query($query);
        $body['category_fields'] = $result->fetch_all(MYSQLI_ASSOC);
        $query = "select * from category_fields where category_id is not null and category_id<>$category_id and active=1";
        $result = self::$db_conn->query($query);
        $body['other_fields'] = $result->fetch_all(MYSQLI_ASSOC);
        self::sendResponse(200, 'Fetched fields successfully', $body);
    }

    public static function getProductFields($req) {
        $p_id = $req['p_id'];
        $query = "
            select *, (pf.quantity*cf.quote_1_price) as total_price, pf.id as pf_id 
            from product_fields as pf
            join category_fields as cf on cf.id=pf.field_id
            where pf.product_id=$p_id
        ";
        $result = self::$db_conn->query($query);
        $body['p_fields'] = $result->fetch_all(MYSQLI_ASSOC);
        $query = "
            select * from category_fields as cf where cf.id not in (
                select  field_id
                from product_fields
                where product_id=$p_id
            )
        ";
        $result = self::$db_conn->query($query);
        $body['other_fields'] = $result->fetch_all(MYSQLI_ASSOC);
        self::sendResponse(200, 'Fetched fields successfully', $body);
    }

    public static function updateFieldPrices($req) {
        $field_prices = $req['field_prices'];
        try {
            foreach($field_prices as $cf_id => $cf_cols) {
                $condition = "id=$cf_id";
                $col_vals = [];
                foreach($cf_cols as $cf_col => $cf_col_price) {
                    array_push($col_vals, "$cf_col = $cf_col_price");
                }
                $set_clause = implode(", ", $col_vals);
                $query = "update category_fields set $set_clause where $condition";
                self::$db_conn->query($query);
            }
            $_SESSION['price_update_success'] = true;
            self::sendResponse(200, 'Updated the prices successfully');
        } catch(Exception $err) {
            self::sendResponse(500, 'Unable to update the prices');
        }
    }

    private static function sendResponse($status, $msg, $body = []){
        $resp = [
            'status' => $status,
            'msg' => $msg,
        ];
        if(count($body) > 0) {
            $resp['body'] = $body;
        }
        echo json_encode($resp);
        exit();
    }

    private static function checkAndSanitizeReqBody($reqBody, $requiredFields){
        foreach($requiredFields as $requiredField) {
            if(!isset($reqBody[$requiredField])) {
                self::sendResponse(400, "$requiredField is required");
            }
        }

        foreach($_REQUEST as $field => $value) {
            if(!is_array($value)) {
                $_REQUEST[$field] = Utils::sanitize($value);
            }
        }
    }
}

?>