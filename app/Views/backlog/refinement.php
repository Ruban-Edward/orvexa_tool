
<dl class="ox-context">
    <div><dt>Product</dt><dd><?= esc(trim(ucfirst(strtolower($data['productName'])))); ?></dd></div>
</dl>
<div class="container">
  <div class="page">
    <div class="row">
      <table id="priority-table" class="table table-borderless custom-table">
        <thead id="tableHeader" class="header_color">
          <!-- Table headers will be inserted here -->
        </thead>
        <tbody id="tableBody">
          <!-- Table rows will be inserted here -->
        </tbody>
      </table>
    </div>
  </div>
</div>

<div class="toast-container position-fixed top-0 end-0 p-3">
  <div id="liveToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="toast-header">
      <strong class="me-auto">Backlog gromming</strong>
      <small>0 mins ago</small>
      <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
    <div class="toast-body">
      Backlog gromming failed
    </div>
  </div>
</div>

<script>
  const refinement_url = "<?= ASSERT_PATH ?>"
  const data = <?php echo json_encode($data['backlogItemType']); ?>;
  var pid = <?php echo json_encode(array($data['p_id'])[0]); ?>;

  // module name 
  const m_refinement = [];
</script>
<script src='<?= ASSERT_PATH ?>assets/js/backlog/refinement.js'></script>