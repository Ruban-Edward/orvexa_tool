/**
 * product user setting page
 */
$(document).ready(function () {
  $("#productSelect").change(function () {
    let productId = $(this).val();
    $.ajax({
      url: baseUrl + "admin/getMembersByProductList/" + productId,
      type: "POST",
      dataType: "json",
      success: function (data) {
        // Clear the existing options
        $("#productUserMemberSelect").empty();

        // Add the default "Select the owner" option
        $("#productUserMemberSelect").append(
          '<option value="" disabled selected>Select the owner</option>'
        );

        // Check if an owner is already set
        let currentOwnerId = data.currentOwnerId || null;

        // Loop through the members and add each one as an option
        data.members.forEach(function (member) {
          let isSelected =
            member.external_employee_id == currentOwnerId ? "selected" : "";
          $("#productUserMemberSelect").append(
            '<option value="' +
              member.external_employee_id +
              '" ' +
              isSelected +
              ">" +
              member.first_name +
              "</option>"
          );
        });
      },
      error: function (xhr, status, error) {
        console.error("Error:", error);
      },
    });
  });
});

/**
 * gets the poker value from the table when the poker button is clicked
 */
$(document).ready(function () {
  // Function to fetch poker limit
  function fetchPokerLimit() {
    $.ajax({
      url: baseUrl + "admin/getPokerLimit",
      type: "GET",
      dataType: "json",
      success: function (response) {
        if (response.success) {
          // Set the fetched value to the input field
          $("#poker").val(response.data.poker_limit);
        } else {
          console.log(response.message);
        }
      },
      error: function (xhr, status, error) {
        console.error("AJAX Error:", status, error);
      },
    });
  }

  // Fetch poker limit on page load
  fetchPokerLimit();

  //button to fetch poker limit when clicked
  $(".cls-btn").click(function () {
    fetchPokerLimit();
  });
});

/**
 * handles the which div should show on button click and tick icon
 */
$(document).ready(function () {
  // Function to show the correct div based on the stored value
  function showDiv(divId) {
    // Hide all divs
    $(
      "#productUserDiv, #pokerConfigDiv, #addRoleDiv, #holidayDiv, #tShirtDiv, #customerDiv"
    ).hide();

    // Show the selected div
    $(`#${divId}`).show();

    // Remove tick from all buttons
    $(".cls-btn .cls-settings").removeClass("show-tick");

    // Add the show-tick class to the tick icon inside the corresponding button
    $(`.cls-btn[data-div='${divId}']`).find(".cls-settings").addClass("show-tick");
  }

  // Retrieve the last visible div from localStorage, default to productUserDiv
  const lastVisibleDiv = localStorage.getItem("visibleDiv") || "productUserDiv";

  // Show the last visible div
  showDiv(lastVisibleDiv);

  // Handle the click event for each button
  $(".cls-btn").click(function () {
    const divId = $(this).data("div");
    showDiv(divId);
    localStorage.setItem("visibleDiv", divId); // Store the current div
  });
});

/**
 * For getting the deleting form div
 */
document.querySelector(".cls-delete").addEventListener("click", function () {
  // Show the form
  document.getElementById("deleteRoleForm").style.display = "block";

  // Make an AJAX call to fetch roles from the database
  $.ajax({
    url: baseUrl + "admin/getRoles",
    type: "POST",
    dataType: "json",
    success: function (response) {
      const select = document.getElementById("deleteRoleSelect");
      select.innerHTML =
        '<option value="" disabled selected>Select the role</option>'; // Reset options

      // Assuming 'response.role' contains the array of roles
      response.role.forEach((role) => {
        const option = document.createElement("option");
        option.value = role.role_id; // Assuming 'role_id' is the value you want to use
        option.textContent = role.role_name; // Assuming 'role_name' is the display name
        select.appendChild(option);
      });
    },
    error: function (xhr, status, error) {
      console.error("Error:", error);
    },
  });
});

/**
 * holiday upload file div
 */
// When the Import file button is clicked
$("#importButton").click(function (e) {
  e.preventDefault(); // Prevent the default form submission
  $("#holidayForm").hide(); // Hide the holiday form
  $(".import").hide(); // Hide the buttons container
  $("#fileUploadForm").show(); // Show the file upload form
  $(".btn-secondary").show();
});

