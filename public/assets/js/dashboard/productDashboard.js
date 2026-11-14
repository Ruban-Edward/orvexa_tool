// Orvexa chart palette (validated for colour-blind separation and 3:1 contrast on white).
// Categorical order is fixed: green, blue, amber, violet, rose.
const OX_CHART = {
    green: '#1f9450',
    greenFill: 'rgba(31, 148, 80, 0.12)',
    blue: '#2f6fd1',
    amber: '#b7741a',
    violet: '#7b5bd6',
    rose: '#d1495b',
    neutral: '#6f7c74',
    categorical: ['#1f9450', '#2f6fd1', '#b7741a', '#7b5bd6', '#d1495b'],
};

// Read a theme token (tokens.css switches them for the dark theme)
function oxToken(name, fallback) {
    return getComputedStyle(document.documentElement).getPropertyValue(name).trim() || fallback;
}
// Slice and point separators follow the card surface. Scriptable, so the
// chart.update() that layout/ux.js runs on a theme switch picks up the new colour.
const oxSurface = () => oxToken('--ox-surface', '#ffffff');

// Message on a chart that has no data: sized to the screen (sharp on HiDPI),
// muted text colour, redrawn when the canvas resizes or the theme switches.
function oxChartEmpty(canvas, text) {
    if (!canvas) return;
    canvas.style.width = '100%';
    canvas.style.height = '200px';
    const draw = () => {
        const dpr = window.devicePixelRatio || 1;
        const w = canvas.clientWidth;
        const h = canvas.clientHeight;
        if (!w || !h) return;
        canvas.width = Math.round(w * dpr);
        canvas.height = Math.round(h * dpr);
        const ctx = canvas.getContext('2d');
        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
        ctx.clearRect(0, 0, w, h);
        ctx.fillStyle = oxToken('--ox-muted', '#5e6e64');
        ctx.font = '500 14px ' + getComputedStyle(document.body).fontFamily;
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText(text, w / 2, h / 2);
    };
    draw();
    if (typeof ResizeObserver !== 'undefined') new ResizeObserver(draw).observe(canvas);
    document.addEventListener('ox:themechange', draw);
}
if (typeof Chart !== 'undefined') {
    Chart.defaults.font.family = getComputedStyle(document.body).fontFamily;
    // Theme-aware: read the tokens (layout/ux.js updates them when the theme switches)
    Chart.defaults.color = getComputedStyle(document.documentElement).getPropertyValue('--ox-muted').trim() || '#5e6e64';
    Chart.defaults.borderColor = getComputedStyle(document.documentElement).getPropertyValue('--ox-border').trim() || '#e3eadc';
}


