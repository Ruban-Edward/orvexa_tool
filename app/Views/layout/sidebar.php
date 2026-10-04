<!-- Sidebar -->
<aside>
    <div id="sidebar" class="sidebar-desktop position-fixed">
        <div class="sidebar-wrapper">
            <div class="sidebar-menu">
                <ul class="menu">
                    <li class="sidebar-item">
                        <a href="<?= ASSERT_PATH ?>dashboard/dashboardView" class='sidebar-link'>
                            <i class="icon-grid"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>

                    <?php if (has_permission('backlog/productbacklogs')): ?>
                        <li class="sidebar-item">
                            <a href="<?= ASSERT_PATH ?>backlog/productbacklogs" class='sidebar-link'>
                                <i class="icon-package"></i>
                                <span>Products</span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if (has_permission('sprint/sprintlist')): ?>
                        <li class="sidebar-item">
                            <a href="<?= ASSERT_PATH ?>sprint/sprintlist" class='sidebar-link'>
                                <i class="icon-framer"></i>
                                <span>Sprints</span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if (has_permission('meeting/calendar')): ?>
                        <li class="sidebar-item">
                            <a href="<?= ASSERT_PATH ?>meeting/calendar" class='sidebar-link'>
                                <i class="icon-calendar"></i>
                                <span>Calendar</span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <!-- Report sidebar item -->
                    <?php if (has_permission('report/backlogreport/backlog') || has_permission('report/sprintreport/sprint') || has_permission('report/meetingreport/meet')): ?>
                        <li class="sidebar-item has-sub">
                            <a href="#" class='sidebar-link'>
                                <i class="icon-trending-up"></i>
                                <span>Report</span>
                            </a>
                            <ul class="submenu">
                                <?php if (has_permission('report/backlogreport/backlog')): ?>
                                    <li class="submenu-item">
                                        <a href="<?= ASSERT_PATH ?>report/backlogreport/backlog">Backlog report</a>
                                    </li>
                                <?php endif; ?>

                                <?php if (has_permission('report/sprintreport/sprint')): ?>
                                    <li class="submenu-item">
                                        <a href="<?= ASSERT_PATH ?>report/sprintreport/sprint">Sprint report</a>
                                    </li>
                                <?php endif; ?>

                                <?php if (has_permission('report/meetingreport/meet')): ?>
                                    <li class="submenu-item">
                                        <a href="<?= ASSERT_PATH ?>report/meetingreport/meet">Meeting report</a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </li>
                    <?php endif; ?>

                    <?php if (has_permission('admin/manageUser') || has_permission('admin/setPermissionPage')): ?>
                        <li class="sidebar-item has-sub">
                            <a href="#" class='sidebar-link'>
                                <i class="icon-user"></i>
                                <span>Admin</span>
                            </a>
                            <ul class="submenu">
                                <?php if (has_permission('admin/manageUser')): ?>
                                    <li class="submenu-item">
                                        <a href="<?= ASSERT_PATH ?>admin/manageUser">Manage user</a>
                                    </li>
                                <?php endif; ?>

                                <?php if (has_permission('admin/setPermissionPage')): ?>
                                    <li class="submenu-item">
                                        <a href="<?= ASSERT_PATH ?>admin/setPermissionPage">Manage permission</a>
                                    </li>
                                <?php endif; ?>

                                <?php if (has_permission('admin/adminSettings')): ?>
                                    <li class="submenu-item">
                                        <a href="<?= ASSERT_PATH ?>admin/adminSettings">Admin settings</a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </li>
                    <?php endif; ?>

                    <?php if (has_permission('userGuide')): ?>
                    <li class="sidebar-item">
                        <a href="https://www.orvexa.com/docs/" target="_blank"
                        class='sidebar-link'>
                            <i class="icon-book-open"></i> 
                            <span>User guide</span>
                        </a>
                   </li>
                   <?php endif; ?>
                </ul>
            </div>

            <!-- Footer -->
            <div class="footer">
                <div class="cpy-rights">
                    <div class="sidebar-footer">
                        <p>Powered by <a href="https://www.infinitisoftware.net/" target="_blank">
                                <img src="<?= ASSERT_PATH ?>assets/images/logo/orvexa_08_monochrome_light.png" alt="Orvexa"></a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</aside>

<button id="sidebar-toggle" class="sidebar-toggle" type="button" aria-label="Collapse navigation" title="Collapse navigation" aria-expanded="true"></button>