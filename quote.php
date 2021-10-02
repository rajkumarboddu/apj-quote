<?php include("layout/header.php"); ?>

<?php
require_once("modules/core.php");
Utils::checkAuth();
if (
    (!isset($_GET['p_id']) || empty($_GET['p_id'])) ||
    (!isset($_GET['quote']) || empty($_GET['quote']))
) {
    header("Location: index.php");
}
require_once("modules/product.php");

$p_id = $_GET['p_id'];
$quote = $_GET['quote'];
if (!in_array($quote, ["quote_a", "quote_b", "quote_c"])) {
    $quote = "quote_a";
}

$heading_sufix = [
    'quote_a' => "Quotation",
    'quote_b' => "Revised Quote",
    'quote_c' => "Final Revised Quote"
];
$download_sufix = [
    'quote_a' => "Quote",
    'quote_b' => "RQ",
    'quote_c' => "FRQ"
];
$product = new Product;
$p_fields = $product->get_quote($p_id);
$p = $p_fields[0];
$total_price = 0;

?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-12">
                    <a data-html2canvas-ignore href="#" role="button" id="back-btn">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    <h5 class="d-inline-block ml-2"><?= $heading_sufix[$quote] ?></h5>
                    <div class="float-right print-field text-sm"><?= date("d-M-Y") ?></div>
                </div>
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12 text-center">
                    <img src="<?= Product::get_web_image_path($p['img_name']) ?>" class="p-quote-img" />
                    <div class="text-center mt-2" id="product-code">
                        <?= Product::get_product_code($p['id']) ?>
                    </div>
                </div>
                <?php foreach ($p_fields as $p_field) { ?>
                    <div class="col-12">
                        <div class="row mt-4">
                            <div class="col-4 pt-1 text-center">
                                <?= $p_field['field_name']; ?>
                            </div>
                            <div class="col-4">
                                <div class="input-group">
                                    <input disabled value="<?= $p_field['quantity'] ?>" type="number" class="form-control text-right" aria-label="weight">
                                    <div class="input-group-append">
                                        <span class="input-group-text">
                                            <?= $p_field['unit'] ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-4 pt-1 text-right">
                                <i class="fas fa-rupee-sign rupee-sign"></i>
                                <span class="field-price"><?= $p_field[$quote] ?></span>
                            </div>
                        </div>
                    </div>
                <?php
                    $total_price += $p_field[$quote];
                }
                ?>
                <div class="col-12 mb-4">
                    <div class="row mt-4">
                        <div class="col-8 text-right">Total Cost: </div>
                        <div class="col-4 text-right">
                            <i class="fas fa-rupee-sign rupee-sign"></i>
                            <?= $total_price ?>
                        </div>
                    </div>
                </div>
                <div class="col-12 mt-3 mb-5 text-center">
                    <button id="download-btn" class="btn btn-primary">Download</button>
                    <a id="hidden-download-link" class="d-none" href="">Hidden download link</a>
                </div>
            </div>
            <!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->

<?php include("layout/footer.php"); ?>

<script src="assets/js/html2canvas.min.js"></script>
<script>
    $(document).ready(function() {
        $("#back-btn").click(function(e) {
            e.preventDefault();
            window.history.back();
        });

        $('#download-btn').click(function() {
            $(this).parent("div").hide();
            html2canvas(document.querySelector("body")).then(canvas => {
                $(this).parent("div").show();
                var canvasData = canvas.toDataURL("image/png");
                var newData = canvasData.replace(/^data:image\/png/, "data:application/octet-stream");
                var downloadFileName = `${$("#product-code").text().trim()}_<?= $download_sufix[$quote] ?>_${Date.now()}.png`;
                $("#hidden-download-link")
                    .attr("download", downloadFileName)
                    .attr("href", newData);
                setTimeout(function() {
                    document.getElementById("hidden-download-link").click();
                }, 500);
            });
        });
    });
</script>