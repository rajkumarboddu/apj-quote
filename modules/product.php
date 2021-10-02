<?php

class Product
{
    public static $product_code_prefix = "AJ";
    public static $product_img_web_path = "product-imgs/web";
    public static $product_img_original_path = "product-imgs/original";

    public function add_product_code_to_image($img_name, $ext, $p_code)
    {
        $image = null;
        $file_path = self::$product_img_original_path."/$img_name";
        $is_jpg = $ext === "jpg" || $ext==="jpeg";
        $is_png = $ext === "png";
        if($is_jpg) {
            $image = imagecreatefromjpeg($file_path);
            imagejpeg($image, $file_path);
        } else if($is_png) {
            $image = imagecreatefrompng($file_path);
            imagejpeg($image, $file_path);
        }

        $text_color = imagecolorallocate($image, 255, 0, 0);
        list($width, $height) = getimagesize($file_path);
        $font = __DIR__ ."/RobotoMono-Bold.ttf";
        imagettftext($image, 24, 0, 10, $height-10, $text_color, $font, $p_code);

        $new_img_path = self::get_web_image_path($img_name);
        if($is_jpg) {
            imagejpeg($image, $new_img_path);
        } else if($is_png) {
            imagepng($image, $new_img_path);
        }

        imagedestroy($image);
    }

    public function delete_file($file_path)
    {
    }

    public static function get_product_code($p_id) {
        return self::$product_code_prefix."$p_id";
    }

    public static function get_web_image_path($img_name) {
        return self::$product_img_web_path."/$img_name";
    }

    public static function get_original_image_path($img_name) {
        return self::$product_img_original_path."/$img_name";
    }

    public function add_product($req) {
        try {
            $required_fields = ['category_id', 'subcategory_id'];
            foreach ($required_fields as $required_field) {
                if (!isset($req[$required_field]) || empty($req[$required_field])) {
                    $_SESSION["error"] = "$required_field is required";
                    return false;
                }
            }

            if (empty($_FILES['product_img']['name'])) {
                $_SESSION["error"] = "Product image is required";
                return false;
            }

            $file_tmp = $_FILES['product_img']['tmp_name'];
            $file_segs = explode('.', $_FILES['product_img']['name']);
            $file_ext = strtolower(end($file_segs));

            $extensions = array("jpeg", "jpg", "png");

            if (in_array($file_ext, $extensions) === false) {
                $_SESSION["error"] = "Please choose a JPEG or PNG file.";
                return false;
            }
            $img_name = MD5(microtime()) . ".$file_ext";
            $img_path = self::$product_img_original_path."/$img_name";
            move_uploaded_file($file_tmp, $img_path);

            if($req['subcategory_id']==='$new' && 
                (isset($req['new_subcategory']) && empty($req['new_subcategory']))
            ) {
                $_SESSION["error"] = "New subcategory is required";
                return false;
            }

            require_once("db.php");
            $db = new DB;

            $query = "insert into category (category, parent_id) 
                    values ('".$req['new_subcategory']."', ".$req['category_id'].")";
            $db->conn->query($query);
            $req['subcategory_id'] = $db->conn->insert_id;
            if($db->conn->error) {
                echo $db->conn->error;
                exit();
            }

            $query = "insert into products 
                    (category_id, subcategory_id, img_name) 
                    values (" . $req['category_id'] . ", " . $req['subcategory_id'] . ", '$img_name')";
            
            $result = $db->conn->query($query);
            if ($result == true) {
                $p_id = $db->conn->insert_id;

                // add product code to image
                $p_code = self::get_product_code($p_id);
                $this->add_product_code_to_image($img_name, $file_ext, $p_code);

                $query = "";
                foreach ($req['fields'] as $field_id => $field_data) {
                    $quantity = (float)$field_data['quantity'];
                    if($quantity > 0) {
                        $query .= "insert into product_fields 
                                (product_id, field_id, quantity) 
                                values ($p_id, $field_id, $quantity);";
                    }
                }
                $result = $db->conn->multi_query($query);
                if ($result === true) {
                    header("Location: index.php");
                }
                $_SESSION["error"] = "Unable to add product";
                return $result;
            } else {
                $_SESSION["error"] = "Unable to add product";
                return $result;
            }
        } catch (Exception $e) {
            $_SESSION["error"] = "Unable to add product";
            return $result;
        }
    }

