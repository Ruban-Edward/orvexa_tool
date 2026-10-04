<!-- Footer -->
<footer>
        
</footer>


<script src="<?= ASSERT_PATH ?>support/extensions/perfect-scrollbar/perfect-scrollbar.min.js"></script>
<script src="<?= ASSERT_PATH ?>support/compiled/js/app.js"></script>
<script src="<?= ASSERT_PATH ?>support/extensions/jquery/jquery.min.js"></script>
<script src="<?= ASSERT_PATH ?>support/extensions/flatpickr/flatpickr.min.js"></script>
<script src="<?= ASSERT_PATH ?>support/static/js/pages/date-picker.js"></script>

<!-- Include the Chart.js DataLabels plugin -->
<script src="<?= ASSERT_PATH ?>support/extensions/chart.js/chartjs-plugin-datalabels.js"></script>

<!-- Need: Apexcharts -->
<!-- <script src="<?= ASSERT_PATH ?>support/extensions/apexcharts/apexcharts.min.js"></script>
<script src="<?= ASSERT_PATH ?>support/static/js/pages/dashboard.js"></script> -->
<script>
    // Pass the base URL from PHP to JavaScript
    var baseUrl = '<?php echo base_url(); ?>';

    // document.getElementById('sidebar-toggle').addEventListener('click', function() {
    //     document.getElementById('sidebar').classList.toggle('active');
    // });
    var homebtn = document.getElementById('home-icon');
    var menuitems = document.getElementById('menu-items');
    if (homebtn) {
        homebtn.addEventListener('click', () => {
            menuitems.classList.toggle('show');
        });
    }
    window.addEventListener('resize', () => {
        if (window.innerWidth > 500) {
            menuitems.style.display = 'block';
        } else {
            menuitems.style.display = 'none';
        }
    })
    document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('hamburger-icon');
    const desktopSidebarToggle = document.getElementById('sidebar-toggle');
    const sidebarLinks = document.querySelectorAll('.sidebar-link');
    const hasSubItems = document.querySelectorAll('.has-sub');

    const closeMobileSidebar = () => {
        sidebar?.classList.remove('active');
        document.body.classList.remove('sidebar-mobile-open');
        document.querySelector('.sidebar-backdrop')?.remove();
        sidebarToggle?.setAttribute('aria-expanded', 'false');
        sidebarToggle?.setAttribute('aria-label', 'Open navigation');
    };

    const toggleMobileSidebar = () => {
        const isOpen = !document.body.classList.contains('sidebar-mobile-open');
        sidebar?.classList.toggle('active', isOpen);
        document.body.classList.toggle('sidebar-mobile-open', isOpen);
        sidebarToggle?.setAttribute('aria-expanded', String(isOpen));
        sidebarToggle?.setAttribute('aria-label', isOpen ? 'Close navigation' : 'Open navigation');

        if (isOpen) {
            const backdrop = document.createElement('div');
            backdrop.className = 'sidebar-backdrop';
            backdrop.addEventListener('click', closeMobileSidebar, { once: true });
            document.body.appendChild(backdrop);
        } else {
            document.querySelector('.sidebar-backdrop')?.remove();
        }
    };

    sidebarToggle?.addEventListener('click', toggleMobileSidebar);
    sidebarToggle?.addEventListener('keydown', event => {
        if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            toggleMobileSidebar();
        }
    });

    desktopSidebarToggle?.addEventListener('click', () => {
        const isExpanded = document.body.classList.toggle('sidebar-collapsed') === false;
        desktopSidebarToggle.setAttribute('aria-expanded', String(isExpanded));
        desktopSidebarToggle.setAttribute('aria-label', isExpanded ? 'Collapse navigation' : 'Expand navigation');
        desktopSidebarToggle.setAttribute('title', isExpanded ? 'Collapse navigation' : 'Expand navigation');
    });

    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && document.body.classList.contains('sidebar-mobile-open')) {
            closeMobileSidebar();
        }
    });

    window.addEventListener('resize', () => {
        if (window.innerWidth >= 1200) {
            closeMobileSidebar();
        } else {
            document.body.classList.remove('sidebar-collapsed');
            desktopSidebarToggle?.setAttribute('aria-expanded', 'true');
        }
    });

    // Handle active state and dropdowns
    sidebarLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const isSubMenu = this.nextElementSibling && this.nextElementSibling.classList.contains('submenu');
            
            if (isSubMenu) {
                e.preventDefault();
                this.parentElement.classList.toggle('open');
            } else {
                sidebarLinks.forEach(l => l.classList.remove('active'));
                this.classList.add('active');
            }
        });

        // Set active class based on current URL
        if (link.getAttribute('href') === window.location.pathname) {
            link.classList.add('active');
        }
    });

    // Close other dropdowns when opening a new one
    hasSubItems.forEach(item => {
        item.querySelector('.sidebar-link').addEventListener('click', function() {
            hasSubItems.forEach(subItem => {
                if (subItem !== item) {
                    subItem.classList.remove('open');
                }
            });
        });
    });
});
    document.addEventListener('DOMContentLoaded', function() {
    const sidebarLinks = document.querySelectorAll('#sidebar .sidebar-link, #sidebar .submenu-item a');
    const normalizePath = path => path.replace(/\/+$/, '') || '/';
    const currentPath = normalizePath(window.location.pathname);

    sidebarLinks.forEach(link => {
        const href = link.getAttribute('href');
        if (href && href !== '#') {
            const targetPath = normalizePath(new URL(href, window.location.origin).pathname);
            const selected = currentPath === targetPath;
            const nestedRoute = targetPath !== '/' && currentPath.startsWith(`${targetPath}/`);

            if (selected || nestedRoute) {
                link.classList.add('active');
                if (selected) {
                    link.setAttribute('aria-current', 'page');
                }

                link.closest('.sidebar-item')?.classList.add('active');
                const parentMenu = link.closest('.has-sub');
                if (parentMenu) {
                    parentMenu.classList.add('active', 'open');
                    const parentLink = parentMenu.querySelector(':scope > .sidebar-link');
                    parentLink?.classList.add('active');
                    parentLink?.setAttribute('aria-expanded', 'true');
                }
            }
        }

        link.addEventListener('click', function() {
            sidebarLinks.forEach(sidebarLink => sidebarLink.classList.remove('active'));
            this.classList.add('active');
        });
    });
});

    // for appling tooltip
    // $(function () {
    //     $('[data-toggle="tooltip"]').tooltip()
    // });

    $(document).ready(function() {
        $('#notification-icon').click(function() {
            $('#meetingDetails').toggleClass('active');
            //call the notification function when the notification icon is clicked
            notification();
        });
        // call the notification function
        notification();
        $('#close-sidebar').click(function() {
            $('#meetingDetails').removeClass('active');
        });
        
        $(document).click(function(event) {
            var $target = $(event.target);
            if (!$target.closest('#notification-icon').length && 
                !$target.closest('#meetingDetails').length && 
                $('#meetingDetails').hasClass('active')) {
                $('#meetingDetails').removeClass('active');
            }
        });
    });

    function notification(){
        $.ajax({
            url: baseUrl+'notification/notificationDetails', 
            method: 'GET',
            dataType: 'json', // Expect a JSON response
            success: function(response) {
                // Assuming the response contains a "meetings" array
                if (response && response.meetings.length > 0 || response.sprints.length > 0) {
                    $('#meeting-filters').show();
                    renderMeetings(response);
                    $('#no-meetings-message').hide();
                    $('#meetings').click();
                } else {
                    $('#meeting-list').empty();
                    $('#meeting-count').remove();
                    $('#meeting-filters').hide();
                    $('#no-meetings-message').show();
                }
            },
            error: function() {
                $('#no-meetings-message').text('Failed to load meetings').show();
            }
        });
    }

        // for render the user meetings
        function renderMeetings(response) {
            let noMeetings = true;
            let noSprints = true;
            const count = `<span class="badge badge-pill badge-danger" id="meeting-count">
                            ${(response.meetings.length+response.sprints.length)>9?'9+':`${response.meetings.length+response.sprints.length}`}
                        </span>`;

            $('#notification-icon').append(count);
        
            $('#meeting-list').empty();

            response.meetings.forEach(function(meeting) {
                // check that is an valid url or not 
                const isValid = isValidUrl(meeting.meeting_link);

                let meetingHtml = `
                    <li>
                        <div class="d-flex justify-content-between">
                            <div>
                                <strong class="text-dark">
                                    ${formatDate(meeting.meeting_start_date)} ${timeFormat(meeting.meeting_start_time)}
                                </strong>
                            </div>
                            <div class="text-dark">
                                ${meeting.product_name}
                            </div>
                        </div>
                        <div class="d-flex justify-content-between">
                            <div class="text-dark">
                                ${meeting.meeting_type_name}
                            </div>
                            <i class="fas fa-chevron-down toggle-icon d-flex justify-content-center"></i>
                        </div>
                        <div class="row">
                            ${meeting.meeting_link ? 
                                isValid ? 
                                    `<a href="${meeting.meeting_link}" target="_blank" class="meeting-link">${meeting.meeting_link}</a>`:
                                    `<div class="meeting-link text-dark">${meeting.meeting_link}</div>` :
                                '<div class="meeting-link text-dark">No link found</div>'
                            }
                        </div>
                    </li>
                `;
                noMeetings = false;
                $('#meeting-list').append(`<div class="meeting">${meetingHtml}</div>`);
            });

            response.sprints.forEach(function(sprint) {

                let sprintHtml = `
                    <form action="${baseUrl}sprint/navsprintview" class="pointer" method="get">
                        <input type="text" name="sprint_id" value="${sprint.sprint_id}" hidden>
                        <button type="submit" class="cls-upcoming-sprint-btn">
                            <li>
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <strong class="text-dark cls-no-underline">
                                            ${formatDate(sprint.start_date)} 
                                        </strong>
                                    </div>
                                    <div class="text-dark">
                                        ${sprint.product_name}
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <div class="text-dark">
                                        Sprint ${sprint.sprint_version}
                                    </div>
                                    <div class="text-dark">
                                        ${sprint.activity}
                                    </div>
                                </div>
                            </li>
                        </button>
                    </form>
                `;
                noSprints = false;
                $('#meeting-list').append(`<div class="upcoming-sprints-list">${sprintHtml}</div>`);
            });

            if (noMeetings) {
                $('#meeting-list').append('<div class="card mt-3 p-2 meeting"><h6 class="text-center text-dark">No meetings available</h6></div>');
            }

            if (noSprints) {
                $('#meeting-list').append('<div class="card mt-3 p-2 upcoming-sprints-list"><h6 class="text-center text-dark">No upcoming scrum available</h6></div>');
            }

            // for showing and  hiding meeting links
            document.querySelectorAll('.toggle-icon').forEach(icon => {
                icon.addEventListener('click', () => {
                    const link = icon.closest('li').querySelector('.meeting-link');
                    if (link.style.display === 'none' || link.style.display === '') {
                        link.style.display = 'block';
                        icon.classList.remove('fa-chevron-down');
                        icon.classList.add('fa-chevron-up');
                    } else {
                        link.style.display = 'none';
                        icon.classList.remove('fa-chevron-up');
                        icon.classList.add('fa-chevron-down');
                    }
                });
            });
        }

        function isValidUrl(string) {
            try {
                new URL(string);
                return true;
            } catch (_) {
                return false;
            }
        }

    $(".navbar-nav li .notification-icon").click(function(){
    $(this).parents('.navbar-nav').find(".panel").slideDown(200)
})

        //remove the loader when the DOM loaded
        document.addEventListener("DOMContentLoaded", function() {
            const loader = document.getElementById('loader');
            
            loader.style.display = 'none';

            // Function to remove the base URL from a given path
            function stripBaseUrl(url, baseUrl) {
                if (url.startsWith(baseUrl)) {
                    return url.slice(baseUrl.length);
                }
                return url;
            }

            // // Get the current URL path
            // var currentPath = window.location.href;

            // // Get the current URL path with menu
            // var currentPathMenu = stripBaseUrl(currentPath, baseUrl).split('/')[0];
            
            // // Get all the sidebar links
            // var sidebarLinks = document.querySelectorAll(".sidebar-link");

            // // Loop through the links and add the 'active' class to the matching link
            // sidebarLinks.forEach(function(link) {
            //     // Ignore links with href="#"
            //     if (link.getAttribute('href') === '#') {
            //         return;
            //     }
            //     // Get the pathname from the link's href attribute and strip the base URL
            //     var linkPath = stripBaseUrl(link.href, baseUrl).split('/')[0];

            //     if (linkPath === currentPathMenu) {
            //         link.classList.add("active");
            //     }
            // });

            // // Handle submenu items
            // var submenuLinks = document.querySelectorAll(".submenu-item a");
            // submenuLinks.forEach(function(link) {
            //     if (link.href === currentPath) {
            //     // Open the submenu if an item inside is active
            //     link.closest(".has-sub").classList.add("open");
            //     link.closest(".submenu").classList.add("submenu-open");
            //     link.closest(".has-sub").querySelector(".sidebar-link").classList.add("active");
            //     link.closest(".submenu-item").classList.add("active");
            //     }
            // });

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


        // for apply filter for today and tomorrow meetings
        let today = document.getElementById('meetings');
        let upcomingSprints = document.getElementById('upcoming-scrum');

        meetings = document.getElementsByClassName('meeting');
        upcomingSprintsList = document.getElementsByClassName('upcoming-sprints-list');

        today.addEventListener('click',function(){
            for (let element of meetings) {
                element.style.display = "block";
            }
            for (let element of upcomingSprintsList) {
                element.style.display = "none";
            }
        });


        upcomingSprints.addEventListener('click',function(){
            for (let element of meetings) {
                element.style.display = 'none'
            }
            for (let element of upcomingSprintsList) {
                element.style.display = "block";
            }
        });


        // Fetch view
        // var baseUrl = '<?= base_url() ?>';
        // $(document).ready(function() {
            
        //     $('.view').on('click', function() {

        //         event.preventDefault();
        //         let productId = $(this).data('product-id');
        //         console.log(productId);
                
        //         if(typeof productId !== 'undefined'){
        //             console.log(productId);
        //             $.ajax({
        //                 url: baseUrl + 'dashboard/showProductDashboard',
        //                 type: 'POST',
        //                 dataType: 'json',
        //                 data: {
        //                     product_id: productId
        //                 },
        //                 success: function(response) {
        //                     // console.log(response.html);
        //                     $('#content').empty().html(response.html);
        //                     // $('#content').empty().append($(response.html));
        //                     ppnew();
                           
        //                 },
        //                 error: function(xhr, status, error) {
        //                     // Handle errors
        //                     console.error('An error occurred: ' + error);
        //                 }
        //             });
        //         }
        //     });
        // });

        
</script>

<!-- <script src="<?= ASSERT_PATH ?>dist/js/bundle.min.js"></script> -->

<?php if (isset($view)) {
    echo "<script type='text/javascript' src='" . JS_PATH . $view . ".js'></script>";
} ?> 
</body>

</html>