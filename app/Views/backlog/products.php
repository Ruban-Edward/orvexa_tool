<div class="product-view-container">
    <div class="row d-flex justify-content-end align-items-center">
        <div class="col me-auto"><h3 class="mb-0">Products</h3></div>
        <div class="col-md-auto me-2"><button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#addProductModal"><i class="bi bi-plus-lg"></i> Add product</button></div>
        <!-- <div class="col-md-auto">
            <h5><span class="h6">No of products: </span></h5>
        </div> -->
        <div class="col-md-auto d-flex align-items-center">
            <div class="search-container me-2">
                <div class="search-box">
                    <input type="text" id="userSearchInput" class="search-input" placeholder="Search products">
                    <button id="search_btn" class="search-button">
                        <i class="bi bi-search"></i>
                        <span>Search</span>
                    </button>
                </div>
            </div>
            <!-- <div class="sort-container">
                <select id="sortCriteria" class="form-select">
                    <option value="">Sort by</option>
                    <option value="backlog_items">Number of backlog items</option>
                    <option value="items_in_sprint">Number of items in sprint</option>
                    <option value="completed_percentage">Completed percentage</option>
                </select>
            </div> -->
        </div>
    </div>
    <div class="row g-3 mt-0">
        <?php
        $cardClass = count($data['product_details']) === 1 ? 'product-card increased-width' : 'product-card';
        ?>
        <?php foreach ($data['product_details'] as $index => $details) : ?>
            <div class="col-md-4 backlog-card position-relative">
                        <?php $id = $details['product_id']; ?>
                        <a href="<?= ASSERT_PATH ?>backlog/backlogitems?pid=<?= $id ?>" class="<?= $cardClass ?> active" data-toggle="tooltip" data-placement="top" title="View backlog items of <?= strtoupper($details["product_name"]) ?>">     
                        <div class="card-body-container">
                            <div class="title-product"> 
                                <h4 class="product-title"><?= trim(strtoupper($details["product_name"])) ?></h4>
                            </div>
                            <div class="backlog-content">
                                <div class="row d-flex justify-content-center">
                                    <div class="col-sm-7 text-left">
                                        <p class="product-detail-label">No of pbl items:</p>
                                    </div> 
                                    <div class="col-sm-5 text-left">
                                        <p class="product-detail"><?= $details['number_of_backlog_items'] ?></p>
                                    </div>
                                </div>
                                <div class="row d-flex justify-content-center">
                                    <div class="col-sm-7 text-left">
                                        <p class="product-detail-label">No of user stories:</p>
                                    </div>
                                    <div class="col-sm-5 d-flex align-items-end">
                                        <p class="product-detail"><?= (empty($details['number_of_user_stories']) ? '0' : $details['number_of_user_stories']) ?></p>
                                    </div>
                                </div>
            
                                <div class="row d-flex justify-content-center">
                                    <div class="col-sm-7 text-left">
                                        <p class="product-detail-label">Items onhold:</p>
                                    </div>
                                    <div class="col-sm-5 text-left">
                                        <p class="product-detail"><?= (empty($details['on_hold']) ? '0' : $details['on_hold']) ?></p>
                                    </div>
                                </div>
                                
                                <!-- <div class="row d-flex justify-content-center">
                                    <div class="col-sm-7 text-left">
                                        <p class="product-detail-label">Product owner:</p>
                                    </div>
                                    <div class="col-sm-5 text-left">
                                        <p class="product-detail"></p>
                                    </div>
                                </div> -->
                                <div class="row d-flex justify-content-center">
                                    <div class="col-sm-7 text-left">
                                        <p class="product-detail-label">Last updated:</p>
                                    </div>
                                    <div class="col-sm-5 text-left">
                                        <p class="product-detail">
                                        <?= isset($details['last_updated']) ? date('d, M Y',strtotime($details['last_updated'])):'N/A';?>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            
                            <!-- <div class="cls-pie-graph">

                                <div class="chart-container">
                                    <canvas id="pieChart<?= $index + 1 ?>"></canvas>
                                </div>
                                <div class="legend-container" id="pieChart<?= $index + 1 ?>Legend"></div>
                            </div> -->
                        </div>
                    </a>
                    <div class="dropdown product-actions">
                        <button class="product-actions-button" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Product actions">
                            <i class="bi bi-three-dots-vertical"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#editProduct<?= $id ?>"><i class="bi bi-pencil me-2"></i>Edit</button></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="post" action="<?= base_url('backlog/deleteproduct') ?>" onsubmit="return confirm('Delete this product? It will be hidden but can be restored from the database.');">
                                    <?= csrf_field() ?><input type="hidden" name="product_id" value="<?= $id ?>">
                                    <button type="submit" class="dropdown-item text-danger"><i class="bi bi-trash me-2"></i>Delete</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                    <div class="modal fade" id="editProduct<?= $id ?>" tabindex="-1" aria-labelledby="editProductLabel<?= $id ?>" aria-hidden="true">
                        <div class="modal-dialog"><form class="modal-content" method="post" action="<?= base_url('backlog/editproduct') ?>">
                            <?= csrf_field() ?><input type="hidden" name="product_id" value="<?= $id ?>">
                            <div class="modal-header"><h5 class="modal-title" id="editProductLabel<?= $id ?>">Edit product</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
                            <div class="modal-body"><label class="form-label" for="productName<?= $id ?>">Product name</label><input id="productName<?= $id ?>" name="product_name" class="form-control" value="<?= esc($details['product_name']) ?>" maxlength="50" required></div>
                            <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Save changes</button></div>
                        </form></div>
                    </div>
                
            </div>
        <?php endforeach; ?>
        <?php if (empty($data['product_details'])): ?><div class="col-12 text-center py-5"><h4>No products yet</h4><p>Create your first product to start managing its backlog.</p></div><?php endif; ?>
    </div>

    <div class="empty-content" id="empty" style="display: none;">
        <div class="row mt-5 text-center cls-norecFound">
        <span class="bi bi-emoji-frown cls-noProductIcon"></span>
            <h2>No such products found</h2>
        </div>
    </div>
</div>
<div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductLabel" aria-hidden="true"><div class="modal-dialog"><form class="modal-content" method="post" action="<?= base_url('backlog/addproduct') ?>"><?= csrf_field() ?><div class="modal-header"><h5 class="modal-title" id="addProductLabel">Add product</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div><div class="modal-body"><label for="product_name" class="form-label">Product name</label><input id="product_name" name="product_name" class="form-control" maxlength="50" required autofocus><div class="form-text">Use a clear name up to 50 characters.</div></div><div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Create product</button></div></form></div></div>
<script>
    var productCount = <?= count($data['product_details']);?>
    
    //module name 
    const m_ProductBacklog = [];
</script>
