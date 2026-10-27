if (typeof m_tasks !== 'undefined') {

    document.addEventListener("DOMContentLoaded", () => {
        const filterBtn = document.getElementById("filter_btn");
        const closeFilterSidebarBtn = document.getElementById("closeFilterSidebarBtn");
        const filterSidebar = document.getElementById("filterSidebar");

        // Open the filter sidebar
        filterBtn.addEventListener("click", () => {
            filterSidebar.classList.add("open");
        });

        // Close the filter sidebar
        closeFilterSidebarBtn.addEventListener("click", () => {
            filterSidebar.classList.remove("open");
        });

        // Optionally close the sidebar when clicking outside of it
        document.addEventListener("click", (event) => {
            if (!filterSidebar.contains(event.target) && event.target !== filterBtn) {
                filterSidebar.classList.remove("open");
            }
        });

        // Handling filter form submission
        const filterForm = document.getElementById("filterOptionsForm");
        if (filterForm) {
            filterForm.addEventListener("submit", (event) => {
                event.preventDefault(); // Prevent form submission
            });
        }

        // serach
        const searchBox = document.querySelector('.search-box');
        const searchBtn = document.getElementById('search_btn');
        const searchInput = document.getElementById('backlogSearchInput');

        searchBtn.addEventListener('click', function (e) {
            e.preventDefault();
            searchBox.classList.toggle('expanded');
            if (searchBox.classList.contains('expanded')) {
                searchInput.focus();
            }
        });

        // Close search box when clicking outside
        document.addEventListener('click', function (e) {
            if (!searchBox.contains(e.target)) {
                searchBox.classList.remove('expanded');
            }
        });


    });

    (() => {
        'use strict';
        const forms = document.querySelectorAll('.needs-validation');
        const completedPercentage = document.getElementById('completed_percentage');

        completedPercentage.addEventListener('input', function () {
            if (this.value > 100) {
                this.setCustomValidity('Maximum completed percentage is 100');
                this.classList.add('is-invalid');
                this.classList.remove('is-valid');
            } else {
                this.setCustomValidity('');
                this.classList.add('is-valid');
                this.classList.remove('is-invalid');
            }
        });

        // Loop over them and prevent submission
        Array.from(forms).forEach(form => {
            form.addEventListener('submit', event => {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }

                form.classList.add('was-validated');
            }, false);
        });
    })();

    function addform(name, id) {
        $("#addTaskModal").modal("show");
        taskId = id; // Assuming taskId is a global variable or defined elsewhere

        if (name === "add") {
            document.getElementById("taskDetailsForm").reset();
            document.getElementById("add_tasks").innerText = "Add task";
            document.getElementById("submitTaskBtn").innerText = "Add";
            document.getElementById("formOperation").value = "insertTask"; // Set the value to "insertTask"
        } else if (name === "edit") {
            document.getElementById("taskDetailsForm").reset();
            document.getElementById("add_tasks").innerText = "Edit Task";
            document.getElementById("submitTaskBtn").innerText = "Update task";

            $.ajax({
                url: assertPath + "backlog/getTaskById/" + taskId,
                method: "POST",
                dataType: "json",
                success: function (response) {
                    console.log(response);
                    if (response) {
                        $("#task_title").val(response[0].task_title);
                        $("#task_description").val(response[0].task_description);
                        $("#task_tracker").val(response[0].tracker_id);
                        $("#task_statuses").val(response[0].task_status);
                        $("#task_priority").val(response[0].priority);
                        $("#task_assignee").val(response[0].assignee_id);
                        if (response[0].start_date === null) {
                            strtDate = '';
                        } else {
                            strtDate = response[0].start_date.trim().split(" ")[0];
                        }
                        $("#start_date").val(strtDate);
                        if (response[0].end_date === null) {
                            endDate = '';
                        } else {
                            endDate = response[0].end_date.trim().split(" ")[0];
                        }
                        $("#end_date").val(endDate);
                        $("#completed_percentage").val(response[0].completed_percentage);
                        $("#estimated_time").val(response[0].estimated_hours);
                        // alert(response[0].estimated_hours);
                        // console.log(strtDate);
                        // console.log(endDate);
                        // console.log(response[0].completed_percentage);
                        // console.log(response[0].estimated_hours);

                        document.getElementById("formOperation").value = "updateTask"; // Set the value to "updateTask"
                    } else {
                        console.error("Invalid response data:", response);
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.error("AJAX request failed:", textStatus, errorThrown);
                },
            });
        }
    }


    $(document).ready(function () {
        $("#taskDetailsForm").on("submit", function (e) {
            e.preventDefault();
            if (!this.checkValidity()) {
                e.stopPropagation();
                return false;
            }
            let formData = $(this).serialize();
            formData += "&taskbutton=1";
            let operation = $("#formOperation").val();
            console.log(operation);
            let url =
                operation === "insertTask"
                    ? assertPath + "backlog/addTasks/" + pid + "/" + pblid + "/" + userStoryId
                    : assertPath + "backlog/updateTaskById/" + pid + "/" + pblid + "/" + taskId;
            $.ajax({
                url: url,
                method: "POST",
                data: formData,
                dataType: "json",
                success: function (response) {
                    console.log(response);
                    if (response.success) {
                        $("#addTaskModal").modal("hide");
                        $(".modal-backdrop").remove();
                        Swal.fire({
                            title: "Success",
                            text: response.message ||
                                (operation === "insertTask"
                                    ? "Task successfully added."
                                    : "Task successfully updated."),
                            icon: "success",
                            confirmButtonText: "OK",
                        }).then((result) => {
                            if (result.isConfirmed) {
                                location.reload();
                            }
                        });
                    } else if (response.error) {
                        // Handle server-side validation errors
                        let errorMessage = '';
                        for (const [field, message] of Object.entries(response.error)) {
                            errorMessage += `<strong>${field}:</strong> ${message}<br>`;
                        }
                        Swal.fire({
                            title: "Validation Error!",
                            html: errorMessage,
                            icon: "warning",
                            confirmButtonText: "OK",
                        });
                    }
                },
                error: function () {
                    // Handle AJAX error
                    Swal.fire({
                        title: "Error",
                        text: "An unexpected error occurred while processing your request.",
                        icon: "error",
                        confirmButtonText: "OK",
                    });
                },
            });
        });
    });




    document.addEventListener('DOMContentLoaded', function (event) {
        const rowsPerPage = 10; // Number of tasks to display per page
        let currentPage = 1;
        // let filteredTasks = tasks; // Assume tasks is already defined and contains all task details
        let page = 1;
        filterTable();

        function displayTasks(filteredTasks) {
            // console.log("filtered tasks");
            // console.log(filteredTasks);
            if (filteredTasks == null) {
                filteredTasks = [];
            }

            const startIndex = (page - 1) * rowsPerPage;
            const endIndex = startIndex + rowsPerPage;
            tasksToDisplay = filteredTasks.slice(startIndex, endIndex);
            const taskContainer = document.getElementById('taskList');
            taskContainer.innerHTML = tasksToDisplay.map(task => createTaskCard(task)).join('');
        }

        function createTaskCard(task) {
            const startDate = task.start_date ? task.start_date.trim().split(" ")[0] : "-";
            const endDate = task.end_date ? task.end_date.trim().split(" ")[0] : "-";
            return `
            <div class='col-md-12'>
                <div class='card cls-task-card'>
                    <div class='card-header cls-task-header'>
                        <span class='ox-id-chip' title='Redmine ID'>#${task.external_id ?? '—'}</span>
                        <div class='card-title cls-task-header-details'>${task.task_title}</div>
                        <span class='ox-status-pill'>${task.task_status}</span>
                        <span class='cls-priority priority-box ${task.priority.toLowerCase()}'>${task.priority}</span>
                        <div class='cls-action-buttons'>
                            ${userPermissions.updateTask ? `
                            <button type='button' class='btn btn-link editButton ox-icon-action' data-bs-toggle="modal" data-bs-target="#addTaskModal" onclick="addform('edit', ${task.external_id})" data-toggle="tooltip" data-placement="top" title="Update" aria-label="Update">
                                <i class='icon-edit-2'></i>
                            </button>`: ``}
                            ${userPermissions.deleteTask ? `
                            <button type='button' class='btn btn-link ox-icon-action is-danger' onclick='deleteTask(${task.external_id})' data-toggle="tooltip" data-placement="top" title="Delete" aria-label="Delete">
                                <i class='icon-trash-2'></i>
                            </button>
                            `: ``}
                        </div>
                    </div>
                    <div class='card-body-content cls-task-body'>
                        <dl class='ox-fields'>
                            <div><dt>Assignee</dt><dd>${task.first_name}</dd></div>
                            <div><dt>Tracker</dt><dd>${task.tracker_name}</dd></div>
                            <div><dt>Start date</dt><dd>${startDate}</dd></div>
                            <div><dt>End date</dt><dd>${endDate}</dd></div>
                            <div><dt>Estimated hours</dt><dd>${task.estimated_hours}</dd></div>
                            <div><dt>Completed</dt><dd>
                                <span class='ox-mini-progress'><span style='width:${Math.min(100, Math.max(0, Number(task.completed_percentage) || 0))}%'></span></span>
                                ${task.completed_percentage}%
                            </dd></div>
                        </dl>
                        <p class='ox-task-desc'>${task.task_description}</p>
                    </div>
                </div>
            </div>
        `;
        }


        function updatePaginationControls() {
            const totalPages = Math.ceil(totalCount / rowsPerPage);
            document.getElementById(
                "pageInfo"
            ).textContent = `Page ${currentPage} of ${totalPages}`;
            document.getElementById("pageInfo").textContent = `Page ${currentPage} of ${totalPages}`;
            document.getElementById("prevPage").style.visibility = currentPage > 1 ? "visible" : "hidden";
            document.getElementById("nextPage").style.visibility = currentPage < totalPages ? "visible" : "hidden";
        }

        // Event listeners for pagination controls
        document.getElementById("prevPage").addEventListener("click", () => {
            if (currentPage > 1) {
                currentPage--;
                filterTable();
            }
        });

        document.getElementById("nextPage").addEventListener("click", () => {
            const totalPages = Math.ceil(totalCount / rowsPerPage);
            if (currentPage < totalPages) {
                currentPage++;
                filterTable();

            }
        });


        function filterTable() {

            const filterSidebar = document.getElementById("filterSidebar");
            filterSidebar.classList.remove('open');
            
            let filter = {
                UId: userStoryId,
                priorityFilter: $("#filterPriority").val(),
                statusFilter: $("#filterstatus").val(),
                search: $("#backlogSearchInput").val(),
                limit: rowsPerPage,
                offset: (currentPage - 1) * rowsPerPage
            };
            $.ajax({
                url: assertPath + "backlog/filterTasks",
                type: "POST",
                headers: { "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')?.content || "" },
                data: JSON.stringify({ filter: filter }),
                contentType: "application/json",
                dataType: "json",
                success: function (response) {
                    if (response) {
                        displayTasks(response.data)
                        updatePaginationControls();
                        countNumberOfFilters();
                    } else {
                        console.error("Invalid response format");
                    }
                    if (response.data == 0) {
                        document.getElementById('main-page').style.display = "none";
                        document.getElementById('empty').style.display = "block";
                    } else {
                        document.getElementById('main-page').style.display = "block";
                        document.getElementById('empty').style.display = "none";
                    }
                },
                error: function (xhr, status, error) {
                    console.error("AJAX Error:", status, error);
                    console.error("Response Text:", xhr.responseText);
                },
            });
        }
        $("#backlogSearchInput").on("input", filterTable);
        function resetFilters() {
            document.getElementById('filterPriority').value = '';
            document.getElementById('filterstatus').value = '';
            filterTable();
        }

        document.getElementById('resetFiltersBtn').addEventListener('click', resetFilters);
        $("#filterformAction").on("submit", function (event) {
            event.preventDefault(); // Prevent form submission
            filterTable(); // Apply filters
        });

        function countNumberOfFilters(){
            let numberOfFilters = document.getElementById("noti");
            numberOfFilters.style.display="block";
            let formData = $("#filterformAction").serializeArray();
            // console.log("formdata",formData);
            let filledFieldsCount = formData.filter(function(field) {
                return field.value.trim() !== "";
            }).length;
            if(filledFieldsCount>0){
                numberOfFilters.textContent = filledFieldsCount;
            }
            else{
                numberOfFilters.style.display="none";
            }
        }


    });


    var taskId = 0;
    document.addEventListener("DOMContentLoaded", function () {
        const labels = document.querySelectorAll("label");

        labels.forEach((label) => {
            const inputId = label.getAttribute("for");
            const inputElement = document.getElementById(inputId);

            // Check if the input element exists and if it has the 'required' attribute
            if (inputElement && inputElement.hasAttribute('required')) {
                label.classList.add("label-with-asterisk");
            }
        });
    });


    if (totalCount == 0) {
        document.getElementById("main-page").style.display = "none";
        document.getElementById("searchDropdown").style.display = "none";
        // document.getElementById("filterDropdown").style.display = "none";
        document.getElementById('empty').style.display = "block";
    }
    else {
        document.getElementById('empty').style.display = "none";
    }





    function deleteTask(id) {
        
        Swal.fire({
            title: "Are you sure?",
            text: "Deleted task can't be recovered",
            icon: "question",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes",
            cancelButtonText: "No",
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: assertPath + "backlog/deletetask/" + pid + "/" + pblid + "/" + id,
                    method: "POST",
                    dataType: "json",
                    success: function (response) {
                        if (response.success) {
                            // Assuming your backend responds with a success field
                            Swal.fire(
                                "Deleted!",
                                "Task has been deleted.",
                                "success"
                            ).then(() => {
                                window.location.href =
                                    assertPath +
                                    "backlog/tasks?pid=" +
                                    pid +
                                    "&pblid=" +
                                    pblid +
                                    "&usid=" +
                                    userStoryId;
                            });
                        } else {
                            console.error("Invalid response data:", response);
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        console.error("AJAX request failed:", textStatus, errorThrown);
                    },
                });
            } else {
                console.log("Deletion cancelled");
            }
        });
    }


    const startDatePicker = flatpickr("#start_date", {
        onChange: function (selectedDates, dateStr, instance) {
            endDatePicker.set('minDate', dateStr);
        }
    });
    const endDatePicker = flatpickr("#end_date", {
        minDate: "today" // or another default value if needed
    });


}