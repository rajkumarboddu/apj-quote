<?php include("layout/header.php"); ?>

<?php
require_once("modules/core.php");
Utils::checkAuth();
require_once("modules/product.php");

$product = new Product();
$products = [];
$heading = "Recently added products";
$no_match_found = "";
if(isset($_GET['product_code']) && !empty($_GET['product_code'])) {
    $products = $product->get_product_by_code($_GET['product_code']);
    $heading = "Search result";
    if(count($products) === 0) {
        $no_match_found = "No results found for ".$_GET['product_code'];
    }
} else {
    $products = $product->get_recently_added();
}

?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <form action="index.php" method="get">
                <div class="row mb-2">
                    <div class="col-8 col-sm-4">
                        <div class="input-group">
                            <input name="product_code" type="text" class="form-control form-control-sm" placeholder="Search with product code" />
                            <div class="input-group-append">
                                <button type="submit" class="input-group-text" role="button">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                        
                    </div><!-- /.col -->
                    <div class="col-4 col-sm-3 text-center">
                        <a href="product.php?action=new" role="button" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus"></i> &nbsp;
                            Add New
                        </a>
                    </div><!-- /.col -->
                </div><!-- /.row -->
            </form>
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <p class="h6 mb-3">
                        <?= $heading ?>
                    </p>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <?php Utils::printError(); ?>
                    <?php if($no_match_found !== ""): ?>
                        <div class="text-muted"><?= $no_match_found; ?></div>
                    <?php endif; ?>
                    <?php foreach($products as $p) { ?>
                    <div class="card">
                        <div class="card-body px-3 pt-3 pb-2">
                            <a class="text-primary p-edit-btn" href="product.php?action=edit&p_id=<?= $p['id'] ?>">
                                <i class="fas fa-edit"></i>
                            </a>
                            <div class="row">
                                <div class="col-4 p-card-img-container">
                                    <img src="<?= Product::get_web_image_path($p['img_name']) ?>" class="p-card-img" />
                                    <div class="fs-small text-muted mt-1">
                                        Date: <?= $p['updated_date'] ?>
                                    </div>
                                </div>
                                <div class="col-8">
                                    <div>Code: <?= Product::get_product_code($p['id']) ?></div>
                                    <div class="mb-1">
                                        Category: <?= $p['category']." - ".$p['subcategory'] ?>
                                    </div>
                                    <div class="row">
                                        <div class="col-4 p-1">
                                            <a href="quote.php?p_id=<?= $p['id'] ?>&quote=quote_a" class="btn btn-success p-q-btn p-1 w-100">
                                                <div>
                                                    <i class="fas fa-rupee-sign"></i> 
                                                    <span class="price-to-format">
                                                        <?= $p['quote_a'] ?>
                                                    </span>
                                                </div>
                                                <div>Quote</div>
                                            </a>
                                        </div>
                                        <div class="col-4 p-1">
                                            <a href="quote.php?p_id=<?= $p['id'] ?>&quote=quote_b" class="btn btn-info p-q-btn p-1 w-100">
                                                <div>
                                                    <i class="fas fa-rupee-sign"></i> 
                                                    <span class="price-to-format">
                                                        <?= $p['quote_b'] ?>
                                                    </span>
                                                </div>
                                                <div>Quote (RQ)</div>
                                            </a>
                                        </div>
                                        <div class="col-4 p-1">
                                            <a href="quote.php?p_id=<?= $p['id'] ?>&quote=quote_c" class="btn btn-warning p-q-btn p-1 w-100">
                                                <div>
                                                    <i class="fas fa-rupee-sign"></i>
                                                    <span class="price-to-format">
                                                        <?= $p['quote_c'] ?>
                                                    </span>
                                                </div>
                                                <div>Quote (FRQ)</div>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php } ?>
                </div>
                <!-- /.col-md-6 -->
            </div>
            <!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->

<?php include("layout/footer.php"); ?>