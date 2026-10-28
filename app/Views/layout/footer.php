<script src="<?= ASSERT_PATH ?>assets/js/bootstrap/js/bootstrap.min.js"></script>
<script src="<?= ASSERT_PATH ?>support/extensions/flatpickr/flatpickr.min.js"></script>
<script src="<?= ASSERT_PATH ?>support/static/js/pages/date-picker.js"></script>

<script>
    // Pass the base URL from PHP to JavaScript
    var baseUrl = '<?php echo base_url(); ?>';
    var oxUserId = <?= (int) session('employee_id') ?>;

    // ---------------------------------------------------------------------
    // Sidebar: collapsible on desktop (remembered), off-canvas on small screens
    // ---------------------------------------------------------------------
    (function () {
        const body = document.body;
        const sidebar = document.getElementById('sidebar');
        const hamburger = document.getElementById('hamburger-icon');
        const collapseBtn = document.getElementById('sidebar-toggle');
        const isDesktop = () => window.innerWidth >= 1200;

        const closeMobileSidebar = () => {
            body.classList.remove('sidebar-mobile-open');
            document.querySelector('.sidebar-backdrop')?.remove();
            hamburger?.setAttribute('aria-expanded', 'false');
        };

        const openMobileSidebar = () => {
            body.classList.add('sidebar-mobile-open');
            hamburger?.setAttribute('aria-expanded', 'true');
            if (!document.querySelector('.sidebar-backdrop')) {
                const backdrop = document.createElement('div');
                backdrop.className = 'sidebar-backdrop';
                backdrop.addEventListener('click', closeMobileSidebar, { once: true });
                body.appendChild(backdrop);
            }
        };

        const syncCollapseButton = () => {
            const collapsed = body.classList.contains('sidebar-collapsed');
            collapseBtn?.setAttribute('aria-expanded', String(!collapsed));
            collapseBtn?.setAttribute('aria-label', collapsed ? 'Expand navigation' : 'Collapse navigation');
        };

        hamburger?.addEventListener('click', () => {
            body.classList.contains('sidebar-mobile-open') ? closeMobileSidebar() : openMobileSidebar();
        });

        collapseBtn?.addEventListener('click', () => {
            if (!isDesktop()) {
                closeMobileSidebar();
                return;
            }
            const collapsed = body.classList.toggle('sidebar-collapsed');
            try { localStorage.setItem('ox-sidebar-collapsed', collapsed ? '1' : '0'); } catch (e) {}
            syncCollapseButton();
        });

        document.addEventListener('keydown', event => {
            if (event.key === 'Escape') {
                closeMobileSidebar();
                document.getElementById('meetingDetails')?.classList.remove('active');
            }
        });

        window.addEventListener('resize', () => {
            if (isDesktop()) {
                closeMobileSidebar();
            } else {
                body.classList.remove('sidebar-collapsed');
            }
            syncCollapseButton();
        });

        // Keep the active item visible in a long navigation list
        sidebar?.querySelector('.ox-sidenav-link.is-active')?.scrollIntoView({ block: 'nearest' });
        syncCollapseButton();
    })();

    // ---------------------------------------------------------------------
    // Notifications popover
    // ---------------------------------------------------------------------
    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, ch => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
        })[ch]);
    }

    function isValidUrl(string) {
        try {
            const url = new URL(string);
            return url.protocol === 'http:' || url.protocol === 'https:';
        } catch (_) {
            return false;
        }
    }

    const NOTICE_ICONS = { task_assigned: 'user-plus', story_comment: 'message-square', sprint_status: 'zap' };
    let unreadCount = 0;

    $(document).ready(function() {
        $('#notification-icon').on('click', function(event) {
            event.stopPropagation();
            const panel = $('#meetingDetails').toggleClass('active');
            $(this).attr('aria-expanded', panel.hasClass('active') ? 'true' : 'false');
            if (panel.hasClass('active')) {
                notification();
            }
        });
        notification();

        // Keep the badge fresh with a cheap count; skip while the tab is hidden
        setInterval(function() {
            if (document.visibilityState !== 'visible') return;
            $.ajax({
                url: baseUrl + 'notification/unreadCount',
                method: 'GET',
                dataType: 'json',
                global: false,
                success: response => {
                    if ((response?.unread ?? 0) !== unreadCount) notification();
                }
            });
        }, 60000);

        $(document).on('click', function(event) {
            const $target = $(event.target);
            if (!$target.closest('#notification-icon').length &&
                !$target.closest('#meetingDetails').length &&
                $('#meetingDetails').hasClass('active')) {
                $('#meetingDetails').removeClass('active');
                $('#notification-icon').attr('aria-expanded', 'false');
            }
        });

        $('#meeting-filters').on('change', 'input[name="meetingFilter"]', function() {
            const group = this.value;
            $('#meeting-list > li').each(function() {
                $(this).toggle($(this).data('group') === group);
            });
        });

        // Opening an activity item marks it read first (keepalive survives the navigation)
        $('#meeting-list').on('click', '.activity-item a', function() {
            const item = $(this).closest('.activity-item');
            if (item.hasClass('is-unread')) {
                markRead([item.data('id')]);
            }
        });

        $('#markAllRead').on('click', function() {
            markRead([]);
            $('#meeting-list .activity-item').removeClass('is-unread');
        });
    });

    function markRead(ids) {
        const body = new URLSearchParams();
        ids.forEach(id => body.append('ids[]', id));
        fetch(baseUrl + 'notification/markRead', { method: 'POST', body, keepalive: true })
            .then(response => response.json())
            .then(data => setUnread(data.unread ?? 0))
            .catch(() => {});
    }

    function setUnread(count) {
        unreadCount = count;
        $('#meeting-count').remove();
        if (count > 0) {
            $('#notification-icon').append(`<span class="badge" id="meeting-count">${count > 9 ? '9+' : count}</span>`)
                .attr('aria-label', `Notifications, ${count} unread`);
        } else {
            $('#notification-icon').attr('aria-label', 'Notifications');
        }
        $('#activity-count').text(count > 9 ? '9+' : count).prop('hidden', count === 0);
        $('#markAllRead').prop('hidden', count === 0);
    }

    function notification() {
        $.ajax({
            url: baseUrl + 'notification/notificationDetails',
            method: 'GET',
            dataType: 'json',
            global: false, // background refresh: don't trigger the page loader
            success: function(response) {
                renderNotifications(response?.activity ?? [], response?.meetings ?? [], response?.sprints ?? []);
                setUnread(response?.unread ?? 0);
                $('#no-meetings-message').hide();
                $('#meeting-filters input:checked').trigger('change');
            },
            error: function() {
                $('#meeting-list').empty();
                $('#no-meetings-message').find('span').text('Failed to load notifications');
                $('#no-meetings-message').show();
            }
        });
    }

    function timeAgo(value) {
        const seconds = Math.max(0, (Date.now() - new Date(String(value).replace(' ', 'T')).getTime()) / 1000);
        if (seconds < 60) return 'Just now';
        if (seconds < 3600) return `${Math.floor(seconds / 60)}m ago`;
        if (seconds < 86400) return `${Math.floor(seconds / 3600)}h ago`;
        if (seconds < 604800) return `${Math.floor(seconds / 86400)}d ago`;
        return formatDate(value);
    }

    // Render activity, meetings and upcoming scrum events into one list, tagged by tab
    function renderNotifications(activity, meetings, sprints) {
        const list = $('#meeting-list').empty();
        const empty = (group, icon, text) => list.append(
            `<li class="ox-empty-inline" data-group="${group}"><i class="icon-${icon}" aria-hidden="true"></i><span>${text}</span></li>`);

        activity.forEach(function(item) {
            list.append(`
                <li class="activity-item ox-notice${item.is_read === 'N' ? ' is-unread' : ''}" data-group="activity" data-id="${Number(item.notification_id)}">
                    <a class="ox-notice-link" href="${baseUrl}${escapeHtml(item.link)}">
                        <div class="ox-notice-icon"><i class="icon-${NOTICE_ICONS[item.type] || 'bell'}" aria-hidden="true"></i></div>
                        <div class="ox-notice-body">
                            <div class="ox-notice-title">${escapeHtml(item.title)}</div>
                            <div class="ox-notice-meta">${escapeHtml(item.message || '')}</div>
                            <div class="ox-notice-time">${escapeHtml(timeAgo(item.created_date))}</div>
                        </div>
                    </a>
                </li>`);
        });

        meetings.forEach(function(meeting) {
            const link = meeting.meeting_link;
            const linkHtml = !link
                ? '<div class="meeting-link text-muted">No link found</div>'
                : isValidUrl(link)
                    ? `<a href="${escapeHtml(link)}" target="_blank" rel="noopener" class="meeting-link">${escapeHtml(link)}</a>`
                    : `<div class="meeting-link">${escapeHtml(link)}</div>`;

            list.append(`
                <li class="meeting ox-notice" data-group="meetings">
                    <div class="ox-notice-icon"><i class="icon-video" aria-hidden="true"></i></div>
                    <div class="ox-notice-body">
                        <div class="ox-notice-title">${escapeHtml(meeting.meeting_type_name)}</div>
                        <div class="ox-notice-meta">
                            ${escapeHtml(formatDate(meeting.meeting_start_date))} · ${escapeHtml(timeFormat(meeting.meeting_start_time))}
                            · ${escapeHtml(meeting.product_name)}
                        </div>
                        ${linkHtml}
                    </div>
                </li>`);
        });

        sprints.forEach(function(sprint) {
            list.append(`
                <li class="upcoming-sprints-list ox-notice" data-group="upcoming-scrum">
                    <a class="ox-notice-link" href="${baseUrl}sprint/navsprintview?sprint_id=${encodeURIComponent(sprint.sprint_id)}">
                        <div class="ox-notice-icon"><i class="icon-zap" aria-hidden="true"></i></div>
                        <div class="ox-notice-body">
                            <div class="ox-notice-title">${escapeHtml(sprint.activity)} · Sprint ${escapeHtml(sprint.sprint_version)}</div>
                            <div class="ox-notice-meta">${escapeHtml(formatDate(sprint.start_date))} · ${escapeHtml(sprint.product_name)}</div>
                        </div>
                    </a>
                </li>`);
        });

        if (activity.length === 0) empty('activity', 'bell-off', "You're all caught up");
        if (meetings.length === 0) empty('meetings', 'calendar', 'No meetings scheduled');
        if (sprints.length === 0) empty('upcoming-scrum', 'zap', 'No upcoming scrum events');
    }

    // ---------------------------------------------------------------------
    // Loading feedback: overlay for slow page navigation, slim top bar for AJAX
    // ---------------------------------------------------------------------
    document.addEventListener('DOMContentLoaded', function() {
        const loader = document.getElementById('loader');
        const progress = document.getElementById('oxProgress');
        let loaderTimer = null;

        const showLoader = () => {
            // Only show the overlay if navigation is actually slow
            clearTimeout(loaderTimer);
            loaderTimer = setTimeout(() => loader?.classList.remove('is-loaded'), 350);
        };
        const hideLoader = () => {
            clearTimeout(loaderTimer);
            loader?.classList.add('is-loaded');
        };
        hideLoader();

        document.querySelectorAll('a[href], form').forEach(element => {
            if (element.matches('a[href]')) {
                element.addEventListener('click', event => {
                    const href = element.getAttribute('href');
                    if (event.defaultPrevented || event.metaKey || event.ctrlKey || event.shiftKey || !href || href.startsWith('#') ||
                        href.startsWith('javascript:') || element.target === '_blank' || element.hasAttribute('data-bs-toggle') ||
                        element.hasAttribute('download')) return;
                    showLoader();
                });
            } else {
                element.addEventListener('submit', event => {
                    if (!event.defaultPrevented) showLoader();
                });
            }
        });

        window.addEventListener('pageshow', hideLoader);
        if (window.jQuery) {
            $(document).ajaxStart(() => progress?.classList.add('is-active'))
                .ajaxStop(() => progress?.classList.remove('is-active'));
        }
    });

    // date format
    function formatDate(dateString) {
        const date = new Date(dateString);

        const day = date.getDate().toString().padStart(2, '0');
        const month = date.toLocaleString('en-US', { month: 'short' });
        const year = date.getFullYear();

        return `${day}, ${month} ${year}`;
    }

    // time format
    function timeFormat(timeString) {
        // Split the time string into hours, minutes, and seconds
        const [hours, minutes] = timeString.split(':').map(Number);

        // Determine AM or PM
        const ampm = hours >= 12 ? 'PM' : 'AM';

        // Convert hours to 12-hour format
        let hours12 = hours % 12;
        hours12 = hours12 ? hours12 : 12; // the hour '0' should be '12'

        return `${hours12}:${minutes.toString().padStart(2, '0')} ${ampm}`;
    }
</script>
<script src="<?= ASSERT_PATH ?>assets/js/layout/ux.js?v=<?= filemtime(FCPATH . 'assets/js/layout/ux.js') ?>"></script>
<script src="<?= ASSERT_PATH ?>assets/js/layout/search.js?v=<?= filemtime(FCPATH . 'assets/js/layout/search.js') ?>"></script>

<?php if (isset($view) && is_file(FCPATH . 'assets/js/' . $view . '.js')) {
    // Versioned by modification time so browsers never run a stale page script
    echo "<script type='text/javascript' src='" . JS_PATH . $view . ".js?v=" . filemtime(FCPATH . 'assets/js/' . $view . '.js') . "'></script>";
} ?>
</body>

</html>
