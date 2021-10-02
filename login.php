<?php include("layout/base-header.php"); ?>

<?php
require_once("modules/core.php");

if (isset($_POST["username"]) && isset($_POST["password"])) {
    Auth::login($_POST["username"], $_POST["password"]);
    if (Auth::isLoggedIn()) {
        echo "hello";
        header("Location: index.php");
    } else {
        $login_error = "Invalid username or password";
    }
}

?>

<body>
    <main class="login-form">
        <div class="cotainer">
            <div class="row justify-content-center">
                <div class="col-10 col-md-4">
                    <div class="my-5" style="height: 100px; border: 1px solid grey;"></div>
                    <div class="card">
                        <div class="card-header">Login</div>
                        <div class="card-body">
                            <?php if(isset($login_error)): ?>
                            <div class="alert alert-danger alert-sm" role="alert">
                                <?= $login_error ?>
                            </div>
                            <?php endif ?>
                            <form action="login.php" method="post">
                                <div class="form-group row">
                                    <label for="username" class="col-md-4 col-form-label text-md-right">Username</label>
                                    <div class="col-md-6">
                                        <input autocomplete="off" type="text" id="username" class="form-control" name="username" required autofocus>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="password" class="col-md-4 col-form-label text-md-right">Password</label>
                                    <div class="col-md-6">
                                        <input autocomplete="off" type="password" id="password" class="form-control" name="password" required>
                                    </div>
                                </div>

                                <div class="col-md-6 offset-md-4 text-center">
                                    <button type="submit" class="btn btn-primary">
                                        Submit
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>

<?php include("layout/base-footer.php"); ?>