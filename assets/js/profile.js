document.addEventListener("DOMContentLoaded", function () {
  // Tab switching
  const tabButtons = document.querySelectorAll(".tab-button");
  const tabContents = document.querySelectorAll(".tab-content");

  tabButtons.forEach((button) => {
    button.addEventListener("click", function () {
      const tabId = this.getAttribute("data-tab");

      // Remove active class from all buttons and contents
      tabButtons.forEach((btn) => btn.classList.remove("active"));
      tabContents.forEach((content) => content.classList.remove("active"));

      // Add active class to current button and content
      this.classList.add("active");
      document.getElementById(tabId).classList.add("active");
    });
  });

  // Add education entry
  document
    .getElementById("add-education")
    .addEventListener("click", function () {
      const container = document.getElementById("education-container");
      const entries = container.querySelectorAll(".education-entry");
      const newIndex = entries.length;

      const newEntry = document.createElement("div");
      newEntry.className = "form-section education-entry";
      newEntry.innerHTML = `
                    <div class="input-field">
                        <label for="education_level_${newIndex}">Education Level:</label>
                        <select name="education[${newIndex}][education_level]" id="education_level_${newIndex}" required>
                            <option value="">Select Level</option>
                            <option value="Secondary">Secondary</option>
                            <option value="Diploma">Diploma</option>
                            <option value="Bachelor">Bachelor's Degree</option>
                            <option value="Master">Master's Degree</option>
                            <option value="PhD">PhD</option>
                        </select>
                    </div>
                    <div class="input-field">
                        <label for="institution_${newIndex}">Institution:</label>
                        <input type="text" id="institution_${newIndex}" name="education[${newIndex}][institution]" required>
                    </div>
                    <div class="input-field">
                        <label for="field_of_study_${newIndex}">Field of Study:</label>
                        <input type="text" id="field_of_study_${newIndex}" name="education[${newIndex}][field_of_study]" required>
                    </div>
                    <div class="input-field">
                        <label for="graduation_year_${newIndex}">Graduation Year:</label>
                        <input type="number" id="graduation_year_${newIndex}" name="education[${newIndex}][graduation_year]" min="1950" max="2030" required>
                    </div>
                    <div class="input-field">
                        <label for="education_cert_${newIndex}">Upload Certificate:</label>
                        <input type="file" id="education_cert_${newIndex}" name="education[${newIndex}][certificate]" accept=".pdf,.jpg,.jpeg,.png" required>
                    </div>
                    <button type="button" class="remove-btn remove-education">Remove</button>
                `;

      container.appendChild(newEntry);
      addRemoveListeners();
    });

  // Add achievement entry
  document
    .getElementById("add-achievement")
    .addEventListener("click", function () {
      const container = document.getElementById("achievements-container");
      const entries = container.querySelectorAll(".achievement-entry");
      const newIndex = entries.length;

      const newEntry = document.createElement("div");
      newEntry.className = "form-section achievement-entry";
      newEntry.innerHTML = `
                    <div class="input-field">
                        <label for="achievement_title_${newIndex}">Title:</label>
                        <input type="text" id="achievement_title_${newIndex}" name="achievements[${newIndex}][title]" required>
                    </div>
                    <div class="input-field">
                        <label for="achievement_description_${newIndex}">Description:</label>
                        <textarea id="achievement_description_${newIndex}" name="achievements[${newIndex}][description]" rows="3" required></textarea>
                    </div>
                    <div class="input-field">
                        <label for="achievement_year_${newIndex}">Year:</label>
                        <input type="number" id="achievement_year_${newIndex}" name="achievements[${newIndex}][year]" min="1950" max="2030" required>
                    </div>
                    <div class="input-field">
                        <label for="achievement_cert_${newIndex}">Upload Certificate/Evidence:</label>
                        <input type="file" id="achievement_cert_${newIndex}" name="achievements[${newIndex}][certificate]" accept=".pdf,.jpg,.jpeg,.png" required>
                    </div>
                    <button type="button" class="remove-btn remove-achievement">Remove</button>
                `;

      container.appendChild(newEntry);
      addRemoveListeners();
    });

  // Add work experience entry
  document.getElementById("add-work").addEventListener("click", function () {
    const container = document.getElementById("work-container");
    const entries = container.querySelectorAll(".work-entry");
    const newIndex = entries.length;

    const newEntry = document.createElement("div");
    newEntry.className = "form-section work-entry";
    newEntry.innerHTML = `
                    <div class="input-field">
                        <label for="company_${newIndex}">Company:</label>
                        <input type="text" id="company_${newIndex}" name="work[${newIndex}][company]" required>
                    </div>
                    <div class="input-field">
                        <label for="position_${newIndex}">Position:</label>
                        <input type="text" id="position_${newIndex}" name="work[${newIndex}][position]" required>
                    </div>
                    <div class="input-field">
                        <label for="start_date_${newIndex}">Start Date:</label>
                        <input type="date" id="start_date_${newIndex}" name="work[${newIndex}][start_date]" required>
                    </div>
                    <div class="input-field">
                        <label for="end_date_${newIndex}">End Date:</label>
                        <input type="date" id="end_date_${newIndex}" name="work[${newIndex}][end_date]">
                        <small>(Leave blank if current position)</small>
                    </div>
                    <div class="input-field">
                        <label for="work_description_${newIndex}">Description:</label>
                        <textarea id="work_description_${newIndex}" name="work[${newIndex}][description]" rows="3" required></textarea>
                    </div>
                    <button type="button" class="remove-btn remove-work">Remove</button>
                `;

    container.appendChild(newEntry);
    addRemoveListeners();
  });

  // Function to add remove listeners
  function addRemoveListeners() {
    // Remove education entry
    document.querySelectorAll(".remove-education").forEach((button) => {
      button.addEventListener("click", function () {
        this.closest(".education-entry").remove();
        renumberEducationEntries();
      });
    });
    // Remove achievement entry
    document.querySelectorAll(".remove-achievement").forEach((button) => {
      button.addEventListener("click", function () {
        this.closest(".achievement-entry").remove();
        renumberAchievementEntries();
      });
    });

    // Remove work entry
    document.querySelectorAll(".remove-work").forEach((button) => {
      button.addEventListener("click", function () {
        this.closest(".work-entry").remove();
        renumberWorkEntries();
      });
    });
  }

  // Functions to renumber entries after removal
  function renumberEducationEntries() {
    const entries = document.querySelectorAll(".education-entry");
    entries.forEach((entry, index) => {
      const inputs = entry.querySelectorAll("input, select, textarea");
      inputs.forEach((input) => {
        const name = input.getAttribute("name");
        const newName = name.replace(/education\[\d+\]/, `education[${index}]`);
        input.setAttribute("name", newName);

        const id = input.getAttribute("id");
        const newId = id.replace(
          /education_.*_\d+/,
          `education_${id.split("_")[1]}_${index}`
        );
        input.setAttribute("id", newId);
      });

      const labels = entry.querySelectorAll("label");
      labels.forEach((label) => {
        const forAttr = label.getAttribute("for");
        const newForAttr = forAttr.replace(
          /education_.*_\d+/,
          `education_${forAttr.split("_")[1]}_${index}`
        );
        label.setAttribute("for", newForAttr);
      });
    });
  }

  function renumberAchievementEntries() {
    const entries = document.querySelectorAll(".achievement-entry");
    entries.forEach((entry, index) => {
      const inputs = entry.querySelectorAll("input, textarea");
      inputs.forEach((input) => {
        const name = input.getAttribute("name");
        const newName = name.replace(
          /achievements\[\d+\]/,
          `achievements[${index}]`
        );
        input.setAttribute("name", newName);

        const id = input.getAttribute("id");
        const newId = id.replace(
          /achievement_.*_\d+/,
          `achievement_${id.split("_")[1]}_${index}`
        );
        input.setAttribute("id", newId);
      });

      const labels = entry.querySelectorAll("label");
      labels.forEach((label) => {
        const forAttr = label.getAttribute("for");
        const newForAttr = forAttr.replace(
          /achievement_.*_\d+/,
          `achievement_${forAttr.split("_")[1]}_${index}`
        );
        label.setAttribute("for", newForAttr);
      });
    });
  }

  function renumberWorkEntries() {
    const entries = document.querySelectorAll(".work-entry");
    entries.forEach((entry, index) => {
      const inputs = entry.querySelectorAll("input, textarea");
      inputs.forEach((input) => {
        const name = input.getAttribute("name");
        const newName = name.replace(/work\[\d+\]/, `work[${index}]`);
        input.setAttribute("name", newName);

        const id = input.getAttribute("id");
        if (id) {
          const idParts = id.split("_");
          const newId = `${idParts[0]}_${
            idParts.length > 2 ? idParts[1] : ""
          }_${index}`.replace(/__/, "_");
          input.setAttribute("id", newId);
        }
      });

      const labels = entry.querySelectorAll("label");
      labels.forEach((label) => {
        const forAttr = label.getAttribute("for");
        if (forAttr) {
          const forParts = forAttr.split("_");
          const newForAttr = `${forParts[0]}_${
            forParts.length > 2 ? forParts[1] : ""
          }_${index}`.replace(/__/, "_");
          label.setAttribute("for", newForAttr);
        }
      });
    });
  }

  // CV Generation
  document
    .getElementById("generate-cv-btn")
    .addEventListener("click", function () {
      // Get form data
      const name = document.getElementById("name").value;
      const email = document.getElementById("email").value;
      const phone = document.getElementById("phone").value;
      const icNumber = document.getElementById("ic_number").value;

      // Education
      const educationEntries = document.querySelectorAll(".education-entry");
      let educationHTML = "";

      educationEntries.forEach((entry) => {
        const level = entry.querySelector('[id^="education_level_"]').value;
        const institution = entry.querySelector('[id^="institution_"]').value;
        const field = entry.querySelector('[id^="field_of_study_"]').value;
        const year = entry.querySelector('[id^="graduation_year_"]').value;

        if (level && institution && field && year) {
          educationHTML += `
                            <div class="cv-item">
                                <h4>${level} in ${field}</h4>
                                <p>${institution} | ${year}</p>
                            </div>
                        `;
        }
      });

      // Work Experience
      const workEntries = document.querySelectorAll(".work-entry");
      let workHTML = "";

      workEntries.forEach((entry) => {
        const company = entry.querySelector('[id^="company_"]').value;
        const position = entry.querySelector('[id^="position_"]').value;
        const startDate = entry.querySelector('[id^="start_date_"]').value;
        const endDateInput = entry.querySelector('[id^="end_date_"]');
        const endDate = endDateInput.value || "Present";
        const description = entry.querySelector(
          '[id^="work_description_"]'
        ).value;

        if (company && position && startDate && description) {
          const formattedStartDate = new Date(startDate).toLocaleDateString(
            "en-US",
            { year: "numeric", month: "short" }
          );
          const formattedEndDate =
            endDate === "Present"
              ? "Present"
              : new Date(endDate).toLocaleDateString("en-US", {
                  year: "numeric",
                  month: "short",
                });

          workHTML += `
                            <div class="cv-item">
                                <h4>${position}</h4>
                                <p>${company} | ${formattedStartDate} - ${formattedEndDate}</p>
                                <p>${description}</p>
                            </div>
                        `;
        }
      });

      // Achievements
      const achievementEntries =
        document.querySelectorAll(".achievement-entry");
      let achievementsHTML = "";

      achievementEntries.forEach((entry) => {
        const title = entry.querySelector('[id^="achievement_title_"]').value;
        const description = entry.querySelector(
          '[id^="achievement_description_"]'
        ).value;
        const year = entry.querySelector('[id^="achievement_year_"]').value;

        if (title && description && year) {
          achievementsHTML += `
                            <div class="cv-item">
                                <h4>${title} (${year})</h4>
                                <p>${description}</p>
                            </div>
                        `;
        }
      });

      // Update CV preview
      document.getElementById("cv-name").textContent = name;
      document.getElementById("cv-contact").innerHTML = `
                    <strong>Email:</strong> ${email} | <strong>Phone:</strong> ${phone} | <strong>IC Number:</strong> ${icNumber}
                `;

      document.getElementById("cv-education").innerHTML =
        educationHTML || "<p>No education information provided.</p>";
      document.getElementById("cv-work").innerHTML =
        workHTML || "<p>No work experience information provided.</p>";
      document.getElementById("cv-achievements").innerHTML =
        achievementsHTML || "<p>No achievements information provided.</p>";

      // Show CV preview and save button
      document.getElementById("cv-preview").style.display = "block";
      document.getElementById("save-cv-container").style.display = "block";
    });

  // Save CV button
  document.getElementById("save-cv-btn").addEventListener("click", function () {
    document.getElementById("auto_generate_cv").value = "1";
    const cvContent = document.getElementById("cv-preview").innerHTML;

    // Create a hidden input to store CV HTML content
    const hiddenInput = document.createElement("input");
    hiddenInput.type = "hidden";
    hiddenInput.name = "cv_content";
    hiddenInput.value = cvContent;
    document.getElementById("profileForm").appendChild(hiddenInput);

    // Submit the form
    document.getElementById("profileForm").submit();
  });

  // Floating save button
  document
    .getElementById("floating-save-btn")
    .addEventListener("click", function () {
      document.getElementById("profileForm").submit();
    });

  // Add initial remove listeners
  addRemoveListeners();
});
