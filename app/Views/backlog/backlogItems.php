
<div class="row ">
        <ul class="col heading">
            <li><h5 class="cls-product-head-name"><span class="h6">Product name : </span><?= trim(ucfirst(strtolower($data['product_name']))); ?></h5></li>
            <span class="bar">
        </ul>
</div>


<div class="d-flex justify-content-between cls-backlog-action mb-2">
  <div class="d-flex cls-backlog-left-side-buttons">
      <div class="cls-action-button">
        <?php if(has_permission('backlog/addbacklog')): ?>
        
          <button id="backlog" class="btn primary_button" data-bs-toggle="modal" data-bs-target="#backlogModal" onclick="return addbacklog()">
            <i class="icon-plus-circle sprintListIcon"></i> Add backlog
          </button>
        
        <?php endif;?>
      </div>

      <div id="refinement" class="cls-action-button">
        <?php if(has_permission('backlog/refinement')): ?>
          
          <a href="<?= ASSERT_PATH . "backlog/refinement?pid=" . $data['p_id']; ?>">
            <button class="btn primary_button"><i class="fas fa-retweet"></i> Refinement</button>
          </a>
        
        <?php endif;?>
      </div>

  </div>
  
  <?php if(count($data['backlogItemDetails'])>0):?>
  <div class="cls-backlog-right-side-buttons">
        <ul class="header_button">

          <li class="me-2">
            <div class="dropdown">
              <div class="search-container">
                <div class="search-box">
                  <input type="text" id="backlogSearchInput" class="search-input" placeholder="Search backlog">
                  <button id="search_btn" class="search-button">
                    <i class="bi bi-search"></i>
                    <span class="cls-action-name">Search</span>
                  </button>
                </div>
              </div>
          </li>

          <li class="me-2">
            <div class="dropdown">
            <button type="button" class="button-secondary" data-bs-toggle="modal" data-bs-target="#sprintHistory"
                id="backloghistory">
                <i class="icon-file-text"></i> 
                <span class="cls-action-name">Backlog history</span>
              </button>
            </div>
          </li>

          <li>
            <div class="dropdown ">
              <button id="filter_btn" class="button-secondary d-flex align-items-center justify-content-center">
                <i class="icon-filter me-1"></i> 
                <span class="cls-action-name">Filter</span>
                <span id="noti" class="badge badge-pill badge-danger"></span>
              </button>
            </div>

          </li>
        </ul>
  </div>
  <?php endif;?>
  
</div>

