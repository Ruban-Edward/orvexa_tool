<div class="product-view-container">
    <div class="ox-pagebar">
        <div class="search-box ox-search expanded">
            <button id="search_btn" class="search-button" type="button" aria-label="Search products" tabindex="-1">
                <i class="icon-search" aria-hidden="true"></i>
            </button>
            <input type="search" id="userSearchInput" class="search-input form-control" placeholder="Search products" aria-label="Search products">
        </div>
        <div class="ox-pagebar-actions">
            <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#addProductModal">
                <i class="icon-plus" aria-hidden="true"></i> Add product
            </button>
        </div>
    </div>

    <div class="ox-card-grid">
        <?php foreach ($data['product_details'] as $index => $details) : ?>
            <?php
            $id = $details['product_id'];
            $productName = trim($details['product_name']);
            ?>
            <div class="backlog-card ox-card-cell">
                <a href="<?= ASSERT_PATH ?>backlog/backlogitems?pid=<?= (int) $id ?>" class="product-card ox-product-card"
                    title="View the backlog of <?= esc($productName, 'attr') ?>">
                    <div class="ox-product-card-head">
                        <span class="ox-avatar-square"><?= esc(strtoupper(substr($productName, 0, 1))) ?></span>
                        <h4 class="product-title"><?= esc($productName) ?></h4>
                    </div>
                    <dl class="ox-mini-stats">
                        <div>
                            <dt>Backlog items</dt>
                            <dd><?= (int) $details['number_of_backlog_items'] ?></dd>
                        </div>
                        <div>
                            <dt>User stories</dt>
                            <dd><?= (int) ($details['number_of_user_stories'] ?? 0) ?></dd>
                        </div>
                        <div>
                            <dt>On hold</dt>
                            <dd class="<?= ! empty($details['on_hold']) ? 'is-warn' : '' ?>"><?= (int) ($details['on_hold'] ?? 0) ?></dd>
                        </div>
                    </dl>
                    <div class="ox-product-card-foot">
                        <span>
                            <i class="icon-clock" aria-hidden="true"></i>
                            Updated <?= isset($details['last_updated']) ? date('d M Y', strtotime($details['last_updated'])) : 'N/A' ?>
                        </span>
                        <span class="ox-link-arrow">Open backlog <i class="icon-arrow-right" aria-hidden="true"></i></span>
                    </div>
                </a>
                <div class="dropdown product-actions">
                    <button class="product-actions-button ox-iconbtn" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Product actions">
                        <i class="icon-more-horizontal" aria-hidden="true"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#editProduct<?= $id ?>"><i class="icon-edit-2" aria-hidden="true"></i>Edit</button></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="post" action="<?= base_url('backlog/deleteproduct') ?>" onsubmit="return confirm('Delete this product? It will be hidden but can be restored from the database.');">
                                <?= csrf_field() ?><input type="hidden" name="product_id" value="<?= $id ?>">
                                <button type="submit" class="dropdown-item text-danger"><i class="icon-trash-2" aria-hidden="true"></i>Delete</button>
                            </form>
                        </li>
                    </ul>
                </div>
                <div class="modal fade" id="editProduct<?= $id ?>" tabindex="-1" aria-labelledby="editProductLabel<?= $id ?>" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered"><form class="modal-content" method="post" action="<?= base_url('backlog/editproduct') ?>">
                        <?= csrf_field() ?><input type="hidden" name="product_id" value="<?= $id ?>">
                        <div class="modal-header"><h5 class="modal-title" id="editProductLabel<?= $id ?>">Edit product</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
                        <div class="modal-body"><label class="form-label" for="productName<?= $id ?>">Product name</label><input id="productName<?= $id ?>" name="product_name" class="form-control" value="<?= esc($details['product_name']) ?>" maxlength="50" required></div>
                        <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Save changes</button></div>
                    </form></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if (empty($data['product_details'])): ?>
        <div class="ox-empty ox-panel">
            <i class="icon-package" aria-hidden="true"></i>
            <h4>No products yet</h4>
            <p>Create your first product to start managing its backlog.</p>
        </div>
    <?php endif; ?>

    <div class="empty-content ox-empty ox-panel" id="empty" style="display: none;">
        <i class="icon-search" aria-hidden="true"></i>
        <h4>No products found</h4>
        <p>Try a different search term.</p>
    </div>
</div>
<div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductLabel" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><form class="modal-content" method="post" action="<?= base_url('backlog/addproduct') ?>"><?= csrf_field() ?><div class="modal-header"><h5 class="modal-title" id="addProductLabel">Add product</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div><div class="modal-body"><label for="product_name" class="form-label">Product name</label><input id="product_name" name="product_name" class="form-control" maxlength="50" required autofocus><div class="form-text">Use a clear name up to 50 characters.</div></div><div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Create product</button></div></form></div></div>
<script>
    var productCount = <?= count($data['product_details']);?>

    //module name
    const m_ProductBacklog = [];
</script>
