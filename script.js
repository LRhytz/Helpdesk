// Define the base URL for your PHP back end
const backendUrl = "https://helpdeskpharmacy.infinityfreeapp.com/";

// Fetch and display approved files based on subtopic
function fetchApprovedFiles(subtopic, containerId) {
  fetch(`${backendUrl}getApprovedFiles.php?subtopic=${subtopic}`)
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
    .catch(error => {
      console.error('Error fetching files:', error);
    });
}

// File upload function
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

  fetch(`${backendUrl}process_approval.php`, {
    method: 'POST',
    body: formData
  })
  .then(response => response.text())
  .then(result => {
    alert(result); // Alert the user on the result of file upload
  })
  .catch(error => {
    console.error('Error uploading file:', error);
  });

  // Reset input fields
  fileInput.value = "";
  document.getElementById('fileTitle').value = "";
}

// Search function
function searchFunction() {
  const input = document.getElementById("search-bar").value.toLowerCase();
  const mainTitles = document.querySelectorAll(".main-title");

  // Loop through each main title (each category)
  for (let i = 0; i < mainTitles.length; i++) {
    const mainTitle = mainTitles[i];
    const container = mainTitle.nextElementSibling;
    const lis = container.getElementsByTagName("li");

    if (input === "") {
      mainTitle.style.display = "block";
      container.style.display = "none";
      if (container.classList) {
        container.classList.remove("show");
      }
      for (let j = 0; j < lis.length; j++) {
        lis[j].style.display = "block";
      }
    } else {
      let matchFound = false;
      for (let j = 0; j < lis.length; j++) {
        const li = lis[j];
        const text = li.textContent || li.innerText;
        if (text.toLowerCase().includes(input)) {
          li.style.display = "block";
          matchFound = true;
        } else {
          li.style.display = "none";
        }
      }
      if (matchFound) {
        mainTitle.style.display = "block";
        container.style.display = "block";
        if (container.classList) {
          container.classList.add("show");
        }
      } else {
        mainTitle.style.display = "none";
        container.style.display = "none";
        if (container.classList) {
          container.classList.remove("show");
        }
      }
    }
  }
}

// Toggle subtopics visibility
function toggleSubTopics(contentId, arrowId, event) {
  if (event) { event.preventDefault(); }
  const subtopics = document.getElementById(contentId);
  const arrow = document.getElementById(arrowId);

  if (subtopics.classList) {
    subtopics.classList.toggle("show");
    if (arrow) {
      arrow.textContent = subtopics.classList.contains("show") ? "▲" : "▼";
    }
  } else {
    if (subtopics.className.indexOf("show") === -1) {
      subtopics.className += " show";
      if (arrow) { arrow.textContent = "▲"; }
    } else {
      subtopics.className = subtopics.className.replace(/\s?show/, "");
      if (arrow) { arrow.textContent = "▼"; }
    }
  }
}

// Toggle the add file form visibility
function toggleAddFileForm() {
  const form = document.getElementById('addFileForm');
  if (form.style.display === 'none' || form.style.display === '') {
    form.style.display = 'block';
  } else {
    form.style.display = 'none';
  }
}

// Verify admin passcode for accessing the file upload section
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

// Initial setup for the approved files list based on subtopic
window.onload = function() {
  // Example: fetch files for a specific subtopic (e.g., Receivables)
  fetchApprovedFiles('wi-receivables-subtopics', 'wi-receivables-subtopics-container');
};
