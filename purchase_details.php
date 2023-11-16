<?php
// Include the database connection
include("./config/db_connect.php");

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Store data from the form
    $currentDateTime = date('Y-m-d H:i:s');
    $purchaseStatus = 1;
    $supplierId = $_POST['supplierId'];
    $invoiceNo = $_POST['invoice_no'];
    $invoiceDate = $_POST['invoice_date'];

    // Initialize total amount
    $totalAmount = 0;

    if (empty($supplierId) || empty($invoiceDate) || empty($invoiceNo)) {
        echo '<script>alert("All fields are required.")</script>';
    } else {
        try {
            // Start the transaction
            $conn->begin_transaction();

            // Insert data into the purchase table with the initial total amount
            $stmtPurchase = $conn->prepare("INSERT INTO purchase (invoice_no, invoice_date, supplier_id, total_amount, created_at, status) VALUES (?, ?, ?, ?, ?, ?)");
            $stmtPurchase->bind_param("ssiisi", $invoiceNo, $invoiceDate, $supplierId, $totalAmount, $currentDateTime, $purchaseStatus);

            // Initialize arrays
            $item_ids = $_POST['item_id'];
            $quantities = $_POST['quantity'];
            $prices = $_POST['hidden_price'];

            if ($stmtPurchase->execute()) {             
                $purchaseId = $conn->insert_id;  // Retrieve the last inserted purchase ID
                foreach ($item_ids as $key => $item_id) {   // Process each item in the purchase details
                    if (empty($item_id)) {  // Skip processing if item_id is empty
                        continue;
                    }

                    // Check if item_id exists in the items table
                    $checkItemSql = "SELECT * FROM items WHERE id = ?";
                    $checkItemStmt = $conn->prepare($checkItemSql);
                    $checkItemStmt->bind_param("i", $item_id);
                    $checkItemStmt->execute();
                    $checkItemResult = $checkItemStmt->get_result();

                    if ($checkItemResult->num_rows == 0) {
                        echo '<script>alert("Item with ID ' . $item_id . ' does not exist.");</script>';
                        exit;
                    }

                    $quantity = isset($quantities[$key]) ? (int)$quantities[$key] : 0;
                    $price = isset($prices[$key]) ? (float)$prices[$key] : 0;
                    $amount = $quantity * $price;
                    $purchaseDetailsStatus = 1;

                    // Insert data into the purchase_details table
                    $stmtInsertDetails = $conn->prepare("INSERT INTO purchase_details (item_id, quantity, price, amount, purchase_id, created_at, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmtInsertDetails->bind_param("iiidisi", $item_id, $quantity, $price, $amount, $purchaseId, $currentDateTime, $purchaseDetailsStatus);

                    if (!$stmtInsertDetails->execute()) {
                        throw new Exception("Error in creating purchase details: " . $stmtInsertDetails->error);
                    }

                    $stmtInsertDetails->close();

                    // Accumulate the amount to the total amount
                    $totalAmount += $amount;
                }

                // Update the total amount in the purchase table
                $stmtUpdateTotalAmount = $conn->prepare("UPDATE purchase SET total_amount = ? WHERE id = ?");
                $stmtUpdateTotalAmount->bind_param("ii", $totalAmount, $purchaseId);

                if (!$stmtUpdateTotalAmount->execute()) {
                    throw new Exception("Error in updating total amount: " . $stmtUpdateTotalAmount->error);
                }

                $stmtUpdateTotalAmount->close();

                // Commit the transaction
                $conn->commit();

                echo '<script>alert("Purchase created successfully.");</script>';
                echo '<script>window.location.href = "./purchaseRecordsDetails.php";</script>';
            } else {
                throw new Exception("Error in creating the purchase: " . $stmtPurchase->error);
            }

            $stmtPurchase->close();
        } catch (Exception $e) {
            // Rollback the transaction in case of an exception
            $conn->rollback();
            echo '<script>alert("Transaction failed: ' . $e->getMessage() . '");</script>';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop-Management | Purchase</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body>
    <?php include("./partials/header.php") ?>
    <div class="container mx-auto m-5">
      <div class="row">
        <div class="col-md-12">
           <h1 class="text-center text-secondary display-3 mb-3">Purchase Items</h1>
           <form action="" method="POST">
             <table class="table">
               <h3 class="text-center text-secondary">Supplier's Details</h3>
               <thead>
                  <tr>
                     <th scope="col">Supplier Name</th>
                     <th scope="col">Invoice No.</th>
                     <th scope="col">Invoice Date</th>
                  </tr>
              </thead>
              <tbody>
                  <tr>
                    <td>
                       <select class="form-select" name="supplierId" aria-label="Default select example">
                         <option selected>Select Supplier</option>
                         <?php
                         $sql = "SELECT * FROM supplier";
                         $result = $conn->query($sql);                                  
                         while($row = $result->fetch_assoc()) {
                         $spName = $row['supplier_name'];
                         $spid = $row['id'];
                         echo '                      
                           <option value="'. $spid .'">'. $spName .'</option>
                             ';                                     
                          }
                        ?>
                       </select>
                     </td>
                     <td>
                        <input type="text" name="invoice_no" class="form-control" placeholder="Enter invoice num" required>
                     </td>
                      <td>
                        <input type="date" name="invoice_date" class="form-control" placeholder="select date" required>
                     </td>
                  </tr>
              </tbody>
           </table>
           <br>
           <br>
           <table class="table">
              <h3 class="text-center text-secondary">item's Details</h3>
              <thead>
                <tr>
                    <th scope="col">S.No.</th>
                    <th scope="col">Select Item</th>
                    <th scope="col">Quantity</th>
                    <th scope="col">Price</th>
                    <th scope="col">Amount</th>
                    <th scope="col">Add More</th>
                </tr>
              </thead>
              <tbody id="itemRows">
                <?php $counter = 1; ?>
                   <tr>
                  <th scope="row"><?php echo $counter ?></th>
                  <td>
                    <select class="form-select item-select" name="item_id[]" aria-label="Default select example">
                       <option selected>Select item</option>
                       <?php
                        $sql = "SELECT * FROM items";
                        $result = $conn->query($sql);

                        while ($row = $result->fetch_assoc()) {
                          $id = $row['id'];
                          $name = $row['item_name'];
                          $price = $row['price'];
                          $itemStatus = $row['item_status'];
                          echo '<option value="' . $id . '" data-price="' . $price . '" data-status="' . $itemStatus . '">' . $name . '</option>';
                         }
                       ?>
                   </select>
                 </td>
                 <td>
                    <input type="number" class="form-control quantity" name="quantity[]">
                 </td>
                 <td class="priceCell"></td>
                 <td name="amount" class="amountCell"></td>
                 <td><button type="button" class="btn btn-success btn-sm add-more">Add-More</button></td>
                 <td>
                   <input type="hidden" class="hiddenPrice" name="hidden_price[]">
                   <input type="hidden" class="hiddenAmount" name="hidden_amount[]">
                </td>
             </tr>
               <?php $counter++; ?>
        </tbody>
             <!-- Hidden template row -->
                <tr class="templateRow" style="display: none;">
                            <th scope="row"></th>
                            <td>
                                <select class="form-select item-select" name="item_id[]" aria-label="Default select example">
                                    <option selected value="">Select item</option>
                                    <?php
                                        $sql = "SELECT * FROM items";
                                        $result = $conn->query($sql);

                                    while ($row = $result->fetch_assoc()) {
                                        $id = $row['id'];
                                        $name = $row['item_name'];
                                        $price = $row['price'];
                                        $itemStatus = $row['item_status'];
                                        echo '<option value="' . $id . '" data-price="' . $price . '" data-status="' . $itemStatus . '">' . $name . '</option>';
                                    }
                                    ?>
                                </select>
                            </td>
                            <td>
                                <input type="number" class="form-control quantity" name="quantity[]">
                            </td>
                            <td class="priceCell"></td>
                            <td class="amountCell"></td>
                            <td>
                                <input type="hidden" class="hiddenPrice" name="hidden_price[]">
                                <input type="hidden" class="hiddenAmount" name="hidden_amount[]">
                            </td>
                        </tr>
                    </table>              
                        <input type="hidden" name="status[]" class="hiddenStatus">
                        <button type="submit" class="btn btn-primary">Purchase</button>
                  </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
document.addEventListener('DOMContentLoaded', function () {
    const calculateAmount = (quantity, price, amountCell) => {
        const amount = isNaN(quantity) || isNaN(price) ? '' : (quantity * price).toFixed(2);
        if (amountCell) {
            amountCell.textContent = isNaN(amount) ? '' : amount;
        }
        return amount;
    };

    const updateRow = (row) => {
        const itemSelect = row.querySelector('.item-select');
        const quantityInput = row.querySelector('.quantity');
        const hiddenPrice = row.querySelector('.hiddenPrice');
        const hiddenAmount = row.querySelector('.hiddenAmount');

        if (itemSelect.value !== '' && quantityInput.value !== '') {
            const price = parseFloat(itemSelect.options[itemSelect.selectedIndex].dataset.price);
            const priceCell = row.querySelector('.priceCell');

            const enteredQuantity = parseInt(quantityInput.value);
            priceCell.textContent = isNaN(price) ? '' : price.toFixed(2);
            calculateAmount(enteredQuantity, price, row.querySelector('.amountCell'));

            hiddenPrice.value = price.toFixed(2);
            hiddenAmount.value = calculateAmount(enteredQuantity, price, null);
        }
    };

    const addRemoveButton = (row) => {
        const addMoreBtn = row.querySelector('.add-more');
        if (!addMoreBtn) {
            const buttonContainer = row.querySelector('td:last-child');

            const newRemoveBtn = document.createElement('button');
            newRemoveBtn.type = 'button';
            newRemoveBtn.classList.add('btn', 'btn-danger', 'btn-sm', 'remove-btn');
            newRemoveBtn.textContent = 'Remove';

            buttonContainer.appendChild(newRemoveBtn);

            buttonContainer.addEventListener('click', function (event) {
                if (event.target.classList.contains('remove-btn')) {
                    row.remove();
                }
            });
        }
    };

    const itemRows = document.getElementById('itemRows');

    const addNewRow = () => {
        const templateRow = document.querySelector('.templateRow');
        const clonedRow = templateRow.cloneNode(true);

        const addMoreBtn = clonedRow.querySelector('.add-more');
        if (addMoreBtn) {
            addMoreBtn.remove();
        }

        itemRows.appendChild(clonedRow);
        clonedRow.style.display = '';

        const index = itemRows.childElementCount;
        clonedRow.id = `row${index}`;
        clonedRow.querySelector('.quantity').name = `quantity[]`;
        clonedRow.querySelector('.item-select').name = `item_id[]`;

        clonedRow.querySelector('.quantity').value = '';
        clonedRow.querySelector('.priceCell').textContent = '';
        clonedRow.querySelector('.amountCell').textContent = '';
        clonedRow.querySelector('.hiddenPrice').value = '';
        clonedRow.querySelector('.hiddenAmount').value = '';
        clonedRow.querySelector('.item-select').selectedIndex = 0;

        clonedRow.querySelector('.item-select').addEventListener('change', function () {
            updateRow(clonedRow);
        });

        clonedRow.querySelector('.quantity').addEventListener('input', function () {
            updateRow(clonedRow);
        });

        addRemoveButton(clonedRow);
    };

    const addMoreButton = document.querySelector('.add-more');
    addMoreButton.addEventListener('click', addNewRow);

    document.querySelectorAll('#itemRows tr:not(.templateRow)').forEach(row => {
        addRemoveButton(row);
        row.querySelector('.item-select').addEventListener('change', function () {
            updateRow(row);
        });
        row.querySelector('.quantity').addEventListener('input', function () {
            updateRow(row);
        });
    });

    const supplierSelect = document.querySelector('.form-select');
    const mobileInput = document.querySelector('input[name="mobile_no"]');
    const addressInput = document.querySelector('input[name="address"]');

    const updateSupplierDetails = () => {
        const selectedOption = supplierSelect.options[supplierSelect.selectedIndex];
        if (mobileInput) {
            mobileInput.value = selectedOption.dataset.mobile || '';
        }
        if (addressInput) {
            addressInput.value = selectedOption.dataset.address || '';
        }
    };

    if (supplierSelect) {
        supplierSelect.addEventListener('change', updateSupplierDetails);
    }

    updateSupplierDetails();

    const purchaseForm = document.querySelector('form');
    if (purchaseForm) {
        purchaseForm.addEventListener('submit', function (event) {
            const rows = document.querySelectorAll('#itemRows tr:not(.templateRow)');
            rows.forEach(function (row) {
                const itemSelect = row.querySelector('.item-select');
                const quantityInput = row.querySelector('.quantity');
                const hiddenPrice = row.querySelector('.hiddenPrice');
                const hiddenAmount = row.querySelector('.hiddenAmount');

                if (itemSelect.value !== '' && quantityInput.value !== '') {
                    updateRow(row);
                } else {
                    row.remove();
                }
            });
        });
    }
});
</script>
<script>
    function showPurchaseAlert() {
        alert("Purchase created successfully.");
        setTimeout(function () {
            window.location.href = "./purchaseRecordsDetails.php";
        }, 5000); // Delay for 5000 milliseconds (adjust as needed)
    }
</script>
</body>
</html>