if(typeof productDashboard !== 'undefined') {
    
    $(document).ready(function() {
        $('#currentSprint-submit').on('click', function() {
            $('[data-toggle="tooltip"]').tooltip();
            $(this).submit();
        });
    });

    $(document).ready(function() {
        $('#backlog-submit').on('click', function() {
            $('[data-toggle="tooltip"]').tooltip();
            $(this).submit();
        });
    });

    if (typeof userStoryCompletionHours !== 'undefined' && userStoryCompletionHours.length > 0) {
        var labels = userStoryCompletionHours.map(function(_, index) { return 'US' + (index + 1); });
        var completedHours = userStoryCompletionHours.map(function(story) { return parseFloat(story.total_hours); });

        var storyCompletionctx = document.getElementById('storyCompletionChart').getContext('2d');
        new Chart(storyCompletionctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Completed Hours',
                    data: completedHours,
                    backgroundColor: OX_CHART.green,
                    borderRadius: 4,
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                scales: {
                    x: { 
                        title: {
                            display: true,
                            text: 'User Stories'
                        }
                    },
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Hours'
                        },
                        ticks: {
                            callback: function(value) {
                                return value.toFixed(1); 
                            }
                        }
                    }
                },
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: {
                        callbacks: {
                            title: function(tooltipItems) {
                                return 'User Story ' + userStoryCompletionHours[tooltipItems[0].dataIndex].user_story;
                            }
                        }
                    }
                }
            }
        });
    } else {
        oxChartEmpty(document.getElementById('storyCompletionChart'),
            sprintCounts >= 1 ? 'No sprint is currently active' : 'No sprints yet started');
    }



    //velocity chart
    if (document.getElementById('velocitySprintChart') && Object.keys(velocityChartData).length > 0) {
        const labels = Object.keys(velocityChartData).map(sprint => `Sprint ${sprint}`);
        const velocityData = Object.values(velocityChartData);

        const velocitySprintCtx = document.getElementById('velocitySprintChart').getContext('2d');

        new Chart(velocitySprintCtx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Velocity',
                    data: velocityData,
                    borderColor: OX_CHART.green,
                    backgroundColor: OX_CHART.greenFill,
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true,
                    pointRadius: 4,
                    pointBackgroundColor: OX_CHART.green,
                    pointBorderColor: oxSurface,
                    pointHoverBackgroundColor: oxSurface,
                    pointHoverBorderColor: OX_CHART.green
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Velocity'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom'
                    }
                }
            }
        });
    } else {
        oxChartEmpty(document.getElementById('velocitySprintChart'),
            sprintCounts >= 1 ? 'Check back after current sprint completion' : 'Check back after sprint completion for insights');
    }      

    // Backlog Progress Chart
    if (document.getElementById('backlogProgressChart') && backlogCounts.total > 0) {
        // Calculate percentages for each backlog status
        const total = backlogCounts.total || 0;
        const completedPercentage = total > 0 ? (backlogCounts.completed_backlogs / total) * 100 : 0;
        const notStartedPercentage = total > 0 ? (backlogCounts.not_started_backlogs / total) * 100 : 0;
        const onHoldPercentage = total > 0 ? (backlogCounts.on_hold_backlogs / total) * 100 : 0;
        const inProgressPercentage = total > 0 ? (backlogCounts.in_progress_backlogs / total) * 100 : 0;

        const backlogProgressCtx = document.getElementById('backlogProgressChart').getContext('2d');
        new Chart(backlogProgressCtx, {
            type: 'doughnut',
            data: {
                labels: ['Completed', 'In progress', 'Not in progress', 'On hold'],
                datasets: [{
                    data: [
                        completedPercentage,
                        inProgressPercentage,
                        notStartedPercentage,
                        onHoldPercentage
                    ],
                    // Completed, In progress, Not in progress, On hold
                    backgroundColor: [OX_CHART.green, OX_CHART.blue, OX_CHART.neutral, OX_CHART.amber],
                    borderColor: oxSurface,
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(tooltipItem) {
                                return tooltipItem.label + ': ' + tooltipItem.raw.toFixed(2) + '%';
                            }
                        }
                    },
                    datalabels: {
                        display: true,
                        color: '#FFFFFF',
                        formatter: function(value, context) {
                            // Skip empty slices so their labels don't pile up
                            return value > 0 ? value.toFixed(1) + '%' : '';
                        },
                        font: {
                            weight: 'bold',
                            size: 16,
                        }
                    }
                }
            },
            plugins: [ChartDataLabels]
        });
    } else {
        oxChartEmpty(document.getElementById('backlogProgressChart'), 'Backlog is empty');
    }

    // Sprint Hours Chart
    $(document).ready(function() {
        
        if (Object.keys(sprintDetails).length > 0 ) {
        
            var actualHours = sprintHoursPerDay.map(function(item) {
                return parseFloat(item.daily_actual_hours);
            });
            // Generate day labels 
            function generateDayLabels(startDate, endDate) {
                let currentDate = new Date(startDate);
                const end = new Date(endDate);
                const dayLabels = [];
                let dayCounter = 1;
            
                while (currentDate <= end) {
                    const dayOfWeek = currentDate.getDay();
                    if (  dayOfWeek !== 0) { // Skip Saturdays  and Sundays
                        dayLabels.push(`Day ${dayCounter}`);
                        dayCounter++;
                    }
                    currentDate.setDate(currentDate.getDate() + 1);
                }
                return dayLabels;
            }
            
            // Generate day labels 
            const dayLabels = generateDayLabels(sprintDetails.start_date, sprintDetails.end_date);
            
            var SprintHoursCtx = $('#sprintHoursChart')[0].getContext('2d');
            new Chart(SprintHoursCtx, {
                type: 'line',
                data: {
                    labels: dayLabels,
                    datasets: [{
                        label: 'Daily Sprint Hours',
                        data: actualHours,
                        borderColor: OX_CHART.green,
                        backgroundColor: OX_CHART.greenFill,
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            min: 0, 
                            title: {
                                display: true,
                                text: 'Spent Hours'
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const index = context.dataIndex; 
                                    const resourceCount = sprintHoursPerDay[index].resource_count; 
                                    const spentDate = sprintHoursPerDay[index].spent_date; 
                                    return [
                                        'Today Spent Hours: ' + context.parsed.y.toFixed(2),
                                        'Resource Count: ' + resourceCount,
                                        'Spent Date: ' + spentDate
                                    ];
                                }
                            }
                        },
                        legend: {
                            display: true,
                            position: 'bottom'
                        }
                    }
                }
            });
        } else {
            oxChartEmpty(document.getElementById('sprintHoursChart'),
                sprintCounts >= 1 ? 'No sprint is currently active' : 'No sprints yet started');
        }
    });

    var pendingTasksChart = null;
    $(document).ready(function() {
        $('#pendingTasksCard').click(function() {
            
            // Get the pending tasks container
            var $pendingTasksContainer = $('#pendingTasksContainer');

            // Check if the container is already visible
            if ($pendingTasksContainer.is(':visible')) {
                // Hide the container
                $pendingTasksContainer.hide();
                $('#pendingTasksStatus').show();
                
            } else {
            
                // Get sprint ID and product ID from custom attributes
                var sprintId = $(this).attr('p-sprint-id');
                var productId = $(this).attr('p-product-id');

                // AJAX request to fetch pending tasks
                $.ajax({
                    url: baseUrl + 'dashboard/pendingTasks',
                    method: 'POST',
                    data: {
                        sprint_id: sprintId,
                        product_id: productId
                    },
                    success: function(response) {
                        console.log(response);
                        var tasks = JSON.parse(response);
                        var htmlContent = '';

                        if (tasks.length > 0) {
                            htmlContent += '<div class="d-flex justify-content-between mb-3 px-2">';
                            htmlContent += '<h3 class="task-header mt-2">Pending Tasks</h3>';
                            htmlContent +='<div class="search-container d-flex mt-2">';
                            htmlContent +=    '<input type="text" id="searchInput" placeholder="Search tasks...">';
                            htmlContent += '</div>';
                            htmlContent += '</div>';
                            htmlContent += renderTasks(tasks);
                            updatePendingTasksChart(tasks);
                            
                        } else {
                            htmlContent = '<p class="text-center">No pending tasks available.</p>';
                        }

                        $pendingTasksContainer.show();

                        $('#pendingTasks').html(htmlContent).show();
                        initSearchFunctionality(tasks);

                        $pendingTasksContainer.fadeIn().addClass('show');
                        
                        // Scroll to the container
                        $('html, body').animate({
                            scrollTop: $pendingTasksContainer.offset().top-125
                        }, 500); 
                    },
                    error: function(xhr, status, error) {
                        console.log(xhr.responseText); 
                        Ox.toast('Pending tasks could not be loaded. Please try again.', { type: 'error' });
                    }
                });
            }
        });
    });

    function renderTasks(tasks) {
        var htmlContent = '';
        htmlContent += '<div class="task-list px-2">';
        tasks.forEach(function(task) {
            var priorityClass = '';
            
            if (task.priority === 'H') {
                priorityClass = 'text-danger'; 
            } else if (task.priority === 'M') {
                priorityClass = 'text-warning'; 
            } else if (task.priority === 'L') {
                priorityClass = 'text-primary'; 
            }

            htmlContent += '<div class="task-container p-3">';
            htmlContent += '    <div class="d-flex justify-content-between align-items-center">';
            htmlContent += '        <h5 class="mb-1 task-title ' + priorityClass + '">Task: ' + ( task.task_title ? task.task_title : 'N/A') + '</h5>';
            htmlContent += '        <p><strong>Status: </strong> <span class="task-status">' + task.status + '</span></p>';
            htmlContent += '    </div>';
            htmlContent += '    <div class="task-details mt-2 d-flex justify-content-between">';
            htmlContent += '        <p><i class="far fa-calendar-alt me-1"></i><strong>Start Date:</strong> ' + (task.start_date ? new Date(task.start_date).toLocaleDateString() : 'N/A') + '</p>';
            htmlContent += '        <p><i class="far fa-clock me-2"></i><strong>End Date:</strong> ' + (task.end_date ? new Date(task.end_date).toLocaleDateString() : 'N/A') + '</p>';
            htmlContent += '        <p><i class="fas fa-user me-2"></i><strong>Assignee:</strong> ' + ((task.Assignee ? task.Assignee : 'N/A')) + '</p>';
            htmlContent += '    </div>';
            htmlContent += '</div>';
        });
        htmlContent += '</div>';
        return htmlContent;
    }


    function initSearchFunctionality(tasks) {
        $('#searchInput').on('input', function() {
            var query = $(this).val().toLowerCase();
            var filteredTasks = tasks.filter(function(task) {
                return task.task_title.toLowerCase().includes(query) ||
                    (task.start_date && new Date(task.start_date).toLocaleDateString().includes(query)) ||
                    (task.end_date && new Date(task.end_date).toLocaleDateString().includes(query)) ||
                    task.Assignee.toLowerCase().includes(query) ||
                    task.status.toLowerCase().includes(query);
            });
            $('#pendingTasks .task-list').html(renderTasks(filteredTasks));
        });
    }
    function updatePendingTasksChart(tasks) {
        var statusCounts = {
            'OnHold': 0,
            'Assigned': 0,
            'Feedback': 0,
            'In Progress': 0,
            'New': 0
        };

        tasks.forEach(function(task) {
            if (statusCounts.hasOwnProperty(task.status)) {
                statusCounts[task.status]++;
            }
        });

        var ctx = document.getElementById('pendingTasksChart').getContext('2d');
        
        // Destroy existing chart instance if it exists
        if (pendingTasksChart !== null) {
            pendingTasksChart.destroy();
        }

        pendingTasksChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: Object.keys(statusCounts),
                datasets: [{
                    data: Object.values(statusCounts),
                    backgroundColor: OX_CHART.categorical,
                    borderColor: oxSurface,
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    datalabels: {
                        formatter: (value, context) => {
                            if (!value) return '';
                            let total = context.chart._metasets[0].total;
                            let percentage = (value / total * 100).toFixed(1) + '%';
                            return percentage;
                        },
                        color: '#FFFFFF',
                        font: {
                            weight: 'bold',
                            size: 16,
                        }
                    }
                }
            },
            plugins: [ChartDataLabels]
        });
    }








    // // Sprint Burndown Chart
    // $(document).ready(function() {
    //     console.log(sprintDetails);
    //     if (sprintDetails) {
    //         // Extract actual hours from the data
    //         var actualHours = burndownChartData.map(function(item) {
    //             return parseFloat(item.daily_actual_hours);
    //         });

    //         // Calculate cumulative actual hours
    //         var cumulativeActualHours = actualHours.reduce(function(acc, curr) {
    //             acc.push((acc.length > 0 ? acc[acc.length - 1] : 0) + curr);
    //             return acc;
    //         }, []);

    //         estimatedHours = estimatedHours.estimated_hours;
    //         // Calculate remaining work for actual burndown
    //         var totalEstimatedHours = parseFloat(estimatedHours) || 0;
    //         var actualBurndown = cumulativeActualHours.map(function(hours) {
    //             return totalEstimatedHours - hours;
    //         });

    //         // Generate day labels 
    //         function generateDayLabels(startDate, endDate) {
    //             let currentDate = new Date(startDate);
    //             const end = new Date(endDate);
    //             const dayLabels = [];
    //             let dayCounter = 1;
            
    //             while (currentDate <= end) {
    //                 const dayOfWeek = currentDate.getDay();
    //                 if (dayOfWeek !== 6 && dayOfWeek !== 0) { // Skip Saturdays (6) and Sundays (0)
    //                     dayLabels.push(`Day ${dayCounter}`);
    //                     dayCounter++;
    //                 }
    //                 currentDate.setDate(currentDate.getDate() + 1);
    //             }
    //             return dayLabels;
    //         }
            
    //         // Generate day labels excluding weekends
    //         const dayLabels = generateDayLabels(sprintDetails.start_date, sprintDetails.end_date);

    //         // Calculate ideal burndown
    //         var idealBurndown = dayLabels.map(function(_, index) {
    //             return totalEstimatedHours - (totalEstimatedHours / (dayLabels.length - 1)) * index;
    //         });

            
    //         var today = new Date().toISOString().split('T')[0];
    //         var todayHours = burndownChartData.find(item => item.spent_date === today-1)?.daily_actual_hours || 0;
    //         var totalActualHours = cumulativeActualHours[cumulativeActualHours.length - 1] || 0;
    //         var hoursRemaining = totalEstimatedHours - totalActualHours;
    //         var daysRemaining = dayLabels.length - cumulativeActualHours.length;
    //         var requiredRate = daysRemaining > 0 ? hoursRemaining / daysRemaining : 0;

            
    //         $('#todayHours').text(todayHours);
    //         $('#requiredRate').text(requiredRate.toFixed(2));
    //         $('#hoursRemaining').text(hoursRemaining.toFixed(1));
    //         $('#daysRemaining').text(daysRemaining);

    //         var averageDailyHours = totalEstimatedHours / dayLabels.length;
    //         var highRateThreshold = averageDailyHours * 1.2; 
            
    //         var $requiredRateElement = $('#requiredRate');
    //         $requiredRateElement.text(requiredRate.toFixed(2));
            
            
    //         if (requiredRate > highRateThreshold) {
    //             $requiredRateElement.addClass('text-danger');
    //         } else {
    //             $requiredRateElement.removeClass('text-danger');
    //         }

    //         var burndownCtx = $('#burndownChart')[0].getContext('2d');
    //         new Chart(burndownCtx, {
    //             type: 'line',
    //             data: {
    //                 labels: dayLabels,
    //                 datasets: [{
    //                     label: 'Ideal Burndown',
    //                     data: idealBurndown,
    //                     borderColor: 'rgb(0, 0, 128)',
    //                     backgroundColor: 'rgb(0, 0, 128,0.2)',
    //                     tension: 0.1,
    //                     fill: true
    //                 }, {
    //                     label: 'Actual Burndown',
    //                     data: actualBurndown,
    //                     borderColor: 'rgb(230, 18, 23)',
    //                     backgroundColor: 'rgb(0, 0, 128,0.3)',
    //                     tension: 0.1,
    //                     fill: true
    //                 }]
    //             },
    //             options: {
    //                 responsive: true,
    //                 scales: {
    //                     y: {
    //                         beginAtZero: true,
    //                         min: 0, 
    //                         title: {
    //                             display: true,
    //                             text: 'Remaining Work Hours'
    //                         }
    //                     }
    //                 },
    //                 plugins: {
    //                     tooltip: {
    //                         callbacks: {
    //                             label: function(context) {
    //                                 return 'Remaining Hours: ' + context.parsed.y.toFixed(1);
    //                             }
    //                         }
    //                     },
    //                     legend: {
    //                         display: true,
    //                         position: 'bottom'
    //                     }
    //                 }
    //             }
    //         });
    //     } else {
    //         const ctx = document.getElementById('burndownChart').getContext('2d');
    //         ctx.font = '20px poppins';
    //         ctx.fillStyle = 'black';
    //         ctx.textAlign = 'center';
    //         ctx.fillText('No ', ctx.canvas.width / 2, ctx.canvas.height / 2);
    //     }
    // });
