if (typeof m_createSprint !== 'undefined') {

document.addEventListener('DOMContentLoaded', function () {
    $(document).ready(function () {

        let members = 1;
        let selectedTasks = {};
        let currentProductData = 0;

        var editReason;

        // Backlog functionality
        const $backlogList = $('#backlogList');
        const $epicList = $('#epicList');
        const $userStoryList = $('#userStoryList');
        const $taskList = $('#taskList');
        const $epicColumn = $('#epicColumn');
        const $userStoryColumn = $('#userStoryColumn');
        const $taskColumn = $('#taskColumn');

        let selectedBacklogIndex = -1;
        let selectedEpicIndex = -1;
        let selectedUserStoryIndex = -1;

        // New functions for selected items
        const selectedItemsList = document.getElementById('selectedItemsList');
        const SprintStartDate = document.getElementById('SprintStartDate');
        const SprintEndDate = document.getElementById('SprintEndDate');
        const totalBacklogItemsSpan = document.getElementById('totalBacklogItems');
        const totalEpicItemsSpan = document.getElementById('totalEpicItems');
        const totalUserStoriesItemsSpan = document.getElementById('totalUserStoriesItems');
        const totalTaskItemsSpan = document.getElementById('totalItems');
        const clearAllBtn = document.getElementById('clearAllBtn');
        // const itemTemplate = document.getElementById('selectedItemTemplate');
        const backlogPercentSpan = document.querySelector('.backlog-percent');
        const epicPercentSpan = document.querySelector('.epic-percent');
        const userStoryPercentSpan = document.querySelector('.user-story-percent');
        const taskPercentSpan = document.querySelector('.task-percent');

        // const ctx = document.getElementById('selectedItemsChart').getContext('2d');
        // let selectedItemsChart;

        // introJs().setOptions({
        //     steps: [
        //         {
        //             intro: "Welcome to the tour of our website!"
        //         },
        //         {
        //             element: document.querySelector('#header'),
        //             intro: "This is the header. You can find the navigation links here."
        //         },
        //         {
        //             element: document.querySelector('#dropdown'),
        //             intro: "Here you can find the dropdown menu."
        //         },
        //         {
        //             element: document.querySelector('#main-content'),
        //             intro: "This is the main content area where you can find various features and information."
        //         },
        //         {
        //             element: document.querySelector('#footer'),
        //             intro: "This is the footer with additional links and information."
        //         }
        //     ]
        // }).start();

        $('#backlogSearch').on('input', function () {
            const searchTerm = $(this).val().toLowerCase();
            filterBacklogItems(searchTerm);
        });

        $('#epicSearch').on('input', function () {
            const searchTerm = $(this).val().toLowerCase();
            filterEpics(searchTerm);
        });

        $('#userStorySearch').on('input', function () {
            const searchTerm = $(this).val().toLowerCase();
            filterUserStories(searchTerm);
        });

        $('#taskSearch').on('input', function () {
            const searchTerm = $(this).val().toLowerCase();
            filterTasks(searchTerm);
        });

        function filterBacklogItems(searchTerm) {
            $('#backlogList li').each(function () {
                const backlogItem = $(this).text().toLowerCase();
                if (backlogItem.includes(searchTerm)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }

        function filterEpics(searchTerm) {
            $('#epicList li').each(function () {
                const epic = $(this).text().toLowerCase();
                if (epic.includes(searchTerm)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }

        function filterUserStories(searchTerm) {
            $('#userStoryList li').each(function () {
                const userStory = $(this).text().toLowerCase();
                if (userStory.includes(searchTerm)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }

        function filterTasks(searchTerm) {
            $('#taskList li').each(function () {
                const task = $(this).text().toLowerCase();
                if (task.includes(searchTerm)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }
        // flatpickr("#startDate", {
        //     minDate: "today" 
        // });
        const startDatePicker = flatpickr("#startDate", {
            minDate: "today",
            onChange: function (selectedDates, dateStr, instance) {
                endDatePicker.set('minDate', dateStr);
                SprintStartDate.textContent = dateStr;
            }
        });

        const endDatePicker = flatpickr("#endDate", {
            minDate: "today",
            onChange: function (selectedDates, dateStr, instance) {

            }
        });
        let sprintSettingAdded = false;
        let selectedActivities = [];

        function createNewSetting() {
            const newSetting = $(`
                <div class="sprint-setting mb-3">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="category" class="form-label">Enter Category</label>
                            <select class="form-select activity-select" name="settingName[]">
                                <option value="" disabled selected>Select Activity</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="settingStartDate" class="form-label">Start Date</label>
                            <input type="text" class="form-control mb-3 flatpickr-no-config flatpickr-input settingStartDateInput"
                                name="settingStartDate[]" placeholder="Select Date" readonly="readonly">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="settingEndDate" class="form-label">End Date</label>
                            <input type="text" class="form-control mb-3 flatpickr-no-config flatpickr-input settingEndDateInput"
                                name="settingEndDate[]" placeholder="Select Date" readonly="readonly">
                        </div>
                        <div class="col-md-2 mb-3 mt-3 d-flex align-items-center cls-danger-list">
                            <button type="button" class="btn btn-danger remove-setting"><i class="fas fa-trash-alt"></i></button>
                        </div>
                    </div>
                    <div class="row d-flex">
                        
                        <textarea name="planningComments[]" class="form-control" id="exampleFormControlTextarea1" rows="3" style="width: 81%; margin-left: 1%;"
                            placeholder="Comments about activity"></textarea>
                            <button type="button" class="btn btn-danger cls-danger-lists remove-setting"><i class="fas fa-trash-alt"></i></button>
                    </div>
                </div>
            `);

            const $select = newSetting.find('.activity-select');
            sprintActivity.forEach(activity => {
                if (!selectedActivities.includes(activity.sprint_activity_id)) {
                    const option = `<option value="${activity.sprint_activity_id}">${activity.activity}</option>`;
                    $select.append(option);
                }
            });

            $('#settingsContainer').append(newSetting);

            // Initialize flatpickr after appending the new setting
            flatpickr(newSetting.find('.settingStartDateInput'), {
                minDate: "today",
                maxDate: $('#endDate').val()
            });
            flatpickr(newSetting.find('.settingEndDateInput'), {
                minDate: "today",
                maxDate: $('#endDate').val()
            });

            // Remove setting on button click
            newSetting.find('.remove-setting').on('click', function () {
                $(this).closest('.sprint-setting').remove();
            });

            $select.on('change', function () {
                const selectedValue = parseInt($(this).val());
                selectedActivities.push(selectedValue);
                $('.activity-select').not(this).each(function () {
                    $(this).find(`option[value="${selectedValue}"]`).prop('disabled', true);
                });
            });

            $('#sprintSettingContainer').append(newSetting);
        }

        if (!editMode) {
            $('#sprintSettingBlock').show();
            $('#sprintSettingCollapse').addClass('show');
            //$('[data-bs-target="#sprintSettingCollapse"]').attr('aria-expanded', 'true');
            //$('[data-bs-target="#sprintSettingCollapse"]')[0].scrollIntoView({ behavior: 'smooth' });
        }

        createNewSetting();

        $('#addMoreSetting').on('click', createNewSetting);
        const startDateValue = $('#startDate').val();
        const endDateValue = $('#endDate').val();




        $(document).on('click', '.remove-setting', function () {
            $(this).closest('.sprint-setting').remove();
            if ($('#sprintSettingContainer').children().length === 0) {
                sprintSettingAdded = false;
            }
        });



        $('#createSprintForm').on('submit', function (e) {
            e.preventDefault();
            if (!this.checkValidity()) {
                e.stopPropagation();
                this.classList.add('was-validated');
                return;
            }

            if (!sprintSettingAdded) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: "Do you want to submit the form?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, submit data',
                    cancelButtonText: 'No, stay on this page'
                }).then((result) => {
                    if (result.isConfirmed) {
                        submitFormData();
                    } else {
                        Swal.fire(
                            'Submission Cancelled',
                            'Your data has not been submitted.',
                            'info'
                        );
                    }
                });
            } else {
                submitFormData();
            }
        });
        $('#editSprintForm').on('submit', function (e) {
            e.preventDefault();

            if (!this.checkValidity()) {
                e.stopPropagation();
                this.classList.add('was-validated');
                return;
            }

            // Function to handle the form submission
            const submitForm = () => {
                let formData = {
                    sprint_id: sprintId,
                    productName: $('#productName').val(),
                    customer: $('#customer').val(),
                    sprintDuration: $('#sprintDuration').val(),
                    sprintName: $('#sprintName').val(),
                    sprintVersion: $('#sprintVersion').val(),
                    startDate: $('#startDate').val(),
                    endDate: $('#endDate').val(),
                    sprintStatus: $('#sprintStatus').val(),
                    default: tinymce.get('default').getContent(),
                    editedFor: editReason,
                    selectedTasks: Object.keys(selectedTasks).filter(key => selectedTasks[key]).map(key => {
                        const [bIndex, eIndex, uIndex, tIndex] = key.split('-').map(Number);
                        const task = currentProductData.backlogItems[bIndex].epics[Object.keys(currentProductData.backlogItems[bIndex].epics)[eIndex]].userStories[Object.keys(currentProductData.backlogItems[bIndex].epics[Object.keys(currentProductData.backlogItems[bIndex].epics)[eIndex]].userStories)[uIndex]].tasks[tIndex];
                        return task[0];
                    }),
                    selectedMembers: JSON.stringify(Array.from(checkedMembers)),
                    sprintSettings: $('.sprint-setting').map(function () {
                        return {
                            activity: $(this).find('.activity-select').val(),
                            startDate: $(this).find('input[name="settingStartDate[]"]').val(),
                            endDate: $(this).find('input[name="settingEndDate[]"]').val()
                        };
                    }).get()
                };

                console.log("for update data");
                console.log(formData);

                $.ajax({
                    url: BASE_URL + "sprint/update",
                    type: 'POST',
                    data: formData,
                    dataType: 'JSON',
                    success: function (response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: 'Your sprint has been updated successfully.',
                                confirmButtonText: 'OK'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.href = BASE_URL + "/sprint/sprintlist";
                                }
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: 'Something went wrong. Please try again.',
                            });
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error("Error details:", xhr.responseText);
                        console.log("Status:", status);
                        console.log("Error:", error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Error occurred.  Please try again.',
                        });
                    }
                });
            };

            // Show SweetAlert2 prompt for edit reason
            Swal.fire({
                title: 'Reason',
                input: 'textarea',
                inputLabel: 'Please enter the reason for editing:',
                inputValidator: (value) => {
                    editReason = value;
                    if (!value) {
                        return 'You need to enter a reason!';
                    }
                },
                showCancelButton: true,
                confirmButtonText: 'OK',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    submitForm();
                }
            });
        });


        function submitFormData() {
            let formData = {
                productName: $('#productName').val(),
                customer: $('#customer').val(),
                sprintDuration: $('#sprintDuration').val(),
                sprintName: $('#sprintName').val(),
                sprintVersion: $('#sprintVersion').val(),
                startDate: $('#startDate').val(),
                endDate: $('#endDate').val(),
                sprintGoal: tinymce.get('default').getContent(),
                selectedTasks: Object.keys(selectedTasks).filter(key => selectedTasks[key]).map(key => {
                    const [bIndex, eIndex, uIndex, tIndex] = key.split('-').map(Number);
                    const task = currentProductData.backlogItems[bIndex].epics[Object.keys(currentProductData.backlogItems[bIndex].epics)[eIndex]].userStories[Object.keys(currentProductData.backlogItems[bIndex].epics[Object.keys(currentProductData.backlogItems[bIndex].epics)[eIndex]].userStories)[uIndex]].tasks[tIndex];
                    return task[0];
                }),
                // selectedMembers: $('input[name="selectedMembers[]"]:checked').map(function () {
                //     return this.value;
                // }).get(),
                selectedMembers: JSON.stringify(Array.from(checkedMembers)),
                sprintSettings: $('.sprint-setting').map(function () {
                    return {
                        activity: $(this).find('.activity-select').val(),
                        startDate: $(this).find('input[name="settingStartDate[]"]').val(),
                        endDate: $(this).find('input[name="settingEndDate[]"]').val(),
                        comments: $(this).find('textarea[name="planningComments[]"]').val()
                    };
                }).get()
            };
            console.log(formData);
            $.ajax({
                url: BASE_URL + "sprint/createsprint",
                type: 'POST',
                data: formData,
                dataType: 'JSON',
                //contentType: 'application/json; charset=utf-8',
                success: function (response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: 'Your sprint has been created successfully.',
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = BASE_URL + "/sprint/sprintlist";
                            }
                        });
                    } else {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Oops!',
                            text: response.message,
                        });
                    }
                },
                error: function (xhr, status, error, response) {
                    console.error("Error details:", xhr.responseText);
                    console.log("Status:", status);
                    console.log("Error:", error);
                    console.log(response);
                    Swal.fire({
                        icon: 'warning',
                        title: 'Error!',
                        text: "Unexpected error",
                    })
                }
            });
        }

        function calculateEndDate(startDate, durationId) {
            let endDate = new Date(startDate);
            let daysToAdd;
            let daysAdded = 0;

            // Set the number of days to add based on the duration ID
            switch (parseInt(durationId)) {
                case 1:
                    daysToAdd = 4; // Changed to 5 for sprint duration 1
                    break;
                case 2:
                    daysToAdd = 9;
                    break;
                case 3:
                    daysToAdd = 14;
                    break;
                case 4:
                    daysToAdd = 19;
                    break;
                default:
                    daysToAdd = 0; // Default changed to 5
            }

            while (daysAdded < daysToAdd) {
                endDate.setDate(endDate.getDate() + 1);
                // Skip Saturday (6) and Sunday (0)
                if (endDate.getDay() !== 0 && endDate.getDay() !== 6) {
                    daysAdded++;
                }
            }

            // If the end date falls on a weekend, move it to the next Monday
            while (endDate.getDay() === 0 || endDate.getDay() === 6) {
                endDate.setDate(endDate.getDate() + 1);
            }

            return endDate.toISOString().split('T')[0];
        }

        $('#sprintDuration, #startDate').on('change', function () {
            const startDate = $('#startDate').val();
            const durationId = $('#sprintDuration').val();
            if (startDate && durationId) {
                // Only calculate end date if it hasn't been manually changed
                if (!$('#endDate').data('manually-changed')) {
                    const endDate = calculateEndDate(startDate, durationId);
                    $('#endDate').val(endDate);
                    SprintEndDate.textContent = endDate;
                    flatpickr(".settingEndDateInput", {
                        minDate: "today",
                        maxDate: endDate
                    });
                    flatpickr(".settingStartDateInput", {
                        minDate: "today",
                        maxDate: endDate
                    });
                }
            }
        });
        $('#startDate, #sprintDuration').on('change', function () {
            $('#endDate').data('manually-changed', false);
        });
        $('#SelectSprintTasks').on('click', function () {
            $('#SelectSprint').show();
        });
        $('#endDate').on('change', function () {
            // Set the sprint duration to option with id 5
            $('#sprintDuration').val('5');

            // Prevent the automatic recalculation of the end date
            $(this).data('manually-changed', true);
        });
        let currentPage = 1;
        const itemsPerPage = 7;
        let filteredMembers = [];
        let checkedMembers = new Set();

        if (editMode) {
            let value = sprint_data[0]['r_product_id'];
            getMembersList(value);
        }
        function displayMembers(page, membersToUse = members, isAddNew) {
            const memberTableBody = document.getElementById('memberTableBody');
            memberTableBody.innerHTML = ''; // Clear existing rows

            const membersArray = Array.isArray(membersToUse) ? membersToUse : Object.values(membersToUse);
            const start = (page - 1) * itemsPerPage;
            const end = start + itemsPerPage;
            const membersToDisplay = membersArray.slice(start, end);
            if (editMode && isAddNew === true) {
                console.log("member_in_sprint,===>", member_in_sprint);
                member_in_sprint.forEach(id => {
                    checkedMembers.add(id);
                });

            }

            membersToDisplay.forEach((member) => {
                const isChecked = checkedMembers.has(member.id.toString()) ? 'checked' : '';
                const row = `<tr>
                <td style="padding: 13px 10px 10px 30px;"><input type="checkbox" name="selectedMembers[]" id="checkbox_${member.id}" value="${member.id}" ${isChecked}></td>
                <td><label for="checkbox_${member.id}">${member.name}</label></td>
                <td><label for="checkbox_${member.id}">${member.role_name}</label></td>
                </tr>`;

                memberTableBody.innerHTML += row;
            });

            updatePaginationControls(membersArray.length);
            updateSelectAllCheckbox();
            updateSelectedMembersDisplay();
        }

        function updateSelectedMembersDisplay() {
            const container = document.getElementById('selectedMembersContainer');
            container.innerHTML = '';

            checkedMembers.forEach(memberId => {
                const member = Object.values(members).find(m => m.id.toString() === memberId);
                if (member) {
                    const memberBox = document.createElement('span');
                    memberBox.className = 'badge bg-primary me-2 mb-2';
                    memberBox.innerHTML = `
                        <span style="font-size:0.7rem; font-weight:500;">${member.name}</span>
                        <i class="fas fa-times ms-1" style="cursor: pointer; font-size:0.7rem;" data-member-id="${member.id}"></i>
                    `;
                    memberBox.querySelector('i').addEventListener('click', function () {
                        removeMember(this.getAttribute('data-member-id'));
                    });
                    container.appendChild(memberBox);
                }
            });
        }

        function removeMember(memberId) {
            checkedMembers.delete(memberId.toString());
            const checkbox = document.getElementById(`checkbox_${memberId}`);
            if (checkbox) {
                checkbox.checked = false;
            }
            updateSelectedMembersDisplay();
            updateSelectAllCheckbox();
        }

        function changePage(newPage) {
            const membersToUse = filteredMembers.length > 0 ? filteredMembers : members;
            const totalPages = Math.ceil(Object.keys(membersToUse).length / itemsPerPage);
            if (newPage >= 1 && newPage <= totalPages) {
                currentPage = newPage;
                displayMembers(currentPage, membersToUse);
            }
        }

        function updatePaginationControls(totalItems) {
            const totalPages = Math.max(1, Math.ceil(totalItems / itemsPerPage));
            const paginationControls = document.getElementById('paginationControls');
            let controlsHTML = `
            <button class="btn btn-primary p-1 m-1" style="border-radius:50%; height:50px; width:50px; " onclick="changePage(${currentPage - 1})" ${currentPage <= 1 ? 'disabled' : ''}><i class='fas fa-angle-left'
                                style='font-size:36px'></i></button>
            <button class="btn btn-primary p-1 m-1" style="border-radius:50%; height:50px; width:50px;"  onclick="changePage(${currentPage + 1})" ${currentPage >= totalPages ? 'disabled' : ''}><i class='fas fa-angle-right'
                                style='font-size:36px'></i></button>
        `;
            // <span>Page ${currentPage} of ${totalPages}</span>


            paginationControls.innerHTML = controlsHTML;
        }

        // Initialize the display
        displayMembers(currentPage);

        // Select all members functionality
        document.getElementById('selectAllMembers').addEventListener('change', function () {
            const checkboxes = document.querySelectorAll('#memberTableBody input[type="checkbox"]');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
                if (this.checked) {
                    checkedMembers.add(checkbox.value);
                } else {
                    checkedMembers.delete(checkbox.value);
                }
            });
            updateSelectedMembersDisplay();
        });

        // Update individual checkbox event listeners
        document.getElementById('memberTableBody').addEventListener('change', function (e) {
            if (e.target.type === 'checkbox') {
                const memberId = e.target.value;
                if (e.target.checked) {
                    checkedMembers.add(memberId);
                } else {
                    checkedMembers.delete(memberId);
                }
                updateSelectedMembersDisplay();
                updateSelectAllCheckbox();
            }
        });

        function updateSelectAllCheckbox() {
            const selectAllCheckbox = document.getElementById('selectAllMembers');
            const checkboxes = document.querySelectorAll('#memberTableBody input[type="checkbox"]');
            selectAllCheckbox.checked = checkboxes.length > 0 && Array.from(checkboxes).every(cb => cb.checked);
        }

        // Member search functionality
        document.getElementById('memberSearch').addEventListener('input', function () {
            const searchTerm = this.value.toLowerCase();
            filteredMembers = Object.values(members).filter(member =>
                member.name.toLowerCase().includes(searchTerm)
            );
            currentPage = 1; // Reset to first page when searching
            displayMembers(currentPage, filteredMembers);
        });

        // Make changePage function global so it can be called from HTML
        window.changePage = changePage;


        // Collapsible functionality
        document.querySelectorAll('.card-header[data-bs-toggle="collapse"]').forEach(header => {
            header.addEventListener('click', function () {
                const icon = this.querySelector('.collapsible-icon');
                // icon.classList.toggle('fa-chevron-down');
                // icon.classList.toggle('fa-chevron-up');
            });
        });

        // // Make changePage function global so it can be called from HTML
        // window.changePage = changePage;



        $('#productName').on('change', function () {
            getMembersList($(this).val());
        }
        );
        function getMembersList(r_product_id) {
            const productId = r_product_id;
            if (productId) {
                if (editMode) {
                    currentProductData = data;
                    renderBacklogItems(data);
                    updateSelectedItems();
                }
                else if (!editMode) {
                    $.ajax({
                        url: BASE_URL + "sprint/getProductTasks/" + productId,
                        type: 'GET',
                        dataType: 'JSON',
                        success: function (response) {
                            console.log(response);
                            if (response.success) {
                                if(response.data.length == 0){
                                    Swal.fire({
                                        title: 'Oops!',
                                        text: "No tasks are available",
                                        icon: 'warning',
                                    })
                                }
                                if (response.data && response.data.backlogItems && response.data.backlogItems.length > 0) {
                                    currentProductData = response.data;
                                    renderBacklogItems(response.data);
                                    updateSelectedItems();
                                } else {
                                    // Reset currentProductData if there's no data
                                    currentProductData = null;
                                    $backlogList.empty();
                                    $epicList.empty();
                                    $userStoryList.empty();
                                    $taskList.empty();
                                    updateSelectedItems();
                                }
                            } else {
                                console.error("Error fetching product tasks:", response.message);
                                // Reset currentProductData on error
                                currentProductData = null;
                                updateSelectedItems();
                            }
                        },
                        error: function (xhr, status, error) {
                            console.error("AJAX error:", status, error);
                            // Reset currentProductData on error
                            currentProductData = null;
                            updateSelectedItems();
                        }
                    });
                }
            } else {
                currentProductData = null;
                $backlogList.empty();
                $epicList.empty();
                $userStoryList.empty();
                $taskList.empty();
                updateSelectedItems();
            }

            if (productId) {
                $.ajax({
                    url: BASE_URL + "sprint/getMembers/" + productId,
                    type: 'GET',
                    dataType: 'JSON',
                    success: function (response) {
                        console.log(response.data);
                        if (response.success) {
                            members = response.data;
                            displayMembers(currentPage, members, true);
                        } else {
                            console.error("Error fetching members:", response.message);
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error("AJAX error:", status, error);
                    }
                });
            }
            else {
                displayMembers(currentPage, members);
            }
        }

        function renderBacklogItems(data) {
            console.log(data);
            $backlogList.empty();
            if (!data || !data.backlogItems || !Array.isArray(data.backlogItems)) {
                console.error("Invalid data structure for backlog items");
                return;
            }

            data.backlogItems.forEach((backlogItem, bIndex) => {
                // const $backlogItem = $('<li class="list-group-item"></li>')
                //     .html(`${backlogItem.name} - <span style="color: #167a40; font-weight: bold">${backlogItem.priority}</span>`);
                const $backlogItem = $('<li class="list-group-item"></li>')
                    .html(`${backlogItem.name}`);
                $backlogList.append($backlogItem);
                selectedBacklogIndex = bIndex;
                selectedEpicIndex = -1;
                selectedUserStoryIndex = -1;
                $epicColumn.show();
                $userStoryColumn.show();
                $taskColumn.show();
                $epicList.empty();
                $userStoryList.empty();
                $taskList.empty();

                if (editMode) {
                    selectedBacklogIndex = bIndex;
                    selectedEpicIndex = -1;
                    selectedUserStoryIndex = -1;
                    $epicColumn.show();
                    $userStoryColumn.show();
                    $taskColumn.show();
                    $epicList.empty();
                    $userStoryList.empty();
                    $taskList.empty();

                    renderEpics(backlogItem.epics, bIndex);
                    updateBackgroundColors();
                    $backlogItem.on('click', function () {
                        selectedBacklogIndex = bIndex;
                        selectedEpicIndex = -1;
                        selectedUserStoryIndex = -1;
                        $epicColumn.show();
                        $userStoryColumn.show();
                        $taskColumn.show();
                        $epicList.empty();
                        $userStoryList.empty();
                        $taskList.empty();

                        renderEpics(backlogItem.epics, bIndex);
                        updateBackgroundColors();
                    });

                }
                else {
                    $backlogItem.on('click', function () {
                        selectedBacklogIndex = bIndex;
                        selectedEpicIndex = -1;
                        selectedUserStoryIndex = -1;
                        $epicColumn.show();
                        $userStoryColumn.show();
                        $taskColumn.show();
                        $epicList.empty();
                        $userStoryList.empty();
                        $taskList.empty();

                        renderEpics(backlogItem.epics, bIndex);
                        updateBackgroundColors();
                    });
                }
            });
            updateSelectedItems();
        }

        function renderEpics(epics, bIndex) {
            $epicList.empty();
            if (!epics) {
                console.error("Invalid data structure for epics");
                return;
            }

            Object.entries(epics).forEach(([epicName, epic], eIndex) => {
                const $epic = $('<li class="list-group-item"></li>')
                    .text(epic.name);
                $epicList.append($epic);

                if (editMode) {
                    selectedEpicIndex = eIndex;
                    selectedUserStoryIndex = -1;
                    $userStoryColumn.show();
                    $taskColumn.show();
                    $userStoryList.empty();
                    $taskList.empty();

                    renderUserStories(epic.userStories, bIndex, eIndex);
                    updateBackgroundColors();
                    $epic.on('click', function () {
                        selectedEpicIndex = eIndex;
                        selectedUserStoryIndex = -1;
                        $userStoryColumn.show();
                        $taskColumn.show();
                        $userStoryList.empty();
                        $taskList.empty();

                        renderUserStories(epic.userStories, bIndex, eIndex);
                        updateBackgroundColors();
                    });

                }
                else {
                    $epic.on('click', function () {
                        selectedEpicIndex = eIndex;
                        selectedUserStoryIndex = -1;
                        $userStoryColumn.show();
                        $taskColumn.show();
                        $userStoryList.empty();
                        $taskList.empty();

                        renderUserStories(epic.userStories, bIndex, eIndex);
                        updateBackgroundColors();
                    });
                }
            });
            updateSelectedItems();
        }

        function renderUserStories(userStories, bIndex, eIndex) {
            $userStoryList.empty();
            if (!userStories) {
                console.error("Invalid data structure for user stories");
                return;
            }

            Object.entries(userStories).forEach(([userStoryName, userStory], uIndex) => {
                const $userStory = $('<li class="list-group-item"></li>').html('<strong>US_' + userStory.id + '</strong> - ' + userStory.name);

                $userStoryList.append($userStory);

                if (editMode) {
                    selectedUserStoryIndex = uIndex;
                    $taskColumn.show();
                    $taskList.empty();

                    renderTasks(userStory.tasks, bIndex, eIndex, uIndex);
                    updateBackgroundColors();
                    $userStory.on('click', function () {
                        selectedUserStoryIndex = uIndex;
                        $taskColumn.show();
                        $taskList.empty();

                        renderTasks(userStory.tasks, bIndex, eIndex, uIndex);
                        updateBackgroundColors();
                    });

                }
                else {
                    $userStory.on('click', function () {
                        selectedUserStoryIndex = uIndex;
                        $taskColumn.show();
                        $taskList.empty();

                        renderTasks(userStory.tasks, bIndex, eIndex, uIndex);
                        updateBackgroundColors();
                    });
                }
            });
            updateSelectedItems();
        }

        function renderTasks(tasks, bIndex, eIndex, uIndex) {
            $taskList.empty();

            const $overallCheckbox = $('<li class="list-group-item"></li>')
                .append('<input type="checkbox" class="user-story-checkbox" id="overallTasksCheckbox"> <label for="overallTasksCheckbox">Select All Tasks</label>');
            $taskList.append($overallCheckbox);

            if (!tasks || !Array.isArray(tasks)) {
                console.error("Invalid data structure for tasks");
                return;
            }
            tasks.forEach((task, tIndex) => {
                const taskId = `${bIndex}-${eIndex}-${uIndex}-${tIndex}`;
                let isChecked = selectedTasks[taskId] ? 'checked' : '';
                if (editMode) {
                    isChecked = task_in_sprint.includes(`${task[0]}`) ? 'checked' : '';
                    console.log(isChecked);
                }
                const $task = $('<li class="list-group-item"></li>')
                    .append(`<input type="checkbox" class="user-story-checkbox" id="${taskId}" value="${task[0]}" ${isChecked}> <label for="${taskId}">${task[1]} - <span style="color: #102018; font-weight: bold">${task[2]}</span> - <span style="color: #167a40; font-weight: bold">${task[3]}</span></label>`);
                $taskList.append($task);


                if (editMode) {
                    $task.find('input[type="checkbox"]').not(this).prop('checked', isChecked).each(function () {
                        const taskId = $(this).attr('id');
                        selectedTasks[taskId] = this.checked;
                        updateBackgroundColors();
                        updateSelectedItems();
                    });
                }

                $task.find('input').on('change', function () {
                    selectedTasks[taskId] = this.checked;
                    updateBackgroundColors();
                    updateSelectedItems();
                });
            });

            $('#overallTasksCheckbox').on('change', function () {
                const isChecked = this.checked;
                $taskList.find('input[type="checkbox"]').not(this).prop('checked', isChecked).each(function () {
                    const taskId = $(this).attr('id');
                    selectedTasks[taskId] = isChecked;
                });
                updateBackgroundColors();
                updateSelectedItems();
            });
            if (editMode) {
                updateBackgroundColors();
                updateOverallCheckbox();
            }
            updateBackgroundColors();
            updateOverallCheckbox();
        }

        // function updateBackgroundColors() {
        //     $backlogList.find('li').each(function (bIndex) {
        //         const hasSelectedTask = currentProductData.backlogItems[bIndex].epics.some((epic, eIndex) =>
        //             epic.userStories.some((userStory, uIndex) =>
        //                 userStory.tasks.some((task, tIndex) =>
        //                     selectedTasks[`${bIndex}-${eIndex}-${uIndex}-${tIndex}`]
        //                 )
        //             )
        //         );
        //         $(this).toggleClass('selected-backlog', hasSelectedTask);
        //     });

        //     $epicList.find('li').each(function (eIndex) {
        //         const hasSelectedTask = selectedBacklogIndex !== -1 &&
        //             currentProductData.backlogItems[selectedBacklogIndex].epics[eIndex].userStories.some((userStory, uIndex) =>
        //                 userStory.tasks.some((task, tIndex) =>
        //                     selectedTasks[`${selectedBacklogIndex}-${eIndex}-${uIndex}-${tIndex}`]
        //                 )
        //             );
        //         $(this).toggleClass('selected-epic', hasSelectedTask);
        //     });

        //     $userStoryList.find('li').each(function (uIndex) {
        //         const hasSelectedTask = selectedBacklogIndex !== -1 && selectedEpicIndex !== -1 &&
        //             currentProductData.backlogItems[selectedBacklogIndex].epics[selectedEpicIndex].userStories[uIndex].tasks.some((task, tIndex) =>
        //                 selectedTasks[`${selectedBacklogIndex}-${selectedEpicIndex}-${uIndex}-${tIndex}`]
        //             );
        //         $(this).toggleClass('selected-user-story', hasSelectedTask);
        //     });

        //     $taskList.find('li').each(function (tIndex) {
        //         const $checkbox = $(this).find('input[type="checkbox"]');
        //         if ($checkbox.length) {
        //             $(this).toggleClass('selected-task', $checkbox.prop('checked'));
        //         }
        //     });
        //     const allTasksChecked = $taskList.find('input[type="checkbox"]').not('#overallTasksCheckbox').length ===
        //         $taskList.find('input[type="checkbox"]:checked').not('#overallTasksCheckbox').length;
        //     $('#overallTasksCheckbox').prop('checked', allTasksChecked);

        //     updateOverallCheckbox();
        // }
        function updateBackgroundColors() {

            $backlogList.find('li').each(function (bIndex) {
                $(this).toggleClass('selected-backlog', selectedBacklogIndex === bIndex);
            });

            $epicList.find('li').each(function (eIndex) {
                const isSelected = selectedBacklogIndex !== -1 && selectedEpicIndex === eIndex;
                $(this).toggleClass('selected-epic', isSelected);
            });

            $userStoryList.find('li').each(function (uIndex) {
                const isSelected = selectedBacklogIndex !== -1 &&
                    selectedEpicIndex !== -1 &&
                    selectedUserStoryIndex === uIndex;
                $(this).toggleClass('selected-user-story', isSelected);
            });

            $taskList.find('li').each(function (tIndex) {
                const $checkbox = $(this).find('input[type="checkbox"]');
                if ($checkbox.length) {
                    $(this).toggleClass('selected-task', $checkbox.prop('checked'));
                }
            });

            const allTasksChecked = $taskList.find('input[type="checkbox"]').not('#overallTasksCheckbox').length ===
                $taskList.find('input[type="checkbox"]:checked').not('#overallTasksCheckbox').length;
            $('#overallTasksCheckbox').prop('checked', allTasksChecked);

            updateOverallCheckbox();
        }

        //   function selectBacklogItem(index) {
        //     selectedBacklogIndex = index;
        //     selectedEpicIndex = -1;
        //     selectedUserStoryIndex = -1;
        //     updateBackgroundColors();
        //   }

        //   function selectEpic(index) {
        //     selectedEpicIndex = index;
        //     selectedUserStoryIndex = -1;
        //     updateBackgroundColors();
        //   }

        //   function selectUserStory(index) {
        //     selectedUserStoryIndex = index;
        //     updateBackgroundColors();
        //   }

        function updateSelectedItems() {
            if (!currentProductData) {
                console.log("No product data available");
                // Reset all counts and percentages to 0
                resetCountsAndPercentages();
                return;
            }

            selectedItemsList.innerHTML = '';
            let count = 0;
            let backlogCount = 0;
            let epicCount = 0;
            let userStoryCount = 0;
            let taskCount = 0;
            const selectedBacklogs = new Set();
            const selectedEpics = new Set();
            const selectedUserStories = new Set();

            // Create a structure to group tasks by backlog
            const groupedBacklogs = {};

            Object.keys(selectedTasks).forEach(key => {
                if (selectedTasks[key]) {
                    const [bIndex, eIndex, uIndex, tIndex] = key.split('-').map(Number);
                    const backlogItem = currentProductData.backlogItems[bIndex];
                    const epicKey = Object.keys(backlogItem.epics)[eIndex];
                    const epic = backlogItem.epics[epicKey];
                    const userStoryKey = Object.keys(epic.userStories)[uIndex];
                    const userStory = epic.userStories[userStoryKey];
                    const task = userStory.tasks[tIndex];

                    if (!groupedBacklogs[bIndex]) {
                        groupedBacklogs[bIndex] = {
                            backlog: backlogItem.name,
                            epics: {}
                        };
                    }
                    if (!groupedBacklogs[bIndex].epics[eIndex]) {
                        groupedBacklogs[bIndex].epics[eIndex] = {
                            epic: epic.name,
                            userStories: {}
                        };
                    }
                    if (!groupedBacklogs[bIndex].epics[eIndex].userStories[uIndex]) {
                        groupedBacklogs[bIndex].epics[eIndex].userStories[uIndex] = {
                            userStory: userStory.name,
                            tasks: []
                        };
                    }
                    groupedBacklogs[bIndex].epics[eIndex].userStories[uIndex].tasks.push({
                        name: task[1],
                        key: key
                    });

                    selectedBacklogs.add(bIndex);
                    selectedEpics.add(`${bIndex}-${eIndex}`);
                    selectedUserStories.add(`${bIndex}-${eIndex}-${uIndex}`);
                    taskCount++;
                }
            });

            // Render grouped backlogs
            Object.entries(groupedBacklogs).forEach(([bIndex, backlog]) => {
                const backlogCard = document.createElement('div');
                backlogCard.className = 'card mb-3 backlog-card';
                backlogCard.innerHTML = `
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 headerName">Backlog Item : ${backlog.backlog}</h5>
                        <button class="btn btn-sm btn-light clear-backlog" data-backlog-index="${bIndex}" style="padding:3px; font-size:0.7rem;">Clear Backlog</button>
                    </div>
                    <div class="card-body">
                        <div class="epic-list"></div>
                    </div>
                `;

                const epicList = backlogCard.querySelector('.epic-list');

                Object.entries(backlog.epics).forEach(([eIndex, epic]) => {
                    const epicElement = document.createElement('div');
                    epicElement.className = 'epic mb-3';
                    epicElement.innerHTML = `
                        <h6 class="epic-name">Epic : ${epic.epic}</h6>
                        <div class="user-story-list"></div>
                    `;

                    const userStoryList = epicElement.querySelector('.user-story-list');

                    Object.entries(epic.userStories).forEach(([uIndex, userStory]) => {
                        const userStoryElement = document.createElement('div');
                        userStoryElement.className = 'user-story mb-2';
                        userStoryElement.innerHTML = `
                            <p class="user-story-name">User Story : ${userStory.userStory}</p>
                            <ul class="list-group task-list"></ul>
                        `;

                        const taskList = userStoryElement.querySelector('.task-list');
                        let ind = 1;

                        userStory.tasks.forEach(task => {
                            const taskItem = document.createElement('li');
                            taskItem.className = 'list-group-item d-flex justify-content-between align-items-center';
                            taskItem.innerHTML = `
                                <span>Task ${ind++} : ${task.name}</span>
                                <button class="btn btn-sm btn-danger remove-task" data-task-id="${task.key}" style="color:white;">Clear</button>
                            `;
                            taskList.appendChild(taskItem);
                        });
                        ind = 1;

                        if (userStory.tasks.length > 0) {
                            userStoryList.appendChild(userStoryElement);
                        }
                    });

                    if (epicElement.querySelector('.user-story-list').children.length > 0) {
                        epicList.appendChild(epicElement);
                    }
                });

                if (backlogCard.querySelector('.epic-list').children.length > 0) {
                    selectedItemsList.appendChild(backlogCard);
                }
            });

            backlogCount = selectedBacklogs.size;
            epicCount = selectedEpics.size;
            userStoryCount = selectedUserStories.size;
            totalBacklogItemsSpan.textContent = backlogCount;
            totalEpicItemsSpan.textContent = epicCount;
            totalUserStoriesItemsSpan.textContent = userStoryCount;
            totalTaskItemsSpan.textContent = taskCount;
            // Calculate and update percentages
            updatePercentages(backlogCount, epicCount, userStoryCount, taskCount);

            // // Update chart
            // updateChart(backlogCount, epicCount, userStoryCount, taskCount);
        }
        function resetCountsAndPercentages() {
            totalBacklogItemsSpan.textContent = 0;
            totalEpicItemsSpan.textContent = 0;
            totalUserStoriesItemsSpan.textContent = 0;
            totalTaskItemsSpan.textContent = 0;

            backlogPercentSpan.textContent = "0% (0/0)";
            epicPercentSpan.textContent = "0% (0/0)";
            userStoryPercentSpan.textContent = "0% (0/0)";
            taskPercentSpan.textContent = "0% (0/0)";

            selectedItemsList.innerHTML = '';
        }

        function updatePercentages(backlogCount, epicCount, userStoryCount, taskCount) {
            if (!currentProductData) {
                resetCountsAndPercentages();
                return;
            }

            const totalBacklogs = currentProductData.backlogItems.length;
            const totalEpics = currentProductData.backlogItems.reduce((sum, backlog) => sum + Object.keys(backlog.epics).length, 0);
            const totalUserStories = currentProductData.backlogItems.reduce((sum, backlog) =>
                sum + Object.values(backlog.epics).reduce((epicSum, epic) => epicSum + Object.keys(epic.userStories).length, 0), 0);
            const totalTasks = currentProductData.backlogItems.reduce((sum, backlog) =>
                sum + Object.values(backlog.epics).reduce((epicSum, epic) =>
                    epicSum + Object.values(epic.userStories).reduce((storySum, story) => storySum + story.tasks.length, 0), 0), 0);

            function calculatePercentage(count, total) {
                if (total === 0) return "0% (0/0)";
                const percentage = Math.round((count / total) * 100);
                return `${percentage}% (${count}/${total})`;
            }

            backlogPercentSpan.textContent = calculatePercentage(backlogCount, totalBacklogs);
            epicPercentSpan.textContent = calculatePercentage(epicCount, totalEpics);
            userStoryPercentSpan.textContent = calculatePercentage(userStoryCount, totalUserStories);
            taskPercentSpan.textContent = calculatePercentage(taskCount, totalTasks);
        }

        // Update event listeners
        selectedItemsList.addEventListener('click', function (e) {
            if (e.target.classList.contains('remove-task')) {
                e.preventDefault();
                const taskId = e.target.dataset.taskId;
                clearTask(taskId);
            } else if (e.target.classList.contains('clear-backlog')) {
                e.preventDefault();
                const backlogIndex = e.target.dataset.backlogIndex;
                clearBacklog(backlogIndex);
            }
        });

        function clearTask(taskId) {
            selectedTasks[taskId] = false;
            updateSelectedItems();
            updateBackgroundColors();
            updateTaskCheckbox(taskId);
            updateOverallCheckbox();
        }

        function updateTaskCheckbox(taskId) {
            const checkbox = document.getElementById(taskId);
            if (checkbox) {
                checkbox.checked = selectedTasks[taskId] || false;
            }
        }
        function updateOverallCheckbox() {
            const $overallCheckbox = $('#overallTasksCheckbox');
            const $taskCheckboxes = $taskList.find('input[type="checkbox"]').not('#overallTasksCheckbox');
            const allChecked = $taskCheckboxes.length === $taskCheckboxes.filter(':checked').length;
            $overallCheckbox.prop('checked', allChecked);
            $overallCheckbox.closest('li').toggleClass('selected-task', allChecked);
        }



        function clearBacklog(backlogIndex) {
            Object.keys(selectedTasks).forEach(key => {
                if (key.startsWith(backlogIndex + '-')) {
                    selectedTasks[key] = false;
                    updateTaskCheckbox(key);
                }
            });
            updateSelectedItems();
            updateBackgroundColors();
            updateOverallCheckbox();
        }

        clearAllBtn.addEventListener('click', function (event) {
            event.preventDefault(); // Prevent the form from submitting

            Swal.fire({
                title: 'Are you sure?',
                text: "Selected all tasks will be clear!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, clear it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Perform the clear action here
                    Object.keys(selectedTasks).forEach(key => {
                        selectedTasks[key] = false;
                        updateTaskCheckbox(key);
                    });
                    updateSelectedItems();
                    updateBackgroundColors();
                    updateOverallCheckbox();

                    Swal.fire(
                        'Cleared!',
                        'Your form has been cleared.',
                        'success'
                    );
                }
            });
        });

        // clearAllBtn.addEventListener('click', function (e) {
        //     e.preventDefault();
        //     Object.keys(selectedTasks).forEach(key => {
        //         selectedTasks[key] = false;
        //         updateTaskCheckbox(key);
        //     });
        //     updateSelectedItems();
        //     updateBackgroundColors();
        //     updateOverallCheckbox();
        // });


        // Hover effect for columns
        $('.column').hover(
            function () {
                $(this).css({
                    // 'border': '2px solid #4a90e2',
                    // 'box-shadow': '0 0 10px rgba(98, 102, 106, 0.5)'
                });
            },
            function () {
                $(this).css({
                    // 'border': 'none',
                    // 'box-shadow': '0 2px 10px rgba(0,0,0,0.05)'
                });
            }
        );

        // Initial render
        // renderBacklogItems();
    });

    // sprint_data is only defined by the view in edit mode
    if (typeof sprint_data !== 'undefined' && sprint_data) {
        const sprint = sprint_data[0];
        $('#productName').val(sprint.r_product_id);
        $('#customer').val(sprint.r_customer_id);
        $('#sprintDuration').val(sprint.r_sprint_duration_id);
        $('#sprintName').val(sprint.sprint_name);
        $('#sprintVersion').val(sprint.sprint_version);
        $('#startDate').val(sprint.start_date);
        $('#endDate').val(sprint.end_date);
        $('#sprintStatus').val(sprint.r_module_status_id);
        // tinymce.get('default').setContent(sprint.sprint_goal);
        document.getElementById('default').innerHTML = sprint.sprint_goal
        //fetchProductTasks(sprint.r_product_id); 

    }
});
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
function handleProductChange() {
    const productSelect = document.getElementById('productName');
    const productSpecificSections = document.getElementById('product-specific-sections');

    productSelect.addEventListener('change', function () {
        if (this.value) {
            productSpecificSections.style.display = 'block';
        } else {
            productSpecificSections.style.display = 'none';
        }
    });
}

