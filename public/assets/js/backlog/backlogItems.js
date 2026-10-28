// script.js

if (typeof m_backlogItems !== 'undefined') {

    const esc = Ox.esc;
    const filterSidebar = document.getElementById("filterSidebar");
    const filterBtn = document.getElementById("filter_btn");
    const searchInput = document.getElementById("backlogSearchInput");
    const filterSelects = () => [
        { el: document.getElementById("filterBacklogType"), group: "Type", onRemove: reloadBacklog },
        { el: document.getElementById("filterCustName"), group: "Customer", onRemove: reloadBacklog },
        { el: document.getElementById("filterPriority"), group: "Priority", onRemove: reloadBacklog },
        { el: document.getElementById("filterStatus"), group: "Status", onRemove: reloadBacklog },
    ];
    // Backlog filters are remembered per user and per product (customers/types differ by product)
    const filterStore = Ox.store("filters:backlog:" + pId);

    function setFilterPanel(open) {
        if (!filterSidebar) return;
        filterSidebar.classList.toggle("open", open);
        filterBtn?.setAttribute("aria-expanded", String(open));
    }
    filterBtn?.addEventListener("click", (event) => {
        event.stopPropagation();
        setFilterPanel(!filterSidebar.classList.contains("open"));
    });
    document.getElementById("closeFilterSidebarBtn")?.addEventListener("click", () => {
        setFilterPanel(false);
        filterBtn?.focus();
    });
    document.addEventListener("click", (event) => {
        if (filterSidebar?.classList.contains("open") && !filterSidebar.contains(event.target) && !event.target.closest("#filter_btn")) {
            setFilterPanel(false);
        }
    });

    const savedFilters = filterStore.load();
    if (savedFilters) {
        filterSelects().forEach(({ el }) => Ox.setSelectValues(el, savedFilters[el.id] || []));
        if (searchInput) searchInput.value = savedFilters.search || "";
    }

    if (totalCount == 0) {
        document.getElementById("refinement").style.display = "none";
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
        tableBody.innerHTML = data.map((item) => {
            const id = Number(item.backlog_item_id);
            const name = esc(item.backlog_item_name);
            return `<tr data-backlog-id="${id}">
                <td class="capitalize-text text-center"><a href="${assert_path}backlog/backlogitemdetails?pid=${pId}&pblid=${id}" title="View details of ${name}">${name}</a></td>
                <td class="capitalize-text text-center">${esc(item.tracker)}</td>
                <td class="capitalize-text text-center">${esc(item.customer_name)}</td>
                <td class="capitalize-text text-center">${Number(item.completed) || 0}/${Number(item.total) || 0}</td>
                <td class="capitalize-text text-center"><div class="priority-${esc(item.priority)}">${esc(item.priority)}</div></td>
                <td class="capitalize-text text-center">
                    <select class="status-select form-select form-select-sm" aria-label="Status of ${name}" onchange="updateStatus(this, ${id})">
                        ${statuses.map(s => `<option value="${Number(s.status_id)}" ${item.r_module_status_id == s.status_id ? "selected" : ""}>${esc(s.status_name)}</option>`).join("")}
                    </select>
                </td>
                <td data-label="Action" class="action_button custom-action-buttons">
                    ${userPermissions.viewUserStories ? `<a class="ox-icon-action" href="${assert_path}backlog/userstories?pid=${pId}&pblid=${id}" title="View or add user stories" aria-label="User stories of ${name}"><i class="icon-book-open" aria-hidden="true"></i></a>` : ""}
                    ${userPermissions.updateBacklog ? `<button type="button" class="btn btn-link ox-icon-action" onclick="editbacklog(${id})" title="Edit backlog item" aria-label="Edit ${name}"><i class="icon-edit" aria-hidden="true"></i></button>` : ""}
                    ${userPermissions.deleteBacklog ? `<button type="button" class="btn btn-link ox-icon-action is-danger" onclick="confirmDelete(${id})" title="Delete backlog item" aria-label="Delete ${name}"><i class="icon-trash" aria-hidden="true"></i></button>` : ""}
                </td>
            </tr>`;
        }).join("");
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

    const pager = Ox.pager(document.getElementById("paginationControls"), {
        scope: "backlog",
        onChange: () => filterTable(),
    });
    const values = (el) => el ? [...el.selectedOptions].map(o => o.value).filter(Boolean) : [];
    const hasFilters = () => filterSelects().some(({ el }) => values(el).length) || (searchInput && searchInput.value.trim() !== "");

    function reloadBacklog() {
        pager.reset();
        filterTable();
    }

    function resetFilters() {
        filterSelects().forEach(({ el }) => Ox.setSelectValues(el, []));
        if (searchInput) searchInput.value = "";
        reloadBacklog();
    }

    function renderFilterState() {
        const count = filterSelects().reduce((n, { el }) => n + values(el).length, 0);
        const badge = document.getElementById("noti");
        if (badge) {
            badge.textContent = count;
            badge.hidden = count === 0;
        }
        Ox.chips(document.getElementById("backlogChips"), Ox.chipsFor(filterSelects(), { el: searchInput, onRemove: reloadBacklog }), resetFilters);
        const state = { search: searchInput ? searchInput.value.trim() : "" };
        filterSelects().forEach(({ el }) => { state[el.id] = values(el); });
        filterStore.save(state);
    }

    function showEmpty() {
        const filtered = hasFilters();
        Ox.empty(document.getElementById("empty"), filtered ? {
            icon: "filter",
            title: "No backlog items match your filters",
            text: "Try removing a filter or changing your search.",
            actions: [{ label: "Clear filters", icon: "x", onClick: resetFilters }],
        } : {
            icon: "layers",
            title: "This product has no backlog items yet",
            text: "Add the first item to start planning work for this product.",
            actions: document.getElementById("backlog") ? [{ label: "Add first backlog item", icon: "plus", primary: true, onClick: () => document.getElementById("backlog").click() }] : [],
        });
    }

    let backlogRequest = null;
    function filterTable() {
        setFilterPanel(false);
        renderFilterState();
        backlogRequest?.abort();
        const filter = {
            pid: pId,
            priorityFilter: values(document.getElementById("filterPriority")),
            statusFilter: values(document.getElementById("filterStatus")),
            BtypeFilter: values(document.getElementById("filterBacklogType")),
            custName: values(document.getElementById("filterCustName")),
            searchQuery: searchInput ? searchInput.value.trim().toLowerCase() : "",
            limit: pager.size,
            offset: pager.offset,
            sort: order,
        };
        backlogRequest = $.ajax({
            url: assert_path + "backlog/filterBacklogItem",
            type: "POST",
            data: JSON.stringify({ filter }),
            contentType: "application/json",
            dataType: "json",
            success: function (response) {
                const rows = response?.data || [];
                const total = Number(response?.total) || 0;
                generateTableBody(rows);
                pager.render(total);
                $("#mainPage").toggle(total > 0);
                $("#empty").toggle(total === 0);
                if (total === 0) showEmpty();
            },
            error: function (xhr, status) {
                if (status !== "abort") Ox.toast("Backlog items could not be loaded. Please try again.", { type: "error" });
            },
        });
    }
    document.querySelector(".apply-reset-filters-btn")?.addEventListener("click", () => {
        setFilterPanel(false);
        resetFilters();
    });

    let backlogSearchTimer;
    $("#backlogSearchInput").on("input", () => {
        clearTimeout(backlogSearchTimer);
        backlogSearchTimer = setTimeout(reloadBacklog, 250);
    });
    $("#filterOptionsForm").on("submit", function (event) {
        event.preventDefault();
        reloadBacklog();
    });
    generateTableHeader();
    filterTable();

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
                    bootstrap.Modal.getOrCreateInstance(document.getElementById("backlogModal")).show();
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
                    document.getElementById("addbacklogform").oxMarkClean?.();
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

    // Delete immediately (soft delete on the server) and offer Undo
    function confirmDelete(id) {
        Ox.undoableDelete({
            label: "Backlog item",
            deleteUrl: assert_path + "backlog/deletebacklogitem?pid=" + pId + "&pblid=" + id,
            restoreUrl: assert_path + "backlog/restorebacklogitem?pid=" + pId + "&pblid=" + id,
            onChange: filterTable,
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
                                    form.dataset.saved = "1";
                                    bootstrap.Modal.getOrCreateInstance(document.getElementById("backlogModal")).hide();
                                    Ox.toast(data.message || actionMessage);
                                    filterTable();
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
    const historyPager = Ox.pager(document.getElementById('historyPager'), {
        scope: 'backlog-history',
        sizes: [8, 20, 50],
        onChange: () => displayActivities(),
    });

    document.getElementById('backloghistory').addEventListener('click', loadActivities);
    document.getElementById('clearHistory').addEventListener('click', clearHistory);
    document.getElementById('filterButton').addEventListener('click', filterActivities);

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

        historyPager.reset();
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

            historyPager.render(0);
            return;
        }

        historyPager.render(filteredActivities.length);
        const activitiesToDisplay = filteredActivities.slice(historyPager.offset, historyPager.offset + historyPager.size);

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

    }

    // To clear the history each time it gets closed
    function clearHistory() {
        activities = [];
        filteredActivities = [];
        historyPager.reset();
        displayActivities();
    }

    document.getElementById('resetButton').addEventListener('click', resetActivities);

    // To rest the filter applied
    function resetActivities() {
        filteredActivities = [...activities];
        historyPager.reset();

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