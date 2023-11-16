<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop-Management | Home</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body>
<?php include("./partials/header.php") ?>
   <div class="container">
    <div class="row">
        <div class="col-md-12">
        <h1 class="text-center text-secondary display-3">List of Items</h1>
          <a href="php_actions/createItem.php" class="btn btn-outline-primary m-1">Create Item</a>
          <table class="table m-3">
             <thead>
                <tr>
                    <th scope="col">S.no.</th>
                    <th scope="col">Item name</th>
                    <th scope="col">Item code</th>
                    <th scope="col">Price</th>
                    <th scope="col">Actions</th>
                </tr>
             </thead>
           <tbody>
                    <?php include('./partials/display_items.php'); ?>
           </tbody>
           </table>
        </div>
    </div>
   </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
