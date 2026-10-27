<div class="container my-5">
    <h1 class="text-center display-4 mb-4">Planning Poker Game</h1>

    <!-- Card Selection Section -->
    <div id="cardSelection" class="text-center mb-5">
        <h3 class="mb-4">Select Your Estimate</h3>
        <div class="row justify-content-center">
            <div class="col-6 col-md-3">
                <label class="poker-card sync-label shadow-sm" data-value="1">
                    <div class="sync-icon">1</div>
                </label>
            </div>
            <div class="col-6 col-md-3">
                <label class="poker-card sync-label shadow-sm" data-value="2">
                    <div class="sync-icon">2</div>
                </label>
            </div>
            <div class="col-6 col-md-3">
                <label class="poker-card sync-label shadow-sm" data-value="3">
                    <div class="sync-icon">3</div>
                </label>
            </div>
            <div class="col-6 col-md-3">
                <label class="poker-card sync-label shadow-sm" data-value="5">
                    <div class="sync-icon">5</div>
                </label>
            </div>
            <div class="col-6 col-md-3">
                <label class="poker-card sync-label shadow-sm" data-value="8">
                    <div class="sync-icon">8</div>
                </label>
            </div>
            <div class="col-6 col-md-3">
                <label class="poker-card sync-label shadow-sm" data-value="13">
                    <div class="sync-icon">13</div>
                </label>
            </div>
            <div class="col-6 col-md-3">
                <label class="poker-card sync-label shadow-sm" data-value="21">
                    <div class="sync-icon">21</div>
                </label>
            </div>
            <div class="col-6 col-md-3">
                <label class="poker-card sync-label shadow-sm" data-value="34">
                    <div class="sync-icon">34</div>
                </label>
            </div>
        </div>
    </div>

    <!-- Voting Section -->
    <div id="votingSection" class="text-center mb-5" style="display: none;">
        <h3 class="mb-3">Voting In Progress...</h3>
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="text-muted mt-3">Please wait while others vote.</p>
    </div>

    <!-- Result Section -->
    <div id="resultSection" class="text-center" style="display: none;">
        <h3 class="mb-4">Results</h3>
        <p class="result-text display-5">The selected estimate is: <span id="resultValue"></span></p>
        <button class="btn btn-lg btn-primary mt-4" id="resetGame">Play Again</button>
    </div>

    <!-- Message Section -->
    <div class="text-center mt-5">
        <input type="text" id="message" class="form-control d-inline-block w-50" placeholder="Type your message">
        <button class="btn btn-success" onclick="sendMessage()">Send</button>
        <div id="messages" class="mt-4 border p-3" style="height: 200px; overflow-y: auto; background-color: #f9f9f9;"></div>
    </div>
</div>

<style>
    body {
        background: linear-gradient(to right, #74ebd5, #ACB6E5);
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    h1 {
        color: #ffffff;
        font-weight: 700;
    }

    h3 {
        color: #ffffff;
        font-weight: 500;
    }

    .sync-label {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 20px;
        background-color: #ffffff;
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        text-align: center;
        margin: 10px;
    }

    .sync-checkbox:checked + .sync-label {
        background-color: #e7f5ff;
        box-shadow: 0 6px 12px rgba(0, 123, 255, 0.2);
    }

    .sync-label:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
    }

    .sync-icon {
        font-size: 2.5rem;
        color: #117a45;
        margin-bottom: 15px;
    }

    .result-text {
        font-weight: bold;
        color: #007bff;
        text-shadow: 1px 1px 2px #ffffff;
    }

    #votingSection {
        color: #ffffff;
    }

    #resetGame {
        background: linear-gradient(to right, #ff416c, #ff4b2b);
        border: none;
        transition: background 0.3s ease;
    }

    #resetGame:hover {
        background: linear-gradient(to right, #ff4b2b, #ff416c);
    }

    #messages {
        background: #f8f8f8;
    }
</style>

<script>
    var conn = new WebSocket('ws://localhost:8081/chat');
    conn.onopen = function(e) {
        console.log("Connection established!");
    };

    conn.onmessage = function(e) {
        console.log(e.data);
        var messages = document.getElementById('messages');
        messages.innerHTML += e.data + '<br>';
        messages.scrollTop = messages.scrollHeight; // Auto-scroll to the bottom
    };

    function sendMessage() {
        var message = document.getElementById('message').value;
        if (message.trim() !== '') {
            conn.send(message);
            document.getElementById('message').value = '';
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const cards = document.querySelectorAll('.poker-card');
        const votingSection = document.getElementById('votingSection');
        const resultSection = document.getElementById('resultSection');
        const resultValue = document.getElementById('resultValue');
        const resetGame = document.getElementById('resetGame');

        cards.forEach(card => {
            card.addEventListener('click', () => {
                const selectedValue = card.getAttribute('data-value');
                showVotingSection(selectedValue);
                sendMessage(); // Send the selected value via WebSocket
            });
        });

        resetGame.addEventListener('click', () => {
            resetGameView();
        });

        function showVotingSection(selectedValue) {
            document.getElementById('cardSelection').style.display = 'none';
            votingSection.style.display = 'block';

            // Simulate voting process
            setTimeout(() => {
                votingSection.style.display = 'none';
                resultSection.style.display = 'block';
                resultValue.textContent = selectedValue;
            }, 2000);
        }

        function resetGameView() {
            document.getElementById('cardSelection').style.display = 'block';
            votingSection.style.display = 'none';
            resultSection.style.display = 'none';
        }
    });
</script>
