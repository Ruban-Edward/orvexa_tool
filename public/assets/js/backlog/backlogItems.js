// script.js

if (typeof m_backlogItems !== 'undefined') {

    document.addEventListener("DOMContentLoaded", () => {
        const filterBtn = document.getElementById("filter_btn");
        const closeFilterSidebarBtn = document.getElementById(
            "closeFilterSidebarBtn"
        );
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

        // serach box to get open
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

    // If the total count remains 0 then no backlog will be displayed
    if (totalCount == 0) {
        document.getElementById("refinement").style.display = "none";
        document.getElementById("mainPage").style.display = "none";
        document.getElementById("empty").style.display = "block";
    } else {
        document.getElementById("empty").style.display = "none";
    }

    // To difine the sort order
    let sortColumn = "order_by";
    let sortOrder = 1;
    let currentPage = 1;
    // rows displaying in a single page
    const rowsPerPage = 10;

    function validateFileInputs() {
        const fileUploadContainer = document.getElementById("fileUploadContainer");
        const fileInputs = fileUploadContainer.querySelectorAll('input[type="file"]');
        const fileTypeSelects = fileUploadContainer.querySelectorAll("select");

        let valid = true;

        fileInputs.forEach((fileInput, index) => {
            const fileTypeSelect = fileTypeSelects[index];

            if (fileTypeSelect.value && !fileInput.files.length) {
                fileInput.setCustomValidity(
                    "Please choose a file if a document type is selected."
                );
                fileInput.classList.add("is-invalid");
                valid = false;
            } else {
                fileInput.setCustomValidity(""); // Reset custom validity
                fileInput.classList.remove("is-invalid");
            }

            // Reset validity when a file is selected
            fileInput.addEventListener("change", () => {
                if (fileTypeSelect.value && fileInput.files.length) {
                    fileInput.setCustomValidity(""); // Clear custom validity
                    fileInput.classList.remove("is-invalid");
                }
            });
        });

        return valid;
    }

    function addFileInput() {
        const fileUploadContainer = document.getElementById("fileUploadContainer");
        const newIndex = fileUploadContainer.children.length;

        const newFileUploadDiv = document.createElement("div");
        newFileUploadDiv.className = "row g-3 align-items-center mb-3";
        newFileUploadDiv.innerHTML = `
        <div class="col-md-6">
            <label for="fileInput${newIndex}" class="form-label">Choose a file</label>
            <input type="file" name="fileInput[]" class="form-control" id="fileInput${newIndex}">
        </div>
        <div class="col-md-6">
            <label for="fileType${newIndex}" class="form-label">Document Type</label>
            <select name="fileType[]" class="form-select" id="fileType${newIndex}">
                <option value="BRD">BRD</option>
                <option value="Test Cases">Test Cases</option>
                <option value="Technical Document">Technical Document</option>
            </select>
        </div>
    `;
        fileUploadContainer.appendChild(newFileUploadDiv);
    }


    // Function to generate the table header
    function generateTableHeader(data) {
        const tableHeader = document.getElementById("tableHeader");
        tableHeader.innerHTML = "";
        const headerRow = document.createElement("tr");
        const columns = [{
            name: "backlog_item_name",
            label: "Name"
        },
        {
            name: "tracker",
            label: "Type"
        },
        {
            name: "customer_name",
            label: "Customer"
        },
        {
            name: "completed_stories",
            label: "Completed stories"
        },
        {
            name: "priority",
            label: "Priority"
        },
        {
            name: "status_name",
            label: "Status"
        },
        {
            name: "Action",
            label: "Action"
        },
        ];

        columns.forEach((column) => {
            const th = document.createElement("th");
            th.textContent = column.label;
            th.setAttribute("class", "text-center");
            th.classList.add("sorting");
            th.addEventListener("click", () => sortTable(column.name));
            headerRow.appendChild(th);
        });

        tableHeader.appendChild(headerRow);
    }

    // Function to dynamically generate the table body with pagination
    function generateTableBody(data) {
        const tableBody = document.getElementById("tableBody");
        tableBody.innerHTML = "";


        data.forEach((item) => {
            const row = document.createElement("tr");
            row.setAttribute("data-order", item.order_by); // Add order_by as data attribute for sortable
            row.setAttribute("data-backlog-id", item.backlog_id); // Add backlog_id as data attribute

            row.innerHTML =
                `
            <td class="capitalize-text text-center"><a href=` +
                assert_path +
                `backlog/backlogitemdetails?pid=` +
                pId +
                `&pblid=${item.backlog_item_id} " data-toggle="tooltip" data-placement="top" title="Click to view the details of backlog item ${item.backlog_item_name}">${item.backlog_item_name}</a></td>
            <td class="capitalize-text text-center" >${item.tracker}</td>
            <td class="capitalize-text text-center">${item.customer_name}</td>
            <td class="capitalize-text text-center">${item.completed}/${item.total}</td>
            <td class="capitalize-text text-center">
                <div class="priority-${item.priority}">${item.priority}</div>
            </td>          
             <td class="capitalize-text text-center">
                    <select class="status-select" onchange="updateStatus(this, ${item.backlog_item_id})">
                        ${statuses.map(s => `
                            <option value="${s.status_id}" ${item.r_module_status_id == s.status_id ? 'selected' : ''}>
                                ${s.status_name}
                            </option>
                        `).join('')}
                    </select>
                </td>
              <td data-label='Action' class='action_button custom-action-buttons'>
                  ${userPermissions.viewUserStories ? `
                      <a href='${assert_path}backlog/userstories?pid=${pId}&pblid=${item.backlog_item_id}'><i class="icon-book-open" data-toggle="tooltip" data-placement="top" title="View / add user stories"></i></a>
                  ` : ``}
                  
                  ${userPermissions.updateBacklog ? `
                      <a href="#" id="backlog" data-bs-toggle="modal" data-bs-target="#backlogModal" onclick="editbacklog(${item.backlog_item_id})" data-toggle="tooltip" data-placement="top" title="Update backlog">
                        <i class="icon-edit"></i>
                      </a>` : ``}
                    ${userPermissions.deleteBacklog ? `
                      <a href="#" onclick="confirmDelete(${item.backlog_item_id})"><i class="icon-trash" data-toggle="tooltip" data-placement="top" title="Delete"></i></a>` : ``}
                  
              </td>


        `;
            tableBody.appendChild(row);

        });
        updatePaginationControls(data);
    }

    let orderNum = 0;
    let order = '';

    // function to select whether the sort order is asc or desc in each click
    function sortTable(column) {
        orderNum++;
        order = orderNum % 2 == 0 ? 'asc' : 'desc';
        order = column + ' ' + order;
        console.log(order);
        filterTable();
    }

    // Function to filter the table data
    function filterTable() {
        const filterSidebar = document.getElementById("filterSidebar");
        filterSidebar.classList.remove('open');

        // All the req filter values are stored inside filter array
        let filter = {
            pid: pId,
            priorityFilter: $("#filterPriority").val(),
            statusFilter: $("#filterStatus").val(),
            BtypeFilter: $("#filterBacklogType").val(),
            custName: $("#filterCustName").val(),
            limit: rowsPerPage,
            offset: (currentPage - 1) * rowsPerPage,
            sort: order
        };
        if (totalCount > 0) {
            filter.searchQuery = $("#backlogSearchInput").val().toLowerCase();
        } else {
            filter.searchQuery = ''; // or remove this line if you want it undefined
        }
        console.log(filter);
        $.ajax({
            url: assert_path + "backlog/filterBacklogItem",
            type: "POST",
            headers: { "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')?.content || "" },
            data: JSON.stringify({
                filter: filter
            }),
            contentType: "application/json",
            dataType: "json",
            success: function (response) {
                console.log("Server Response:", response);
                if (response && response.data) {
                    if (response.data.length === 0) {                        $("#tableBody").html("<tr><td colspan='100%' class='text-center'>No Data Found</td></tr>");                        $("#mainPage").hide();                        $("#empty").show();
                    } else {                        $("#mainPage").show();                        $("#empty").hide();
                        // To call generatetable to display the array
                        generateTableBody(response.data);
                        // To Update the pagination 
                        updatePaginationControls(response.data);
                        // To show the number of filters applied on the button
                        countNumberOfFilters();
                    }
                } else {
                    console.error("Invalid response format");
                }
            },
            error: function (xhr, status, error) {
                console.error("AJAX Error:", status, error);
                console.error("Response Text:", xhr.responseText);
            },
        });
    }

    // To remove the applied filter
    function resetFilters() {
        document.getElementById('filterPriority').value = '';
        document.getElementById('filterStatus').value = '';
        document.getElementById('backlogSearchInput').value = '';
        document.getElementById('filterBacklogType').value = '';
        document.getElementById('filterCustName').value = '';
        filterTable();
    }
    document.querySelector('.apply-reset-filters-btn').addEventListener('click', resetFilters);


    // Function to update pagination controls
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
            console.log("prev");

        }
    });
    document.getElementById("nextPage").addEventListener("click", () => {
        const totalPages = Math.ceil(totalCount / rowsPerPage);
        if (currentPage < totalPages) {
            currentPage++;
            filterTable();

        }
    });

    // Event listeners for search and filter functionalities
    $("#backlogSearchInput").on("input", filterTable);
    $("#filterOptionsForm").on("submit", function (event) {
        event.preventDefault(); // Prevent form submission
        filterTable(); // Apply filters

    });
    // Call genertetableheader and filtertable automatically
    generateTableHeader();
    filterTable();

    // To show the number of filters applied on the button
    function countNumberOfFilters() {
        let numberOfFilters = document.getElementById("noti");
        numberOfFilters.style.display = "block";
        let formData = $("#filterOptionsForm").serializeArray();

        let filledFieldsCount = formData.filter(function (field) {
            return field.value.trim() !== "";
        }).length;
        if (filledFieldsCount > 0) {
            numberOfFilters.textContent = filledFieldsCount;
        }
        else {
            numberOfFilters.style.display = "none";
        }
    }

    // Function to define whether the model performs add or update function
    function addbacklog() {
        $("#modalTitle").text("Add backlog item");
        $("#submitBtn").text("Add");
        $("#addbacklogform").attr("action", "addbacklog");
        $("#backlog_item_id").val("");
        $("#addbacklogform")[0].reset();
        $("#fileList").empty(); // Clear file list
        $("#addbacklogform").removeClass("was-validated"); // Remove validation classes
        $("#backlogitemstatus").val("1");
        updateSelectOption();
    }

    // To modify the model to update function
    function editbacklog(pblId) {
        $("#modalTitle").text("Update backlog item");
        $("#submitBtn").text("Update");
        $("#addbacklogform").attr("action", "updatebacklog?pid=" + pId + "&pblid=" + pblId); // Set the form action to 'updatebacklog'
        $("#backlog_item_id").val(pblId); // Set the backlog item ID for update

        $.ajax({
            url: assert_path + "backlog/getbacklogItemById?pid=" + pblId, // Endpoint for fetching backlog item details
            method: "POST",
            success: function (response) {
                console.log("here");
                console.log(response);
                if (response) {
                    $("#productname").val(response.product_name);
                    $("#priority").val(response.priority);
                    $("#backlogitemname").val(response.backlog_item_name);
                    $("#priorityorder").val(response.backlog_order);
                    $("#backlogitemtype").val(response.r_tracker_id);
                    $("#tshirtsize").val(response.backlog_t_shirt_size);
                    $("#backlogitemcustomer").val(response.r_customer_id);
                    $("#backlogitemstatus").val(response.r_module_status_id);
                    $("#description").val(response.backlog_description);
                    // Populate files (if applicable)
                    $('#fileList').empty();
                    updateSelectOption();
                    if (response.files && response.files.length > 0) {
                        response.files.forEach(function (file) {
                            $('#fileList').append(`<li class="list-group-item">${file.document_name} - ${file.document_type}</li>`);
                        });
                    } else {
                        $('#fileList').append(`<li class="list-group-item">No files attached</li>`);
                    }
                } else {
                    console.error("Invalid response data:", response);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.error("AJAX request failed:", textStatus, errorThrown);
            },
        });
    }

    function updateSelectOption() {
        var statusSelect = document.getElementById('backlogitemstatus');
        var selectedValue = parseInt(statusSelect.value);
        console.log(selectedValue);
        Array.from(statusSelect.options).forEach(option => {
            var optionValue = parseInt(option.value);
            if (selectedValue > 8 && optionValue <= 8) {
                option.disabled = true;
                option.classList.add('disabled');
            } else {
                option.disabled = false;
                option.classList.remove('disabled');
            }
        });
    }

    // To delete the backlog 
    function confirmDelete(id) {
        // To get the details of the backlog
        $.ajax({
            url: assert_path + "backlog/getbacklogItemById?pid=" + id, // Replace with the correct endpoint to get the status
            method: "POST",
            success: function (response) {
                Swal.fire({
                    title: "Are you sure?",
                    text: "You won't be able to revert this!",
                    icon: "question",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Yes",
                    cancelButtonText: "No",
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: assert_path + "backlog/deletebacklogitem?pid=" + pId + "&pblid=" + id,
                            method: "POST",
                            success: function (response) {
                                // Handle success response
                                if (response.success) {
                                    Swal.fire(
                                        "Deleted!",
                                        response.message,
                                        "success"
                                    ).then((result) => {
                                        if (result.isConfirmed) {
                                            // Redirect to another page
                                            window.location.href =
                                                assert_path + "backlog/backlogitems?pid=" + pId;
                                        }
                                    });
                                } else {
                                    Swal.fire(
                                        "Warning!",
                                        response.message,
                                        "warning"
                                    ).then((result) => {
                                        if (result.isConfirmed) {
                                            // Redirect to another page
                                            window.location.href =
                                                assert_path + "backlog/backlogitems?pid=" + pId;
                                        }
                                    });
                                }
                            },
                            error: function (xhr, status, error) {
                                // Handle error
                                Swal.fire("Error!", "Something went wrong.", "error");
                            },
                        });
                    }
                });
                // }
            },
            error: function (xhr, status, error) {
                // Handle error
                Swal.fire(
                    "Error!",
                    "Something went wrong while fetching the status.",
                    "error"
                );
            },
        });
    }

    let fileInputCount = 0;

    // To add extra input fileds for file upload in add backlog for 
    function addFileInput() {
        const fileUploadContainer = document.getElementById("fileUploadContainer");

        const rowDiv = document.createElement("div");
        rowDiv.classList.add("row", "g-3", "align-items-center", "mb-3");
        rowDiv.setAttribute("id", "fileInputRow" + fileInputCount);

        const fileColDiv = document.createElement("div");
        fileColDiv.classList.add("col-md-6");
        const fileLabel = document.createElement("label");
        fileLabel.classList.add("form-label");
        fileLabel.setAttribute("for", "fileInput" + fileInputCount);
        fileLabel.innerText = "Choose a file";
        const fileInput = document.createElement("input");
        fileInput.classList.add("form-control");
        fileInput.setAttribute("type", "file");
        fileInput.setAttribute("name", "fileInput[]");
        fileInput.setAttribute("id", "fileInput" + fileInputCount);
        fileInput.setAttribute("required", "");
        fileColDiv.appendChild(fileLabel);
        fileColDiv.appendChild(fileInput);

        const typeColDiv = document.createElement("div");
        typeColDiv.classList.add("col-md-5");
        const typeLabel = document.createElement("label");
        typeLabel.classList.add("form-label");
        typeLabel.setAttribute("for", "fileType" + fileInputCount);
        typeLabel.innerText = "Document Type";
        const typeSelect = document.createElement("select");
        typeSelect.classList.add("form-select");
        typeSelect.setAttribute("name", "fileType[]");
        typeSelect.setAttribute("id", "fileType" + fileInputCount);
        fileInput.setAttribute("required", "");

        const option1 = document.createElement("option");
        option1.setAttribute("value", 1);
        option1.innerText = "BRD";
        const option2 = document.createElement("option");
        option2.setAttribute("value", 4);
        option2.innerText = "Test Cases";
        const option3 = document.createElement("option");
        option3.setAttribute("value", 2);
        option3.innerText = "Technical Document";
        typeSelect.appendChild(option1);
        typeSelect.appendChild(option2);
        typeSelect.appendChild(option3);
        typeColDiv.appendChild(typeLabel);
        typeColDiv.appendChild(typeSelect);

        const cancelColDiv = document.createElement("div");
        cancelColDiv.classList.add(
            "col-md-1",
            "d-flex",
            "align-items-center",
            "justify-content-center"
        );
        const cancelButton = document.createElement("button");
        cancelButton.classList.add("btn", "btn", "btn-sm");
        cancelButton.setAttribute("type", "button");
        cancelButton.setAttribute(
            "onclick",
            "removeFileInput(" + fileInputCount + ")"
        );
        cancelButton.innerText = "X";
        cancelColDiv.appendChild(cancelButton);

        rowDiv.appendChild(fileColDiv);
        rowDiv.appendChild(typeColDiv);
        rowDiv.appendChild(cancelColDiv);

        fileUploadContainer.appendChild(rowDiv);
        fileInputCount++;
    }

    function removeFileInput(index) {
        const rowDiv = document.getElementById("fileInputRow" + index);
        rowDiv.remove();
    }

    // (() => {
    //   "use strict";

    //   const forms = document.querySelectorAll(".needs-validation");

    //   Array.from(forms).forEach((form) => {
    //     form.addEventListener(
    //       "submit",
    //       (event) => {
    //         if (!validateFileInputs() || !form.checkValidity()) {
    //           event.preventDefault();
    //           event.stopPropagation();
    //           form.classList.add("was-validated");
    //         } else {
    //           event.preventDefault(); // Prevent the default form submission
    //           var submitButton = form.querySelector("#submitBtn");
    //           var actionMessage = submitButton.textContent.includes("Add")
    //             ? "Successfully added!"
    //             : "Successfully updated!";

    //           Swal.fire({
    //             title: "Success!",
    //             text: actionMessage,
    //             icon: "success",
    //             confirmButtonText: "OK",
    //           }).then(() => {
    //             form.submit(); // Proceed with form submission
    //           });
    //         }
    //         form.classList.add("was-validated");
    //       },
    //       false
    //     );
    //   });
    // })();

    (() => {
        "use strict";

        const forms = document.querySelectorAll(".needs-validation");

        Array.from(forms).forEach((form) => {
            form.addEventListener(
                "submit",
                (event) => {
                    event.preventDefault();

                    if (!validateFileInputs() || !form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                        form.classList.add("was-validated");
                    } else {
                        var submitButton = form.querySelector("#submitBtn");
                        var actionMessage = submitButton.textContent.includes("Add") ?
                            "Successfully added!" :
                            "Successfully updated!";

                        var formData = new FormData(form);

                        fetch(form.action, {
                            method: form.method,
                            headers: { "X-CSRF-TOKEN": form.querySelector("input[name=csrf_test_name]")?.value || "" },
                            body: formData
                        })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    Swal.fire({
                                        title: "Success!",
                                        text: data.message || actionMessage,
                                        icon: "success",
                                        confirmButtonText: "OK",
                                    }).then(() => {
                                        window.location.reload();
                                    });
                                } else {
                                    let errorMessage = "An error occurred.";
                                    if (data.error) {
                                        errorMessage = typeof data.error === 'object' ? Object.values(data.error).join("\n") : data.error;
                                    }
                                    Swal.fire({
                                        title: "Validation Error!",
                                        text: errorMessage,
                                        icon: "warning",
                                        confirmButtonText: "OK",
                                    });
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                Swal.fire({
                                    title: "Error!",
                                    text: "An unexpected error occurred.",
                                    icon: "error",
                                    confirmButtonText: "OK",
                                });
                            });
                    }
                    form.classList.add("was-validated");
                },
                false
            );
        });
    })();

    function validateFileInputs() {
        // Implement your file input validation logic here
        return true; // Return true if validation passes, false otherwise
    }

    document.addEventListener("DOMContentLoaded", function () {
        const labels = document.querySelectorAll("label");

        labels.forEach(label => {
            const inputId = label.getAttribute("for");
            const inputElement = document.getElementById(inputId);

            if (inputElement && !inputElement.readOnly) {
                label.classList.add("label-with-asterisk");
            }
        });
    });

    const startDatePicker = flatpickr("#startDate", {
        onChange: function (selectedDates, dateStr, instance) {
            endDatePicker.set('minDate', dateStr);
        }
    });
    const endDatePicker = flatpickr("#endDate", {
        minDate: "today" // or another default value if needed
    });

    // For backlog History
    let activities = [];
    let filteredActivities = [];
    let currentpage = 1;
    const perPageItem = 8;

    document.getElementById('backloghistory').addEventListener('click', loadActivities);
    document.getElementById('clearHistory').addEventListener('click', clearHistory);
    document.getElementById('filterButton').addEventListener('click', filterActivities);
    document.getElementById('history-previous').addEventListener('click', () => changePage(-1));
    document.getElementById('history-next').addEventListener('click', () => changePage(1));

    // Get the history details
    function loadActivities() {
        // let id = {
        //     pid: pId,
        // };
        // console.log(id);
        $.ajax({
            url: assert_path + "backlog/historydata",
            method: 'POST',
            dataType: 'json',
            data: JSON.stringify({
                pId: pId
            }),
            contentType: "application/json",
            dataType: "json",
            success: function (response) {
                console.log("history");
                console.log(response);
                for (let i = 0; i < response.data.length; i++) {
                    const [datePart, timePart] = response.data[i].action_date.split(' ');
                    activities.push({
                        date: datePart,
                        time: timePart,
                        icon: response.data[i].firstName.charAt(0),
                        source: response.data[i].firstName,
                        data: response.data[i].action_data,
                        title: response.data[i].module_name + '-' + ' '
                    });
                }

                filteredActivities = [...activities];
                displayActivities();
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.error('Error fetching data:', textStatus, errorThrown);
            }
        });
    }

    // to filter the history data
    function filterActivities() {
        const startDate = new Date(document.getElementById('startDate').value);
        const endDate = new Date(document.getElementById('endDate').value);

        filteredActivities = activities.filter(activity => {
            const activityDate = new Date(activity.date);
            return activityDate >= startDate && activityDate <= endDate;
        });

        currentpage = 1;
        displayActivities();
    }

    // To display the history activities
    function displayActivities() {
        const dataList = document.getElementById('dataList');
        dataList.innerHTML = '';

        if (filteredActivities.length === 0) {
            const noDataMessage = document.createElement('div');
            noDataMessage.className = 'no-data-message';
            noDataMessage.textContent = 'No data found';
            dataList.appendChild(noDataMessage);

            // Hide pagination when no data
            document.querySelector('.pagination').style.display = 'none';
            return;
        }

        // Show pagination when there's data
        document.querySelector('.pagination').style.display = 'flex';

        const startIndex = (currentpage - 1) * perPageItem;
        const endIndex = startIndex + perPageItem;
        const activitiesToDisplay = filteredActivities.slice(startIndex, endIndex);

        let currentDate = null;

        activitiesToDisplay.forEach((activity) => {
            if (activity.date !== currentDate) {
                currentDate = activity.date;
                const dateHeader = document.createElement('div');
                dateHeader.className = 'history-date';
                dateHeader.textContent = currentDate;
                dataList.appendChild(dateHeader);
            }

            const li = document.createElement('li');
            li.innerHTML = `
                <div class="activity-item">
                    <div class="activity-header">
                        <span class="time capitalize">${activity.time}</span>
                        <span class="icon capitalize">${activity.icon}</span>
                        <span class="title mt-3 capitalize">${activity.title}${activity.data} <p><span class="source">${activity.source}</span></p></span>
                    </div>
                </div>
            `;
            dataList.appendChild(li);
        });

        updatePaginationButtons();
    }

    // To update te pagination of History
    function updatePaginationButtons() {
        const totalPages = Math.ceil(filteredActivities.length / perPageItem);
        if (filteredActivities.length < perPageItem && currentPage === 1) {
            document.getElementById("history-previous").style.visibility = "hidden";
            document.getElementById("history-next").style.visibility = "hidden";
        }
        else if (currentpage === totalPages) {
            document.getElementById("history-next").style.visibility = "hidden";
            document.getElementById('history-previous').style.visibility = "visible";
        }
        else if (currentpage === 1) {
            document.getElementById("history-previous").style.visibility = "hidden";
            document.getElementById('history-next').style.visibility = "visible";
        } else {
            document.getElementById("history-previous").style.visibility = "visible";
            document.getElementById("history-next").style.visibility = "visible";
        }
    }
    
    // To change the page in history
    function changePage(direction) {
        const newPage = currentpage + direction;
        const totalPages = Math.ceil(filteredActivities.length / perPageItem);
        if (newPage >= 1 && newPage <= totalPages) {
            currentpage = newPage;
            displayActivities();
        }
    }
    // To clear the history each time it gets closed
    function clearHistory() {
        activities = [];
        filteredActivities = [];
        currentpage = 1;
        displayActivities();
    }

    document.getElementById('resetButton').addEventListener('click', resetActivities);

    // To rest the filter applied
    function resetActivities() {
        filteredActivities = [...activities];
        currentpagee = 1;

        // Clear filter inputs
        document.getElementById('startDate').value = '';
        document.getElementById('endDate').value = '';

        displayActivities();
    }

    // To update the status of the backlog
    function updateStatus(select, itemId) {
        const newStatusId = select.value;
        const newStatusName = select.options[select.selectedIndex].text;
        console.log(newStatusId + " " + itemId);
        Swal.fire({
            icon: 'question',
            title: 'Are you sure?',
            text: "Do you want to change the status?",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: 'Yes',
            cancelButtonText: 'No'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: assert_path + "backlog/updatebacklog",
                    method: 'POST',
                    data: {
                        item_id: itemId,
                        status_id: newStatusId
                    },
                    dataType: 'json',
                    success: function (response) {
                        if (response.success) {
                            // Show success message with SweetAlert2
                            Swal.fire({
                                icon: 'success',
                                title: 'Status Updated',
                                text: `Status for item ${itemId} updated to ${newStatusName}`,
                                confirmButtonText: 'Ok'

                            }).then((result) => {
                                if (result.isConfirmed) {
                                    location.reload();
                                }
                            });
                            console.log(`Status for item ${itemId} updated to ${newStatusName}`);
                        } else {
                            // Show error message with SweetAlert2
                            Swal.fire({
                                icon: 'warning',
                                title: 'Update Failed',
                                text: response.message,
                            }).then(() => {
                                location.reload();
                            });

                        }
                    },
                    error: function (xhr, status, error) {
                        // Show error message with SweetAlert2
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An error occurred while updating status. Please try again.',
                        });
                        console.error('Error occurred while updating status.');
                        console.error('Status:', status);
                        console.error('Error:', error);
                        console.error('Response Text:', xhr.responseText);
                    }
                });
            } else {
                location.reload();
            }
        });
    }
}