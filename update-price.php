<?php include("layout/header.php"); ?>

<?php
require_once("modules/core.php");
Utils::checkAuth();
require_once("modules/db.php");

$db = new DB;
$query = "select *, cf.id as cf_id from 
                    category as c join category_fields as cf 
                    on c.id=cf.category_id
                    order by c.id asc";
$c_fields = $db->conn->query($query)->fetch_all(MYSQLI_ASSOC);

?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-12">
                    <h5 class="d-inline-block text-center">Update Price</h5>
                </div>
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">
            <div class="text-danger mb-3" style="display: none;">Unable to update the prices</div>
            <?php if(isset($_SESSION['price_update_success'])) : ?>
                <div class="text-success mb-3">Prices updated successfully</div>
            <?php 
                unset($_SESSION['price_update_success']);
                endif;
            ?>
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col" style="width: 30%;" class="pl-1">Item</th>
                        <th scope="col" style="width: 15%;" class="pl-1">Unit</th>
                        <th scope="col" class="pl-1">Price / Unit</th>
                    </tr>
                </thead>
                <tbody>
                    <?php for ($i = 0, $j = 0, $c_field_count = count($c_fields); $i < $c_field_count; $i++) {
                        $is_gold = $c_fields[$i]['category'] === "Gold";
                    ?>
                        <tr>
                            <td colspan="3" class="p-0">
                                <table class="table table-borderless table-sm">
                                    <thead>
                                        <tr>
                                            <th scope="col" colspan="<?= $is_gold ? 3 : 4; ?>">
                                                <?= $c_fields[$i]['category']; ?>
                                            </th>
                                            <?php if(!$is_gold): ?>
                                            <th scope="col" class="text-right">
                                                <i data-enable="true" style="cursor: pointer;" class="fas fa-edit edit-btn text-primary"></i>
                                            </th>
                                            <?php endif; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $continue_loop = true;
                                        do { ?>
                                            <tr>
                                                <td style="width: 30%;">
                                                    <?= $c_fields[$j]['field_name']; ?>
                                                </td>
                                                <td style="width: 15%;">
                                                    <?= $c_fields[$j]['unit']; ?>
                                                </td>
                                                <?php if (!$is_gold) { ?>
                                                    <?php for ($k = 1; $k < 4; $k++) { ?>
                                                        <td>
                                                            <?php $q1_price = $c_fields[$j]["quote_" . $k . "_price"]; ?>
                                                            <input class="update-price-input" type="number" data-price="<?= $q1_price; ?>" data-cf-id="<?= $c_fields[$j]['cf_id']; ?>" data-col="<?= "quote_" . $k . "_price"; ?>" data-non-gold="true" disabled value="<?= $q1_price; ?>" />
                                                        </td>
                                                    <?php } ?>
                                                <?php } else { ?>
                                                    <td>
                                                        <?= $c_fields[$j]['quote_1_price']; ?>
                                                    </td>
                                                <?php } ?>
                                            </tr>
                                        <?php
                                            if ($j + 1 < $c_field_count && $c_fields[$j]['category_id'] === $c_fields[$j + 1]['category_id']) {
                                                $continue_loop = true;
                                            } else {
                                                $i = $j + 1;
                                                $continue_loop = false;
                                            }
                                            $j++;
                                        } while ($continue_loop)
                                        ?>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
            <!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->

<?php include("layout/footer.php"); ?>

<script>
    $(document).ready(function() {
        $(".edit-btn").click(function() {
            var enable = $(this).data('enable');
            if (enable) {
                $(this).closest("table").find('.update-price-input').attr('disabled', !enable);
                $(this).data('enable', !enable);
                $(this).removeClass("fa-edit");
                $(this).toggleClass("fa-save");
            } else {
                var fields_to_update = {};
                $(this).closest("table").find('.update-price-input').each(function() {
                    var this_val = parseInt($(this).val());
                    if ($(this).data('price') !== this_val) {
                        var cf_id = $(this).data('cf-id');
                        var col = $(this).data('col');
                        if (!fields_to_update[cf_id]) {
                            fields_to_update[cf_id] = {};
                        }
                        fields_to_update[cf_id][col] = this_val;
                    }
                });
                
                $.post("api.php",
                    {
                        api_name: "updateFieldPrices",
                        field_prices: fields_to_update
                    },
                    function(data, status) {
                        console.log(data);
                        data = JSON.parse(data);
                        if(data.status != 200) {
                            $('.alert-danger').show();
                        } else {
                            location.reload();
                        }
                    }
                );
            }
        });
    });
</script>