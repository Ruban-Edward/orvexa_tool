<div class="header d-flex justify-content-between align-item-center">
    <div class="heading">
        <h4>Backlog item: <?= ucfirst($data['backlog_item_name']) ?></h4>
    </div>
    <div class="mt-2">
        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="header-links" ><a href="<?= ASSERT_PATH ?>/backlog/userstories?pid=<?= $data['pId'] ?>&pblid=<?= $data['pblId'] ?>" data-toggle="tooltip" placement="top" title="Click to view the user story">View user stories </a></li>
            <li class="nav-item" role="presentation">
                <button class="button-secondary active" id="details-tab" data-bs-toggle="tab" data-bs-target="#details" type="button" role="tab" aria-controls="details" aria-selected="true">Details</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="button-secondary" id="history-tab" data-bs-toggle="tab" data-bs-target="#history" type="button" role="tab" aria-controls="history" aria-selected="false">History</button>
            </li>
        </ul>
        </ul>
    </div>
</div>
<div class="tab-content" id="myTabContent">
    <div class="tab-pane fade show active" id="details" role="tabpanel" aria-labelledby="details-tab">
        <div class="row row-content">
            <div class="col-sm-3 d-flex align-items-center h5">
                <p>Product name:</p>
            </div>
            <div class="col-sm-3 d-flex align-items-center fs-6">
                <p><?= ucfirst($data['product_name']) ?></p>
            </div>
            <div class="col-sm-4 d-flex align-items-center h5">
                <p>Customer:</p>
            </div>
            <div class="col-sm-2 d-flex align-items-center fs-6">
                <p><?= $data['customer_name'] ?></p>
            </div>
        </div>
        <hr class="my-1">
        <div class="row row-content">
            <div class="col-sm-3 d-flex align-items-center h5">
                <p>Status:</p>
            </div>
            <div class="col-sm-3 d-flex align-items-center fs-6">
                <p><?= $data['status_name'] ?></p>
            </div>
            <div class="col-sm-4 d-flex align-items-center h5">
                <p>Type:</p>
            </div>
            <div class="col-sm-2 d-flex align-items-center fs-6">
                <p><?= $data['tracker'] ?></p>
            </div>
        </div>
        <hr class="my-1">
        <div class="row row-content">
            <div class="col-sm-3 d-flex align-items-centert h5">
                <p>Priority order:</p>
            </div>
            <div class="col-sm-3 d-flex align-items-center fs-6">
                <p><?= $data['backlog_order'] ?></p>
            </div>
            <div class="col-sm-4 d-flex align-items-center h5">
                <p>Priority:</p>
            </div>
            <div class="col-sm-2 d-flex align-items-center fs-6">
                <?php
                $priority = '';
                if (strtolower($data['priority']) == 'l') {
                    $priority = 'Low';
                } else if (strtolower($data['priority']) == 'm') {
                    $priority = 'Medium';
                } else {
                    $priority = 'High';
                } ?>
                <p><?= $priority ?></p>
            </div>
        </div>
        <hr class="my-1">
        <div class="row row-content">
            <div class="col-sm-3 d-flex align-items-center h5">
                <p>No of user stories:</p>
            </div>
            <div class="col-sm-3 d-flex align-items-center fs-6">
                <p><?= $data['total_user_stories']?></p>
            </div>
            <div class="col-sm-4 d-flex align-items-center h5">
                <p>No of completed user stories:</p>
            </div>
            <div class="col-sm-2 d-flex align-items-center fs-6">
                <p><?= $data['completed_user_stories'] ?></p>
            </div>
        </div>
        <hr class="my-1">
        <div class="row row-content">
            <div class="col-sm-3 d-flex align-items-center h5">
                <p>No of tasks:</p>
            </div>
            <div class="col-sm-3 d-flex align-items-center fs-6">
                <p><?= $data['total_tasks'] ?></p>
            </div>
            <div class="col-sm-4 d-flex align-items-center h5">
                <p>No of completed tasks:</p>
            </div>
            <div class="col-sm-2 d-flex align-items-center fs-6">
                <p><?= $data['completed_tasks'] ?></p>
            </div>
        </div>
        <hr class="my-1">
        <div class="row row-content">
            <div class="col-sm-3 d-flex align-items-center h5">
                <p>Description:</p>
            </div>
            <div class="col-sm-9 d-flex align-items-center fs-6">
                <p><?= $data['backlog_description'] ?></p>
            </div>
        </div>
        <hr class="my-1">
        <div class="row row-content">
            <div class="col-sm-3 d-flex align-items-center h5">
                <p>Attachments:</p>

            </div>

            <?php if (count($data['document']) > 0) : ?>
                <ul style="list-style: none;">
                    <li>
                        <?php foreach ($data['document'] as $value) : ?>
                            <?php $absolute_path = $value['document_path'];
                            $project_root = str_replace('\\', '/', FCPATH);
                            $relative_path = str_replace($project_root, '', str_replace('\\', '/', $absolute_path)); ?>
                            <div class="row">
                                <div class="col-sm-2 h3"><span class="fs-6"><?= ucfirst($value['document_type']) ?></span></div>
                                <div class="col-sm-4 h3 d-flex flex-column justify-content-end">
                                    <div class="fs-6 file-name"><?= ucfirst($value['document_name']) ?></div>
                                </div>
                                <div class="col-sm-1 d-flex align-items-center">
                                    <a href="<?= site_url('backlog/documents/view/' . urlencode(basename($value['document_path']))) ?>" target="_blank" class="ms-2"><i class="bi bi-eye" data-toggle="tooltip" data-placement="top" title="view"></i></a>
                                    <a href="<?= site_url('backlog/documents/download/' . urlencode(basename($value['document_path']))) ?>" class="ms-2"><i class="bi bi-download" data-toggle="tooltip" data-placement="top" title="download"></i></a>
                                    <a href="#" class="ms-2"onclick="deleteDocument(<?= $value['document_id']?>)"><i class="bi bi-trash" data-toggle="tooltip" data-placement="top" title="delete"></i></a>
                                </div>
                                <div class="col-sm-5  d-flex align-items-center">
                                    <?= "Added by " . strtolower($value['first_name']) . " " . $value['last_name'] . " " . $value['created_date'] ?>
                                </div>
                            </div>
                        <?php endforeach ?>
                    </li>
                </ul>
            <?php else : ?>
                <div class="row">
                    <div class="col sm-12 text-center">
                        <h4 class="no-document">No documents attached</h4>
                    </div>
                </div>
            <?php endif ?>
        </div>
    </div>
    <div class="tab-pane fade" id="history" role="tabpanel" aria-labelledby="history-tab">
        <div> 
             <h4 class="history-heading">Backlog history:</h4>
        </div>
       
        <div class="cls-filterHeaderContainer">
            <div class="page">
                <div class="date-filter" style="display:flex;">
                    <label for="" style="margin-top: 7px;color:#117a45;font-weight:bold">Start Date:</label>
                    <input type="text" class="form-control mb-3 flatpickr-no-config flatpickr-input" id="startDate" name="startDate" accept="" placeholder="YYYY-MM-DD" readonly="readonly" required style="margin: 0px 10px;width: auto;">
                    <label for="" style="margin-top: 7px;color:#117a45;font-weight:bold">End Date:</label>
                    <input type="text" class="form-control mb-3 flatpickr-no-config flatpickr-input" id="endDate" name="startDate" accept="" placeholder="YYYY-MM-DD" readonly="readonly" required style="margin: 0px 10px;width: auto;">
                    <button id="filterButton" class="btn primary_button" style="
                        height: 37px;
                        width: 75px;
                        ">Filter</button>
                    <button id="resetButton" class="button-secondary" style="
                        height: 37px;
                        width: 79px;
                        margin-left: 5px;
                        ">Reset</button>
                </div>
                <div class="scrollable">
                <ul class="data-list" id="dataList"></ul>
                </div>
                <div class="pagination d-flex justify-content-between">
                    <button id="previous" class="pagination-button button-secondary">Previous</button>
                    <button id="next" class="pagination-button button-secondary" >Next</button>
                </div>
            </div>
        </div>
    </div>
</div>


<script>
    const assert_path = "<?= ASSERT_PATH ?>";
    const pId = <?= json_encode($data['pId']); ?>;
    const pblId = <?= json_encode($data['pblId']); ?>;

    const m_backlogItemDetails = [];
</script>