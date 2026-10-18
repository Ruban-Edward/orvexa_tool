<?= $this->include('layout/header') ?>
<?php if (session()->getFlashdata('success') || session()->getFlashdata('error')): ?>
    <div id="globalFlashMessage" class="global-flash-message alert <?= session()->getFlashdata('success') ? 'alert-success' : 'alert-danger' ?>" role="alert">
        <?= esc(session()->getFlashdata('success') ?: session()->getFlashdata('error')) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <script>
        setTimeout(function () {
            const alert = document.getElementById('globalFlashMessage');
            if (alert) {
                alert.classList.remove('show');
                setTimeout(() => alert.remove(), 150);
            }
        }, 3000);
    </script>
<?php endif; ?>
<?= $this->include('layout/sidebar') ?>
<div id="main" class="main-content">
    <?php if(isset($breadcrumbs)): ?>
    <div >
        <p class="bread">
            <?php foreach($breadcrumbs as $key => $url): ?>
                <a href="<?= $url ?>"><?= ucfirst($key) ?></a>
                <?php if($url !== end($breadcrumbs)): ?> > <?php endif; ?>
            <?php endforeach; ?>
        </p>
    </div>
    <?php endif; ?>
    
    <?php if(isset($title)): ?>
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center mb-3 border-bottom">
        <h1 class="cls-h2"><?= $title ?></h1>
    </div>
    <?php endif; ?>

    <?php if(isset($view)){
        echo $this->include($view);
    } ?>
</div>
<?= $this->include('layout/footer') ?>
