<!-- Basics Style for the nav-bar -->
<style>
    .navbar {
        border: 3px solid gray;
        border-radius : 12px;
        background-color: lightgray;
        font-weight: 900;
    }
</style>

<!-- Nav-bar -->
<nav class="navbar navbar-expand-lg mb-3 mr-3">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Inventory Management System</a>
        <button class="navbar-toggler navbar-toggler-right" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" aria-current="page" href="/shop_management/index.php">List of Items</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Records
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="/shop_management/purchaseRecordsDetails.php">Purchase Records</a></li>
                        <li><a class="dropdown-item" href="/shop_management/saleDetailsRecords.php">Sale Records</a></li>
                        <li><a class="dropdown-item" href="/shop_management/supplier.php">Suppliers</a></li>
                        <li><hr class="dropdown-divider"></hr></li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Reports
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="/shop_management/reports/saleReport.php">Sale Reports</a></li>
                        <li><a class="dropdown-item" href="/shop_management/reports/purchaseReport.php">Purchase Reports</a></li>
                        <li><a class="dropdown-item" href="/shop_management/stocks.php">Stock Report</a></li>
                        <li><hr class="dropdown-divider"></hr></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/shop_management/purchase_details.php">Purchase Items</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/shop_management/sale_details.php">Sale Items</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
