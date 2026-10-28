<form id="scrumDiaryForm" action="<?= ASSERT_PATH ?>sprint/scrumdiary?sprint_id=<?= $_GET['sprint_id'] ?>" method="post">
    <div class="date-picker">
        <input type="date" id="date-picker" name="date" aria-label="Scrum date">
    </div>
    <div class="container">
        <div class="input-area">
            <div class="radio-group">
                <span>Challenges:</span>
                <label>
                    <input type="radio" name="challenges" value="N"> No
                </label>
                <label>
                    <input type="radio" name="challenges" value="Y"> Yes
                </label>
            </div>
            <div class="error-container">
                <span class="error-message" id="radioError">Please select any one of the above options</span>
            </div>
            <div class="form-group">
                <div class="form-group-header" id="formGroupHeader">
                    <h5 class="headerName"><i class="fas fa-clipboard"></i> General</h5>
                </div>
                <div class="form-group-content">
                    <div class="voice-recorder">
                        <button id="voiceRecorderBtn" type="button">
                            <i class="fas fa-microphone"></i>
                        </button>
                    </div>
                    <textarea id="general" name="general" placeholder="General comments"></textarea>
                </div>
                <div class="error-container">
                    <span class="error-message" id="generalError">This is a required field</span>
                </div>
            </div>
            <button type="submit" class="blue-button" id="submit-btn">Submit</button>
        </div>
    </div>
</form>

<?php 
if($data['modal']==1) 
{
    echo "<script>
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: 'Your Feedback has been added successfully!',
        });
    </script>";
}
if($data['modal']==0) 
{
    echo "<script>
        Swal.fire({
            icon: 'warning',
            title: 'Error!',
            text: 'Error in adding Feedback!',
        });
    </script>";
}
?>