    public function update_product($req) {
        try {
            $required_fields = ['category_id', 'subcategory_id'];
            foreach ($required_fields as $required_field) {
                if (!isset($req[$required_field]) || empty($req[$required_field])) {
                    $_SESSION["error"] = "$required_field is required";
                    return false;
                }
            }

            $img_name = $img_path = $file_ext = "";
            if (!empty($_FILES['product_img']['name'])) {
                $file_tmp = $_FILES['product_img']['tmp_name'];
                $file_segs = explode('.', $_FILES['product_img']['name']);
                $file_ext = strtolower(end($file_segs));

                $extensions = array("jpeg", "jpg", "png");

                if (in_array($file_ext, $extensions) === false) {
                    $_SESSION["error"] = "Please choose a JPEG or PNG file.";
                    return false;
                }
                $img_name = MD5(microtime()) . ".$file_ext";
                $img_path = self::$product_img_original_path."/$img_name";
                move_uploaded_file($file_tmp, $img_path);
            }

            require_once("db.php");
            $db = new DB;
            $p_id = $req['p_id'];
            $query = "select img_name from products where id=$p_id";
            $result = $db->conn->query($query);
            $product = $result->fetch_assoc();
            $query = "update products 
                    set category_id=".$req['category_id'].", 
                    subcategory_id=".$req['subcategory_id'];
            if($img_name !== "") {
                $query .= ", img_name='$img_name'";
            }
            $query.= " where id=$p_id";
            
            $result = $db->conn->query($query);
            if($result === true) {
                if($img_name !== "") {
                    $p_code = self::get_product_code($p_id);
                    $this->add_product_code_to_image($img_name, $file_ext, $p_code);
                    $img_name = $product['img_name'];
                    unlink(self::get_web_image_path($img_name));
                    unlink(self::get_original_image_path($img_name));
                }
                
                $query = "";
                $fields_to_delete = [];
                foreach($req['p_fields'] as $pf_id => $pf) {
                    $quantity = (float)$pf['quantity'];
                    if($quantity > 0) {
                        $query .= "update product_fields set quantity=$quantity where id=$pf_id;";
                    } else {
                        array_push($fields_to_delete, $pf_id);
                    }
                }
                if(count($fields_to_delete) > 0) {
                    $pf_ids = implode(",", $fields_to_delete);
                    $query .= "delete from product_fields where id in ($pf_ids);";
                }
                if(isset($req['fields'])) {
                    foreach($req['fields'] as $field_id => $field) {
                        $quantity = (float)$field['quantity'];
                        if($quantity > 0) {
                            $query .= "insert into product_fields 
                                    (product_id, field_id, quantity) 
                                    values ($p_id, $field_id, $quantity);";
                        }
                    }
                }
                if($query !== "") {
                    $result = $db->conn->multi_query($query);
                    if($result !== true) {
                        $_SESSION['error'] = "Unable to update product";
                    }
                }
                $_SESSION['success'] = "Product updated successfully";
            }
        } catch(Exception $e) {
            $_SESSION['error'] = "Unable to update product";
        }
    }

    public function get_recently_added($limit = 10, $p_id = "") {
        $filter = "";
        if($p_id !== "") {
            $filter = "where p.id=$p_id";
        }
        $query = "
            select p.*, c.category, sc.category as subcategory,
            sum(cf.quote_1_price*pf.quantity) as quote_a,
            sum(cf.quote_2_price*pf.quantity) as quote_b,
            sum(cf.quote_3_price*pf.quantity) as quote_c,
            DATE_FORMAT(p.updated_at, '%e %b %Y') as updated_date
            from products as p
            join category as c on p.category_id=c.id
            join category as sc on p.subcategory_id=sc.id
            join product_fields as pf on pf.product_id=p.id
            join category_fields as cf on cf.id=pf.field_id
            $filter
            group by p.id
            order by p.updated_at desc limit $limit;
        ";
        try {
            require_once("db.php");
            $db = new DB;
            $result = $db->conn->query($query);
            return $result->fetch_all(MYSQLI_ASSOC);
        } catch(Exception $e) {
            $_SESSION["error"] = "Unable to fetch Products";
        }
    }

    public function get_product_by_code($p_code) {
        $p_id = str_replace(self::$product_code_prefix, "", $p_code);
        return $this->get_recently_added(1, $p_id);
    }

    public function get_quote($p_id) {
        $query = "
            select p.*, cf.field_name, cf.unit, pf.quantity,
            (cf.quote_1_price*pf.quantity) as quote_a,
            (cf.quote_2_price*pf.quantity) as quote_b,
            (cf.quote_3_price*pf.quantity) as quote_c
            from products as p
            join product_fields as pf on pf.product_id=p.id
            join category_fields as cf on cf.id=pf.field_id
            where p.id=$p_id;
        ";
        
        try {
            require_once("modules/db.php");
            $db = new DB;
            $result = $db->conn->query($query);
            return $result->fetch_all(MYSQLI_ASSOC);
        } catch(Exception $e) {
            $_SESSION["error"] = "Unable to fetch quote details";
        }
    }
}