// Call this function when the document is ready
document.addEventListener('DOMContentLoaded', handleProductChange);

document.addEventListener('DOMContentLoaded', function () {
    const columns = document.querySelectorAll('.column');
    let timeout;

    columns.forEach(column => {
        column.addEventListener('mouseenter', () => {
            clearTimeout(timeout);
            expandColumn(column);
        });
        column.addEventListener('mouseleave', () => {
            clearTimeout(timeout);
            timeout = setTimeout(() => resetColumns(), 50);
        });
    });

    //     function expandColumn(targetColumn) {
    //       columns.forEach(column => {
    //         if (column === targetColumn) {
    //           column.style.width = '50%';
    //           column.classList.add('expanded');
    //           column.classList.remove('shrunk');
    //         } else {
    //           column.style.width = '16.666%';
    //           column.classList.add('shrunk');
    //           column.classList.remove('expanded');
    //         }
    //       });
    //     }

    //     function resetColumns() {
    //       columns.forEach(column => {
    //         column.style.width = '25%';
    //         column.classList.remove('expanded', 'shrunk');
    //       });
    //     }
});
// Ensure the DOM is fully loaded
document.addEventListener('DOMContentLoaded', function () {
    var toggleBacklog = document.getElementById('toggleBacklog');
    var collapseBacklogEl = document.getElementById('collapseBacklog');
    if (!toggleBacklog || !collapseBacklogEl) {
        return;
    }
    var collapseBacklog = new bootstrap.Collapse(collapseBacklogEl, {
        toggle: false
    });

    toggleBacklog.addEventListener('click', function () {
        if (collapseBacklog._isShown()) {
            collapseBacklog.hide();
        } else {
            collapseBacklog.show();
        }
    });
});
document.addEventListener('DOMContentLoaded', function () {
    const steps = [
        { id: 'step-sprint-detail', fields: ['productName', 'customer', 'sprintName', 'sprintVersion', 'sprintDuration', 'startDate'] },
        { id: 'step-user-stories', button: 'SelectSprintTasks' },
        { id: 'step-add-members', header: 'addMembers' },
        { id: 'step-sprint-planning', header: 'sprintSettingBlock' },
        { id: 'step-sprint-goal', isLastStep: true }
    ];

    let completedSteps = new Set();

    function isStepComplete(step, index) {
        if (step.fields) {
            return step.fields.every(field => {
                const element = document.getElementById(field);
                return element && element.value.trim() !== '';
            });
        }
        if (step.button || step.header) {
            return completedSteps.has(index);
        }
        if (step.isLastStep) {
            return completedSteps.size === steps.length - 1;
        }
        return false;
    }

    function updateProcessFlow() {
        let activeStepFound = false;

        steps.forEach((step, index) => {
            const stepElement = document.getElementById(step.id);
            stepElement.classList.remove('active', 'completed');

            if (isStepComplete(step, index)) {
                stepElement.classList.add('completed');
            } else if (!activeStepFound) {
                stepElement.classList.add('active');
                activeStepFound = true;
            }
        });
    }

    // Add event listeners to all form fields
    steps[0].fields.forEach(field => {
        const element = document.getElementById(field);
        if (element) {
            element.addEventListener('input', () => {
                if (isStepComplete(steps[0], 0)) {
                    completedSteps.add(0);
                } else {
                    completedSteps.delete(0);
                }
                updateProcessFlow();
            });
        }
    });

    // Add event listener to "Select sprint tasks" button
    const selectSprintTasksBtn = document.getElementById('SelectSprintTasks');
    if (selectSprintTasksBtn) {
        selectSprintTasksBtn.addEventListener('click', () => {
            completedSteps.add(1);
            updateProcessFlow();
        });
    }

    // Add event listeners to headers
    ['addMembers', 'sprintSettingBlock'].forEach((headerId, index) => {
        const header = document.getElementById(headerId);
        if (header) {
            header.addEventListener('click', () => {
                completedSteps.add(index + 2);
                updateProcessFlow();
            });
        }
    });

    // Initial update
    updateProcessFlow();
});
function toggleCollapse(collapseId) {
    var collapseElement = document.getElementById(collapseId);
    var icon = collapseElement.previousElementSibling.querySelector('.collapsible-icon');

    if (collapseElement.classList.contains('show')) {
        $(collapseElement).collapse('hide');
        icon.classList.remove('fa-chevron-up');
        icon.classList.add('fa-chevron-down');
    } else {
        $(collapseElement).collapse('show');
        icon.classList.remove('fa-chevron-down');
        icon.classList.add('fa-chevron-up');
    }
}
}