<div class="modal fade" id="backlogModal" tabindex="-1" aria-labelledby="backlogModalLabel" aria-hidden="true" data-bs-backdrop="static">
  <div id="size" class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTitle">Add backlog items</h5>
        <button type="button" class="btn-closes" data-bs-dismiss="modal" aria-label="Close">&times;</button>
      </div>
      <div class="modal-body form-container">
        <form class="row g-3 needs-validation" id="addbacklogform" method="POST" enctype="multipart/form-data" novalidate>
          <?= csrf_field() ?>
          <input type="hidden" id="addBackLog" name="addBackLog" value="insert">
          <input type="hidden" id="pId" name="pId" value="<?=$data['p_id']?>">
          <div class="col-md-6">
            <label for="productname" class="form-label form-need">Product name</label>
            <input type="text" class="form-control" name="productname" id="productname" value="<?= trim(ucfirst(strtolower($data['product_name']))); ?>" autocomplete="off" readonly>
          </div>
          <div class="col-md-6">

          </div>
          <div class="col-md-6">
            <label for="backlogitemname" class="form-label form-need">Backlog item name</label>
            <input type="text" class="form-control" name="backlog_item_name" placeholder="Enter the backlog item name" id="backlogitemname" autocomplete="off" required>
            <div class="valid-feedback">Looks good!</div>
            <div class="invalid-feedback">Enter Backlog Item</div>
          </div>
          <div class="col-md-6">
            <label for="priority" class="form-label">Priority</label>
            <select class="form-select" name="priority" id="priority" required>
              <option value="L">Low</option>
              <option value="M">Medium</option>
              <option value="H">High</option>
            </select>
            <div class="valid-feedback">Looks good!</div>
            <div class="invalid-feedback">Choose priority</div>
          </div>


          <div class="col-md-6">
            <label for="backlogitemtype" class="form-label form-need">Backlog item type</label>
            <select class="form-select" name="r_tracker_id" id="backlogitemtype" required>
              <option value="">Select backlog item type</option>
              <?php foreach ($data['tracker'] as $type) : ?>
                <option value="<?= $type['tracker_id']; ?>"><?= ucwords($type['tracker']); ?></option>
              <?php endforeach; ?>
            </select>
            <div class="valid-feedback">Looks good!</div>
            <div class="invalid-feedback">Enter backlog item type</div>
          </div>
          <div class="col-md-6">
            <label for="backlogitemcustomer" class="form-label form-need">Customer</label>
            <select class="form-select" name="r_customer_id" id="backlogitemcustomer" required>
              <option value="">Select customer</option>
              <?php foreach ($data['backlog_item_customer'] as $customer) : ?>
                <option value="<?= $customer['customer_id']; ?>"><?= ucwords($customer['customer_name']); ?></option>
              <?php endforeach; ?>
            </select>
            <div class="invalid-feedback">Choose customer</div>
            <div class="valid-feedback">Looks good!</div>
          </div>
          <div class="col-md-6">
            <label for="backlogitemstatus" class="form-label form-need">Status</label>
            <select class="form-select" id="backlogitemstatus" name="r_module_status_id" required>
              <?php foreach ($data['backlog_item_status'] as $status) : ?>
                <option value="<?= $status['status_id']; ?>"><?= ucwords($status['status_name']); ?></option>
              <?php endforeach; ?>
            </select>
            <div class="valid-feedback">Looks good!</div>
            <div class="invalid-feedback">Choose backlog item status</div>
          </div>
          <div class="col-md-6">
            <label for="tshirtsize" class="form-label form-need">T-shirt size</label>
            <select class="form-select" name="backlog_t_shirt_size" id="tshirtsize" required>
              <option value="">Select T-shirt size</option>
              <?php foreach ($data['t_shirt_size'] as $key => $value) : ?>
                <option value="<?= $value['t_shirt_size_id']; ?>"><?= ucfirst($value['t_size_name'])." ".$value['t_size_values']; ?></option>
              <?php endforeach; ?>
            </select>
            <div class="valid-feedback">Looks good!</div>
            <div class="invalid-feedback">Enter t-shirt size</div>
          </div>

          <div class="col-md-12">
            <label for="description" class="form-label form-need">Description</label>
            <textarea class="form-control" name="backlog_description" id="description" placeholder="Description" required></textarea>
            <div class="valid-feedback">Looks good!</div>
            <div class="invalid-feedback">Enter description</div>
          </div>
          <div id="fileUploadContainer"></div>
          <div class="d-flex justify-content-end">
             <a href="#" class="mt-3" id="addFilesButton" onclick="addFileInput()" style="width: 100px;"><i class="bi bi-file-plus"></i> Add files</a>
          </div>
          <ul id="fileList" class="list-group mt-3"></ul>

          <div class="modal-footer d-flex justify-content-center">
            <button type="submit" class="btn primary_button" name="addbacklogitem" id="submitBtn">Submit</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<div class="main-page" id="mainPage">
  <div class="page">
    <div class="row">
                
    </div>
    <div class="row">
      <table id="priority-table" class="table table-borderless custom-table">
        <thead id="tableHeader" class="header_color">
          <!-- Table headers will be inserted here -->
        </thead>
        <tbody id="tableBody">
          <!-- Table rows will be inserted here -->
        </tbody>
      </table>
      <!-- Pagination Controls -->
      <div id="paginationControls" class="d-flex justify-content-between align-items-center mt-3">
        <button id="prevPage" class="button-secondary">Prev</button>
        <span id="pageInfo" class="mx-3"></span>
        <button id="nextPage" class="button-secondary">Next</button>
      </div>
    </div>
  </div>
</div>

<div class="empty-content" id="empty">
  <div class="row mt-5 text-center cls-norecFound">
    <span class="bi bi-emoji-frown cls-noProductIcon"></span>
    <h2>No backlog items found</h2>
  </div>
</div>


