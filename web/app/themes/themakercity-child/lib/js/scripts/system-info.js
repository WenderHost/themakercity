function sendSystemInfo(event) {
  event.preventDefault();
  const sendButton = document.getElementById('send-system-info');
  sendButton.disabled = true; // Disable the button
  document.getElementById('status').innerText = "Gathering system details...";

  fetch('https://api64.ipify.org?format=json')
    .then(response => response.json())
    .then(data => {
      const systemInfo = {
        browser: navigator.userAgent,
        os: navigator.platform,
        screenResolution: `${screen.width}x${screen.height}`,
        ip: data.ip,
        user_email: wpvars.user_email
      };

      const endpoint = wpvars.ajax_url + '?action=send_system_info';
      console.log('🔔 Endpoint: ', endpoint );
      console.log('👉 systeminfo', systemInfo );

      fetch(endpoint, {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(systemInfo)
      })
      .then(response => response.text())
      .then(result => {
        document.getElementById('status').innerText = "System details sent successfully!";
      })
      .catch(error => {
        document.getElementById('status').innerText = "Error sending system details.";
        sendButton.disabled = false; // Re-enable the button on error
      });
    })
    .catch(error => {
      document.getElementById('status').innerText = "Error retrieving IP address.";
      sendButton.disabled = false; // Re-enable the button on error
    });
}


document.addEventListener("DOMContentLoaded", function() {
    document.getElementById("send-system-info").addEventListener("click", sendSystemInfo);
});