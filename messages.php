<?php session_start();
include('assets/inc/header.php');
include('db_connect.php'); 

if (!isset($_SESSION["staff_id"])) {
    header("Location: login/login.php");
    exit;
}
?>


<link rel="stylesheet" href="assets/css/messages.css">
<div class="chat-container">
    <div class="chat-header bg-primary">
        <h3>Messages</h3>
    </div>
    <div class="chat-messages" id="chatMessages">
        <!-- Messages will be dynamically added here -->
    </div>
    <form class="chat-input-form" id="chatInputForm">
        <input type="text" class="chat-input" id="messageInput" placeholder="Type your message...">
        <button type="submit" class="send-button">Send</button>
    </form>
</div>
<script>
    document.addEventListener('DOMContentLoaded', () => {
    const chatMessages = document.getElementById('chatMessages');
    const messageInput = document.getElementById('messageInput');
    const chatInputForm = document.getElementById('chatInputForm');

    chatInputForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const messageText = messageInput.value.trim();

        if (messageText) {
            const messageDiv = document.createElement('div');
            messageDiv.classList.add('message', 'sender'); // Assuming the user is the sender
            messageDiv.textContent = messageText;
            chatMessages.appendChild(messageDiv);
            messageInput.value = '';
            chatMessages.scrollTop = chatMessages.scrollHeight; // Scroll to the bottom
        }
    });
});
</script>
<?php include('assets/inc/footer.php'); ?>
