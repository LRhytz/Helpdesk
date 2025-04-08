// Fetch and display approved files based on subtopic
function fetchApprovedFiles(subtopic, containerId) {
    fetch(`https://helpdeskpharmacy.infinityfreeapp.com/getApprovedFiles.php?subtopic=${subtopic}`)
      .then(response => response.json())
      .then(data => {
        const fileListContainer = document.getElementById(containerId);
        fileListContainer.innerHTML = ''; // Clear previous files
  
        if (data.length === 0) {
          fileListContainer.innerHTML = '<p>No approved files available.</p>';
        } else {
          data.forEach(file => {
            const listItem = document.createElement('li');
            const link = document.createElement('a');
            link.href = file.fileUrl;
            link.target = '_blank';
            link.innerText = file.displayName;
            listItem.appendChild(link);
            fileListContainer.appendChild(listItem);
          });
        }
      })
      .catch(error => console.error('Error fetching files:', error));
  }
  
  // Upload file function
  function uploadFile() {
    const fileInput = document.getElementById('newFileInput');
    const file = fileInput.files[0];
    const fileTitle = document.getElementById('fileTitle').value || (file ? file.name : 'New File');
    const fileCategory = document.getElementById('fileCategory').value;
  
    if (!file) {
      alert('Please select a file.');
      return;
    }
  
    const formData = new FormData();
    formData.append('uploadedFile', file);
    formData.append('fileTitle', fileTitle);
    formData.append('fileCategory', fileCategory);
  
    fetch('https://helpdeskpharmacy.infinityfreeapp.com/upload_file.php', {
      method: 'POST',
      body: formData
    })
    .then(response => response.text())
    .then(result => alert(result))
    .catch(error => console.error('Error uploading file:', error));
  
    // Reset input fields
    fileInput.value = "";
    document.getElementById('fileTitle').value = "";
  }
  
  // Passcode verification
  function verifyPasscode() {
    const passcodeInput = document.getElementById('adminPasscode');
    const passcode = passcodeInput.value.trim();
    if (passcode === "helpdesk123") {
      document.getElementById('addFileContainer').style.display = 'block';
      document.getElementById('adminPasscodeSection').style.display = 'none';
    } else {
      alert("Incorrect passcode! Please try again.");
    }
  }
  
  // Toggle subtopics visibility
  function toggleSubTopics(contentId, arrowId) {
    const subtopics = document.getElementById(contentId);
    const arrow = document.getElementById(arrowId);
    subtopics.classList.toggle("show");
    arrow.textContent = subtopics.classList.contains("show") ? "▲" : "▼";
  }

  // Toggle the visibility of the Add File form
  function toggleAddFileForm() {
    var form = document.getElementById('addFileForm');
    if (form.style.display === 'none' || form.style.display === '') {
      form.style.display = 'flex'; // Show the form as a flex container
    } else {
      form.style.display = 'none';  // Hide the form
    }
  }

  
  // Initial setup for approved files list based on subtopic
  window.onload = function() {
    fetchApprovedFiles("wi-receivables-subtopics", "receivables-files");
    fetchApprovedFiles("wi-payables-subtopics", "payables-files");
    fetchApprovedFiles("wi-gl-subtopics", "gl-files");
    fetchApprovedFiles("wi-purchasing-subtopics", "purchasing-files");
    fetchApprovedFiles("wi-oracle-guides-subtopics", "oracle-guides-files");
  };
  