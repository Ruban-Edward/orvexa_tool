if (typeof setPermissionPage !== "undefined") {
  $(document).ready(function () {
    // Log initial state of modules and permissions
    logModuleState();
    checkModulePermissions();

    // Event listener for "Select This Module" checkboxes
    $(document).on("change", '[id^="selectAll_"]', function () {
      const module = $(this).data("module");

      // Use attribute selector to handle spaces in module names
      $(`input[data-module="${module}"]`).prop("checked", this.checked);
      updateSelectAllCheckbox();
      checkModulePermissions();
    });

    // Event listener for individual permission checkboxes
    $(".module-checkbox").on("change", function () {
      const module = $(this).data("module");
      updateModuleCheckbox(module);
      updateSelectAllCheckbox();
      checkModulePermissions();
    });

    // Event listener for the main "Select All" checkbox
    $("#selectAll").on("change", function () {
      $('.cls-checkbox input[type="checkbox"]').prop("checked", this.checked);
      checkModulePermissions();
    });

    // Handle form submission for setting permissions
    $("#permissionsForm").on("submit", function (e) {
      e.preventDefault();

      let formData = $(this).serialize();
      formData += "&SaveUserRolebutton=1";
      let url = baseUrl + "admin/setPermissions";

      $.ajax({
        url: url,
        method: "POST",
        data: formData,
        dataType: "json",
        success: function (response) {
          $("#permissionsForm").modal("hide");
          if (response.success) {
            Swal.fire({
              title: "Success",
              text: "User Role Permission updated successfully",
              icon: "success",
              confirmButtonText: "OK",
            }).then((result) => {
              if (result.isConfirmed) {
                window.location.href = window.location.href;
              }
            });
          } else if (response.validation) {
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
          } else {
            Swal.fire({
              title: "Warning",
              text: "Please select at least one permission",
              icon: "warning",
              confirmButtonText: "OK",
            });
          }
        },
        error: function () {
          Swal.fire({
            title: "Error",
            text: "An Error occured while setting the permssion",
            icon: "error",
            confirmButtonText: "OK",
          });
        },
      });
    });

    // Handle form submission for adding new permission
    $("#newPermissionForm").on("submit", function (e) {
      e.preventDefault();
      let formData = $(this).serialize();
      formData += "&setPermissionButton=1";
      let operation = $("#operation").val();
      if (operation == "inserting") {
        operationUrl = "admin/setNewPermission";
        successMessage = "New permission inserted successfully";
      } else if (operation == "updating") {
        let id = $("#editPermissionNameModel").val();
        operationUrl = "admin/updatePermission";
        successMessage = "Permission updated successfully";
      } else {
        let id = $("#deletePermissionModel").val();
        operationUrl = "admin/deletePermission/" + id;
        successMessage = "Permission deleted successfully";
      }
      $.ajax({
        url: baseUrl + operationUrl,
        method: "POST",
        data: formData,
        dataType: "json",
        success: function (response) {
          if (response.permission) {
            $("#newPermissionForm").modal("hide");
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
          } else if (response.validation) {
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
          }
        },
        error: function () {
          Swal.fire({
            title: "Error",
            text: "An error occurred while " + operation + " the permission",
            icon: "error",
            confirmButtonText: "OK",
          });
        },
      });
    });

    // Handle role selection change
    $("#selectUser").on("change", function () {
      var roleId = $(this).serialize();
      $.ajax({
        url: baseUrl + "admin/getSpecificPermissions",
        method: "POST",
        data: roleId,
        dataType: "json",
        success: function (response) {
          $('input[type="checkbox"]').prop("checked", false);

          // Check the checkboxes based on the response
          response.permissions.forEach(function (permissionId) {
            $('input[value="' + permissionId + '"]').prop("checked", true);
          });

          // Update all module checkboxes
          $('[id^="selectAll_"]').each(function () {
            updateModuleCheckbox($(this).data("module"));
          });

          // Update the "Select All" checkbox
          updateSelectAllCheckbox();
          checkModulePermissions();
        },
        error: function () {
          Swal.fire({
            title: "Error",
            text: "An error occurred while fetching permissions",
            icon: "error",
            confirmButtonText: "OK",
          });
        },
      });
    });
  });

  function updateModuleCheckbox(module) {
    const moduleCheckbox = $(`#selectAll_${module.replace(/ /g, "\\ ")}`);
    const modulePermissions = $(
      `input[data-module="${module}"]:not([id^="selectAll_"])`
    );
    const allChecked =
      modulePermissions.length === modulePermissions.filter(":checked").length;
    moduleCheckbox.prop("checked", allChecked);
  }

  function updateSelectAllCheckbox() {
    const allModuleCheckboxes = $('[id^="selectAll_"]');

    let allChecked = true;
    allModuleCheckboxes.each(function (index) {
      const module = $(this).data("module");
      const isChecked = $(this).prop("checked");
      const isDisabled = $(this).prop("disabled");

      if (!isChecked && !isDisabled) {
        allChecked = false;
      }
    });

    $("#selectAll").prop("checked", allChecked);
  }

  function checkModulePermissions() {
    $('[id^="selectAll_"]').each(function () {
      const module = $(this).data("module");
      const permissions = $(
        `input[data-module="${module}"]:not([id^="selectAll_"])`
      );
      const checkedPermissions = permissions.filter(":checked");

      if (permissions.length !== checkedPermissions.length) {
        permissions.not(":checked").each(function () {});
      }
    });
  }

  function logModuleState() {
    $('[id^="selectAll_"]').each(function () {
      const module = $(this).data("module");
      const modulePermissions = $(
        `input[data-module="${module}"]:not([id^="selectAll_"])`
      );
    });
  }

  function permissionModal(operation) {
    if (operation == "add") {
      document.getElementById("permissionModalLabel").innerText =
        "Add new permission";
      document.getElementById("setPermissionButton").innerText =
        "Add permission";
      document.getElementById("operation").value = "inserting";
      $("#editPermissionName, #deletePermission").hide();
      $("#permissionName, #module, #routesURL").show();
    }

    if (operation == "update") {
      document.getElementById("permissionModalLabel").innerText =
        "Update permission";
      document.getElementById("setPermissionButton").innerText =
        "Update permission";
      document.getElementById("operation").value = "updating";
      $("#editPermissionName, #permissionName, #module, #routesURL").show();
      $("#deletePermission").hide();

      $.ajax({
        url: baseUrl + "admin/getPermissionName",
        method: "GET",
        success: function (response) {
          let permissionSelect = document.getElementById(
            "editPermissionNameModel"
          );
          permissionSelect.innerHTML =
            '<option value="" disabled selected>Select the permission</option>';
          response.permissionsName.forEach(function (permission) {
            let option = document.createElement("option");
            option.value = permission.permission_id;

            // Convert permission_name to a more readable format
            let formattedName = permission.permission_name
              .toLowerCase()
              .replace(/_/g, " ") // Replace underscores with spaces
              .replace(/\b\w/g, function (l) {
                return l.toUpperCase();
              }); // Capitalize the first letter of each word

            option.text = formattedName;
            permissionSelect.appendChild(option);
          });

          //event listener to fetch details when a permission is selected
          permissionSelect.addEventListener("change", function () {
            let selectedPermissionId = this.value;
            fetchPermissionDetails(selectedPermissionId);
          });
        },
        error: function (xhr, status, error) {
          console.error(
            "An error occurred while fetching permissions: " + error
          );
        },
      });
    }

    if (operation == "delete") {
      document.getElementById("permissionModalLabel").innerText =
        "Delete permission";
      document.getElementById("setPermissionButton").innerText =
        "Delete permission";
      document.getElementById("operation").value = "deleting";
      $("#permissionName,#editPermissionName,#module, #routesURL").hide();
      $("#deletePermission").show();

      //dynamically fetches the permission after the delete button is pressed
      $.ajax({
        url: baseUrl + "admin/getPermissionName",
        method: "GET",
        success: function (response) {
          let permissionSelect = document.getElementById(
            "deletePermissionModel"
          );
          permissionSelect.innerHTML =
            '<option value="" disabled selected>Select the permission</option>';
          response.permissionsName.forEach(function (permission) {
            let option = document.createElement("option");
            option.value = permission.permission_id;

            // Convert permission_name to a more readable format
            let formattedName = permission.permission_name
              .toLowerCase()
              .replace(/_/g, " ") // Replace underscores with spaces
              .replace(/\b\w/g, function (l) {
                return l.toUpperCase();
              }); // Capitalize the first letter of each word

            option.text = formattedName;
            permissionSelect.appendChild(option);
          });
        },
        error: function (xhr, status, error) {
          console.error(
            "An error occurred while fetching permissions: " + error
          );
        },
      });
    }
  }

  // Function to fetch and populate permission details based on the selected permission
  function fetchPermissionDetails(permissionId) {
    $.ajax({
      url: baseUrl + "admin/getPermissionDetails/" + permissionId,
      method: "GET",
      success: function (response) {
        let formattedName = response.permission_name
          .toLowerCase()
          .replace(/_/g, " ") // Replace underscores with spaces
          .replace(/\b\w/g, function (l) {
            return l.toUpperCase();
          }); // Capitalize the first letter of each word
        
        // Populate the fields with the fetched details
        document.getElementById("permissionNameModal").value = formattedName;
        document.getElementById("moduleModel").value = response.r_module_id;
        document.getElementById("routesURLModel").value = response.routes_url;
      },
      error: function (xhr, status, error) {
        console.error(
          "An error occurred while fetching permission details: " + error
        );
      },
    });
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
}

// Keeping permissions in step with the tool: reuse or create permissions for
// features the code checks, and remove ones that point at removed features
if (typeof setPermissionPage !== "undefined") {
  const post = (url, data) => $.post(baseUrl + url, data, null, "json");
  const reload = () => setTimeout(() => window.location.reload(), 900);

  $(document).on("click", "[data-relink]", function () {
    const button = this;
    button.disabled = true;
    post("admin/permissions/relink", { permission_id: button.dataset.relink, route: button.dataset.route })
      .done((response) => {
        if (!response.success) {
          Ox.toast(response.message || "The permission could not be updated.", { type: "error" });
          button.disabled = false;
          return;
        }
        Ox.toast(`${button.textContent.trim().replace(/^Use /, "")} now controls ${button.dataset.route}`);
        reload();
      })
      .fail(() => {
        Ox.toast("The permission could not be updated.", { type: "error" });
        button.disabled = false;
      });
  });

  $(document).on("click", "[data-create-route]", function () {
    permissionModal("add");
    document.getElementById("newPermissionForm").reset();
    document.getElementById("routesURLModel").value = this.dataset.createRoute;
    document.getElementById("permissionNameModal").value = this.dataset.createName;
    if (this.dataset.createModule !== "0") document.getElementById("moduleModel").value = this.dataset.createModule;
    bootstrap.Modal.getOrCreateInstance(document.getElementById("permissionModal")).show();
  });

  function removeStale(ids) {
    return post("admin/permissions/cleanup", { ids })
      .done((response) => {
        if (!response.success) {
          Ox.toast(response.message || "Nothing was removed.", { type: "error" });
          return;
        }
        Ox.toast(`${response.removed} unused ${response.removed === 1 ? "permission" : "permissions"} removed`);
        reload();
      })
      .fail(() => Ox.toast("The permissions could not be removed.", { type: "error" }));
  }

  $(document).on("click", "[data-remove]", function () {
    removeStale([this.dataset.remove]);
  });

  $("#removeAllStale").on("click", function () {
    const ids = $("[data-stale]").map((i, el) => el.dataset.stale).get();
    Swal.fire({
      title: `Remove ${ids.length} unused ${ids.length === 1 ? "permission" : "permissions"}?`,
      text: "They point to features that are no longer in the tool, so no role loses access to anything.",
      icon: "question",
      showCancelButton: true,
      confirmButtonText: "Remove",
      reverseButtons: true,
    }).then((result) => {
      if (result.isConfirmed) removeStale(ids);
    });
  });
}
