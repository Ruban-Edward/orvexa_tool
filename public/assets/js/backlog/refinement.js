if (typeof m_refinement !== 'undefined') {

    function escapeHtml(v) {
        if (v === null || v === undefined) return '';
        return String(v)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    let sortColumn = 'order_by';
    let sortOrder = 1; // 1 for ascending, -1 for descending

    // Function to dynamically generate the table header
    function generateTableHeader() {
        const tableHeader = document.getElementById('tableHeader');
        tableHeader.innerHTML = '';
        const headerRow = document.createElement('tr');
        const columns = [
            { name: 'backlog_order', label: 'Backlog order' },
            { name: 'backlog_item_name', label: 'Name' },
            { name: 'backlog_item_type', label: 'Type' },
            { name: 'customer_name', label: 'Customer' },
            { name: 'priority', label: 'Priority' },
            { name: 'status_name', label: 'Status' }
        ];

        columns.forEach(column => {
            const th = document.createElement('th');
            th.textContent = column.label;
            th.setAttribute("class", "text-center");
            th.classList.add('sorting');
            th.addEventListener('click', () => sortTable(column.name));
            headerRow.appendChild(th);
        });

        tableHeader.appendChild(headerRow);
    }

    // Function to dynamically generate the table body
    function generateTableBody(data) {
        const tableBody = document.getElementById('tableBody');
        tableBody.innerHTML = '';

        data.forEach(item => {
            console.log("response",item)
            const row = document.createElement('tr');
            row.setAttribute('data-order', item.order_by); // Add order_by as data attribute for sortable
            row.setAttribute('data-backlog-id', item.backlog_item_id); // Add backlog_id as data attribute
            row.innerHTML = `
            <td class="drag-handle capitalize-text text-center">${escapeHtml(item.backlog_order)}</td>
            <td class="drag-handle capitalize-text text-center">${escapeHtml(item.backlog_item_name)}</td>
            <td class="drag-handle capitalize-text text-center">${escapeHtml(item.tracker)}</td>
            <td class="drag-handle capitalize-text text-center">${escapeHtml(item.customer_name)}</td>
            <td class="drag-handle capitalize-text text-center">
                <div class="priority-${escapeHtml(item.priority)}">${escapeHtml(item.priority)}</div>
            </td>
            <td class="drag-handle text-center">${escapeHtml(item.status_name)}</td>
        `;
            tableBody.appendChild(row);
        });

        // Initialize jQuery UI sortable
        $("#priority-table tbody").sortable({
            handle: '.drag-handle',
            axis: 'y',
            helper: function (event, ui) {
                var $helper = ui.clone();
                $helper.addClass("sortable-helper");
                $helper.width($('#priority-table').outerWidth());
                return $helper;
            },
            cursor: 'move',
            containment: '#priority-table',
            start: function (event, ui) {
                // Reset previous dragging state
                $('.dragging').removeClass('dragging');
                ui.item.addClass('dragging'); // Add dragging class to the item being dragged
                ui.placeholder.addClass('placeholder');
            },
            stop: function (event, ui) {
                ui.item.removeClass('dragging'); // Remove dragging class from the item
                ui.placeholder.removeClass('placeholder');
                updatePriorities();
                sendPrioritiesToPHP();
            }
        }).disableSelection();
    }

    // Function to sort the table data
    function sortTable(column) {
        sortOrder = sortColumn === column ? -sortOrder : 1;
        sortColumn = column;

        data.sort((a, b) => {
            if (a[column] < b[column]) return -sortOrder;
            if (a[column] > b[column]) return sortOrder;
            return 0;
        });

        generateTableBody(data);
    }

    //Function to update priorities based on current order
    function updatePriorities() {
        var rows = $("#priority-table tbody tr");
        rows.each(function (index) {
            var orderBy = $(this).find('td:nth-child(1)');
            orderBy.text(index + 1); // Adjusting index for 1-based order
            $(this).attr('data-order', index + 1); // Updating data-order attribute
        });
    }
    
    // Function to send updated priorities to the server
    function sendPrioritiesToPHP() {
        let orderArray = [];  
        let draggedRowId = null;
    
        $('#priority-table tbody tr').each(function () {
            if ($(this).hasClass('dragging')) {
                draggedRowId = $(this).data('backlog-id'); // Get the backlog_id of the dragged row
            }
            // Populate orderArray with the new state of the table
            orderArray.push({
                backlog_order: $(this).find('td:nth-child(1)').text(), // Get the backlog order from the first column
                backlog_item_id: $(this).data('backlog-id') // Get the backlog_id from the data attribute
            });
        });


        // Logging the orderArray for debugging
        console.log('Final Order Array:', orderArray);

        // Example AJAX request to send the orderArray to the server
        $.ajax({
            url: refinement_url + "backlog/backlogGrooming/" + pid,
            type: 'POST',
            data: JSON.stringify({ order: orderArray }),
            dataType: 'json',
            success: function (response) {
                console.log("Response :",response);
                var toastMessage = response.data.length>0 ? "Priority is Changed !" : 'Priority is Unchanged ! ';
                
                
                var toastTitle = 'Backlog Grooming';

                // Create the toast element dynamically
                var toastHTML = `
                    <div id="liveToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="toast-header" id="toast-header">
                    <strong class="me-auto">${toastTitle}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                    <div class="toast-body">
                    ${toastMessage}
                    </div>
                    </div>
                    `;
                    
                $('.toast-container').html(toastHTML);
                color=response.data.length> 0 ? "yellow" : "red";
                text=response.data.length> 0 ? "black" : "white";
                document.getElementById("toast-header").style.backgroundColor= color;
                document.getElementById("toast-header").style.color= text;
                var toastEl = document.getElementById('liveToast');
                var toast = new bootstrap.Toast(toastEl, {
                    animation: true,
                    autohide: true,
                    delay: 5000
                });
                toast.show();
            },
            error: function (xhr, status, error) {
                console.error('AJAX Error:', status, error);
                console.error('Response Text:', xhr.responseText);
            }
        });


    }


    // Generate table header and sort by 'order_by' column
    generateTableHeader();
    sortTable('order_by');

}