// When the Back button is clicked
$("#backButton").click(function (e) {
  e.preventDefault(); // Prevent the default form submission
  $("#fileUploadForm").hide(); // Hide the file upload form
  $("#holidayForm").show(); // Show the holiday form
  $(".import").show(); // Show the buttons container
  $(".btn-secondary").hide();
});

/**
 * Submitting the form for Setting the product owner
 */
$("#productUserForm").on("submit", function (e) {
  e.preventDefault();
  let formData = $(this).serialize();
  formData += "&setProductOwnerButton=1";
  $.ajax({
    url: baseUrl + "admin/setProductOwner",
    method: "POST",
    data: formData,
    dataType: "json",
    success: function (response) {
      if (response.message) {
        Swal.fire({
          title: "Success",
          text: response.message,
          icon: "success",
          confirmButtonText: "OK",
        }).then((result) => {
          if (result.isConfirmed) {
            window.location.href = window.location.href;
          }
        });
      } else if (response.errors) {
        var errorMessage = "";
        for (var field in response.errors) {
          errorMessage += response.errors[field] + "<br>";
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
      Swal.fire({
        title: "Error",
        text: "An error occurred while setting product owner",
        icon: "error",
        confirmButtonText: "OK",
      });
    },
  });
});

/**
 * form to set the poker limit
 */
$("#pokerConfigForm").on("submit", function (e) {
  e.preventDefault();
  let formData = $(this).serialize();
  formData += "&setPokerLimitButton=1";
  $.ajax({
    url: baseUrl + "admin/pokerConfig",
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
          icon: "warning",
          confirmButtonText: "OK",
        });
      } else {
        Swal.fire({
          title: "Success",
          text: "Poker value changed",
          icon: "success",
          confirmButtonText: "OK",
        }).then((result) => {
          if (result.isConfirmed) {
            window.location.href = window.location.href;
          }
        });
      }
    },
    error: function () {
      Swal.fire({
        title: "Error",
        text: "An error occurred while changing poker value",
        icon: "error",
        confirmButtonText: "OK",
      });
    },
  });
});

$(document).ready(function () {
  // Attach a submit event handler to both forms
  $("#addRoleForm, #deleteRoleForm").on("submit", function (e) {
    e.preventDefault(); // Prevent the default form submission

    let formId = $(this).attr("id"); // Get the ID of the form being submitted
    let actionUrl = ""; // Initialize the action URL

    // Determine which form is being submitted and set the appropriate action and data
    if (formId === "addRoleForm") {
      actionUrl = baseUrl + "admin/addRole"; // Replace with your actual add role URL
      formData = $(this).serialize();
      formData += "&addRoleButton=1";
      successMessage = "New role added successfully";
    } else if (formId === "deleteRoleForm") {
      actionUrl = baseUrl + "admin/deleteRole"; // Replace with your actual delete role URL
      formData = $(this).serialize();
      formData += "&deleteRoleButton=1";
      successMessage = "Role Deleted successfully";
    }
    // Make the AJAX call
    $.ajax({
      url: actionUrl,
      type: "POST",
      data: formData,
      dataType: "json",
      success: function (response) {
        // Handle the success response
        if (response.errors) {
          var errorMessage = "";
          for (var field in response.errors) {
            errorMessage += response.errors[field] + "<br>";
          }
          Swal.fire({
            title: "Validation Error!",
            html: errorMessage,
            icon: "warning",
            confirmButtonText: "OK",
          });
        } else {
          Swal.fire({
            title: "Success",
            text: successMessage,
            icon: "success",
            confirmButtonText: "OK",
          }).then((result) => {
            if (result.isConfirmed) {
              window.location.href = window.location.href;
            }
          });
        }
      },
      error: function (xhr, status, error) {
        // Handle any errors
        Swal.fire({
          icon: "error",
          title: "Error",
          text: "Something went wrong! Please try again later.",
        });
        console.error("Error:", error);
      },
    });
  });

  // Show the delete form when the delete button is clicked
  $(".cls-delete").on("click", function () {
    $("#deleteRoleForm").show();
  });
});

