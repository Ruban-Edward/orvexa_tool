// ************************ brainstroming ************************
/**
 * Initializes the time select elements when the DOM is fully loaded.
 */

if (typeof m_userStories !== "undefined") {
  document.addEventListener("DOMContentLoaded", function () {

    const labels = document.querySelectorAll("label");

    labels.forEach((label) => {
      const inputId = label.getAttribute("for");
      const inputElement = document.getElementById(inputId);

      // Check if the input element exists and has the 'required' attribute
      if (inputElement && inputElement.hasAttribute("required")) {
        label.classList.add("label-with-asterisk");
      }
    });

    const startTimeSelect = document.getElementById("startTimeSelect");
    const durationHoursInput = document.getElementById(
      "meeting_duration_hours"
    );
    const durationMinutesInput = document.getElementById(
      "meeting_duration_minutes"
    );
    const endTimeSelect = document.getElementById("endTimeSelect");
    const startDateInput = document.getElementById("meeting_start_date");
    const endDateInput = document.getElementById("meeting_end_date");

    /**
     * Formats time in hh:mm AM/PM format.
     * @param {number} minutes - The total minutes to be converted to time format.
     * @returns {string} - The formatted time string.
     */
    const formatTime = (minutes) => {
      let hours = Math.floor(minutes / 60);
      let mins = minutes % 60;
      let period = hours >= 12 ? "PM" : "AM";
      hours = hours % 12;
      hours = hours ? hours : 12;
      mins = mins < 10 ? "0" + mins : mins;
      return `${hours}:${mins} ${period}`;
    };

    /**
     * Fills select elements with time options in 15-minute intervals.
     * @param {HTMLSelectElement} select - The select element to fill with time options.
     * @param {number} [startMinutes=0] - The starting minutes for time options.
     */
    const fillTimeOptions = (select, startMinutes = 0) => {
      let options = "";
      for (let i = startMinutes; i < 24 * 60; i += 15) {
        const time = formatTime(i);
        options += '<option value="' + i + '">' + time + "</option>";
      }
      select.innerHTML = options;
    };

    /**
     * Sets the start time select element to the current time rounded to the nearest quarter-hour.
     * @param {HTMLSelectElement} selectElement - The select element to set to the current time.
     */
    const setStartTimeToCurrent = (selectElement) => {
      const now = new Date();
      const currentMinutes = now.getHours() * 60 + now.getMinutes();
      const nearestQuarter = Math.ceil(currentMinutes / 15) * 15;
      selectElement.value = nearestQuarter % (24 * 60);
    };

    /**
     * Updates the end time and end date based on the selected start time and duration.
     */
    const updateEndTime = () => {
      const startTimeValue = parseInt(startTimeSelect.value);
      const durationHours = parseInt(durationHoursInput.value);
      const durationMinutes = parseInt(durationMinutesInput.value);
      const durationValue = durationHours * 60 + durationMinutes;

      if (isNaN(durationHours) && isNaN(durationMinutes)) {
        endDateInput.value = startDateInput.value;
      } else if (!isNaN(startTimeValue) && !isNaN(durationValue)) {
        const endTimeValue = (startTimeValue + durationValue) % (24 * 60);
        const endDateOffset = Math.floor(
          (startTimeValue + durationValue) / (24 * 60)
        );
        const startDate = new Date(startDateInput.value);
        const endDate = new Date(startDate);
        endDate.setDate(startDate.getDate() + endDateOffset);

        endTimeSelect.innerHTML = ""; // Clear existing options
        const time = formatTime(endTimeValue);
        let option = document.createElement("option");
        option.value = endTimeValue;
        option.textContent = time;
        endTimeSelect.appendChild(option);
        endTimeSelect.value = endTimeValue; // Set the end time value

        // Update the end date input
        endDateInput.value = endDate.toISOString().split("T")[0];
      } else {
        endTimeSelect.innerHTML =
          '<option value="">Select the End time</option>';
        fillTimeOptions(endTimeSelect, parseInt(startTimeSelect.value) + 15);
      }
    };

    // Event listeners to update end time when start time or duration changes
    startTimeSelect.addEventListener("change", updateEndTime);
    durationHoursInput.addEventListener("change", updateEndTime);
    durationMinutesInput.addEventListener("change", updateEndTime);
    startDateInput.addEventListener("change", updateEndTime);

    // Initialize the time options and set the start time to current time
    fillTimeOptions(startTimeSelect);
    fillTimeOptions(endTimeSelect);
    setStartTimeToCurrent(startTimeSelect);

    /**
     * Pre-fills the form for editing a meeting with the given start time and duration.
     * @param {number} startTime - The start time in minutes.
     * @param {number} duration - The duration in minutes.
     */
    const prefillForm = (startTime, duration) => {
      startTimeSelect.value = startTime;
      durationHoursInput.value = Math.floor(duration / 60);
      durationMinutesInput.value = duration % 60;
      updateEndTime();
    };
  });
  document.addEventListener("DOMContentLoaded", function () {
    if (totalCount == 0) {
      document.getElementById("empty").style.display = "block";
    } else {
      document.getElementById("empty").style.display = "none";
    }

    function updateUserStories() {
      var selectedEpics = $(".epic-checkbox:checked");
      var userstoryDropdown = $("#userstoryDropdown");
      var defaultMessage = $("#defaultMessage");
      var allUserStories = $("#allUserStories");

      userstoryDropdown.empty(); // Clear the dropdown

      if (selectedEpics.length > 0) {
        defaultMessage.addClass("d-none"); // Hide the default message
        allUserStories.removeClass("d-none"); // Show the "Select all" option

        selectedEpics.each(function () {
          var epicId = $(this).val();
          $.ajax({
            url: assert_path + "backlog/userstoryByEpic/" + epicId,
            method: "POST",
            success: function (response) {
              if (response.length > 0) {
                response.forEach(function (userstory) {
                  userstoryDropdown.append(
                    `<label for="${epicId}" class="dropdown-item px-3"><input type="checkbox" class="userstory-checkbox" value="${
                      userstory.user_story_id
                    }" data-description="${
                      userstory.story_name
                    }" data-epic-id="${epicId}"> US_ ${
                      userstory.user_story_id + " : " + userstory.story_name
                    }</label>`
                  );
                });
              } else {
                userstoryDropdown.append(
                  `<label class="dropdown-item px-3 text-danger">No user stories available for this epic.</label>`
                );
              }
            },
            error: function () {
              userstoryDropdown.append(
                `<label class="dropdown-item px-3 text-danger">Failed to load user stories.</label>`
              );
            },
          });
        });
      } else {
        defaultMessage.removeClass("d-none"); // Show the default message
        allUserStories.addClass("d-none"); // Hide the "Select all" option
      }
    }

    function handleSelectAllUserStories() {
      $(document).on("change", "#selectAllUserStories", function () {
        var isChecked = $(this).is(":checked");
        $(".userstory-checkbox").prop("checked", isChecked).trigger("change");
      });
    }

    function handleCheckboxChange(checkboxClass, inputId) {
      $(document).on("change", checkboxClass, function () {
        var inputField = $(inputId);
        var hiddenInputField = $(inputId + "Hidden");

        var descriptions = [];
        var values = [];
        var selectedCount = 0; // Count selected checkboxes

        $(checkboxClass + ":checked").each(function () {
          descriptions.push($(this).data("description"));
          values.push($(this).val());
          selectedCount++; // Increment count
        });

        if (checkboxClass === ".userstory-checkbox") {
          inputField.val(`${selectedCount} : User stories selected`); // Show count in the input field
        } else {
          inputField.val(descriptions.join(", "));
        }

        hiddenInputField.val(values.join(","));

        if (this.checked) {
          $(this).closest("label").addClass("highlight");
        } else {
          $(this).closest("label").removeClass("highlight");
        }

        // Check or uncheck the "Select All" checkbox based on the selection of individual checkboxes
        const allChecked =
          $(checkboxClass).length === $(checkboxClass + ":checked").length;
        if (checkboxClass === ".userstory-checkbox") {
          $("#selectAllUserStories").prop("checked", allChecked);
        }

        if (checkboxClass === ".epic-checkbox") {
          updateUserStories();
        }
      });

      $(document).on("click", ".dropdown-item", function (e) {
        if (!$(e.target).is("input")) {
          $(this).find("input").trigger("click");
        }
      });
      $(document).on("click", "#allUserStories", function () {
        handleSelectAllUserStories();
      });
    }

    handleSelectAllUserStories();
    handleCheckboxChange(".epic-checkbox", "#epicdropdown");
    handleCheckboxChange(".userstory-checkbox", "#userstory");
    handleCheckboxChange(".team-checkbox", "#addgroup");
  });

  // Email selection and filtering functionality
  var selectedEmailsInput = document.getElementById("selectedEmailsInput");

  /**
   * Filters the list of emails based on user input.
   * @param {Event} event - The input event triggered by the user.
   */
  function filterEmails(event) {
    var input, filter, div, labels, i, txtValue;
    input = document.getElementById("teamInput");
    filter = input.value.trim().toUpperCase();
    div = document.getElementById("emailDropdown");
    labels = div.getElementsByClassName("email-option");
    var hasMatch = false;
    var matchCount = 0;
    for (i = 0; i < labels.length; i++) {
      txtValue = labels[i].textContent.trim().toUpperCase();
      if (txtValue.startsWith(filter) && matchCount < 4) {
        labels[i].style.display = "";
        hasMatch = true;
        matchCount++;
      } else {
        labels[i].style.display = "none";
      }
    }
    if (hasMatch && filter.length > 0) {
      div.classList.add("show");
    } else {
      div.classList.remove("show");
    }
    // Handle arrow key (Tab or Right Arrow) selection
    if (event.key === "ArrowRight" || event.key === "Tab") {
      autofillInput(filter);
    }
    // Check if Enter key is pressed
    if (event.key === "Enter") {
      addFirstMatchingEmail(filter);
      event.preventDefault(); // Prevent form submission on Enter
    }
  }

  // Attach click event listener to the dropdown labels
  document
    .getElementById("emailDropdown")
    .addEventListener("click", function (e) {
      if (e.target && e.target.classList.contains("email-option")) {
        var email = e.target.textContent.trim();
        updateSelectedEmails(email);
        document.getElementById("teamInput").value = ""; // Clear the input after adding
        this.classList.remove("show"); // Hide dropdown after selection
      }
    });

  /**
   * Autofills the input with the first matching email when the arrow key is pressed.
   * @param {string} filter - The filter string to match the email.
   */
  function autofillInput(filter) {
    var div = document.getElementById("emailDropdown");
    var labels = div.getElementsByClassName("email-option");

    // Find the first matching label
    for (var i = 0; i < labels.length; i++) {
      var txtValue = labels[i].textContent.trim().toUpperCase();
      if (txtValue.startsWith(filter)) {
        var email = labels[i].textContent.trim();
        document.getElementById("teamInput").value = email; // Autofill input with the first matching email
        return;
      }
    }
  }

  /**
   * Adds the first matching email to the selected emails list when Enter key is pressed.
   * @param {string} filter - The filter string to match the email.
   */
  function addFirstMatchingEmail(filter) {
    var div = document.getElementById("emailDropdown");
    var labels = div.getElementsByClassName("email-option");

    // Find the first matching label
    for (var i = 0; i < labels.length; i++) {
      var txtValue = labels[i].textContent.trim().toUpperCase();
      if (txtValue.startsWith(filter)) {
        var email = labels[i].textContent.trim();
        updateSelectedEmails(email);
        document.getElementById("teamInput").value = ""; // Clear the input after adding
        div.classList.remove("show"); // Hide dropdown after selection
        return;
      }
    }
  }

  /**
   * Updates the list of selected emails.
   * @param {string} email - The email to be added to the selected list.
   */
  function updateSelectedEmails(email) {
    // Check if email is already selected
    var selectedEmails = selectedEmailsInput.value.split(",");
    if (selectedEmails.includes(email)) {
      return; // Do nothing if email is already selected
    }

    // Add the selected email
    selectedEmails.push(email);
    selectedEmailsInput.value = selectedEmails.join(",");

    // Update UI to show selected email
    var selectedEmailsContainer = document.getElementById("selectedEmails");
    var label = document.createElement("div");
    label.classList.add("selected-email");
    label.innerHTML = `
        <span>${email}</span>
        <span class="remove-email" onclick="removeSelectedEmail(this, '${email}')">&times;</span>`;
    selectedEmailsContainer.appendChild(label);
  }

  /**
   * Removes the selected email from the list and updates the UI.
   * @param {HTMLElement} element - The HTML element that triggered the removal.
   * @param {string} email - The email to be removed from the selected list.
   */
  function removeSelectedEmail(element, email) {
    var selectedEmails = selectedEmailsInput.value.split(",");
    var emailIndex = selectedEmails.indexOf(email);
    if (emailIndex > -1) {
      selectedEmails.splice(emailIndex, 1);
      selectedEmailsInput.value = selectedEmails.join(",");
      element.parentElement.remove();
    }
  }

  // Event listener for the input field
  document.getElementById("teamInput").addEventListener("input", filterEmails);
  document
    .getElementById("teamInput")
    .addEventListener("keydown", filterEmails);

  document.addEventListener("DOMContentLoaded", () => {
    const searchBox = document.querySelector(".search-box");
    const searchBtn = document.getElementById("search_btn");
    const searchInput = document.getElementById("backlogSearchInput");

    searchBtn.addEventListener("click", function (e) {
      e.preventDefault();
      searchBox.classList.toggle("expanded");
      if (searchBox.classList.contains("expanded")) {
        searchInput.focus();
      }
    });

    // Close search box when clicking outside
    document.addEventListener("click", function (e) {
      if (!searchBox.contains(e.target)) {
        searchBox.classList.remove("expanded");
      }
    });

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
    document.addEventListener("click", function (e) {
      if (!searchBox.contains(e.target)) {
        if (searchInput.value) {
          searchBox.classList.add("expanded");
        } else {
          searchBox.classList.remove("expanded");
        }
      }
      //   console.log("expand", searchBox.contains(e.target));
    });
  });

  document.addEventListener("DOMContentLoaded", (event) => {
    var addUserStoriesBtn = document.getElementById("addUserStoriesBtn");

    //For handling the no epic found situation
    addUserStoriesBtn.addEventListener("click", function (event) {
      var hasEpics =
        addUserStoriesBtn.getAttribute("data-has-epics") === "true";
      if (!hasEpics) {
        event.preventDefault(); // Prevent the default button action
        Swal.fire({
          icon: "warning",
          title: "No Epics Available",
          text: "Please add an epic before adding user stories.",
          confirmButtonText: "Add Epic",
        }).then((result) => {
          if (result.isConfirmed) {
            // Open the Add Epic modal
            var addEpicModal = new bootstrap.Modal(
              document.getElementById("epicAddModal")
            );
            addEpicModal.show();
          }
        });
      } else {
        // If epics are available, open the Add User Stories modal
        var addUserStoriesModal = new bootstrap.Modal(
          document.getElementById("epicModal")
        );
        addUserStoriesModal.show();
      }
    });
  });

  document
    .getElementById("fileUploadForm")
    .addEventListener("submit", function (event) {
      event.preventDefault();

      var form = event.target;
      var formData = new FormData(form);

      fetch(form.action, {
        method: "POST",
        body: formData,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            Swal.fire({
              icon: "success",
              title: "Success",
              text: data.message,
            }).then((result) => {
              if (result.isConfirmed) {
                location.reload();
              }
            });
          } else if (data.warning) {
            Swal.fire({
              icon: "warning",
              title: "Warning",
              text: "The file is already exist",
            }).then((result) => {
              if (result.isConfirmed) {
                // location.reload();
              }
            });
          } else {
            Swal.fire({
              icon: "error",
              title: "Error",
              text: data.message,
            }).then((result) => {
              if (result.isConfirmed) {
                // location.reload();
              }
            });
          }
        })
        .catch((error) => {
          Swal.fire({
            icon: "error",
            title: "Error",
            text: "An error occurred while uploading the file.",
          });
        });
    });

  //For Pagination and dynamically creating the user story card
  document.addEventListener("DOMContentLoaded", function (event) {
    const rowsPerPage = 10;
    let currentPage = 1;
    // let filteredSrowsPerPage
    //For accessing the status class in the css file
    function getStatusClass(status) {
      switch (status) {
        case "new":
          return "new-status";
        case "new requirement":
          return "new-requirement";
        case "ready for brainstorming":
          return "ready-for-brainstorming";
        case "brainstorming completed":
          return "brainstorming-completed";
        case "ready for sprint":
          return "ready-for-sprint";
        case "in sprint":
          return "in-sprint";
        case "completed":
          return "completed";
        case "in brainstorming":
          return "in-brainstorming";
        case "partially brainstormed":
          return "partially-brainstormed";
        case "brainstorming":
          return "brainstorming";
        default:
          return "";
      }
    }

    //Creation of the user story card dynamically based on the input
    function createCard(story, index, pokerDetails) {
      // var selectClass = (story.status_name.replace(/ /g, '-')).toLowerCase();
      console.log(pokerDetails);
      console.log(userPermissions);
      if (pokerDetails.length > 0) {
        var disabled = pokerDetails[0]["reveal"] == "Y" ? "disabled" : "";
        var status = pokerDetails[0]["reveal"] == "Y" ? true : false;
        // Extract card points as numbers
        const cardPoints = pokerDetails.map((story) =>
          parseInt(story.card_points)
        );

        // Get the minimum and maximum card points
        var minCardPoints = Math.min(...cardPoints);
        var maxCardPoints = Math.max(...cardPoints);
      }

      return `
            <div class="card cls-user-story-card">
                <div class="card-header cls-userstory-card-head d-flex justify-content-between align-items-center" data-bs-toggle="collapse" data-bs-target="#story-${index}">
                    <div class="col-sm-2 align-items-center us-id">
                        <p>USID :<strong> US_${story.user_story_id}</strong></p>
                    </div>
                    <div class="col-sm-5 d-flex flex-direction-row cls-story-name">
                        <div class="name">${
                          story.as_a_an
                        }<strong> -> </strong>${story.i_want}<strong> -> </strong>${story.so_that}</div>
                    </div>
                    <div class="col-sm-2 cls-status">
                        
                        <select class="status-select" onchange="updateUserStoryStatus(this, ${
                          story.user_story_id
                        })">
                            ${statuses
                              .map(
                                (s) => `
                                <option value="${s.status_id}" ${
                                  story.r_module_status_id == s.status_id
                                    ? "selected"
                                    : ""
                                }>
                                    ${s.status_name}
                                </option>
                            `
                              )
                              .join("")}
                        </select>
                    </div>
                    <div class="cls-action-buttons header-action-buttons">
                        
                            ${
                              userPermissions.updateUserStory
                                ? `
                            <div class='col-sm-3 d-flex justify-content-center'>
                                <button type="button" id="update${story.user_story_id}" class="btn btn-link btn-sm" onclick="openEditModal(${story.user_story_id})" data-toggle="tooltip" data-placement="top" title="Update"><i class='bi bi-pencil-square'></i></button>
                            </div>`
                                : ``
                            }
                            ${
                              userPermissions.deleteUserStory
                                ? `
                            <div class='col-sm-3'>
                                <button type='button' class='btn btn-link delete-btn text-center' onclick="deleteUserStory(${story.user_story_id},${pId},${pblId}, '${story.status_name}', event)" data-toggle="tooltip" data-placement="top" title="Delete">
                                    <i class='bi bi-trash'></i>
                                </button>
                            </div>`
                                : ``
                            }
                            ${
                              userPermissions.viewTask
                                ? `
                            <a href="${link}${story.user_story_id}"><button class="button-secondary view-tasks-btn" data-id="${index}">Tasks(${story.count_task})</button></a>`
                                : ``
                            }
                        
                    </div>
                </div>
                <div id="story-${index}" class="collapse">
                    <div class="card-body cls-user-card-details">
                        <div class="cls-card-lft ">
                            <div class="row row-content">
                                <div class="col-sm-3 d-flex align-items-center h6"><p>Epic:</p></div>
                                <div class="col-sm-9 d-flex align-items-center fs-6"><p class="cls-user-stories-p">${
                                  story.epic_description
                                }</p></div>
                            </div>
                            <hr class="my-1">
                            <div class="row row-content">
                                <div class="col-sm-3 d-flex align-items-center h6"><p>User story</p></div>
                                <div class="col-sm-9 fs-6"><p class="cls-user-stories-p"><strong> As a </strong>${
                                  story.as_a_an
                                }<strong> I want </strong>${story.i_want}<strong> So that </strong>${story.so_that}</p></div>
                            </div>
                            <hr class="my-1">
                            ${
                              story.user_story_points != null
                                ? `
                                <div class="row row-content">
                                    <div class="col-sm-3 d-flex align-items-center h6"><p>Story Point</p></div>
                                    <div class="col-sm-9 fs-6"><p class="cls-user-stories-p"><strong>${story.user_story_points}</strong></p></div>
                                </div>
                                <hr class="my-1">`
                                : ""
                            }                            
                            <div class="row row-content">
                                <div class="col-sm-3 d-flex align-items-center h6"><p class="cls-user-stories-p">Acceptance criteria:</p></div>
                            </div>
                            <div class="row row-content">
                                <div class="col-sm-3 d-flex align-items-center h6"><p class="cls-user-stories-p">Given:</p></div>
                                <div class="col-sm-9 fs-6"><p class="cls-user-stories-p">${
                                  story.given
                                }</p></div>
                            </div>
                            <div class="row row-content">
                                <div class="col-sm-3 d-flex align-items-center h6"><p class="cls-user-stories-p">When: </p></div>
                                <div class="col-sm-9 fs-6"><p class="cls-user-stories-p">${
                                  story.us_when
                                }</p></div>
                            </div>
                            <div class="row row-content">
                                <div class="col-sm-3 d-flex align-items-center h6"><p class="cls-user-stories-p">Then:</p></div>
                                <div class="col-sm-9 fs-6"><p class="cls-user-stories-p">${
                                  story.us_then
                                }</p></div>
                            </div>
                            <hr class="my-1">
                            <div class="row row-content">
                                <div class="col-sm-3 d-flex align-items-center h6"><p class="cls-user-stories-p">Status:</p></div>
                                <div class="col-sm-9 fs-6"><p class="cls-user-stories-p">${
                                  story.status_name
                                }</p></div>
                            </div>
                            <hr class="my-1">
                            <div class="row row-content">
                                <div class="col-sm-3 d-flex align-items-center h6"><p class="cls-user-stories-p">Conditions</p></div>
                                <div class="col-sm-9 fs-6"><p class="cls-user-stories-p">${
                                  story.condition_text
                                }</p></div>
                            </div>
                            <hr class="my-1">
                            <div class="row row-content">
                                <div class="col-sm-3 d-flex align-items-center h6"><p class="cls-user-stories-p">No of tasks:</p></div>
                                <div class="col-sm-9 fs-6"><p class="cls-user-stories-p">${
                                  story.count_task
                                }</p></div>
                            </div>

                            <div class="comment-section">
                                    <div class="toggle-suggestion-button" >
                                    <h4> Suggestions </h4> <span class="dropdown-icon text-end">&#x25BC;</span>
                                    </div>
                                    <div id="comments-container-${
                                      story.user_story_id
                                    }" class="suggestion-container " style="display:none">
                                    <!-- Comment Form -->
                                    <div class="comment-form ">
                                    <textarea id="comment-text-${
                                      story.user_story_id
                                    }" class="suggestion-box" placeholder="Add a suggestion..."></textarea>
                                    <div class="d-flex justify-content-end">
                                    <button class="btn primary_button mb-1" id="submit-comment-${
                                      story.user_story_id
                                    }">Add</button>
                                    </div>
                                    </div>

                                    <!-- Comment Section -->
                                    <div id="comment-section-"></div>
                                    <div id="comment-section-${
                                      story.user_story_id
                                    }"
                                    class="suggestion-section">
                                    <!-- Predefined Comment -->

                                    </div>


                                    </div>
                            </div>
                        
                            <div class="poker-planning-section">
                            <div class="toggle-poker-button" >
                                <h4> Poker Planning </h4> 
                                ${
                                  pokerDetails.length > 0
                                    ? `
                                    <h6>Lowest point: ${minCardPoints}</h6>
                                    <h6>Highest point: ${maxCardPoints}</h6> 
                                    <h6>Total voting: ${pokerDetails.length}</h6> `
                                    : ""
                                }                                                              
                                <span class="dropdown-icon text-end">&#x25BC;</span>
                            </div>
                            <div class="poker-continer" id="poker-planning-continer-${
                              story.user_story_id
                            }" style="display:none;">
                            ${
                              userPermissions.revealPoker
                                ? `<button type="submit" class="btn primary_button" name="revealsubmit" onclick="changeReveal(${story.user_story_id}, this)" style="margin-bottom: 13px;float: right;margin-right: 20px;" ${disabled}>Reveal points</button>`
                                : ``
                            }
                            ${
                              pokerDetails.length === 0 &&
                              !userPermissions.addUserStoryPoint
                                ? `<form class="row g-3 needs-validation" id="fibonacciUserStoryform" action="${assert_path}backlog/insertPokerPlanning" method="POST" enctype="multipart/form-data" novalidate onsubmit="event.preventDefault(); handleFormSubmission({formId: 'fibonacciUserStoryform',modalId: 'fibonacciModal'});">
                                    <input type="hidden" value="${
                                      story.user_story_id
                                    }" id="fibonacciUserStoryId-${
                                    story.user_story_id
                                  }" name="fibonacciUserStoryId">
                                    <div class="col-md-6">
                                        <label for="fibonacciNumber" class="form-label label-with-asterisk">Story points</label>
                                        <select class="form-select" id="fibonacciNumber" name="fibonacciNumber" aria-label="Default select example" required>
                                        <option value="" disabled selected>Select story point</option>
                                            ${fibonacciList
                                              .map(
                                                (points) => `
                                                <option value="${points}">
                                                    ${points}
                                                </option>
                                            `
                                              )
                                              .join("")}
                                        </select>
                                        <div class="valid-feedback">Looks good!</div>
                                        <div class="invalid-feedback">Please choose your points.</div>
                                    </div>
                                    <div>
                                        <label for="poker" class="form-label form-need">Poker description</label>
                                        <textarea class="form-control" name="poker_description" id="poker" placeholder="Your poker description"></textarea>
                                    </div>
                                    <div class="modal-footer d-flex justify-content-center">
                                        <button type="submit" class="btn primary_button" name="pokersubmit" id="submitBtn">Add poker</button>
                                    </div>
                                </form>`
                                : `<table id="priority-table" class="table table-borderless custom-table">
                                    <thead id="tableHeader" class="header_color">
                                        <tr>
                                            <th class="text-center sorting">Name</th>
                                            <th class="text-center sorting">Poker point</th>
                                            <th class="text-center sorting">Reason</th>
                                            <th class="text-center sorting">Date</th>
                                            ${
                                              userPermissions.addUserStoryPoint &&
                                              status &&
                                              story.user_story_points == null
                                                ? `<th class="text-center sorting">Action</th>`
                                                : ""
                                            }
                                        </tr>
                                    </thead>
                                    <tbody id="tableBody">
                                        ${
                                          pokerDetails.length > 0
                                            ? `${pokerDetails
                                                .map(
                                                  (detail) => `
                                                <tr>
                                                    <td class="capitalize-text text-center">${
                                                      detail.name
                                                    }</td>
                                                    <td class="capitalize-text text-center">${
                                                      detail.card_points
                                                    }</td>
                                                    <td class="capitalize-text text-center">${
                                                      detail.reason != ""
                                                        ? detail.reason
                                                        : "No reason found"
                                                    }</td>
                                                    <td class="capitalize-text text-center">${
                                                      formatDate(
                                                        detail.added_date
                                                      ) +
                                                      " " +
                                                      timeFormat(
                                                        detail.added_date.split(
                                                          " "
                                                        )[1]
                                                      )
                                                    }</td>
                                                    ${
                                                      userPermissions.addUserStoryPoint &&
                                                      status &&
                                                      story.user_story_points ==
                                                        null
                                                        ? `<td class="capitalize-text text-center" data-toggle="tooltip" data-placement="top" data-bs-original-title="Add story point">
                                                        <input type="radio" ${
                                                          story.user_story_points ==
                                                          detail.card_points
                                                            ? "checked"
                                                            : ""
                                                        } 
                                                        class="${
                                                          story.user_story_points ==
                                                          detail.card_points
                                                            ? "checked"
                                                            : ""
                                                        } form-check-input meet-select-radio pointer pokerValue${
                                                            story.user_story_id
                                                          }" onclick="addStoryPoint(${
                                                            detail.card_points
                                                          }, ${
                                                            story.user_story_id
                                                          }, this)" value="${
                                                            detail.card_points
                                                          }"></input>
                                                    </td>`
                                                        : ""
                                                    }
                                                </tr>
                                            `
                                                )
                                                .join("")}`
                                            : `<tr><td colspan="${
                                                userPermissions.addUserStoryPoint &&
                                                status &&
                                                story.user_story_points == null
                                                  ? 5
                                                  : 4
                                              }" class="capitalize-text text-center">No data found</td></tr>`
                                        }
                                        `
                            }
                                    </tbody>
                               </table>
                            <div class="mt-4"> </div>
                            </div>
                            </div>
                            
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    // function applyStatusClass() {
    //     // Iterate over all select elements or apply individually
    //     document.querySelectorAll('.status-select').forEach(selectElement => {
    //         const statusName = selectElement.querySelector('option:checked').textContent;
    //         const statusClass = getStatusClass(statusName);
    //         selectElement.classList.add(statusClass);
    //     });
    // }

    function displayStories(filteredStories) {
      console.log('hello');
      console.log(filteredStories);
      var pokerDetails;
      $.ajax({
        url: assert_path + "backlog/getpoker",
        type: "POST",
        headers: { "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')?.content || "" },
        data: {
          product_id: pId,
          userStory: filteredStories.map((item) => item.user_story_id),
        },
        dataType: "json",
        success: function (response) {
          pokerDetails = response.data; // Handle the data received
          console.log(pokerDetails);
          // Get the container element and set its inner HTML
          const userStoryContainer =
            document.getElementById("userStoryContainer");
          console.log(filteredStories);
          console.log("filteredstories");

          userStoryContainer.innerHTML = filteredStories
            .map((story, index) => {
              return createCard(story, index, pokerDetails[index]);
            })
            .join("");
          document.querySelectorAll(".card-header").forEach((header, index) => {
            if (index === 0) {
              header.click();
            }
            header.addEventListener("click", function (event) {
              // Check if the click is within a button
              if (event.target.closest("button")) {
                return;
              }
              const target = document.querySelector(
                header.getAttribute("data-bs-target")
              );
              const isCollapsed = target.classList.contains("show");
              document
                .querySelectorAll(".collapse.show")
                .forEach((collapse) => {
                  collapse.classList.remove("show");
                });
              if (!isCollapsed) {
                target.classList.add("show");
              }
            });
          });
        },
        error: function () {},
      });
    }

    // Function to update pagination controls
    function updatePaginationControls() {
      const totalPages = Math.ceil(totalCount / rowsPerPage);
      document.getElementById(
        "pageInfo"
      ).textContent = `Page ${currentPage} of ${totalPages}`;
      document.getElementById(
        "pageInfo"
      ).textContent = `Page ${currentPage} of ${totalPages}`;
      document.getElementById("prevPage").style.visibility =
        currentPage > 1 ? "visible" : "hidden";
      document.getElementById("nextPage").style.visibility =
        currentPage < totalPages ? "visible" : "hidden";
    }

    // Event listeners for pagination controls
    document.getElementById("prevPage").addEventListener("click", () => {
      if (currentPage > 1) {
        currentPage--;
        applyFilters();
      }
    });

    document.getElementById("nextPage").addEventListener("click", () => {
      const totalPages = Math.ceil(totalCount / rowsPerPage);
      if (currentPage < totalPages) {
        currentPage++;
        applyFilters();
      }
    });

    //For applying the filter
    //For applying the filter
    applyFilters();
    function applyFilters() {
      const filterSidebar = document.getElementById("filterSidebar");
      filterSidebar.classList.remove("open");

      const searchText = document
        .getElementById("backlogSearchInput")
        .value.toLowerCase();
      const statusFilter = $("#statusFilter").val();
      const epicFilter = $("#epicFilter").val();
      console.log("status :", statusFilter);
      let filter = {
        id: pblId,
        status: statusFilter,
        epic: epicFilter,
        search: searchText,
        limit: rowsPerPage,
        offset: (currentPage - 1) * rowsPerPage,
      };
      console.log(filter);
      $.ajax({
        url: assert_path + "backlog/filterUserStories",
        type: "POST",
        headers: { "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')?.content || "" },
        data: JSON.stringify({
          filter: filter,
        }),
        contentType: "application/json",
        dataType: "json",
        success: function (response) {
          console.log("Server Response:", response);
          if (response && response.data) {
            filteredStories = response.data;
            displayStories(filteredStories); // Redisplay the stories based on the new data
            countNumberOfFilters();
            updatePaginationControls();
            if (filteredStories.length == 0) {
              document.getElementById("userStoryContainer").style.display =
                "none";
              document.getElementById("empty").style.display = "block";
              document.getElementById("pageInfo").style.display = "none";
            } else {
              document.getElementById("userStoryContainer").style.display =
                "block";
              document.getElementById("empty").style.display = "none";
            }
          } else {
            filteredStories = []; // Clear the filtered stories if no data is returned
            displayStories(currentPage);
            updatePaginationControls();
          }
        },
        error: function () {},
      });
    }

    function countNumberOfFilters() {
      let numberOfFilters = document.getElementById("noti");
      numberOfFilters.style.display = "block";
      let formData = $("#filterOptionsForm").serializeArray();

      let filledFieldsCount = formData.filter(function (field) {
        return field.value.trim() !== "";
      }).length;
      if (filledFieldsCount > 0) {
        numberOfFilters.textContent = formData;
      } else {
        numberOfFilters.style.display = "none";
      }
    }

    $("#backlogSearchInput").on("input", applyFilters);
    $("#filterOptionsForm").on("submit", function (event) {
      event.preventDefault();
      applyFilters();
    });
    $("#resetFiltersBtn").on("click", resetFilters);
    function resetFilters() {
      console.log("rest code");
      document.getElementById("statusFilter").value = "";
      document.getElementById("epicFilter").value = "";
      applyFilters();
    }

    displayStories(currentPage);
    updatePagination();

    function countNumberOfFilters() {
      let numberOfFilters = document.getElementById("noti");
      numberOfFilters.style.display = "block";
      let formData = $("#filterOptionsForm").serializeArray();

      let filledFieldsCount = formData.filter(function (field) {
        return field.value.trim() !== "";
      }).length;
      if (filledFieldsCount > 0) {
        numberOfFilters.textContent = filledFieldsCount;
      } else {
        numberOfFilters.style.display = "none";
      }
    }
  });

  function clearModalContent() {
    // Reset form fields
    document.getElementById("addUserStoryform").reset();

    // Clear specific form fields
    document.getElementById("userId").value = "";
    document.getElementById("as").value = "";
    document.getElementById("Iwant").value = "";
    document.getElementById("given").value = "";
    document.getElementById("when").value = "";
    document.getElementById("sothat").value = "";
    document.getElementById("epics").value = "";
  }

  function openAddModal() {
    let modal = new bootstrap.Modal(document.getElementById("epicModal"));

    // Clear the form
    clearModalContent();

    $("#addUserStoryform")[0].reset();
    $("#addStoryTitle").text("Add user story");
    $("#submitBtn1").text("Add");
    document.getElementById("addUserStoryform").action =
      baseUrl + "backlog/addUserStory?pid=" + pId + "&pblid" + pblId;
    document.getElementById("submitBtn1").name = "addUserStory";
    $("#userstories_status").val("13");
    updateSelectOption();
  }

  function openFibonacciModal(userStoryId) {
    let modal = new bootstrap.Modal(document.getElementById("fibonacciModal"));
    modal.show();
    $("#fibonacciUserStoryform")[0].reset();
    $("#addStoryTitle").text("Add fibonacci planning");
    let fibonacciUserStoryId = document.getElementById("fibonacciUserStoryId");
    fibonacciUserStoryId.value = userStoryId;
  }

  function openEditModal(userStoryId) {
    let modal = new bootstrap.Modal(document.getElementById("epicModal"));
    modal.show();

    $("#addUserStoryform")[0].reset();
    $("#addStoryTitle").text("Update user story");

    document.getElementById("addUserStoryform").action =
      baseUrl + "backlog/updateUserStory?pid=" + pId + "&pblid" + pblId;
    document.getElementById("submitBtn1").name = "updateUserStory";
    $("#submitBtn1").text("Update");

    $.ajax({
      url: baseUrl + "backlog/userstory/details/" + userStoryId,
      method: "GET",
      success: function (response) {
        if (response) {
          document.getElementById("userId").value = response[0].user_story_id;
          document.getElementById("epics").value = response[0].epic_id;
          document.getElementById("userStories_status").value =
            response[0].status_id;
          document.getElementById("as").value = response[0].as_a_an;
          document.getElementById("Iwant").value = response[0].i_want;
          document.getElementById("given").value = response[0].given;
          document.getElementById("when").value = response[0].us_when;
          document.getElementById("sothat").value = response[0].so_that;
          tinymce.get("default").setContent(response[0].us_then);
          tinymce.get("default2").setContent(response[0].condition_text);
        }
        updateSelectOption();
      },
      error: function () {
        Swal.fire({
          title: "Error",
          text: "An error occurred while UserStory Fetching data",
          icon: "error",
          confirmButtonText: "OK",
        });
      },
    });
  }

  function updateSelectOption() {
    var statusSelect = document.getElementById("userStories_status");
    var selectedValue = parseInt(statusSelect.value);
    Array.from(statusSelect.options).forEach((option) => {
      var optionValue = parseInt(option.value);
      if (selectedValue > 16 && optionValue <= 16) {
        option.disabled = true;
        option.classList.add("disabled");
      } else {
        option.disabled = false;
        option.classList.remove("disabled");
      }
    });
  }

  // Event listener for modal hidden event
  $("#epicModal").on("hidden.bs.modal", function () {
    clearModalContent();

    $(".modal-backdrop").remove();
    $("body").removeClass("modal-open");
    $("body").css("padding-right", "");
  });

  function deleteUserStory(userId, pId, pblId, statusName, event) {
    event.stopPropagation(); // Prevent the collapse from toggling

    if (statusName === "In Sprint") {
      Swal.fire({
        icon: "warning",
        title: "Warning",
        text: "This user story is in a sprint and cannot be deleted.",
        showConfirmButton: true,
        confirmButtonText: "OK",
      });
    } else {
      Swal.fire({
        icon: "question",
        title: "Are you sure?",
        text: "Deleted User story can't be recovered",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes",
        cancelButtonText: "No",
      }).then((result) => {
        if (result.isConfirmed) {
          // Only proceed with deletion if "Yes" is clicked
          $.ajax({
            url:
              assert_path +
              `backlog/deleteuserstory/` +
              pId +
              `/` +
              pblId +
              `/` +
              userId,
            method: "POST",
            success: function (response) {
              if (response.success) {
                Swal.fire({
                  icon: "success",
                  title: "User story",
                  text: response.message,
                  confirmButtonText: "OK",
                }).then((result) => {
                  if (result.isConfirmed) {
                    window.location.href =
                      assert_path +
                      `backlog/userstories?pid=` +
                      pId +
                      `&pblid=` +
                      pblId;
                  }
                });
              } else {
                Swal.fire({
                  icon: "warning",
                  title: "User story",
                  text: response.message,
                  confirmButtonText: "OK",
                }).then((result) => {
                  if (result.isConfirmed) {
                    window.location.href =
                      assert_path +
                      `backlog/userstories?pid=` +
                      pId +
                      `&pblid=` +
                      pblId;
                  }
                });
              }
            },
            error: function () {
              // Display SweetAlert on error
              Swal.fire({
                title: "Error",
                text: "An error occurred while processing the request.",
                icon: "warning",
                confirmButtonText: "OK",
              });
            },
          });
        }
      });
    }
  }

  (() => {
    "use strict";
    const forms = document.querySelectorAll(".needs-validation");

    // Loop over them and prevent submission
    Array.from(forms).forEach((form) => {
      form.addEventListener(
        "submit",
        (event) => {
          if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
          }

          form.classList.add("was-validated");
        },
        false
      );
    });
  })();

  // function handleFormSubmission({
  //     formId,
  //     modalId,
  //     successTitle
  // }) {
  //     // Get form and form data

  //     const form = document.getElementById(formId);
  //     const formData = new FormData(form);
  //     if (form.checkValidity() === false) {
  //         form.classList.add('was-validated');
  //         return; // Prevent submission if validation fails
  //     }
  //     // Fetch action URL from form attribute
  //     const url = form.action;

  //     // Perform AJAX request
  //     $.ajax({
  //         url: url,
  //         method: 'POST',
  //         data: formData,
  //         contentType: false,
  //         processData: false,
  //         dataType: 'json',
  //         success: function(response) {
  //             console.log("Server Response:", response);

  //             if (response.success) {
  //                 // Close the modal
  //                 $(`#${modalId}`).modal('hide');
  //                 $('.modal-backdrop').remove();

  //                 // Display SweetAlert on success
  //                 Swal.fire({
  //                     title: 'Success',
  //                     text: response.message,
  //                     icon: 'success',
  //                     confirmButtonText: 'OK',
  //                 }).then((result) => {
  //                     if (result.isConfirmed) {
  //                         location.reload();
  //                     }
  //                 });
  //             } else if (response.message) {
  //                 // Show error message from response
  //                 var errorMessage = response.message;
  //                 // if(result.error){
  //                 //     for (var field in response.error) {
  //                 //         errorMessage += response.error[field] + '<br>';
  //                 //     }
  //                 // }else{
  //                 //     errorMessage = response.message;
  //                 // }
  //                 Swal.fire({
  //                     title: 'Error!',
  //                     html: errorMessage,
  //                     icon: 'warning',
  //                     confirmButtonText: 'OK'
  //                 });
  //             }
  //         },
  //         error: function() {
  //             // Display SweetAlert on error
  //             Swal.fire({
  //                 title: 'Error',
  //                 text: 'An error occurred while processing the request.',
  //                 icon: 'error',
  //                 confirmButtonText: 'OK',
  //             });
  //         },
  //     });
  // }

  function handleFormSubmission({ formId, modalId, successTitle }) {
    
    const form = document.getElementById(formId);
    const formData = new FormData(form);
    if (form.checkValidity() === false) {
      form.classList.add("was-validated");
      return;
    }
    const url = form.action;

    $.ajax({
      url: url,
      method: "POST",
      data: formData,
      contentType: false,
      processData: false,
      dataType: "json",
      success: function (response) {
        console.log("Server Response:", response);

        if (response.success) {
          $(`#${modalId}`).modal("hide");
          $(".modal-backdrop").remove();

          Swal.fire({
            title: "Success",
            text: response.message,
            icon: "success",
            confirmButtonText: "OK",
          }).then((result) => {
            if (result.isConfirmed) {
              location.reload();
            }
          });
        } else if (response.error) {
          let errorMessage = "";
          if (typeof response.error === "object" && response.error !== null) {
            // Handle multiple validation errors
            for (let field in response.error) {
              errorMessage += response.error[field] + "<br>";
            }
          } else {
            // Handle single error message
            errorMessage = response.error;
          }
          Swal.fire({
            title: "Validation Error",
            html: errorMessage,
            icon: "warning",
            confirmButtonText: "OK",
          });
        }
      },
      error: function (xhr, status, error) {
        console.error("AJAX Error:", status, error);
        Swal.fire({
          title: "Error",
          text: "An error occurred while processing the request.",
          icon: "error",
          confirmButtonText: "OK",
        });
      },
    });
  }
  document.getElementById("submitBtn1").addEventListener("click", function () {
    const form = document.getElementById('addUserStoryform');
    if (form.checkValidity() === false) {
    console.log('double clicked');
      form.classList.add("was-validated");
      // return;
      this.click();
    }
    
  });

  //For scheduling the Brainstorming meeting
  $(document).ready(function () {
    $("#brainstormMeetForm").on("submit", function (e) {
      e.preventDefault();
      let formData = $(this).serialize();

      formData += "&Schedulebutton=1";
      let url = assert_path + "meeting/scheduleMeeting";
      $.ajax({
        url: url,
        method: "POST",
        data: formData,
        dataType: "json",
        success: function (response) {
          if (response.errors) {
            var errorMessage = "";
            for (var field in response.errors) {
              errorMessage += response.errors[field] + "<br>";
            }
            Swal.fire({
              title: "Validation Error!",
              html: errorMessage,
              icon: "error",
              confirmButtonText: "OK",
            });
          } else if (response.conflict) {
            showToast(response.conflictMembers);
          } else if (response.success) {
            if (response.mail) {
              Swal.fire({
                title: "Success!",
                text: "Meeting scheduled Successfully",
                icon: "success",
                confirmButtonText: "OK",
              }).then((result) => {
                if (result.isConfirmed) {
                  window.location.href = window.location.href;
                }
              });
            } else {
              // Handle error if email sending fails
              Swal.fire({
                title: "Warning",
                text: "Meeting scheduled but Email notification failed",
                icon: "warning",
                confirmButtonText: "OK",
              });
            }
          }
        },
      });
    });
  });
  

  function showAddTeamMember() {
    document.getElementById("addTeamMemberSection").style.display = "block";
    document.getElementById("inviteGroupSection").style.display = "none";
  }

  function showInviteGroup() {
    document.getElementById("addTeamMemberSection").style.display = "none";
    document.getElementById("inviteGroupSection").style.display = "block";
  }

  function updateUserStoryStatus(select, storyId) {
    const newStatusId = select.value;
    const newStatusName = select.options[select.selectedIndex].text;
    console.log(newStatusId + " " + storyId);
    Swal.fire({
      icon: "question",
      title: "Are you sure?",
      text: "Do you want to change the status?",
      showCancelButton: true,
      confirmButtonColor: "#3085d6",
      cancelButtonColor: "#d33",
      confirmButtonText: "Yes",
      cancelButtonText: "No",
    }).then((result) => {
      if (result.isConfirmed) {
        $.ajax({
          url: assert_path + "backlog/updateUserStory",
          method: "POST",
          data: {
            pbl_id: pblId,
            story_id: storyId,
            status_id: newStatusId,
          },
          dataType: "json",
          success: function (response) {
            if (response.success) {
              // Show success message with SweetAlert2
              Swal.fire({
                icon: "success",
                title: "Status Updated",
                text: `Status for user story ${storyId} updated to ${newStatusName}`,
                confirmButtonText: "Ok",
              }).then((result) => {
                if (result.isConfirmed) {
                  location.reload();
                }
              });
            } else {
              // Show error message with SweetAlert2
              Swal.fire({
                icon: "error",
                title: "Update Failed",
                text: "Failed to update user story status. Please try again.",
              });
              console.error("Failed to update user story status:", response);
            }
          },
          error: function (xhr, status, error) {
            // Show error message with SweetAlert2
            Swal.fire({
              icon: "error",
              title: "Error",
              text: "An error occurred while updating user story status. Please try again.",
            });
            console.error("Error occurred while updating user story status.");
            console.error("Status:", status);
            console.error("Error:", error);
            console.error("Response Text:", xhr.responseText);
          },
        });
      } else {
        location.reload();
      }
    });
  }

  function toggleCollapse(collapseId) {
    var collapseElement = document.getElementById(collapseId);
    var icon =
      collapseElement.previousElementSibling.querySelector(".collapsible-icon");

    if (collapseElement.classList.contains("show")) {
      $(collapseElement).collapse("hide");
      icon.classList.remove("fa-chevron-up");
      icon.classList.add("fa-chevron-down");
    } else {
      $(collapseElement).collapse("show");
      icon.classList.remove("fa-chevron-down");
      icon.classList.add("fa-chevron-up");
    }
  }

  document.addEventListener("DOMContentLoaded", function () {
    commentSection = document.getElementById(`comment-section`);
    function suggestion(id) {
      const section = document.getElementById(`comment-section-${id}`);
      return section;
    }

    // const submitCommentBtn = document.getElementById("submit-comment");
    // const commentText = document.getElementById("comment-text");
    let commentMap = new Map();
    let commentId = 1;
    let id = comments.length;
    let cid = 0;
    if (id == 0) {
      cid = 1;
    } else {
      cid = parseInt(comments[id - 1]["comments_id"], 10);
      cid += 1;
    }
    console.log("commentid", cid);
    let commentIdCounter = cid;
    let newEntries = [];
    let data = [];

    function generateId() {
      return `${commentIdCounter++}`;
    }

    function generateCommentId(flag) {
      if (flag == 1) {
        commentId++;
      } else {
        return `${commentId}`;
      }
    }

    function getCurrentDateTime() {
      const now = new Date();

      const year = now.getFullYear();
      const month = String(now.getMonth() + 1).padStart(2, "0"); // Months are zero-based
      const day = String(now.getDate()).padStart(2, "0");

      const hours = String(now.getHours()).padStart(2, "0");
      const minutes = String(now.getMinutes()).padStart(2, "0");
      const seconds = String(now.getSeconds()).padStart(2, "0");

      return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
    }

    function createCommentElement(comment) {
      generateCommentId(1);

      const commentDiv = document.createElement("div");
      commentDiv.classList.add("comment");
      commentDiv.dataset.id = comment.id;

      const formattedDate = new Date(comment.timestamp).toLocaleString();
      commentDiv.innerHTML = `
              <div class="comment-content">
                  <p class="comment-content"><strong>${comment.recipient}:</strong> ${comment.text}</p>
                  <span class="comment-timestamp">${comment.date}</span>
              </div>
              <i class="icon-twitch me-1"></i> <button class="btn-link reply-btn reply-button" id="reply-button-${comment.id}">Reply</button>
              <div class="replies" ></div> <!-- Container for nested replies -->
          `;
      console.log("comment.id");
      console.log(comment.id);
      return commentDiv;
    }

    // Create a new reply element
    function createReplyElement(reply) {
      const replyDiv = document.createElement("div");
      replyDiv.classList.add("reply");
      replyDiv.style.display = "block";
      replyDiv.dataset.id = reply.id;
      replyDiv.innerHTML = `
              <p><strong>${reply.recipient}:</strong> ${reply.text}</p>`;
      return replyDiv;
    }

    // Print the first comment with its unique ID
    function printFirstComment(comments, id) {
      comments.forEach(function (data) {
        if (data.parent_id == 0 && id == data.r_user_story_id) {
          if (current_user == data.r_user_id) {
            data.r_user_id = "You";
          }

          const comment = {
            id: data.comments_id,
            text: data.text_data,
            recipient: data.r_user_id,
            replyTo: null,
            date: data.created_date,
          };
          // Create the comment element and append it to the comment section
          const commentElement = createCommentElement(comment);

          commentSection.appendChild(commentElement);
        } else if (data.parent_id > 0 && id == data.r_user_story_id) {
          if (current_user == data.r_user_id) {
            data.r_user_id = "You";
          }
          const newReply = {
            id: data.comments_id,
            text: data.text_data,
            recipient: data.r_user_id,
          };
          const newReplyElement = createReplyElement(newReply);
          //   const repliesContainer = parentElement.querySelector(".replies");
          //     repliesContainer.appendChild(newReplyElement);
          commentSection.appendChild(newReplyElement);
          const replyButton = document.querySelector(
            `#reply-button-${data.parent_id}`
          );
          if (replyButton) {
            replyButton.style.display = "none";
          }
        }
      });

      // Event delegation for reply buttons
      commentSection.addEventListener("click", function (event) {
        if (event.target.classList.contains("reply-btn")) {
          const replyButton = event.target;
          const parentElement = replyButton.closest(".comment, .reply");

          // Remove existing reply form if it exists
          const existingReplyForm = parentElement.querySelector(".reply-form");
          if (existingReplyForm) {
            existingReplyForm.remove();
            return;
          }

          // Create and insert the reply form
          const replyInput = document.createElement("div");
          replyInput.classList.add("reply-form");
          replyInput.style.display = "block";
          replyInput.innerHTML = `
                <div class="textarea">
                <textarea class="reply-box" id="reply-box" rows="2" placeholder="Add a reply..."></textarea>
                <button type="button" id="mic" class="mic-button"
                                    onclick="voiceRecognition('mic','reply-box')">
                                    <i class="bi bi-mic"></i>
                                </button>
                </div>
                <div class="d-flex justify-content-end">
                  <button class="btn btn-primary mt-2 submit_reply">Add Reply</button>
                </div>
            `;

          // Insert the reply form immediately after the clicked reply button
          replyButton.insertAdjacentElement("afterend", replyInput);

          replyInput
            .querySelector(".submit_reply")
            .addEventListener("click", function () {
              const replyText = replyInput
                .querySelector("textarea")
                .value.trim();
              const recipient = parentElement
                .querySelector("p")
                .textContent.split(":")[0]
                .trim();
              if (replyText) {
                const parentId = parentElement.dataset.id;

                const newReply = {
                  id: generateId(),
                  text: replyText,
                  recipient: "you",
                  replyTo: parentElement
                    .querySelector("p")
                    .textContent.split(":")[0]
                    .trim(),
                  parentId: parentId,
                  comment: generateCommentId(2),
                };
                const newReplyElement = createReplyElement(newReply);

                // Find the container for replies and append the new reply
                const repliesContainer =
                  parentElement.querySelector(".replies");
                repliesContainer.appendChild(newReplyElement);
                replyInput.remove();
                replyButton.style.display = "none";

                commentMap.set(newReply.id, {
                  element: newReplyElement,
                  data: newReply,
                });

                newEntries.push(newReply); // Add to newEntries
                printNewEntries(id); // Print newly added reply
              }
            });
        }
      });

      const elementId = id ? `submit-comment-${id}` : 3;
      const commentId = id ? `comment-text-${id}` : 3;
      // Get the element
      var submitCommentBtn = document.getElementById(elementId);
      const commentText = document.getElementById(commentId);
      // Handle new comment submission
      submitCommentBtn.addEventListener("click", function () {
        const text = commentText.value.trim();
        if (text) {
          const newComment = {
            id: generateId(),
            text: text,
            recipient: "You",
            replyTo: null,
            date: getCurrentDateTime(),
          };
          const newCommentElement = createCommentElement(newComment);
          commentSection.appendChild(newCommentElement);
          commentText.value = "";
          commentMap.set(newComment.id, {
            element: newCommentElement,
            data: newComment,
          });
          newEntries.push(newComment); // Add to newEntries
          printNewEntries(id);
        }
      });
    }

    // Print only the newly added comments or replies
    function printNewEntries(userStoryId) {
      newEntries.forEach((entry) => {
        const parentId = entry.parentId || 0;
        const replyTo = entry.replyTo || "null";
        const cid = entry.comment || generateCommentId(2);

        let comm = {
          c_id: entry.id,
          text: entry.text,
          r_user_id: entry.recipient,
          parent_id: parentId,
          r_user_story_id: userStoryId,
        };
        $.ajax({
          url: assert_path + "backlog/comments",
          type: "POST",
          data: JSON.stringify({
            data: comm,
          }),
          contentType: "application/json",
          dataType: "json",
          success: function (response) {
            console.log("Server Response:", response);
            data = response.data[response.data.length - 1];
          },
          error: function (xhr, status, error) {
            console.error("AJAX Error:", status, error);
            console.error("Response Text:", xhr.responseText);
          },
        });
      });
      newEntries = []; // Clear the newEntries array after printing
    }

    // comment coallapse

    document.addEventListener("click", function (event) {
      if (event.target.matches(".toggle-suggestion-button")) {
        // Retrieve the user story ID
        const card = event.target.closest(".card");
        const storyId = card
          .querySelector(".us-id strong")
          .textContent.replace("US_", "")
          .trim();

        const container = document.getElementById(
          `comments-container-${storyId}`
        );

        if (container) {
          container.style.display =
            container.style.display === "none" || container.style.display === ""
              ? "block"
              : "none";
          const isVisible = container.style.display === "block";
          if (isVisible) {
            event.target.classList.add("open");

            commentSection = suggestion(storyId);

            printFirstComment(comments, storyId);
          } else {
            event.target.classList.remove("open");
          }
        } else {
          console.error("No container found for storyId:", storyId);
        }
      }
    });
  });

  // Toggle comments visibility
  $(document).on("click", ".toggle-suggestion-button", function () {
    $("#comments-container").toggle();
    const isVisible = $("#comments-container").is(":visible");
    $(this).toggleClass("open", isVisible);
  });

  //Drop toggle for poker planning
  document.addEventListener("DOMContentLoaded", function () {
    document.addEventListener("click", function (event) {
      if (event.target.matches(".toggle-poker-button")) {
        const card = event.target.closest(".card");
        const storyId = card
          .querySelector(".us-id strong")
          .textContent.replace("US_", "")
          .trim();
        const pokerContainer = document.getElementById(
          `poker-planning-continer-${storyId}`
        );

        if (pokerContainer) {
          pokerContainer.style.display =
            pokerContainer.style.display === "none" ||
            pokerContainer.style.display === ""
              ? "block"
              : "none";
          const isVisible = pokerContainer.style.display === "block";
          if (isVisible) {
            event.target.classList.add("open");
          } else {
            event.target.classList.remove("open");
          }
        } else {
          console.error("No pokerContainer found for storyId:", storyId);
        }
      }
    });
  });

  function changeReveal(userStoryId, button) {
    Swal.fire({
      icon: "question",
      title: "Are you sure?",
      text: "Do you want to reveal the story points",
      showCancelButton: true,
      confirmButtonColor: "#3085d6",
      cancelButtonColor: "#d33",
      confirmButtonText: "Yes",
      cancelButtonText: "No",
    }).then((result) => {
      if (result.isConfirmed) {
        // Only proceed with deletion if "Yes" is clicked
        $.ajax({
          url: assert_path + "backlog/updatereveal",
          method: "POST",
          data: { userStory: userStoryId },
          dataType: "json",
          success: function (response) {
            if (response.success) {
              console.log(response);
              // Show success message with SweetAlert2
              Swal.fire({
                icon: "success",
                title: "Status Updated",
                text: "Poker points revealed",
                showConfirmButton: true,
              }).then((result) => {
                if (result.isConfirmed) {
                  button.disabled = true;
                  location.reload();
                }
              });
            } else {
              // Show error message with SweetAlert2
              Swal.fire({
                icon: "error",
                title: "Update Failed",
                text: "Poker points not revealed",
              });
              console.error("Failed to update user story status:", response);
            }
          },
          error: function (xhr, status, error) {
            // Show error message with SweetAlert2
            Swal.fire({
              icon: "error",
              title: "Error",
              text: "An error occurred while updating user story status. Please try again.",
            });
            console.error("Error occurred while updating user story status.");
            console.error("Status:", status);
            console.error("Error:", error);
            console.error("Response Text:", xhr.responseText);
          },
        });
      }
    });
  }

  function addStoryPoint(storyPoint, userStoryId, button) {
    // Deselect all radio buttons in the group
    const radios = document.querySelectorAll(".pokerValue" + userStoryId);
    const checkedRadios = document.querySelectorAll(".checked");
    radios.forEach((radio) => {
      radio.checked = false;
    });
    // Select the clicked radio button
    button.checked = true;

    Swal.fire({
      icon: "question",
      title: "Are you sure?",
      text: "Do you want to set the story points",
      showCancelButton: true,
      confirmButtonColor: "#3085d6",
      cancelButtonColor: "#d33",
      confirmButtonText: "Yes",
      cancelButtonText: "No",
    }).then((result) => {
      if (result.isConfirmed) {
        // Only proceed with deletion if "Yes" is clicked
        $.ajax({
          url: assert_path + "backlog/addUserStoryPoint",
          method: "POST",
          data: { userStoryId: userStoryId, storyPoint: storyPoint },
          dataType: "json",
          success: function (response) {
            if (response.success) {
              console.log(response);
              // Show success message with SweetAlert2
              Swal.fire({
                icon: "success",
                title: "Status Updated",
                text: "Story points added",
                showConfirmButton: true,
              }).then((result) => {
                if (result.isConfirmed) {
                  location.reload();
                }
              });
            } else {
              // Show error message with SweetAlert2
              Swal.fire({
                icon: "error",
                title: "Update Failed",
                text: "Story points not added",
              });
              console.error("Failed to update user story status:", response);
            }
          },
          error: function (xhr, status, error) {
            // Show error message with SweetAlert2
            Swal.fire({
              icon: "error",
              title: "Error",
              text: "An error occurred while updating user story points. Please try again.",
            });
            console.error("Error occurred while updating user story status.");
            console.error("Status:", status);
            console.error("Error:", error);
            console.error("Response Text:", xhr.responseText);
          },
        });
      } else {
        radios.forEach((radio) => {
          radio.checked = false;
        });
        checkedRadios.forEach((checkedradio) => {
          checkedradio.checked = true;
        });
      }
    });
  }
  function getTodayDate() {
    const today = new Date();
    const yyyy = today.getFullYear();
    const mm = String(today.getMonth() + 1).padStart(2, "0"); // Months start at 0
    const dd = String(today.getDate()).padStart(2, "0");
    return `${yyyy}-${mm}-${dd}`;
  }
  document.addEventListener("DOMContentLoaded", function () {
    //const meetingDateInput = document.getElementById("meeting_start_date");
    const todayDate = getTodayDate();
    flatpickr("#meeting_start_date,#meeting_end_date", {
      minDate: todayDate,
      dateFormat: "Y-m-d",
    });
  });
}
