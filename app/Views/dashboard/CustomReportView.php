<!-- 
    Author: T. Siva Teja
    Email: thotasivateja57@gmail.com
    Date: 8 July 2024
    Purpose: View page for report page
-->
<div class="main-report">
    <script>
        const BASE_URL = "<?= ASSERT_PATH ?>";
        let data = <?php echo json_encode($data['data']); ?>;
    </script>
    <!-- Example in your_view.php -->
    <?php if (isset($data["message"]) && !empty($data["message"])): ?>
        <div data-message="true" id="ale" class="alert alert-danger" style="display:none;">
            <?= $data["message"] ?>
        </div>
    <?php endif ?>
    <!-- Alert Box for Notifications -->
    <div id="notify" class="alert alert-success" role="alert"></div>

    <!-- Custom Report Header -->

    <!-- Filter and Download Options -->
    <div class="filter-container mb-4">
        <button type="button" class="btn-outline button-secondary" onclick="downloadbtn()" data-bs-toggle='modal'
            data-bs-target='#teamModal' class="button" id="downloadBtn">
            <i class="icon-download"> </i> Download
        </button>


        <button class="btn-outline button-secondary" data-param="<?= $data['report'][0] ?>" type="button"
            class="button ms-2" id="filterBtn">
            <i class="icon-filter"> </i> Filters
            <span id="noti" class="badge badge-pill badge-danger"></span>

        </button>
    </div>


    <!-- Download Modal -->
    <div class="modal fade" id="teamModal" tabindex="-1" aria-labelledby="teamModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="teamModalLabel">Download Report</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div id="team" class="modal-body">
                    <p>Are you sure you want to download the team report?</p>
                </div>
                <div id="modal-footer" class="modal-footer mb-3">
                    <button type="button" class="btn primary_button" data-bs-dismiss="modal">No</button>
                    <?php $report = $data['report'][0] ?>

                    <button id="ok" onclick="downloadFilterReport('<?= $report ?>')" type="submit"
                        data-bs-dismiss="modal" class="btn primary_button">Yes</button>
                    <!-- </form> -->
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Sidebar -->
    <div id="filterSidebar" class="sidebar">
        <form method="post" id="filterForm">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="filter-heading">Filter</h3>
                <button type="button" id='closeBtn' class="btn-close" aria-label="Close filters"></button>
            </div>
            <div id="date">
                <!-- Date Filters -->
                <div class="mb-3">
                    <label for="fromDate" class="form-label filter-text">From Date:</label>
                    <input type="date" name="fromdate" class="dates form-control mb-3 flatpickr-no-config" id="fromDate"
                        placeholder="Select Date">
                </div>
                <div class="mb-3">
                    <label for="toDate" class="form-label filter-text">To Date:</label>
                    <input type="date" name="todate" class="dates form-control mb-3 flatpickr-no-config" id="toDate"
                        placeholder="Select Date">
                </div>
            </div>


            <!-- Product Filter -->
            <div class="mb-3">
                <label for="product" class="form-label filter-text">Product:</label>
                <select name="product[]" class="form-select select" multiple multiselect-search="true"
                    placeholder="Select Product" multiselect-select-all="true" multiselect-max-items="1">
                    <?php foreach ($data['drops']['product_result'] as $product): ?>
                        <option><?= $product['product_name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Meeting Filters -->
            <div id="meetdrops" class="mb-3">
                <label for="meettype" class="form-label filter-text">Meet Type:</label>
                <select name="meettype[]" class="form-select select" multiple multiselect-search="true"
                    placeholder="Select Meet Type" multiselect-select-all="true" multiselect-max-items="1">
                    <?php foreach ($data['drops']['meeting_type_result'] as $meetType): ?>
                        <option><?= $meetType['meeting_type_name'] ?></option>
                    <?php endforeach; ?>
                </select>
                <label for="creator" class="form-label filter-text">Creator:</label>
                <select name="creator[]" class="form-select select" placeholder="Select Creator" multiple
                    multiselect-search="true" multiselect-select-all="true" multiselect-max-items="1">
                    <?php foreach ($data['drops']['user_result'] as $user): ?>
                        <option><?= $user['first_name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Sprint and Backlog Common Filters -->
            <div id="sprintbacklogdrops" class="mb-3">
                <label for="customer" class="form-label filter-text">Customer:</label>
                <select name="customer[]" id="customer" class="form-select select" placeholder="Select Customer"
                    multiple multiselect-search="true" multiselect-select-all="true" multiselect-max-items="1">
                    <?php foreach ($data['drops']['customer_name_result'] as $customer): ?>
                        <option><?= $customer['customer_name'] ?></option>
                    <?php endforeach; ?>
                </select>
                <label for="status" class="form-label filter-text">Status:</label>
                <select name="status[]" id="sprintstatus" class="form-select select" placeholder="Select Status"
                    multiple multiselect-search="true" multiselect-select-all="true" multiselect-max-items="1">
                    <?php foreach ($data['drops']['status_result'] as $status): ?>
                        <option><?= $status['status_name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Sprint Filters -->
            <div id="sprint" class="mb-3">
                <label for="sprintname" class="form-label filter-text">Sprint Name:</label>
                <select name="sprintname[]" id="sprintname" class="form-select select"
                    multiple multiselect-search="true" multiselect-select-all="true" multiselect-max-items="1">
                    <?php foreach ($data['drops']['sprint_name_result'] as $sprintName): ?>
                        <option><?= $sprintName['sprint_name'] ?></option>
                    <?php endforeach; ?>
                </select>
                <label for="sprintversion" class="form-label filter-text">Version:</label>
                <select name="version[]" placeholder="Select Version" id="sprintversion" class="form-select select"
                    multiple multiselect-search="true" multiselect-select-all="true" multiselect-max-items="1">
                    <?php foreach ($data['drops']['sprint_version_result'] as $version): ?>
                        <option><?= $version['sprint_version'] ?></option>
                    <?php endforeach; ?>
                </select>
                <label for="duration" class="form-label filter-text">Duration:</label>
                <select name="duration[]" id="duration" placeholder="Select Duration" class="form-select select"
                    multiple multiselect-search="true" multiselect-select-all="true" multiselect-max-items="1">
                    <?php foreach ($data['drops']['sprint_duration_result'] as $duration): ?>
                        <option><?= $duration['sprint_duration_value'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Backlog Filters -->
            <div id="backlog" class="mb-3">
                <label for="trackername" class="form-label filter-text">Tracker:</label>
                <select placeholder="Select Tracker" name="trackername[]" id="trackername" class="form-select select"
                    multiple multiselect-search="true" multiselect-select-all="true" multiselect-max-items="1">
                    <?php foreach ($data['drops']['backlog_item_result'] as $backlogName): ?>
                        <option><?= $backlogName['tracker'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="d-flex justify-content-between mt-4 mb-4">
                <button type="submit" data-report="<?= $data["report"][0] ?>" id="applyfilter"
                    class="btn primary_button">Apply</button>
                <button id="resetBtn" type="button" class="button-secondary">Reset</button>
            </div>
        </form>
    </div>

    <!-- Table Content -->
    <div id="customreport" class="row">
        <table id="priority-table" class="table table-borderless custom-table">
            <thead id="tableHeader" class="header_color">
                <!-- Table headers will be inserted here -->
            </thead>
            <tbody id="tableBody">
                <!-- Table rows will be inserted here -->
            </tbody>
        </table>
        <div id="paginationControls" class="d-flex justify-content-between align-items-center mt-3">
            <button id="prevPage" class="btn-outline button-secondary">Prev</button>
            <span id="pageInfo" class="mx-3"></span>
            <button id="nextPage" class="btn-outline button-secondary">Next</button>
        </div>
    </div>



</div>

<script>
    const customReportView = "customReportView";
</script>