<!-- Sidebar for filter -->
<div id="filterSidebar" class="filter-sidebar">
  <div class="sidebar-content">
    <div class="d-flex justify-content-between align-items-center mb-2">
      <h4 class="filter-heading">Filter</h4>
      <button type="button" id="closeFilterSidebarBtn" class=" btn-filterclose align-item-center"></button>
    </div>
    <form id="filterOptionsForm">
      <div class="mb-3">
        <label for="filterBacklogType" class="form-label filter-text">Backlog item type</label>
        <!-- <select id="filterBacklogType" class="form-select"> -->
        <select name="sprintname[]" id="filterBacklogType" placeholser="Select Sprint Name" class="form-select select" multiple multiselect-search="true" multiselect-select-all="true" multiselect-max-items="1">
          <!-- <option value="">All</option> -->
          <?php foreach ($data['tracker'] as $val) : ?>
            <option value="<?= $val['tracker_id']; ?>"><?= trim(ucfirst(strtolower($val['tracker']))); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="mb-3">
        <label for="filterCustName" class="form-label filter-text">Customer name</label>
        <!-- <select id="filterCustName" class="form-select"> -->
          <!-- <option value="">All</option> -->
          <select name="sprintname[]" id="filterCustName" placeholser="Select Sprint Name" class="form-select select" multiple multiselect-search="true" multiselect-select-all="true" multiselect-max-items="1">
          <?php foreach ($data['backlog_item_customer'] as $val) : ?>
            <option value="<?= $val['customer_id']; ?>"><?= trim(ucfirst(strtolower($val['customer_name']))); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="mb-3">
        <label for="filterPriority" class="form-label filter-text">Priority</label>
        <select name="sprintname[]" id="filterPriority" placeholser="Select Sprint Name" class="form-select select" multiple multiselect-search="true" multiselect-select-all="true" multiselect-max-items="1">
        <!-- <select id="filterPriority" class="form-select"> -->
          <!-- <option value="">All</option> -->
          <option value="H">High</option>
          <option value="M">Medium</option>
          <option value="L">Low</option>
        </select>
      </div>
      <div class="mb-3">
        <label for="filterStatus" class="form-label filter-text">Status</label>
        <!-- <select id="filterStatus" class="form-select"> -->
          <!-- <option value="">All</option> -->
          <select name="sprintname[]" id="filterStatus" placeholser="Select Sprint Name" class="form-select select" multiple multiselect-search="true" multiselect-select-all="true" multiselect-max-items="1">
          <?php foreach ($data['backlog_item_status'] as $val) : ?>
            <option value="<?= $val['status_id']; ?>"><?= trim(ucfirst(strtolower($val['status_name']))); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="d-flex justify-content-between mt-4">
        <button type="submit" class="btn primary_button">Apply </button>
        <button type="reset" class="btn button-secondary  apply-reset-filters-btn" id="resetFiltersBtn">Reset</button>
      </div>
    </form>
  </div>
</div>
<!--backlog History Modal -->
<div class="modal fade" id="sprintHistory" tabindex="-1" aria-labelledby="sprinthistory" aria-hidden="true"
  data-bs-backdrop="static">
  <div class="modal-dialog modal-lg">
    <div class="modal-content histroy-model">
      <div class="modal-header">
        <h5 class="modal-title">Backlog history</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="clearHistory" style="color: white;"></button>
      </div>
      <div class="modal-body">
        <div class="date-filter mb-2" style="display:flex;">
          <label for="">Start date</label>
          <input type="text" class="form-control mb-2 flatpickr-no-config flatpickr-input" id="startDate"
            name="startDate" accept="" placeholder="YYYY-MM-DD" readonly="readonly" required
            style=" margin: 0px 20px; width: auto;">
          <label for="">End date</label>
          <input type="text" class="form-control mb-2 flatpickr-no-config flatpickr-input" id="endDate" name="startDate"
            accept="" placeholder="YYYY-MM-DD" readonly="readonly" required
            style="margin: 0px 20px;width: auto;">
          <button id="filterButton" class="btn primary_button" style=" height: 37px; width: 75px;">Filter</button>
          <button id="resetButton" class="button-secondary ms-1" style=" height: 37px; width: 75px; ">Reset</button>
        </div>
        <!-- <div class="history-date"></div> -->
        <ul class="data-list" id="dataList"></ul>
        <div class="pagination">
          <button id="history-previous" class=" button-secondary">Previous</button>
          <button id="history-next" class=" button-secondary">Next</button>
        </div>
      </div>
    </div>
  </div>
</div>


<script>
  const assert_path = "<?= ASSERT_PATH ?>";
  const pId = <?= json_encode($data['p_id']); ?>;
  var statuses = <?= json_encode($data['backlog_item_status'])?>;
  const totalCount=<?= json_encode($data['totalCount'][0]['count']); ?>;
  console.log(totalCount);
  const userPermissions = {
        viewUserStories: <?php echo json_encode(has_permission('backlog/userstories')); ?>,
        updateBacklog: <?php echo json_encode(has_permission('backlog/updatebacklog')); ?>,
        deleteBacklog: <?php echo json_encode(has_permission('backlog/deletebacklogitem'));?>
    };

  // module name 
  const m_backlogItems = [];
</script>
