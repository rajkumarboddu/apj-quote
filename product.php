<?php include("layout/header.php"); ?>

<?php
require_once("modules/core.php");
Utils::checkAuth();

if (isset($_POST["category_id"])) {
    require_once("modules/product.php");
    $product = new Product();
    if(isset($_POST["p_fields"])) {
        $product->update_product($_POST);
    } else {
        $product->add_product($_POST);
    }
}

$is_new = true;
$p = null;
$heading = "Add Product";
if (isset($_GET['action'])) {
    if ($_GET['action'] === "edit" && isset($_GET['p_id']) && !empty($_GET['p_id'])) {
        require_once("modules/product.php");
        $product = new Product();
        $p_id = $_GET['p_id'];
        $p_fields = $product->get_quote($p_id);
        if (count($p_fields) === 0) {
            $p_not_found = true;
        } else {
            $p = $p_fields[0];
            $is_new = false;
            $heading = "Edit Product: " . Product::get_product_code($p_id);
        }
    }
}

?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-12">
                    <a href="index.php" role="button">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    <h5 class="d-inline-block ml-2">
                        <?= $heading ?>
                    </h5>
                </div>
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">
            <form action="" method="post" enctype="multipart/form-data">
                <?php if(!$is_new) : ?>
                    <input type="hidden" name="p_id" value="<?= $p['id'] ?>" />
                <?php endif ?>
                <div class="row">
                    <div class="col-12 category-col mb-2">
                        <?php 
                            Utils::printError();
                            Utils::printSuccess();
                        ?>
                        <select required id="category_id" name="category_id" class="form-control mb-3" aria-label="Default select example">
                            <option value="" selected>Select Category</option>
                        </select>
                        <select required id="subcategory_id" name="subcategory_id" class="form-control" aria-label="Default select example">
                            <option value="" selected>Select Sub Category</option>
                        </select>
                        <input id="new-s-category" class="form-control mt-2" required style="display: none;" type="text" placeholder="Enter new Subcategory" name="new_subcategory" />
                    </div>
                    <div class="col-12" id="total-col" style="display: none;">
                        <div class="row mt-2">
                            <div class="col-8 text-right pt-1">
                                Total
                            </div>
                            <div class="col-4 pt-1 text-center">
                                <i class="fas fa-rupee-sign rupee-sign"></i> <span id="field-cal-price-total">0</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="row mt-4">
                            <div class="col-6">
                                <div class="mb-2">Product image</div>
                                <input <?php if($is_new): ?> required <?php endif; ?> accept="image/jpeg,image/png" id="product-img" name="product_img" type="file" class="form-control-file" />
                            </div>
                            <div class="col-6" id="img-preview-container">
                                <?php
                                    if(!$is_new):
                                ?>
                                <img src="<?= Product::get_web_image_path($p['img_name']) ?>" class="product-img-preview" />
                                <?php
                                    endif;
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 my-5 text-center">
                        <button type="submit" class="btn-primary btn">Submit</button>
                    </div>
                </div>
            </form>
            <!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->

<?php include("layout/footer.php"); ?>

