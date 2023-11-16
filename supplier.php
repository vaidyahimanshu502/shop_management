<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop-Management | Suppliers</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body>
   <?php include("./partials/header.php") ?>

   <h1 class="text-center text-secondary display-3">Supplier's List</h1>

   <div class="container mx-auto m-5">
        <div class="row">
            <div class="col-md-12">
                  <table class="table m-3">
                  <a href="php_actions/createSupplier.php" class="btn btn-primary m-1">Add-Supplier</a>
                     <thead>
                        <tr>
                           <th scope="col">S.no.</th>
                           <th scope="col">Supplier Name</th>
                           <th scope="col">Contact No.</th>
                           <th scope="col">Address</th>
                           <th scope="col">Actions</th>
                        </tr>
                     </thead>
                     <tbody>
                        <?php
                           // db connection
                           include('config/db_connect.php');

                           $sql = "SELECT * FROM supplier";
                           $result = $conn->query($sql);

                           if ($result) {
                              $counter = 1; // Initialize the counter variable

                              while ($row = $result->fetch_assoc()) {
                                 $supplierId = isset($row['id']) ? $row['id'] : '';
                                 $supplierName = isset($row['supplier_name']) ? $row['supplier_name'] : '';
                                 $mobileNo = isset($row['mobile_no']) ? $row['mobile_no'] : '';
                                 $address = isset($row['address']) ? $row['address'] : '';

                                 echo '
                                 <tr>
                                    <th>' . $counter . '</th>
                                    <td>' . $supplierName . '</td>
                                    <td>' . $mobileNo . '</td>
                                    <td>' . $address . '</td>
                                    <th>
                                       <a href="php_actions/editSupplier.php?id=' . $supplierId . '" class="btn btn-primary btn-sm">Edit</a>
                                       <a href="./php_actions/deleteSupplier.php?id=' . $supplierId . '" class="btn btn-danger btn-sm">Delete</a>
                                    </th>
                                 </tr> 
                                 ';

                                 $counter++; // Increment the counter variable
                              }
                           } else {
                              echo "Invalid query.";
                           }
                        ?>
                     </tbody>
                  </table>
             </div>
         </div>
     </div>
    
   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
