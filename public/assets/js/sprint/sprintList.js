if (typeof m_sprintList !== "undefined") {
  let sortColumn = "order_by";
  let sortOrder = 1; // 1 for ascending, -1 for descending
  let currentPage = 1; // Current page number
  let totalRows = totalPages_sprint;
  const rowsPerPage = 10; // Number of rows per page
  const maxCheckedColumns = 7;
  let ajaxFunction = "fetchTableData";
  let formData = null;
  var checkboxarr = [];

  // Maximum number of columns that can be checked at a time
  function initialPage(current_Page) {
    currentPage = current_Page;
    if (currentPage === 1) {
      document.getElementById("prevPage").style.visibility = "hidden";
    } else {
      document.getElementById("prevPage").style.visibility = "visible";
    }
    if (Math.ceil(totalRows / rowsPerPage) == currentPage) {
      document.getElementById("nextPage").style.visibility = "hidden";
    } else {
      document.getElementById("nextPage").style.visibility = "visible";
      console.log(totalRows);
      console.log(currentPage);
    }
  }

  function invalidReponse() {
    document.getElementById("nextPage").style.visibility = "hidden";
    document.getElementById("prevPage").style.visibility = "hidden";
  }

  function fetchTableData(page) {
    $.ajax({
      url: refinement_url + "sprint/sprintlist", // Adjust URL to match your setup
      method: "GET",
      data: {
        page: page,
        limit: rowsPerPage,
      },
      dataType: "json",
      success: function (response) {
        data = response.data;
        if (response && response.data && response.data.length > 0) {
          console.log(response);
          document.getElementById("noData").style.display = "none";
          document.getElementById("priority-table").style.display = "table";
          document.getElementById("pageInfo").style.display = "block";
          console.log(response);
          totalRows = response.totalrows;
          console.log(totalRows);
          generateTableHeader(response.data);
          generateTableBody(response.data);
          generateColumnCheckboxes(response.data);
          updatePaginationControls(response.current_page);
          initialPage(response.current_page);
          data = response.data;
          // for appling tooltip
          $(function () {
            $('[data-toggle="tooltip"]').tooltip();
          });
        } else {
          console.error("Invalid response format");
          invalidReponse();
          const tableBody = document.getElementById("tableBody");
          tableBody.innerHTML = "";
          document.getElementById("priority-table").style.display = "none";
          document.getElementById("noData").style.display = "block";
          document.getElementById("pageInfo").style.display = "none";
        }
      },
      error: function (jqXHR, textStatus, errorThrown) {
        console.error("Error fetching data:", textStatus, errorThrown);
      },
    });
  }
  generateTableHeader(data);
  generateTableBody(data);
  generateColumnCheckboxes(data);
  updatePaginationControls(1);
  initialPage(1);

  $("#backlogSearchInput").on("input", function () {
    filterTable(1); // Ensure $currentPage is defined and valid
    ajaxFunction = "filterTable";
  });

  document.addEventListener("DOMContentLoaded", function () {
    const filterBtn = document.getElementById("filter_btn");
    const sidebar = document.getElementById("filterSidebar");
    const closeBtn = document.getElementById("closeBtn");
    const applyFIlterBtn = document.getElementById("applyfilter");

    filterBtn.addEventListener("click", function () {
      sidebar.classList.toggle("open");
    });
    closeBtn.addEventListener("click", function () {
      sidebar.classList.remove("open");
    });
    applyFIlterBtn.addEventListener("click", () => {
      sidebar.classList.remove("open");
    });
  });

  $("#filterForm").submit(function (event) {
    event.preventDefault();
    let serializedData = $(this).serialize();
    let formDataObject = {};

    serializedData.split("&").forEach(function (pair) {
      let [key, value] = pair.split("=");
      key = decodeURIComponent(key);
      value = decodeURIComponent(value || "");

      // Handle array notation (e.g., key[])
      if (key.endsWith("[]")) {
        key = key.slice(0, -2); // Remove '[]'
        if (!Array.isArray(formDataObject[key])) {
          formDataObject[key] = [];
        }
        formDataObject[key].push(value);
      } else {
        formDataObject[key] = value;
      }
    });
    ajaxFunction = "applyFilter";
    applyFilter(formDataObject, 1);
    formData = formDataObject;
  });

  var columnBtn = document.getElementById("column_btn");
  var columnDropdown = document.getElementById("columnDropdown");

  // Toggle dropdown on button click
  columnBtn.addEventListener("click", function () {
    columnDropdown.classList.toggle("show");
  });

  // Close dropdown when clicking outside
  document.addEventListener("click", function (event) {
    if (
      !columnBtn.contains(event.target) &&
      !columnDropdown.contains(event.target)
    ) {
      columnDropdown.classList.remove("show");
    }
  });

  var filterBtn = document.getElementById("filter_btn");
  var filterDropdown = document.getElementById("filterSidebar");

  // Toggle dropdown on button click
  filterBtn.addEventListener("click", function () {
    filterDropdown.classList.toggle("show");
  });

  // Close dropdown when clicking outside
  // document.addEventListener("click", function (event) {
  //   if (
  //     !filterBtn.contains(event.target) &&
  //     !filterDropdown.contains(event.target)
  //   ) {
  //     filterDropdown.classList.remove("open");
  //   }
  // });
  //genertate table header
  function generateTableHeader(data) {
    const tableHeader = document.getElementById("tableHeader");
    tableHeader.innerHTML = "";
    const headerRow = document.createElement("tr");

    let firstItem = null;
    for (let i = 0; i < data.length; i++) {
      if (data[i] && typeof data[i] === "object" && !Array.isArray(data[i])) {
        firstItem = data[i];
        break;
      }
    }

    if (firstItem) {
      const columns = Object.keys(firstItem);

      columns.forEach((key, index) => {
        const th = document.createElement("th");
        var dbKey = key;
        key = key.charAt(0).toUpperCase() + key.slice(1);
        th.textContent = key.replace(/_/g, " ");
        th.classList.add("text-center");
        th.classList.add("sorting");
        th.classList.add(`column-${index}`);
        if (index >= 7) th.style.display = "none"; // Initially hide columns after the first 7
        th.addEventListener("click", () => sortTable(dbKey));
        headerRow.appendChild(th);
      });

      const actionTh = document.createElement("th");
      actionTh.textContent = "Action";
      actionTh.setAttribute("class", "text-center");
      actionTh.classList.add("sorting");
      headerRow.appendChild(actionTh);
    }

    tableHeader.appendChild(headerRow);
  }
  //generate table body
  function generateTableBody(data) {
    const tableBody = document.getElementById("tableBody");
    tableBody.innerHTML = "";

    // Sort data
    data.sort((a, b) => {
      if (a[sortColumn] < b[sortColumn]) return -sortOrder;
      console.log(a[sortColumn] < b[sortColumn]);
      if (a[sortColumn] > b[sortColumn]) return sortOrder;
      return 0;
    });

    // Paginate data
    // const startIndex = (currentPage - 1) * rowsPerPage;
    // const endIndex = startIndex + rowsPerPage;
    // const paginatedData = data.slice(startIndex, endIndex);

    data.forEach((item) => {
      const row = document.createElement("tr");

      // Dynamically create table cells for each key in the item
      Object.keys(item).forEach((key, index) => {
        const cell = document.createElement("td");
        cell.classList.add("text-center");
        cell.classList.add(`column-${index}`);
        if (index >= 7) cell.style.display = "none"; // Initially hide cells after the first 7 columns

        if (key === "end_date" || key === "start_date") {
          const date = new Date(item[key]);
          const day = String(date.getDate()).padStart(2, "0");
          const monthNames = [
            "Jan",
            "Feb",
            "Mar",
            "Apr",
            "May",
            "Jun",
            "Jul",
            "Aug",
            "Sep",
            "Oct",
            "Nov",
            "Dec",
          ];
          const month = monthNames[date.getMonth()];
          const year = date.getFullYear();
          const formattedDate = `${month} ${day}, ${year}`;
          cell.textContent = formattedDate;
        } else if (key === "sprint_status") {
          cell.textContent = item[key];
          switch (item[key]) {
            case "Sprint Planned":
              cell.classList.add("text-warning");
              break;
            case "Sprint Running":
              cell.classList.add("text-primary");
              break;
            case "Sprint Review":
              cell.classList.add("text-info");
              break;
            case "Sprint Retrospective":
              cell.classList.add("text-secondary");
              break;
            case "Sprint Completed":
              cell.classList.add("text-success");
              break;
            default:
              cell.classList.add("text-muted");
          }
        } else {
          cell.textContent = item[key];
        }
        row.appendChild(cell);
      });

      // Add the action buttons
      const actionCell = document.createElement("td");
      actionCell.classList.add("action_button");
      actionCell.setAttribute("data-label", "Action");
      if (permit) {
        actionCell.innerHTML = `
      <div class='btn-group custom-action-buttons' role='group' aria-label='Button group with nested dropdown'>
      
      <a href='navsprintview?sprint_id=${
        item["sprint_id"]
      }'data-toggle="tooltip" data-placement="top"  title="Click to view sprint"><i class="far fa-eye"></i></a>
        <form  action="edit" method="post">
            <input type="hidden" name="sprint_id" value="${item["sprint_id"]}">
            <button class='removeBtnstyle' type="submit" data-bs-toggle="modal" data-toggle="tooltip" data-placement="top"  title="Click to edit sprint"><i class="far fa-edit"></i></button>

        </form>
                    <button class='dropdown-item ' style=" padding-right : 10px; " type='button' aria-label='Schedule a meeting' data-bs-toggle='modal' data-bs-target='#meetingModal' onclick='showMeetingModal(${JSON.stringify(
                      item
                    )})'><i class="fas fa-user-friends" data-toggle="tooltip" data-placement="top"  title="Click to schedule meeting"></i></button>
      `;
      } else {
        actionCell.innerHTML = `
        <div class='btn-group' role='group' aria-label='Button group with nested dropdown'><a href='navsprintview?sprint_id=${item["sprint_id"]}' data-toggle="tooltip" data-placement="top"  title="Click to view sprint"><i class="far fa-eye"></i></a>`;
      }

      actionCell.innerHTML += ` </div>`;
      row.appendChild(actionCell);
      tableBody.appendChild(row);
    });
  }
  /**
   * Initializes the time select elements when the DOM is fully loaded.
   */
  document.addEventListener("DOMContentLoaded", function () {
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
      return `${hours}:${mins}${period}`;
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

      if (!isNaN(startTimeValue) && !isNaN(durationValue)) {
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
  /**
   * Displays the meeting modal with details.
   * Fetches sprint details and populates modal fields and dropdowns.
   * @param {Object} data - The data object containing sprint information.
   * @author Rama Selvan
   */
  function showMeetingModal(data) {
    console.log(data);
    var sprintId = data.sprint_id;
    $.ajax({
      url: refinement_url + "/meeting/getSprintDetails/" + sprintId,
      type: "POST",
      dataType: "json",
      success: function (response) {
        console.log(response);

        //email
        populateTeamMembers(
          response.team_members,
          response.team_members_email,
          response.team_members_id
        );

        // Populate team members
        var teamMembers = response.team_members;
        // Populate meeting type dropdown
        var meetingTypes = response.meettype;
        var meetingTypesId = response.meettypeId;
        var meetingTypeDropdown = $("#meeting_type");
        meetingTypeDropdown.empty(); // Clear any existing options

        // Add default disabled option
        meetingTypeDropdown.append(
          '<option value="" disabled selected>Select the Meeting type</option>'
        );

        // Append new options
        $.each(meetingTypes, function (index, type) {
          meetingTypeDropdown.append(
            '<option value="' +
              meetingTypesId[index] +
              '" data-type="' +
              type +
              '">' +
              type +
              "</option>"
          );
        });

        // Populate meeting date input with sprint start date
        // Check if the selected meeting type is "Daily Scrum"
        flatpickr(
          "#meeting_start_date,#meeting_end_date,#sprint_start_date,#sprint_end_date",
          {
            minDate: "today",
            dateFormat: "Y-m-d",
          }
        );

        meetingTypeDropdown.on("change", function () {
          var selectedOption = $(this).find("option:selected");
          var selectedType = selectedOption.data("type");
          if (selectedType === "Daily Scrum") {
            $(
              "#recurrence_meeting_container,#sprint_start_date_container,#sprint_end_date_container"
            ).show();
            $(
              "#meeting_start_date_container,#meeting_end_date_container"
            ).hide();
          } else {
            $(
              "#recurrence_meeting_container,#sprint_start_date_container,#sprint_end_date_container"
            ).hide();
            $(
              "#meeting_start_date_container,#meeting_end_date_container"
            ).show();
            $("#recurrence_meeting").val(""); // Reset the dropdown value
          }
        });
      },
      error: function (xhr, status, error) {
        console.log("Error: " + error);
      },
    });
    // Populate the modal with data
    document.getElementById("sprintName").value = data.sprint_name || "N/A";
    document.getElementById("productName").value = data.product || "N/A";

    var sprint_start_date = convertDateToYYYYMMDD(data.start_date);
    var sprint_end_date = convertDateToYYYYMMDD(data.end_date);
    document.getElementById("sprint_start_date").value =
      sprint_start_date || "N/A";
    document.getElementById("sprint_end_date").value = sprint_end_date || "N/A";

    document.getElementById("product").value = data.product_id || "N/A";
    document.getElementById("sprintID").value = data.sprint_id || "N/A";
  }

  function convertDateToYYYYMMDD(dateString) {
    const dateObj = new Date(dateString);

    const year = dateObj.getFullYear();
    const month = String(dateObj.getMonth() + 1).padStart(2, "0"); // Months are zero-based
    const day = String(dateObj.getDate()).padStart(2, "0");

    return `${year}-${month}-${day}`;
  }

  /**
   * Populates the team members dropdown.
   * Adds checkboxes for each team member with their details.
   * @param {Array<string>} teamMembers - List of team member names.
   * @param {Array<string>} teamMembersEmail - List of team member emails.
   * @param {Array<number>} teamMembersId - List of team member IDs.
   * @author Rama Selvan
   */
  function populateTeamMembers(teamMembers, teamMembersEmail, teamMembersId) {
    var dropdown = $("#teamMembersDropdown");
    dropdown.empty();
    for (var i = 0; i < teamMembers.length; i++) {
      var memberName = teamMembers[i];
      var memberId = teamMembersId[i];
      var memberEmail = teamMembersEmail[i];
      dropdown.append(`
          <div class=".form-check-list">
              <input class="form-check-inputs team-member-checkbox" type="checkbox" 
                     value="${memberName}" data-id="${memberId}" data-email="${memberEmail}" 
                     id="member${i}" checked>
              <label class="form-check-label" for="member${i}">${memberName}</label>
          </div>
      `);
    }
    updateSelectedTeamMembers();
  }

  // Add event listeners to checkboxes
  $(document).on("change", ".team-member-checkbox", function () {
    updateSelectedTeamMembers();
  });

  /**
   * Updates the selected team members input field.
   * Collects selected members from checkboxes and updates hidden input.
   * @author Rama Selvan
   */
  function updateSelectedTeamMembers() {
    var selectedMembers = [];
    $(".team-member-checkbox:checked").each(function () {
      selectedMembers.push($(this).val());
    });
    $("#selectedTeamMembersInput").val(selectedMembers.join(","));

    // Update the button text to show the number of selected members
    var numSelected = selectedMembers.length;
    $("#teamMembersDropdownButton").text(numSelected + " member(s) selected");
  }

  /**
   * Removes a selected team member from the list.
   * @param {string} member - The name of the member to remove.
   * @author Rama Selvan
   */
  function removeSelectedTeamMember(member) {
    $(`#selectedTeamMembers .selected-member:contains('${member}')`).remove();
    $(`#teamMembersDropdown input[value="${member}"]`).prop("checked", false);
    updateHiddenInput();
  }

  /**
   * Updates the hidden input with the list of selected team members.
   * @author Rama Selvan
   */
  function updateHiddenInput() {
    var selectedMembers = $(".selected-member")
      .map(function () {
        return {
          name: $(this).text().trim(),
          // id: $(this).data("id"),
          // email: $(this).data("email"),
        };
      })
      .get();
    $("#selectedTeamMembersInput").val(JSON.stringify(selectedMembers));
  }

  /**
   * Handles time duration and formatting for meeting times.
   * Updates end time based on start time and duration, and vice versa.
   * @author Rama Selvan
   */
  document.addEventListener("DOMContentLoaded", function () {
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

  /**
   * Updates the selected recurrence value.
   * Syncs the recurrence dropdown with a hidden input field.
   * @author Rama Selvan
   */
  $("#recurrence_meeting").on("change", function () {
    var selectedRecurrence = $(this).val();
    $("#selected_recurrence").val(selectedRecurrence);
  });

  $(document).ready(function () {
    var initialFormState = $("#meetingForm").html(); // Store the initial state of the form
    function resetForm(formId) {
      var form = $("#" + formId)[0];
      form.reset();
      form.classList.remove("was-validated");
      $(form).find(".invalid-feedback").hide();
      $(form).find(".is-invalid").removeClass("is-invalid");

      if (formId === "meetingForm") {
        $("#meetingForm").html(initialFormState);
        $("#meeting_type").val("");
      }
    }

    $("#meetingModal").on("hidden.bs.modal", function (e) {
      var modalTitle = document.getElementById("modalTitle").innerText;
      if (modalTitle === "Schedule Meeting") {
        resetForm("meetingForm");
        formBehavior();
      }
    });

    function formBehavior() {
      $("#meeting_type").on("change", function () {
        var selectedType = $(this).val();
        updateFormFields(selectedType);
      });
    }

    function updateFormFields(meetingType) {
      if (meetingType === "3") {
        $(
          "#recurrence_meeting_container, #sprint_start_date_container, #sprint_end_date_container"
        ).show();
        $("#meeting_start_date_container, #meeting_end_date_container").hide();
      } else if (meetingType === "4" || meetingType === "5") {
        $("#meeting_start_date_container, #meeting_end_date_container").show();
        $(
          "#recurrence_meeting_container, #sprint_start_date_container, #sprint_end_date_container"
        ).hide();
      }
    }
    formBehavior();

    (function () {
      "use strict";
      var forms = document.querySelectorAll(".needs-validation");
      Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener(
          "submit",
          function (event) {
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
  });

  /**
   * Submits the meeting form data and handles the response.
   * Sends AJAX request for scheduling and email notifications.
   * @author Rama Selvan
   */
  $(document).ready(function () {
    $("#meetingForm").on("submit", function (e) {
      e.preventDefault();
      let formData = $(this).serialize();
      formData += "&Schedulebutton=1";
      let url = refinement_url + "meeting/scheduleMeeting";
      var meetStartDate = document.getElementById("meeting_start_date").value;
      function isSunday(date) {
        const givenDate = new Date(date);
        return givenDate.getDay() === 0;
      }
      if (isSunday(meetStartDate)) {
        Swal.fire({
          title: "Are you sure?",
          text: "Did you like to schedule meeting on sundays",
          icon: "warning",
          showCancelButton: true,
          confirmButtonColor: "#3085d6",
          cancelButtonColor: "#d33",
          confirmButtonText: "Yes, schedule it!",
        }).then((result) => {
          if (result.isConfirmed) {
            submitData();
          }
        });
      } else {
        submitData();
      }
      function submitData() {
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
                  text: "Meeting scheduled and Email notification sent",
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
            } else {
              Swal.fire({
                title: "Error",
                text:
                  response.message ||
                  "An error occurred while scheduling meeting(s)",
                icon: "error",
                confirmButtonText: "OK",
              });
            }
          },
          error: function (xhr, status, error) {
            console.error("AJAX Error:", status, error);
            Swal.fire({
              title: "Error",
              text: "An error occurred while scheduling meeting(s)",
              icon: "error",
              confirmButtonText: "OK",
            });
          },
        });
      }
    });
  });

  //dynamic checkbox generation
  function generateColumnCheckboxes(data) {
    const columnDropdown = document.getElementById("columnDropdown");
    columnDropdown.innerHTML = "";

    let firstItem = null;
    for (let i = 0; i < data.length; i++) {
      if (data[i] && typeof data[i] === "object" && !Array.isArray(data[i])) {
        firstItem = data[i];
        break;
      }
    }

    if (firstItem) {
      const columns = Object.keys(firstItem);

      columns.forEach((key, index) => {
        const checkboxDiv = document.createElement("div");
        checkboxDiv.classList.add("form-check-list");

        const checkbox = document.createElement("input");
        checkbox.type = "checkbox";
        checkbox.classList.add("form-check-inputs");
        checkbox.id = "checkbox-" + index;
        checkbox.dataset.columnIndex = index;
        checkbox.checked = index < 7; // Check the first 7 columns by default
        checkbox.addEventListener("change", toggleColumnVisibility);

        const label = document.createElement("label");
        label.classList.add("form-check-label");
        label.setAttribute("for", "checkbox-" + index);
        label.textContent = key.replace(/_/g, " ");

        checkboxDiv.appendChild(checkbox);
        checkboxDiv.appendChild(label);
        columnDropdown.appendChild(checkboxDiv);
      });
    }

    updateCheckboxStates();
  }

  function toggleColumnVisibility(event) {
    const checkbox = event.target;
    const columnIndex = checkbox.dataset.columnIndex;
    const columns = document.querySelectorAll(`.column-${columnIndex}`);

    columns.forEach((column) => {
      column.style.display = checkbox.checked ? "" : "none";
    });

    updateCheckboxStates();
  }

  function updateCheckboxStates() {
    const checkboxes = document.querySelectorAll(".form-check-inputs");
    const checkedCount = Array.from(checkboxes).filter(
      (cb) => cb.checked
    ).length;

    checkboxes.forEach((checkbox) => {
      if (!checkbox.checked && checkedCount >= maxCheckedColumns) {
        checkbox.disabled = true;
      } else {
        checkbox.disabled = false;
      }
    });
  }
  // var startDate = document.getElementById("fromDate");
  // var year1 = new Date().getFullYear();
  // var initializeDate = year1 + "-01-01";
  // var finalDate = year1 + "-12-31";
  // startDate.setAttribute("min", initializeDate);
  // startDate.setAttribute("max", finalDate);
  // document.getElementById("toDate").setAttribute("max", finalDate);
  // console.log(finalDate);
  // startDate.addEventListener("change", () => {
  //   document.getElementById("toDate").setAttribute("min", startDate.value);

  //   console.log(startDate.value);
  // })
  flatpickr("#fromDate", {
    minDate: new Date().getFullYear() + "-01-01",
    maxDate: new Date().getFullYear() + "-12-31",
    disable: [
      function (date) {
        // Disable weekends (0 = Sunday, 6 = Saturday)
        return date.getDay() === 0 || date.getDay() === 6;
      },
    ],
    // Set the minimum date here
  });
  document.getElementById("fromDate").addEventListener("change", () => {
    flatpickr("#toDate", {
      maxDate: new Date().getFullYear() + "-12-31",
      minDate: document.getElementById("fromDate").value, // Set the minimum date here
      disable: [
        function (date) {
          // Disable weekends (0 = Sunday, 6 = Saturday)
          return date.getDay() === 0 || date.getDay() === 6;
        },
      ],
    });
    console.log(document.getElementById("toDate"));
  });

  function sortTable(column) {
    console.log(sortOrder);
    sortOrder = sortColumn === column ? -sortOrder : 1;
    sortColumn = column;
    console.log("column", column);
    data.sort((a, b) => {
      if (a[column] < b[column]) return -sortOrder;
      if (a[column] > b[column]) return sortOrder;
      return 0;
    });

    generateTableBody(data);
  }

  document.getElementById("prevPage").addEventListener("click", () => {
    if (currentPage > 1) {
      currentPage--;
      if (ajaxFunction === "fetchTableData") {
        fetchTableData(currentPage);
      } else if (ajaxFunction == "filterTable") {
        filterTable(currentPage);
      } else if (ajaxFunction == "applyFilter") {
        applyFilter(formData, currentPage);
      }
    }
  });

  document.getElementById("nextPage").addEventListener("click", () => {
    const totalPages_sprint = Math.ceil(totalRows / rowsPerPage);
    if (currentPage < totalPages_sprint) {
      currentPage++;

      if (ajaxFunction === "fetchTableData") {
        fetchTableData(currentPage);
      } else if (ajaxFunction == "filterTable") {
        filterTable(currentPage);
      } else if (ajaxFunction == "applyFilter") {
        applyFilter(formData, currentPage);
      }
    }
  });

  function updatePaginationControls(currentPage) {
    const totalPages_sprint = Math.ceil(totalRows / rowsPerPage);
    document.getElementById(
      "pageInfo"
    ).textContent = `Page ${currentPage} of ${totalPages_sprint}`;
    console.log(currentPage);
  }
  document.addEventListener("DOMContentLoaded", function () {
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
  });
  document.getElementById("resetbtn").addEventListener("click", () => {
    document.getElementById("filterForm").reset();
    document.getElementById("noti").style.visibility = "hidden";
    fetchTableData(1);
    ajaxFunction = "fetchTableData";
  });
  function countNumberOfFilters() {
    let numberOfFilters = document.getElementById("noti");
    numberOfFilters.style.visibility = "visible";
    let formData = $("#filterForm").serializeArray();
    console.log(formData);
    let filledFieldsCount = formData.filter(function (field) {
      return field.value.trim() !== "";
    }).length;
    if (filledFieldsCount > 0) {
      numberOfFilters.textContent = filledFieldsCount;
    } else {
      numberOfFilters.style.display = "none";
    }
  }
  function filterTable(page) {
    var searchQuery = $("#backlogSearchInput").val().toLowerCase();

    $.ajax({
      url: refinement_url + "sprint/sprintlist",
      type: "GET",
      data: {
        page: page,
        limit: rowsPerPage,
        filter: searchQuery,
      },
      contentType: "application/json",
      dataType: "json",
      success: function (response) {
        console.log("Server Response:", response);
        if (response && response.data && response.data.length > 0) {
          document.getElementById("noData").style.display = "none";
          document.getElementById("priority-table").style.display = "table";
          document.getElementById("pageInfo").style.display = "block";
          totalRows = response.totalrows;
          generateTableHeader(response.data);
          generateTableBody(response.data);
          generateColumnCheckboxes(response.data);
          updatePaginationControls(response.current_page);
          initialPage(response.current_page);
          // for appling tooltip
          $(function () {
            $('[data-toggle="tooltip"]').tooltip();
          });
        } else {
          console.error("Invalid response format");
          invalidReponse();
          const tableBody = document.getElementById("tableBody");
          tableBody.innerHTML = "";
          document.getElementById("priority-table").style.display = "none";
          document.getElementById("noData").style.display = "block";
          document.getElementById("pageInfo").style.display = "none";
        }
      },
      error: function (xhr, status, error) {
        console.error("AJAX Error:", status, error);
        console.error("Response Text:", xhr.responseText);
      },
    });
  }

  function applyFilter(formData, page) {
    console.log("applyFilter", page);
    $.ajax({
      url: refinement_url + "sprint/applySelection",
      type: "POST",
      dataType: "json",
      data: {
        page: page,
        limit: rowsPerPage,
        formData: formData,
      },

      success: function (response) {
        countNumberOfFilters();
        console.log("Server Response:", response);
        if (response && response.data && response.data.length > 0) {
          document.getElementById("noData").style.display = "none";
          document.getElementById("priority-table").style.display = "table";
          document.getElementById("pageInfo").style.display = "block";
          totalRows = response.totalrows;
          generateTableHeader(response.data);
          generateTableBody(response.data);
          generateColumnCheckboxes(response.data);
          updatePaginationControls(response.current_page);
          initialPage(response.current_page);
          // for appling tooltip
          $(function () {
            $('[data-toggle="tooltip"]').tooltip();
          });
        } else {
          console.error("Invalid response format");
          invalidReponse();
          const tableBody = document.getElementById("tableBody");
          tableBody.innerHTML = "";
          document.getElementById("priority-table").style.display = "none";
          document.getElementById("noData").style.display = "block";
          document.getElementById("pageInfo").style.display = "none";
        }
      },
      error: function (xhr, status, error) {
        console.log(error);
      },
    });
  }
}
