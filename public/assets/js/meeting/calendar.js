/**
 * @file calendars.js
 * @fileoverview Manages meetings, displays the meeting and sprint details in calendar,
 *               retrieves and updates meeting details, and handles meeting cancellations.
 * @module FullCalendar
 * @requires FullCalendar
 * @since 2024-06-25
 * @author Hari Sankar, Gokul ,Rama Selvan, Ruban Edward.
 */

/**
 * Initializes the FullCalendar instance when the DOM content is fully loaded.
 * @event DOMContentLoaded
 * @listens document#DOMContentLoaded
 * @description This function is called when the DOM is fully loaded. It sets up the calendar
 *              element, initializes the default event type, and creates a new FullCalendar instance
 *              with specific configurations.
 */

if (typeof calendar !== "undefined") {
  document.addEventListener("DOMContentLoaded", function () {
    /**
     * The HTML element where the calendar will be rendered.
     * @type {HTMLElement}
     */
    var calendarEl = document.getElementById("calendar");

    /**
     * The current event type to be displayed in the calendar
     * @type {string}
     */
    var currentEventType = "Meeting";

    /**
     * The FullCalendar instance initialized with configurations.
     * @type {FullCalendar.Calendar}
     */
    // ---- Calendar state ----------------------------------------------------
    const VIEW_KEY = "orvexa.calendar.view";
    const VALID_VIEWS = ["dayGridMonth", "timeGridWeek", "timeGridDay", "listMonth"];
    let savedView = null;
    try { savedView = window.localStorage.getItem(VIEW_KEY); } catch (e) { /* storage unavailable */ }

    const calendar = new FullCalendar.Calendar(calendarEl, {
      initialView: VALID_VIEWS.includes(savedView) ? savedView : "dayGridMonth",
      // Toolbar is built in calendar.php (own markup) so views can be switched freely
      headerToolbar: false,
      height: "auto",
      nowIndicator: true,
      navLinks: true,
      navLinkDayClick: "timeGridDay",
      dayMaxEvents: 3,
      moreLinkClick: "popover",
      moreLinkText: function (n) { return "+" + n + " more"; },
      fixedWeekCount: false,
      stickyHeaderDates: true,
      expandRows: true,
      slotMinTime: "07:00:00",
      slotMaxTime: "21:00:00",
      slotDuration: "00:30:00",
      snapDuration: "00:15:00",
      scrollTime: "08:00:00",
      allDayText: "All day",
      eventMinHeight: 24,
      eventInteractive: true,
      eventDisplay: "block",
      eventTimeFormat: { hour: "numeric", minute: "2-digit", omitZeroMinute: true, meridiem: "short" },
      slotLabelFormat: { hour: "numeric", minute: "2-digit", meridiem: "short" },
      dayHeaderFormat: { weekday: "short" },
      views: {
        dayGridMonth: { dayHeaderFormat: { weekday: "short" } },
        timeGridWeek: { dayHeaderFormat: { weekday: "short", day: "numeric" }, dayMaxEvents: false },
        timeGridDay: { dayHeaderFormat: { weekday: "long", day: "numeric" } },
        listMonth: { listDayFormat: { weekday: "long", day: "numeric", month: "short" }, listDaySideFormat: false },
      },
      noEventsContent: "Nothing scheduled in this period",

      // Keep the page and the calendar chrome in sync with whatever view/date is showing
      datesSet: function (arg) {
        onDatesSet(arg);
      },

      /** Click on an empty day (month) or time slot (week/day). */
      dateClick: function (info) {
        const dateStr = info.dateStr.slice(0, 10);
        const minutes = info.allDay ? null : info.date.getHours() * 60 + info.date.getMinutes();
        handleSlotClick(dateStr, minutes);
      },

      /**
       * Handles event click events on the calendar.
       * @param {Object} info - Information about the clicked event.
       */
      eventClick: function (info) {
        var eventId = info.event.id;
        var numericValue = getNumericValue(eventId);
        var meetType = info.event.extendedProps.meetType;
        if (info.event.extendedProps.eventType === "Meeting") {
          clearModalContent();
          $.ajax({
            url:
              baseUrl + "meeting/eventdetails/" + numericValue + "/" + meetType,
            method: "POST",
            success: function (response) {
              if (
                response &&
                response.length > 0 &&
                response[0].cancel_reason == null
              ) {
                var meetingStarttime =
                  response[0].meeting_start_date
                    .split("-")
                    .reverse()
                    .join("-") +
                  " " +
                  response[0].meeting_start_time;
                var meetingEndtime =
                  response[0].meeting_end_date +
                  " " +
                  response[0].meeting_end_time;
                var meetingLink = response[0].meeting_link;
                var meetingTime =
                  formatToAmPm(response[0].meeting_start_time) +
                  " - " +
                  formatToAmPm(response[0].meeting_end_time);
                var deletedMeeting = response[0].is_deleted;
                var timeLogged = response[0].is_logged;
                var hostCreated = response[0].r_user_id;
                var iosEndTime = meetingEndtime.replace(" ", "T");
                // Show the meeting details modal
                $("#meetingModal").modal("show");
                document.getElementById("modalTitle").innerText =
                  "Meeting Details";
                $(
                  "#scheduleMeetingForm, #timeLogSection, #helpdetails, #groupdetails,#pcancel"
                ).hide();
                $(
                  "#eventDetails,#peventdesc,#pmeetingLink,#peventMembers"
                ).show();
                document.getElementById("sprint_div").style.visibility =
                  "visible";
                document.getElementById("eventTitle").innerText =
                  response[0].meeting_title;
                document.getElementById("productName").innerText =
                  response[0].product_name;
                document.getElementById("sprintName").innerText =
                  response[0].sprint_name || "No Sprint Details";
                document.getElementById("eventDate").innerText =
                  convertDateFormat(response[0].meeting_start_date);
                document.getElementById("eventdesc").innerText =
                  response[0].meeting_description || "No description Provided";
                document.getElementById("eventHost").innerText =
                  response[0].first_name;
                document.getElementById("eventduration").innerText =
                  formatDuration(
                    response[0].meeting_start_time,
                    response[0].meeting_end_time
                  );
                document.getElementById("eventStart").innerText = meetingTime;
                var linkElement = document.getElementById("meetingLink");
                var descriptionElement =
                  document.getElementById("eventDescription");
                if (meetingLink) {
                  linkElement.href = meetingLink;
                  linkElement.target = "_blank";
                  descriptionElement.innerText = meetingLink;
                } else {
                  descriptionElement.innerText = "No link provided";
                }
                document.getElementById("eventMembers").innerText =
                  response[1].meeting_members_names;
                document.querySelector("#eventDescription").href =
                  meetingLink || "#";
                if (response[0].r_meeting_type_id == 2) {
                  document.getElementById("viewDetails").style.display =
                    "block";
                  document.querySelector(".view-details a").innerHTML =
                    "<i class='far fa-eye'></i> backlog item details";
                  document.querySelector(".view-details a").href =
                    baseUrl +
                    "backlog/backlogitemdetails?pid=" +
                    response[0].r_product_id +
                    "&pblid=" +
                    response[0].r_backlog_item_id;
                }

                if (
                  response[0].r_meeting_type_id == 3 ||
                  response[0].r_meeting_type_id == 4 ||
                  response[0].r_meeting_type_id == 5
                ) {
                  document.getElementById("viewDetails").style.display =
                    "block";
                  document.querySelector(".view-details a").innerHTML =
                    "<i class='far fa-eye'></i> View sprint details";
                  document.querySelector(".view-details a").href =
                    baseUrl +
                    "sprint/navsprintview?sprint_id=" +
                    response[0].r_sprint_id;
                }

                if (
                  new Date(meetingStarttime) <= new Date() &&
                  deletedMeeting === "N" &&
                  timeLogged === "N" &&
                  sessionId == hostCreated
                ) {
                  var timeLogButton = document.createElement("button");
                  timeLogButton.innerText = "Time Log in Redmine";
                  timeLogButton.classList.add(
                    "btn",
                    "btn-primary",
                    "me-1",
                    "mb-2"
                  );

                  timeLogButton.addEventListener("click", function () {
                    showTimeLogDetails(response[0], response[1]);
                  });
                  document
                    .getElementById("eventDetails")
                    .appendChild(timeLogButton);
                }

                if (
                  new Date(iosEndTime) >= new Date() &&
                  deletedMeeting === "N" &&
                  sessionId == hostCreated
                ) {
                  // Create the "Edit Meeting" button
                  var editButton = document.createElement("button");
                  editButton.innerText = "Edit meeting";
                  editButton.classList.add(
                    "btn",
                    "btn-primary",
                    "me-1",
                    "mb-2"
                  );

                  editButton.addEventListener("click", function () {
                    editMeeting(response);
                  });
                }
                // Check if the meeting start time is in the future
                if (
                  new Date(meetingStarttime) > new Date() &&
                  deletedMeeting === "N" &&
                  sessionId == hostCreated
                ) {
                  // Create the "Cancel Meeting" button

                  var cancelButton = document.createElement("button");
                  cancelButton.innerText = "Cancel meeting";
                  cancelButton.classList.add(
                    "btn",
                    "btn-danger",
                    "me-1",
                    "mb-2"
                  );
                  cancelButton.id = "cancelButton"; // Add ID to cancelButton

                  // Add event listener to the "Cancel Meeting" button
                  cancelButton.addEventListener("click", function (event) {
                    event.preventDefault(); // Prevent the default action of the button
                    $("#meetingModal").modal("hide");
                    // Show a confirmation dialog with reason input for cancellation
                    Swal.fire({
                      title: "Cancel Meeting",
                      text: "Please provide a reason for the cancellation:",
                      icon: "warning",
                      input: "textarea",
                      inputPlaceholder: "Enter your reason here...",
                      inputAttributes: {
                        name: "cancellationReason",
                      },
                      showCancelButton: true,
                      cancelButtonText: "Back",
                      confirmButtonText: "Submit",
                      didOpen: () => {
                        const confirmButton = Swal.getConfirmButton();
                        confirmButton.name = "MeetingCancelReason";
                      },
                      // Validate the reason input before submitting
                      preConfirm: (reason) => {
                        if (!reason) {
                          Swal.showValidationMessage(
                            "Please enter a reason for the cancellation."
                          );
                          return false;
                        } else {
                          return new Promise((resolve) => {
                            $.ajax({
                              url:
                                baseUrl +
                                "meeting/cancelMeeting/" +
                                numericValue,
                              method: "POST",
                              data: {
                                reason: reason,
                              },
                              success: function (response) {
                                resolve(response);
                              },
                              error: function (xhr, status, error) {
                                console.error("AJAX Error:", error);
                                Swal.showValidationMessage(
                                  "There was a problem submitting your reason. Please try again later."
                                );
                              },
                            });
                          });
                        }
                      },
                    }).then((result) => {
                      // If the reason is confirmed, show success message and reload the calendar
                      if (result.isConfirmed) {
                        Swal.fire({
                          title: "Meeting Cancelled",
                          text: "Your meeting has been cancelled.",
                          icon: "success",
                        }).then(() => {
                          window.location.href = window.location.href;
                        });
                      }
                    });
                  });
                  document
                    .getElementById("eventDetails")
                    .appendChild(editButton);
                  document
                    .getElementById("eventDetails")
                    .appendChild(cancelButton);
                }
              } else if (
                response &&
                response.length > 0 &&
                response[0].cancel_reason != null
              ) {
                var meetingName = response[0].meeting_title;
                var meetingStarttime =
                  response[0].meeting_start_date
                    .split("-")
                    .reverse()
                    .join("-") +
                  " " +
                  response[0].meeting_start_time;
                // var meetingEndtime = response[0].meeting_start_date + " " + response[0].meeting_end_time;
                var meetingLink = response[0].meeting_link;
                var meetingTime =
                  formatToAmPm(response[0].meeting_start_time) +
                  " - " +
                  formatToAmPm(response[0].meeting_end_time);
                var deletedMeeting = response[0].is_deleted;

                // Show the meeting details modal
                $("#meetingModal").modal("show");
                document.getElementById("modalTitle").innerText =
                  "Meeting Details";
                $(
                  "#scheduleMeetingForm, #timeLogSection, #groupdetails, #helpdetails, #pmeetingLink, #peventMembers, #peventdesc, #groupdetails"
                ).hide();
                $("#eventDetails,#pcancel").show();
                document.getElementById("sprint_div").style.visibility =
                  "visible";
                document.getElementById("eventTitle").innerText = meetingName;
                document.getElementById("productName").innerText =
                  response[0].product_name;
                document.getElementById("sprintName").innerText =
                  response[0].sprint_name || "No Sprint Details";
                document.getElementById("eventDate").innerText =
                  response[0].meeting_start_date;
                document.getElementById("eventHost").innerText =
                  response[0].first_name;
                document.getElementById("eventduration").innerText =
                  formatDuration(
                    response[0].meeting_start_time,
                    response[0].meeting_end_time
                  );
                document.getElementById("eventStart").innerText = meetingTime;
                document.getElementById("cancel").innerText =
                  response[0].cancel_reason;
                document.getElementById("cancel").style.color = "red";
                document.getElementById("cancel").style.fontWeight = "700";
              } else {
                console.error("Empty or invalid response received.");
                // Handle the case where response is empty or not as expected
              }
            },
            error: function (error) {
              alert("An error occurred while fetching meeting detail.");
            },
          });
        } else if (info.event.extendedProps.eventType === "Sprint") {
          // Clear previous modal content
          clearModalContent();
          // Make an AJAX request to fetch sprint details
          $.ajax({
            url: baseUrl + "meeting/sprintdetails/" + numericValue,
            method: "POST",
            success: function (response) {
              // Check if the response is valid and contains data
              if (response && response.length > 0) {
                // Show the modal with sprint details
                $("#sprintModal").modal("show");
                document.getElementById("sprintModalLabel").innerText =
                  "Sprint Details";

                // Sprint ID
                let sprintId = response[0].sprint_id;

                // Populate sprint details in the modal
                document.getElementById("sprintNames").innerText =
                  response[0].sprint_name;
                document.getElementById("sprintCreator").innerText =
                  response[0].first_name;
                document.getElementById("sprintProduct").innerText =
                  response[0].product_name;
                document.getElementById("sprintCustomer").innerText =
                  response[0].customer_name;
                document.getElementById("sprintDuration").innerText =
                  response[0].sprint_duration_value;
                document.getElementById("startDate").innerText =
                  response[0].start_date;
                document.getElementById("endDate").innerText =
                  response[0].end_date;
                document.getElementById("sprintStatus").innerText =
                  response[0].status_name;

                function restoreButtonListeners() {
                  document.getElementById("changeStatusBtn").onclick =
                    function () {
                      document.getElementById("modalBody").innerHTML = `
                            <h5 class="modal-title">Change Status</h5>
                            <form id="changeStatusForm" class="mb-4">
                              <div class="row alterbutton">
                                <div class="col-md-5 mb-3">
                                  <label for="newStatus" class="form-label">Sprint Status</label>
                                </div>
                                <div class="col-md-7">
                                  <select class="form-select" name="newStatus" id="newStatus" required></select>
                                </div>
                              </div>
                              <div id="sprintButton">
                                <button type="button" class="btn btn-primary" id="backButton">Back</button>
                                <button type="submit" class="btn btn-primary">Confirm</button>
                              </div>
                            </form>
                          `;

                      var selectElement = document.getElementById("newStatus");
                      sprintStatus.forEach((status) => {
                        const option = document.createElement("option");
                        option.value = status.module_status_id;
                        option.textContent = status.status_name;
                        if (status.status_name === response[0].status_name) {
                          option.selected = true;
                        }
                        selectElement.appendChild(option);
                      });

                      $("#changeStatusForm").on("submit", function (e) {
                        e.preventDefault();
                        const newStatus = $("#newStatus").val();
                        $.ajax({
                          url:
                            baseUrl +
                            "sprint/changeSprintStatusById/" +
                            sprintId,
                          type: "POST",
                          data: {
                            newStatus: newStatus,
                          },
                          success: function (response) {
                            if (response.success === true) {
                              Swal.fire({
                                icon: "success",
                                title: "Sprint",
                                text: "Status Changed",
                                confirmButtonText: "OK",
                              }).then((result) => {
                                if (result.isConfirmed) {
                                  location.reload();
                                }
                              });
                            } else {
                              Swal.fire({
                                icon: "error",
                                title: "Sprint",
                                text: "Status Not Changed",
                                confirmButtonText: "OK",
                              }).then((result) => {
                                if (result.isConfirmed) {
                                  location.reload();
                                }
                              });
                            }
                          },
                          error: function (xhr, status, error) {
                            Swal.fire({
                              icon: "warning",
                              title: "Sprint",
                              text: "Something Went Wrong",
                              confirmButtonText: "OK",
                            }).then((result) => {
                              if (result.isConfirmed) {
                                location.reload();
                              }
                            });
                          },
                        });
                      });

                      document.getElementById("backButton").onclick =
                        function () {
                          restoreSprintDetails();
                        };
                    };

                  document.getElementById("viewDetailsLink").href =
                    baseUrl +
                    "sprint/navsprintview?sprint_id=" +
                    response[0].sprint_id;
                }

                function restoreSprintDetails() {
                  let modalContent = `
                      <div id="sprintDetails" class="sprintDetails mb-4">
                          <p><b>Sprint name</b> <span>:</span> <span id="sprintNames">${response[0].sprint_name}</span></p>
                          <p><b>Sprint creator</b> <span>:</span> <span id="sprintCreator">${response[0].first_name}</span></p>
                          <p><b>Product name</b> <span>:</span> <span id="sprintProduct">${response[0].product_name}</span></p>
                          <p><b>Customer</b> <span>:</span> <span id="sprintCustomer">${response[0].customer_name}</span></p>
                          <p><b>Duration</b> <span>:</span> <span id="sprintDuration">${response[0].sprint_duration_value}</span></p>
                          <p><b>Start date</b> <span>: </span><span id="startDate">${response[0].start_date}</span></p>
                          <p><b>End date</b> <span>: </span><span id="endDate">${response[0].end_date}</span></p>
                          <p><b>Sprint status</b> <span>: </span><span id="sprintStatus">${response[0].status_name}</span></p>
                      </div>
                      <div class="button-container" id="orgButton">`;

                  if (permissions.changestatus) {
                    modalContent += `
                          <button type="button" class="btn btn-primary" id="changeStatusBtn">
                              <i class="bi bi-check-square"></i> Change Status
                          </button>`;
                  }

                  modalContent += `
                      <a href="" id="viewDetailsLink">
                          <button type="button" class="btn btn-primary" id="viewDetailsBtn">
                              <i class="bi bi-eye"></i> View Details
                          </button>
                      </a>
                      </div>`;

                  document.getElementById("modalBody").innerHTML = modalContent;

                  restoreButtonListeners();
                }
                restoreButtonListeners();
              } else {
                // Handle the case where response is empty or not as expected
                console.error("Empty or invalid response received.");
              }
            },
            error: function (error) {
              alert("An error occurred while fetching sprint detail.");
            },
          });
        }
        /**
         * Show the time log details in the modal and entry for the log
         * @author Rama Selvan
         * @param {Object} meetingData - The data object containing meeting details.
         */

        function showTimeLogDetails(meetingData, meetingMembers) {
          // Set the modal title and display appropriate sections
          document.getElementById("modalTitle").innerText = "Time log details";
          $(
            "#scheduleMeetingForm, #eventDetails,  #helpdetails, #groupdetails"
          ).hide();
          $("#timeLogSection").show();
          const comments =
            meetingData.product_name +
            "," +
            meetingData.meeting_title +
            "," +
            meetingData.meeting_description;
          // Format meeting start and end times, duration
          const startTime = formatToAmPm(meetingData.meeting_start_time);
          const endTime = formatToAmPm(meetingData.meeting_end_time);
          const duration = calculateDurationInMinutes(
            meetingData.meeting_start_time,
            meetingData.meeting_end_time
          );
          const fractionalHours = convertMinutesToFractionalHours(duration);
          const formattedDuration = `${duration} Minutes (${fractionalHours})`;

          // Populate meeting details
          document.getElementById("mHost").innerText =
            meetingData.first_name || "N/A";
          document.getElementById("mName").innerText =
            meetingData.meeting_title || "N/A";
          document.getElementById("mDescription").innerText =
            meetingData.meeting_description || "N/A";
          document.getElementById("mDate").innerText =
            meetingData.meeting_start_date;
          document.getElementById("mstartTime").innerText = startTime;
          document.getElementById("mendTime").innerText = endTime;
          document.getElementById("mDuration").innerText = formattedDuration;
          document.getElementById("mProduct").innerText =
            meetingData.product_name || "N/A";
          document.getElementById("meetingIdHiddenInput").value =
            meetingData.meeting_details_id || "N/A";
          document.getElementById("comments").value = comments || "N/A";
          document.getElementById("sdate").value =
            meetingData.meeting_start_date;
          document.getElementById("product").value = meetingData.r_product_id;
          document.getElementById("mtype").value =
            meetingData.r_meeting_type_id;
          document.getElementById("sprintId").value = meetingData.r_sprint_id;

          const attendeesList = document.getElementById("attendeesList");
          attendeesList.innerHTML = ""; // Clear existing content

          if (
            meetingMembers.meeting_members_names &&
            meetingMembers.meeting_members_id
          ) {
            const names = meetingMembers.meeting_members_names.split(",");
            const ids = meetingMembers.meeting_members_id.split(",");

            if (names.length === ids.length) {
              names.forEach((attendee, index) => {
                const attendeeDiv = document.createElement("div");
                attendeeDiv.className = "d-flex align-items-center mb-3";
                attendeeDiv.innerHTML = `
                      <label style="color: #117a45; width: 70%; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${
                        index + 1
                      }. ${attendee.trim()}</label>
                      <input type="hidden" name="attendees[${index}][id]" value="${ids[
                  index
                ].trim()}">
                      <input type="hidden" name="attendees[${index}][name]" value="${attendee.trim()}">
                      <input type="number" name="attendees[${index}][hours]" class="form-control form-control-sm ml-2" placeholder="Hours" 
                             value="${fractionalHours}" step="0.25" min="0" max="24" style="width: 80px;">
                  `;
                attendeesList.appendChild(attendeeDiv);
              });
            } else {
              attendeesList.innerHTML = "<p>No attendees found.</p>";
            }

            // Show the modal if it's not already visible
            if (!$("#meetingModal").is(":visible")) {
              $("#meetingModal").modal("show");
            }
          }
        }
        //
      },

      // ---- Event appearance ------------------------------------------------
      events: [],
      eventContent: function (arg) {
        const p = arg.event.extendedProps;
        const view = arg.view.type;
        if (p.eventType === "HolidayBg") { return { html: "" }; }
        if (p.eventType === "Holiday") {
          return { html: '<span class="ox-evt-title"><i class="fas fa-umbrella-beach"></i> ' + esc(arg.event.title) + "</span>" };
        }
        if (p.eventType === "Sprint") {
          return { html: '<span class="ox-evt-title"><i class="fas fa-flag-checkered"></i> ' + esc(arg.event.title) + "</span>" };
        }
        const badge = p.state === "needslog" ? '<span class="ox-evt-flag" title="Time not logged yet"><i class="fas fa-clock"></i></span>' : "";
        if (view === "listMonth") {
          return { html: '<span class="ox-evt-title">' + esc(arg.event.title) + "</span>" + badge + '<span class="ox-evt-state">' + esc((STATE_META[p.state] || {}).label || "") + "</span>" };
        }
        if (view.indexOf("timeGrid") === 0) {
          return { html: '<div class="ox-evt-inner"><div class="ox-evt-title">' + esc(arg.event.title) + badge + '</div><div class="ox-evt-time">' + esc(arg.timeText) + "</div></div>" };
        }
        return { html: '<div class="ox-evt-inner ox-evt-row"><span class="ox-evt-time">' + esc(arg.timeText) + '</span><span class="ox-evt-title">' + esc(arg.event.title) + "</span>" + badge + "</div>" };
      },
      eventDidMount: function (info) {
        const p = info.event.extendedProps;
        if (p.eventType === "HolidayBg") { return; }
        const fmt = { hour: "numeric", minute: "2-digit", meridiem: "short" };
        let when = "";
        if (p.eventType === "Meeting" && info.event.start) {
          when = calendar.formatDate(info.event.start, fmt) + (info.event.end ? " – " + calendar.formatDate(info.event.end, fmt) : "");
        }
        const label = p.eventType === "Meeting" ? (STATE_META[p.state] || {}).label : p.eventType === "Sprint" ? p.statusLabel : "Holiday";
        info.el.setAttribute("aria-label", info.event.title + (when ? ", " + when : "") + (label ? ", " + label : ""));
        if (window.tippy) {
          tippy(info.el, {
            content: "<strong>" + esc(info.event.title) + "</strong>" + (when ? "<div>" + esc(when) + "</div>" : "") + (label ? '<div class="ox-tip-state">' + esc(label) + "</div>" : ""),
            allowHTML: true, placement: "top", delay: [350, 0], theme: "ox", appendTo: document.body,
          });
        }
      },
      eventWillUnmount: function (info) {
        if (info.el && info.el._tippy) { info.el._tippy.destroy(); }
      },
    });

    // =========================================================================
    // Toolbar, filters, mini calendar and rendering
    // =========================================================================
    function el(id) { return document.getElementById(id); }
    function esc(v) {
      return String(v == null ? "" : v).replace(/[&<>"']/g, function (c) {
        return { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c];
      });
    }
    function pad(n) { return String(n).padStart(2, "0"); }
    function ymd(d) { return d.getFullYear() + "-" + pad(d.getMonth() + 1) + "-" + pad(d.getDate()); }
    function toDate(date, time) { return new Date(date + "T" + (time || "00:00:00")); }
    function addDays(dateStr, n) {
      const d = new Date(dateStr + "T00:00:00");
      d.setDate(d.getDate() + n);
      return ymd(d);
    }

    const STATE_META = {
      upcoming: { label: "Upcoming", icon: "fa-calendar-alt" },
      live: { label: "Ongoing", icon: "fa-spinner" },
      done: { label: "Completed", icon: "fa-check-circle" },
      needslog: { label: "Needs time log", icon: "fa-clock" },
      cancelled: { label: "Cancelled", icon: "fa-ban" },
    };

    function meetingState(m, now) {
      if (m.is_deleted === "Y") { return "cancelled"; }
      const s = toDate(m.meeting_start_date, m.meeting_start_time);
      const e = toDate(m.meeting_end_date || m.meeting_start_date, m.meeting_end_time);
      if (e < now) { return m.is_logged === "Y" ? "done" : "needslog"; }
      if (s <= now) { return "live"; }
      return "upcoming";
    }
    function sprintState(statusName) {
      if (statusName === "Sprint Completed") { return "done"; }
      if (statusName === "Sprint Planned") { return "upcoming"; }
      return "live";
    }

    // ---- Filters (they now combine instead of overriding each other) ------
    const filters = { kind: "Meeting", type: "All", status: "All", product: "All" };

    function statusMatches(state) {
      if (filters.status === "All") { return true; }
      if (filters.status === "Completed") { return state === "done" || state === "needslog"; }
      return state === filters.status;
    }
    function meetingMatches(m, now, ignoreStatus) {
      if (filters.type !== "All" && String(m.r_meeting_type_id) !== filters.type) { return false; }
      return ignoreStatus ? true : statusMatches(meetingState(m, now));
    }
    function sprintMatches(sp, ignoreStatus) {
      if (filters.product !== "All" && String(sp.product_name || "").toLowerCase() !== filters.product) { return false; }
      return ignoreStatus ? true : (filters.status === "All" || sp.status_name === filters.status);
    }

    let mini = null;
    let eventDays = new Set();

    function buildEvents() {
      const now = new Date();
      const out = [];
      const days = new Set();

      if (filters.kind === "Meeting") {
        meetingDetails.forEach(function (m) {
          if (!meetingMatches(m, now, false)) { return; }
          const state = meetingState(m, now);
          out.push({
            id: "meeting-event-" + m.meeting_details_id,
            title: m.meeting_type_name,
            start: m.meeting_start_date + "T" + m.meeting_start_time,
            end: (m.meeting_end_date || m.meeting_start_date) + "T" + m.meeting_end_time,
            classNames: ["ox-evt", "ox-" + state],
            extendedProps: { eventType: "Meeting", meetType: m.r_meeting_type_id, deleted: m.is_deleted, logged: m.is_logged, state: state },
          });
          days.add(m.meeting_start_date);
        });
      } else {
        sprintDetails.forEach(function (sp) {
          if (!sprintMatches(sp, false)) { return; }
          const state = sprintState(sp.status_name);
          // One continuous bar per sprint (instead of one event per weekday)
          out.push({
            id: "sprint-event-" + sp.sprint_id,
            title: capitalizeFirstLetter(sp.sprint_name || "") + " · " + capitalizeFirstLetter(sp.product_name || ""),
            start: sp.start_date,
            end: addDays(sp.end_date, 1),
            allDay: true,
            classNames: ["ox-evt", "ox-" + state],
            extendedProps: { eventType: "Sprint", statusName: sp.status_name, statusLabel: String(sp.status_name || "").replace(/^Sprint /, "") },
          });
          for (let d = sp.start_date; d <= sp.end_date; d = addDays(d, 1)) { days.add(d); }
        });
      }

      // Holidays: shade every day of the range and label it (multi-day holidays now show on every day)
      (holidayDetails || []).forEach(function (h) {
        const last = h.holiday_end_date || h.holiday_start_date;
        out.push({ id: "holiday-bg-" + h.holiday_id, start: h.holiday_start_date, end: addDays(last, 1), allDay: true, display: "background", classNames: ["ox-holiday-bg"], extendedProps: { eventType: "HolidayBg" } });
        out.push({ id: "holiday-event-" + h.holiday_id, title: h.holiday_title, start: h.holiday_start_date, end: addDays(last, 1), allDay: true, classNames: ["ox-holiday"], editable: false, extendedProps: { eventType: "Holiday" } });
      });
      return { events: out, days: days };
    }

    function renderEvents() {
      const built = buildEvents();
      calendar.batchRendering(function () {
        calendar.removeAllEvents();
        built.events.forEach(function (e) { calendar.addEvent(e); });
      });
      eventDays = built.days;
      if (mini) { mini.redraw(); }
      updateCounts();
    }

    // ---- Filter panel -------------------------------------------------------
    function chipHtml(group, value, label, icon, active) {
      return '<button type="button" class="ox-chip' + (active ? " active" : "") + '" data-group="' + group + '" data-value="' + esc(value) + '" aria-pressed="' + (active ? "true" : "false") + '">' +
        (icon ? '<i class="fas ' + icon + '"></i>' : "") + '<span class="ox-chip-label">' + esc(label) + '</span><span class="ox-count" data-count="' + group + ":" + esc(value) + '"></span></button>';
    }

    function buildFilterPanel() {
      const isMeeting = filters.kind === "Meeting";
      el("oxTypeFilter").hidden = !isMeeting;
      el("oxProductFilter").hidden = isMeeting;

      if (isMeeting) {
        let types = chipHtml("type", "All", "All types", "fa-list", filters.type === "All");
        meetType.forEach(function (t) {
          types += chipHtml("type", String(t.meeting_type_id), t.meeting_type_name, getIconClass(t.meeting_type_name).replace("fas ", ""), filters.type === String(t.meeting_type_id));
        });
        el("oxTypeChips").innerHTML = types;
        el("oxStatusChips").innerHTML =
          chipHtml("status", "All", "All", "fa-list", filters.status === "All") +
          chipHtml("status", "upcoming", "Upcoming", "fa-calendar-alt", filters.status === "upcoming") +
          chipHtml("status", "live", "Ongoing", "fa-spinner", filters.status === "live") +
          chipHtml("status", "Completed", "Completed", "fa-check-circle", filters.status === "Completed") +
          chipHtml("status", "needslog", "Needs time log", "fa-clock", filters.status === "needslog") +
          chipHtml("status", "cancelled", "Cancelled", "fa-ban", filters.status === "cancelled");
      } else {
        const names = [];
        sprintDetails.forEach(function (sp) { if (sp.status_name && names.indexOf(sp.status_name) === -1) { names.push(sp.status_name); } });
        el("oxStatusChips").innerHTML = chipHtml("status", "All", "All", "fa-list", filters.status === "All") +
          names.map(function (n) { return chipHtml("status", n, n.replace(/^Sprint /, ""), getIconClass(n).replace("fas ", ""), filters.status === n); }).join("");
        const sel = el("oxProductSelect");
        const seen = {};
        let opts = '<option value="All">All products</option>';
        sprintDetails.forEach(function (sp) {
          const key = String(sp.product_name || "").toLowerCase();
          if (key && !seen[key]) { seen[key] = true; opts += '<option value="' + esc(key) + '">' + esc(capitalizeFirstLetter(sp.product_name)) + "</option>"; }
        });
        sel.innerHTML = opts;
        sel.value = filters.product;
      }
      buildLegend();
      updateCounts();
    }

    function updateCounts() {
      const now = new Date();
      const counts = {};
      const bump = function (k) { counts[k] = (counts[k] || 0) + 1; };
      if (filters.kind === "Meeting") {
        meetingDetails.forEach(function (m) {
          const state = meetingState(m, now);
          bump("type:All"); bump("type:" + m.r_meeting_type_id);
          if (meetingMatches(m, now, true)) {
            bump("status:All"); bump("status:" + state);
            if (state === "done" || state === "needslog") { bump("status:Completed"); }
          }
        });
      } else {
        sprintDetails.forEach(function (sp) {
          if (sprintMatches(sp, true)) { bump("status:All"); bump("status:" + sp.status_name); }
        });
      }
      document.querySelectorAll(".ox-count").forEach(function (n) { n.textContent = counts[n.getAttribute("data-count")] || 0; });
    }

    function buildLegend() {
      const items = filters.kind === "Meeting"
        ? [["upcoming", "Upcoming"], ["live", "Ongoing"], ["done", "Completed"], ["needslog", "Needs time log"], ["cancelled", "Cancelled"]]
        : [["upcoming", "Planned"], ["live", "Running"], ["done", "Completed"]];
      el("oxLegend").innerHTML = items.map(function (i) { return '<span class="ox-legend-item"><i class="ox-dot ox-dot-' + i[0] + '"></i>' + i[1] + "</span>"; }).join("") +
        '<span class="ox-legend-item"><i class="ox-dot ox-dot-holiday"></i>Holiday</span>';
    }

    function setKind(kind) {
      filters.kind = kind;
      filters.status = "All";
      filters.type = "All";
      filters.product = "All";
      currentEventType = kind;
      document.querySelectorAll(".ox-seg-btn[data-kind]").forEach(function (b) {
        const on = b.getAttribute("data-kind") === kind;
        b.classList.toggle("active", on);
        b.setAttribute("aria-selected", on ? "true" : "false");
      });
      buildFilterPanel();
      renderEvents();
    }

    document.querySelector(".ox-cal-side").addEventListener("click", function (e) {
      const chip = e.target.closest(".ox-chip");
      if (chip) {
        filters[chip.getAttribute("data-group")] = chip.getAttribute("data-value");
        buildFilterPanel();
        renderEvents();
        return;
      }
      const seg = e.target.closest(".ox-seg-btn[data-kind]");
      if (seg && seg.getAttribute("data-kind") !== filters.kind) { setKind(seg.getAttribute("data-kind")); }
    });
    el("oxProductSelect").addEventListener("change", function () { filters.product = this.value; updateCounts(); renderEvents(); });

    // ---- Toolbar -----------------------------------------------------------
    el("oxToday").addEventListener("click", function () { calendar.today(); });
    el("oxPrev").addEventListener("click", function () { calendar.prev(); });
    el("oxNext").addEventListener("click", function () { calendar.next(); });
    document.querySelectorAll(".ox-seg-btn[data-view]").forEach(function (b) {
      b.addEventListener("click", function () { calendar.changeView(b.getAttribute("data-view")); });
    });

    function onDatesSet(arg) {
      const title = el("oxTitle");
      if (title) { title.textContent = calendar.view.title; }
      document.querySelectorAll(".ox-seg-btn[data-view]").forEach(function (b) {
        const on = b.getAttribute("data-view") === arg.view.type;
        b.classList.toggle("active", on);
        b.setAttribute("aria-selected", on ? "true" : "false");
      });
      try { window.localStorage.setItem(VIEW_KEY, arg.view.type); } catch (e) { /* ignore */ }
      if (mini) { mini.setDate(calendar.getDate(), false); mini.jumpToDate(calendar.getDate()); }
      // Time-grid views get a fixed, scrollable height; month/agenda grow with their content
      const wanted = arg.view.type.indexOf("timeGrid") === 0 ? Math.max(560, window.innerHeight - 300) : "auto";
      if (calendar.getOption("height") !== wanted) { setTimeout(function () { calendar.setOption("height", wanted); }, 0); }
    }

    // ---- Mini calendar -----------------------------------------------------
    if (window.flatpickr && el("oxMini")) {
      const miniInput = document.createElement("input");
      miniInput.type = "text";
      el("oxMini").appendChild(miniInput);
      mini = flatpickr(miniInput, {
        inline: true,
        monthSelectorType: "static",
        defaultDate: new Date(),
        disableMobile: true,
        onChange: function (sel) { if (sel[0]) { calendar.gotoDate(sel[0]); } },
        onDayCreate: function (d, s, fp, dayElem) { if (eventDays.has(ymd(dayElem.dateObj))) { dayElem.classList.add("has-event"); } },
      });
    }

    // ---- Scheduling from the calendar ---------------------------------------
    function openScheduleModal(dateStr, minutes) {
      document.getElementById("meeting_start_date").value = dateStr;
      document.getElementById("meeting_end_date").value = dateStr;
      $("#meetingModal").modal("show");
      document.getElementById("modalTitle").innerText = "Schedule meeting";
      $("#eventDetails, #timeLogSection, #helpdetails, #groupdetails").hide();
      $("#scheduleMeetingForm").show();
      document.getElementById("formOperation").value = "insert";
      if (minutes != null && minutes < 24 * 60) {
        // Clicking a time slot pre-fills the start time (snapped to 15 minutes)
        $("#meetingModal").one("shown.bs.modal", function () {
          const select = document.getElementById("startTimeSelect");
          select.value = String(Math.floor(minutes / 15) * 15);
          select.dispatchEvent(new Event("change", { bubbles: true }));
          // Default to a one-hour meeting so the end time is filled in straight away
          const hrs = document.getElementById("meeting_duration_hours");
          const mins = document.getElementById("meeting_duration_minutes");
          if (hrs && mins && !hrs.value && !mins.value) {
            hrs.value = "1";
            mins.value = "0";
            hrs.dispatchEvent(new Event("change", { bubbles: true }));
          }
        });
      }
    }

    function handleSlotClick(dateStr, minutes) {
      if (!permissions.scheduleMeeting || currentEventType !== "Meeting") { return; }
      const today = new Date();
      today.setHours(0, 0, 0, 0);
      if (new Date(dateStr + "T00:00:00") < today) {
        Swal.fire({ icon: "warning", text: "You can't schedule a meeting on a past date" });
        return;
      }
      $.ajax({ url: baseUrl + "meeting/checkHoliday", method: "GET", data: { date: dateStr } })
        .done(function (response) {
          const h = response && response.isHoliday && response.isHoliday.length ? response.isHoliday[0] : null;
          if (!h) { openScheduleModal(dateStr, minutes); return; }
          Swal.fire({
            icon: "question", title: "Holiday",
            text: h.holiday_title + " falls on this day. Schedule a meeting anyway?",
            showCancelButton: true, confirmButtonText: "Yes, schedule", cancelButtonText: "Cancel",
          }).then(function (r) { if (r.isConfirmed) { openScheduleModal(dateStr, minutes); } });
        })
        .fail(function () { openScheduleModal(dateStr, minutes); });
    }

    const newBtn = el("oxNewMeeting");
    if (newBtn) {
      newBtn.addEventListener("click", function () {
        if (filters.kind !== "Meeting") { setKind("Meeting"); }
        const now = new Date();
        const next = Math.ceil((now.getHours() * 60 + now.getMinutes() + 1) / 15) * 15;
        handleSlotClick(ymd(now), next < 24 * 60 ? next : null);
      });
    }

    // ---- Keyboard shortcuts (T today, M/W/D/A views, ←/→ navigate, ? help) ----
    document.addEventListener("keydown", function (e) {
      if (e.ctrlKey || e.metaKey || e.altKey) { return; }
      const t = e.target;
      if (t && (/^(INPUT|TEXTAREA|SELECT)$/.test(t.tagName) || t.isContentEditable)) { return; }
      if (document.body.classList.contains("modal-open") || document.querySelector(".swal2-container")) { return; }
      const k = e.key.toLowerCase();
      const views = { m: "dayGridMonth", w: "timeGridWeek", d: "timeGridDay", a: "listMonth" };
      if (k === "t") { calendar.today(); }
      else if (views[k]) { calendar.changeView(views[k]); }
      else if (e.key === "ArrowLeft") { calendar.prev(); }
      else if (e.key === "ArrowRight") { calendar.next(); }
      else if (e.key === "?") { showEventDetailsModal(); }
    });

    // ---- Go ----------------------------------------------------------------
    buildFilterPanel();
    calendar.render();
    renderEvents();
    // Statuses depend on "now": refresh every 5 minutes unless a dialog is open
    setInterval(function () { if (!document.body.classList.contains("modal-open")) { renderEvents(); } }, 300000);
  });

  /**
   * Extract numeric value from an event ID string.
   * @param {string} eventId - The event ID string.
   * @returns {number} - The numeric value extracted from the event ID.
   */
  function getNumericValue(eventId) {
    return parseInt(eventId.match(/\d+$/)[0], 10);
  }

  /**
   * Clears the content of the modal.
   */
  function clearModalContent() {
    document.getElementById("modalTitle").innerText = "";
    document.getElementById("eventTitle").innerText = "";
    document.getElementById("eventStart").innerText = "";
    document.getElementById("eventDescription").innerText = "";
    document.querySelector("#eventDescription").href = "#";
    document.getElementById("eventDetails").style.display = "none";
    document.getElementById("scheduleMeetingForm").style.display = "none";
    document.getElementById("groupdetails").style.display = "none";

    // Remove all buttons within event details section
    var buttons = document.querySelectorAll("#eventDetails button");
    buttons.forEach(function (button) {
      button.remove();
    });
    var buttons = document.querySelectorAll("#sprintDetails button");
    buttons.forEach(function (button) {
      button.remove();
    });
  }

  /**
   * Handles the edit meeting logic.
   * @param {Array} meeting - An array containing meeting details and members.
   */
  function editMeeting(meeting) {
    clearModalContent();
    document.getElementById("modalTitle").innerText = "Edit meeting";
    $("#timeLogSection, #eventDetails, #helpdetails, #groupdetails").hide();
    $("#scheduleMeetingForm").show();
    document.getElementById("formOperation").value = "update";
    document.getElementById("Schedulebutton").innerText = "Re-schedule";
    // Pre-fill the form with the meeting details
    document.getElementById("meeting_details_id").value =
      meeting[0].meeting_details_id;
    document.getElementById("meeting_title").value = meeting[0].meeting_title;
    document.getElementById("meeting_start_date").value =
      meeting[0].meeting_start_date.split("-").reverse().join("-");
    document.getElementById("meeting_end_date").value =
      meeting[0].meeting_end_date;
    document.getElementById("meeting_type").value =
      meeting[0].r_meeting_type_id;
    document.getElementById("product_div").value = meeting[0].r_product_id;
    $.ajax({
      url: baseUrl + "meeting/getMembersByProduct/" + meeting[0].r_product_id,
      type: "POST",
      dataType: "json",
      success: function (data) {
        let emailDropdown = $("#emailDropdown");
        emailDropdown.empty();
        $.each(data.members, function (index, member) {
          emailDropdown.append(
            `<label class='email-option' onclick="updateSelectedEmails('${member.first_name}')">${member.first_name}</label>`
          );
        });
      },
      error: function (xhr, status, error) {
        console.error("Error:", error);
      },
    });

    let selectSprint = document.getElementById("spId");
    var option = document.createElement("option");
    if (meeting[0].r_sprint_id) {
      option.text = meeting[0].sprint_name;
      option.value = meeting[0].r_sprint_id;
      selectSprint.appendChild(option);
      selectSprint.value = meeting[0].r_sprint_id;
    } else {
      option.text = "No Sprint";
      option.value = "";
      selectSprint.appendChild(option);
      selectSprint.value = "";
    }

    const prefillForm = (duration) => {
      const [hours, minutes] = duration.split(":");
      document.getElementById("meeting_duration_hours").value = parseInt(
        hours,
        10
      );
      document.getElementById("meeting_duration_minutes").value = parseInt(
        minutes,
        10
      );
    };

    var durations = meeting[0].meeting_duration;
    prefillForm(durations);
    var starttime = (document.getElementById("startTimeSelect").value =
      meeting[0].meeting_start_time);
    var convertStartTime = convertDurationToMinutes(starttime);
    document.getElementById("startTimeSelect").value = convertStartTime;
    var endtime = (document.getElementById("endTimeSelect").value =
      meeting[0].meeting_end_time);
    var convertEndTime = convertDurationToMinutes(endtime);
    document.getElementById("endTimeSelect").value = convertEndTime;
    document.getElementById("meeting_location").value =
      meeting[0].r_meeting_location_id;
    document.getElementById("meeting_link").value = meeting[0].meeting_link;
    document.getElementById("meeting_description").value =
      meeting[0].meeting_description;
    var meetingMembersString = meeting[1].meeting_members_names;
    var selectedEmails = meetingMembersString.split(",");

    // Get the input and container elements
    var selectedEmailsInput = document.getElementById("selectedEmailsInput");
    var selectedEmailsContainer = document.getElementById("selectedEmails");

    // Clear the container and set the input value
    selectedEmailsContainer.innerHTML = "";
    selectedEmailsInput.value = selectedEmails.join(",");

    // Loop through the selected emails and create labels for each
    selectedEmails.forEach(function (email) {
      var label = document.createElement("div");
      label.classList.add("selected-email");
      label.innerHTML = `
        <span>${email.trim()}</span>
        <span class="remove-email" onclick="removeEmail(this)">x</span>`;
      selectedEmailsContainer.appendChild(label);
    });
    var isRecurring = meeting[0].recurrance_meeting_id != null;
    var updateAsSeriesDiv = document.getElementById("updateAsSeriesDiv");
    if (isRecurring) {
      updateAsSeriesDiv.style.display = "block";
      document.getElementById("recurranceId").value =
        meeting[0].recurrance_meeting_id;
    } else {
      updateAsSeriesDiv.style.display = "none";
      document.getElementById("recurranceId").value = "";
    }
  }

  /**
   * Converts a duration string in the format "HH:MM:SS" to minutes.
   * @param {string} duration - The duration string.
   * @returns {number} - The total duration in minutes.
   */
  function convertDurationToMinutes(name) {
    var timeParts = name.split(":");
    var hours = parseInt(timeParts[0], 10);
    var minutes = parseInt(timeParts[1], 10);
    var seconds = parseInt(timeParts[2], 10);
    var totalMinutes = hours * 60 + minutes + seconds / 60;
    return totalMinutes;
  }

  /**
   * Removes an email from the selected emails list.
   * @param {HTMLElement} element - The element to be removed.
   */
  function removeEmail(element) {
    var email = element.previousElementSibling.innerText;
    var selectedEmailsInput = document.getElementById("selectedEmailsInput");
    var selectedEmails = selectedEmailsInput.value.split(",");
    selectedEmails = selectedEmails.filter(function (e) {
      // Remove the email from the array
      return e !== email;
    });
    selectedEmailsInput.value = selectedEmails.join(",");
    element.parentElement.remove();
  }

  /**
   * Form validation using Bootstrap's custom validation styles.
   * Immediately invoked function to add validation to forms.
   */
  $(document).ready(function () {
    var initialFormState = $("#meetingForm").html(); // Store the initial state of the form
    function resetForm(formId, type) {
      var form = $("#" + formId)[0];
      form.reset();
      form.classList.remove("was-validated");
      $(form).find(".invalid-feedback").hide();
      $(form).find(".is-invalid").removeClass("is-invalid");

      if (formId === "meetingForm" && type != "Edit meeting") {
        $("#meetingForm").html(initialFormState);
        $("#meeting_type").val("");
        $("#selectedEmails").empty();
      } else if (formId === "meetingForm" && type == "Edit meeting") {
        $("#meeting_type").val("");
        $("#selectedEmails").empty();
      }
    }

    $("#meetingModal").on("hidden.bs.modal", function (e) {
      var modalTitle = document.getElementById("modalTitle").innerText;
      if (
        modalTitle === "Edit meeting" ||
        modalTitle === "Schedule meeting" ||
        modalTitle === "Create group for meeting" ||
        modalTitle === "Edit group for meeting" ||
        modalTitle === "Delete group for meeting"
      ) {
        resetForm("meetingForm", modalTitle);
        resetForm("groupForm", modalTitle);
        $("#memberTableBody").empty();

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
          "#recurrence_meeting_container,#sprint_start_date_container,#sprint_end_date_container,#sprint_div"
        ).show();
        $(
          "#backlog-container,#epic-container,#userstory-container,#meeting_start_date_container,#meeting_end_date_container"
        ).hide();
      } else if (meetingType === "2") {
        $(
          "#recurrence_meeting_container,#sprint_start_date_container,#sprint_end_date_container"
        ).hide();
        $(
          "#backlog-container,#epic-container,#userstory-container,#meeting_start_date_container,#meeting_end_date_container"
        ).show();
      } else if (meetingType === "4" || meetingType === "5") {
        $(
          "#recurrence_meeting_container,#sprint_start_date_container,#sprint_end_date_container,#backlog-container,#epic-container,#userstory-container"
        ).hide();
        $(
          "#meeting_start_date_container, #meeting_end_date_container, #sprint_div"
        ).show();
      } else {
        $(
          "#recurrence_meeting_container,#sprint_start_date_container,#sprint_end_date_container,#backlog-container,#epic-container,#userstory-container"
        ).hide();
        $("#meeting_start_date_container, #meeting_end_date_container").show();
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

  //updated by Ruban
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
    if (event) {
      if (event.key === "ArrowRight" || event.key === "Tab") {
        autofillInput(filter);
      }
      // Check if Enter key is pressed
      if (event.key === "Enter") {
        addFirstMatchingEmail(filter);
      }
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
   * Removes an email from the selected list.
   * @param {HTMLElement} element - The element to be removed.
   * @param {string} email - The email to be removed from the selected list.
   */
  function removeSelectedEmail(element, email) {
    while (!element.classList.contains("selected-email")) {
      element = element.parentNode;
    }
    element.remove();

    // Remove email from selected emails input
    var selectedEmails = selectedEmailsInput.value.split(",");
    var index = selectedEmails.indexOf(email);
    if (index !== -1) {
      selectedEmails.splice(index, 1);
      selectedEmailsInput.value = selectedEmails.join(",");
    }
  }

  /**
   * Adds keyup and keydown event listeners to the team input field.
   * Filters emails and prevents form submission on Enter key press.
   */
  document
    .getElementById("teamInput")
    .addEventListener("keyup", function (event) {
      filterEmails(event);
    });

  document
    .getElementById("teamInput")
    .addEventListener("keydown", function (event) {
      if (event.key === "Enter") {
        event.preventDefault(); // Prevent form submission on Enter key press
      }
    });

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
   * Capitalizes the first letter of each word in the given string.
   * @param {string} string - The string to be capitalized.
   * @returns {string} - The capitalized string.
   */
  function capitalizeFirstLetter(string) {
    return string.toLowerCase().replace(/\b\w/g, (c) => c.toUpperCase());
  }
  /**
   * Filters and refetches events from the calendar.
   */
  function filterEvents() {
    calendar.refetchEvents();
  }
  /**
   * Formats a date-time string to a 12-hour time format.
   * @param {string} dateTimeString - The date-time string to be formatted.
   * @returns {string} - The formatted time string.
   */
  function formatTime(dateTimeString) {
    const date = new Date(dateTimeString);
    return date.toLocaleTimeString("en-US", {
      hour: "numeric",
      minute: "numeric",
      hour12: true,
    });
  }

  /**
   * Calculates duration between two time strings in minutes.
   * @param {string} startTimeString - Start time in "HH:MM:SS" format.
   * @param {string} endTimeString - End time in "HH:MM:SS" format.
   * @returns {number} - The duration in minutes.
   */
  function calculateDurationInMinutes(startTimeString, endTimeString) {
    const start = new Date(`1970-01-01T${startTimeString}Z`);
    const end = new Date(`1970-01-01T${endTimeString}Z`);
    const durationMs = end - start;
    return Math.round(durationMs / (1000 * 60));
  }
  /**
   * Converts time from hh:mm:ss format to h:mm AM/PM format.
   * @param {string} timeString - The time string in hh:mm:ss format.
   * @returns {string} - The formatted time string in h:mm AM/PM format.
   */
  function formatToAmPm(timeString) {
    // Split the time string into hours, minutes, and seconds
    const [hours, minutes, seconds] = timeString.split(":").map(Number);

    // Determine AM or PM period
    const period = hours >= 12 ? "PM" : "AM";

    // Convert hours to 12-hour format
    const formattedHours = hours % 12 === 0 ? 12 : hours % 12;

    // Format minutes with leading zero if necessary
    const formattedMinutes = minutes < 10 ? "0" + minutes : minutes;

    // Return formatted time string
    return `${formattedHours}:${formattedMinutes} ${period}`;
  }

  /**
   * Converts minutes to fractional hours for RedMinE LOG.
   *
   * This function takes a duration in minutes and converts it to fractional hours.
   * For example, 60 minutes will be converted to 1, 30 minutes will be converted to 0.5,
   * 45 minutes will be converted to 0.75, and 120 minutes will be converted to 2.
   *
   * @param {number} minutes - The duration in minutes.
   * @returns {number} - The duration in fractional hours.
   */

  function convertMinutesToFractionalHours(minutes) {
    return minutes / 60;
  }

  function combineMembersAndIds(names, ids) {
    if (names.length !== ids.length) {
      console.error("Names and IDs arrays must have the same length.");
      return [];
    }

    return names.map((name, index) => ({
      meeting_members_names: name,
      meeting_members_id: ids[index],
    }));
  }

  function showEventDetailsModal() {
    $("#meetingModal").modal("show");
    $(
      "#scheduleMeetingForm, #eventDetails, #timeLogSection, #helpdetails, #groupdetails"
    ).hide();
    $("#helpdetails").show();
    document.getElementById("modalTitle").innerText = "How to use the calendar";
  }

  function getIconClass(status) {
    switch (status) {
      case "All":
        return "fas fa-list";
      case "Completed":
        return "fas fa-check-circle";
      case "Ongoing":
        return "fas fa-spinner";
      case "Upcoming":
        return "fas fa-calendar-alt";
      case "Sprint Completed":
        return "fas fa-check-circle";
      case "Sprint Running":
        return "fas fa-spinner";
      case "Sprint Planned":
        return "fas fa-calendar-alt";
      case "On Hold":
        return "fas fa-pause-circle";
      case "General":
        return "fas fa-users";
      case "Brainstorming":
        return "fas fa-lightbulb";
      case "Daily Scrum":
        return "fas fa-sync-alt";
      case "Sprint Review":
        return "fas fa-check";
      case "Sprint Retrospective":
        return "fas fa-undo";
      default:
        return "fas fa-circle";
    }
  }

  function formatDuration(meeting_start_time, meeting_end_time) {
    // Convert times to Date objects for easy calculation
    const start = new Date(`1970-01-01T${meeting_start_time}`);
    const end = new Date(`1970-01-01T${meeting_end_time}`);

    // Calculate the difference in milliseconds
    const diff = end - start;

    // Convert milliseconds to hours, minutes, and seconds
    const hours = Math.floor(diff / 3600000);
    const minutes = Math.floor((diff % 3600000) / 60000);
    const seconds = Math.floor((diff % 60000) / 1000);

    if (hours > 0) {
      if (minutes > 0) {
        return `${hours} hour${hours > 1 ? "s" : ""} ${minutes} minute${
          minutes > 1 ? "s" : ""
        }`;
      } else {
        return `${hours} hour${hours > 1 ? "s" : ""}`;
      }
    } else if (minutes > 0) {
      return `${minutes} minute${minutes > 1 ? "s" : ""}`;
    } else if (seconds > 0) {
      return `${seconds} second${seconds > 1 ? "s" : ""}`;
    } else {
      return "0 minutes";
    }
  }

  $(document).ready(function () {
    $("#meetingForm").on("submit", function (e) {
      e.preventDefault();
      let formData = $(this).serialize();
      formData += "&Schedulebutton=1";
      let operation = $("#formOperation").val();
      let url =
        operation === "insert"
          ? baseUrl + "meeting/scheduleMeeting"
          : baseUrl + "meeting/updateMeeting";
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
            } else if (response.members) {
              Swal.fire({
                title: "Warning",
                text: "Select a group or add a member to schedule",
                icon: "warning",
                confirmButtonText: "OK",
              });
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
                title: "Error!",
                text:
                  response.message ||
                  (operation === "insert"
                    ? "Failed to Schedule Meeting"
                    : "Failed to Update Meeting"),
                icon: "error",
                confirmButtonText: "OK",
              });
            }
          },
          error: function () {
            // Handle AJAX error
            Swal.fire({
              title: "Error",
              text: "An error occurred while Scheduling Meeting",
              icon: "error",
              confirmButtonText: "OK",
            });
          },
        });
      }
    });
  });

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
    flatpickr(
      "#meeting_start_date,#meeting_end_date,#sprint_start_date,#sprint_end_date",
      {
        minDate: todayDate,
        dateFormat: "Y-m-d",
      }
    );
  });

  const rowsPerPage = 8;
  let currentPage = 1;
  const selectedMembers = new Set();
  function updateSelectedMembers() {
    // Get all visible checkboxes within the table body
    const pageRows = $("#memberTableBody tr:visible .memberCheckbox");

    // Iterate over each visible checkbox
    pageRows.each(function () {
      const memberId = $(this).val(); // Get the value of the checkbox (member ID)

      // Update the global selectedMembers set based on the checkbox status
      if ($(this).is(":checked")) {
        selectedMembers.add(memberId); // Add the member ID to the selected set
      } else {
        selectedMembers.delete(memberId); // Remove the member ID from the selected set
      }
    });

    // Update the hidden input with the current selected member IDs from all pages
    $("#selectedMembers").val(Array.from(selectedMembers).join(","));
  }
  function updatePaginationButtons(totalFilteredRows) {
    const totalPages = Math.ceil(totalFilteredRows / rowsPerPage);
    const paginationControls = document.getElementById("paginationControls");
    paginationControls.innerHTML = "";
    const prevButton = document.createElement("button");
    prevButton.className = "btn btn-primary p-1 m-1";
    prevButton.style.borderRadius = "50%";
    prevButton.style.height = "25px";
    prevButton.style.width = "25px";
    prevButton.innerHTML =
      "<i class='fas fa-angle-left' style='font-size:24px; display:inline-block;'></i>";
    prevButton.disabled = currentPage <= 1;
    prevButton.addEventListener("click", function () {
      changePage(currentPage - 1);
    });
    const nextButton = document.createElement("button");
    nextButton.className = "btn btn-primary p-1 m-1";
    nextButton.style.borderRadius = "50%";
    nextButton.style.height = "25px";
    nextButton.style.width = "25px";
    nextButton.innerHTML =
      "<i class='fas fa-angle-right' style='font-size:24px'></i>";
    nextButton.disabled = currentPage >= totalPages;
    nextButton.addEventListener("click", function () {
      changePage(currentPage + 1);
    });
    paginationControls.appendChild(prevButton);
    paginationControls.appendChild(nextButton);
  }

  function updateSelectAllCheckbox() {
    const totalCheckboxes = $(
      "#memberTableBody tr:visible .memberCheckbox"
    ).length;
    const checkedCheckboxes = $(
      "#memberTableBody tr:visible .memberCheckbox:checked"
    ).length;
    $("#selectAllMembers").prop(
      "checked",
      totalCheckboxes > 0 && totalCheckboxes === checkedCheckboxes
    );
  }

  $("#memberSearch").on("keyup", function () {
    currentPage = 1; // Reset to first page on new search
    updateDisplayedMembers();
  });

  $("#selectAllMembers").on("click", function () {
    const isChecked = $(this).prop("checked");
    $("#memberTableBody tr:visible .memberCheckbox").prop("checked", isChecked);
    $("#memberTableBody tr:visible .memberCheckbox").each(function () {
      const memberId = $(this).val();
      if (isChecked) {
        selectedMembers.add(memberId);
      } else {
        selectedMembers.delete(memberId);
      }
    });
    $("#selectedMembers").val(Array.from(selectedMembers).join(","));
  });

  $("#memberTableBody").on("change", ".memberCheckbox", function () {
    updateSelectedMembers();
    updateDisplayedMembers();
  });

  $("#memberTableBody").on("click", "tr", function (event) {
    if (!$(event.target).is(".memberCheckbox")) {
      const checkbox = $(this).find(".memberCheckbox");
      checkbox.prop("checked", !checkbox.prop("checked")).trigger("change");
    }
  });

  $("#selectedMembersDisplay").on("click", ".btn-close", function () {
    const id = $(this).data("id");
    $("#memberTableBody .memberCheckbox[value='" + id + "']")
      .prop("checked", false)
      .trigger("change");
    selectedMembers.delete(id);
    $("#selectedMembers").val(Array.from(selectedMembers).join(","));
    updateDisplayedMembers();
  });

  $(document).ready(function () {
    updateDisplayedMembers();
  });

  function changePage(newPage) {
    currentPage = newPage;
    updateDisplayedMembers();
  }

  function createGroup() {
    clearModalContent();
    $("#meetingModal").modal("show");
    $(
      "#scheduleMeetingForm, #eventDetails, #timeLogSection, #helpdetails, #editGroup"
    ).hide();
    $("#groupdetails, #productDetails").show();
    document.getElementById("modalTitle").innerText =
      "Create group for meeting";
    document.getElementById("groupbutton").innerText = "Create Group";
    const productData = JSON.parse(
      document.getElementById("productData").value
    );
    let options = productData
      .map(
        (product) =>
          `<option value="${product.external_project_id}">${product.product_name}</option>`
      )
      .join("");
    document.getElementById("productDetails").innerHTML = `
        <label for="productNames" class="form-label">Product</label>
        <select class="form-select" id="productNames" name="r_product_id" required>
            <option value="" disabled selected>Select the Product</option>
            ${options}
        </select>
        <div class="valid-feedback">Looks good!</div>
        <div class="invalid-feedback">Please choose a product.</div>
    `;

    document.getElementById("showDetails").innerHTML = `
        <label for="meeting_team_name" class="form-label">Group name</label>
        <input type="text" class="form-control" id="group_name" name="meeting_team_name" placeholder="Enter the Group Name" required>
        <div class="valid-feedback">Looks good!</div>
        <div class="invalid-feedback">Please provide a group name.</div>
    `;

    document.getElementById("memberDetails").style.display = "block";
    $(document).ready(function () {
      $("#productNames").change(function () {
        let productId = $(this).val();
        $("#groupProductId").val(productId);
        if (productId) {
          $("#groupIdByName").val(productId);
          $.ajax({
            url: baseUrl + "meeting/getMembersByProduct/" + productId,
            type: "POST",
            dataType: "json",
            success: function (data) {
              populateMemberTable(data);
              updateDisplayedMembers();
            },
            error: function (xhr, status, error) {
              console.error("Error:", error);
            },
          });
        }
      });
    });

    function populateMemberTable(team) {
      $("#memberTableBody").empty();
      team.members.forEach((member) => {
        $("#memberTableBody").append(`
                <tr>
                    <td><input type="checkbox" class="memberCheckbox" value="${member.external_employee_id}"></td>
                    <td>${member.first_name}</td>
                </tr>
            `);
      });
      let selectedMembers = team.members
        .filter((member) => member.isSelected)
        .map((member) => member.r_external_employee_id);
      $("#selectedMembers").val(selectedMembers.join(","));
    }
  }

  function editGroup() {
    clearModalContent();
    $("#meetingModal").modal("show");
    $(
      "#scheduleMeetingForm, #eventDetails, #timeLogSection, #helpdetails "
    ).hide();
    $("#groupdetails, #editGroup").show();
    document.getElementById("modalTitle").innerText = "Edit group for meeting";
    document.getElementById("groupbutton").innerText = "Edit Group";
    const groupData = JSON.parse(document.getElementById("groupData").value);
    let options = groupData
      .map(
        (group) =>
          `<option value="${group.meeting_team_id}">${group.meeting_team_name}</option>`
      )
      .join("");
    document.getElementById("productDetails").style.display = "none";
    document.getElementById("showDetails").innerHTML = `
        <div id="editGroupDiv">
            <label for="meeting_team_id" class="form-label">Group Name</label>
            <select class="form-select" id="groupSelect" name="meeting_team_id" required>
                <option value="">Select the Group Name</option>
                ${options}
            </select>
            <div class="valid-feedback">Looks good!</div>
            <div class="invalid-feedback">Please choose a Group Name.</div>
        </div>
    `;
    document.getElementById("memberDetails").style.display = "block";

    $("#groupSelect").change(function () {
      let groupId = $(this).val();
      if (groupId) {
        $("#groupIdByName").val(groupId);
        $.ajax({
          url: baseUrl + "meeting/getTeamDetailsById/" + groupId,
          type: "POST",
          dataType: "json",
          success: function (data) {
            populateMemberTable(data);
          },
          error: function (xhr, status, error) {
            console.error("Error:", error);
          },
        });
      }
    });
  }

  let allSelectedIds = new Set(); // Use Set to store unique selected IDs
  let totalMembers = []; // Declare this globally to avoid conflicts

  function populateMemberTable(team) {
    const selectedIdsFromInput = $("#selectedMembers")
      .val()
      .split(",")
      .filter(Boolean);
    selectedIdsFromInput.forEach((id) => allSelectedIds.add(id));

    const newSelectedIds = team.members.map(
      (member) => member.r_external_employee_id
    );
    newSelectedIds.forEach((id) => allSelectedIds.add(id));
    updateHiddenInput();

    const productIds = [
      ...new Set(team.members.map((member) => member.r_product_id)),
    ];
    if (productIds.length > 0) {
      $("#groupProductId").val(productIds[0]);
    }

    function handleAjaxResponse(data) {
      const newMembers = data.members.filter(
        (member) =>
          !totalMembers.some(
            (existingMember) =>
              existingMember.external_employee_id ===
              member.external_employee_id
          )
      );
      totalMembers = [...totalMembers, ...newMembers];
      populateTableRows();
    }

    function populateTableRows() {
      $("#memberTableBody").empty();
      totalMembers.forEach((productMember) => {
        const isChecked = allSelectedIds.has(
          productMember.external_employee_id
        );
        const $row = $(`
                <tr>
                    <td><input type="checkbox" class="memberCheckbox" value="${productMember.external_employee_id}"></td>
                    <td>${productMember.first_name}</td>
                </tr>
            `);
        $row.find(".memberCheckbox").prop("checked", isChecked);
        $("#memberTableBody").append($row);
      });
      updateDisplayedMembers();
    }

    $(document)
      .off("change", ".memberCheckbox")
      .on("change", ".memberCheckbox", function () {
        const memberId = $(this).val();
        if ($(this).is(":checked")) {
          allSelectedIds.add(memberId);
        } else {
          allSelectedIds.delete(memberId);
        }
        updateHiddenInput();
      });

    document.getElementById("editGroup").innerHTML = `
        <div id="editGroupDiv">
            <label for="meeting_team_name" class="form-label">Edit Group Name</label>
            <input type="text" class="form-control" id="editGroupName" name="meeting_team_name" value="${
              team.members[0]?.meeting_team_name || ""
            }" required>
            <div class="valid-feedback">Looks good!</div>
            <div class="invalid-feedback">Please provide a Group Name.</div>
        </div>`;

    productIds.forEach((productId) => {
      $.ajax({
        url: `${baseUrl}meeting/getMembersByProduct/${productId}`,
        type: "POST",
        dataType: "json",
        async: false,
        success: handleAjaxResponse,
        error: function (xhr, status, error) {
          console.error("Error:", error);
        },
      });
    });
  }

  function updateHiddenInput() {
    $("#selectedMembers").val([...allSelectedIds].join(","));
  }

  function updateDisplayedMembers() {
    const searchValue = $("#memberSearch").val().toLowerCase();
    const rows = $("#memberTableBody tr");
    const filteredRows = rows.filter(function () {
      return $(this).text().toLowerCase().indexOf(searchValue) > -1;
    });
    const start = (currentPage - 1) * rowsPerPage;
    const end = start + rowsPerPage;
    rows.hide();
    filteredRows.each(function (index) {
      if (index >= start && index < end) {
        $(this).show();
      }
    });
    updateSelectAllCheckbox();
    updatePaginationButtons(filteredRows.length);
  }
  $(document).ready(function () {
    $("#groupForm").on("submit", function (event) {
      event.preventDefault();
      const formData = new FormData(this);
      let buttonname = document.getElementById("groupbutton").innerText;
      let name = "";
      if (buttonname == "Create Group") {
        name = "createGroupDetails";
        submitForm(name, formData);
      } else if (buttonname == "Edit Group") {
        updateHiddenInput();
        name = "editGroupDetails";
        submitForm(name, formData);
      } else if (buttonname == "Delete Group") {
        Swal.fire({
          title: "Are you sure?",
          text: "You won't be able to revert this!",
          icon: "warning",
          showCancelButton: true,
          confirmButtonColor: "#3085d6",
          cancelButtonColor: "#d33",
          confirmButtonText: "Yes, delete it!",
        }).then((result) => {
          if (result.isConfirmed) {
            name = "deleteGroupDetails";
            submitForm(name, formData);
          }
        });
      }
    });

    function submitForm(actionName, formData) {
      let url = baseUrl + "meeting/" + actionName;
      $.ajax({
        url: url,
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,
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
              confirmButtonColor: "#117a45",
              confirmButtonText: "OK",
            });
          } else if (response.update) {
            Swal.fire({
              title: "Success!",
              text: "Group Updated Successfully",
              icon: "success",
              confirmButtonColor: "#117a45",
              confirmButtonText: "OK",
            }).then((result) => {
              if (result.isConfirmed) {
                window.location.href = window.location.href;
              }
            });
          } else if (response.deleted === true) {
            Swal.fire({
              title: "Success!",
              text: "Group Deleted Successfully",
              icon: "success",
              confirmButtonColor: "#117a45",
              confirmButtonText: "OK",
            }).then((result) => {
              if (result.isConfirmed) {
                window.location.href = window.location.href;
              }
            });
          } else if (response.deleted === false) {
            Swal.fire({
              title: "Warning!",
              text: "Failed to Delete a Group",
              icon: "warning",
              confirmButtonColor: "#117a45",
              confirmButtonText: "OK",
            });
          } else if (response.empty === true) {
            Swal.fire({
              title: "Warning!",
              text: "Add members to the group",
              icon: "warning",
              confirmButtonColor: "#117a45",
              confirmButtonText: "OK",
            });
          } else if (response.success) {
            Swal.fire({
              title: "Success!",
              text: "Group Created Successfully",
              icon: "success",
              confirmButtonColor: "#117a45",
              confirmButtonText: "OK",
            }).then((result) => {
              if (result.isConfirmed) {
                window.location.href = window.location.href;
              }
            });
          } else {
            Swal.fire({
              title: "Warning",
              text: "Failed to create group for meeting",
              icon: "warning",
              confirmButtonColor: "#117a45",
              confirmButtonText: "OK",
            });
          }
        },
        error: function (xhr, status, error) {
          Swal.fire({
            title: "Error",
            text: "An error occurred while creating the group",
            icon: "error",
            confirmButtonColor: "#117a45",
            confirmButtonText: "OK",
          });
        },
      });
    }
    $("#selectAllMembers").on("change", function () {
      $(".memberCheckbox").prop("checked", this.checked);
    });
  });

  function DeleteGroup() {
    clearModalContent();
    $("#meetingModal").modal("show");
    $(
      "#scheduleMeetingForm, #eventDetails, #timeLogSection, #helpdetails, #editGroup, #memberDetails"
    ).hide();
    $("#groupdetails").show();
    document.getElementById("modalTitle").innerText =
      "Delete group for meeting";
    document.getElementById("groupbutton").innerText = "Delete Group";
    const groupData = JSON.parse(document.getElementById("groupData").value);
    let options = groupData
      .map(
        (group) =>
          `<option value="${group.meeting_team_id}">${group.meeting_team_name}</option>`
      )
      .join("");
    document.getElementById("productDetails").style.display = "none";
    document.getElementById("showDetails").innerHTML = `
            <div id="editGroupDiv">
                <label for="groupSelect" class="form-label">Group Name</label>
                <select class="form-select" id="groupSelect" name="groupSelect" required>
                    <option value="">Select the Group Name</option>
                    ${options}
                </select>
                <div class="valid-feedback">Looks good!</div>
                <div class="invalid-feedback">Please choose a meeting location.</div>
            </div>
    `;
  }

  $(document).ready(function () {
    $("#timeLogForm").on("submit", function (e) {
      e.preventDefault();
      let formData = $(this).serialize();
      formData += "&timelogbutton=1";
      let url = baseUrl + "meeting/logMeetingTimes";
      $.ajax({
        url: url,
        method: "POST",
        data: formData,
        dataType: "json",
        success: function (response) {
          if (response.success) {
            Swal.fire({
              title: "Success!",
              text: "Time Logged Successfully",
              icon: "success",
              confirmButtonText: "OK",
            }).then((result) => {
              if (result.isConfirmed) {
                window.location.href = window.location.href;
              }
            });
          }
        },
      });
    });

    // sprint input visible based on meeting type
    $(document).ready(function () {
      $("#meeting_type").on("change", function () {
        var selectedValue = $(this).find("option:selected").text();
        if (
          selectedValue === "Daily Scrum" ||
          selectedValue === "Sprint Review" ||
          selectedValue === "Sprint Retrospective"
        ) {
          $("#sprint_div").css("display", "block");
        } else {
          $("#sprint_div").css("display", "none");
        }
      });
    });

    $(document).ready(function () {
      $("#product_div").change(function () {
        let productId = $(this).val();
        $.ajax({
          url: baseUrl + "meeting/sprintByProduct/" + productId,
          type: "POST",
          dataType: "json",
          success: function (data) {
            let sprintSelect = $("#spId");
            sprintSelect.empty();
            if (data.length > 0) {
              sprintSelect.append(
                '<option value="" disabled selected>Select the Sprint</option>'
              );
              $.each(data, function (index, sprint) {
                sprintSelect.append(
                  `<option value="${sprint.sprint_id}">${sprint.sprint_name}</option>`
                );
              });
            } else {
              sprintSelect.append(
                '<option value="" disabled selected style="color: red;">No sprints available for the selected product</option>'
              );
            }
            $("#sprint_div").css("visibility", "visible");
          },
          error: function (xhr, status, error) {
            console.error("Error:", error);
          },
        });
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

  $("#product_div").change(function () {
    let productId = $(this).val();
    $.ajax({
      url: baseUrl + "meeting/backlogByProduct/" + productId,
      type: "POST",
      dataType: "json",
      success: function (data) {
        let backlogSelect = $("#backlog");
        backlogSelect.empty();
        if (data.length === 0) {
          backlogSelect.append(
            `<option class="text-danger" disabled selected>No backlog</option>`
          );
        } else {
          backlogSelect.append(
            `<option disabled selected>Select the backlog</option>`
          );
          $.each(data, function (index, backlog) {
            backlogSelect.append(
              `<option value="${backlog.backlog_item_id}">${backlog.backlog_item_name}</option>`
            );
          });
        }
      },
      error: function (xhr, status, error) {
        console.error("Error:", error);
      },
    });
  });

  $(document).ready(function () {
    $("#backlog").change(function () {
      let backlogId = $(this).val();
      $.ajax({
        url: baseUrl + "meeting/epicByBacklog/" + backlogId,
        type: "POST",
        dataType: "json",
        success: function (data) {
          let epicContainer = $("#epic-checkbox-container");
          epicContainer.empty();
          if (data.length === 0) {
            epicContainer.append(
              `<label class="dropdown-item px-3 text-danger">No epics available.</label>`
            );
          } else {
            $.each(data, function (index, backlog) {
              epicContainer.append(
                `<label class="dropdown-item px-3">
                                  <input type="checkbox" class="epic-checkbox"
                                  value="${backlog.epic_id}" data-description="${backlog.epic_description}"> ${backlog.epic_description}
                                </label>`
              );
            });
          }
          // Add event listener for checkboxes
          $(".epic-checkbox").on("change", function () {
            $(this)
              .closest("label")
              .toggleClass("highlighted", $(this).is(":checked"));
          });
        },
        error: function (xhr, status, error) {
          console.error("Error:", error);
        },
      });
    });
  });

  $("#backlog").change(function () {
    let backlogId = $(this).val();
    $.ajax({
      url: baseUrl + "meeting/epicByBacklog/" + backlogId,
      type: "POST",
      dataType: "json",
      success: function (data) {
        let epicSelect = $("#epic-checkbox");
        epicSelect.empty();
        epicSelect.append(
          '<option value="" disabled selected>Select the Epic</option>'
        );
        $.each(data, function (index, backlog) {
          epicSelect.append(`
                    <input type="checkbox" class="epic-checkbox"
                    value="${backlog.epic_id}" data-description="${backlog.epic_description}">${backlog.epic_description}<br>`);
        });
      },
      error: function (xhr, status, error) {
        console.error("Error:", error);
      },
    });
  });

  document.addEventListener("DOMContentLoaded", function () {
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
            url: baseUrl + "backlog/userstoryByEpic/" + epicId,
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

  //to Fetch the user based on product
  $(document).ready(function () {
    $("#product_div").change(function () {
      let productId = $(this).val();
      $.ajax({
        url: baseUrl + "meeting/getMembersByProduct/" + productId,
        type: "POST",
        dataType: "json",
        success: function (data) {
          let emailDropdown = $("#emailDropdown");
          emailDropdown.empty();
          $.each(data.members, function (index, member) {
            emailDropdown.append(
              `<label class='email-option' onclick="updateSelectedEmails('${member.first_name}')">${member.first_name}</label>`
            );
          });
        },
        error: function (xhr, status, error) {
          console.error("Error:", error);
        },
      });
    });
  });

  function convertDateFormat(dateStr) {
    // Split the date string into components
    let parts = dateStr.split("-");
    let day = parts[0];
    let month = parts[1];
    let year = parts[2];

    // Array of month names
    let monthNames = [
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

    // Convert the month number to month name
    let monthName = monthNames[parseInt(month) - 1];

    // Construct the new date format
    return `${day}, ${monthName} ${year}`;
  }
  $(document).ready(function () {
    $("#spId").on("change", function () {
      let sprintId = $(this).val();
      $.ajax({
        url: baseUrl + "meeting/getSprintMembersById/" + sprintId,
        type: "POST",
        dataType: "json",
        success: function (data) {
          var selectedEmailsInput = document.getElementById(
            "selectedEmailsInput"
          );
          var selectedEmailsContainer =
            document.getElementById("selectedEmails");
          // Clear the container and set the input value
          selectedEmailsContainer.innerHTML = "";
          var selectedEmails = [];
          $.each(data.members, function (index, member) {
            selectedEmails.push(member.first_name.split(","));
            var label = document.createElement("div");
            label.classList.add("selected-email");
            label.innerHTML = `
                <span>${member.first_name.trim()}</span>
                <span class="remove-email" onclick="removeEmail(this)">x</span>`;
            selectedEmailsContainer.appendChild(label);
            selectedEmailsInput.value = selectedEmails.join(",");
          });
        },
        error: function (xhr, status, error) {
          console.error("Error:", error);
        },
      });
    });
  });
  document.addEventListener("DOMContentLoaded", function () {
    const labels = document.querySelectorAll("label");
    labels.forEach((label) => {
      const inputId = label.getAttribute("for");
      const inputElement = document.getElementById(inputId);
      if (inputElement && !inputElement.readOnly) {
        label.classList.add("label-with-asterisk");
      }
    });
  });
}
