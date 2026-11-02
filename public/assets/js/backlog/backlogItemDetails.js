if (typeof m_backlogItemDetails !== 'undefined') {

  function escapeHtml(v) {
    if (v === null || v === undefined) return '';
    return String(v)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  let activities = [];
  let filteredActivities = [];
  let currentpage = 1;
  const perPageItem = 8;

  document.getElementById('history-tab').addEventListener('click', loadActivities);
  // document.getElementById('history-tab').addEventListener('click', clearHistory);
  document.getElementById('filterButton').addEventListener('click', filterActivities);
  document.getElementById('previous').addEventListener('click', () => changePage(-1));
  document.getElementById('next').addEventListener('click', () => changePage(1));

  function loadActivities() {
    clearHistory();
    $.ajax({
      url: assert_path + "backlog/historyDataDetails",
      method: 'POST',
      dataType: 'json',
      data: JSON.stringify({
        pId: pId,
        pblId: pblId
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


  function deleteDocument(docId) {
    Swal.fire({
      icon: 'question',
      title: 'Are you sure?',
      text: 'Do you want to remove the document?',
      showCancelButton: true,
      cancelButtonColor: "#d33",
      confirmButtonText: 'Yes',
      cancelButtonText: 'No'
    }).then((result) => {
      if (result.isConfirmed) {
        $.ajax({
          url: assert_path + "backlog/deletedocument",
          method: 'POST',
          data: {
            pId: pId,
            pblId: pblId,
            docId: docId
          },
          success: function (response) {
            if (response.success) {
              Swal.fire({
                icon: 'success',
                title: 'Document',
                text: response.message,
                confirmButtonText: 'OK'
              }).then((result) => {
                if (result.isConfirmed) {
                  location.reload();
                }
              });
            } else {
              Swal.fire({
                icon: 'error',
                title: 'Document',
                text: response.message,
                confirmButtonText: 'OK'
              }).then((result) => {
                if (result.isConfirmed) {
                  location.reload();
                }
              });
            }
          }
        });
      }
    });
  }

  function filterActivities() {
    const startDate = new Date(document.getElementById("startDate").value);
    const endDate = new Date(document.getElementById("endDate").value);

    filteredActivities = activities.filter((activity) => {
      const activityDate = new Date(activity.date);
      return activityDate >= startDate && activityDate <= endDate;
    });

    currentpage = 1;
    displayActivities();
  }

  function displayActivities() {
    const dataList = document.getElementById("dataList");
    dataList.innerHTML = "";

    if (filteredActivities.length === 0) {
      const noDataMessage = document.createElement("div");
      noDataMessage.className = "no-data-message";
      noDataMessage.textContent = "No data found";
      dataList.appendChild(noDataMessage);

      // Hide pagination when no data
      document.querySelector(".pagination").style.display = "none";
      document.getElementById("previous").style.visibility = "hidden";
      document.getElementById("next").style.visibility = "hidden";
      return;
    }

    // Show pagination when there's data
    document.querySelector(".pagination").style.display = "flex";

    const startIndex = (currentpage - 1) * perPageItem;
    const endIndex = startIndex + perPageItem;
    const activitiesToDisplay = filteredActivities.slice(startIndex, endIndex);

    let currentDate = null;

    activitiesToDisplay.forEach((activity) => {
      if (activity.date !== currentDate) {
        currentDate = activity.date;
        const dateHeader = document.createElement("div");
        dateHeader.className = "history-date";
        dateHeader.textContent = currentDate;
        dataList.appendChild(dateHeader);
      }

      const li = document.createElement("li");
      li.innerHTML = `
                <div class="activity-item">
                    <div class="activity-header">
                        <span class="time col-sm-2 capitalize">${escapeHtml(activity.time)}</span>
                        <span class="icon col-sm-2 capitalize">${escapeHtml(activity.icon)}</span>
                        <span class="title capitalize">${escapeHtml(activity.title)}${escapeHtml(activity.data)} <p><span class="source">${escapeHtml(activity.source)}</span></p></span>
                    </div>
                </div>
            `;
      dataList.appendChild(li);
    });

    updatePaginationButtons();
  }

  function updatePaginationButtons() {
    const totalPages = Math.ceil(filteredActivities.length / perPageItem);
    if (filteredActivities.length <= perPageItem) {
      document.getElementById("previous").style.visibility = "hidden";
      document.getElementById("next").style.visibility = "hidden";
    }
    else if (currentpage === totalPages) {
      document.getElementById("next").style.visibility = "hidden";
      document.getElementById('previous').style.visibility = "visible";
    }
    else if (currentpage === 1) {
      document.getElementById("previous").style.visibility = "hidden";
      document.getElementById('next').style.visibility = "visible";
    } else {
      document.getElementById("previous").style.visibility = "visible";
      document.getElementById("next").style.visibility = "visible";
    }
  }

  function changePage(direction) {
    const newPage = currentpage + direction;
    const totalPages = Math.ceil(filteredActivities.length / perPageItem);
    if (newPage >= 1 && newPage <= totalPages) {
      currentpage = newPage;
      displayActivities();
    }
  }

  function clearHistory() {
    activities = [];
    filteredActivities = [];
    currentpage = 1;
    displayActivities();
  }

  document
    .getElementById("resetButton")
    .addEventListener("click", resetActivities);

  function resetActivities() {
    filteredActivities = [...activities];
    currentpagee = 1;

    // Clear filter inputs
    document.getElementById("startDate").value = "";
    document.getElementById("endDate").value = "";

    displayActivities();
  }
  const startDatePicker = flatpickr("#startDate", {
    onChange: function (selectedDates, dateStr, instance) {
      endDatePicker.set('minDate', dateStr);
    }
  });
  const endDatePicker = flatpickr("#endDate", {
    minDate: "today" // or another default value if needed
  });

}