// Variables for pagination
let currentPage = 1;
const rowsPerPage = 10;
// `data` is only rendered by the view when the product has sprints
const sprintRows = typeof data !== 'undefined' && Array.isArray(data) ? data : [];
const totalRows = sprintRows.length;
const totalPages = Math.ceil(totalRows / rowsPerPage);

// Function to display rows based on current page
function displayTableRows(page) {
    const tableBody = document.querySelector('#sprintTable tbody');
    tableBody.innerHTML = ''; // Clear previous rows

    const start = (page - 1) * rowsPerPage;
    const end = start + rowsPerPage;
    const paginatedItems = sprintRows.slice(start, end);

    paginatedItems.forEach(sprint => {
        const row = `<tr>
                        <td>${oxEscape(sprint.sprint_version || 'N/A')}</td>
                        <td>${oxEscape(sprint.sprint_name || 'N/A')}</td>
                        <td>${sprint.start_date ? formatDate(sprint.start_date) : 'N/A'}</td>
                        <td>${sprint.end_date ? formatDate(sprint.end_date) : 'N/A'}</td>
                        <td>${sprint.sprint_completed || 'N/A'}%</td>
                        <td>${sprint.sprint_duration || 'N/A'}</td>
                        <td><span class="badge ${getBadgeClass(sprint.sprint_status, sprint.sprint_completed)}">${getBadgeText(sprint.sprint_status, sprint.sprint_completed)}</span></td>
                    </tr>`;
        tableBody.innerHTML += row;
    });

    document.getElementById('pageInfo').innerText = `Page ${currentPage} of ${totalPages}`;
    document.getElementById("prevPage").style.visibility = currentPage > 1 ? "visible" : "hidden";
    document.getElementById("nextPage").style.visibility = currentPage < totalPages ? "visible" : "hidden";
}

