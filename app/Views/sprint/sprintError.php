<script>
    const BASE_URL = "<?= ASSERT_PATH ?>";
    var permit = <?= has_permission('sprint/edit') ? 1 : 0 ?>;
</script>
<div class="ox-empty ox-panel ox-empty-page">
    <i class="icon-zap" aria-hidden="true"></i>
    <h4>No sprints yet</h4>
    <p>Plan a sprint to pick backlog work, assign the team and track progress.</p>
    <?php if (has_permission('sprint/navcreatesprint')): ?>
        <a href="<?= ASSERT_PATH ?>sprint/navcreatesprint" id="sprint" class="btn btn-primary mt-3"><i class="icon-plus" aria-hidden="true"></i> Create sprint</a>
    <?php endif; ?>
</div>
