<script src="<?= ASSERT_PATH ?>assets/js/bootstrap/js/bootstrap.min.js"></script>
<script src="<?= ASSERT_PATH ?>support/extensions/flatpickr/flatpickr.min.js"></script>
<script src="<?= ASSERT_PATH ?>support/static/js/pages/date-picker.js"></script>

<!-- Include the Chart.js DataLabels plugin -->
<script src="<?= ASSERT_PATH ?>support/extensions/chart.js/chartjs-plugin-datalabels.js"></script>

<script>
    // Pass the base URL from PHP to JavaScript
    var baseUrl = '<?php echo base_url(); ?>';

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
            const showMeetings = this.value === 'meetings';
            $('.meeting').toggle(showMeetings);
            $('.upcoming-sprints-list').toggle(!showMeetings);
        });
    });

    function notification() {
        $.ajax({
            url: baseUrl + 'notification/notificationDetails',
            method: 'GET',
            dataType: 'json',
            global: false, // background refresh: don't trigger the page loader
            success: function(response) {
                const meetings = response?.meetings ?? [];
                const sprints = response?.sprints ?? [];
                if (meetings.length > 0 || sprints.length > 0) {
                    $('#meeting-filters').show();
                    renderMeetings(meetings, sprints);
                    $('#no-meetings-message').hide();
                    $('#meetings').prop('checked', true).trigger('change');
                } else {
                    $('#meeting-list').empty();
                    $('#meeting-count').remove();
                    $('#meeting-filters').hide();
                    $('#no-meetings-message').show();
                }
            },
            error: function() {
                $('#no-meetings-message').find('span').text('Failed to load notifications');
                $('#no-meetings-message').show();
            }
        });
    }

    // Render the user's meetings and upcoming scrum activities
    function renderMeetings(meetings, sprints) {
        const total = meetings.length + sprints.length;
        $('#meeting-count').remove();
        $('#notification-icon').append(`<span class="badge" id="meeting-count">${total > 9 ? '9+' : total}</span>`);

        const list = $('#meeting-list').empty();

        meetings.forEach(function(meeting) {
            const link = meeting.meeting_link;
            const linkHtml = !link
                ? '<div class="meeting-link text-muted">No link found</div>'
                : isValidUrl(link)
                    ? `<a href="${escapeHtml(link)}" target="_blank" rel="noopener" class="meeting-link">${escapeHtml(link)}</a>`
                    : `<div class="meeting-link">${escapeHtml(link)}</div>`;

            list.append(`
                <li class="meeting ox-notice">
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
                <li class="upcoming-sprints-list ox-notice">
                    <a class="ox-notice-link" href="${baseUrl}sprint/navsprintview?sprint_id=${encodeURIComponent(sprint.sprint_id)}">
                        <div class="ox-notice-icon"><i class="icon-zap" aria-hidden="true"></i></div>
                        <div class="ox-notice-body">
                            <div class="ox-notice-title">${escapeHtml(sprint.activity)} · Sprint ${escapeHtml(sprint.sprint_version)}</div>
                            <div class="ox-notice-meta">${escapeHtml(formatDate(sprint.start_date))} · ${escapeHtml(sprint.product_name)}</div>
                        </div>
                    </a>
                </li>`);
        });

        if (meetings.length === 0) {
            list.append('<li class="meeting ox-empty-inline"><i class="icon-calendar" aria-hidden="true"></i><span>No meetings scheduled</span></li>');
        }
        if (sprints.length === 0) {
            list.append('<li class="upcoming-sprints-list ox-empty-inline"><i class="icon-zap" aria-hidden="true"></i><span>No upcoming scrum events</span></li>');
        }
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

<?php if (isset($view) && is_file(FCPATH . 'assets/js/' . $view . '.js')) {
    echo "<script type='text/javascript' src='" . JS_PATH . $view . ".js'></script>";
} ?>
</body>

</html>