// Event listeners for pagination controls
document.getElementById('prevPage')?.addEventListener('click', () => {
    if (currentPage > 1) {
        currentPage--;
        displayTableRows(currentPage);
    }
});

document.getElementById('nextPage')?.addEventListener('click', () => {
    if (currentPage < totalPages) {
        currentPage++;
        displayTableRows(currentPage);
    }
});

// Utility functions
function formatDate(dateStr) {
    const date = new Date(dateStr);
    return date.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
}

function getBadgeClass(status, completed) {
    if (status === 'Sprint Running') return 'text-primary';
    if (status === 'Sprint Planned') return 'text-warning';
    if (status === 'Sprint Completed' && completed < 100) return 'text-danger';
    if (status === 'Sprint Completed' && completed === 100) return 'primary';
    if (status === 'Sprint Review') return 'text-primary';
    if (status === 'Sprint Retrospective') return 'text-primary';
    return 'secondary';
}

function getBadgeText(status, completed) {
    if (status === 'Sprint Running') return 'ongoing';
    if (status === 'Sprint Planned') return 'upcoming';
    if (status === 'Sprint Completed' && completed < 100) return 'overdue';
    if (status === 'Sprint Completed' && completed === 100) return 'completed';
    if (status === 'Sprint Review') return 'In review';
    if (status === 'Sprint Retrospective') return 'In retrospective';
    return 'Unknown';
}

