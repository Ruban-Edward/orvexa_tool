// Author: T. Siva Teja
// Email: thotasivateja57@gmail.com
// Date: 8 July 2024
// Purpose: View page for report page

if(typeof customReportView !== 'undefined'){

document.addEventListener("DOMContentLoaded", function () {
    // Initialize elements
    const filterBtn = document.getElementById("filterBtn");
    const sidebar = document.getElementById("filterSidebar");
    const closeBtn = document.getElementById("closeBtn");
    // const filtericon=document.getElementsByClassName("icon-filter");
    // const myDropdownReport = document.getElementById("myDropdownreport");
    const downloadBtn = document.getElementById("downloadBtn");
    const alertElement = document.getElementById("ale");
    const resetBtn=document.getElementById("resetBtn");
    const calendarElements = document.querySelectorAll('.flatpickr-calendar');

    
    // Show an alert if there are more than 5000 records
    if (alertElement) {
        Swal.fire({
            title: "Info",
            text: "You have more than 5000 records. If you want, you can download.",
            icon: "info"
        });
    }

    // Toggle sidebar visibility
    filterBtn.addEventListener('click', function () {
        toggleReportVisibility(filterBtn.dataset.param);
        sidebar.classList.add('open');
    });
   

   
  
  

    closeBtn.addEventListener('click', () => sidebar.classList.remove('open'));

    // Initialize download button
    downloadBtn.addEventListener("click", showDownloadConfirmation);
    
    // Close sidebar when clicking outside of it
    document.addEventListener("click", (event) => {
        console.log(event.target);
        console.log(event.target.tagName.toLowerCase());
        if (!sidebar.contains(event.target) && 
            event.target !== filterBtn && 
            event.target !== resetBtn && 
            !event.target.classList.contains('swal2-confirm') && 
            !event.target.classList.contains('flatpickr-current-month') &&
            !event.target.classList.contains('flatpickr-prev-month') &&
            !event.target.classList.contains('flatpickr-next-month') &&
            !event.target.classList.contains('numInput') &&
            !event.target.classList.contains('flatpickr-monthDropdown-months') &&
            !event.target.classList.contains('flatpickr-weekday') &&
            !event.target.classList.contains('flatpickr-calendar') &&
            !event.target.classList.contains('flatpickr-weekdays') &&
            !event.target.classList.contains('flatpickr-months')&&
            !event.target.classList.contains('flatpickr-month') &&
            !event.target.classList.contains('arrowUp')&&
            !event.target.classList.contains('arrowDown')&&
            !event.target.classList.contains("numInputWrapper") &&
            event.target.tagName.toLowerCase() !== 'svg' &&
            event.target.tagName.toLowerCase()!=='path')  {
            sidebar.classList.remove("open");
        }
    });
    
    
    resetBtn.addEventListener('click', (event) => {
        resetsubmit(); 
       
        
    });
    flatpickr("#fromDate", {
        dateFormat: "d-m-Y", // Flatpickr uses d-m-Y format
    });
    flatpickr("#toDate", {
        dateFormat: "d-m-Y",
    });
    // document.getElementById("applyfilter").addEventListener("click", countNumberOfFilters);
  
  
 
 
});

function countNumberOfFilters(){
    let numberOfFilters = document.getElementById("noti");
    numberOfFilters.style.display="block";
    let formData = $("#filterForm").serializeArray();

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

// Toggle visibility of different report sections
function toggleReportVisibility(report) {
    const elements = {
        meetdrops: document.getElementById("meetdrops"),
        backlog: document.getElementById("backlog"),
        sprintbacklogdrops: document.getElementById("sprintbacklogdrops"),
        sprint: document.getElementById("sprint"),
        date: document.getElementById("date")
    };

    // Hide all report sections
    Object.values(elements).forEach(el => el.style.display = "none");

    // Show relevant report sections based on the selected report type
    switch (report) {
        case "SprintReport":
            elements.sprintbacklogdrops.style.display = "block";
            elements.date.style.display = "block";
            break;
        case "MeetReport":
            elements.meetdrops.style.display = "block";
            elements.date.style.display = "block";
            break;
        case "BacklogReport":
            elements.sprintbacklogdrops.style.display = "block";
            elements.backlog.style.display="block";
            elements.date.style.display = "none";
            break;
    }
  
}

// Show a confirmation dialog before downloading the report
function showDownloadConfirmation() {
    document.getElementById("teamModalLabel").textContent = "Download Confirmation";
    document.getElementById("ok").style.display = "block";
    document.getElementById("team").textContent = "Do you want to download the report?";
}

// Handle form submission with validation
document.addEventListener("DOMContentLoaded", function () {
    const sidebar = document.getElementById("filterSidebar");
    $("#filterForm").submit(function (event) {
        event.preventDefault();
        let formData = $(this).serializeArray();
        let isAllFieldsEmpty = formData.every(field => !field.value.trim());

        if (isAllFieldsEmpty) {
            Swal.fire({
                icon: "warning",
                title: "Empty Field",
                text: "Please fill at least one form data",
            }).then(() => {
                sidebar.classList.add('open');
            });

        } else {
            sidebar.classList.remove('validation-error');
            let serializedData = $(this).serialize();
            console.log(serializedData); // For demonstration purposes
            const formType = document.getElementById("applyfilter").dataset.report;
            applyFilter(formType, serializedData, true);
        }
    });
});




// Download the filtered report
function downloadFilterReport(formType) {
    let formData = $("#filterForm").serialize();
    let url = `${BASE_URL}report/download/${formType}`;
    
    $.ajax({
        url: url,
        data: formData,
        type: 'POST',
        xhrFields: {
            responseType: 'blob' 
        },
        success: function (data) {
           
            let blob = new Blob([data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
            let link = document.createElement('a');
            link.href = window.URL.createObjectURL(blob);
            link.download = `${formType}_${new Date().toISOString().split('T')[0]}.xlsx`; 
            link.click();
        },
        error: function (xhr, status, error) {
            console.error('Error downloading report:', error);
        }
    });
}


// Apply filters to the report
function applyFilter(formType, formData,type) {
     if(type==true){
      document.getElementById("filterSidebar").classList.remove("open");
     }
    $.ajax({
        url: `${BASE_URL}report/${formType}filter/${formType}`,
        dataType: "json",
        type: "post",
        data: formData,
        success: function (response) {
            countNumberOfFilters();
            console.log(response);
            if (response.message !== "") {
                flashcard(response.message);
            }
            if (response.data.length) {
                currentPage=1;
                data=response.data
                generateTableHeader(data);
                generateTableBody(data);
                updatePaginationControls(data);
            } else {
                currentPage=0;
                data=response.data;
                updatePaginationControls(data);
                $("#tableBody").html("<tr><td colspan='100%' class='text-center'>No Data Found</td></tr>");

            }
        },
        error: function (xhr, status, error) {
            console.log(error);
        }
    });
}

// Show an informational flashcard
function flashcard(message) {
    Swal.fire({
        title: "Info",
        text: message,
        icon: "info"
    });
}

// Reset form and apply filters
function resetsubmit() {
    const filterForm = document.getElementById("filterForm");
    filterForm.reset();
    const formType = document.getElementById("applyfilter").dataset.report;
    applyFilter(formType, $(filterForm).serialize(), false);
}

/**
 * Exports the report data to an Excel file using the ExcelJS library.
 *
 * @param {Array} reportData - The data to be exported.
 * @param {string} reportName - The name of the report.
 * @param {string} formData - Serialized form data to include in the report.
 */

// Global variables
let currentPage = 1; 
const rowsPerPage = 10;

/**
 * Generates the table header based on the keys of the first item in the data.
 * @param {Array} data - The data used to determine the headers.
 */
function generateTableHeader(data) {
    const tableHeader = document.getElementById("tableHeader");
    tableHeader.innerHTML = "";
    console.log(data);

    const headerRow = document.createElement("tr");

    // Find the first object in the data to use its keys as headers
    const firstItem = data.find(item => item && typeof item === 'object' && !Array.isArray(item));

    if (firstItem) {
        const columns = Object.keys(firstItem);

        // Create table header cells
        columns.forEach((key, index) => {
            const th = document.createElement("th");
            th.textContent = key.replace(/_/g, " "); // Replace underscores with spaces
            th.classList.add("text-center", "sorting", `column-${index}`);
            if (index >= 8) th.style.display = "none"; // Initially hide columns after the first 8
            headerRow.appendChild(th);
        });
    }

    tableHeader.appendChild(headerRow);
}

/**
 * Generates the table body based on the current page of the data.
 * @param {Array} data - The data to populate the table body.
 */
function generateTableBody(data) {
    const tableBody = document.getElementById("tableBody");
    tableBody.innerHTML = "";

    // Calculate the start and end index for pagination
    const startIndex = (currentPage - 1) * rowsPerPage;
    const endIndex = startIndex + rowsPerPage;
    const paginatedData = data.slice(startIndex, endIndex);

    function stripHtmlTags(input) {
        const tempDiv = document.createElement("div");
        tempDiv.innerHTML = input;
        return tempDiv.textContent || tempDiv.innerText || "";
    }

    // Array of month names
    const monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

    // Create table rows for the current page of data
    paginatedData.forEach(item => {
        const row = document.createElement("tr");

        // Create table cells for each key in the item
        Object.keys(item).forEach((key, index) => {
            const cell = document.createElement("td");
            cell.classList.add("text-center", `column-${index}`);

            if (key === "Priority") {
                // Create a div for priority with specific styling
                const ele = document.createElement("div");
                const priorityMapping = {
                    H: "High",
                    M: "Medium",
                    L: "Low"
                };
                ele.classList.add(`priority-${item[key]}`);
                ele.setAttribute("data-toggle", "tooltip");
                ele.setAttribute("data-placement", "top");
                ele.setAttribute("title", `Priority: ${priorityMapping[item[key]] || item[key]}`);
                ele.textContent = stripHtmlTags(item[key]);
                cell.appendChild(ele);
            } 
             else {
                cell.textContent = stripHtmlTags(item[key]);
            }

            if (index >= 8) cell.style.display = "none"; // Initially hide cells after the first 8 columns
            row.appendChild(cell);
        });

        tableBody.appendChild(row);
    });
}

/**
 * Updates pagination controls based on the current page and total pages.
 * @param {Array} data - The data used to determine the total number of pages.
 */
function updatePaginationControls(data) {
    const totalPages = Math.ceil(data.length / rowsPerPage);
    document.getElementById("pageInfo").textContent = `Page ${currentPage} of ${totalPages}`;
    
    // Show or hide pagination buttons based on the current page
    document.getElementById("prevPage").style.visibility = currentPage > 1 ? "visible" : "hidden";
    document.getElementById("nextPage").style.visibility = currentPage < totalPages ? "visible" : "hidden";
}

// Event listeners for pagination controls
document.getElementById("prevPage").addEventListener("click", () => {
    if (currentPage > 1) {
        currentPage--;   
        generateTableHeader(data);
        generateTableBody(data); 
        updatePaginationControls(data);// Regenerate table body with the updated page
    }
});

document.getElementById("nextPage").addEventListener("click", () => {
    const totalPages = Math.ceil(data.length / rowsPerPage);

    if (currentPage < totalPages) {
        currentPage++;
        generateTableHeader(data);
        generateTableBody(data); 
        updatePaginationControls(data);// Regenerate table body with the updated page
    }
});

// Initial table generation
generateTableHeader(data);
generateTableBody(data);
updatePaginationControls(data);

}