$(document).ready(function () {
  $("#holidayButton").on("click", function (e) {
    e.preventDefault(); // Prevent default form submission

    // Trigger validation on form submission
    const holidayForm = document.getElementById("holidayForm");
    const fileUploadForm = document.getElementById("fileUploadForm");

    if ($("#holidayForm").is(":visible")) {
      // Check form validity
      if (!holidayForm.checkValidity()) {
        holidayForm.classList.add("was-validated");
      }

      let formData = $("#holidayForm").serialize();
      formData += "&holidayButton=1";

      // AJAX call for holidayForm
      $.ajax({
        url: baseUrl + "admin/createHolidays",
        type: "POST",
        data: formData,
        success: function (response) {
          // Handle success response
          if (response.errors) {
            var errorMessage = "";
            for (var field in response.errors) {
              errorMessage += response.errors[field] + "<br>";
            }
            Swal.fire({
              title: "Validation Error!",
              html: errorMessage,
              icon: "warning",
              confirmButtonText: "OK",
            });
          } else {
            Swal.fire({
              title: "Success",
              text: "Holiday Added to Calendar",
              icon: "success",
              confirmButtonText: "OK",
            }).then((result) => {
              if (result.isConfirmed) {
                window.location.href = window.location.href;
              }
            });
          }
        },
        error: function (xhr, status, error) {
          // Handle error response
          Swal.fire({
            title: "Error",
            text: "An error occurred while adding holiday",
            icon: "error",
            confirmButtonText: "OK",
          });
        },
      });
    } else if ($("#fileUploadForm").is(":visible")) {
      // Check file upload form validity
      if (!fileUploadForm.checkValidity()) {
        fileUploadForm.classList.add("was-validated");
        return; // Stop execution if the form is invalid
      }

      // AJAX call for fileUploadForm
      var formData = new FormData($("#fileUploadForm")[0]); // Use FormData for file upload
      $.ajax({
        url: baseUrl + "admin/holidayFileUpload",
        type: "POST",
        data: formData,
        contentType: false, // Important for file upload
        processData: false, // Important for file upload
        success: function (response) {
          // Handle success response
          if (response.message) {
            Swal.fire({
              title: "Success",
              text: "Holiday Added to Calendar",
              icon: "success",
              confirmButtonText: "OK",
            }).then((result) => {
              if (result.isConfirmed) {
                window.location.href = window.location.href;
              }
            });
          } else if (response.duplicate) {
            Swal.fire({
              title: "Warning",
              text: response.duplicate,
              icon: "warning",
              confirmButtonText: "OK",
            });
          }
        },
        error: function (xhr, status, error) {
          // Handle error response
          Swal.fire({
            title: "Error",
            text: "An error occurred while adding holiday",
            icon: "error",
            confirmButtonText: "OK",
          });
        },
      });
    }
  });
});

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

// Function to toggle the visibility of remove buttons
function toggleRemoveButtons() {
  // Show remove button only if there are more than one input groups
  if ($(".t-shirtValues").length > 1) {
    $(".remove-btn").show();
  } else {
    $(".remove-btn").hide();
  }
}

// Initialize the visibility of remove buttons
toggleRemoveButtons();

// Event handler for adding new T-shirt size fields
$("#addTshirtSize").click(function () {
  // Create new T-shirt size input group with a remove button
  var newInputGroup = `
      <div class="t-shirtValues">
          <div class="col-sm-5">
              <label for="t-shirtName" class="form-label">T-Shirt size name</label>
              <input type="text" class="form-control" name="t-shirtName[]" placeholder="Set T-Shirt size name" required>
              <div class="valid-feedback">Looks good!</div>
              <div class="invalid-feedback">Please give a T-Shirt name.</div>
          </div>
          <div class="col-sm-2"></div>
          <div class="col-sm-5">
              <label for="t-shirtValue" class="form-label">T-Shirt size value</label>
              <input type="text" class="form-control" name="t-shirtValue[]" placeholder="Set T-Shirt size value" required>
              <div class="valid-feedback">Looks good!</div>
              <div class="invalid-feedback">Please give a T-Shirt value.</div>
          </div>
          <button type="button" class="remove-btn sec-icon close"><i class="bi bi-x"></i></button>
      </div>`;

  // Append the new input group to the container
  $("#tshirtContainer").append(newInputGroup);
  // Toggle the remove button visibility
  toggleRemoveButtons();
});