// Initial display
if (sprintRows.length && document.getElementById('pageInfo')) {
    displayTableRows(currentPage);
}


}
// Sprint burnup: cumulative hours logged (green) against the ideal pace (blue, dashed) and the
// estimated scope (neutral reference line). One axis, hours.
(function () {
    const source = document.getElementById('sprintBurnupData');
    const canvas = document.getElementById('sprintBurnupChart');
    if (!source || !canvas || typeof Chart === 'undefined') return;
    let data;
    try { data = JSON.parse(source.textContent); } catch (_) { return; }
    if (!data || !Array.isArray(data.labels) || !data.labels.length) return;

    const fmt = v => `${Math.round(v * 10) / 10} h`;
    new Chart(canvas.getContext('2d'), {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [
                {
                    label: 'Logged',
                    data: data.logged,
                    borderColor: OX_CHART.green,
                    backgroundColor: OX_CHART.greenFill,
                    fill: 'origin',
                    borderWidth: 2,
                    pointRadius: 0,
                    pointHoverRadius: 5,
                    pointHoverBorderColor: oxSurface,
                    pointHoverBorderWidth: 2,
                    spanGaps: false,
                    tension: 0,
                },
                {
                    label: 'Ideal',
                    data: data.ideal,
                    borderColor: OX_CHART.blue,
                    borderDash: [6, 4],
                    borderWidth: 2,
                    pointRadius: 0,
                    pointHoverRadius: 4,
                    fill: false,
                },
                {
                    label: 'Scope',
                    data: data.labels.map(() => data.scope),
                    borderColor: OX_CHART.neutral,
                    borderDash: [2, 3],
                    borderWidth: 2,
                    pointRadius: 0,
                    pointHoverRadius: 0,
                    fill: false,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? false : { duration: 200 },
            interaction: { mode: 'index', intersect: false },
            scales: {
                x: { grid: { display: false }, ticks: { maxRotation: 0, autoSkip: true, maxTicksLimit: 10 } },
                y: { beginAtZero: true, grid: { color: Chart.defaults.borderColor }, title: { display: true, text: 'Hours' } },
            },
            plugins: {
                legend: { position: 'top', align: 'end', labels: { usePointStyle: true, pointStyle: 'line', boxWidth: 24 } },
                datalabels: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => ctx.parsed.y === null ? null : `${ctx.dataset.label}: ${fmt(ctx.parsed.y)}`,
                    },
                },
            },
        },
    });
})();
