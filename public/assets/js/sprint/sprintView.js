if (typeof m_sprintView !== 'undefined') {
    var totalPages = 0;
    var backlogData = [];
    // Function to filter tasks based on search input
    function filterTasks() {
        //console.log("filtertasks");
        const searchText = document.getElementById('searchit').value.toLowerCase();
        const tasks = taskCheckboxes.querySelectorAll('label:not(:first-child)');
        let visibleTasksCount = 0;

        tasks.forEach(task => {
            const taskText = task.textContent.toLowerCase();
            if (taskText.includes(searchText)) {
                task.style.display = '';
                visibleTasksCount++;
            } else {
                task.style.display = 'none';
            }
        });
    }
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

    document.addEventListener('DOMContentLoaded', function () {

        let sprintSettingAdded = false;
        let selectedActivities = [];

        function createNewSetting() {
            const newSetting = $(`
            <div class="sprint-setting mb-3">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="category" class="form-label">Enter Category</label>
                        <select class="form-select activity-select" name="settingName[]" required>
                            <option value="" disabled selected>Select Activity</option>
                        </select>
                        <div class="invalid-feedback">
                            Please Select an Activity.
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="settingStartDate" class="form-label">Start Date</label>
                        <input type="text" class="form-control mb-3 flatpickr-no-config flatpickr-input settingStartDateInput"
                                name="settingStartDate[]" placeholder="Select Date" readonly="readonly" required>
                        <div class="invalid-feedback">
                            Please Select a Start Date.
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="settingEndDate" class="form-label">End Date</label>
                        <input type="text" class="form-control mb-3 flatpickr-no-config flatpickr-input settingEndDateInput"
                                name="settingEndDate[]" placeholder="Select Date" readonly="readonly" required>
                        <div class="invalid-feedback">
                            Please Select an End Date.
                        </div>
                    </div>
                    <div class="col-md-2 mb-3 d-flex align-items-end">
                        <button type="button" class="btn btn-danger remove-setting cls-dangerhide"><i class="fas fa-trash-alt"></i></button>
                    </div>
                    <div class="row cls-deletebutton">
                        <textarea class="form-control cls-textarea-plan" id="exampleFormControlTextarea1" name="planningComments[]"
                            rows="3" 
                            placeholder="Comments about activity"></textarea>
                            <button type="button" class="btn btn-danger remove-setting cls-danger-setting"><i class="fas fa-trash-alt"></i></button>
                            
                    </div>
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

            $select.on('change', function () {
                const selectedValue = parseInt($(this).val());
                selectedActivities.push(selectedValue);
                $('.activity-select').not(this).each(function () {
                    $(this).find(`option[value="${selectedValue}"]`).prop('disabled', true);
                });
            });

            $('#sprintSettingContainer').append(newSetting);

            flatpickr(newSetting.find('.settingStartDateInput'), {
                minDate: sprintStartDate,
                maxDate: sprintEndDate
            });

            flatpickr(newSetting.find('.settingEndDateInput'), {
                minDate: sprintStartDate,
                maxDate: sprintEndDate
            });

            newSetting.find('.settingStartDateInput').on('change', function () {
                const startDate = $(this).val();
                const endDateInput = newSetting.find('.settingEndDateInput');

                flatpickr(endDateInput, {
                    minDate: startDate,
                    defaultDate: startDate
                });

                endDateInput.val(startDate); // Automatically set end date to start date
            });

            sprintSettingAdded = true;
        }

        $(document).ready(function () {
            createNewSetting();

            $('#addMoreSetting').on('click', createNewSetting);

            $(document).on('click', '.remove-setting', function () {
                const $select = $(this).closest('.sprint-setting').find('.activity-select');
                const selectedValue = parseInt($select.val());
                selectedActivities = selectedActivities.filter(id => id !== selectedValue);
                $('.activity-select').each(function () {
                    $(this).find(`option[value="${selectedValue}"]`).prop('disabled', false);
                });
                $(this).closest('.sprint-setting').remove();
                if ($('#sprintSettingContainer').children().length === 0) {
                    sprintSettingAdded = false;
                }
            });

            $('#saveChanges').on('click', function () {
                let sprintSettings = [];

                $('.sprint-setting').each(function () {
                    const setting = {
                        activityId: $(this).find('.activity-select').val(),
                        startDate: $(this).find('input[name="settingStartDate[]"]').val(),
                        endDate: $(this).find('input[name="settingEndDate[]"]').val(),
                        comments: $(this).find('textarea[name="planningComments[]"]').val()
                    };
                    sprintSettings.push(setting);
                });

                let formValue = {
                    sprintData: sprintSettings,
                    sprint_id: sprintId
                };

                console.log(formValue);

                $.ajax({
                    url: ASSERT_PATH + 'sprint/ReviewSprintPlanDetails',
                    method: 'POST',
                    data: formValue,
                    dataType: 'json',
                    success: function (response) {
                        console.log(response, "Sprint plan details saved successfully");
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: 'Your sprint plan has been updated successfully.',
                                confirmButtonText: 'OK'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.href = ASSERT_PATH + "sprint/navsprintview?sprint_id=" + sprintId;
                                }
                            });
                        } else {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Warning!',
                                text: 'Your sprint plan has not been updated successfully.',
                                confirmButtonText: 'OK'
                            })
                        }

                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        console.error(jqXHR);
                        console.error('Error saving sprint plan details:', textStatus, errorThrown);
                        Swal.fire({
                            icon: 'warning',
                            title: 'Error!',
                            text: 'Your sprint plan has not been updated successfully.',
                            confirmButtonText: 'OK'
                        })
                    }
                });
            });
        });
        // Fetch data with AJAX
        $.ajax({
            url: ASSERT_PATH + 'sprint/navsprintview?sprint_id=' + sprintId, // Adjust URL to match your setup
            method: 'GET',
            dataType: 'json',
            success: function (response) {
                // Assuming 'response' is in the correct format for backlogData and userData
                backlogData = response.data; // Adjust this as per your data structure
                // sprintRetrospective = response.sprintRetrospective; // Adjust this as per your data structure
                //console.log(backlogData);
                // Initialize functions after data retrieval
                generateTable(currentPage);
                totalCompleteTasks(backlogData);
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.error('Error fetching data:', textStatus, errorThrown);
            }
        });

        document.getElementById('sprint-planning-card').addEventListener('click', () => {
            $.ajax({
                url: ASSERT_PATH + 'sprint/sprintplanning?sprint_id=' + sprintId, // Adjust URL to match your setup
                method: 'GET',
                dataType: 'json',
                success: function (response) {
                    userData = response.data;
                    populateSprintDetails(userData);
                    $(function () {
                        $('[data-toggle="tooltip"]').tooltip()
                    });
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.error('Error fetching data:', textStatus, errorThrown);
                }
            });
        })
        document.getElementById('sprint-retrospective-card').addEventListener('click', () => {
            $.ajax({
                url: ASSERT_PATH + 'sprint/retrospectivedetails?sprint_id=' + sprintId, // Adjust URL to match your setup
                method: 'GET',
                dataType: 'json',
                success: function (response) {
                    console.log(response.data);
                    retrospectiveData = response.data[0];
                    retrospectiveDate = response.data[1];
                    console.log(retrospectiveDate);
                    if (document.getElementById('sprint-retrpctve').innerHTML.trim() === '') {
                        populateRetorspectiveDate(retrospectiveDate);
                        populatePros(retrospectiveData);
                        populateCons(retrospectiveData);
                        populateSuggestions(retrospectiveData);
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.error('Error fetching data:', textStatus, errorThrown);
                }
            });
        })

        document.querySelectorAll('.scrum-item').forEach(element => {
            element.addEventListener('click', (event) => {
                event.currentTarget.classList.add('open');
                document.querySelectorAll('.scrum-item').forEach(innerele => {
                    innerele.classList.remove('open');
                });
            });
        });

        document.getElementById('sprint-review-card').addEventListener('click', () => {
            $.ajax({
                url: ASSERT_PATH + 'sprint/fetchReview?sprint_id=' + sprintId, // Adjust URL to match your setup
                method: 'GET',
                dataType: 'json',
                success: function (response) {
                    console.log(response);
                    // Assuming 'response' is in the correct format for backlogData and userData
                    //backlogData = response.data; // Adjust this as per your data structure
                    reviewData = response.data.sprintReview;
                    reviewDate = response.data.sprintReviewDate;
                    console.log(reviewData);

                    if (document.getElementById('reviewDetails').innerHTML.trim() === '') {
                        populateReviewData(reviewData, reviewDate);
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.error('Error fetching data:', textStatus, errorThrown);
                }
            });
        })

        const itemsPerPage = 8;
        let presentPage = 1;

        function displayActivities(page) {
            //console.log("inside displayActivities");
            //console.log(activities);
            const startIndex = (page - 1) * itemsPerPage;
            const endIndex = startIndex + itemsPerPage;
            const dataList = document.getElementById('dataList');
            dataList.innerHTML = '';

            let currentDate = null;
            for (let i = startIndex; i < endIndex && i < activities.length; i++) {
                const printData = activities[i];
                //console.log(printData.date);
                if (!currentDate || currentDate !== printData.date) {
                    currentDate = printData.date;
                    const dateHeader = document.createElement('div');
                    dateHeader.className = 'date-header';
                    dateHeader.textContent = printData.date;
                    dataList.appendChild(dateHeader);
                }
                const li = document.createElement('li');
                li.className = 'data-item';
                li.innerHTML = `
                <span class="data-time">${printData.time}</span>
                <span class="data-icon">${printData.icon}</span>
                <div class="data-details">
                    <div class="data-title">${printData.title}</div>
                    <div class="data-source">${printData.source}</div>
                </div>
            `;
                dataList.appendChild(li);
            }

            updatePaginationButtons();
        }

        function updatePaginationButtons() {
            const previous = document.getElementById('previous');
            const next = document.getElementById('next');

            previous.disabled = presentPage === 1;
            next.disabled = presentPage === Math.ceil(activities.length / itemsPerPage);
        }

        document.getElementById('previous').addEventListener('click', () => {
            if (presentPage > 1) {
                presentPage--;
                displayActivities(presentPage);
            }
        });

        document.getElementById('next').addEventListener('click', () => {
            if (presentPage < Math.ceil(activities.length / itemsPerPage)) {
                presentPage++;
                displayActivities(presentPage);
            }
        });

        flatpickr("#startDate", {
            onChange: function (selectedDates, dateStr, instance) {
                // When start date changes, update end date picker
                const endDatePicker = flatpickr("#endDate", {
                    minDate: dateStr,
                    defaultDate: dateStr
                });
                // Set end date to the same value as start date
                document.getElementById('endDate').value = dateStr;
            }
        });

        // Initialize end date picker
        flatpickr("#endDate", {
            minDate: "today"
        });
        let activities = [];
        let filteredActivities = [];
        let currentPage = 1;
        const perPageItem = 8;

        document.getElementById('spntHis').addEventListener('click', loadActivities);
        document.getElementById('clearHistory').addEventListener('click', clearHistory);
        document.getElementById('filterButton').addEventListener('click', filterActivities);
        document.getElementById('previous').addEventListener('click', () => changePage(-1));
        document.getElementById('next').addEventListener('click', () => changePage(1));

        function loadActivities() {
            $.ajax({
                url: ASSERT_PATH + 'sprint/navsprinthistory?sprint_id=' + sprintId,
                method: 'GET',
                dataType: 'json',
                success: function (response) {
                    activities = response.data.users.map((user, index) => ({
                        date: response.data.date[index],
                        time: response.data.time[index],
                        icon: response.data.action[index][0].toUpperCase(),
                        title: response.data.action[index],
                        source: user,
                    }));
                    filteredActivities = [...activities];
                    displayActivities();
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.error('Error fetching data:', textStatus, errorThrown);
                }
            });
        }

        function filterActivities() {
            const startDateInput = document.getElementById('startDate').value;
            const endDateInput = document.getElementById('endDate').value;

            if (!startDateInput || !endDateInput) {
                return; // Do nothing if either date is not selected
            }

            // Normalize startDate and endDate to midnight
            const startDate = new Date(startDateInput);
            startDate.setHours(0, 0, 0, 0);
            const endDate = new Date(endDateInput);
            endDate.setHours(23, 59, 59, 999);

            filteredActivities = activities.filter(activity => {
                const activityDate = new Date(activity.date);
                return activityDate >= startDate && activityDate <= endDate;
            });

            currentPage = 1;
            displayActivities();
        }


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

            const startIndex = (currentPage - 1) * perPageItem;
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
                        <span class="time">${activity.time}</span>
                        <span class="icon">${activity.icon}</span>
                        <span class="title mt-3">${activity.title} <p><span class="source">${activity.source}</span></p></span>
                    </div>
                </div>
            `;
                dataList.appendChild(li);
            });

            updatePaginationButtons();
        }

        function updatePaginationButtons() {
            const totalPages = Math.ceil(filteredActivities.length / perPageItem);
            document.getElementById('previous').disabled = currentPage === 1;
            document.getElementById('next').disabled = currentPage === totalPages;
        }

        function changePage(direction) {
            const newPage = currentPage + direction;
            const totalPages = Math.ceil(filteredActivities.length / perPageItem);
            if (newPage >= 1 && newPage <= totalPages) {
                currentPage = newPage;
                displayActivities();
            }
        }

        function clearHistory() {
            activities = [];
            filteredActivities = [];
            currentPage = 1;
            displayActivities();
        }

        document.getElementById('resetButton').addEventListener('click', resetActivities);

        function resetActivities() {
            filteredActivities = [...activities];
            currentPagee = 1;

            // Clear filter inputs
            document.getElementById('startDate').value = '';
            document.getElementById('endDate').value = '';

            displayActivities();
        }

        // Scroll to top button functionality
        window.addEventListener('scroll', function () {
            const scrollTopButton = document.getElementById('scrollTopButton');
            const middleOfPage = document.documentElement.scrollHeight / 8;

            if (window.scrollY >= middleOfPage) {
                scrollTopButton.classList.add('show');
            } else {
                scrollTopButton.classList.remove('show');
            }
        });

        document.getElementById('scrollTopButton').addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });

        // Collapse state toggling
        const collapseHeaders = document.querySelectorAll('.card-header button');
        collapseHeaders.forEach(header => {
            header.addEventListener('click', function () {
                const targetCollapseId = this.getAttribute('data-bs-target');
                const isExpanded = this.getAttribute('aria-expanded') === 'true';

                // Close all other collapses
                collapseHeaders.forEach(otherHeader => {
                    if (otherHeader.getAttribute('data-bs-target') !== targetCollapseId) {
                        otherHeader.setAttribute('aria-expanded', 'false');
                        const otherCollapseTarget = document.querySelector(otherHeader.getAttribute('data-bs-target'));
                        if (otherCollapseTarget) {
                            otherCollapseTarget.classList.remove('show');
                        }
                    }
                });

                // Toggle current collapse
                this.setAttribute('aria-expanded', isExpanded ? 'false' : 'true');
                const targetCollapse = document.querySelector(targetCollapseId);
                if (targetCollapse) {
                    if (isExpanded) {
                        targetCollapse.classList.remove('show');
                    } else {
                        targetCollapse.classList.add('show');
                    }
                }
            });
        });

        document.querySelectorAll('.scrum-item').forEach(item => {
            item.addEventListener('click', () => {
                item.classList.toggle('open');
            });
        });

        // Sprint Retrospective
        const datePicker = document.getElementById('date-picker');
        const today = new Date().toISOString().split('T')[0];
        datePicker.value = today;
        datePicker.disabled = true;
        datePicker.style.opacity = "0.6";

        const feedbackLabel = document.getElementById('feedback-label');
        const textarea = document.getElementById('general');
        const typeError = document.getElementById('typeError');
        const generalError = document.getElementById('generalError');

        document.querySelectorAll('input[name="feedback-type"]').forEach(radio => {
            radio.addEventListener('change', function () {
                typeError.style.display = 'none';
                if (this.value === 'pros') {
                    feedbackLabel.innerHTML = '<h5 class="headerName"><i class="fas fa-thumbs-up"></i> Pros</h5>';
                    textarea.placeholder = 'Enter your pros';
                } else if (this.value === 'cons') {
                    feedbackLabel.innerHTML = '<h5 class="headerName"><i class="fas fa-exclamation-triangle"></i> Cons</h5>';
                    textarea.placeholder = 'Enter your cons';
                } else if (this.value === 'lns') {
                    feedbackLabel.innerHTML = '<h5 class="headerName"><i class="fas fa-envelope"></i> Suggestions</h5>';
                    textarea.placeholder = 'Enter your suggestions';
                }
            });
        });

        const feedbackData = {
            pros: '',
            cons: '',
            lns: ''
        };

        document.querySelectorAll('input[name="feedback-type"]').forEach(radio => {
            radio.addEventListener('change', function () {
                const selectedType = document.querySelector('input[name="feedback-type"]:checked').value;
                document.getElementById('general').value = feedbackData[selectedType];
            });
        });

        document.getElementById('general').addEventListener('input', function () {
            const selectedType = document.querySelector('input[name="feedback-type"]:checked').value;
            feedbackData[selectedType] = this.value;
        });

        document.getElementById('saveRetrospective').addEventListener('click', function (event) {
            let valid = true;
            const selectedType = document.querySelector('input[name="feedback-type"]:checked');
            const generalValue = document.getElementById('general').value.trim();

            if (!selectedType) {
                document.getElementById('typeError').style.display = 'block';
                valid = false;
            } else {
                document.getElementById('typeError').style.display = 'none';
            }

            if (generalValue === '') {
                document.getElementById('generalError').style.display = 'block';
                valid = false;
            } else {
                document.getElementById('generalError').style.display = 'none';
            }

            if (!valid) {
                return;
            }

            const formData = {
                "sprint_id": sprintId,
                "feedbacks": feedbackData,
                "added_date": today
            };

            //console.log(formData);

            $.ajax({
                url: ASSERT_PATH + 'sprint/sprintretrospective',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function (response) {
                    console.log(response);
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: "Sprint retrospective updated successfully",
                            confirmButtonText: 'OK'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = ASSERT_PATH + "sprint/navsprintview?sprint_id=" + sprintId;
                            }
                        });
                    } else {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Warning!',
                            text: 'Retrospective has not been updated',
                            confirmButtonText: 'OK'
                        })
                    }
                },
                error: function (jqXHR, textStatus, errorThrown, response) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Error!',
                        text: "Sprint retrospective not updated",
                        confirmButtonText: 'OK'
                    })
                }
            });
        });
    });

    var itemsPerPage = 1; // Number of items per page
    var currentPage = 1; // Current page
    var selectedStatus = 'all'; // Default to show all statuses

    generateTable(currentPage);

    // Function to apply the status filter
    function applyStatusFilter() {
        selectedStatus = document.getElementById('statusFilter').value;
        currentPage = 1
        generateTable(currentPage);
    }

    function updateGradientBar(percentage) {
        const gradientBar = document.getElementById('gradientBar');
        const barWidth = percentage; // percentage directly maps to pixel width

        // Calculate the gradient stops based on the width of the bar
        let gradient = `linear-gradient(to right, red 17px, orange 49px, green 144px)`;

        gradientBar.style.background = gradient;
        gradientBar.style.width = `${barWidth}%`;
    }

    // Function to generate the HTML table with pagination and filtering
    function generateTable(page) {

        document.getElementById('table-containers').style.display = 'block'
        document.getElementById('myNav').style.display = 'flex'
        document.getElementById('errormsg').style.display = 'none';
        var tableBody = document.getElementById('table-body');
        var pagination = document.getElementById('pagination');
        var tableContent = '';
        var startIndex = (page - 1) * itemsPerPage; var endIndex = startIndex + itemsPerPage;
        var filteredBacklogData = filterBacklogDataByStatus(backlogData, selectedStatus);
        console.log(filteredBacklogData);
        if (filteredBacklogData.length === 0 && selectedStatus != 'all') {
            errormsg();
        }


        document.getElementById('total-backlogs').innerHTML = backlogData.length;

        var backlogSlice = filteredBacklogData.slice(startIndex, endIndex);
        backlogSlice.forEach(function (backlogItem, backlogIndex) {
            var backlogName = backlogItem.backlog;
            var backlogRowSpan = countBacklogRows(backlogItem);
            var completedTasks = countCompletedTasks(backlogItem);
            var totalTasks = countTotalTasks(backlogItem);
            var completionPercentage = totalTasks > 0 ? ((completedTasks / totalTasks) * 100) : 0;
            progress(completionPercentage);

            backlogItem.epics.forEach(function (epicItem, epicIndex) {
                var epicName = epicItem.epic;
                var epicRowSpan = countEpicRows(epicItem);

                epicItem.userStories.forEach(function (userStoryItem, userStoryIndex) {
                    var userStoryName = userStoryItem.userStory;
                    var userStoryId = userStoryItem.userStoryId;
                    var userStoryRowSpan = countUserStoryRows(userStoryItem);

                    userStoryItem.tasks.forEach(function (taskItem, taskIndex) {
                        var taskName = taskItem.task;
                        var status = taskItem.status;
                        var percentage = taskItem.percentage;

                        if (taskIndex === 0) {
                            tableContent += '<tr>';
                            if (epicIndex === 0 && userStoryIndex === 0) {
                                document.getElementById('backlogName').innerHTML = '<h6>Backlog Name:</h6><span>' + backlogName + '</span> </h5>';
                            }
                            if (userStoryIndex === 0) {
                                tableContent += '<td rowspan="' + epicRowSpan + '" class="epic-cell">' + epicName + '</td>';
                            }
                            tableContent += '<td rowspan="' + userStoryRowSpan + '" class="user-story">US_' + userStoryId + '</td>';
                            tableContent += '<td rowspan="' + userStoryRowSpan + '" class="user-story-cell">' + userStoryName + '</td>';
                            tableContent += '<td class="task-cell">' + taskName + ' - <b>' + percentage + ' %</b></td>';
                            tableContent += `<td class='status-cell'><span class="status">${status}</span></td>`;
                            tableContent += '</tr>';
                        } else {
                            tableContent += '<tr>';
                            tableContent += '<td class="task-cell">' + taskName + ' - <b>' + percentage + ' %</b></td>';
                            tableContent += `<td class='status-cell'><span class="status">${status}</span></td>`;
                            tableContent += '</tr>';
                        }
                    });
                });
            });
        });

        tableBody.innerHTML = tableContent;

        totalPages = Math.ceil(filteredBacklogData.length / itemsPerPage);

        if (currentPage === 1) {
            //console.log('hide prev')
            document.getElementById('prevbutton').style.opacity = "0.2";
            document.getElementById('prevbutton').style.disabled = true;
            document.getElementById('nextbutton').style.opacity = "0.9";
            document.getElementById('nextbutton').style.disabled = false;

        }
        if (currentPage == 1 && totalPages == 1) {
            document.getElementById('prevbutton').style.opacity = "0.2";
            document.getElementById('prevbutton').style.disabled = true;
            document.getElementById('nextbutton').style.opacity = "0.2";
            document.getElementById('nextbutton').style.disabled = true;
        }
        // for (var i = 1; i <= totalPages; i++) {
        //     paginationContent += '<li class="page-item' + (currentPage === i ? ' active' : '') + '"><a class="page-link"  onclick="changePage(' + i + ')">' + i + '</a></li>';
        // }

    }

    function errormsg() {
        document.getElementById('table-containers').style.display = 'none'
        document.getElementById('myNav').style.display = 'none'
        document.getElementById('errormsg').style.display = 'block';

    }

    // document.addEventListener('DOMContentLoaded', function () {
    //     const tableStriped = document.querySelector('.table-container');
    //     const nav = document.getElementById('myNav');

    //     // Add mouseover event listener
    //     tableStriped.addEventListener('mouseover', function () {
    //         nav.style.display = 'block'; // Show nav
    //     });

    //     // Add mouseout event listener
    //     tableStriped.addEventListener('mouseout', function () {
    //         nav.style.display = 'none'; // Hide nav
    //     });
    // });


    document.getElementById('nextbutton').addEventListener('click', (event) => {
        if (currentPage === totalPages) {
            return 0;
        }
        else {

            currentPage++;
            if (currentPage === totalPages) {
                //console.log('hide next')
                document.getElementById('nextbutton').style.opacity = "0.2";
                document.getElementById('nextbutton').style.disabled = true;
                document.getElementById('prevbutton').style.opacity = "0.9";
                document.getElementById('prevbutton').style.disabled = false;
            }
            else {
                //console.log('show -prev')
                document.getElementById('nextbutton').style.opacity = "0.9";
                document.getElementById('prevbutton').style.opacity = "0.9";
            }
            //console.log('next', currentPage);
            generateTable(currentPage)
        }
    })

    document.getElementById('prevbutton').addEventListener('click', (event) => {

        if (currentPage === 1) {

            return 0;
        }
        else {

            currentPage--
            if (currentPage === 1) {
                //console.log('hide prev')
                document.getElementById('prevbutton').style.opacity = "0.2";
                document.getElementById('prevbutton').style.disabled = true;
                document.getElementById('nextbutton').style.opacity = "0.9";
                document.getElementById('nextbutton').style.disabled = false;

            }
            else {
                //console.log('show -next')
                document.getElementById('nextbutton').style.opacity = "0.9";
                document.getElementById('prevbutton').style.opacity = "0.9";
            }
            //console.log('prev', currentPage);
            generateTable(currentPage)
        }
    })

    function filterBacklogDataByStatus(data, status) {
        if (status === 'all') {
            return data;
        }

        return data.map(backlogItem => {
            var filteredEpics = backlogItem.epics.map(epicItem => {
                var filteredUserStories = epicItem.userStories.map(userStoryItem => {
                    var filteredTasks = userStoryItem.tasks.filter(taskItem => taskItem.status === status);
                    return {
                        ...userStoryItem,
                        tasks: filteredTasks
                    };
                }).filter(userStoryItem => userStoryItem.tasks.length > 0);

                return {
                    ...epicItem,
                    userStories: filteredUserStories
                };
            }).filter(epicItem => epicItem.userStories.length > 0);

            return {
                ...backlogItem,
                epics: filteredEpics
            };
        }).filter(backlogItem => backlogItem.epics.length > 0);
    }

    function changePage(page) {
        currentPage = page;
        generateTable(currentPage);
    }

    function countTotalTasks(backlogItem) {
        var totalTasks = 0;
        backlogItem.epics.forEach(function (epicItem) {
            epicItem.userStories.forEach(function (userStoryItem) {
                totalTasks += userStoryItem.tasks.length;
            });
        });
        return totalTasks;
    }

    function progress(completionPercentage) {
        var progressText = 'Progress:<span class="completion">' + Math.ceil(completionPercentage) + '%</span><div class="progress-bar "><div class="progress progress-bar-striped" id="backlogProgress" style="width: ' + completionPercentage + '%;"></div>';
        var progress = document.getElementById('progress-bar')
        progress.innerHTML = progressText;
    }

    function countCompletedTasks(backlogItem) {
        var completedTasks = 0;
        backlogItem.epics.forEach(function (epicItem) {
            epicItem.userStories.forEach(function (userStoryItem) {
                userStoryItem.tasks.forEach(function (taskItem) {
                    if (taskItem.status === 'Completed' || taskItem.status === 'Assign for UAT'
                        || taskItem.status === 'Assign for Testing' || taskItem.status === 'Move to Live'
                        || taskItem.status === 'Move to Prelive') {
                        completedTasks++;
                    }
                });
            });
        });
        return completedTasks;
    }

    function totalCompleteTasks(userData) {
        var completedTasks = 0;
        var totalTasks = 0;
        userData.forEach(backlog => {
            backlog.epics.forEach(epic => {
                epic.userStories.forEach(userStories => {
                    userStories.tasks.forEach(tasks => {
                        totalTasks += 1;
                        if (tasks.status === 'completed' || tasks.status === 'Completed' || tasks.status === 'Assign for UAT'
                            || tasks.status === 'Assign for Testing' || tasks.status === 'Move to Live'
                            || tasks.status === 'Move to Prelive') {
                            completedTasks += 1;
                        }
                    });
                });
            });
        });
        document.getElementById('total-tasks').innerHTML = totalTasks;
        document.getElementById('task-no').innerHTML = completedTasks;
    }

    function countBacklogRows(backlogItem) {
        var rowCount = 0;
        backlogItem.epics.forEach(function (epicItem) {
            epicItem.userStories.forEach(function (userStoryItem) {
                rowCount += userStoryItem.tasks.length;
            });
        });
        return rowCount;
    }

    function countEpicRows(epicItem) {
        var rowCount = 0;
        epicItem.userStories.forEach(function (userStoryItem) {
            rowCount += userStoryItem.tasks.length;
        });
        return rowCount;
    }

    function countUserStoryRows(userStoryItem) {
        return userStoryItem.tasks.length;
    }

    function storeInitialValues() {
        document.querySelectorAll('select[name="status"]').forEach(selectElement => {
            selectElement.dataset.initialText = selectElement.selectedOptions[0].innerText;
        });
    }

    function populateSprintDetails(userData) {
        console.log(userData);
        var sprintList = document.getElementById('sprintList');

        if (userData.length > 0) {
            sprintList.innerHTML = ''; // Clear previous content

            userData.forEach(item => {
                var sprintItem = document.createElement('tr');
                sprintItem.classList.add('sprint-item');
                // Create the options for the select dropdown
                var options = sprintPlanningStatus.map(status => {
                    return `<option value="${status.module_status_id}"${status.status_name === item.status_name ? ' selected' : ''}>${status.status_name}</option>`;
                }).join('');

                sprintItem.innerHTML = `<td class="hidden">${item.r_sprint_activity_id}</td>
                                    <td>${item.activity}</td>
                                    <td>${item.startDate}</td>
                                    <td>${item.endDate}</td>`;
                if (permit) {
                    sprintItem.innerHTML += `<td class="activity">
                <select class="form-select" name="status" id="sprintPlanStatus">
                    ${options}
                </select>
            </td>`;
                } else {
                    sprintPlanningStatus.map(status => {
                        if (status.status_name === item.status_name) {
                            sprintItem.innerHTML += `<td>${status.status_name}</td>`;
                        }
                    })
                }

                sprintItem.innerHTML += `<td>
                                        <div class="maintext" data-toggle="tooltip" data-placement="top" title="${item.notes}" data-toggle="tooltip" data-placement="top">
                                            ${item.notes.substring(0, 10)}...
                                        </div>
                                    </td>`;

                sprintList.appendChild(sprintItem);
            });

            // Add event listeners to the newly created select elements
            storeInitialValues();
            addChangeEventListeners();
            addaddChangeEventListeners();
        } else {
            sprintList.innerHTML = '<tr><td colspan="5" style="text-align:center">Data not found</td></tr>';
        }
    }

    function addChangeEventListeners() {
        document.querySelectorAll('select[name="status"]').forEach(selectElement => {
            selectElement.addEventListener('change', function () {
                //console.log("plan");
                var status = this.value;
                var activity = this.closest('tr').querySelector('td:first-child').innerText;
                var activityName = this.closest('tr').querySelector('td:nth-child(2)').innerText;
                var previousValue = this.dataset.initialText;
                var newValue = this.selectedOptions[0].innerText;

                //console.log(previousValue, "previous");
                var planValue = {
                    r_status_id: status,
                    sprint_id: sprintId,
                    activity_id: activity,
                    activity_name: activityName,
                    prev_value: previousValue,
                    new_value: newValue
                };

                $.ajax({
                    url: ASSERT_PATH + 'sprint/updatePlan',
                    method: 'POST',
                    data: planValue,
                    dataType: 'json',
                    success: function (response) {
                        //console.log(response, "Sprint plan details saved successfully");
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: 'Your sprint plan has been updated successfully.',
                            showConfirmButton: false
                        });
                        setTimeout(function () {
                            Swal.close();
                        }, 1500);
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        console.error(jqXHR);
                        console.error('Error saving sprint plan details:', textStatus, errorThrown);
                        Swal.fire({
                            icon: 'warning',
                            title: 'Error!',
                            text: 'Your sprint plan has not been updated',
                            showConfirmButton: false
                        });
                        setTimeout(function () {
                            Swal.close();
                        }, 2000);
                    }
                });
            });
        });
    }

    document.querySelectorAll('select[name="sprintStatus"]').forEach(selectElement => {
        selectElement.addEventListener('change', function () {
            Swal.fire({
                title: 'Are you sure?',
                text: "Do you want to update status?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes',
                cancelButtonText: 'No'
            }).then((result) => {
                if (result.isConfirmed) {
                    var status = this.value;
                    var previousValue = sprintStatus;
                    var newValue = this.selectedOptions[0].innerText;

                    var planValue = {
                        r_status_id: status,
                        sprint_id: sprintId,
                        prev_value: previousValue,
                        new_value: newValue
                    };

                    $.ajax({
                        url: ASSERT_PATH + 'sprint/updateSprintStatus',
                        method: 'POST',
                        data: planValue,
                        dataType: 'json',
                        success: function (response) {
                            console.log(response);
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success!',
                                    text: 'Your sprint status has been updated successfully.',
                                    showConfirmButton: false
                                });
                                setTimeout(function () {
                                    Swal.close();
                                }, 1500);
                                location.reload();
                            } else {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Error!',
                                    text: 'Your sprint status has not been updated.',
                                    showConfirmButton: false
                                });
                                setTimeout(function () {
                                    Swal.close();
                                }, 1500);
                            }
                        },
                        error: function (jqXHR, textStatus, errorThrown) {
                            console.error(jqXHR);
                            console.error('Error saving sprint plan details:', textStatus, errorThrown);
                            Swal.fire({
                                icon: 'warning',
                                title: 'Error!',
                                text: 'Your sprint status has not been updated',
                                showConfirmButton: false
                            });
                            setTimeout(function () {
                                Swal.close();
                            }, 2000);
                        }
                    });
                } else {
                    Swal.fire(
                        'Status updation cancelled',
                        'Status has not been updated.',
                        'info'
                    ).then((result) => { 
                        if(result.isConfirmed){
                            // var currentStatus = document.getElementById('sprintStatus');
                            // currentStatus.innerHTML = sprintStatus;
                            location.reload();
                        }
                    });
                }
            });
        });
    });

    // Function to calculate days between start date and today
    function calculateDaysLeft(startDate) {
        if (!startDate) return '';

        var oneDay = 24 * 60 * 60 * 1000; // hours * minutes * seconds * milliseconds
        var today = new Date();
        today.setHours(0, 0, 0, 0); // Reset hours to start of the day

        // Calculate difference in days
        var daysLeft = Math.ceil((startDate - today) / oneDay);

        return daysLeft >= 0 ? daysLeft + ' days' : 'Expired';
    }
    function getCompletionStatus(startDate, endDate, today) {

        if (!startDate || !endDate) {
            return '';
        }

        if (today < startDate) {
            return 'Not-started';
        }
        else if (today >= startDate && today <= endDate) {
            return 'in-progress';
        } else if (today > endDate) {
            return 'completed';
        } else {
            return 'delayed';
        }
    }
    function parseFormattedDate(dateString) {
        var [year, month, day] = dateString.split('-').map(Number);
        return new Date(year, month - 1, day); // month is zero-indexed in Date constructor
    }
    function populateRetorspectiveDate(retrospectiveDate) {
        document.getElementById('retrospectiveDate').innerHTML = retrospectiveDate;

    }
    function populatePros(data) {
        if (data['pros']) {

            var prosparent = document.getElementById('sprint-retrpctve');
            var pros1 = document.createElement('div');
            pros1.setAttribute('id', 'prosSection');
            pros1.setAttribute('class', 'pros');
            var prosList = data['pros']['notes'].map(item => `<li>${item}</li>`).join('');
            pros1.innerHTML = `<h5>Pros</h5><ul>${prosList}</ul>`;
            //console.log(pros1);
            prosparent.appendChild(pros1);
        }
    }

    function populateCons(data) {
        if (data['cons']) {
            var consparent = document.getElementById('sprint-retrpctve');
            var cons1 = document.createElement('div');
            cons1.setAttribute('id', 'consSection');
            cons1.setAttribute('class', 'cons');
            var consList = data['cons']['notes'].map(item => `<li>${item}</li>`).join('');
            cons1.innerHTML = `
        <h5>Cons/Challenges</h5>
        <ul>${consList}</ul>`;
            consparent.appendChild(cons1);
        }
    }

    function populateSuggestions(data) {
        if (data['lns']) {
            var suggparent = document.getElementById('sprint-retrpctve');
            var sugg = document.createElement('div');
            sugg.setAttribute('id', 'suggestionsSection');
            sugg.setAttribute('class', 'suggestions');
            var suggestionsList = data['lns']['notes'].map(item => `<li>${item}</li>`).join('');
            sugg.innerHTML = `<h5>Suggestions</h5><ul>${suggestionsList}</ul>`;
            suggparent.appendChild(sugg);
            //console.log(sugg);
        }
    }

    function populateChallengesSection() {
        const challengesSection = document.getElementById("challenges-section");
        challengesSection.innerHTML = ""; // Clear existing content

        sprintRetrospective.challenges.forEach(function (challenge) {
            const listItem = document.createElement("li");
            listItem.textContent = challenge;
            challengesSection.appendChild(listItem);
        });
    }
    function populateReviewData(reviewData, reviewDate) {
        document.getElementById('reviewDate').innerText = reviewDate;
        //console.log(document.getElementById('reviewDate'))
        var displyList = [reviewData.General, reviewData.challengeFaced, reviewData.sprintGoal];
        var ReviewElement = document.getElementById('sprintReviewDisplay');
        var GeneralELements = document.createElement('div');
        var GeneralELementsHeading = document.createElement('h4');
        GeneralELementsHeading.innerHTML = "General";
        var GeneralElementsData = document.createElement('div');
        GeneralElementsData.setAttribute('class', 'pros');
        GeneralElementsData.textContent = reviewData['General'] || '';
        GeneralELements.appendChild(GeneralELementsHeading)
        GeneralELements.appendChild(GeneralElementsData)

        if (reviewData['challengeFaced']['status'] === 'Y') {
            var challengeFacedElement = document.createElement('div');
            var challengeFacedElementHeader = document.createElement('h4');
            challengeFacedElementHeader.innerHTML = "Challenge faced - yes";
            var challengeFacedStatus = document.createElement('span');
            challengeFacedStatus.textContent = "yes";
            challengeFacedStatus.setAttribute('class', '');
            var challengeFacedElementData = document.createElement('div');
            challengeFacedElementData.setAttribute('class', 'pros');
            challengeFacedElementData.textContent = reviewData.challengeFaced['notes'] || '';
            challengeFacedElement.appendChild(challengeFacedElementHeader)
            //challengeFacedElement.appendChild(challengeFacedStatus)
            challengeFacedElement.appendChild(challengeFacedElementData)

        }
        else if (reviewData['challengeFaced']['status'] === 'N') {
            var challengeFacedElement = document.createElement('div');
            var challengeFacedElementHeader = document.createElement('h4');
            challengeFacedElementHeader.innerHTML = "Challenge faced - no";
            var challengeFacedStatus = document.createElement('span');
            challengeFacedStatus.textContent = "no";
            challengeFacedStatus.setAttribute('class', '');
            challengeFacedElement.appendChild(challengeFacedElementHeader)
            //challengeFacedElement.appendChild(challengeFacedStatus)
        }

        if (reviewData['codeReview']['status'] === 'N') {
            var codeReviewElement = document.createElement('div');
            var codeReviewElementHeader = document.createElement('h4');
            codeReviewElementHeader.innerHTML = "Code review - no";
            var codeReviewStatus = document.createElement('span');
            codeReviewStatus.textContent = "no";
            var codeReviewElementData = document.createElement('div');
            codeReviewElementData.setAttribute('class', 'pros');
            codeReviewElementData.textContent = reviewData['codeReview']['notes'] || '';
            codeReviewElement.appendChild(codeReviewElementHeader)
            //codeReviewElement.appendChild(codeReviewStatus)
            codeReviewElement.appendChild(codeReviewElementData)

        }
        else if (reviewData['codeReview']['status'] === 'Y') {
            console.log(reviewData['codeReview']['reviewers']);
            var codeReviewElement = document.createElement('div');
            var codeReviewElementHeader = document.createElement('h4');
            codeReviewElementHeader.innerHTML = "Code review - yes";
            var codeReviewStatus = document.createElement('span');
            codeReviewStatus.textContent = "yes";
            var codeReviewElementData = document.createElement('div');
            codeReviewElementData.setAttribute('class', 'pros');
            let reviewersText = Array.isArray(reviewData['codeReview']['reviewers']) ? reviewData['codeReview']['reviewers'].join(', ') : '';
            codeReviewElementData.textContent = "Code reviewers: " + reviewersText;
            codeReviewElement.appendChild(codeReviewElementHeader)
            codeReviewElement.appendChild(codeReviewElementData)
            console.log(codeReviewElementHeader);
            //codeReviewElement.appendChild(codeReviewStatus)

        }
        if (reviewData['sprintGoal']['status'] === 'N') {
            var sprintGoalElement = document.createElement('div');
            var sprintGoalElementHeader = document.createElement('h4');
            sprintGoalElementHeader.innerHTML = `Sprint goal - no`;
            // var codeReviewStatus=document.createElement('button');
            // codeReviewStatus.textContent="Yes";
            // codeReviewStatus.setAttribute('class','');
            var sprintGoalElementStatus = document.createElement('span');
            sprintGoalElementStatus.textContent = "no";
            sprintGoalElementStatus.setAttribute('class', '');
            var sprintGoalElementData = document.createElement('div');
            sprintGoalElementData.setAttribute('class', 'pros');
            sprintGoalElementData.textContent = reviewData['sprintGoal']['notes'] || '';
            sprintGoalElement.appendChild(sprintGoalElementHeader)
            //sprintGoalElement.appendChild(sprintGoalElementStatus)
            sprintGoalElement.appendChild(sprintGoalElementData)

        }
        else if (reviewData['sprintGoal']['status'] === 'Y') {
            var sprintGoalElement = document.createElement('div');
            var sprintGoalElementHeader = document.createElement('h4');
            sprintGoalElementHeader.innerHTML = "Sprint goal - yes";
            var sprintGoalElementStatus = document.createElement('span');
            sprintGoalElementStatus.textContent = "Yes";
            sprintGoalElementStatus.setAttribute('class', '');
            sprintGoalElement.appendChild(sprintGoalElementHeader)
            //sprintGoalElement.appendChild(sprintGoalElementStatus)

        }

        document.getElementById('reviewDetails').appendChild(GeneralELements)
        document.getElementById('reviewDetails').appendChild(challengeFacedElement)
        document.getElementById('reviewDetails').appendChild(codeReviewElement)
        document.getElementById('reviewDetails').appendChild(sprintGoalElement)
    }


    Object.values(dailyScrum).forEach((item, index) => {
        var scrumDairy = "";

        if (!(item.Y == "")) {
            scrumDairy += `<div class="scrum-item" style='border:1px solid red;'><div class="scrum-item-title" id="daily-scrum"><span>Daily scrum ${Object.keys(dailyScrum)[index]}</span></div><div class="scrum-item-details"><div class="scrum-details">`;

        }
        else {
            scrumDairy += `<div class="scrum-item"'><div class="scrum-item-title" id="daily-scrum"><span>Daily scrum ${Object.keys(dailyScrum)[index]}</span></div><div class="scrum-item-details"><div class="scrum-details">`;
        }
        if (!(item.N == "")) {
            scrumDairy += `<p class ="ds_title">Review</p><div class='scrum-para'>`;
            item.N.forEach(data => {
                scrumDairy += `<p class='ds_Review'>${data[0]} - ${data[1]}</p>`;

            })
            scrumDairy += '</div>';
        }
        if (!(item.Y == "")) {
            scrumDairy += `<p class ="ds_title">Challenge</p><div class='scrum-para'>`;
            item.Y.forEach(data => {
                scrumDairy += `<p class='ds_Challenge'>${data[0]} - ${data[1]}</p>`;

            })
            scrumDairy += '</div>';
        }
        scrumDairy += '</div></div></div>'
        document.getElementById('errorsdmsg').style.display = 'none';
        document.getElementById('dailyScrumComponents').style.display = 'block';
        document.getElementById('dailyScrumComponents').innerHTML += scrumDairy;
    })

    // 
    document.getElementById('sprintMembersList').addEventListener('click', () => {
        //console.log(sprintId);
        $.ajax({
            url: ASSERT_PATH + 'sprint/fetchMembers?sprint_id=' + sprintId, // Adjust URL to match your setup
            method: 'GET',
            dataType: 'json',
            success: function (response) {
                //console.log(response);
                generatemembers(response.data);
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.error('Error fetching data:', textStatus, errorThrown);
            }
        });
    })

    function generatemembers(Members) {
        var MembersTable = document.getElementById('Members-body')
        var MembersValues = '';
        Members.forEach(member => {
            MembersValues += `<tr>`;
            Object.values(member).filter((rows, index) => {
                MembersValues += `<td>${rows}</td>`

            })
            MembersValues += `</tr>`;
            MembersTable.innerHTML = MembersValues;
            //console.log(MembersValues);
        })
    }

    // scrum diary

    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('scrumDiaryForm');
        const generalTextarea = document.getElementById('general');
        const generalError = document.getElementById('generalError');
        const radioError = document.getElementById('radioError');
        const challengesRadios = document.querySelectorAll('input[name="challenges"]');
        const formGroupHeader = document.getElementById('formGroupHeader');
        const datePicker = document.getElementById('datepicker');
        const taskSelect = document.getElementById('taskSelect');
        const selectSelected = taskSelect.querySelector('.select-selected');
        const taskCheckboxes = document.getElementById('taskCheckboxes');
        const selectAllCheckbox = document.getElementById('selectAll');

        // Set today's date in the date picker
        const today = new Date().toISOString().split('T')[0];
        datePicker.value = today;
        datePicker.disabled = true;
        datePicker.style.opacity = "0.6";

        // Toggle dropdown
        selectSelected.addEventListener('click', function (e) {
            e.stopPropagation();
            this.nextElementSibling.classList.toggle('select-show');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function () {
            taskCheckboxes.classList.remove('select-show');
        });

        // Prevent closing when clicking inside the dropdown
        taskCheckboxes.addEventListener('click', function (e) {
            e.stopPropagation();
        });

        // Handle individual task checkboxes
        taskCheckboxes.addEventListener('change', function (e) {
            if (e.target.type === 'checkbox' && e.target.id !== 'selectAll') {
                updateSelectedText();
                updateSelectAllCheckbox();
            }
        });

        function updateSelectedText() {
            const checkedBoxes = taskCheckboxes.querySelectorAll('input[type="checkbox"]:checked:not(#selectAll)');
            //console.log(taskCheckboxes);
            if (checkedBoxes.length === 0) {
                selectSelected.textContent = 'Choose a task...';
            } else if (checkedBoxes.length === 1) {
                selectSelected.textContent = checkedBoxes[0].nextSibling.textContent.trim();
            } else {
                selectSelected.textContent = `${checkedBoxes.length} tasks selected`;
            }
        }

        function populateScrumTask(data) {
            const taskCheckboxesContainer = document.getElementById('taskCheckboxes');
            taskCheckboxesContainer.innerHTML = `
            <input type="text" id="searchit" placeholder="Search for tasks..."
                                            class="task-search" oninput="filterTasks()">
            <label>
                <input type="checkbox" id="selectAll"> Select All
            </label>
        `;

            Object.keys(data).forEach(task => {
                taskCheckboxesContainer.innerHTML += `
                <label for="${data[task].id}">
                    <input type="checkbox" id="${data[task].id}" name="TaskId[]" value="${data[task].id}">
                    ${data[task].name}
                </label>
            `;
            });

            document.getElementById('selectAll').addEventListener('change', function () {
                const checkboxes = taskCheckboxesContainer.querySelectorAll('input[type="checkbox"][name="TaskId[]"]');
                checkboxes.forEach(checkbox => checkbox.checked = this.checked);
                updateSelectedText();
            });
            updateSelectedText();
        }

        // Update Select All checkbox state
        function updateSelectAllCheckbox() {
            const checkboxes = taskCheckboxes.querySelectorAll('input[type="checkbox"]:not(#selectAll)');
            const checkedBoxes = taskCheckboxes.querySelectorAll('input[type="checkbox"]:checked:not(#selectAll)');
            //console.log(checkboxes);
            selectAllCheckbox.checked = checkboxes.length === checkedBoxes.length;
        }

        // Event listener for radio buttons
        challengesRadios.forEach(radio => {
            radio.addEventListener('change', function () {
                radioError.style.display = 'none';
                if (radio.checked && radio.value === 'Y') {
                    formGroupHeader.innerHTML = '<h5 class="headerName"><i class="fas fa-exclamation-triangle"></i> Challenge</h5>';
                    generalTextarea.placeholder = 'Your challenges';
                } else if (radio.checked && radio.value === 'N') {
                    formGroupHeader.innerHTML = '<h5 class="headerName"><i class="fas fa-clipboard"></i> General</h5>';
                    generalTextarea.placeholder = 'General comments';
                }
            });
        });

        form.addEventListener('submit', function (e) {
            let isValid = true;

            // Validate the radio buttons
            const selectedRadio = document.querySelector('input[name="challenges"]:checked');
            if (!selectedRadio) {
                radioError.style.display = 'block';
                isValid = false;
            } else {
                radioError.style.display = 'none';
            }

            // Validate the general textarea
            if (generalTextarea.value.trim() === '') {
                generalError.style.display = 'block';
                generalTextarea.focus();
                isValid = false;
            } else {
                generalError.style.display = 'none';
            }

            // Prevent form submission if any field is invalid
            if (!isValid) {
                e.preventDefault();
                return;
            }
        });

        const tasksPerPage = 10;
        let currentPage = 1;

        function updatePagination(totalTasks) {
            const totalPages = Math.ceil(totalTasks / tasksPerPage);
            const pageInfo = document.getElementById('pageInfo');
            const prevButton = document.getElementById('prevPage');
            const nextButton = document.getElementById('nextPage');

            pageInfo.textContent = `Page ${currentPage} of ${totalPages}`;

            // Enable/disable pagination buttons only when there are more than 10 tasks
            if (totalTasks > 10) {
                prevButton.disabled = currentPage === 1;
                nextButton.disabled = currentPage === totalPages;
                prevButton.style.display = 'inline-block';
                nextButton.style.display = 'inline-block';
                pageInfo.style.display = 'inline-block';
            } else {
                prevButton.style.display = 'none';
                nextButton.style.display = 'none';
                pageInfo.style.display = 'none';
            }

            showTasksForCurrentPage();
        }

        function showTasksForCurrentPage() {
            const tasks = taskCheckboxes.querySelectorAll('label:not(:first-child)');
            const startIndex = (currentPage - 1) * tasksPerPage;
            const endIndex = startIndex + tasksPerPage;

            tasks.forEach((task, index) => {
                if (index >= startIndex && index < endIndex && task.style.display !== 'none') {
                    task.style.display = '';
                } else {
                    task.style.display = 'none';
                }
            });
        }

        document.getElementById('prevPage').addEventListener('click', () => {
            if (currentPage > 1) {
                currentPage--;
                updatePagination(taskCheckboxes.querySelectorAll('label:not(:first-child)').length);
            }
        });

        document.getElementById('nextPage').addEventListener('click', () => {
            const totalTasks = taskCheckboxes.querySelectorAll('label:not(:first-child)').length;
            if (currentPage < Math.ceil(totalTasks / tasksPerPage)) {
                currentPage++;
                updatePagination(totalTasks);
            }
        });

        // Initial pagination setup
        updatePagination(taskCheckboxes.querySelectorAll('label:not(:first-child)').length);

        // Voice recognition function (you need to implement this)
        function voiceRecognition(buttonId) {
            // Implement voice recognition logic here
            //console.log('Voice recognition triggered for button:', buttonId);
        }

        document.getElementById('scrumDiaryButton').addEventListener('click', () => {
            $.ajax({
                url: ASSERT_PATH + 'sprint/fetchScrumTasks',
                type: 'POST',
                data: { "sprintId": sprintId },
                dataType: 'json',
                success: function (response) {
                    //console.log(response.data);
                    populateScrumTask(response.data);
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.error(jqXHR);
                    console.error('Daily scrum not updated:', textStatus, errorThrown);
                }
            });
        })

    });

    $("#scrumDiaryForm").submit(function (event) {
        event.preventDefault();
        let formData = $(this).serialize();
        //console.log(formData);
        $.ajax({
            url: ASSERT_PATH + 'sprint/scrumdiary?sprint_id=' + sprintId,
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function (response) {
                console.log(response.data);
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: "Your Daily scrum has been updated successfully",
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = ASSERT_PATH + "sprint/navsprintview?sprint_id=" + sprintId;
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Error!',
                        text: 'Your Daily scrum has not been updated successfully.',
                        confirmButtonText: 'OK'
                    })
                }
            },
            error: function (jqXHR, textStatus, errorThrown, response) {
                console.error(jqXHR);
                console.error('Daily scrum not updated:', textStatus, errorThrown);
                //console.log(response.data);
                Swal.fire({
                    icon: 'warning',
                    title: 'Error!',
                    text: 'Your Daily scrum has not been updated successfully.',
                    confirmButtonText: 'OK'
                })
            }
        });
    })

    document.querySelectorAll('.card-links a').forEach(item => {
        item.addEventListener('click', (event) => {
            event.preventDefault();
            var href = event.target.getAttribute('href');
            if (href) {
                window.location.href = href;
            }
        });
    });

    if (sprintStatus != 'Sprint Running') {
        console.log(sprintStatus);
        document.getElementById('scrumDiaryButton').removeAttribute('data-bs-target');
        document.getElementById('scrumDiaryButton').style.opacity = '0.2';
        document.getElementById('scrumDiaryButton').style.borderBottom = 'none';
    }
    if (sprintStatus != 'Sprint Review' || sprintReviewStatus) {
        scrumReviewButton.style.opacity = '0.2';
        scrumReviewButton.style.borderBottom = 'none';
        var form = document.getElementById('sprintReviewForm');
        form.addEventListener('submit', function (event) {
            event.preventDefault();
        });
    }
    if (sprintStatus != 'Sprint Retrospective' || sprintRetrospectiveStatus) {
        document.getElementById('scrumRetrospectiveButton').removeAttribute('data-bs-target');
        document.getElementById('scrumRetrospectiveButton').style.opacity = '0.2';
        document.getElementById('scrumRetrospectiveButton').style.borderBottom = 'none';

    }

    async function fetchAllData() {
        let allData = [];
        let currentPage = 1;
        let totalPages = await getTotalPages();

        while (currentPage <= totalPages) {
            const pageData = await fetchPageData(currentPage);
            allData = allData.concat(pageData);
            currentPage++;
        }

        return allData;
    }

    async function generateReports() {
        const data = await fetchAllData();
        // Use the data for generating PDF and Excel
        generatePDF(data);
    }
}