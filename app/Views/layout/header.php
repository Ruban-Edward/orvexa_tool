<!DOCTYPE html>
<html lang="en">     
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <?= $this->include('layout/headerLinks') ?>
</head>
     
<body>
    <div id="loader">
        <div class="spinner-border text-info" role="status">
            <span class="sr-only">Loading...</span>
        </div>
        <h4 class="text-info mb-0">
            Loading...
        </h4>
    </div>
        <!-- Header -->
        <header>
            <!-- <div class="container-fluid headerback"></div> -->
            <nav class="navbar navbar-expand-lg navbar-light bg-white">

                <div class="container-fluid cls-navbar-links">
                    <a class="navbar-brand" href="<?= ASSERT_PATH ?>dashboard/dashboardView"><img src="<?= ASSERT_PATH ?>assets/images/logo/orvexa_08_monochrome_light.png" alt="Orvexa" style="height: 48px;"></a>
                    
                    <div id="home-icon" style="height: 25px; width: 25px;">
                        <a href="#"><img src="<?= ASSERT_PATH ?>support/compiled/svg/home.svg" alt="home" height="100%" width="100%"></a>
                    </div> 

                    <div class="cls-hamburger" id="hamburger-icon" role="button" tabindex="0" aria-label="Open navigation" aria-expanded="false">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>  
                                 
                    <div id="menu-items" class="cls-menu-items">
                        <div class="menu-item-list">
                            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                                <li class="nav-item">
                                    <div class="container">
                                        <div class="d-flex">
                                            <div class="notification-icon" id="notification-icon">
                                                <h5 style="margin-top: 8px;"><i class="icon-bell"></i></h5>
                                            </div>
                                            <div class="sidebar-right" id="meetingDetails">
                                                <div class="cls-up-arrow"></div>
                                                <div class="cls-meetings">
                                                    <?php include 'meetingDetails.php'; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                <div class="dropdown">
                                    <button class="btn btn-link dropdown-toggle text-white"  type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false" style="display: flex;text-decoration: none;">
                                        <div class="avatar">
                                            <?php $firstName = session()->get('first_name')?>
                                            <?= ucfirst(substr($firstName,0,1))?>
                                        </div>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton">
                                        <li><a class="dropdown-item" href="#">My account</a></li>
                                        <li><a class="dropdown-item" href="#">Settings</a></li>
                                        <li>
                                            <hr class="dropdown-divider">
                                        </li>
                                        <li><a class="dropdown-item" href="<?= ASSERT_PATH ?>logout"><i class="icon-log-out"></i> Sign out</a></li>
                                    </ul>
                                </div>
                            </ul>
                        </div>
                    </div>

            </nav>

        </header>    