// Event handler for removing T-shirt size fields
$(document).on("click", ".remove-btn", function () {
  $(this).closest(".t-shirtValues").remove();
  // Toggle the remove button visibility after removal
  toggleRemoveButtons();
});

// Form submission handler
$("#tShirtForm").submit(function (event) {
  // Prevent default form submission
  event.preventDefault();

  let formData = $(this).serialize();
  formData += "&setTShirtSize=1";

  // AJAX call to post data to the controller
  $.ajax({
    url: baseUrl + "admin/setTShirtSize", // Replace with your controller's URL
    type: "POST",
    data: formData,
    success: function (response) {
      if (response.errors) {
        var errorMessage = "";
        for (var field in response.errors) {
          errorMessage += response.errors[field] + "<br>";
        }
        Swal.fire({
          title: "Validation Error!",
          html: errorMessage,
          icon: "warning",
          confirmButtonText: "OK",
        });
      } else if (response.message) {
        Swal.fire({
          title: "Success",
          text: response.message,
          icon: "success",
          confirmButtonText: "OK",
        }).then((result) => {
          if (result.isConfirmed) {
            window.location.href = window.location.href;
          }
        });
      }
    },
    error: function (error) {
      // Handle error response
      console.log(error);
    },
  });
});

$(document).ready(function () {
  //ajax to to get the product t shirt size on selecting the product
  $("#parentProductSelect").change(function () {
    let productId = $(this).val();

    $.ajax({
      url: baseUrl + "admin/getTShirtSizeByProduct/" + productId,
      type: "POST",
      dataType: "json",
      success: function (response) {
        // Populate input boxes with response data
        renderInputGroups(response.values);
      },
      error: function (xhr, status, error) {
        console.error("Error:", error);
      },
    });
  });
  // Function to render the input groups
  function renderInputGroups(data) {
    // Clear the existing input boxes
    $("#tshirtContainer").empty();

    // Check if there are values to display
    if (data && data.length > 0) {
      // Loop through the received values and create input boxes
      data.forEach(function (item, index) {
        let newInputGroup = `
          <div class="t-shirtValues">
            <div class="col-sm-5">
              <label for="t-shirtName" class="form-label">T-Shirt size name</label>
              <input type="text" class="form-control" name="t-shirtName[]" placeholder="Set T-Shirt size name" value="${
                item.t_size_name
              }" required>
              <div class="valid-feedback">Looks good!</div>
              <div class="invalid-feedback">Please give a T-Shirt name.</div>
            </div>
            <div class="col-sm-2">
            </div>
            <div class="col-sm-5">
              <label for="t-shirtValue" class="form-label">T-Shirt size value</label>
              <input type="text" class="form-control" name="t-shirtValue[]" placeholder="Set T-Shirt size value" value="${
                item.t_size_values
              }" required>
              <div class="valid-feedback">Looks good!</div>
              <div class="invalid-feedback">Please give a T-Shirt value.</div>
            </div>
            ${
              index > 0
                ? '<button type="button" class="remove-btn sec-icon close"><i class="bi bi-x"></i></button>'
                : ""
            }
          </div>`;

        $("#tshirtContainer").append(newInputGroup);
      });
    } else {
      // Add at least one empty row if no data is returned
      let newInputGroup = `
        <div class="t-shirtValues">
          <div class="col-sm-5">
            <label for="t-shirtName" class="form-label">T-Shirt size name</label>
            <input type="text" class="form-control" name="t-shirtName[]" placeholder="Set T-Shirt size name" required>
            <div class="valid-feedback">Looks good!</div>
            <div class="invalid-feedback">Please give a T-Shirt name.</div>
          </div>
          <div class="col-sm-2">
            <!-- Empty for the first row -->
          </div>
          <div class="col-sm-5">
            <label for="t-shirtValue" class="form-label">T-Shirt size value</label>
            <input type="text" class="form-control" name="t-shirtValue[]" placeholder="Set T-Shirt size value" required>
            <div class="valid-feedback">Looks good!</div>
            <div class="invalid-feedback">Please give a T-Shirt value.</div>
          </div>
        </div>`;

      $("#tshirtContainer").append(newInputGroup);
    }
  }

  // Event delegation for dynamically added remove buttons
  $(document).on("click", ".remove-btn", function () {
    $(this).closest(".t-shirtValues").remove();
  });
});