<script>
    $(document).ready(function() {

        var isNew = "<?= $is_new ?>" === "true";

        $.get("api.php", {
                api_name: "getCategories"
            },
            function(data, status) {
                data = JSON.parse(data);
                if (data.status != 200) {
                    $('.alert-danger').show();
                } else {
                    data.body.forEach(function(category) {
                        var option = `<option value='${category.id}'>${category.category}</option>`;
                        $('#category_id').append(option);
                    });
                    <?php if(!$is_new) : ?>
                        $("#category_id").val('<?= $p['category_id'] ?>').trigger('change');
                    <?php endif; ?>
                }
            }
        );

        $("#product-img").change(function() {
            var files = $(this).prop("files");
            var imgSrc = URL.createObjectURL(files[0]);
            var imgMarkup = `<img src="${imgSrc}" class="product-img-preview" />`;
            $("#img-preview-container").html(imgMarkup);
        });

        $('#subcategory_id').change(function() {
            var s_id = $(this).val();
            if(s_id!=="" && s_id==="$new") {
                $("#new-s-category").show();
            } else {
                $("#new-s-category").hide();
            }
        });

        $("#category_id").change(function() {
            if ($(this).val() === "") {
                $("#total-col").hide();
                $(".field-col").remove();
                $("#add-field-col").remove();
            } else {
                $('#subcategory_id option:not(:first-child)').remove();
                $.get("api.php", {
                        api_name: "getSubCategories",
                        category_id: $(this).val()
                    },
                    function(data, status) {
                        data = JSON.parse(data);
                        if (data.status != 200) {
                            $('.alert-danger').show();
                        } else {
                            data.body?.forEach(function(s_category) {
                                var option = `<option value='${s_category.id}'>${s_category.category}</option>`;
                                $('#subcategory_id').append(option);
                            });
                            $('#subcategory_id').append(`<option value='$new'>New</option>`);
                            <?php if(!$is_new) : ?>
                                $("#subcategory_id").val('<?= $p['subcategory_id'] ?>');
                            <?php endif; ?>
                        }
                    }
                );

                var getFieldMarkup = (field) => {
                    const value = field.quantity || 0;
                    const total_price = field.total_price || 0;
                    const name = field.product_id ? `p_fields[${field.pf_id}][quantity]` : `fields[${field.id}][quantity]`;
                    return `
                    <div class="col-12 field-col">
                        <div class="row mt-2">
                            <div class="col-4 pt-1 text-center">${field.field_name}</div>
                            <div class="col-4">
                                <div class="input-group">
                                    <input 
                                        required
                                        type="number" 
                                        value="${value}" 
                                        class="form-control field-input" 
                                        aria-label="weight" 
                                        data-price="${field.quote_1_price}"
                                        data-category="${field.category}"
                                        name="${name}"
                                    />
                                    <div class="input-group-append">
                                        <span class="input-group-text">${field.unit}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-4 pt-1 text-center">
                                <i class="fas fa-rupee-sign rupee-sign"></i> <span class="field-cal-price">${total_price.toLocaleString('en-IN')}</span>
                            </div>
                        </div>
                    </div>
                    `;
                };

                var getFieldAdditionMarkup = (fieldOptionsMarkup) => {
                    return `
                    <div id="add-field-col" class="col-12">
                        <div class="row mt-4">
                            <div class="col-10">
                                <select id="other_fields" class="form-control">
                                    <option value="" selected>Select field</option>
                                    ${fieldOptionsMarkup}
                                </select>
                            </div>
                            <div class="col-2 text-center">
                                <button type="button" id="add-field" class="btn btn-primary">Add</button>
                            </div>
                        </div>
                    </div>
                    `;
                };

                var appendAddFieldMarkup = (other_fields) => {
                    var optionsMarkup = "";
                    other_fields.forEach(function(field) {
                        optionsMarkup += `<option data-field-id="${field.id}" value='${JSON.stringify(field)}'>${field.field_name}</option>`;
                    });
                    var addFieldMarkup = getFieldAdditionMarkup(optionsMarkup);
                    $(addFieldMarkup).insertAfter("#total-col");
                    $("#add-field").click(function() {
                        if ($("#other_fields").val() !== "") {
                            var field = JSON.parse($("#other_fields").val());
                            var fieldMarkup = getFieldMarkup(field);
                            $(fieldMarkup).insertBefore("#total-col");
                            $("#other_fields").val("");
                            $(`option[data-field-id='${field.id}']`).remove();
                        }
                    });
                }

                var attachOnFieldInputChange = () => {
                    $('body').on('keyup', ".field-input", function() {
                        if ($(this).val() == "") {
                            $(this).closest(".field-col").find(".field-cal-price").html(0);
                        } else {
                            var quantity = parseFloat($(this).val());
                            var unitPrice = parseInt($(this).data('price'));
                            $(this).closest(".field-col").find(".field-cal-price").html(quantity * unitPrice);
                        }
                        var totalPrice = 0;
                        $('.field-cal-price').each(function() {
                            totalPrice += parseFloat($(this).text());
                        });
                        $("#field-cal-price-total").html(totalPrice);
                    });
                }

                <?php if ($is_new) { ?>
                    $(".field-col").remove();
                    $("#add-field-col").remove();
                    $.get("api.php", {
                            api_name: "getFields",
                            category_id: $(this).val()
                        },
                        function(data, status) {
                            data = JSON.parse(data);
                            if (data.status != 200) {
                                $('.alert-danger').show();
                            } else {
                                $("#total-col").show();

                                var markup = "";
                                data.body.common_fields.forEach(function(field) {
                                    markup += getFieldMarkup(field);
                                });
                                data.body.category_fields.forEach(function(field) {
                                    markup += getFieldMarkup(field);
                                });
                                $(markup).insertAfter(".category-col");
                                attachOnFieldInputChange();
                                appendAddFieldMarkup(data.body.other_fields);
                            }
                        }
                    );
                <?php } else { ?>
                    $.get("api.php", {
                            api_name: "getProductFields",
                            p_id: <?= $p['id'] ?>
                        },
                        function(data, status) {
                            data = JSON.parse(data);
                            if (data.status != 200) {
                                $('.alert-danger').show();
                            } else {
                                $("#total-col").show();

                                var markup = "";
                                var totalPrice = 0;
                                data.body.p_fields.forEach(function(field) {
                                    totalPrice += parseFloat(field.total_price);
                                    markup += getFieldMarkup(field);
                                });
                                $(markup).insertAfter(".category-col");
                                $("#field-cal-price-total").html(totalPrice);
                                attachOnFieldInputChange();
                                appendAddFieldMarkup(data.body.other_fields);
                            }
                        }
                    );
                <?php } ?>
            }
        });
    });
</script>