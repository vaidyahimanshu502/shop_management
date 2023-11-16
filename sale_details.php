<?php
include("./config/db_connect.php");

function generateInvoiceNumber()
{
    $prefix = 'INV';
    $datePart = date('Ymd');
    $randomPart = mt_rand(1000, 9999);
    $invoiceNumber = $prefix . $datePart . $randomPart;
    return $invoiceNumber;
}

$invoiceNo = generateInvoiceNumber();

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Current date and time
    $currentDateTime = date('Y-m-d H:i:s');

    // Inputs for the sale table
    $customerName = isset($_POST['customer_name']) ? $_POST['customer_name'] : '';
    $customerContact = isset($_POST['mob_no']) ? $_POST['mob_no'] : '';
    $status = 1;
    $invoiceDate = date('Y-m-d');
    $totalAmount = 0;

    // Validation for customer name
    if (!preg_match("/^[a-zA-Z ]*$/", $customerName)) {
        $response = array('success' => false, 'message' => 'Customer name should only contain letters and spaces.');
        echo json_encode($response);
        exit;
    }

    // Validation for mobile number
    if (!preg_match("/^[0-9]{10}$/", $customerContact)) {
        $response = array('success' => false, 'message' => 'Mobile number should be a 10-digit number.');
        echo json_encode($response);
        exit;
    }

    // Validate inputs
    if (empty($customerName) || empty($customerContact)) {
        $response = array('success' => false, 'message' => 'Customer name and mobile number are required.');
        echo json_encode($response);
        exit;
    } else {
        // Calculate total amount and prepare sale details array
        $saleDetails = array();
        foreach ($_POST['quantity'] as $key => $quantity) {
            $item_id = isset($_POST['item_id'][$key]) ? $_POST['item_id'][$key] : '';
            $price = isset($_POST['hidden_price'][$key]) ? $_POST['hidden_price'][$key] : '';
            $amount = isset($_POST['hidden_amount'][$key]) ? $_POST['hidden_amount'][$key] : '';

            if ($quantity > 0 && $price > 0 && $amount > 0 && !empty($item_id)) {
                $totalAmount += $amount;
                $saleDetails[] = array(
                    'item_id' => $item_id,
                    'quantity' => $quantity,
                    'price' => $price,
                    'amount' => $amount
                );
            }
        }

        // Insert data into the sale table
        $stmtSale = $conn->prepare("INSERT INTO sale (customer_name, mob_no, invoice_no, invoice_date, total_amount, created_at, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmtSale->bind_param("sissisi", $customerName, $customerContact, $invoiceNo, $invoiceDate, $totalAmount, $currentDateTime, $status);

        if ($stmtSale->execute()) {
            // Retrieve the last inserted sale ID
            $saleID = $conn->insert_id;

            // Process each item in the sale details
            foreach ($saleDetails as $saleDetail) {
                $item_id = $saleDetail['item_id'];
                $quantity = $saleDetail['quantity'];
                $price = $saleDetail['price'];
                $amount = $saleDetail['amount'];
                $status = 1;

                // Check if item_id exists in the items table
                $checkItemSql = "SELECT * FROM items WHERE id = ?";
                $checkItemStmt = $conn->prepare($checkItemSql);
                $checkItemStmt->bind_param("i", $item_id);
                $checkItemStmt->execute();
                $checkItemResult = $checkItemStmt->get_result();

                if ($checkItemResult->num_rows == 0) {
                    $response = array('success' => false, 'message' => "Item with ID $item_id does not exist.");
                    echo json_encode($response);
                    exit;
                }

                // Insert data into the sale_details table
                $stmtSaleDetails = $conn->prepare("INSERT INTO sale_details (item_id, qty, price, amount, sale_id, created_at, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmtSaleDetails->bind_param("iiidisi", $item_id, $quantity, $price, $amount, $saleID, $currentDateTime, $status);

                if (!$stmtSaleDetails->execute()) {
                    $response = array('success' => false, 'message' => 'Error in creating sale details: ' . $stmtSaleDetails->error);
                    echo json_encode($response);
                    exit;
                }
            }

            // Update the total amount in the sale table
            $stmtUpdateTotalAmount = $conn->prepare("UPDATE sale SET total_amount = ? WHERE id = ?");
            $stmtUpdateTotalAmount->bind_param("ii", $totalAmount, $saleID);

            if (!$stmtUpdateTotalAmount->execute()) {
                $response = array('success' => false, 'message' => 'Error in updating total amount: ' . $stmtUpdateTotalAmount->error);
                echo json_encode($response);
                exit;
            }

            $stmtSale->close();

            // Return a JSON response indicating success
            $response = array('success' => true, 'message' => 'Sale created successfully.');
            echo json_encode($response);
            exit;
        } else {
            // Return a JSON response indicating failure
            $response = array('success' => false, 'message' => 'Error in creating sale: ' . $stmtSale->error);
            echo json_encode($response);
            exit;
        }
        $stmtSale->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop-Management | Sale</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>

<body>
    <?php include("./partials/header.php") ?>
    <div class="container mx-auto m-5">
        <div class="row">
            <div class="col-md-12">
                <h1 class="text-center text-secondary display-3 mb-3">Sale Items</h1>
                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
                    <table class="table">
                        <h3 class="text-center text-secondary">Customer's Details</h3>
                        <thead>
                            <tr>
                                <th scope="col">Customer Name</th>
                                <th scope="col">Customer Mobile</th>
                                <th scope="col">Invoice No.</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <input type="text" name="customer_name" class="form-control" placeholder="cus-name..." required>
                                </td>
                                <td>
                                    <input type="text" name="mob_no" class="form-control" placeholder="cus-mobile..." required>
                                </td>
                                <td>
                                    <input type="text" name="invoice_no" class="form-control" value="<?php echo $invoiceNo; ?>" readonly>
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
                                <th scope="col">Stocks</th>
                                <th scope="col">Add More</th>
                            </tr>
                        </thead>

                        <tbody id="itemRows">
                            <?php $counter = 1; ?>
                            <tr>
                                <th scope="row"><?php echo $counter ?></th>
                                <td>
                                    <select class="form-select item-select" name="item_id[]" aria-label="Default select example">
                                        <option value="" selected>Select item</option>
                                        <?php
                                      $sql = "SELECT * FROM items";
                                      $result = $conn->query($sql);
                                      
                                      if (!$result) {
                                          die("Query failed: " . $conn->error);
                                      }
                                      
                                      while ($row = $result->fetch_assoc()) {
                                          // Output for debugging
                                          var_dump($row);
                                      
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
                                <td class="stockCell"></td>
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
                            <td class="stockCell"></td>
                            <td>
                                <input type="hidden" class="hiddenPrice" name="hidden_price[]">
                                <input type="hidden" class="hiddenAmount" name="hidden_amount[]">
                            </td>
                        </tr>
                    </table>              
                        <!-- <input type="hidden" name="price[]" class="hiddenPrice"> -->
                        <input type="hidden" name="status[]" class="hiddenStatus">
                        <!-- <input type="hidden" name="amount[]" class="hiddenAmount"> -->
                        <button type="submit" class="btn btn-primary">Sale</button>
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

    const updateStockCell = async (itemSelect, stockCell) => {
    const itemId = itemSelect.value;

    console.log('Item ID:', itemId);

    if (itemId !== '') {
        try {
            const response = await fetch(`get_available_stock.php?id=${itemId}`);
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }

            const responseData = await response.text();
            console.log('Raw Response:', responseData);

            // Check if the response starts with '{' (JSON) and trim any leading whitespace
            if (responseData.trim().startsWith('{')) {
                const data = JSON.parse(responseData);

                if (data.success) {
                    stockCell.textContent = data.available_stock;
                } else {
                    console.error(`Error: ${data.message}`);
                    stockCell.textContent = 'Error fetching stock';
                }
            } else {
                console.error('Invalid JSON response:', responseData);
                stockCell.textContent = 'Error fetching stock';
            }
        } catch (error) {
            console.error('Error fetching stock:', error);
            stockCell.textContent = 'Error fetching stock';
        }
    } else {
        stockCell.textContent = '';
    }
};

    const updateHiddenFields = (itemSelect, quantityInput, hiddenPrice, hiddenAmount) => {
        const price = parseFloat(itemSelect.options[itemSelect.selectedIndex].dataset.price);
        const enteredQuantity = parseInt(quantityInput.value);

        hiddenPrice.value = price.toFixed(2);
        hiddenAmount.value = calculateAmount(enteredQuantity, price, null);
    };

    const updateRow = (row) => {
        const itemSelect = row.querySelector('.item-select');
        const quantityInput = row.querySelector('.quantity');
        const hiddenPrice = row.querySelector('.hiddenPrice');
        const hiddenAmount = row.querySelector('.hiddenAmount');
        const priceCell = row.querySelector('.priceCell');
        const amountCell = row.querySelector('.amountCell');
        const stockCell = row.querySelector('.stockCell');

        if (itemSelect.value !== '' && quantityInput.value !== '') {
            const price = parseFloat(itemSelect.options[itemSelect.selectedIndex].dataset.price);
            const enteredQuantity = parseInt(quantityInput.value);
            const availableStock = parseInt(stockCell.textContent);

            if (enteredQuantity > availableStock) {
                alert('Please enter a valid quantity. It is greater than the available stock.');
                quantityInput.value = '';
                return;
            }

            priceCell.textContent = isNaN(price) ? '' : price.toFixed(2);
            amountCell.textContent = calculateAmount(enteredQuantity, price, amountCell);

            updateHiddenFields(itemSelect, quantityInput, hiddenPrice, hiddenAmount);
            updateStockCell(itemSelect, stockCell);
        } else {
            priceCell.textContent = '';
            amountCell.textContent = '';
            hiddenPrice.value = '';
            hiddenAmount.value = '';
            stockCell.textContent = '';
        }
    };

    const itemRows = document.getElementById('itemRows');
    itemRows.addEventListener('change', function (event) {
        const target = event.target;
        if (target.classList.contains('item-select')) {
            updateRow(target.closest('tr'));
        }
    });

    itemRows.addEventListener('input', function (event) {
        const target = event.target;
        if (target.classList.contains('quantity')) {
            updateRow(target.closest('tr'));
        }
    });

    const addMoreButton = document.querySelector('.add-more');
    addMoreButton.addEventListener('click', function () {
        const templateRow = document.querySelector('.templateRow');
        const clonedRow = templateRow.cloneNode(true);

        const addMoreBtn = clonedRow.querySelector('.add-more');
        if (addMoreBtn) {
            addMoreBtn.remove();
        }

        const removeButton = document.createElement('button');
        removeButton.className = 'btn btn-danger btn-sm remove-item';
        removeButton.textContent = 'Remove';
        clonedRow.querySelector('td:last-child').appendChild(removeButton);

        itemRows.appendChild(clonedRow);
        clonedRow.style.display = '';

        const index = itemRows.childElementCount;
        clonedRow.id = `row${index}`;
        clonedRow.querySelector('.quantity').name = `quantity[]`;
        clonedRow.querySelector('.item-select').name = `item_id[]`;
        clonedRow.querySelector('.hiddenPrice').name = `hidden_price[]`;
        clonedRow.querySelector('.hiddenAmount').name = `hidden_amount[]`;

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

        updateRemoveButton(clonedRow);
    });

    const purchaseForm = document.querySelector('form');
    purchaseForm.addEventListener('submit', async function (event) {
        event.preventDefault();

        const rows = document.querySelectorAll('#itemRows tr:not(.templateRow)');
        for (const row of rows) {
            const itemSelect = row.querySelector('.item-select');
            const quantityInput = row.querySelector('.quantity');
            const hiddenPrice = row.querySelector('.hiddenPrice');
            const hiddenAmount = row.querySelector('.hiddenAmount');

            if (itemSelect.value !== '' && quantityInput.value !== '') {
                updateHiddenFields(itemSelect, quantityInput, hiddenPrice, hiddenAmount);
            } else {
                row.remove();
            }
        }

        const formData = new FormData(purchaseForm);
        try {
            const response = await fetch(window.location.href, {
                method: 'POST',
                body: formData
            });

            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }

            const result = await response.json();
            if (result.success) {
                alert('Sale created successfully.');
                window.location.href = './saleDetailsRecords.php';
            } else {
                alert(`Error in creating sale: ${result.message}`);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error submitting the form.');
        }
    });

    const updateRemoveButton = (row) => {
        const removeButton = row.querySelector('.remove-item');
        removeButton.addEventListener('click', function () {
            row.remove();
        });
    };

    const allItemSelects = document.querySelectorAll('.item-select');
    allItemSelects.forEach(itemSelect => {
        const stockCell = itemSelect.closest('tr').querySelector('.stockCell');
        updateStockCell(itemSelect, stockCell);
    });
});
</script>
</body>
</html>