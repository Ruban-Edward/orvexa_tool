<div class="ox-popover-head">
    <h5 class="content-header">Notifications</h5>
    <button type="button" class="ox-link-btn" id="markAllRead" hidden>Mark all read</button>
</div>
<div id="meeting-filters" class="ox-segmented ox-notify-tabs" role="radiogroup" aria-label="Notification type">
    <input type="radio" class="meet-select-radio" id="notify-activity" name="meetingFilter" value="activity" checked>
    <label for="notify-activity" class="meet-select">Activity <span class="ox-tab-count" id="activity-count" hidden></span></label>
    <input type="radio" class="meet-select-radio" id="meetings" name="meetingFilter" value="meetings">
    <label for="meetings" class="meet-select">Meetings</label>
    <input type="radio" class="meet-select-radio" id="upcoming-scrum" name="meetingFilter" value="upcoming-scrum">
    <label for="upcoming-scrum" class="meet-select">Scrum</label>
</div>
<ul id="meeting-list"></ul>
<div id="no-meetings-message" class="ox-empty-inline" style="display:none;">
    <i class="icon-bell-off" aria-hidden="true"></i>
    <span>You're all caught up</span>
</div>
